# TODO — Manifesto — Седмични milestones и текущи задачи

> Живо-актуализиран файл. Маркирай задачите като ✅ при завършване, ⏳ при in-progress, ⏸ ако паузирано.
> Версия: 1.1 · Дата: 2026-05-14

---

## Текущ статус

**Дата на стартиране:** 2026-05-14
**Целева дата на готовност (MVP):** ~3-4 седмици (гъвкаво)
**Текуща фаза:** Phase 0 (Documentation & Planning)

---

## Phase 0 — Documentation & Planning ✅

- [x] PRD написан и одобрен (виж `PRODUCT_REQUIREMENTS.md`).
- [x] Архитектурни решения документирани (виж `DECISIONS.md`).
- [x] Folder structure описан (виж `ARCHITECTURE.md`).
- [x] DB schema preview готов (виж `DATABASE_SCHEMA.md`).
- [x] Setup стратегия описана (виж `SETUP_AND_DEPLOYMENT.md`).
- [x] JSON формат документиран (виж `IMPORT_EXPORT_FORMAT.md`).
- [x] Future work каталогизиран (виж `FUTURE_WORK.md`).
- [ ] **NEXT:** Детайлен implementation plan (writing-plans skill).

---

## Phase 1 — Week 1: Foundation

### 1.1 Project setup
- [ ] `git init`, добави `.gitignore`.
- [ ] Първи commit с docs/ структурата.
- [ ] Създай `composer.json` с PSR-4 autoload (без vendor deps).
- [ ] `composer dump-autoload`.
- [ ] Folder структура: `public/`, `src/Core/`, `src/Models/`, `src/Repositories/`, `src/Controllers/`, `src/Services/`, `src/Views/`, `config/`, `db/`, `storage/logs/`, `storage/generated/`.

### 1.2 Database
- [ ] `db/schema.sql` — всички 16 таблици (10 entities + 6 child/log).
- [ ] `db/seed.sql` — admin/viewer users + 1 demo project.
- [ ] Ръчно импортирай в XAMPP, валидирай.

### 1.3 Core slot
- [ ] `src/Core/EnvLoader.php` — чете `.env` ръчно (4-5 реда).
- [ ] `src/Core/Database.php` — PDO singleton, charset utf8mb4.
- [ ] `src/Core/Session.php` — secure session setup.
- [ ] `src/Core/Csrf.php` — token generate + verify.
- [ ] `src/Core/Request.php` — wrap $_GET/$_POST/$_SERVER.
- [ ] `src/Core/Response.php` — render view, redirect, JSON, abort.
- [ ] `src/Core/Router.php` — regex-based dispatch от `config/routes.php`.
- [ ] `src/Core/Auth.php` — login, logout, requireRole guard.
- [ ] `src/Core/ViewRenderer.php` — рендира `Views/*.php` с layout-и.

### 1.4 Front controller
- [ ] `public/index.php` — bootstraps всичко.
- [ ] `public/.htaccess` — mod_rewrite към index.php.
- [ ] `config/config.php` + `config/routes.php`.
- [ ] `.env.example`.

### 1.5 Auth flow
- [ ] `AuthController::showLogin` + view.
- [ ] `AuthController::login` — verify, regenerate session.
- [ ] `AuthController::logout`.
- [ ] AppUserRepository.
- [ ] Тест: login с seed user, протектне страница, logout.

### 1.6 Base layout
- [ ] `Views/layouts/app.php` — sidebar + topbar + content area.
- [ ] `Views/partials/sidebar.php` — placeholder tree (без data).
- [ ] `Views/partials/topbar.php` — username + logout button.
- [ ] `public/assets/css/app.css` — custom CSS, минимален.
- [ ] Welcome dashboard (празна страница след login).

### 1.7 Week 1 verification
- [ ] Чист clone → setup → login работи.
- [ ] CSRF token присъства в login form.
- [ ] Wrong password не може да login.
- [ ] Viewer и admin login-ват с различни роли (visible в topbar).
- [ ] Logout връща към login.

