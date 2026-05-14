# Week 1 Foundation Implementation Plan — Manifesto

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Изградим foundation на Manifesto: project structure, БД schema + seed, HTTP layer (Router/Request/Response/Session/CSRF), auth flow (login/logout с admin/viewer роли), и базов UI layout. Краен резултат: можеш да login-неш на `http://localhost/manifesto/public/`, виждаш sidebar + topbar layout с placeholder dashboard, logout връща към login страницата.

**Architecture:** Vanilla PHP 8.1+ с PSR-4 autoload (Composer само за autoloader, без vendor зависимости). Front controller pattern с router, който dispatch-ва от `config/routes.php`. PDO singleton за MySQL connection. Sessions с CSRF protection. Templates в чист PHP в `src/Views/`. Layered architecture: Core → Controllers → Repositories → Models → Views.

**Tech Stack:** PHP 8.1+, MySQL/MariaDB (`utf8mb4_unicode_ci`), Apache 2.4+ с `mod_rewrite`, Composer (autoload only), XAMPP/WAMP за local dev. Frontend: vanilla HTML/CSS/JS — без библиотеки.

**Конвенции:**
- Всички пътеки относителни към project root: `C:\Users\Az\Documents\WEB\PROEKT\`.
- PHP namespace: `Manifesto\`.
- Commit съобщения на английски, conventional commits format (`feat:`, `chore:`, `test:`, etc.).
- Коментари в кода на английски (стандарт за open-source-style codebase).
- Документация и markdown на български.
- Bash commands в Unix syntax (forward slashes, `cp`/`rm`). На Windows GitBash или WSL се справят.

**Какво НЕ влиза в този plan (за следващи планове):**
- CRUD за всички 14 entities (Week 2).
- Tree view (Week 2).
- File generators (Week 3).
- Import/export, health check (Week 4).

---

## File map за Week 1

Файлове, които ще създадем (групирани по отговорност):

**Bootstrap & config:**
- `.gitignore`, `.env.example`, `.env`, `composer.json`, `README.md`
- `config/config.php`, `config/routes.php`
- `public/index.php`, `public/.htaccess`

**Database:**
- `db/schema.sql`, `db/seed.sql`

**Core HTTP/infrastructure:**
- `src/Core/EnvLoader.php` — зарежда `.env`
- `src/Core/Database.php` — PDO singleton
- `src/Core/Request.php` — wraps `$_GET`/`$_POST`/`$_SERVER`
- `src/Core/Response.php` — redirect, abort, JSON
- `src/Core/Router.php` — regex-based dispatch
- `src/Core/Session.php` — secure session helpers + flash
- `src/Core/Csrf.php` — token generate/verify
- `src/Core/ViewRenderer.php` — render PHP templates с layout
- `src/Core/Auth.php` — login/logout/role guard

**Auth domain:**
- `src/Models/AppUser.php`
- `src/Repositories/AppUserRepository.php`
- `src/Controllers/AuthController.php`
- `src/Controllers/DashboardController.php` (placeholder)

**Views:**
- `src/Views/layouts/auth.php`
- `src/Views/layouts/app.php`
- `src/Views/partials/sidebar.php`
- `src/Views/partials/topbar.php`
- `src/Views/partials/flash.php`
- `src/Views/auth/login.php`
- `src/Views/dashboard/index.php`
- `src/Views/errors/404.php`
- `src/Views/errors/403.php`

**Assets:**
- `public/assets/css/app.css`

**Tests (vanilla, no framework):**
- `tests/bootstrap.php`
- `tests/test_env_loader.php`
- `tests/test_database.php`
- `tests/test_router.php`
- `tests/test_csrf.php`
- `tests/test_auth.php`
- `tests/fixtures/sample.env`

---

## Task 1: Initialize project — git, folders, gitignore, README

**Files:**
- Create: `.gitignore`
- Create: `README.md`
- Create: фолдер структура (празни папки)

- [ ] **Step 1.1: Създай фолдер структура**

В Bash от project root (`C:\Users\Az\Documents\WEB\PROEKT`):

```bash
mkdir -p public/assets/css public/assets/js
mkdir -p src/Core src/Models src/Repositories src/Controllers src/Services src/Services/Generators
mkdir -p src/Views/layouts src/Views/partials src/Views/auth src/Views/dashboard src/Views/errors
mkdir -p config db storage/logs storage/generated tests tests/fixtures
```

Verify: `ls -la` показва `public/`, `src/`, `config/`, `db/`, `storage/`, `tests/`, `docs/`.

- [ ] **Step 1.2: Създай `.gitignore`**

Файл `.gitignore` в project root:

```gitignore
# Environment
.env
.env.*.local

# Composer
vendor/
composer.lock

