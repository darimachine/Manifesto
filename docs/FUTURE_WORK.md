# FUTURE WORK

> Идеи и подобрения, които НЕ влизат в MVP, но са оставени за бъдещи итерации.
> Версия: 1.0 · Дата: 2026-05-14

---

## Когнитивно: разделение по приоритет

- **Tier 1** — Полезни добавки, които биха направили MVP-то значително по-силно. Логически следващи стъпки.
- **Tier 2** — По-сериозни extension-и, ако проектът продължи да се развива след защитата.
- **Tier 3** — Амбициозни / архитектурни промени, които биха превърнали MVP-то в реален продукт.

---

## Tier 1 — Естествени продължения

### F1. Bidirectional Emmet parser
- Импортиране на Emmet-подобен низ → автоматично създаване на цялата йерархия.
- Lexer + parser + AST → DB mapping.
- Поддръжка на `>`, `+`, `*N`, `[attr=value]`, `{text}`, `()`, `^`, `$`.
- Estimated effort: 12-18 часа.
- Стойност: Превръща Emmet нотацията в равноправен input към dashboard-а.

### F2. Reverse: import от съществуващ docker-compose.yml
- Качваш `docker-compose.yml` → системата го парсва (YAML парсер) → създава Project + Services + EnvVars + Ports + Volumes.
- Очаквана работа: 8-12ч.
- Стойност: Чудесен onboarding flow за нови потребители с готови compose файлове.

### F3. Automatic health checks
- PHP CLI скрипт, който се пуска от cron всеки X минути.
- Чете всички WebApp-и със `enabled_auto_check=true`, прави cURL пинг, записва в `health_check`.
- При промяна на статус → flash notification на следващия login.
- Effort: 4-6ч.

### F4. JS polling за live статус
- Когато потребител е на WebApp детайл страница, JS poll-ва /webapps/{id}/check ендпойнт всеки 30 секунди.
- Update в DOM без reload.
- Effort: 2-3ч.

### F5. Login throttling / rate limiting
- След 5 неуспешни опита от един IP → block за 5 минути.
- Записваме `login_attempts` таблица.
- Effort: 3-4ч.

### F6. Промяна на парола от UI
- Setting страница за всеки логнат user — current password + new password.
- Forgot password flow — отвъд MVP (без email integration).
- Effort: 2-3ч.

### F7. Pagination + filter + search на list views
- При >50 записа — pagination.
- Server-side search по name.
- Client-side filter (vanilla JS).
- Effort: 4-5ч.

### F8. Soft delete + recover
- Вместо DELETE — `deleted_at` колона.
- „Trash" page показва soft-deleted entities; възстановяване с бутон.
- Effort: 3-4ч.

---

## Tier 2 — Сериозни extension-и

### F9. Multi-tenancy / organizations
- `organization` таблица; всеки entity има `organization_id`.
- AppUser принадлежи към една или повече organizations с роли per org.
- Effort: 15-20ч + сериозен redesign на queries.

### F10. Granular RBAC matrix
- Permissions като отделна таблица: (role, resource, action) тройки.
- Admin UI за конфигуриране на permissions.
- Effort: 12-15ч.

### F11. Audit log
- Всяко write действие → запис в `audit_log` (user_id, action, entity_type, entity_id, before, after, timestamp).
- Admin page за разглеждане и филтриране.
- Effort: 6-8ч.

### F12. Public API + tokens
- REST API за всеки entity (GET, POST, PUT, DELETE).
- Token-based auth (Bearer header).
- Rate limiting.
- OpenAPI документация.
- Effort: 15-20ч.

### F13. Email notifications
- При промяна на webapp статус → email до admin.
- При неуспешен login → email до user.
- Изисква SMTP интеграция (PHPMailer или ръчен `mail()` подход).
- Effort: 4-6ч.

### F14. Two-factor authentication
- TOTP с Google Authenticator.
- QR код за setup.
- Backup кодове.
- Effort: 6-8ч.

### F15. Export като ZIP с истинска папкова структура
- Сега expor е JSON. Идея: експортирай цял proect като деплойваем ZIP с docker-compose.yml + .env + Dockerfile-и + README + папки за всеки service (build_context).
- Effort: 4-5ч.

### F16. Generated file diff view
- При нова версия — visual diff (line-by-line) между новата и предишната версия.
- Effort: 5-6ч (използваме custom diff алгоритъм или малка JS lib).

---

## Tier 3 — Архитектурни / амбициозни

### F17. Реално оркестриране (с консент модел)
- Системата може да изпълнява `docker compose up` / `down` на отдалечен Docker host.
- Изисква: SSH ключове, secure agent на target машината, или Docker daemon API.
- **ВНИМАНИЕ:** Сериозно security exposure. Само при ясна authorization story.
- Effort: 25-40ч + ongoing security maintenance.

### F18. WebSocket / SSE realtime updates
- При промяна в БД → push до всички свързани клиенти.
- Изисква дългоживеещи сесии — за PHP с FrankenPHP или ReactPHP, или fronting със Node.js.
- Effort: 15-20ч.

### F19. Prometheus exporter
- Метрики за health check резултати, generation counts, user activity.
- Endpoint `/metrics` с Prometheus формат.
- Effort: 4-5ч.

### F20. Containerized deployment на самия dashboard
- Dockerfile + docker-compose.yml за dashboard-а самия.
- „Meta": проектът, който управлява Docker конфигурации, живее в Docker.
- Effort: 3-5ч.

### F21. Integration с Git
- При „Generate" — commit-ва генерираните файлове в Git repository.
- Branch per Project, commit per version.
- Effort: 8-12ч.

### F22. Темплейти за чести deployment patterns
- „WordPress + MySQL", „PHP + Apache + MySQL", „Node.js + Postgres" — pre-built templates, които потребителят клонира.
- Effort: 6-10ч за template engine + 5-10 sample templates.

### F23. Migration от друг tool
- Importer от Portainer stack JSON.
- Importer от Compose stack export от Docker Desktop.
- Effort: 8-15ч.

### F24. Mobile responsive UI
- Sidebar collapse, touch-friendly forms, mobile-first redesign.
- Effort: 10-15ч (зависи от текущото състояние на CSS).

### F25. i18n
- Поддръжка на български и английски.
- Translation файлове.
- Language switcher.
- Effort: 8-10ч.

---

## Какво НИКОГА не би трябвало да правим

- ❌ Изпълнение на user-provided shell команди.
- ❌ Reverse shell или remote execution feature.
- ❌ Съхранение на plain text пароли в DB.
- ❌ Telemetry към външни сървъри без opt-in.
- ❌ Cryptocurrency / blockchain integration. 😄

---

## Roadmap suggestion (ако проектът се развива)

**v1.0 (MVP — за защита):** Текущият PRD.
**v1.1:** F2 (compose import), F3 (auto checks), F6 (password change).
**v1.2:** F1 (Emmet parser), F11 (audit log), F15 (real ZIP).
**v2.0:** F9 (multi-tenancy), F10 (granular RBAC), F12 (public API).
**v3.0:** F17 (real orchestration) — само ако има реална нужда и security model.
