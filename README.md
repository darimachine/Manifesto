# Manifesto

> **Declare your infrastructure. Generate your stack.**

Уеб dashboard на vanilla **PHP 8** + **MySQL/MariaDB** (без framework), който описва Docker инфраструктура и генерира конфигурационни файлове: `docker-compose.yml`, `.env` и Emmet текстов export.

Курсов проект — `Web Programming` (FMI, w26).

---

## Технологии

- **Backend:** PHP 8.1+ (vanilla, PSR-4 autoload, без vendor зависимости)
- **DB:** MySQL 8.0+ / MariaDB 10.4+
- **Frontend:** HTML + CSS + vanilla JavaScript (без CDN, всичко локално)
- **Сигурност:** PDO prepared statements, CSRF защита на всеки POST, `password_hash`/`verify`, session regenerate след login, `htmlspecialchars` навсякъде
- **Деплой:** XAMPP (primary) + по желание Docker (`docker compose up`)

---

## Бърз старт (XAMPP — препоръчителен начин за изпит)

### 1. Постави проекта в `htdocs`

```
C:\xampp\htdocs\manifesto\           ← Windows
/Applications/XAMPP/htdocs/manifesto/ ← macOS
```

### 2. Конфигурирай `.env`

```bash
cp .env.example .env
```

Default стойностите работят с XAMPP без редакции (`root` / празна парола).

### 3. Импортирай схемата + seed данните

Отвори [http://localhost/phpmyadmin](http://localhost/phpmyadmin) и пусни:

1. `db/schema.sql` — създава БД `manifesto` + 9-те таблици
2. `db/seed.sql` — admin/viewer потребители + демо проект

Алтернативно през CLI:

```bash
mysql -u root < db/schema.sql
mysql -u root manifesto < db/seed.sql
```

### 4. (Опционално) Composer autoload

Ако имаш Composer:

```bash
composer dump-autoload
```

Без Composer работи — има built-in PSR-4 fallback в `public/index.php`.

### 5. Отвори в браузъра

```
http://localhost/manifesto/
```

Login:
- **Admin:** `admin` / `admin123`
- **Viewer:** `viewer` / `viewer123`

---

## Бърз старт (Docker — алтернатива)

```bash
cp .env.example .env
docker compose up -d
```

- App: [http://localhost:8080](http://localhost:8080)
- DB exposed на host port `33060` (за DBeaver / phpMyAdmin)
- Schema + seed се зареждат автоматично при първи `up`

Спиране:

```bash
docker compose down       # пази данните
docker compose down -v    # изтрива всичко (за чист тест)
```

---

## Какво прави приложението

1. **CRUD на инфраструктурна йерархия:** Docker Host → Project → Service (+ ports / env vars / volumes) → Web App
2. **Генерира 3 файла за всеки project:**
   - `docker-compose.yml` (Docker Compose v3.8 YAML)
   - `.env` (групиран по service, маркирани secrets)
   - Emmet export (UTF-8 дърво с box-drawing chars)
3. **Версионира** всяка генерация (`generated_file` table) — целият history е достъпен
4. **Sidebar tree** с цялата йерархия — навигация с един клик
5. **Role-based access:** admin (CRUD) и viewer (read-only)

---

## Структура на проекта

```
Manifesto/
├── public/              ← DocumentRoot (front controller + assets)
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── src/
│   ├── Core/            ← Router, Auth, Database, Session, CSRF, ...
│   ├── Models/          ← POPO DTOs (9 entities)
│   ├── Repositories/    ← PDO data access (всички SQL queries)
│   ├── Services/        ← DockerComposeGenerator, EnvFileGenerator, EmmetExporter
│   ├── Controllers/     ← HTTP layer (7 controllers)
│   └── Views/           ← PHP templates
├── config/
│   ├── config.php
│   └── routes.php
├── db/
│   ├── schema.sql       ← 9 таблици
│   └── seed.sql         ← admin/viewer + demo project
├── storage/             ← logs + generated files cache
├── docs/USER_GUIDE.md   ← Ръководство за ползване (с екранни снимки)
├── Dockerfile
├── docker-compose.yml
├── .env.example
├── composer.json
└── README.md
```

---

## Архитектурни принципи

- **Без framework** (изискване на курса)
- **Без vendor зависимости** (`composer.json` само за autoload)
- **PDO prepared statements навсякъде** — без raw query конкатенация
- **Controllers НЕ правят PDO** — само през Repository
- **Views НЕ правят PDO** — данните идват като примитиви/Models от Controller
- **Models са POPO/DTO** — само properties + конструктор, без логика
- **Generators са stateless** — данни → низ, без I/O вътре
- **CSRF на всяка форма** — централна проверка в `public/index.php`

---

## Sub-folder deployment (за изпит)

Лекторът ще пусне проекта в собствена XAMPP инсталация в **поддиректория**:

```
http://localhost/w26/<unique-name>/
```

Това работи без конфигурация — приложението автоматично детектира base path-а (виж `Manifesto\Core\Request::basePath()`). `.htaccess` файловете използват само relative rules, така че пренасочването работи независимо от папката.

---

## Default credentials (от `db/seed.sql`)

| Username | Password    | Role   |
|----------|-------------|--------|
| `admin`  | `admin123`  | admin  |
| `viewer` | `viewer123` | viewer |

> **⚠️ Production warning:** Тези credentials са за демонстрация. В продукция смени паролите след първи login.

---

## Документация

- **Setup и как да го пусна** — този файл (README.md)
- **Ръководство за ползване с екранни снимки** — [`docs/USER_GUIDE.md`](docs/USER_GUIDE.md)
- **AI prompt log** — [`docs/PROMPTS.md`](docs/PROMPTS.md)

---

## Автор

Курсов проект, FMI Sofia University, летен семестър 2025/2026.
