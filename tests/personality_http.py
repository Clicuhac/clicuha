"""A–F integration checks. Requires local PHP server and .env DB_NAME ending _test.
Run: python tests/personality_http.py [http://127.0.0.1:8080]
Creates synthetic records only in that explicit test database.
"""
import atexit
import http.cookiejar
import json
import os
from pathlib import Path
import re
import secrets
import subprocess
import sys
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
BASE = sys.argv[1] if len(sys.argv) > 1 else 'http://127.0.0.1:8080'
assert urllib.parse.urlparse(BASE).hostname in ('127.0.0.1', 'localhost')
PHP = os.environ.get('CLICUHA_PHP', '/workspace/setup/bin/php')
SQL_HELPER = r'''
$env=parse_ini_file($argv[1],false,INI_SCANNER_RAW);
if (!str_ends_with($env['DB_NAME'],'_test')) throw new RuntimeException('Test DB required');
$p=new PDO('mysql:host='.$env['DB_HOST'].';dbname='.$env['DB_NAME'].';charset=utf8mb4',$env['DB_USER'],$env['DB_PASS'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$in=json_decode(stream_get_contents(STDIN),true);
$s=$p->prepare($in['sql']);$s->execute($in['args']);
echo json_encode($s->columnCount() ? $s->fetchAll(PDO::FETCH_ASSOC) : []);
'''
def sql(query, args=()):
    out = subprocess.run([PHP, '-r', SQL_HELPER, str(ROOT / '.env')],
        input=json.dumps({'sql': query, 'args': list(args)}), text=True, capture_output=True, check=True)
    return json.loads(out.stdout)

created_users = []
def cleanup():
    for uid in created_users:
        sql('DELETE FROM nicknames WHERE created_by=?', [uid])
        sql('DELETE FROM logs WHERE user_id=?', [uid])
        sql('DELETE FROM users WHERE id=?', [uid])
atexit.register(cleanup)

class Client:
    def __init__(self):
        self.client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    def request(self, path, data=None, expected=200):
        data = urllib.parse.urlencode(data, doseq=True).encode() if data is not None else None
        try:
            response = self.client.open(BASE + path, data)
        except urllib.error.HTTPError as error:
            response = error
        body = response.read().decode()
        assert response.code == expected, (path, response.code, body[:400])
        assert not any(text in body for text in ['Fatal error', 'DB connection failed', 'Warning:']), body[:400]
        return response.url, body
    def token(self, path='/forming.php'):
        body = self.request(path)[1]
        return re.search(r'name="csrf_token"[^>]*value="([^"]+)"', body).group(1)
    def register(self):
        email = 'ready-' + secrets.token_hex(8) + '@example.invalid'
        password = secrets.token_urlsafe(24)
        self.request('/register.php', {'email': email, 'password': password, 'password2': password})
        self.request('/login.php', {'email': email, 'password': password})
        self.uid = int(sql('SELECT id FROM users WHERE email=?', [email])[0]['id'])
        created_users.append(self.uid)
    def create(self, free=False, route='/add_bootstrap.php'):
        title = 'READY-' + secrets.token_hex(8)
        token = self.token('/add_bootstrap.php')
        data = {'csrf_token': token, 'title': title, 'save_mode': 'publish'}
        if free: data['leave_free'] = '1'
        self.request(route, data)
        nick = sql('SELECT * FROM nicknames WHERE title=?', [title])[0]
        assert nick['is_published'] == 0 and nick['created_by'] == self.uid
        assert nick['owner_id'] == (None if free else self.uid)
        return nick['id'], title
    def publish(self, ident, expected=200, path='/forming.php'):
        return self.request(path, {'id': ident, 'action':'publish','csrf_token':self.token('/add_bootstrap.php')}, expected)
    def core(self, ident, **values):
        return self.request('/personality.php?id='+str(ident), {'csrf_token':self.token('/add_bootstrap.php'), **values})

