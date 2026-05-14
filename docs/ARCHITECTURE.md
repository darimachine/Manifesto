# ARCHITECTURE — Manifesto

> Структура на кода, слоеве, отговорности, граници.
> Версия: 1.1 · Дата: 2026-05-14

---

## 1. High-level подход

Класическа **layered architecture** в vanilla PHP 8.x, организирана по PSR-4. Без framework, но със собствен минимален front controller, рутер, repository pattern, и service слой за генерация на файлове.

```
┌─────────────────────────────────────────────────────────┐
│  Browser (vanilla JS, custom CSS, HTML)                 │
└────────────────────────┬────────────────────────────────┘
                         │ HTTP (form posts, GET pages)
┌────────────────────────▼────────────────────────────────┐
│  public/index.php  ←  Front Controller                  │
│   - .env loader                                         │
│   - Router (config/routes.php)                          │
│   - Session + CSRF middleware                           │
│   - Auth middleware (role guards)                       │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│  Controllers (src/Controllers/)                         │
│   - Validate input, call services, render views         │
└─────────┬─────────────────────────┬─────────────────────┘
          │                         │
┌─────────▼──────────┐    ┌─────────▼─────────────────────┐
│  Services          │    │  Repositories                 │
│  (src/Services/)   │    │  (src/Repositories/)          │
│   - Generators     │    │   - PDO queries               │
│   - HealthChecker  │    │   - Prepared statements       │
│   - ZipPacker      │    │   - Maps rows → Model objects │
│   - EmmetExporter  │    │                               │
└─────────┬──────────┘    └─────────┬─────────────────────┘
          │                         │
          └────────────┬────────────┘
                       │
            ┌──────────▼────────────┐
            │  Models (POPO/DTO)    │
            │  (src/Models/)        │
            │   - Дъмбове структури │
            │     без логика        │
            └──────────┬────────────┘
                       │
            ┌──────────▼────────────┐
            │  MySQL / MariaDB      │
            └───────────────────────┘
```

---

## 2. Слоеве и отговорности

| Слой | Папка | Какво прави | Какво НЕ прави |
|---|---|---|---|
| **Core** | `src/Core/` | Router, Request, Response, Session, CSRF, EnvLoader, Database (PDO singleton). | Бизнес логика. |
| **Controllers** | `src/Controllers/` | Парсва request, валидира, вика Services/Repositories, връща View или JSON. | SQL заявки, file I/O. |
| **Models** | `src/Models/` | POPO/DTO класове — едно property per поле. | Заявки към БД, бизнес логика. |
| **Repositories** | `src/Repositories/` | Всички PDO заявки. Връща Model обекти или масиви от тях. | HTTP, валидация. |
| **Services** | `src/Services/` | Генератори, health checker, ZIP пакетажор, Emmet exporter. | HTTP, директни SQL. |
| **Views** | `src/Views/` | PHP templates с inline `<?= htmlspecialchars($var) ?>`. | Заявки, бизнес логика. |

**Ключово правило:** Controllers НЕ викат Repositories директно за write операции, които изискват генерация. Те викат Service, който вика Repository. За прости CRUD-и Controller → Repository е ОК.

---

## 3. Folder structure

