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

Звірено з production phpMyAdmin 2026-08-25.

| Поле | Тип / властивості | Призначення |
| --- | --- | --- |
| `id` | `int(11)`, PK, AUTO_INCREMENT | ID клікухи |
| `title` | `varchar(100)`, NOT NULL | Назва |
| `short_title` | `varchar(100)`, NULL | Коротка назва |
| `user_id` | `bigint(20) unsigned`, NULL, indexed | Власник; `NULL` означає відсутність прив’язаного власника |
| `is_anonymous` | `tinyint(1)`, NOT NULL, default `0` | Анонімне відображення автора |
| `tone` | `varchar(32)`, NOT NULL, default `neutral` | Тон / тип подачі |
| `slug` | `varchar(120)`, NULL, unique index | URL-friendly slug |
| `description` | `text`, NULL | Опис |
| `ip` | `varbinary(16)`, NULL | IP-дані створення/службові дані |
| `cookie_id` | `varchar(64)`, NULL | Ідентифікатор cookie |
| `created_at` | `timestamp`, NOT NULL, default `current_timestamp()` | Дата створення |
| `deleted_at` | `datetime`, NULL | Soft delete; `NULL` = активна |

### Поточна логіка MVP

- `user_id` використовується для ownership.
- `is_anonymous` керує показом автора в галереї.
- `deleted_at` реалізує м’яке видалення; галерея, detail та «Мої клікухи» мають показувати тільки `deleted_at IS NULL`.
- `slug` має бути нормалізований і унікальний.

## 3. Майбутні таблиці

`comments`, `likes` та інші модулі залишаються планованими й не повинні вважатися production-схемою, доки реально не створені в БД.

## 4. Правило синхронізації

Після будь-якої зміни production-схеми цей файл треба оновлювати в тому самому PR/релізі. Не використовувати стару документацію як підставу для ALTER TABLE без звірки з phpMyAdmin або міграціями.
