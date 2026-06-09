# DATABASE SCHEMA — Manifesto

> Пълна релационна схема на MySQL/MariaDB базата.
> Database name: `manifesto` · Engine: InnoDB · Charset: utf8mb4 · Collation: utf8mb4_unicode_ci
> Версия: 1.1 · Дата: 2026-05-14
> ⚠️ **D-10 (2026-06-09): SCOPE РЕДУЦИРАН до 9 таблици:** `app_user`, `docker_host`, `project`, `service`, `port_mapping`, `env_var`, `volume`, `web_app`, `generated_file`. Игнорирай секциите за: hardware_host, hypervisor, virtual_machine, infrastructure_role, infrastructure_user, network, service_network, health_check (stretch). **`docker_host` губи XOR FK** — става самостоятелна таблица с колони name, ip_address, os, docker_version, notes. Виж `DECISIONS.md` D-10.

---

## ER overview (text)

```
AppUser  (independent — само за login)

HardwareHost ──< Hypervisor ──< VirtualMachine ──< DockerHost*
                                                       │
HardwareHost ───────────────────────────────────> DockerHost*  (XOR)
                                                       │
                                                       v
                                                    Project ──< Service ──< PortMapping
                                                       │           │
                                                       │           ├──< EnvVar
                                                       │           ├──< Volume
                                                       │           └──< WebApp ──< InfrastructureRole ──< InfrastructureUser
                                                       │
                                                       ├──< Network ─< ServiceNetwork >─ Service
                                                       ├──< GeneratedFile (history)
                                                       └─(чрез WebApp)─< HealthCheck
```

\* DockerHost има XOR между vm_id и hardware_host_id — точно едно е NOT NULL.

---

## Таблици

### `app_user` — потребители на dashboard

| Колона | Тип | NULL | Default | Notes |
|---|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | — | PK |
| username | VARCHAR(64) | NO | — | UNIQUE |
| password_hash | VARCHAR(255) | NO | — | BCRYPT |
| role | ENUM('admin','viewer') | NO | 'viewer' | |
| display_name | VARCHAR(128) | YES | NULL | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP ON UPDATE | |

Indexes: `UNIQUE(username)`.

---

### `hardware_host` — физически сървър

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| name | VARCHAR(128) | NO | човешко име |
| mac_address | VARCHAR(17) | YES | формат AA:BB:CC:DD:EE:FF |
| physical_location | VARCHAR(255) | YES | напр. „fmi" |
| mgmt_type | VARCHAR(64) | YES | напр. „kvm", „ilo" |
| admin_email | VARCHAR(255) | YES | |
| ip | VARCHAR(45) | YES | IPv4 или IPv6 |
| web_url | VARCHAR(255) | YES | management URL |
| notes | TEXT | YES | |
| created_at, updated_at | TIMESTAMP | NO | стандартни |

Indexes: `UNIQUE(name)`, INDEX на `ip`.

---

### `hypervisor` — хипервайзор върху hardware

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| hardware_host_id | INT UNSIGNED | NO | FK → hardware_host(id) ON DELETE CASCADE |
| name | VARCHAR(128) | NO | |
| vendor | VARCHAR(64) | YES | напр. „HyperV" |
| os | VARCHAR(128) | YES | напр. „WindowsDatacenter2019R2" |
| admin_email | VARCHAR(255) | YES | |
| ip | VARCHAR(45) | YES | |
| notes | TEXT | YES | |
| created_at, updated_at | TIMESTAMP | NO | |

Indexes: `UNIQUE(hardware_host_id, name)`, INDEX на `hardware_host_id`.

---

### `virtual_machine` — VM под hypervisor

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| hypervisor_id | INT UNSIGNED | NO | FK → hypervisor(id) ON DELETE CASCADE |
| name | VARCHAR(128) | NO | |
| ip | VARCHAR(45) | YES | |
| port | INT UNSIGNED | YES | напр. SSH порт |
| status | ENUM('running','stopped','unknown') | NO | DEFAULT 'unknown' |
| notes | TEXT | YES | |
| created_at, updated_at | TIMESTAMP | NO | |

Indexes: `UNIQUE(hypervisor_id, name)`.

---