# Storage
storage/logs/*.log
storage/generated/*
!storage/generated/.gitkeep

# IDE
.idea/
.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Backups
*.bak
*.backup
*~
```

- [ ] **Step 1.3: Създай `.gitkeep` файлове**

```bash
touch storage/logs/.gitkeep
touch storage/generated/.gitkeep
```

- [ ] **Step 1.4: Създай `README.md` (project root)**

```markdown
# Manifesto

> Declare your infrastructure. Generate your stack.

Vanilla PHP + MySQL dashboard for structured description of Docker-based infrastructure. Generates `docker-compose.yml`, `.env`, `Dockerfile`, `vhost`, `README`, and Emmet-style export from your declared model.

University project. See `docs/PROJECT_CONTEXT.md` for full context.

## Quick start

1. Install XAMPP (PHP 8.1+, MySQL 8.0+).
2. Clone into `C:\xampp\htdocs\manifesto`.
3. `composer dump-autoload`.
4. `cp .env.example .env` and adjust DB credentials.
5. Import `db/schema.sql` and `db/seed.sql` via phpMyAdmin into a database named `manifesto`.
6. Visit `http://localhost/manifesto/public/`.
7. Login: `admin` / `admin`.

Full setup details: [docs/SETUP_AND_DEPLOYMENT.md](docs/SETUP_AND_DEPLOYMENT.md).

## Documentation

| Document | Purpose |
|---|---|
| [PROJECT_CONTEXT.md](docs/PROJECT_CONTEXT.md) | What this project is, why it exists |
| [PRODUCT_REQUIREMENTS.md](docs/PRODUCT_REQUIREMENTS.md) | Full PRD |
| [DECISIONS.md](docs/DECISIONS.md) | Architectural Decision Records |
| [ARCHITECTURE.md](docs/ARCHITECTURE.md) | Code structure, layers, boundaries |
| [DATABASE_SCHEMA.md](docs/DATABASE_SCHEMA.md) | Full relational schema |
| [SETUP_AND_DEPLOYMENT.md](docs/SETUP_AND_DEPLOYMENT.md) | How to run on a fresh machine |
| [IMPORT_EXPORT_FORMAT.md](docs/IMPORT_EXPORT_FORMAT.md) | JSON format for export/import |
| [FUTURE_WORK.md](docs/FUTURE_WORK.md) | Ideas beyond MVP |
| [TODO.md](docs/TODO.md) | Live milestone tracker |
```

- [ ] **Step 1.5: Init git и първи commit**

```bash
git init
git add .gitignore README.md docs/ storage/logs/.gitkeep storage/generated/.gitkeep
git commit -m "chore: initial commit with docs and folder structure"
```

Verify: `git log --oneline` показва един commit.

---

## Task 2: Composer + PSR-4 autoload

**Files:**
- Create: `composer.json`

- [ ] **Step 2.1: Създай `composer.json`**

```json
{
  "name": "manifesto/dashboard",
  "description": "Docker Compose Generator & Infrastructure Manifest Editor",
  "type": "project",
  "license": "MIT",
  "require": {
    "php": ">=8.1",
    "ext-pdo": "*",
    "ext-pdo_mysql": "*",
    "ext-curl": "*",
    "ext-mbstring": "*",
    "ext-openssl": "*",
    "ext-zip": "*",
    "ext-json": "*"
  },
  "autoload": {
    "psr-4": {
      "Manifesto\\": "src/"
    }
  },
  "config": {
    "sort-packages": true
  }
}
```

- [ ] **Step 2.2: Генерирай autoloader**

```bash
composer dump-autoload
```

Expected output:
```
Generating autoload files
Generated autoload files
```

Verify: файлът `vendor/autoload.php` съществува; `vendor/` папка съдържа `composer/autoload_psr4.php`.

- [ ] **Step 2.3: Commit**

```bash
git add composer.json
git commit -m "chore: add composer.json with PSR-4 autoload (Manifesto namespace)"
```

---

## Task 3: Environment configuration — `.env.example` и `EnvLoader`

**Files:**
- Create: `.env.example`
- Create: `.env`
- Create: `src/Core/EnvLoader.php`
- Create: `tests/fixtures/sample.env`
- Create: `tests/bootstrap.php`
- Create: `tests/test_env_loader.php`

- [ ] **Step 3.1: Създай `.env.example`**

```env
# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=manifesto
DB_USER=root
DB_PASS=

# App
APP_NAME=Manifesto
APP_BASE_URL=http://localhost/manifesto/public
APP_ENV=development
SESSION_LIFETIME=3600
```

- [ ] **Step 3.2: Създай `.env` (local copy)**

```bash
cp .env.example .env
```

`.env` е gitignored, така че няма да се commit-не.

- [ ] **Step 3.3: Напиши `tests/bootstrap.php`**

Този файл се require-ва от всеки тест-скрипт, за да заредеи autoloader и да включи `assert()` директивата.

```php
<?php
declare(strict_types=1);

// Enable assert() и throw on failure
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 0);
assert_options(ASSERT_EXCEPTION, 1);

require __DIR__ . '/../vendor/autoload.php';

function ok(string $msg): void {
    echo "  \u{2713} {$msg}\n";
}

function fail(string $msg): void {
    fwrite(STDERR, "  \u{2717} {$msg}\n");
    exit(1);
}

function section(string $name): void {
    echo "\n=== {$name} ===\n";
}
```

- [ ] **Step 3.4: Напиши failing test за EnvLoader**

`tests/test_env_loader.php`:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Manifesto\Core\EnvLoader;

section('EnvLoader');

// Test 1: Зарежда KEY=value формат
$fixture = __DIR__ . '/fixtures/sample.env';
EnvLoader::load($fixture);

if (getenv('DB_HOST') !== '127.0.0.1') fail('DB_HOST not loaded');
ok('DB_HOST loaded as 127.0.0.1');

if ($_ENV['DB_NAME'] !== 'manifesto_test') fail('DB_NAME not in $_ENV');
ok('DB_NAME in $_ENV');

// Test 2: Игнорира comments и празни редове
if (getenv('COMMENT_TEST') !== false) fail('Comments should not load as vars');
ok('Comments and empty lines ignored');

// Test 3: Quoted values
if (getenv('QUOTED_VALUE') !== 'value with spaces') fail('Quoted values not parsed');
ok('Quoted values parsed correctly');

// Test 4: Missing файл хвърля exception
try {
    EnvLoader::load(__DIR__ . '/fixtures/nonexistent.env');
    fail('Should have thrown on missing file');
} catch (\RuntimeException $e) {
    ok('Throws RuntimeException on missing file');
}

echo "\nAll EnvLoader tests passed.\n";
```

- [ ] **Step 3.5: Създай fixture за теста**

`tests/fixtures/sample.env`:

```env
# This is a comment, should be ignored
DB_HOST=127.0.0.1
DB_NAME=manifesto_test

# Empty line above

QUOTED_VALUE="value with spaces"
```

- [ ] **Step 3.6: Пусни теста за да видиш че fail-ва**

```bash
php tests/test_env_loader.php
```

Expected: Грешка „Class Manifesto\Core\EnvLoader not found" — защото още не сме го написали.

- [ ] **Step 3.7: Имплементирай `EnvLoader`**

`src/Core/EnvLoader.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Core;

use RuntimeException;

final class EnvLoader
{
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Cannot read env file: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new RuntimeException("Failed reading env file: {$path}");
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));

            // Strip surrounding quotes (single или double)
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[-1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }

    public static function require(string $key): string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            throw new RuntimeException("Missing required env var: {$key}");
        }
        return $value;
    }
}
```

- [ ] **Step 3.8: Пусни теста — трябва да минe**

```bash
php tests/test_env_loader.php
```

Expected output:
```
=== EnvLoader ===
  ✓ DB_HOST loaded as 127.0.0.1
  ✓ DB_NAME in $_ENV
  ✓ Comments and empty lines ignored
  ✓ Quoted values parsed correctly
  ✓ Throws RuntimeException on missing file

All EnvLoader tests passed.
```

- [ ] **Step 3.9: Commit**

```bash
git add .env.example composer.json src/Core/EnvLoader.php tests/
git commit -m "feat(core): add EnvLoader with comment/quote/empty-line handling and tests"
```

---

## Task 4: Database schema (16 tables)

**Files:**
- Create: `db/schema.sql`

- [ ] **Step 4.1: Напиши `db/schema.sql`**

Файл `db/schema.sql`:

```sql
-- Manifesto — full schema
-- MySQL 8.0+ / MariaDB 10.5+ · utf8mb4_unicode_ci

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS health_check;
DROP TABLE IF EXISTS generated_file;
DROP TABLE IF EXISTS infrastructure_user;
DROP TABLE IF EXISTS infrastructure_role;
DROP TABLE IF EXISTS webapp;
DROP TABLE IF EXISTS service_network;
DROP TABLE IF EXISTS network;
DROP TABLE IF EXISTS volume;
DROP TABLE IF EXISTS env_var;
DROP TABLE IF EXISTS port_mapping;
DROP TABLE IF EXISTS service;
DROP TABLE IF EXISTS project;
DROP TABLE IF EXISTS docker_host;
DROP TABLE IF EXISTS virtual_machine;
DROP TABLE IF EXISTS hypervisor;
DROP TABLE IF EXISTS hardware_host;
DROP TABLE IF EXISTS app_user;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE app_user (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(64)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','viewer') NOT NULL DEFAULT 'viewer',
    display_name  VARCHAR(128) NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hardware_host (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(128) NOT NULL UNIQUE,
    mac_address       VARCHAR(17)  NULL,
    physical_location VARCHAR(255) NULL,
    mgmt_type         VARCHAR(64)  NULL,
    admin_email       VARCHAR(255) NULL,
    ip                VARCHAR(45)  NULL,
    web_url           VARCHAR(255) NULL,
    notes             TEXT         NULL,
    created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hardware_host_ip (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hypervisor (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hardware_host_id INT UNSIGNED NOT NULL,
    name             VARCHAR(128) NOT NULL,
    vendor           VARCHAR(64)  NULL,
    os               VARCHAR(128) NULL,
    admin_email      VARCHAR(255) NULL,
    ip               VARCHAR(45)  NULL,
    notes            TEXT         NULL,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_hypervisor (hardware_host_id, name),
    CONSTRAINT fk_hypervisor_hw FOREIGN KEY (hardware_host_id)
        REFERENCES hardware_host(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE virtual_machine (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hypervisor_id INT UNSIGNED NOT NULL,
    name          VARCHAR(128) NOT NULL,
    ip            VARCHAR(45)  NULL,
    port          INT UNSIGNED NULL,
    status        ENUM('running','stopped','unknown') NOT NULL DEFAULT 'unknown',
    notes         TEXT         NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_vm (hypervisor_id, name),
    CONSTRAINT fk_vm_hv FOREIGN KEY (hypervisor_id)
        REFERENCES hypervisor(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE docker_host (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vm_id            INT UNSIGNED NULL,
    hardware_host_id INT UNSIGNED NULL,
    name             VARCHAR(128) NOT NULL,
    docker_version   VARCHAR(32)  NULL,
    notes            TEXT         NULL,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dh_vm (vm_id),
    INDEX idx_dh_hw (hardware_host_id),
    CONSTRAINT fk_dh_vm FOREIGN KEY (vm_id)
        REFERENCES virtual_machine(id) ON DELETE CASCADE,
    CONSTRAINT fk_dh_hw FOREIGN KEY (hardware_host_id)
        REFERENCES hardware_host(id) ON DELETE CASCADE,
    CONSTRAINT chk_dh_xor CHECK (
        (vm_id IS NOT NULL AND hardware_host_id IS NULL)
        OR (vm_id IS NULL AND hardware_host_id IS NOT NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    docker_host_id INT UNSIGNED NOT NULL,
    name           VARCHAR(128) NOT NULL,
    slug           VARCHAR(64)  NOT NULL UNIQUE,
    description    TEXT         NULL,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_project_dh (docker_host_id),
    CONSTRAINT fk_project_dh FOREIGN KEY (docker_host_id)
        REFERENCES docker_host(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id     INT UNSIGNED NOT NULL,
    name           VARCHAR(128) NOT NULL,
    image          VARCHAR(255) NULL,
    build_context  VARCHAR(255) NULL,
    command        VARCHAR(512) NULL,
    depends_on     TEXT         NULL,
    restart_policy ENUM('no','always','on-failure','unless-stopped') NULL DEFAULT 'unless-stopped',
    notes          TEXT         NULL,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_service (project_id, name),
    CONSTRAINT fk_service_project FOREIGN KEY (project_id)
        REFERENCES project(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE port_mapping (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id     INT UNSIGNED NOT NULL,
    host_port      INT UNSIGNED NOT NULL,
    container_port INT UNSIGNED NOT NULL,
    protocol       ENUM('tcp','udp') NOT NULL DEFAULT 'tcp',
    INDEX idx_portmapping_service (service_id),
    CONSTRAINT fk_portmapping_service FOREIGN KEY (service_id)
        REFERENCES service(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE env_var (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id  INT UNSIGNED NOT NULL,
    key_name    VARCHAR(128) NOT NULL,
    value       TEXT         NULL,
    is_secret   TINYINT(1)   NOT NULL DEFAULT 0,
    description TEXT         NULL,
    UNIQUE KEY uk_envvar (service_id, key_name),
    CONSTRAINT fk_envvar_service FOREIGN KEY (service_id)
        REFERENCES service(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE volume (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id     INT UNSIGNED NOT NULL,
    host_path      VARCHAR(512) NOT NULL,
    container_path VARCHAR(512) NOT NULL,
    mode           ENUM('ro','rw') NOT NULL DEFAULT 'rw',
    INDEX idx_volume_service (service_id),
    CONSTRAINT fk_volume_service FOREIGN KEY (service_id)
        REFERENCES service(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE network (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    name       VARCHAR(128) NOT NULL,
    driver     VARCHAR(32)  NOT NULL DEFAULT 'bridge',
    UNIQUE KEY uk_network (project_id, name),
    CONSTRAINT fk_network_project FOREIGN KEY (project_id)
        REFERENCES project(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service_network (
    service_id INT UNSIGNED NOT NULL,
    network_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (service_id, network_id),
    CONSTRAINT fk_sn_service FOREIGN KEY (service_id)
        REFERENCES service(id) ON DELETE CASCADE,
    CONSTRAINT fk_sn_network FOREIGN KEY (network_id)
        REFERENCES network(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE webapp (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id          INT UNSIGNED NOT NULL,
    name                VARCHAR(128) NOT NULL,
    url                 VARCHAR(512) NULL,
    dns_name            VARCHAR(255) NULL,
    vhost_ip            VARCHAR(45)  NULL,
    vhost_path          VARCHAR(512) NULL,
    authors             TEXT         NULL,
    status              ENUM('up','down','unknown') NOT NULL DEFAULT 'unknown',
    last_status_change  TIMESTAMP    NULL,
    notes               TEXT         NULL,
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_webapp (service_id, name),
    INDEX idx_webapp_dns (dns_name),
    CONSTRAINT fk_webapp_service FOREIGN KEY (service_id)
        REFERENCES service(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE infrastructure_role (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    webapp_id   INT UNSIGNED NOT NULL,
    role_name   VARCHAR(128) NOT NULL,
    url_path    VARCHAR(255) NULL,
    description TEXT         NULL,
    UNIQUE KEY uk_infrarole (webapp_id, role_name),
    CONSTRAINT fk_infrarole_webapp FOREIGN KEY (webapp_id)
        REFERENCES webapp(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE infrastructure_user (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id       INT UNSIGNED NOT NULL,
    username      VARCHAR(128) NOT NULL,
    password_hint VARCHAR(255) NULL,
    permissions   TEXT         NULL,
    UNIQUE KEY uk_infrauser (role_id, username),
    CONSTRAINT fk_infrauser_role FOREIGN KEY (role_id)
        REFERENCES infrastructure_role(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE generated_file (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id           INT UNSIGNED NOT NULL,
    file_type            ENUM('docker-compose','env','dockerfile','vhost-nginx','vhost-apache','readme','emmet') NOT NULL,
    filename             VARCHAR(255) NOT NULL,
    content              LONGTEXT     NOT NULL,
    version_number       INT UNSIGNED NOT NULL,
    generated_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    generated_by_user_id INT UNSIGNED NULL,
    INDEX idx_genfile_project_type_ver (project_id, file_type, version_number),
    INDEX idx_genfile_generated_at (generated_at),
    CONSTRAINT fk_genfile_project FOREIGN KEY (project_id)
        REFERENCES project(id) ON DELETE CASCADE,
    CONSTRAINT fk_genfile_user FOREIGN KEY (generated_by_user_id)
        REFERENCES app_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE health_check (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    webapp_id     INT UNSIGNED NOT NULL,
    status_code   INT          NULL,
    latency_ms    INT          NULL,
    error_message TEXT         NULL,
    checked_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_health_webapp_time (webapp_id, checked_at),
    CONSTRAINT fk_health_webapp FOREIGN KEY (webapp_id)
        REFERENCES webapp(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

- [ ] **Step 4.2: Създай базата и я импортирай**

В phpMyAdmin или Bash:

```bash
mysql -u root -e "CREATE DATABASE manifesto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root manifesto < db/schema.sql
```

Verify:
```bash
mysql -u root manifesto -e "SHOW TABLES"
```

Expected output (16 таблици):
```
+-----------------------+
| Tables_in_manifesto   |
+-----------------------+
| app_user              |
| docker_host           |
| env_var               |
| generated_file        |
| hardware_host         |
| health_check          |
| hypervisor            |
| infrastructure_role   |
| infrastructure_user   |
| network               |
| port_mapping          |
| project               |
| service               |
| service_network       |
| virtual_machine       |
| volume                |
| webapp                |
+-----------------------+
```

(Има 17 ред-броя в `SHOW TABLES` ако броим заглавието — fact-check: 16 entity таблици.)

- [ ] **Step 4.3: Commit**

```bash
git add db/schema.sql
git commit -m "feat(db): add schema.sql with 16 tables, FK constraints, indexes"
```

---

## Task 5: Database seed

**Files:**
- Create: `db/seed.sql`
- Create: `tools/hash_password.php` (helper)

- [ ] **Step 5.1: Създай helper за password hashing**

Не можем да сложим plain `password_hash()` извикване в SQL. Затова — еднократен PHP скрипт:

`tools/hash_password.php`:

```php
<?php
declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "Usage: php tools/hash_password.php <password>\n");
    exit(1);
}

$hash = password_hash($argv[1], PASSWORD_BCRYPT, ['cost' => 12]);
echo $hash . "\n";
```

```bash
mkdir -p tools
```

- [ ] **Step 5.2: Генерирай hash-ове за seed users**

```bash
php tools/hash_password.php admin
php tools/hash_password.php viewer
```

Запиши си двете bcrypt низа (всеки започва с `$2y$12$...`). Те ще влязат в seed.sql.

(Защо два пъти — bcrypt е salt-ован, така че всеки път различен hash. И двата работят с дадените пароли.)

- [ ] **Step 5.3: Напиши `db/seed.sql`**

Замени `__ADMIN_HASH__` и `__VIEWER_HASH__` с реалните bcrypt низа от стъпка 5.2.

```sql
-- Manifesto seed: 2 dashboard users + 1 demo infrastructure hierarchy

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE health_check;
TRUNCATE TABLE generated_file;
TRUNCATE TABLE infrastructure_user;
TRUNCATE TABLE infrastructure_role;
TRUNCATE TABLE webapp;
TRUNCATE TABLE service_network;
TRUNCATE TABLE network;
TRUNCATE TABLE volume;
TRUNCATE TABLE env_var;
TRUNCATE TABLE port_mapping;
TRUNCATE TABLE service;
TRUNCATE TABLE project;
TRUNCATE TABLE docker_host;
TRUNCATE TABLE virtual_machine;
TRUNCATE TABLE hypervisor;
TRUNCATE TABLE hardware_host;
TRUNCATE TABLE app_user;

SET FOREIGN_KEY_CHECKS = 1;

-- Dashboard users (passwords: admin/admin, viewer/viewer)
INSERT INTO app_user (username, password_hash, role, display_name) VALUES
('admin',  '__ADMIN_HASH__',  'admin',  'Administrator'),
('viewer', '__VIEWER_HASH__', 'viewer', 'Viewer');

-- Demo infrastructure
INSERT INTO hardware_host (name, mac_address, physical_location, mgmt_type, admin_email, ip, notes) VALUES
('fmi-srv-01', 'AA:BB:CC:DD:EE:01', 'FMI Building, room 211', 'ilo', 'admin@fmi.uni-sofia.bg', '192.168.10.10', 'Demo bare-metal server');

INSERT INTO hypervisor (hardware_host_id, name, vendor, os, admin_email, ip) VALUES
(1, 'hyperv-main', 'HyperV', 'WindowsDatacenter2019R2', 'admin@fmi.uni-sofia.bg', '192.168.10.20');

INSERT INTO virtual_machine (hypervisor_id, name, ip, port, status) VALUES
(1, 'vm-prod-01', '192.168.10.30', 22, 'running');

INSERT INTO docker_host (vm_id, hardware_host_id, name, docker_version) VALUES
(1, NULL, 'docker-main', '24.0.5');

INSERT INTO project (docker_host_id, name, slug, description) VALUES
(1, 'MyShop Demo', 'myshop', 'Demo e-commerce stack — nginx + node + mysql');

INSERT INTO service (project_id, name, image, restart_policy) VALUES
(1, 'web', 'nginx:alpine',  'unless-stopped'),
(1, 'api', 'node:20-alpine', 'unless-stopped'),
(1, 'db',  'mysql:8.0',      'unless-stopped');

INSERT INTO port_mapping (service_id, host_port, container_port, protocol) VALUES
(1, 8080, 80, 'tcp');

INSERT INTO env_var (service_id, key_name, value, is_secret) VALUES
(1, 'NGINX_HOST',          'myshop.local', 0),
(2, 'DB_HOST',              'db',           0),
(2, 'DB_PASS',              'supersecret',  1),
(3, 'MYSQL_ROOT_PASSWORD',  'supersecret',  1),
(3, 'MYSQL_DATABASE',       'myshop',       0);

INSERT INTO network (project_id, name, driver) VALUES
(1, 'frontend', 'bridge'),
(1, 'backend',  'bridge');

INSERT INTO service_network (service_id, network_id) VALUES
(1, 1),  -- web → frontend
(2, 1),  -- api → frontend
(2, 2),  -- api → backend
(3, 2);  -- db → backend

INSERT INTO webapp (service_id, name, url, dns_name, vhost_ip, vhost_path, authors, status) VALUES
(1, 'main-site', 'http://myshop.local', 'myshop.local', '127.0.0.1', '/var/www/myshop', 'fn9999 <a@b.c>', 'unknown');

INSERT INTO infrastructure_role (webapp_id, role_name, url_path, description) VALUES
(1, 'admin',  '/admin',  'Full admin panel'),
(1, 'editor', '/editor', 'Content editor');

INSERT INTO infrastructure_user (role_id, username, password_hint, permissions) VALUES
(1, 'root',    'see env DB_PASS', 'all'),
(2, 'editor1', '',                 'edit,publish');
```

- [ ] **Step 5.4: Импортирай seed**

```bash
mysql -u root manifesto < db/seed.sql
```

Verify:
```bash
mysql -u root manifesto -e "SELECT id, username, role FROM app_user"
mysql -u root manifesto -e "SELECT name, slug FROM project"
```

Expected:
```
+----+----------+--------+
| id | username | role   |
+----+----------+--------+
|  1 | admin    | admin  |
|  2 | viewer   | viewer |
+----+----------+--------+
+-------------+--------+
| name        | slug   |
+-------------+--------+
| MyShop Demo | myshop |
+-------------+--------+
```

- [ ] **Step 5.5: Commit**

```bash
git add db/seed.sql tools/hash_password.php
git commit -m "feat(db): add seed.sql with 2 users (admin/viewer) and demo project"
```

---

## Task 6: PDO Database singleton

**Files:**
- Create: `src/Core/Database.php`
- Create: `tests/test_database.php`

- [ ] **Step 6.1: Напиши failing test**

`tests/test_database.php`:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Manifesto\Core\EnvLoader;
use Manifesto\Core\Database;

section('Database');

EnvLoader::load(__DIR__ . '/../.env');

$pdo = Database::getInstance();
if (!$pdo instanceof PDO) fail('getInstance should return PDO instance');
ok('getInstance returns PDO');

// Same instance on repeat call (singleton)
$pdo2 = Database::getInstance();
if ($pdo !== $pdo2) fail('Database is not singleton');
ok('Singleton: same instance returned');

// Charset is utf8mb4
$stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set_client'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!str_starts_with($row['Value'], 'utf8mb4')) fail('Charset is not utf8mb4');
ok('Connection uses utf8mb4');

// Можем да query-ваме app_user
$stmt = $pdo->query("SELECT COUNT(*) FROM app_user");
$count = (int) $stmt->fetchColumn();
if ($count < 2) fail('Should have at least 2 users from seed');
ok("Found {$count} users in app_user");

echo "\nAll Database tests passed.\n";
```

- [ ] **Step 6.2: Пусни — fail**

```bash
php tests/test_database.php
```

Expected: „Class Manifesto\Core\Database not found".

- [ ] **Step 6.3: Имплементирай `Database`**

`src/Core/Database.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = EnvLoader::require('DB_HOST');
        $port = EnvLoader::get('DB_PORT', '3306');
        $name = EnvLoader::require('DB_NAME');
        $user = EnvLoader::require('DB_USER');
        $pass = EnvLoader::get('DB_PASS', '') ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
```

- [ ] **Step 6.4: Пусни теста**

```bash
php tests/test_database.php
```

Expected:
```
=== Database ===
  ✓ getInstance returns PDO
  ✓ Singleton: same instance returned
  ✓ Connection uses utf8mb4
  ✓ Found 2 users in app_user

All Database tests passed.
```

- [ ] **Step 6.5: Commit**

```bash
git add src/Core/Database.php tests/test_database.php
git commit -m "feat(core): add PDO Database singleton with utf8mb4 and exception mode"
```

---

## Task 7: Request wrapper

**Files:**
- Create: `src/Core/Request.php`

- [ ] **Step 7.1: Имплементирай `Request`**

`src/Core/Request.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Core;

final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Strip script subpath ако сме под subdirectory (напр. /manifesto/public).
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        return $path === '' ? '/' : $path;
    }

    public function input(string $key, ?string $default = null): ?string
    {
        if (isset($_POST[$key])) {
            return is_string($_POST[$key]) ? trim($_POST[$key]) : $default;
        }
        if (isset($_GET[$key])) {
            return is_string($_GET[$key]) ? trim($_GET[$key]) : $default;
        }
        return $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
```

- [ ] **Step 7.2: Commit**

```bash
git add src/Core/Request.php
git commit -m "feat(core): add Request wrapper (method, path, input, helpers)"
```

(Без отделен test файл — Request се тества имплицитно като част от router интеграцията.)

---

## Task 8: Response helper

**Files:**
- Create: `src/Core/Response.php`

- [ ] **Step 8.1: Имплементирай `Response`**

`src/Core/Response.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Core;

final class Response
{
    public static function redirect(string $url, int $status = 302): void
    {
        header("Location: {$url}", true, $status);
        exit;
    }

    public static function abort(int $status, string $message = ''): void
    {
        http_response_code($status);

        $view = match ($status) {
            404 => 'errors/404',
            403 => 'errors/403',
            419 => 'errors/419',
            default => 'errors/500',
        };

        // Опит за рендер на error view ако ViewRenderer е достъпен.
        try {
            (new ViewRenderer())->render($view, ['message' => $message], 'auth');
        } catch (\Throwable) {
            echo "<h1>Error {$status}</h1><p>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</p>";
        }

        exit;
    }

    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
```

- [ ] **Step 8.2: Commit**

```bash
git add src/Core/Response.php
git commit -m "feat(core): add Response helper (redirect, abort, json)"
```

---

## Task 9: Router

**Files:**
- Create: `src/Core/Router.php`
- Create: `tests/test_router.php`

- [ ] **Step 9.1: Failing test**

`tests/test_router.php`:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Manifesto\Core\Router;

section('Router');

$routes = [
    ['GET',  '/',                 'HomeController',    'index'],
    ['GET',  '/projects/{id}',    'ProjectController', 'show'],
    ['POST', '/projects/{id}',    'ProjectController', 'update'],
    ['GET',  '/projects/{id}/services/{sid}', 'ServiceController', 'show'],
];

$router = new Router($routes);

// 1. Static route
$match = $router->match('GET', '/');
if ($match === null) fail('Should match GET /');
if ($match['controller'] !== 'HomeController') fail('Wrong controller');
ok('Static GET / matches');

// 2. Param route
$match = $router->match('GET', '/projects/42');
if ($match === null) fail('Should match /projects/42');
if (($match['params']['id'] ?? null) !== '42') fail('id param not extracted');
ok('Single param route matches and extracts id=42');

// 3. Method mismatch
$match = $router->match('DELETE', '/projects/42');
if ($match !== null) fail('DELETE should not match');
ok('Method mismatch returns null');

// 4. Multi-param route
$match = $router->match('GET', '/projects/42/services/7');
if ($match === null) fail('Should match nested');
if ($match['params']['id'] !== '42' || $match['params']['sid'] !== '7') fail('Nested params wrong');
ok('Multi-param route extracts id=42, sid=7');

// 5. Trailing slash tolerance
$match = $router->match('GET', '/projects/42/');
if ($match === null) fail('Trailing slash should match');
ok('Trailing slash tolerated');

// 6. No match
$match = $router->match('GET', '/nonexistent');
if ($match !== null) fail('Should not match unknown');
ok('Unknown path returns null');

echo "\nAll Router tests passed.\n";
```

- [ ] **Step 9.2: Пусни — fail**

```bash
php tests/test_router.php
```

- [ ] **Step 9.3: Имплементирай `Router`**

`src/Core/Router.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Core;

final class Router
{
    /**
     * @param array<int, array{0:string,1:string,2:string,3:string}> $routes
     *        [method, pattern, controllerClass, methodName]
     */
    public function __construct(private array $routes) {}

    /**
     * @return array{controller:string, method:string, params:array<string,string>}|null
     */
    public function match(string $httpMethod, string $path): ?array
    {
        $path = rtrim($path, '/') ?: '/';
        $httpMethod = strtoupper($httpMethod);

        foreach ($this->routes as [$method, $pattern, $controller, $action]) {
            if (strtoupper($method) !== $httpMethod) {
                continue;
            }

            $regex = $this->patternToRegex($pattern);
            if (preg_match($regex, $path, $matches) === 1) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                return [
                    'controller' => $controller,
                    'method'     => $action,
                    'params'     => $params,
                ];
            }
        }

        return null;
    }

    private function patternToRegex(string $pattern): string
    {
        $pattern = rtrim($pattern, '/') ?: '/';
        $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }
}
```

- [ ] **Step 9.4: Пусни теста — pass**

```bash
php tests/test_router.php
```

Expected: всичките 6 теста минават.

- [ ] **Step 9.5: Commit**

```bash
git add src/Core/Router.php tests/test_router.php
git commit -m "feat(core): add Router with regex-based dispatch and named params"
```

---

## Task 10: Session wrapper

**Files:**
- Create: `src/Core/Session.php`

- [ ] **Step 10.1: Имплементирай `Session`**

`src/Core/Session.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = (int) (EnvLoader::get('SESSION_LIFETIME', '3600') ?? 3600);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => false,                // в production: true
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('MANIFESTO_SESSION');
        session_start();

        // Idle timeout
        if (isset($_SESSION['_last_activity'])) {
            if (time() - $_SESSION['_last_activity'] > $lifetime) {
                self::destroy();
                session_start();
            }
        }
        $_SESSION['_last_activity'] = time();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    // Flash messages
    public static function flash(string $type, string $message): void
    {
        $flashes = $_SESSION['_flash'] ?? [];
        $flashes[] = ['type' => $type, 'message' => $message];
        $_SESSION['_flash'] = $flashes;
    }

    /** @return array<int, array{type:string,message:string}> */
    public static function pullFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }
}
```

- [ ] **Step 10.2: Commit**

```bash
git add src/Core/Session.php
git commit -m "feat(core): add Session wrapper with idle timeout, regenerate, flash"
```

---

## Task 11: CSRF protection

**Files:**
- Create: `src/Core/Csrf.php`
- Create: `tests/test_csrf.php`

- [ ] **Step 11.1: Failing test**

`tests/test_csrf.php`:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Manifesto\Core\Csrf;

section('CSRF');

// Симулираме сесия за изолация (не start-ваме истинска)
$_SESSION = [];

$token1 = Csrf::token();
if (strlen($token1) !== 64) fail('Token should be 64 chars (32 bytes hex)');
ok('Token is 64 hex chars');

$token2 = Csrf::token();
if ($token1 !== $token2) fail('Repeated token() should return same token within session');
ok('Token is stable within session');

// Verify
if (!Csrf::verify($token1)) fail('Should verify own token');
ok('Verify own token');

if (Csrf::verify('wrong-token')) fail('Should reject wrong token');
ok('Reject wrong token');

if (Csrf::verify('')) fail('Should reject empty');
ok('Reject empty');

echo "\nAll CSRF tests passed.\n";
```

