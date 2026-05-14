# SETUP & DEPLOYMENT — Manifesto

> Как се пуска проектът на нова машина (XAMPP/WAMP), какво трябва да е инсталирано, как се конфигурира.
> Цел: < 10 минути от clone до login screen.
> Версия: 1.1 · Дата: 2026-05-14

---

## 1. Предварителни изисквания (target машина)

| Софтуер | Версия | Бележка |
|---|---|---|
| XAMPP | 8.1+ (с PHP 8.1+) | или WAMP. Apache + MySQL/MariaDB включени. |
| PHP extensions | pdo_mysql, curl, mbstring, openssl, zip, json | обикновено включени по подразбиране в XAMPP. |
| Composer | 2.x | САМО за autoload (без vendor зависимости). |
| Git | 2.x | за clone (по избор — може и да копирате папката). |
| Browser | модерен (Chrome, Firefox, Edge) | за достъп до dashboard-а. |

**Проверка на PHP extensions:**
```bash
php -m | grep -E "pdo_mysql|curl|mbstring|openssl|zip|json"
```

---

## 2. Стъпки за стартиране на нова машина

### 2.1 Подготовка

```bash
# 1. Инсталирай XAMPP (Windows installer от apachefriends.org)
# 2. Стартирай XAMPP Control Panel
# 3. Start Apache + Start MySQL
```

### 2.2 Клониране на проекта

```bash
cd C:\xampp\htdocs
git clone <repo-url> manifesto
cd manifesto
```

(Или копирай папката ръчно в `C:\xampp\htdocs\manifesto\`.)

### 2.3 Autoload

```bash
composer dump-autoload
```

Това създава `vendor/autoload.php`. Не свалят никакви външни пакети — само PSR-4 autoloader.

### 2.4 Конфигурация на средата

```bash
cp .env.example .env
```

Редактирай `.env`:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=manifesto
DB_USER=root
DB_PASS=
APP_BASE_URL=http://localhost/manifesto/public
APP_ENV=development
SESSION_LIFETIME=3600
```

**Бележка:** При XAMPP по подразбиране `DB_USER=root` и `DB_PASS=` (празна парола).

### 2.5 База данни

Опция А — phpMyAdmin (GUI):
1. Отвори `http://localhost/phpmyadmin`.
2. Създай база `manifesto` (utf8mb4_unicode_ci).
3. Import → избери `db/schema.sql` → Go.
4. Import → избери `db/seed.sql` → Go.

Опция Б — командно (по-бързо):
```bash
mysql -u root -e "CREATE DATABASE manifesto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root manifesto < db/schema.sql
mysql -u root manifesto < db/seed.sql
```

### 2.6 Достъп

Отвори в browser: `http://localhost/manifesto/public/`.

Login:
- **admin / admin** (admin role)
- **viewer / viewer** (viewer role)

⚠️ Веднага смени паролата на admin от UI настройките (или директно през phpMyAdmin) преди да хостваш където и да било.

---

## 3. Apache конфигурация

`public/.htaccess` се грижи за rewrite-а:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

Изисква `mod_rewrite` enabled (по подразбиране в XAMPP е enabled).

Ако `mod_rewrite` НЕ работи:
1. Отвори `C:\xampp\apache\conf\httpd.conf`.
2. Намери `#LoadModule rewrite_module modules/mod_rewrite.so`.
3. Махни `#` отпред.
4. Намери `<Directory "C:/xampp/htdocs">` блок и провери `AllowOverride All`.
5. Restart Apache.

---

## 4. Troubleshooting

| Проблем | Причина | Решение |
|---|---|---|
| 500 Internal Server Error при отваряне на `/`. | Грешка в `.env` или PDO connection fail. | Виж `storage/logs/error.log`. |
| „Database connection failed". | Грешен `DB_HOST`/`DB_USER`/`DB_PASS`. | Провери `.env`; провери че MySQL service работи. |
| Login не работи, „Invalid credentials". | Seed не е импортнат. | Импортирай `db/seed.sql`. |
| 404 на всички URL-и освен root. | mod_rewrite не работи. | Виж секция 3. |
| Permission denied при писане в `storage/`. | Папката няма write права. | На Windows обикновено не е проблем. На Linux: `chmod -R 777 storage/`. |
| „Class not found". | Composer autoload не е генериран. | `composer dump-autoload`. |
| Кирилицата изглежда като ??? | Charset не е utf8mb4. | Проверка: `SHOW VARIABLES LIKE 'character_set_database';` трябва да върне `utf8mb4`. |

---

## 5. Production deployment (бъдеще)

За MVP — само XAMPP. Бъдещ production deployment ще изисква:
- HTTPS със Let's Encrypt.
- `APP_ENV=production` в `.env` (изключва debug output).
- `display_errors = Off` в PHP.
- Reverse proxy (nginx) пред Apache.
- Backup стратегия на MySQL.
- Различен seed (без admin/admin).
- Firewall за достъп до phpMyAdmin.

Виж `FUTURE_WORK.md` за повече.

---

## 6. Smoke test след setup

След като стартираш проекта на нова машина, провери че:

- [ ] `http://localhost/manifesto/public/login` зарежда.
- [ ] Login с `admin/admin` работи.
- [ ] Dashboard страницата показва demo Project от seed.
- [ ] Tree view се разгъва и сгъва.
- [ ] Натискане на „Generate" на demo Project произвежда docker-compose.yml.
- [ ] Download .zip работи.
- [ ] Logout връща към login страницата.
- [ ] Login с `viewer/viewer` показва същия dashboard, но бутоните за edit/create липсват.

Ако всички проверки минават — setup-ът е успешен.

---

## 7. Backup & restore (локално)

**Backup на DB:**
```bash
mysqldump -u root manifesto > backup_2026-05-14.sql
```

**Restore:**
```bash
mysql -u root manifesto < backup_2026-05-14.sql
```

**Backup на storage (генерирани файлове):** копирай папка `storage/generated/`.

**Не е нужно** да backup-ваш `vendor/` — той се регенерира с `composer dump-autoload`.

---

## 8. Локална разработка vs защита

| Аспект | Development | Защита (демо) |
|---|---|---|
| `APP_ENV` | `development` | `production` |
| Error display | On | Off (логва се само) |
| Seed | full (с demo project) | full (за демонстрация) |
| Default admin | admin/admin | admin/admin (за демо OK) |
| URL | localhost | localhost |
| HTTPS | Не | Не |

Преди защита — пусни smoke test (раздел 6) на машината, която ще използваш. Имай и backup на лаптоп backup (USB, cloud).