---

## Phase 2 — Week 2: CRUD massive

> Pattern: за всеки entity — Model, Repository, Controller, 4 Views (index, show, create, edit), маршрути.

### Entities в реда (parent → child):

- [ ] HardwareHost — list, create, edit, delete, show.
- [ ] Hypervisor — same.
- [ ] VirtualMachine — same.
- [ ] DockerHost — same (с XOR validation).
- [ ] Project — same.
- [ ] Service — same (вложен под Project).
- [ ] PortMapping — inline-managed в Service edit form.
- [ ] EnvVar — inline-managed в Service edit form.
- [ ] Volume — inline-managed в Service edit form.
- [ ] Network — list per project, simple CRUD.
- [ ] ServiceNetwork — managed през Service edit (multi-select).
- [ ] WebApp — same (вложен под Service).
- [ ] InfrastructureRole — inline под WebApp.
- [ ] InfrastructureUser — inline под InfrastructureRole.

### 2.1 Tree view
- [ ] `Views/partials/tree-view.php` — рекурсивен render.
- [ ] `public/assets/js/tree-view.js` — collapse/expand.
- [ ] Текущ възел се highlights.
- [ ] Click → отваря entity show страница.

### 2.2 Common patterns
- [ ] Form validation helpers (PHP функции в `src/Core/Validator.php`).
- [ ] Flash message system.
- [ ] Pagination helper.
- [ ] Sortable table headers (vanilla JS).

### 2.3 Week 2 verification
- [ ] Можеш да създадеш цяла йерархия от UI: HW → Hypervisor → VM → DockerHost → Project → Service с 2 EnvVar, 1 PortMapping, 1 Volume → WebApp → 1 Role → 1 InfraUser.
- [ ] Tree view показва всички и се навигира към тях.
- [ ] Viewer не може да види create/edit/delete бутони.
- [ ] Viewer POST с curl → 403.

---

## Phase 3 — Week 3: Generators

### 3.1 Docker Compose generator
- [ ] `DockerComposeGenerator::generate($projectId)` — обхожда Project → Services → child collections.
- [ ] Output strictly валиден YAML.
- [ ] Запис в `generated_file` с file_type='docker-compose', auto-increment version_number.
- [ ] Тест: ръчен `docker compose config` validation.

### 3.2 .env generator
- [ ] `EnvFileGenerator::generate($projectId)` — извлича всички EnvVars с `is_secret=true` отделно.
- [ ] Output: `KEY=value\n` формат.
- [ ] Запис в `generated_file`.

### 3.3 Dockerfile generator
- [ ] `DockerfileGenerator::generate($serviceId)` — шаблонен (FROM, WORKDIR, COPY, CMD).
- [ ] Един файл per service в проекта.

### 3.4 Vhost generator
- [ ] `VhostGenerator::generate($webappId, $format='nginx')` — nginx формат.
- [ ] Stretch: apache формат.

### 3.5 README generator
- [ ] `ReadmeGenerator::generate($projectId)` — markdown с table на services, ports, инструкции.

### 3.6 Emmet exporter
- [ ] `EmmetExporter::export($projectId)` — рекурсивен printer.
- [ ] UI: modal на Project show страница с textarea + copy button.

### 3.7 ZipPacker
- [ ] `ZipPacker::pack($projectId, $version)` — взема всички файлове от една версия → опакова в ZIP → връща path.
- [ ] Endpoint `/projects/{id}/download/{version}` стриймва ZIP.

### 3.8 Version history page
- [ ] `HistoryController::index` — timeline на версиите.
- [ ] Преглед на стара версия (modal).
- [ ] Download .zip на стара версия.

### 3.9 Generation endpoint
- [ ] `POST /projects/{id}/generate` — вика всички generators, инкрементира версията, redirect-ва към history.