### `docker_host` — Docker среда

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| vm_id | INT UNSIGNED | YES | FK → virtual_machine(id) ON DELETE CASCADE |
| hardware_host_id | INT UNSIGNED | YES | FK → hardware_host(id) ON DELETE CASCADE |
| name | VARCHAR(128) | NO | |
| docker_version | VARCHAR(32) | YES | напр. „24.0.5" |
| notes | TEXT | YES | |
| created_at, updated_at | TIMESTAMP | NO | |

Constraint: **CHECK (vm_id IS NOT NULL XOR hardware_host_id IS NOT NULL)** — точно едно от двете.
Indexes: `INDEX(vm_id)`, `INDEX(hardware_host_id)`, `UNIQUE(COALESCE(vm_id,0), COALESCE(hardware_host_id,0), name)` (MariaDB поддържа functional unique).

Ако MariaDB версията не поддържа CHECK — валидираме на ниво приложение в Repository.

---

### `project` — Docker Compose project

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| docker_host_id | INT UNSIGNED | NO | FK → docker_host(id) ON DELETE CASCADE |
| name | VARCHAR(128) | NO | |
| slug | VARCHAR(64) | NO | UNIQUE — за URL и compose project name |
| description | TEXT | YES | |
| created_at, updated_at | TIMESTAMP | NO | |

Indexes: `UNIQUE(slug)`, `INDEX(docker_host_id)`.

---

### `service` — Docker service

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| project_id | INT UNSIGNED | NO | FK → project(id) ON DELETE CASCADE |
| name | VARCHAR(128) | NO | unique within project |
| image | VARCHAR(255) | YES | напр. „nginx:alpine" |
| build_context | VARCHAR(255) | YES | напр. „./web" |
| command | VARCHAR(512) | YES | overrides image CMD |
| depends_on | TEXT | YES | comma-separated service names |
| restart_policy | ENUM('no','always','on-failure','unless-stopped') | YES | DEFAULT 'unless-stopped' |
| notes | TEXT | YES | |
| created_at, updated_at | TIMESTAMP | NO | |

Indexes: `UNIQUE(project_id, name)`.

---

### `port_mapping` — host:container порт мапване

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| service_id | INT UNSIGNED | NO | FK → service(id) ON DELETE CASCADE |
| host_port | INT UNSIGNED | NO | |
| container_port | INT UNSIGNED | NO | |
| protocol | ENUM('tcp','udp') | NO | DEFAULT 'tcp' |

Indexes: `INDEX(service_id)`.
Note: уникалност на (project_id, host_port) се валидира на application level (port collision check).

---

### `env_var` — environment variables

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| service_id | INT UNSIGNED | NO | FK → service(id) ON DELETE CASCADE |
| key_name | VARCHAR(128) | NO | напр. „DB_PASSWORD" |
| value | TEXT | YES | |
| is_secret | TINYINT(1) | NO | DEFAULT 0 |
| description | TEXT | YES | |

Indexes: `UNIQUE(service_id, key_name)`.

---

### `volume` — volume mounts

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| service_id | INT UNSIGNED | NO | FK → service(id) ON DELETE CASCADE |
| host_path | VARCHAR(512) | NO | |
| container_path | VARCHAR(512) | NO | |
| mode | ENUM('ro','rw') | NO | DEFAULT 'rw' |

Indexes: `INDEX(service_id)`.

---

### `network` — Docker network

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| project_id | INT UNSIGNED | NO | FK → project(id) ON DELETE CASCADE |
| name | VARCHAR(128) | NO | |
| driver | VARCHAR(32) | NO | DEFAULT 'bridge' |

Indexes: `UNIQUE(project_id, name)`.

---

### `service_network` — many-to-many join

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| service_id | INT UNSIGNED | NO | FK → service(id) ON DELETE CASCADE |
| network_id | INT UNSIGNED | NO | FK → network(id) ON DELETE CASCADE |

Indexes: `PK(service_id, network_id)`.

---

### `webapp` — публично достъпно приложение

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| service_id | INT UNSIGNED | NO | FK → service(id) ON DELETE CASCADE |
| name | VARCHAR(128) | NO | |
| url | VARCHAR(512) | YES | напр. „https://myshop.local" |
| dns_name | VARCHAR(255) | YES | напр. „myshop.fmi.uni-sofia.bg" |
| vhost_ip | VARCHAR(45) | YES | |
| vhost_path | VARCHAR(512) | YES | document root |
| authors | TEXT | YES | comma-separated |
| status | ENUM('up','down','unknown') | NO | DEFAULT 'unknown' |
| last_status_change | TIMESTAMP | YES | |
| notes | TEXT | YES | |
| created_at, updated_at | TIMESTAMP | NO | |