- [ ] **Step 11.2: Пусни — fail**

```bash
php tests/test_csrf.php
```

- [ ] **Step 11.3: Имплементирай `Csrf`**

`src/Core/Csrf.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Core;

final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (!isset($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    public static function verify(?string $submitted): bool
    {
        $expected = $_SESSION[self::KEY] ?? null;
        if ($expected === null || $submitted === null || $submitted === '') {
            return false;
        }
        return hash_equals($expected, $submitted);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
```

- [ ] **Step 11.4: Pass test**

```bash
php tests/test_csrf.php
```

- [ ] **Step 11.5: Commit**

```bash
git add src/Core/Csrf.php tests/test_csrf.php
git commit -m "feat(core): add CSRF token generation and verification"
```

---

## Task 12: ViewRenderer

**Files:**
- Create: `src/Core/ViewRenderer.php`

- [ ] **Step 12.1: Имплементирай**

`src/Core/ViewRenderer.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Core;

use RuntimeException;

final class ViewRenderer
{
    private string $viewsPath;

    public function __construct(?string $viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?? __DIR__ . '/../Views';
    }

    /**
     * @param array<string,mixed> $data
     */
    public function render(string $view, array $data = [], ?string $layout = null): string
    {
        $content = $this->renderPartial($view, $data);

        if ($layout !== null) {
            $data['content'] = $content;
            $content = $this->renderPartial("layouts/{$layout}", $data);
        }

        echo $content;
        return $content;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function renderPartial(string $view, array $data = []): string
    {
        $path = $this->viewsPath . '/' . $view . '.php';
        if (!is_file($path)) {
            throw new RuntimeException("View not found: {$view} (looked in {$path})");
        }

        // Extract data като local vars в scope-а на template-а.
        extract($data, EXTR_SKIP);

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean() ?: '';
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
```