### 3.10 Week 3 verification
- [ ] Генерация на demo project → произвежда валиден `docker-compose.yml`.
- [ ] `docker compose -f generated.yml config` → passes.
- [ ] `docker compose -f generated.yml up` → услугите се вдигат (manual test).
- [ ] ZIP download работи.
- [ ] Emmet export показва пълна йерархия.

---

## Phase 4 — Week 4: Import/Export, Health, Polish, Defense prep

### 4.1 JSON export
- [ ] `JsonExporter::export($projectId)` — pretty-printed JSON по `IMPORT_EXPORT_FORMAT.md` schema.
- [ ] Endpoint `/projects/{id}/export` стриймва файл.

### 4.2 JSON import
- [ ] Upload form.
- [ ] `JsonImporter::import($jsonString)` — валидация → reference resolution → транзакционен insert.
- [ ] Error reporting на потребителя.

### 4.3 Health check
- [ ] `HealthChecker::check($webappId)` — cURL, timeout 5s, follow redirects.
- [ ] Запис в `health_check`, cleanup стари записи.
- [ ] „Check now" бутон на WebApp show.
- [ ] Status badge на list view.

### 4.4 UI polish
- [ ] Error pages (404, 403, 419, 500).
- [ ] Loading states (spinner или text).
- [ ] Mobile-tablet responsive проверка.
- [ ] Color consistency check.
- [ ] Accessibility minimal (labels, alt текстове, contrast).

### 4.5 Documentation final touch
- [ ] Update `README.md` на проекта (root).
- [ ] Update `SETUP_AND_DEPLOYMENT.md` ако са има промени.
- [ ] Update `DECISIONS.md` с всичко ново.
- [ ] Screenshots в `docs/screenshots/` (по избор).

### 4.6 Defense prep
- [ ] Чисто clone на пробна машина — следвай `SETUP_AND_DEPLOYMENT.md` от нула.
- [ ] Smoke test всички 9 verification стъпки.
- [ ] Подготви demo script (какво ще показваш, в какъв ред).
- [ ] Подготви отговори на очаквани въпроси (виж по-долу).
- [ ] Repetiția на защитата — pretend explain пред огледало или приятел.

---

## Очаквани въпроси на защита (prep)

| Въпрос | Кратък отговор |
|---|---|
| „Защо без framework?" | Изискване на курса — да докажа разбиране на core езика. |
| „Защо MySQL вместо PostgreSQL?" | XAMPP идва с нея; по-малко setup на чужда машина. |
| „Реално ли управлява Docker?" | Не — генерира конфигурации. Това е safe-by-design. Реално оркестриране е в `FUTURE_WORK.md` с обяснение защо е risky. |
| „Какъв е твоят threat model?" | Single-user local tool. Защита срещу: SQL injection (PDO), XSS (htmlspecialchars), CSRF (token), session hijacking (regenerate). |
| „Защо DockerHost XOR?" | Bare metal Docker също е валиден scenario. Constraint държи schema-та чиста — точно един parent. |
| „Защо Emmet read-only?" | Bidirectional parser е 12-18ч custom работа за един човек. Read-only постига „духа" на оригиналното задание. |
| „Как се скейлва?" | За MVP — single-instance. Скалирането е в FUTURE_WORK (multi-tenancy, API, etc.). |
| „Защо нямаш unit tests?" | Time constraint. Има manual smoke test checklist. Test framework е във FUTURE_WORK. |

---

## Discovered issues / blockers (живо)

> Записвай тук всичко, което блокира работата или изисква по-късно решение.

- (Празно.)

---

## Done log (chronological)

> Когато завършиш milestone, премести в този раздел с дата.

- **2026-05-14** — Phase 0 завършена: одобрен PRD, написана `docs/` структура с 8 файла.
- **2026-05-14** — Името на проекта избрано: **Manifesto** (виж D-09 в `DECISIONS.md`). Обновени всички 9 документа.