sql('SELECT COUNT(*) FROM clicuha_personalities')  # refuses non-test DB before any writes
actor, stranger, anon = Client(), Client(), Client()
actor.register(); stranger.register()
owned, title = actor.create()
actor.publish(owned, 422)
assert sql('SELECT is_published FROM nicknames WHERE id=?',[owned])[0]['is_published'] == 0
assert 'disabled' in actor.request('/my_nicknames.php')[1]
for client in (stranger, anon):
    for path in ('/view.php?id=', '/room.php?id=', '/edit_nickname.php?id='):
        # anonymous editing redirects to login, but public viewing must refuse.
        if client is anon and 'edit_' in path: continue
        client.request(path+str(owned), expected=403)
    assert title not in client.request('/')[1]
actor.core(owned, mode='manual', **{'traits[]':['curious','creative','independent'], 'motivation':'Досліджувати', 'vulnerability':'Важко довіряти'})
actor.publish(owned)
assert title in anon.request('/view.php?id='+str(owned))[1]
actor.request('/edit_nickname.php?id='+str(owned))
actor.request('/edit.php?id='+str(owned), {'csrf_token':actor.token('/add_bootstrap.php'),'title':title,'description':'Updated by owner'})
print('PASS A owned lifecycle; C direct NOT READY POST; E draft privacy')

free, title_free = actor.create(True)
assert title_free not in actor.request('/my_nicknames.php')[1]
assert title_free in actor.request('/forming.php')[1]
actor.request('/edit_nickname.php?id='+str(free))
actor.request('/edit_nickname.php?id='+str(free), {'csrf_token':actor.token('/add_bootstrap.php'),'title':title_free})
actor.core(free, mode='preset', preset_key='explorer_v1')
stored = sql('SELECT * FROM clicuha_personalities WHERE nickname_id=?',[free])[0]
core = json.loads(stored['core_json'])
assert len(core['traits']) >= 3 and core['motivation'] and core['vulnerability']
# Change the actual preset source temporarily, then verify persisted core is unchanged.
preset_file = ROOT / 'lib/personality.php'
original = preset_file.read_bytes()
try:
    preset_file.write_bytes(original.replace('Досліджувати нове та знаходити власні відповіді.'.encode(), 'Інша мотивація шаблону.'.encode()))
    assert stored['core_json'] == sql('SELECT core_json FROM clicuha_personalities WHERE nickname_id=?',[free])[0]['core_json']
    assert 'Досліджувати нове' in actor.request('/personality.php?id='+str(free))[1]
finally:
    preset_file.write_bytes(original)
actor.publish(free)
for route in ('/edit_nickname.php?id=', '/edit.php?id=', '/personality.php?id='):
    actor.request(route+str(free), expected=403)
    actor.request(route+str(free), {'csrf_token':actor.token('/add_bootstrap.php'),'title':'Unauthorized'}, expected=403)
actor.request('/avatar_upload.php', {'id':free,'csrf_token':actor.token('/add_bootstrap.php')}, expected=403)
actor.request('/nickname_delete.php', {'id':free,'csrf_token':actor.token('/add_bootstrap.php')}, expected=403)
actor.publish(free, 403)
assert sql('SELECT owner_id,created_by FROM nicknames WHERE id=?',[free])[0] == {'owner_id':None,'created_by':actor.uid}
print('PASS B free draft formation then 403 for direct GET/POST; F preset snapshot')

# Legacy public without core: remains visible but needs READY on re-publication.
legacy_title = 'Legacy-' + secrets.token_hex(8)
sql('INSERT INTO nicknames(title,created_by,owner_id,is_published,first_published_at) VALUES (?,?,?,1,NOW())',[legacy_title,actor.uid,actor.uid])
legacy = sql('SELECT id FROM nicknames WHERE title=?',[legacy_title])[0]['id']
assert legacy_title in anon.request('/view.php?id='+str(legacy))[1]
assert legacy_title in anon.request('/')[1]
assert sql('SELECT * FROM clicuha_personalities WHERE nickname_id=?',[legacy]) == []
actor.request('/my_nicknames.php',{'id':legacy,'action':'cabinet','csrf_token':actor.token('/add_bootstrap.php')})
actor.publish(legacy,422)
print('PASS D legacy visibility, no synthetic core, re-publication blocked')

