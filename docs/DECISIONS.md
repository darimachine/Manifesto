# DECISIONS log

> Architecture Decision Records (ADR-style). Всяко решение има context, options, decision, consequences.
> Версия: 1.0 · Дата: 2026-05-14

---

## D-01 — Timeline & ангажимент

**Дата:** 2026-05-13
**Context:** Курсов проект, един човек, академичен график.
**Options:** A) 3-4 седмици × 10ч/седмично · B) 6-8 седмици × 10-15ч · C) 2-3 месеца × 15-20ч · D) 4+ месеца
**Decision:** A — 3-4 седмици, но автор пое ангажимент за гъвкавост (ще отделя „колкото трябва").
**Consequences:** При проблем с deadline се режат FR-13 (Dockerfile), FR-14 (vhost), FR-15 (README) — те са SHOULD, не MUST.

---

## D-02 — Дълбочина на йерархията

**Дата:** 2026-05-13
**Context:** Оригиналното задание описва дълбока йерархия HW → Hypervisor → VM → Docker → WebApp → Roles → Users. Реалното време за 15 таблици е ~110-140ч.
**Options:** A) Плосък модел · B) Server + WebApp · C) Пълна йерархия · D) InfrastructureNode self-ref hybrid
**Decision:** C — пълна йерархия от 10 основни entities.
**Risk acknowledged:** Авторът знае, че scope-ът е амбициозен. Митигация: weekly milestones, безпощадно режене ако се изоставаме.
**Consequences:**
- 10 entity таблици + 4 child/log = ~14 общо.
- DockerHost има XOR FK (vm_id ИЛИ hardware_host_id) — bare metal vs VM-ed Docker.
- Tree view трябва да поддържа 5+ нива дълбочина.

---

## D-03 — Auth модел

**Дата:** 2026-05-13
**Context:** „Users & roles" в оригиналния Emmet синтаксис означава различни неща на различни места.
**Options:** A) Само dashboard auth · B) Само infrastructure metadata · C) И двете прости · D) И двете пълни
**Decision:** C — dashboard auth с admin/viewer + отделни таблици `InfrastructureRole`/`InfrastructureUser` за описателни метаданни.
**Consequences:**
- Две напълно отделни концепции с различни имена в кода (`AppUser` vs `InfrastructureUser`).
- Без RBAC matrix — само две фиксирани роли admin/viewer.
- Сесии, CSRF, password hashing — задължителни.

---

## D-04 — Emmet нотация

**Дата:** 2026-05-13
**Context:** Ръководителят е скицирал Emmet-подобен синтаксис. Bidirectional parser е ~12-18ч.
**Options:** A) Не я правим · B) Read-only export · C) Bidirectional parser · D) Live editor
**Decision:** B — read-only export от йерархия към низ.
**Consequences:**
- Запазваме „духа" на оригиналното задание.
- Спестяваме 10+ часа парсер работа.
- На защита: „реализирах export; bidirectional parser е оставен като future work — custom parser-ите са bug-magnets за един човек".

---

## D-05 — База данни

**Дата:** 2026-05-14
**Context:** Технически constraint: MySQL ИЛИ PostgreSQL.
**Options:** MySQL/MariaDB · PostgreSQL
**Decision:** MySQL / MariaDB.
**Reason:** XAMPP/WAMP идват с MySQL по подразбиране — нулева настройка. PDO драйверът е стандартен в PHP. По-лесно за чужда машина.
**Consequences:** Charset `utf8mb4`, collation `utf8mb4_unicode_ci`. Без JSON columns (използваме TEXT + JSON encoding ръчно ако трябва).

---

## D-06 — Генерирани файлове

**Дата:** 2026-05-14
**Context:** Какви файлове да произвежда системата.
**Options:** Multi-select от {docker-compose.yml, .env, Dockerfile, vhost, README, Emmet .txt}
**Decision:** Всичките шест.
**Consequences:**
- 6 generator класа в `src/Services/Generators/`.
- ~10-15 часа работа общо.
- `docker-compose.yml` и `.env` са MUST, останалите SHOULD — режат се ако се изоставаме.

---

## D-07 — Health check

**Дата:** 2026-05-14
**Context:** Auto vs manual health checks.
**Options:** A) Само ръчен · B) + cron auto · C) + JS polling
**Decision:** A — само ръчен (бутон „Check now" на WebApp детайл).
**Reason:** Cron на чужда XAMPP машина за защита е fragile. JS polling работи само ако някой гледа таб.
**Consequences:**
- cURL към WebApp URL, timeout 5s, follow redirects до 3.
- Запис в `HealthCheck` таблица със status_code, latency_ms, error_message, timestamp.
- Cleanup стратегия: пазим последните 100 чека per webapp.

---

## D-08 — Hosting за защита

**Дата:** 2026-05-14
**Context:** Къде ще се демонстрира проектът пред преподавател.
**Options:** A) XAMPP/WAMP · B) PHP built-in · C) Docker meta · D) Online VPS
**Decision:** A — XAMPP/WAMP на лаптоп.
**Reason:** Най-сигурно. Без зависимост от мрежа или мрежови услуги.
**Consequences:**
- Apache + `mod_rewrite` + `.htaccess`.
- DocumentRoot сочи към `public/`.
- PHP 8.1+ с extensions: pdo_mysql, curl, mbstring, openssl, zip.

---

## D-09 — Име на проекта

**Дата:** 2026-05-14
**Context:** Досега работихме с описателно заглавие „Docker Compose Generator & Deployment Configuration Dashboard". Нужно е истинско име на продукта за README, slug, namespace, login title, защита.
**Options:** A) ComposeForge · B) Dockyard · C) StackScribe · D) Manifesto · E) DeployBlueprint · F) Кей (BG) · G) Чертеж (BG) · H) Скеле (BG)
**Decision:** **Manifesto**.
**Reason:** Двойно значение — технически (Docker image manifests) и декларативно (manifesto = декларация). Точно това прави системата: потребителят декларира инфраструктурата, dashboard-ът я материализира като файлове. Кратко, brand-able, лесно за произнасяне на български и английски.
**Consequences:**
- URL slug / folder: `manifesto`.
- PSR-4 namespace: `Manifesto\\`.
- MySQL database name: `manifesto`.
- Tagline: „Declare your infrastructure. Generate your stack."
- Login screen title: „Manifesto — Sign in".
- Всички docs/*.md файлове и plan документът обновени.

---

## Шаблон за бъдещи решения

```markdown
## D-XX — [Кратко заглавие]

**Дата:** YYYY-MM-DD
**Context:** [Защо имаме нужда от решение тук]
**Options:** A) ... · B) ... · C) ...
**Decision:** [Какво избрахме]
**Reason:** [Защо]
**Consequences:** [Какво следва от това]
```