- [ ] **Step 12.2: Commit**

```bash
git add src/Core/ViewRenderer.php
git commit -m "feat(core): add ViewRenderer with layout composition and html escape helper"
```

---

## Task 13: AppUser model + repository

**Files:**
- Create: `src/Models/AppUser.php`
- Create: `src/Repositories/AppUserRepository.php`

- [ ] **Step 13.1: Model**

`src/Models/AppUser.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Models;

final class AppUser
{
    public function __construct(
        public int $id,
        public string $username,
        public string $passwordHash,
        public string $role,
        public ?string $displayName,
        public string $createdAt,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id:           (int) $row['id'],
            username:     (string) $row['username'],
            passwordHash: (string) $row['password_hash'],
            role:         (string) $row['role'],
            displayName:  $row['display_name'] !== null ? (string) $row['display_name'] : null,
            createdAt:    (string) $row['created_at'],
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
```

- [ ] **Step 13.2: Repository**

`src/Repositories/AppUserRepository.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Repositories;

use Manifesto\Core\Database;
use Manifesto\Models\AppUser;
use PDO;

final class AppUserRepository
{
    public function __construct(private ?PDO $pdo = null)
    {
        $this->pdo ??= Database::getInstance();
    }

    public function findByUsername(string $username): ?AppUser
    {
        $stmt = $this->pdo->prepare("SELECT * FROM app_user WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch();
        return $row ? AppUser::fromRow($row) : null;
    }

    public function findById(int $id): ?AppUser
    {
        $stmt = $this->pdo->prepare("SELECT * FROM app_user WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? AppUser::fromRow($row) : null;
    }
}
```

