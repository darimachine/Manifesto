# Manifesto

Уеб приложение за описание на Docker инфраструктура и автоматично генериране на конфигурационни файлове — `docker-compose.yml`, `.env` и Emmet текстов експорт на йерархията.

Идеята е проста: вместо да пишеш `docker-compose.yml` на ръка всеки път, описваш своите хостове, проекти и сървиси през уеб интерфейс, а приложението ти връща готовите файлове.

## Какво прави

- CRUD върху йерархия: Docker Host → Project → Service (с портове, env променливи, volumes) → Web App
- Генерира три файла за всеки проект:
  - `docker-compose.yml` (Compose v3.8)
  - `.env` групиран по сървис, с маркирани secret полета
  - Emmet експорт — текстово дърво на йерархията
- Пази история на всяка генерация — може да се върнеш към предишна версия
- Sidebar дърво за бърза навигация
- Двe роли: `admin` (пълни права) и `viewer` (само четене)

## Технологии

- PHP 8.1+ (vanilla, без framework)
- MySQL 8 / MariaDB 10.4+
- HTML, CSS, vanilla JavaScript (без CDN)
- PDO с prepared statements, CSRF на всеки POST, `password_hash` за паролите

## Инсталация през XAMPP

1. Постави папката в `htdocs`:

   ```
   /Applications/XAMPP/htdocs/manifesto/    (macOS)
   C:\xampp\htdocs\manifesto\               (Windows)
   ```

2. Копирай `.env`:

   ```bash
   cp .env.example .env
   ```

   Default-ите работят с XAMPP без промени (`root` / празна парола).

3. Импортирай схемата и началните данни през phpMyAdmin (`http://localhost/phpmyadmin`):

   - първо `db/schema.sql`
   - после `db/seed.sql`

   Или през CLI:

   ```bash
   mysql -u root < db/schema.sql
   mysql -u root manifesto < db/seed.sql
   ```

4. Отвори `http://localhost/manifesto/` и влез с:

   - admin / admin123
   - viewer / viewer123

## Инсталация през Docker

```bash
cp .env.example .env
docker compose up -d
```

- Приложение: `http://localhost:8080`
- БД на host port `33060` (за DBeaver / phpMyAdmin)
- Схемата и seed-ът се зареждат автоматично при първото вдигане

Спиране:

```bash
docker compose down       # пази данните
docker compose down -v    # изтрива всичко
```

## Структура

```
Manifesto/
├── public/              DocumentRoot — front controller, .htaccess, assets
├── src/
│   ├── Core/            Router, Auth, Database, Session, CSRF
│   ├── Models/          DTO класове
│   ├── Repositories/    PDO заявки
│   ├── Services/        генератори (compose, env, emmet)
│   ├── Controllers/     HTTP логика
│   └── Views/           PHP шаблони
├── config/              config.php, routes.php
├── db/                  schema.sql, seed.sql
├── storage/             логове и кеш на генерираните файлове
└── docs/                ръководство и допълнителна документация
```

## Принципи

- Никаква бизнес логика във Views — те получават готови данни от Controller-а
- Никакво PDO в Controllers — всичко минава през Repository
- Generators са stateless: вход → низ, без I/O
- CSRF се валидира централно в `public/index.php`
- Models са POPO — само properties и конструктор

## Default credentials

| Username | Password    | Роля   |
|----------|-------------|--------|
| admin    | admin123    | admin  |
| viewer   | viewer123   | viewer |

Сменете ги, ако пускате приложението някъде извън локалната машина.