# Room with/without avatar; only mutate local static data temporarily.
room_id, _ = actor.create()
room_slug = 'room-' + secrets.token_hex(8)
sql('UPDATE nicknames SET slug=? WHERE id=?',[room_slug,room_id])
personas_file = ROOT/'data/ai_personas.php'
original_personas = personas_file.read_bytes()
try:
    modified = original_personas.replace(b"'tyha-iskra' =>", ("'"+room_slug+"' =>").encode())
    personas_file.write_bytes(modified)
    assert '/assets/avatars/tyha-iskra.jpg' in actor.request('/room.php?id='+str(room_id))[1]
    personas_file.write_bytes(modified.replace(b"'avatar_url' => '/assets/avatars/tyha-iskra.jpg'", b"'avatar_url' => ''"))
    assert 'Аватар відсутній' in actor.request('/room.php?id='+str(room_id))[1]
finally:
    personas_file.write_bytes(original_personas)
stranger.request('/room.php?id='+str(room_id),expected=403)

# Additional creation path cannot bypass draft state.
actor.create(True, '/add_simple.php')
# Creator cannot regain control after first publication, even if hidden by system.
sql('UPDATE nicknames SET is_published=0 WHERE id=?',[free])
actor.request('/personality.php?id='+str(free),expected=403)
# CSRF and unknown traits cannot bypass readiness.
actor.request('/forming.php',{'id':owned,'action':'cabinet','csrf_token':'wrong'},expected=403)
# Public core cannot be reduced to an incomplete core.
actor.request('/personality.php?id='+str(owned),{'csrf_token':actor.token('/add_bootstrap.php'),'mode':'manual','motivation':'','vulnerability':''},expected=422)
print('PASS ROOM avatar/fallback, alternate creation, first-publication boundary, CSRF and public-core invariant')

# Administrative visibility / free management / archive restores cannot publish legacy.
admin = Client(); admin.register()
sql("UPDATE users SET role='admin' WHERE id=?", [admin.uid])
admin.request('/view.php?id='+str(room_id))
admin.request('/personality.php?id='+str(free))
_, body = admin.request('/admin/nicknames.php')
admin_csrf = re.search(r'name="csrf"[^>]*value="([^"]+)"', body).group(1)
admin.request('/admin/nicknames.php', {'csrf':admin_csrf,'id':legacy,'action':'archive'})
admin.request('/admin/archive.php', {'csrf':admin_csrf,'id':legacy,'action':'restore'})
assert sql('SELECT is_published,deleted_at FROM nicknames WHERE id=?',[legacy])[0] == {'is_published':0,'deleted_at':None}
admin.publish(legacy,422)
# Draft owner distinct from creator can view and form; no arbitrary user can.
sql('UPDATE nicknames SET owner_id=? WHERE id=?', [stranger.uid,room_id])
stranger.request('/view.php?id='+str(room_id))
stranger.request('/personality.php?id='+str(room_id))
# Existing AI birth route creates an unpublished free record with real creator.
sql('SELECT COUNT(*) FROM nicknames WHERE slug=?', ['tyha-iskra'])
# Use only a fresh test DB with no existing AI birth fixture.
if not sql('SELECT id FROM nicknames WHERE slug=?', ['tyha-iskra']):
    _, body = admin.request('/admin/ai_birth.php')
    birth_token = re.search(r'name="csrf"[^>]*value="([^"]+)"',body).group(1)
    admin.request('/admin/ai_birth.php', {'csrf':birth_token})
    born = sql('SELECT created_by,owner_id,is_published FROM nicknames WHERE slug=?',['tyha-iskra'])[0]
    assert born == {'created_by':admin.uid,'owner_id':None,'is_published':0}
print('PASS administrative access, archive restore, distinct creator/owner draft access and AI birth creation')