- [ ] **Step 13.3: Commit**

```bash
git add src/Models/AppUser.php src/Repositories/AppUserRepository.php
git commit -m "feat(auth): add AppUser model and repository"
```

---

## Task 14: Auth core

**Files:**
- Create: `src/Core/Auth.php`
- Create: `tests/test_auth.php`

- [ ] **Step 14.1: Failing test**

`tests/test_auth.php`:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Manifesto\Core\EnvLoader;
use Manifesto\Core\Auth;
use Manifesto\Core\Session;

section('Auth');

EnvLoader::load(__DIR__ . '/../.env');
$_SESSION = [];

// Wrong password
$user = Auth::attempt('admin', 'wrong-password');
if ($user !== null) fail('Wrong password should not authenticate');
ok('Wrong password rejected');

// Wrong username
$user = Auth::attempt('nosuchuser', 'admin');
if ($user !== null) fail('Wrong username should not authenticate');
ok('Wrong username rejected');

// Correct
$user = Auth::attempt('admin', 'admin');
if ($user === null) fail('admin/admin should authenticate');
if ($user->role !== 'admin') fail('Role should be admin');
ok('admin/admin authenticates as admin role');

// След attempt, session трябва да съдържа user_id
if (Session::get('user_id') !== $user->id) fail('Session should contain user_id');
ok('Session contains user_id after attempt');