```
PROEKT/
├── public/                       # web root (DocumentRoot за Apache)
│   ├── index.php                 # front controller
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css
│   │   └── js/
│   │       ├── tree-view.js
│   │       └── form-validate.js
│   └── .htaccess                 # rewrite to index.php
│
├── src/
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Session.php
│   │   ├── Csrf.php
│   │   ├── EnvLoader.php
│   │   ├── Database.php          # PDO singleton
│   │   ├── Auth.php              # login, logout, role guard
│   │   └── ViewRenderer.php
│   │
│   ├── Models/                   # 1 файл per entity
│   │   ├── AppUser.php
│   │   ├── HardwareHost.php
│   │   ├── Hypervisor.php
│   │   ├── VirtualMachine.php
│   │   ├── DockerHost.php
│   │   ├── Project.php
│   │   ├── Service.php
│   │   ├── WebApp.php
│   │   ├── InfrastructureRole.php
│   │   ├── InfrastructureUser.php
│   │   ├── PortMapping.php
│   │   ├── EnvVar.php
│   │   ├── Volume.php
│   │   ├── Network.php
│   │   ├── GeneratedFile.php
│   │   └── HealthCheck.php
│   │
│   ├── Repositories/             # 1 файл per entity
│   │   ├── AppUserRepository.php
│   │   ├── HardwareHostRepository.php
│   │   ├── ...
│   │   └── HealthCheckRepository.php
│   │
│   ├── Controllers/              # групирани по domain
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── InfrastructureController.php   # HW/Hypervisor/VM/DockerHost CRUD
│   │   ├── ProjectController.php           # Project + Service CRUD
│   │   ├── WebAppController.php
│   │   ├── GenerationController.php        # генерация на файлове
│   │   ├── ImportExportController.php
│   │   ├── HealthController.php
│   │   └── HistoryController.php
│   │
│   ├── Services/
│   │   ├── Generators/
│   │   │   ├── DockerComposeGenerator.php
│   │   │   ├── EnvFileGenerator.php
│   │   │   ├── DockerfileGenerator.php
│   │   │   ├── VhostGenerator.php          # nginx + apache
│   │   │   ├── ReadmeGenerator.php
│   │   │   └── EmmetExporter.php
│   │   ├── HealthChecker.php
│   │   ├── ZipPacker.php
│   │   ├── JsonExporter.php
│   │   └── JsonImporter.php
│   │
│   └── Views/                    # PHP templates
│       ├── layouts/
│       │   ├── app.php           # main layout (sidebar + topbar)
│       │   └── auth.php          # login layout
│       ├── partials/
│       │   ├── sidebar.php
│       │   ├── topbar.php
│       │   ├── tree-view.php
│       │   └── flash-messages.php
│       ├── auth/
│       │   └── login.php
│       ├── infrastructure/
│       │   ├── hardware/...
│       │   ├── hypervisors/...
│       │   ├── vms/...
│       │   └── docker-hosts/...
│       ├── projects/
│       │   ├── index.php
│       │   ├── show.php
│       │   ├── create.php
│       │   └── edit.php
│       ├── services/...
│       ├── webapps/...
│       └── generation/
│           └── show.php          # download links + preview
│
├── config/
│   ├── config.php                # connects .env
│   └── routes.php                # route definitions
│
├── db/
│   ├── schema.sql                # all CREATE TABLE
│   ├── seed.sql                  # demo data + admin/admin
│   └── migrations/               # incremental, ако се наложи
│
├── storage/
│   ├── generated/                # последно генерирани файлове на диск (кеш)
│   └── logs/
│       └── error.log
│
├── docs/                         # този folder
│
├── .env.example
├── .env                          # gitignored
├── .gitignore
├── composer.json                 # ONLY autoload, без vendor deps
└── README.md
```

---

## 4. Request flow (example: създаване на Service)

```
POST /projects/42/services
   │
   ▼
public/index.php
   │  - loads .env via EnvLoader
   │  - opens PDO connection (singleton)
   │  - starts session, regenerates ID if needed
   │
   ▼
Router.dispatch(request)
   │  - matches POST /projects/{id}/services → ProjectController::createService
   │  - extracts route params: project_id=42
   │
   ▼
Auth::requireRole('admin')
   │  - guard: ако role != admin, 403
   │
   ▼
Csrf::verify(request)
   │  - проверява _csrf_token
   │
   ▼
ProjectController::createService(request, project_id)
   │  - валидира input (name, image, ...)
   │  - new Service($input)
   │  - ServiceRepository::insert($service)
   │  - ChildEntitiesRepository::insertPorts/envs/volumes(...)
   │  - flash success message
   │  - redirect to /projects/42
   │
   ▼
HTTP 302 → /projects/42
```

---

## 5. Сесия и CSRF

