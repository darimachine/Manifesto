# PROJECT_CONTEXT — Manifesto

> Контекстен документ — какво представлява този проект, защо съществува, кой го прави.
> Проект: **Manifesto** · Tagline: „Declare your infrastructure. Generate your stack."
> Версия: 1.1 · Дата: 2026-05-14

## Какво е това

**Manifesto** — уеб-базиран dashboard на vanilla PHP + MySQL, който позволява структурирано описание на цяла Docker-базирана инфраструктура (от hardware до webapp) и генерира реални конфигурационни файлове от тези данни (docker-compose.yml, .env, Dockerfile, vhost, README, Emmet export).

Името идва от двойното значение на думата:
- технически: Docker **image manifests** — структурираните метаданни на контейнерите;
- декларативно: **manifesto** — декларация на намеренията. Точно това прави потребителят: декларира как трябва да изглежда инфраструктурата му, а системата я материализира като реални файлове.

## Защо съществува

Курсов/дипломен проект по университетска тема **„Система за управление на Docker контейнери и приложения"**, зададена от ръководител, който описва редактор за импорт и редакция на данни за уеб сайт (DNS, порт, бд, vhost), сървър, виртуалка и Docker контейнер, с проследяване на статус и Emmet-подобен синтаксис за описание на йерархия.

Оригиналното задание е твърде широко за един студент за 3-4 седмици. Затова scope-ът се преориентира към **безопасна генератор-базирана** посока вместо production оркестратор. Виж `PRODUCT_REQUIREMENTS.md` за пълните рамки.

## Кой го прави

- **Автор:** един студент, работещ сам.
- **Асистент:** AI (Claude Opus 4.7) през Claude Code.
- **Ръководител:** университетски преподавател.

## Технически ключови решения (резюме)

- Vanilla PHP 8.x + MySQL/MariaDB. БЕЗ frameworks.
- PDO + prepared statements.
- XAMPP/WAMP за hosting на защитата.
- Пълна йерархия от 10 основни entities (HW → Hypervisor → VM → DockerHost → Project → Service → WebApp → InfraRole → InfraUser → AppUser).
- Dashboard auth с admin/viewer + отделни metadata users/roles.
- Read-only Emmet export, без parser.
- Manual health check (HTTP ping с бутон).

Пълен trace на решенията: `DECISIONS.md`.

## Свързани документи

| Документ | Цел |
|---|---|
| `PRODUCT_REQUIREMENTS.md` | Пълен PRD — функционални, нефункционални изисквания, use cases, scope. |
| `DECISIONS.md` | Trace на всички архитектурни и продуктови решения с причини. |
| `ARCHITECTURE.md` | Структура на кода, слоеве, компоненти. |
| `DATABASE_SCHEMA.md` | Схема на БД с типове, FK, индекси. |
| `SETUP_AND_DEPLOYMENT.md` | Как се пуска проектът на нова машина. |
| `IMPORT_EXPORT_FORMAT.md` | JSON формат за export/import. |
| `FUTURE_WORK.md` | Идеи отвъд MVP. |
| `TODO.md` | Седмични milestones и текущи задачи. |

## Терминологичен речник

| Термин | Значение |
|---|---|
| **AppUser** | Потребител на самия dashboard (admin/viewer). Има login. |
| **InfrastructureUser** | Потребител, описан като метаданни в инфраструктурата (напр. „admin на VM"). Не влиза в dashboard-а. |
| **Project** | Логически контейнер от services и webapps; съответства на един Docker Compose project. |
| **Service** | Docker service вътре в Project (= един container). |
| **WebApp** | Публично достъпно приложение, прикрепено към Service (има URL, DNS). |
| **DockerHost** | Среда, в която живеят Docker контейнерите — VM или bare metal. |
| **GeneratedFile** | Един снимков запис на конкретно генериран файл (с версия и timestamp). |
