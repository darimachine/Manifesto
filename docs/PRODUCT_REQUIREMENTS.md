# PRODUCT REQUIREMENTS — Manifesto

> Пълен PRD на проекта. Версия: 1.1 · Дата: 2026-05-14 · Език: български.
> Този документ е огледално копие на одобрения plan в `~/.claude/plans/`, държано в проектното репо за достъп на developer-и и AI agents.
> Tagline: „Declare your infrastructure. Generate your stack."

---

## 1. Кратко описание

**Manifesto** е уеб приложение на vanilla PHP + MySQL, което позволява на потребител да опише структурирано цяла Docker-базирана инфраструктура през форми и да генерира реални конфигурационни файлове от тези данни. Системата проследява статус през ръчни HTTP health checks и пази версии на генерираните файлове.

## 2. Problem statement

Описването, генерирането и проследяването на Docker-базирани deployment конфигурации днес се прави главно с текстови редактори върху YAML файлове. Това е error-prone, няма единна точка на истината за връзките между инфраструктура (HW → VM → Container → WebApp) и липсва централна история на промените. Този проект решава проблема за един потребител на ниво единичен dashboard.

## 3. Target users

| Роля | Описание | Достъп |
|---|---|---|
| **Admin** | Моделира инфраструктура, генерира файлове, прави health checks. | CRUD на всичко. |
| **Viewer** | Чете конфигурацията, разглежда tree view, изтегля файлове. | Read-only. |

Двете роли са за **dashboard-а**. Това е различно от `InfrastructureRole`/`InfrastructureUser`, които са описателни метаданни.

## 4. Main use cases

- **UC-01** Описване на цялата инфраструктура от HW до WebApp.
- **UC-02** Генериране на `docker-compose.yml`.
- **UC-03** Генериране на пълен deployment пакет (.env, Dockerfile, vhost, README).
- **UC-04** JSON export → миграция → JSON import.
- **UC-05** Emmet-подобен export на йерархията.
- **UC-06** Ръчен health check на WebApp.
- **UC-07** История на генерираните файлове с download на стари версии.
- **UC-08** Viewer-достъп с read-only.
- **UC-09** Setup на нова машина за < 10 минути.

## 5. MVP Scope

### 5.1 Основни entities (10 главни + 4 child/log)

1. `AppUser` — login потребители на dashboard.
2. `HardwareHost` — физически сървър.
3. `Hypervisor` — над hardware.
4. `VirtualMachine` — над hypervisor.
5. `DockerHost` — над VM или директно върху HW (XOR).
6. `Project` — Docker Compose project.
7. `Service` — Docker service в project.
8. `WebApp` — публично достъпно приложение.
9. `InfrastructureRole` — описателна роля.
10. `InfrastructureUser` — описателен потребител.

Child/log таблици: `PortMapping`, `EnvVar`, `Volume`, `Network`, `ServiceNetwork` (join), `GeneratedFile`, `HealthCheck`.

### 5.2 Функционалности

- **F-01** Auth (login, logout, sessions, CSRF, hashed passwords, 2 роли).
- **F-02** CRUD за всички 10 entities.
- **F-03** Йерархична tree-view навигация.
- **F-04** Генератор на 6 файла: docker-compose.yml, .env, Dockerfile, vhost.conf, README.md, infrastructure.emmet.txt.
- **F-05** JSON import / export.
- **F-06** Version history с .zip download.
- **F-07** Ръчен health check (cURL, timeout 5s).
- **F-08** Emmet read-only export.

### 5.3 UI/UX

- Custom CSS, без Bootstrap.
- Sidebar tree view.
- Top bar с username + breadcrumb.
- Color status badges.
- Responsive до tablet.

## 6. Out of scope (MVP)

- Истинско оркестриране (никакви `docker` / `ssh` / `sudo` команди).
- Bidirectional Emmet parser.
- Cron auto health check.
- Multi-tenancy / teams.
- Granular RBAC matrix.
- Push notifications / email alerts.
- WebSocket realtime updates.
- Mobile responsive.
- Full audit log.
- 2FA.
- Public API.
- Online hosting с SSL.

## 7. Functional Requirements (FR)