**Session.php:**
- `session_start()` с `session_set_cookie_params([HttpOnly=true, SameSite=Lax])`.
- `regenerate()` след login.
- `getUserId()`, `getUserRole()`, `setFlash()`, `getFlash()`.

**Csrf.php:**
- `getToken()`: ако няма в сесия — генерира `bin2hex(random_bytes(32))`, пази в `$_SESSION['_csrf_token']`.
- `verify(string $submitted)`: hash_equals comparison; ако fail — abort 419.

Всеки form в Views има:
```html
<input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(Csrf::getToken()) ?>">
```

---

## 6. Routing

`config/routes.php` връща масив:

```php
return [
    ['GET',  '/',                              DashboardController::class, 'index'],
    ['GET',  '/login',                         AuthController::class,      'showLogin'],
    ['POST', '/login',                         AuthController::class,      'login'],
    ['POST', '/logout',                        AuthController::class,      'logout'],
    ['GET',  '/projects',                      ProjectController::class,   'index'],
    ['GET',  '/projects/create',               ProjectController::class,   'create'],
    ['POST', '/projects',                      ProjectController::class,   'store'],
    ['GET',  '/projects/{id}',                 ProjectController::class,   'show'],
    ['GET',  '/projects/{id}/edit',            ProjectController::class,   'edit'],
    ['POST', '/projects/{id}',                 ProjectController::class,   'update'],
    ['POST', '/projects/{id}/delete',          ProjectController::class,   'destroy'],
    ['POST', '/projects/{id}/generate',        GenerationController::class,'generate'],
    ['GET',  '/projects/{id}/history',         HistoryController::class,   'index'],
    ['GET',  '/projects/{id}/download/{ver}',  HistoryController::class,   'download'],
    ['GET',  '/projects/{id}/export',          ImportExportController::class, 'export'],
    ['GET',  '/import',                        ImportExportController::class, 'showImport'],
    ['POST', '/import',                        ImportExportController::class, 'import'],
    ['POST', '/webapps/{id}/check',            HealthController::class,    'check'],
    // ... аналогично за HW, Hypervisor, VM, DockerHost, Service, WebApp, InfraRole, InfraUser
];
```

Router-ът минава през масива, прави regex match на placeholders, връща Controller + method + params.

---

## 7. PSR-4 autoload

`composer.json`:
```json
{
  "autoload": {
    "psr-4": {
      "Manifesto\\": "src/"
    }
  }
}
```

Без vendor зависимости — `composer install` създава само autoloader.

---

## 8. Граници и инварианти

- **Controllers не правят PDO заявки.** Винаги през Repository.
- **Views не правят PDO заявки.** Всичко идва от Controller като примитиви/Models.
- **Models нямат методи с бизнес логика.** Само properties + конструктор + (optional) `toArray()`.
- **Generators са stateless.** Приемат масив с данни → връщат низ. Без I/O вътре.
- **Repository hardware constraint:** DockerHost трябва да има или `vm_id` или `hardware_host_id`, не и двете (XOR-constraint на DB ниво + double-check в код).
- **Generated files:** Записват се едновременно в БД (`GeneratedFile` с LONGTEXT) и кеш на диск (`storage/generated/<project_slug>/v<N>/`). DB е source of truth; disk е оптимизация.

---

## 9. Error handling

- Грешки в PHP → custom error handler → пише в `storage/logs/error.log` + показва generic „500 — нещо се обърка" страница (без stack trace в production).
- Грешки в валидация → връщат се на форма с error messages + запазени input стойности.
- 404 → custom view `Views/errors/404.php`.
- 403 → custom view `Views/errors/403.php`.
- 419 (CSRF fail) → custom view с „Session expired, please refresh".

---

## 10. Какво НЕ е архитектура (още)

Този документ не покрива:
- Конкретна логика на всеки generator (виж `IMPORT_EXPORT_FORMAT.md` за JSON и отделни generator-specs ще се добавят при имплементация).
- Подробни валидационни правила per поле (виж entity форми в имплементационна фаза).
- Caching стратегия (засега няма).