// currentUser()
$current = Auth::user();
if ($current === null) fail('user() should return logged in user');
if ($current->username !== 'admin') fail('user() returned wrong username');
ok('user() returns logged in admin');

// logout
Auth::logout();
if (Auth::user() !== null) fail('user() should be null after logout');
ok('logout clears user');

echo "\nAll Auth tests passed.\n";
```

- [ ] **Step 14.2: Имплементирай `Auth`**

`src/Core/Auth.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Core;

use Manifesto\Models\AppUser;
use Manifesto\Repositories\AppUserRepository;

final class Auth
{
    private static ?AppUser $cachedUser = null;

    public static function attempt(string $username, string $password): ?AppUser
    {
        $repo = new AppUserRepository();
        $user = $repo->findByUsername($username);

        if ($user === null) {
            return null;
        }
        if (!password_verify($password, $user->passwordHash)) {
            return null;
        }

        Session::regenerate();
        Session::set('user_id', $user->id);
        Session::set('user_role', $user->role);
        self::$cachedUser = $user;
        return $user;
    }

    public static function user(): ?AppUser
    {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $id = Session::get('user_id');
        if (!is_int($id)) {
            return null;
        }

        $repo = new AppUserRepository();
        $user = $repo->findById($id);
        self::$cachedUser = $user;
        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user?->role;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Response::redirect('/login');
        }
    }

    public static function requireRole(string $role): void
    {
        if (self::role() !== $role) {
            Response::abort(403, "Forbidden: requires {$role} role");
        }
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::forget('user_role');
        Session::regenerate();
        self::$cachedUser = null;
    }
}
```

- [ ] **Step 14.3: Pass test**

```bash
php tests/test_auth.php
```

- [ ] **Step 14.4: Commit**

```bash
git add src/Core/Auth.php tests/test_auth.php
git commit -m "feat(auth): add Auth with attempt/user/logout/role guards"
```

---

## Task 15: AuthController + login view

**Files:**
- Create: `src/Controllers/AuthController.php`
- Create: `src/Views/auth/login.php`
- Create: `src/Views/layouts/auth.php`

- [ ] **Step 15.1: AuthController**

`src/Controllers/AuthController.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Controllers;

use Manifesto\Core\Auth;
use Manifesto\Core\Csrf;
use Manifesto\Core\Request;
use Manifesto\Core\Response;
use Manifesto\Core\Session;
use Manifesto\Core\ViewRenderer;

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Response::redirect('/');
        }

        (new ViewRenderer())->render('auth/login', [
            'csrfToken' => Csrf::token(),
            'flashes'   => Session::pullFlashes(),
        ], 'auth');
    }

    public function login(Request $request): void
    {
        if (!Csrf::verify($request->input('_csrf_token'))) {
            Response::abort(419, 'CSRF token mismatch');
        }

        $username = $request->input('username', '') ?? '';
        $password = $request->input('password', '') ?? '';

        if ($username === '' || $password === '') {
            Session::flash('error', 'Моля попълни и двете полета.');
            Response::redirect('/login');
        }

        $user = Auth::attempt($username, $password);

        if ($user === null) {
            Session::flash('error', 'Невалидно потребителско име или парола.');
            Response::redirect('/login');
        }

        Session::flash('success', 'Здравей, ' . ($user->displayName ?? $user->username) . '!');
        Response::redirect('/');
    }

    public function logout(Request $request): void
    {
        if (!Csrf::verify($request->input('_csrf_token'))) {
            Response::abort(419, 'CSRF token mismatch');
        }
        Auth::logout();
        Session::flash('success', 'Излязохте успешно.');
        Response::redirect('/login');
    }
}
```

- [ ] **Step 15.2: Auth layout**

`src/Views/layouts/auth.php`:

```php
<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manifesto — Sign in</title>
    <link rel="stylesheet" href="/manifesto/public/assets/css/app.css">
</head>
<body class="auth-body">
    <main class="auth-container">
        <h1 class="brand">Manifesto</h1>
        <p class="tagline">Declare your infrastructure. Generate your stack.</p>
        <?= $content ?>
    </main>
</body>
</html>
```

- [ ] **Step 15.3: Login view**

`src/Views/auth/login.php`:

```php
<?php
/**
 * @var string $csrfToken
 * @var array<int, array{type:string,message:string}> $flashes
 */
use Manifesto\Core\ViewRenderer;
?>
<form method="post" action="/login" class="auth-form" novalidate>
    <input type="hidden" name="_csrf_token" value="<?= ViewRenderer::e($csrfToken) ?>">

    <?php foreach ($flashes as $flash): ?>
        <div class="flash flash-<?= ViewRenderer::e($flash['type']) ?>">
            <?= ViewRenderer::e($flash['message']) ?>
        </div>
    <?php endforeach; ?>

    <label class="field">
        <span>Потребителско име</span>
        <input type="text" name="username" autocomplete="username" required autofocus>
    </label>

    <label class="field">
        <span>Парола</span>
        <input type="password" name="password" autocomplete="current-password" required>
    </label>

    <button type="submit" class="btn btn-primary">Влизане</button>

    <p class="hint">Demo: <code>admin/admin</code> или <code>viewer/viewer</code></p>
</form>
```

- [ ] **Step 15.4: Commit**

```bash
git add src/Controllers/AuthController.php src/Views/layouts/auth.php src/Views/auth/login.php
git commit -m "feat(auth): add AuthController with login/logout and login view"
```

---

## Task 16: Routes config + front controller bootstrap

**Files:**
- Create: `config/config.php`
- Create: `config/routes.php`
- Create: `public/index.php`
- Create: `public/.htaccess`
- Create: `src/Views/errors/404.php`
- Create: `src/Views/errors/403.php`
- Create: `src/Views/errors/500.php`
- Create: `src/Views/errors/419.php`

- [ ] **Step 16.1: `config/config.php`**

`config/config.php`:

```php
<?php
declare(strict_types=1);

use Manifesto\Core\EnvLoader;

EnvLoader::load(__DIR__ . '/../.env');

