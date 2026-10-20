# Database Schema — Clicuha

Цей документ описує відому структуру бази даних Clicuha. Для production-таблиць пріоритет має фактична структура в phpMyAdmin.

## 1. Таблиця `users`

Схема цього розділу потребує окремої звірки з production перед змінами, пов’язаними з псевдонімом або темами кабінету.

Відомо з поточного коду, що використовуються щонайменше:

| Поле | Призначення |
| --- | --- |
| `id` | ID користувача |
| `email` | email для входу |
| `password_hash` | хеш пароля |

## 2. Таблиця `nicknames`

Базову схему звірено з production phpMyAdmin. Розділення авторства/власності описує міграція `2026-10-20_separate_authorship_ownership.sql`.

| Поле | Тип / властивості | Призначення |
| --- | --- | --- |
| `id` | `int(11)`, PK, AUTO_INCREMENT | ID клікухи |
| `title` | `varchar(100)`, NOT NULL | Назва |
| `short_title` | `varchar(100)`, NULL | Коротка назва |
| `user_id` | `bigint(20) unsigned`, NULL, indexed | Legacy-поле власності; тимчасово зберігається для безпечного розгортання/відкату |
| `created_by` | `bigint(20) unsigned`, NULL, indexed | Незмінний автор; NULL для невідомого старого автора |
| `owner_id` | `bigint(20) unsigned`, NULL, indexed | Поточний власник; NULL = вільна клікуха |
| `is_anonymous` | `tinyint(1)`, NOT NULL, default `0` | Анонімне відображення автора |
| `tone` | `varchar(32)`, NOT NULL, default `neutral` | Тон / тип подачі |
| `slug` | `varchar(120)`, NULL, unique index | URL-friendly slug |
| `description` | `text`, NULL | Опис |
| `ip` | `varbinary(16)`, NULL | IP-дані створення/службові дані |
| `cookie_id` | `varchar(64)`, NULL | Ідентифікатор cookie |
| `created_at` | `timestamp`, NOT NULL, default `current_timestamp()` | Дата створення |
| `deleted_at` | `datetime`, NULL | Soft delete; `NULL` = активна |

### Поточна логіка MVP

- `created_by` зберігає автора незалежно від анонімності; `owner_id` визначає власність та owner-only доступ.
- Звичайне створення записує поточного користувача в `created_by` і `owner_id`; «Залишити клікуху вільною» записує NULL лише в `owner_id`.
- `is_anonymous` керує показом автора в галереї.
- `deleted_at` реалізує м’яке видалення; галерея, detail та «Мої клікухи» мають показувати тільки `deleted_at IS NULL`.
- `slug` має бути нормалізований і унікальний.
- `user_id` після цього релізу вважається legacy і не використовується новим кодом; видаляти його слід окремою cleanup-міграцією після стабілізації релізу.

## 3. Майбутні таблиці

`comments`, `likes` та інші модулі залишаються планованими й не повинні вважатися production-схемою, доки реально не створені в БД.

## 4. Правило синхронізації

Після будь-якої зміни production-схеми цей файл треба оновлювати в тому самому PR/релізі. Не використовувати стару документацію як підставу для ALTER TABLE без звірки з phpMyAdmin або міграціями.

## Міграція Issue #50

Перед міграцією зробити backup, звірити `SHOW CREATE TABLE nicknames`, кількість рядків, NULL у `user_id`, індекси та foreign keys. Міграція додає `created_by` і `owner_id`, копіює в обидва поля старе значення `user_id` (включно з NULL) і навмисно залишає `user_id` на місці. Це дозволяє старому коду працювати до моменту deploy та спрощує rollback. Після перевірки міграції слід одразу розгорнути відповідний PHP-код. Видалення legacy `user_id` — окремий майбутній крок.
