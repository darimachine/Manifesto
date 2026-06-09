# Manifesto — Claude Code Context

> „Declare your infrastructure. Generate your stack."
> Курсов/дипломен проект. Автор: един студент, пише на български.

## Какво е проектът

Уеб dashboard на **vanilla PHP 8.x + MySQL/MariaDB** (без framework), който описва Docker-базирана инфраструктура и генерира конфигурационни файлове (`docker-compose.yml`, `.env`, Emmet export).

PSR-4 namespace: `Manifesto\`. MySQL DB name: `manifesto`. Hosting за защита: **XAMPP** на лаптоп (Apache `mod_rewrite`, DocumentRoot → `public/`).

## ⚡ Протокол за сесии (ЗАДЪЛЖИТЕЛНО)

1. **В началото на сесия:** прочети [`.claude/PROGRESS.md`](.claude/PROGRESS.md) — там пише какво е направено и какво е СЛЕДВАЩОТО действие.
2. **В края на сесия / след milestone:** добави запис най-отгоре в `.claude/PROGRESS.md` (направено / следващо / проблеми) и обнови checkbox-овете в `docs/TODO.md`.
3. **Git/push/setup команди:** потребителят ги изпълнява САМ — давай му готови команди, не ги пускай ти. Edit/Write на код файлове е ОК.

## Текущ статус

**⚠️ D-10 (2026-06-09): Scope редуциран — 1 седмица спринт.** Йерархията е смалена до: **DockerHost → Project → Service (+ ports/env/volumes) → WebApp**, 9 таблици общо. Изрязани: HW/Hypervisor/VM, InfraRole/InfraUser, Network, Dockerfile/vhost/README генератори, JSON import/export.

Актуален план: [`docs/TODO.md`](docs/TODO.md) (v2.0, day-by-day). Журнал: [`.claude/PROGRESS.md`](.claude/PROGRESS.md).

## Навигация в docs/

| Питаш се за... | Прочети |
|---|---|
| Какво е направено, какво следва СЕГА | [`.claude/PROGRESS.md`](.claude/PROGRESS.md) + [`docs/TODO.md`](docs/TODO.md) |
| Какво е проектът, защо съществува, речник | [`docs/PROJECT_CONTEXT.md`](docs/PROJECT_CONTEXT.md) |
| Защо е взето дадено решение | [`docs/DECISIONS.md`](docs/DECISIONS.md) — **D-10 е критичното** |
| Архитектура, слоеве, request flow, инварианти | [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) (чети през D-10 банера) |
| Схема на БД | [`docs/DATABASE_SCHEMA.md`](docs/DATABASE_SCHEMA.md) (чети през D-10 банера — само 9-те таблици) |
| Setup на нова машина | [`docs/SETUP_AND_DEPLOYMENT.md`](docs/SETUP_AND_DEPLOYMENT.md) |
| Изисквания (оригинални, преди D-10) | [`docs/PRODUCT_REQUIREMENTS.md`](docs/PRODUCT_REQUIREMENTS.md) |
| Идеи за след MVP | [`docs/FUTURE_WORK.md`](docs/FUTURE_WORK.md) |

## Ключови правила (не нарушавай)

- **Без framework** — изрично изискване на курса. Не предлагай Laravel/Symfony.
- **Без vendor зависимости** — `composer.json` само за PSR-4 autoload.
- **PDO + prepared statements навсякъде** — без raw query string concat.
- **Controllers не правят PDO** — само през Repository. **Views не правят PDO** — данните идват от Controller.
- **Models са POPO/DTO** — само properties, без логика.
- **Generators са stateless** — данни → низ. Без I/O вътре.
- **DockerHost е самостоятелен entity** (D-10) — БЕЗ връзка към VM/HW, без XOR.
- **Не добавяй изрязаните entities** (HW, Hypervisor, VM, InfraRole, InfraUser, Network) — D-10.
- **Emmet е read-only export** — без parser (D-04).
- **Health check е stretch goal** — само ръчен бутон, без cron/polling (D-07).
- **Не предлагай PostgreSQL** — D-05, XAMPP compatibility.

## Сигурност (задължително)

Всеки form има CSRF hidden input. Views escape-ват с `htmlspecialchars()`. Passwords с `password_hash()`. Session regenerate след login.

## Преди защитата (не сега)

Папката `docs/` ще се **махне от repo-то** преди защитата (`git rm -r docs/`) — тя е вътрешна. Публичен `README.md` в root-а се пише на Day 7.
