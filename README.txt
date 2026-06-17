Manifesto - Web Programming Final Project (FMI Sofia University)
==================================================================

Course: Web Programming, FMI Sofia University, 2025/2026 summer semester
Project name: Manifesto - Declare your infrastructure. Generate your stack.
Status: FINAL


CONTENTS OF THIS ARCHIVE
------------------------

  src/                  PHP source code (PSR-4 autoload, no framework)
    Core/               Router, Auth, Database, Session, CSRF, etc.
    Models/             POPO/DTO classes for all 9 entities
    Repositories/       PDO data-access layer (all SQL queries here)
    Services/           Generators: docker-compose, .env, Emmet
    Controllers/        HTTP controllers (Auth, CRUD, Generation)
    Views/              PHP templates (layouts, partials, forms, errors)

  public/               Web entry point (DocumentRoot)
    index.php           Front controller
    .htaccess           Apache mod_rewrite rules
    assets/             CSS + JS (no external CDN, fully offline)

  config/
    config.php          Single configuration entry point (reads .env)
    routes.php          Route table (METHOD, pattern, controller, action, access)

  db/
    schema.sql          9 tables: app_user, docker_host, project, service,
                        port_mapping, env_var, volume, web_app, generated_file
    seed.sql            Demo data: admin/viewer + 2 demo projects

  docs/
    DEFENSE_DOCUMENTATION.md   Project documentation (per FMI template)
    PROMPTS.md          Notable prompts log (per lecturer requirement)

  storage/              Writable runtime directories
    logs/               Error logs (.gitkeep present)
    generated/          Generated file cache (.gitkeep present)

  Dockerfile            Container build recipe (php:8.2-apache + pdo_mysql)
  docker-compose.yml    Local stack: app + mariadb services
  .env.example          Configuration template (copy to .env)
  .htaccess             Root-level forwarder to public/
  composer.json         PSR-4 autoload only (no vendor dependencies)
  README.md             Public README (Markdown, with formatting)
  README.txt            This file


SETUP - XAMPP (recommended for the exam)
----------------------------------------

  1. Copy this folder to XAMPP htdocs:
       Windows : C:\xampp\htdocs\manifesto\
       macOS   : /Applications/XAMPP/htdocs/manifesto/
       Linux   : /opt/lampp/htdocs/manifesto/

  2. Start Apache and MySQL from the XAMPP Control Panel.

  3. Open phpMyAdmin (http://localhost/phpmyadmin) and import:
       - db/schema.sql  (creates database "manifesto" with 9 tables)
       - db/seed.sql    (creates admin/viewer users + demo projects)

     Or via command line (faster):
       mysql -u root < db/schema.sql
       mysql -u root manifesto < db/seed.sql

  4. Copy environment template:
       cp .env.example .env
     (XAMPP defaults work out of the box - root user, empty password)

  5. Open in browser:
       http://localhost/manifesto/

  6. Login credentials:
       admin  / admin123       (full access)
       viewer / viewer123      (read-only)


SETUP - Docker (alternative)
----------------------------

  1. cp .env.example .env
  2. docker compose up -d
  3. Wait ~30 seconds for the database to initialize
  4. Open http://localhost:8080/
  5. Login with admin / admin123

  Database is auto-initialized from db/schema.sql + db/seed.sql on first run.

  To reset to a clean state:  docker compose down -v && docker compose up -d


SUBFOLDER DEPLOYMENT
--------------------

  The application auto-detects its base path. It works identically when
  deployed to a subfolder, e.g.:

       http://localhost/w26/manifesto-fnXXXXX/

  No configuration changes required. The Request::basePath() method in
  src/Core/Request.php detects the prefix from $_SERVER['SCRIPT_NAME']
  and prepends it to all generated URLs.


TECHNOLOGIES
------------

  - PHP 8.1+ (vanilla, no framework - per course requirement)
  - MySQL 8.0+ / MariaDB 10.4+
  - HTML5 + CSS3 + vanilla JavaScript (no jQuery, no React)
  - PDO with prepared statements (SQL injection protection)
  - Apache 2.4 with mod_rewrite
  - Docker (optional alternative deployment)


SECURITY
--------

  - SQL injection      : PDO prepared statements throughout
  - XSS                : All output escaped via htmlspecialchars() helper
  - CSRF               : Hidden token on every form, central verification
  - Session hijacking  : session_regenerate_id() after login
  - Password storage   : password_hash() / password_verify() (bcrypt)
  - Authorization      : Role-based access control (admin / viewer)


REQUIREMENTS COMPLIANCE
-----------------------

  All settings live in .env (single configuration source per requirement).
  No external CDN or font dependencies (works fully offline).
  Tested with PHP 8.1+ and MariaDB 10.11.
  Application docker-ized; can also be deployed to plain XAMPP / LAMP.


NOTES FOR EVALUATOR
-------------------

  - The app does NOT orchestrate Docker containers - it generates
    docker-compose.yml / .env / Emmet export from the data described
    via the dashboard. This is intentional (safety).

  - The "generated_file" table stores every generation as a versioned
    snapshot. Users can view history and download any prior version.

  - The Emmet export uses UTF-8 box-drawing characters for a tree view
    of the entire infrastructure hierarchy.

  - The seed data includes multiple demo projects so the dashboard,
    sidebar tree, and generators are immediately demonstrable after
    a fresh install.


STATUS
------

  FINAL