// Error reporting based on env
$env = EnvLoader::get('APP_ENV', 'production');
if ($env === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// Log file
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

// Default timezone
date_default_timezone_set('Europe/Sofia');
```

- [ ] **Step 16.2: `config/routes.php`**

```php
<?php
declare(strict_types=1);

use Manifesto\Controllers\AuthController;
use Manifesto\Controllers\DashboardController;

return [
    // Auth
    ['GET',  '/login',  AuthController::class,      'showLogin'],
    ['POST', '/login',  AuthController::class,      'login'],
    ['POST', '/logout', AuthController::class,      'logout'],

    // Dashboard
    ['GET',  '/',       DashboardController::class, 'index'],
];
```

- [ ] **Step 16.3: `public/index.php`**

```php
<?php
declare(strict_types=1);

use Manifesto\Core\Auth;
use Manifesto\Core\Request;
use Manifesto\Core\Response;
use Manifesto\Core\Router;
use Manifesto\Core\Session;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

Session::start();

$request = new Request();
$routes  = require __DIR__ . '/../config/routes.php';
$router  = new Router($routes);

$match = $router->match($request->method(), $request->path());

if ($match === null) {
    Response::abort(404, 'Page not found');
}

// Auth guard: всичко без /login изисква login.
$publicPaths = ['/login'];
if (!in_array($request->path(), $publicPaths, true)) {
    Auth::requireLogin();
}

$controllerClass = $match['controller'];
$method          = $match['method'];
$params          = $match['params'];

$controller = new $controllerClass();

// Inject Request ако method го очаква като първи параметър.
$reflection = new ReflectionMethod($controller, $method);
$args = [];
foreach ($reflection->getParameters() as $param) {
    $type = $param->getType();
    if ($type instanceof ReflectionNamedType && $type->getName() === Request::class) {
        $args[] = $request;
    } elseif (isset($params[$param->getName()])) {
        $args[] = $params[$param->getName()];
    } elseif ($param->isDefaultValueAvailable()) {
        $args[] = $param->getDefaultValue();
    }
}

$controller->{$method}(...$args);
```

- [ ] **Step 16.4: `public/.htaccess`**

```apache
RewriteEngine On

# Pass-through for existing files и folders
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Everything else → index.php
RewriteRule ^ index.php [QSA,L]

# Security: don't expose .env, .git, etc.
<FilesMatch "^\.">
    Require all denied
</FilesMatch>
```

- [ ] **Step 16.5: Error views**

`src/Views/errors/404.php`:
```php
<?php use Manifesto\Core\ViewRenderer; ?>
<div class="error-page">
    <h1>404</h1>
    <p>Страницата не съществува.</p>
    <p class="error-detail"><?= ViewRenderer::e($message ?? '') ?></p>
    <a href="/">← Назад към начало</a>
</div>
```

`src/Views/errors/403.php`:
```php
<?php use Manifesto\Core\ViewRenderer; ?>
<div class="error-page">
    <h1>403</h1>
    <p>Нямаш достъп до тази страница.</p>
    <p class="error-detail"><?= ViewRenderer::e($message ?? '') ?></p>
    <a href="/">← Назад към начало</a>
</div>
```

`src/Views/errors/419.php`:
```php
<?php use Manifesto\Core\ViewRenderer; ?>
<div class="error-page">
    <h1>419</h1>
    <p>Сесията изтече или невалиден CSRF token.</p>
    <p class="error-detail"><?= ViewRenderer::e($message ?? '') ?></p>
    <a href="/login">Обнови страницата и опитай отново</a>
</div>
```

`src/Views/errors/500.php`:
```php
<?php use Manifesto\Core\ViewRenderer; ?>
<div class="error-page">
    <h1>500</h1>
    <p>Нещо се обърка. Опитай отново след малко.</p>
    <p class="error-detail"><?= ViewRenderer::e($message ?? '') ?></p>
    <a href="/">← Назад към начало</a>
</div>
```

- [ ] **Step 16.6: Commit**

```bash
git add config/ public/index.php public/.htaccess src/Views/errors/
git commit -m "feat(core): add front controller, routes, .htaccess, error views"
```

---

## Task 17: Dashboard placeholder + app layout + CSS

**Files:**
- Create: `src/Controllers/DashboardController.php`
- Create: `src/Views/layouts/app.php`
- Create: `src/Views/partials/sidebar.php`
- Create: `src/Views/partials/topbar.php`
- Create: `src/Views/partials/flash.php`
- Create: `src/Views/dashboard/index.php`
- Create: `public/assets/css/app.css`

- [ ] **Step 17.1: DashboardController**

`src/Controllers/DashboardController.php`:

```php
<?php
declare(strict_types=1);

namespace Manifesto\Controllers;

use Manifesto\Core\Auth;
use Manifesto\Core\Csrf;
use Manifesto\Core\Session;
use Manifesto\Core\ViewRenderer;

final class DashboardController
{
    public function index(): void
    {
        $user = Auth::user();

        (new ViewRenderer())->render('dashboard/index', [
            'user'      => $user,
            'csrfToken' => Csrf::token(),
            'flashes'   => Session::pullFlashes(),
        ], 'app');
    }
}
```

- [ ] **Step 17.2: App layout**

`src/Views/layouts/app.php`:

```php
<?php
/**
 * @var string $content
 * @var \Manifesto\Models\AppUser|null $user
 * @var string $csrfToken
 * @var array $flashes
 */
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manifesto</title>
    <link rel="stylesheet" href="/manifesto/public/assets/css/app.css">
</head>
<body class="app-body">
    <aside class="sidebar">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    </aside>
    <header class="topbar">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>
    </header>
    <main class="content">
        <?php include __DIR__ . '/../partials/flash.php'; ?>
        <?= $content ?>
    </main>
</body>
</html>
```

- [ ] **Step 17.3: Sidebar partial**

`src/Views/partials/sidebar.php`:

```php
<div class="sidebar-header">
    <a href="/" class="brand-link">Manifesto</a>
</div>
<nav class="sidebar-nav">
    <ul>
        <li><a href="/">Начало</a></li>
        <li class="muted">Hardware <span class="badge">soon</span></li>
        <li class="muted">Hypervisors <span class="badge">soon</span></li>
        <li class="muted">Virtual Machines <span class="badge">soon</span></li>
        <li class="muted">Docker Hosts <span class="badge">soon</span></li>
        <li class="muted">Projects <span class="badge">soon</span></li>
    </ul>
</nav>
```

- [ ] **Step 17.4: Topbar partial**

`src/Views/partials/topbar.php`:

```php
<?php
/**
 * @var \Manifesto\Models\AppUser|null $user
 * @var string $csrfToken
 */
use Manifesto\Core\ViewRenderer;
?>
<div class="topbar-left"></div>
<div class="topbar-right">
    <?php if ($user !== null): ?>
        <span class="user-info">
            <?= ViewRenderer::e($user->displayName ?? $user->username) ?>
            <span class="role-badge role-<?= ViewRenderer::e($user->role) ?>"><?= ViewRenderer::e($user->role) ?></span>
        </span>
        <form method="post" action="/logout" class="inline-form">
            <input type="hidden" name="_csrf_token" value="<?= ViewRenderer::e($csrfToken) ?>">
            <button type="submit" class="btn btn-ghost">Изход</button>
        </form>
    <?php endif; ?>
</div>
```

- [ ] **Step 17.5: Flash partial**

`src/Views/partials/flash.php`:

```php
<?php
/**
 * @var array<int, array{type:string,message:string}> $flashes
 */
use Manifesto\Core\ViewRenderer;
?>
<?php if (!empty($flashes)): ?>
    <div class="flash-stack">
        <?php foreach ($flashes as $flash): ?>
            <div class="flash flash-<?= ViewRenderer::e($flash['type']) ?>">
                <?= ViewRenderer::e($flash['message']) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

- [ ] **Step 17.6: Dashboard view**

`src/Views/dashboard/index.php`:

```php
<?php
/**
 * @var \Manifesto\Models\AppUser $user
 */
use Manifesto\Core\ViewRenderer;
?>
<section class="dashboard-welcome">
    <h1>Здравей, <?= ViewRenderer::e($user->displayName ?? $user->username) ?>!</h1>
    <p>Manifesto е готов за работа. Week 1 foundation е завършен.</p>

    <article class="card">
        <h2>Какво следва</h2>
        <ul>
            <li>Week 2: CRUD за всички 10 entities.</li>
            <li>Week 3: Генератори на конфигурации.</li>
            <li>Week 4: Import/export, health check, polish.</li>
        </ul>
    </article>

    <article class="card">
        <h2>Текуща роля</h2>
        <p>Влязъл си като <strong><?= ViewRenderer::e($user->role) ?></strong>.</p>
        <?php if ($user->isAdmin()): ?>
            <p>Имаш пълен достъп — CRUD на всичко.</p>
        <?php else: ?>
            <p>Имаш read-only достъп.</p>
        <?php endif; ?>
    </article>
</section>
```

- [ ] **Step 17.7: CSS**

`public/assets/css/app.css`:

```css
/* === Reset === */
*, *::before, *::after { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #1f2933;
    background: #f7f9fb;
    line-height: 1.5;
}
a { color: #2c5282; text-decoration: none; }
a:hover { text-decoration: underline; }
code {
    font-family: ui-monospace, "SF Mono", Consolas, monospace;
    background: #edf2f7;
    padding: 0.1em 0.3em;
    border-radius: 3px;
    font-size: 0.9em;
}

/* === Auth layout === */
.auth-body {
    min-height: 100vh;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
}
.auth-container {
    background: white;
    padding: 2.5rem 2.25rem;
    border-radius: 8px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    width: 100%;
    max-width: 400px;
}
.brand {
    font-size: 1.75rem;
    margin: 0 0 0.25rem;
    color: #1a365d;
    letter-spacing: -0.02em;
}
.tagline {
    margin: 0 0 1.5rem;
    color: #718096;
    font-size: 0.9rem;
}
.auth-form { display: grid; gap: 1rem; }
.field { display: grid; gap: 0.35rem; }
.field span {
    font-size: 0.85rem;
    color: #4a5568;
    font-weight: 500;
}
.field input {
    padding: 0.55rem 0.75rem;
    border: 1px solid #cbd5e0;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 120ms ease;
}
.field input:focus {
    outline: 2px solid #2c5282;
    outline-offset: -2px;
    border-color: #2c5282;
}
.btn {
    padding: 0.6rem 1rem;
    border-radius: 6px;
    border: none;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 120ms ease;
}
.btn-primary {
    background: #2c5282;
    color: white;
}
.btn-primary:hover { background: #2a4d7a; }
.btn-ghost {
    background: transparent;
    color: #4a5568;
    border: 1px solid #cbd5e0;
}
.btn-ghost:hover { background: #edf2f7; }
.hint {
    margin: 0.5rem 0 0;
    color: #718096;
    font-size: 0.85rem;
    text-align: center;
}

/* === Flash === */
.flash, .flash-stack > .flash {
    padding: 0.65rem 1rem;
    border-radius: 6px;
    margin-bottom: 0.6rem;
    font-size: 0.9rem;
    border: 1px solid transparent;
}
.flash-success { background: #f0fff4; border-color: #9ae6b4; color: #22543d; }
.flash-error   { background: #fff5f5; border-color: #fc8181; color: #742a2a; }

/* === App layout === */
.app-body {
    display: grid;
    grid-template-columns: 260px 1fr;
    grid-template-rows: 56px 1fr;
    grid-template-areas:
        "sidebar topbar"
        "sidebar content";
    min-height: 100vh;
}
.sidebar {
    grid-area: sidebar;
    background: #1a365d;
    color: #cbd5e0;
    padding: 1rem;
}
.sidebar-header { padding: 0.5rem 0 1.5rem; }
.brand-link {
    color: white;
    font-size: 1.4rem;
    font-weight: 600;
    letter-spacing: -0.02em;
}
.brand-link:hover { text-decoration: none; opacity: 0.85; }
.sidebar-nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 0.25rem;
}
.sidebar-nav li {
    padding: 0.45rem 0.75rem;
    border-radius: 4px;
}
.sidebar-nav li:hover { background: rgba(255,255,255,0.05); }
.sidebar-nav li a {
    color: #e2e8f0;
    display: block;
}
.sidebar-nav li.muted { color: #718096; cursor: not-allowed; }
.badge {
    font-size: 0.7rem;
    background: #4a5568;
    padding: 0.1em 0.4em;
    border-radius: 3px;
    margin-left: 0.5em;
    text-transform: uppercase;
}
.topbar {
    grid-area: topbar;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.25rem;
}
.topbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.user-info {
    color: #2d3748;
    font-size: 0.9rem;
}
.role-badge {
    margin-left: 0.5em;
    font-size: 0.7rem;
    padding: 0.1em 0.45em;
    border-radius: 3px;
    text-transform: uppercase;
}
.role-admin  { background: #fed7d7; color: #742a2a; }
.role-viewer { background: #e2e8f0; color: #4a5568; }
.inline-form { display: inline; }

.content {
    grid-area: content;
    padding: 1.5rem 2rem;
    overflow-y: auto;
}

/* === Cards === */
.card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem 1.5rem;
    margin: 1rem 0;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.card h2 {
    margin: 0 0 0.75rem;
    font-size: 1.1rem;
    color: #2d3748;
}
.card ul { margin: 0; padding-left: 1.25rem; }

.dashboard-welcome h1 {
    font-size: 1.6rem;
    margin: 0 0 0.5rem;
    color: #1a365d;
}

/* === Error pages === */
.error-page {
    text-align: center;
    padding: 4rem 1rem;
}
.error-page h1 {
    font-size: 4rem;
    margin: 0;
    color: #cbd5e0;
}
.error-detail {
    font-size: 0.85rem;
    color: #a0aec0;
    font-family: ui-monospace, monospace;
}
```

- [ ] **Step 17.8: Commit**

```bash
git add src/Controllers/DashboardController.php src/Views/layouts/app.php src/Views/partials/ src/Views/dashboard/ public/assets/css/app.css
git commit -m "feat(ui): add app layout, dashboard placeholder, partials, base CSS"
```

---

## Task 18: End-to-end manual smoke test

- [ ] **Step 18.1: Старта на XAMPP**

В XAMPP Control Panel: Start Apache + Start MySQL.

- [ ] **Step 18.2: Отвори login страницата**

В browser: `http://localhost/manifesto/public/`

Очаквано поведение: redirect към `/login`, виждаш Manifesto branding + form за вход.

- [ ] **Step 18.3: Тест на грешна парола**

Въведи: `admin` / `wrongpass` → Submit.

Очаквано: redirect обратно към `/login` с червен flash „Невалидно потребителско име или парола."

- [ ] **Step 18.4: Тест на празни полета**

Submit с празни полета.

Очаквано: redirect към `/login` с flash „Моля попълни и двете полета."

- [ ] **Step 18.5: Успешен login като admin**

`admin` / `admin` → Submit.

Очаквано:
- Redirect към `/`
- Виждаш sidebar (тъмносин) с brand „Manifesto" + nav списък с placeholder елементи
- Виждаш topbar с username + червен „admin" badge + бутон „Изход"
- Виждаш dashboard карти със зелен flash „Здравей, Administrator!"
- Текстът: „Имаш пълен достъп — CRUD на всичко."

- [ ] **Step 18.6: Logout**

Кликни „Изход".

Очаквано: redirect към `/login` със зелен flash „Излязохте успешно.".

- [ ] **Step 18.7: Login като viewer**

`viewer` / `viewer` → Submit.

Очаквано:
- Topbar показва сив „viewer" badge.
- Dashboard съобщава: „Имаш read-only достъп."

- [ ] **Step 18.8: Auth guard test**

Logout. След това: отвори `http://localhost/manifesto/public/` директно.

Очаквано: redirect към `/login` (защото не си логнат).

- [ ] **Step 18.9: 404 test**

Отвори: `http://localhost/manifesto/public/nonexistent` (без login).

Очаквано: redirect към `/login` (защото и /nonexistent изисква login → но първо guard-ът сработва преди 404 dispatch).

След login като admin → отвори `/nonexistent` → виждаш 404 страница.

- [ ] **Step 18.10: CSRF guard test**

Inspect login формата → копирай action URL → опитай POST без `_csrf_token`:

```bash
curl -X POST -d "username=admin&password=admin" http://localhost/manifesto/public/login
```

Очаквано: HTML с 419 страница „Сесията изтече или невалиден CSRF token."

- [ ] **Step 18.11: Session timeout test (optional)**

Login. Изчакай 60+ минути (или временно постави `SESSION_LIFETIME=10` в `.env`, restart Apache, login, изчакай 12 секунди, опитай нещо). Очаквано: redirect към login.

(Върни SESSION_LIFETIME=3600 след теста.)

- [ ] **Step 18.12: Запиши успешно завършване**

В `docs/TODO.md` под „Done log" добави:
```
- **YYYY-MM-DD** — Week 1 Foundation завършена: auth, login/logout, base layout, CSS, manual smoke test passed (12/12 checks).
```

Final commit ако имаш промени:

```bash
git add docs/TODO.md
git commit -m "docs: mark Week 1 Foundation complete in TODO.md"
```

---

## Task 19: Verification — пусни всички тестове

- [ ] **Step 19.1: Пусни всичките unit-like тестове**

```bash
php tests/test_env_loader.php
php tests/test_database.php
php tests/test_router.php
php tests/test_csrf.php
php tests/test_auth.php
```

Очаквано: всеки скрипт завършва с „All X tests passed." без exit код различен от 0.

- [ ] **Step 19.2: Сграбчи всичко в един runner**

`tests/run_all.php`:

```php
<?php
declare(strict_types=1);

$tests = [
    'test_env_loader.php',
    'test_database.php',
    'test_router.php',
    'test_csrf.php',
    'test_auth.php',
];

$failed = 0;
foreach ($tests as $test) {
    $path = __DIR__ . '/' . $test;
    echo "\n--- Running {$test} ---\n";
    passthru("php " . escapeshellarg($path), $exit);
    if ($exit !== 0) {
        $failed++;
    }
}

echo "\n";
if ($failed > 0) {
    echo "\u{2717} {$failed} test file(s) failed.\n";
    exit(1);
}
echo "\u{2713} All test files passed.\n";
```

```bash
php tests/run_all.php
```

Очаквано: всички 5 файла зелени, finishing с „✓ All test files passed."

- [ ] **Step 19.3: Commit**

```bash
git add tests/run_all.php
git commit -m "test: add aggregate test runner"
```

---

## Verification (Definition of Done за Week 1)

Когато всичко по-горе е завършено и commit-нато, провери че:

- ✅ `git log --oneline` показва ~18 commit-а (един на task).
- ✅ `php tests/run_all.php` минава без failures.
- ✅ От XAMPP login с `admin/admin` отваря dashboard.
- ✅ Logout връща към login.
- ✅ Не-логнат потребител се redirect-ва към `/login` при опит за `/`.
- ✅ CSRF чрез `curl` без token дава 419.
- ✅ `storage/logs/error.log` НЕ съдържа неочаквани грешки.
- ✅ `.env` НЕ е commit-нат (`git ls-files | grep .env` връща само `.env.example`).
- ✅ Всички 16 таблици са в БД (`SHOW TABLES`).
- ✅ Phase 1 секцията в `docs/TODO.md` е изцяло checked.

---

## Какво идва във Week 2

След като Week 1 е готов, следващият план ще покрие:
- CRUD за HardwareHost, Hypervisor, VirtualMachine, DockerHost
- CRUD за Project, Service, WebApp с inline child entities (Ports, EnvVars, Volumes, Networks, Roles, Users)
- Tree view (sidebar нав расте от placeholder към реална tree)
- Form validation helpers
- Pagination
- Server-side role guards на write endpoints

Този план ще се напише в `docs/plans/YYYY-MM-DD-week2-crud.md` след завършването на Week 1.
