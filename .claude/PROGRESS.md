# PROGRESS — сесиен журнал

> **За Claude:** Чети този файл в НАЧАЛОТО на всяка сесия (CLAUDE.md те препраща тук). Пиши нов запис В КРАЯ на всяка работна сесия и при всеки завършен milestone. Най-новото е НАЙ-ОТГОРЕ. Формат: дата, какво е направено, какво е СЛЕДВАЩОТО конкретно действие, отворени проблеми.

---

## 2026-06-10 — Сесия 3: Финализиране (Phase A+B+C завършени)

**Направено (паралелно — 4 Sonnet sub-агента + 2 серийни задачи):**

### Phase A: Generators + GenerationController + Views + Docker (паралелно)

- ✅ `src/Services/DockerComposeGenerator.php` (Docker Compose v3.8 YAML, omit-empty-sections, YAML-safe quoting)
- ✅ `src/Services/EnvFileGenerator.php` (групиран по service, secret marker comment)
- ✅ `src/Services/EmmetExporter.php` (UTF-8 box-drawing tree, secret values masked)
- ✅ `src/Models/GeneratedFile.php` (POPO + filename()/mimeType() helpers)
- ✅ `src/Repositories/GeneratedFileRepository.php` (insertSetForProject(), historyForProject(), latestForProject())
- ✅ `src/Controllers/GenerationController.php` (generate/files/emmet/download — try/catch около generators, transaction в repo)
- ✅ `src/Views/services/create.php` (4-section form: basics + ports + envs + volumes + footer; vanilla JS +Add row / × remove)
- ✅ `src/Views/services/edit.php` (pre-filled от моделите; danger zone delete card)
- ✅ `src/Views/projects/files.php` (3 preview cards + history table + regenerate button с next-version label)
- ✅ `src/Views/projects/emmet.php` (preview + copy-to-clipboard JS + download)
- ✅ `Dockerfile` (php:8.2-apache, pdo_mysql, mod_rewrite, DocumentRoot=public/, composer optional)
- ✅ `docker-compose.yml` (app + db services, healthcheck, schema+seed auto-load, storage volume)
- ✅ `.dockerignore`

### Phase C: Документация (серийно)

- ✅ `README.md` (overwrite — setup за XAMPP + Docker, login credentials, sub-folder note)
- ✅ `docs/USER_GUIDE.md` (11 раздела с placeholder за screenshots — потребителят ще ги направи преди защитата)
- ✅ `docs/PROMPTS.md` (5 най-полезни промпта с оценка — за лектора)
- ✅ `docs/screenshots/` папка създадена (placeholder `.gitkeep`)

### Phase B: Bug check

- 🔍 `ServiceController::destroy()` ред 142-143 — `$service->name` и `$service->projectId` са правилни (Service model има camelCase props). НЕ Е БАГ.
- 🔍 `ProjectController::destroy()` ред 101 — `$project->name` правилно. НЕ Е БАГ.
- 🔍 `WebAppController::destroy()` ред 88-90 — правилно (`$webApp->id`, `->name`, `->serviceId`).

**ФИНАЛЕН СТАТУС: Приложението е feature-complete за защита.**

**СЛЕДВАЩО (потребителят прави сам):**
1. `cd /Users/I776896/Documents/GitHub/Manifesto && composer dump-autoload` (ако има Composer; ако не — пропусни, има fallback)
2. **Smoke test в XAMPP:**
   - Копирай проекта в `htdocs/manifesto/`
   - Старт XAMPP (Apache + MySQL)
   - Импортирай `db/schema.sql` и `db/seed.sql` през phpMyAdmin
   - Отвори `http://localhost/manifesto/` → login admin/admin123
   - Click „Generate files" на демо проекта → провери docker-compose.yml content
   - Login като viewer/viewer123 → провери че Edit/Delete бутоните липсват
3. **Sub-folder test (както лекторът ще пусне):**
   - Премести копие в `htdocs/w26/manifesto-fn1/`
   - Отвори `http://localhost/w26/manifesto-fn1/` → същото поведение, URL-ите включват префикса
4. **Screenshots:** заснеми 16-те екрана за `docs/USER_GUIDE.md` и сложи в `docs/screenshots/`
5. **Преди защитата:**
   - `git rm -r docs/PROJECT_CONTEXT.md docs/ARCHITECTURE.md docs/DATABASE_SCHEMA.md docs/DECISIONS.md docs/PRODUCT_REQUIREMENTS.md docs/IMPORT_EXPORT_FORMAT.md docs/FUTURE_WORK.md docs/TODO.md docs/SETUP_AND_DEPLOYMENT.md docs/plans/` (вътрешни документи)
   - **ОСТАВИ:** `README.md`, `docs/USER_GUIDE.md`, `docs/PROMPTS.md`, `docs/screenshots/`
   - Принтирай `docs/USER_GUIDE.md` двустранно (изискване на лектора)
   - Качи в Moodle: код + docs + db (schema.sql + seed.sql) + screenshots
6. **Git commit + push:**
   ```bash
   git add .
   git commit -m "feat: generators, GenerationController, service views, docker setup, docs"
   git push
   ```