Indexes: `UNIQUE(service_id, name)`, `INDEX(dns_name)`.

---

### `infrastructure_role` — описателна роля

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| webapp_id | INT UNSIGNED | NO | FK → webapp(id) ON DELETE CASCADE |
| role_name | VARCHAR(128) | NO | напр. „admin" |
| url_path | VARCHAR(255) | YES | напр. „/admin" |
| description | TEXT | YES | |

Indexes: `UNIQUE(webapp_id, role_name)`.

---

### `infrastructure_user` — описателен потребител

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| role_id | INT UNSIGNED | NO | FK → infrastructure_role(id) ON DELETE CASCADE |
| username | VARCHAR(128) | NO | |
| password_hint | VARCHAR(255) | YES | НЕ е реална парола — само hint/notes |
| permissions | TEXT | YES | свободен текст |

Indexes: `UNIQUE(role_id, username)`.

**Важно:** Тук НЕ съхраняваме реални пароли — само описание. Реалните пароли остават в `env_var` с `is_secret=1`.

---

### `generated_file` — версии на генерирани файлове

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| project_id | INT UNSIGNED | NO | FK → project(id) ON DELETE CASCADE |
| file_type | ENUM('docker-compose','env','dockerfile','vhost-nginx','vhost-apache','readme','emmet') | NO | |
| filename | VARCHAR(255) | NO | напр. „docker-compose.yml" или „Dockerfile.web" |
| content | LONGTEXT | NO | целият файл като текст |
| version_number | INT UNSIGNED | NO | инкрементален за (project_id, file_type) |
| generated_at | TIMESTAMP | NO | DEFAULT CURRENT_TIMESTAMP |
| generated_by_user_id | INT UNSIGNED | YES | FK → app_user(id) ON DELETE SET NULL |

Indexes: `INDEX(project_id, file_type, version_number)`, `INDEX(generated_at)`.

---

### `health_check` — лог на проверки

| Колона | Тип | NULL | Notes |
|---|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | NO | PK |
| webapp_id | INT UNSIGNED | NO | FK → webapp(id) ON DELETE CASCADE |
| status_code | INT | YES | NULL ако timeout/неуспех преди connect |
| latency_ms | INT | YES | |
| error_message | TEXT | YES | |
| checked_at | TIMESTAMP | NO | DEFAULT CURRENT_TIMESTAMP |

Indexes: `INDEX(webapp_id, checked_at)`.
Cleanup: при insert на нов запис, изтриваме всички записи за този webapp с rank > 100 (rank by checked_at DESC). Алгоритъм в `HealthChecker` service.

---

## DDL skeleton (preview)

Пълният `schema.sql` ще се напише в Week 1 и ще се запази в `db/schema.sql`. Тук е skeleton:

```sql
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
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','viewer') NOT NULL DEFAULT 'viewer',
  display_name VARCHAR(128),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ... останалите 15 таблици в същия стил
```

---

## Naming conventions

- Таблици: `snake_case`, единствено число (`project`, не `projects`).
- Колони: `snake_case`.
- FK: `<parent>_id`.
- Timestamps: `created_at`, `updated_at`, `<event>_at`.
- Boolean: `is_<adjective>` (`is_secret`, `is_active`).
- Enums: lowercase string values (`'admin'`, `'tcp'`).

---

## Migration стратегия

За MVP — **single schema.sql** file, drop-and-recreate подход.

Ако след initial dev възникне нужда от incremental промени, се добавя `db/migrations/001_<name>.sql` номериран файл. Без миграционен framework — ръчно през phpMyAdmin или PHP CLI script.

---

## Seed data

`db/seed.sql` ще съдържа:
- 1 admin user (`admin` / `admin` — password hash на „admin")
- 1 viewer user (`viewer` / `viewer`)
- 1 demo HardwareHost → Hypervisor → VM → DockerHost → Project с 2-3 services и 1 WebApp
- Достатъчно за демонстрация на защита без ръчно въвеждане.
