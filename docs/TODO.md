# TODO — Manifesto — 1-седмичен спринт

> Живо-актуализиран файл. Маркирай задачите като ✅ при завършване, ⏳ при in-progress, ⏸ ако паузирано.
> Версия: 2.0 · Дата: 2026-06-09
> ⚠️ **SCOPE РЕДУЦИРАН** с решение **D-10** (виж `DECISIONS.md`) — йерархията е смалена, срокът е ~1 седмица.

---

## Текущ статус

**Дата на рестарт:** 2026-06-09
**Целева дата на работещ MVP:** ~2026-06-16 (1 седмица)
**Текуща фаза:** Sprint Day 1 — Foundation

**Какво остава в scope (D-10):**
- Entities: **AppUser, DockerHost, Project, Service (+ PortMapping, EnvVar, Volume), WebApp, GeneratedFile** — 9 таблици.
- Generators: **docker-compose.yml + .env** (MUST), **Emmet export** (SHOULD — евтин и е в оригиналното задание).
- Auth: admin/viewer login, CSRF, session security.

**Какво е ИЗРЯЗАНО (D-10):**
- HardwareHost, Hypervisor, VirtualMachine → DockerHost е самостоятелен entity (без XOR FK).
- InfrastructureRole, InfrastructureUser.
- Network, ServiceNetwork (compose ползва default network).
- Dockerfile, vhost, README генератори.
- JSON import/export.
- Health check → stretch goal (само ако остане време).
- ZIP download → stretch goal.

---

## Day 1-2 — Foundation

### Setup
- [x] `git init`, `.gitignore`, GitHub repo (направено 2026-06-09).
- [ ] `composer.json` с PSR-4 autoload (`Manifesto\` → `src/`), `composer dump-autoload`.
- [ ] Folder структура: `public/`, `src/Core/`, `src/Models/`, `src/Repositories/`, `src/Controllers/`, `src/Services/`, `src/Views/`, `config/`, `db/`, `storage/logs/`, `storage/generated/`.

### Database
- [ ] `db/schema.sql` — 9 таблици: `app_user`, `docker_host`, `project`, `service`, `port_mapping`, `env_var`, `volume`, `web_app`, `generated_file`.
- [ ] `db/seed.sql` — admin + viewer users, 1 demo project (с 2 services, ports, envs).
- [ ] Импорт в XAMPP, валидация.

### Core (src/Core/)
- [ ] `EnvLoader.php`, `Database.php` (PDO singleton, utf8mb4), `Session.php`, `Csrf.php`.
- [ ] `Request.php`, `Response.php`, `Router.php`, `Auth.php`, `ViewRenderer.php`.

### Front controller + Auth
- [ ] `public/index.php`, `public/.htaccess`, `config/config.php`, `config/routes.php`, `.env.example`.
- [ ] `AuthController` (showLogin/login/logout) + login view + `AppUserRepository`.
- [ ] Base layout: `Views/layouts/app.php` + sidebar + topbar + минимален CSS.
- [ ] ✅ Verification: login/logout работи, CSRF token в form, wrong password → отказ.

---

## Day 3-4 — CRUD

> Pattern per entity: Model, Repository, Controller, Views (index/show/create/edit), маршрути.

- [ ] **DockerHost** — name, ip, os, docker_version, notes. Прост CRUD.
- [ ] **Project** — принадлежи на DockerHost. CRUD + list.
- [ ] **Service** — принадлежи на Project. CRUD с **inline управление** на PortMapping / EnvVar / Volume в edit формата.
- [ ] **WebApp** — принадлежи на Service. CRUD (name, url, dns, notes).
- [ ] Sidebar: просто дърво DockerHost → Project → Service → WebApp (рекурсивен partial, collapse по избор).
- [ ] Viewer role: скрити create/edit/delete бутони + 403 на POST.
- [ ] ✅ Verification: цяла верига DockerHost → Project → Service (2 env, 1 port, 1 volume) → WebApp създадена от UI.

---

## Day 5 — Generators

- [ ] `DockerComposeGenerator::generate($projectId)` — Project → Services → ports/envs/volumes → валиден YAML.
- [ ] `EnvFileGenerator::generate($projectId)` — `KEY=value` формат.
- [ ] `EmmetExporter::export($projectId)` — рекурсивен printer, modal с textarea + copy.
- [ ] `POST /projects/{id}/generate` — пуска генераторите, пише в `generated_file` с version_number++.
- [ ] Preview/download на генерираните файлове (страница с `<pre>` + download link per файл).
- [ ] ✅ Verification: `docker compose -f generated.yml config` минава.

---

## Day 6 — Polish

- [ ] Error pages (404, 403, 419, 500).
- [ ] Flash messages.
- [ ] Form validation + запазени стойности при грешка.
- [ ] Стилизация — консистентен изглед, без да се прекалява.

### Stretch (само ако има време)
- [ ] Health check — cURL + „Check now" бутон на WebApp.
- [ ] ZIP download на версия.
- [ ] Version history страница.

---

## Day 7 — Defense prep

- [ ] Root `README.md` (публична документация — какво е, как се пуска).
- [ ] Чист clone тест: clone → composer dump-autoload → import schema+seed → login → demo flow.
- [ ] Demo script: какво показваш, в какъв ред.
- [ ] **Махни `docs/` от repo-то** (`git rm -r docs/`).
- [ ] Преговор на очакваните въпроси (по-долу).

---

## Очаквани въпроси на защита (prep)

| Въпрос | Кратък отговор |
|---|---|
| „Защо без framework?" | Изискване на курса — да докажа разбиране на core езика. |
| „Защо MySQL вместо PostgreSQL?" | XAMPP идва с нея; по-малко setup на чужда машина (D-05). |
| „Реално ли управлява Docker?" | Не — генерира конфигурации. Safe-by-design. Реалното оркестриране е future work. |
| „Какъв е твоят threat model?" | Single-user local tool. SQL injection (PDO), XSS (htmlspecialchars), CSRF (token), session hijacking (regenerate). |
| „Защо няма HW/VM ниво?" | Съзнателно scope решение (D-10) — фокус върху Docker слоя, който е сърцето на заданието. HW/VM метаданните са future work. |
| „Защо Emmet read-only?" | Bidirectional parser е 12-18ч custom работа за един човек. Read-only постига духа на заданието (D-04). |
| „Защо нямаш unit tests?" | Time constraint. Има manual smoke test checklist. Test framework е future work. |

---

## Discovered issues / blockers (живо)

- (Празно.)

---

## Done log (chronological)

- **2026-05-14** — Phase 0 завършена: одобрен PRD, написана `docs/` структура.
- **2026-05-14** — Името избрано: **Manifesto** (D-09).
- **2026-06-09** — Git repo + GitHub (private, `darimachine/Manifesto`), `.gitignore`, `CLAUDE.md`.
- **2026-06-09** — **D-10: scope редуциран** — 1 седмица, 9 таблици, 2+1 генератора. TODO v2.0.