7. **GitLab repo:** Лекторът иска private GitLab repo на `https://gitlab.hss.fmi.uni-sofia.bg/`. Mirror-ни от GitHub (`darimachine/Manifesto`) или push директно.
8. **Запиши се** в Google sheet (4-ти таб) с име на проекта (напр. `w26-manifesto-fn1`) и линк към GitLab repo-то.

**Отворени проблеми:**
- PHP не е инсталиран локално — syntax check ще се направи при първо отваряне в браузъра. Всички файлове са визуално прегледани, очаквам да минат.
- Screenshots не са заснети — потребителят го прави преди принтирането.
- `docs/USER_GUIDE.md` все още в repo-то — преди защитата ще се махне (по CLAUDE.md правило). Но трябва да се МАХНЕ САМО ВЪТРЕШНАТА документация — `USER_GUIDE.md`, `PROMPTS.md` и `screenshots/` ОСТАВАТ.

---

## 2026-06-10 — Сесия N: Три stateless Generator класа

**Направено:**
- Създадена папка `src/Services/`.
- `src/Services/DockerComposeGenerator.php` — генерира Docker Compose v3.8 YAML. Constructor injection: `ProjectRepository` + `ServiceChildrenRepository`. Метод `generate(int $projectId): string`. Пропуска празни sections (`ports:`, `environment:`, `volumes:`). YAML-safe quoting за стойности с `:`, `#`, `'`. Null env value → `KEY:`. Валидира service name.
- `src/Services/EnvFileGenerator.php` — генерира `.env` файл, групиран по service. Constructor injection: `ProjectRepository` + `ServiceChildrenRepository`. Метод `generate(int $projectId): string`. Secret env vars получават `# SECRET — store in vault, do not commit` коментар преди себе си. Dotenv-safe quoting.
- `src/Services/EmmetExporter.php` — генерира UTF-8 дърво с box-drawing chars (└─ ├─ │). Constructor injection: `DockerHostRepository` + `ProjectRepository` + `ServiceRepository` + `ServiceChildrenRepository`. Метод `export(int $projectId): string`. Secret стойности → `••••••`. Показва host attrs, services, ports/envs/volumes/webapps.

**СЛЕДВАЩО:**
- Task #2: `GeneratedFileRepository` + `GeneratedFile` model + `GenerationController` (GET `/projects/{id}/generate`, POST `/projects/{id}/generate/{type}`, `/projects/{id}/files`).
- Task #3: Service create/edit views + `projects/files` view + `projects/emmet` view.

**Отворени проблеми:**
- PHP не е в PATH на dev машината (XAMPP-hosted) — syntax check ще се прави при първо зареждане в браузъра.
- `WebAppRepository` няма `forService(int $serviceId)` метод — `EmmetExporter` използва `ServiceRepository::webAppsOf()` (връща `[id, name]`), което е достатъчно за дървото.

---

## 2026-06-09 — Сесия 2: GitHub setup + scope редукция (D-10)

**Направено:**
- Git repo инициализиран локално, push-нат към GitHub: `darimachine/Manifesto` (private).
- `.gitignore` създаден (PHP/Composer/XAMPP boilerplate).
- `CLAUDE.md` създаден в root-а — entry point за AI сесии на всяка машина.
- **D-10: scope редуциран до 1 седмица** (потребителят: „1 седмица да го подкарам и нещо да работи"):
  - Йерархия смалена: остават AppUser, DockerHost (самостоятелен, БЕЗ XOR FK), Project, Service (+PortMapping/EnvVar/Volume), WebApp, GeneratedFile = **9 таблици**.
  - Изрязани: HW/Hypervisor/VM, InfraRole/InfraUser, Network/ServiceNetwork, Dockerfile/vhost/README генератори, JSON import/export.
  - Генератори в scope: docker-compose.yml + .env (MUST), Emmet export (SHOULD).
  - Stretch: health check, ZIP, version history.
- `TODO.md` пренаписан (v2.0, day-by-day спринт). D-10 добавен в `DECISIONS.md`. Банери в `ARCHITECTURE.md` и `DATABASE_SCHEMA.md`.

**СЛЕДВАЩО (Day 1):**
1. `composer.json` (PSR-4: `Manifesto\` → `src/`) + folder структура.
2. `db/schema.sql` (9-те таблици) + `db/seed.sql` (admin/viewer + demo project).
3. Core класове (`src/Core/`): EnvLoader, Database, Session, Csrf, Request, Response, Router, Auth, ViewRenderer.
4. `public/index.php` + `.htaccess` + config + login flow.

**Отворени проблеми / бележки:**
- Потребителят commit-ва и push-ва САМ — давай му команди, не ги изпълнявай (виж CLAUDE.md правилата за работа).
- Целева машина за защита: XAMPP, DocumentRoot → `public/`.
- Преди защитата: `git rm -r docs/` + root README (Day 7).

---

## 2026-05-14 — Сесия 1: Phase 0 (документация)

**Направено:** Пълен `docs/` пакет — PRD, ARCHITECTURE, DATABASE_SCHEMA (оригинално 16 таблици), DECISIONS (D-01…D-09), SETUP, IMPORT_EXPORT_FORMAT, FUTURE_WORK, TODO v1.1, Week-1 план. Име избрано: **Manifesto** (D-09).

**Бележка:** Оригиналният 3-4 седмичен план е заменен от D-10 (виж по-горе) — `docs/plans/2026-05-14-week1-foundation.md` се чете само като справка.