| ID | Изискване | Приоритет |
|---|---|---|
| FR-01 | Login с username + password. | MUST |
| FR-02 | Session timeout 60 мин. | MUST |
| FR-03 | CRUD за всички 10 entities. | MUST |
| FR-04 | Генериране на docker-compose.yml. | MUST |
| FR-05 | Валиден YAML (passes `docker compose config`). | MUST |
| FR-06 | History на генерираните файлове. | MUST |
| FR-07 | JSON export на Project. | MUST |
| FR-08 | JSON import с валидация. | MUST |
| FR-09 | Manual health check. | MUST |
| FR-10 | Emmet read-only export. | MUST |
| FR-11 | Viewer не може да POST-ва (server-side guard). | MUST |
| FR-12 | Генериране на .env. | MUST |
| FR-13 | Генериране на Dockerfile per service. | SHOULD |
| FR-14 | Генериране на vhost. | SHOULD |
| FR-15 | Генериране на README.md. | SHOULD |
| FR-16 | Tree view с expand/collapse. | SHOULD |
| FR-17 | .zip download на пакет. | SHOULD |
| FR-18 | EnvVar `is_secret` flag със секретно скриване в UI. | MUST |

## 8. Non-Functional Requirements (NFR)

| ID | Изискване |
|---|---|
| NFR-01 | Setup на чужда машина < 10 мин. |
| NFR-02 | Само PDO prepared statements. |
| NFR-03 | Всички echo през `htmlspecialchars`. |
| NFR-04 | `password_hash` BCRYPT cost 12. |
| NFR-05 | CSRF на всички state-changing forms. |
| NFR-06 | List view ≤ 500ms при ≤ 100 записа. |
| NFR-07 | PSR-4 организация. |
| NFR-08 | UTF-8 end-to-end (`utf8mb4`). |
| NFR-09 | Error logging в `logs/error.log`, без leak. |
| NFR-10 | `.env` в `.gitignore`. |

## 9. Database schema

Виж отделния документ: `DATABASE_SCHEMA.md`.

## 10. File structure

Виж отделния документ: `ARCHITECTURE.md`.

## 11. Import / Export format

Виж отделния документ: `IMPORT_EXPORT_FORMAT.md`.

## 12. Health check стратегия

- Ръчно, бутон „Check now" на WebApp детайл.
- cURL GET, timeout 5s, follow redirects до 3.
- Запис: `status_code`, `latency_ms`, `error_message`, `checked_at`.
- UI: зелен 2xx-3xx, червен 4xx-5xx/timeout, сив никога не чекнат.
- Cleanup: пазим последните 100 чека per webapp.

## 13. Configuration / setup

Виж отделния документ: `SETUP_AND_DEPLOYMENT.md`.

## 14. Security considerations

- PDO prepared statements навсякъде.
- `password_hash` / `password_verify`.
- CSRF token per session.
- `htmlspecialchars` на всички echo.
- Session regenerate след login.
- HttpOnly + SameSite=Lax cookies.
- `.env` не се commit-ва.
- `is_secret` EnvVars маскирани като `***` за viewer.
- YAML output escaping за user input в docker-compose.yml.
- Role check server-side на всеки write endpoint.

## 15. Deployment considerations

- XAMPP/WAMP local hosting.
- Single-instance.
- DocumentRoot → `public/`.
- `mod_rewrite` enabled.
- MySQL `utf8mb4_unicode_ci`.
- PHP 8.1+ с pdo_mysql, curl, mbstring, openssl, zip extensions.

## 16. Risks & mitigations

| Риск | Веро. | Impact | Mitigation |
|---|---|---|---|
| Изпускане на deadline (15 entities). | Висока | Висок | Седмични milestones; режем FR-13/14/15 ако е нужно. |
| Невалиден YAML/Dockerfile output. | Средна | Висок | Тестване с `docker compose config`. |
| Сложни nullable FK queries. | Средна | Среден | DockerHost XOR constraint. |
| Бъркане dashboard/infra users. | Средна | Среден | Различни имена в таблиците и UI. |
| Buggy tree view с vanilla JS. | Средна | Среден | Минимална имплементация. |
| Загуба на работа без git. | Висока | Висок | Git init първи ден. |

## 17. Open questions

- Q1. Vhost nginx и/или apache? *(Започваме с nginx.)*
- Q2. Защитата изисква ли live `docker compose up`? *(Изясняваме с ръководителя.)*
- Q3. Колко детайлен audit log? *(MVP: само timestamps.)*
- Q4. Emmet export буквален формат? *(Опростена версия първоначално.)*

## 18. Future work

Виж отделния документ: `FUTURE_WORK.md`.

## 19. Verification (definition of done)

- ✅ Clean clone → XAMPP → 5 мин → working dashboard.
- ✅ Project с 3 services, 2 webapps, 5 env vars.
- ✅ Generate produces валиден `docker-compose.yml`.
- ✅ .zip download работи.
- ✅ Export → Import recovers identical hierarchy.
- ✅ Health check дава реален response.
- ✅ Viewer не може да POST даже с curl.
- ✅ Emmet export пълна йерархия.
- ✅ Всички docs/*.md попълнени.
