# PROGRESS — сесиен журнал

> **За Claude:** Чети този файл в НАЧАЛОТО на всяка сесия (CLAUDE.md те препраща тук). Пиши нов запис В КРАЯ на всяка работна сесия и при всеки завършен milestone. Най-новото е НАЙ-ОТГОРЕ. Формат: дата, какво е направено, какво е СЛЕДВАЩОТО конкретно действие, отворени проблеми.

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
