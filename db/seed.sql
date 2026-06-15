-- Manifesto — seed data
-- Logins: admin / admin123  ·  viewer / viewer123
USE manifesto;

INSERT INTO app_user (username, password_hash, role, display_name) VALUES
('admin',  '$2y$10$O8lIjZtI0PQvAA.NnV47EOlQkvdCfP//LAPGKpGA1CX3cX4igV6vK', 'admin',  'Administrator'),
('viewer', '$2y$10$RLYazPD0dRJV0XQyAY3zquott/zovY2fThQc7qBABEpoN12RsBgV6', 'viewer', 'Read-only Viewer');

-- ─── Docker hosts ────────────────────────────────────────────────────────
INSERT INTO docker_host (id, name, ip_address, os, docker_version, notes) VALUES
(1, 'local-dev',     '127.0.0.1',     'Ubuntu 24.04',  '28.0', 'Demo Docker host (laptop).'),
(2, 'staging-eu',    '10.0.12.45',    'Debian 12',     '27.5', 'Internal staging server.'),
(3, 'production-01', '203.0.113.12',  'Rocky Linux 9', '27.5', 'Production cluster node #1.');

-- ─── Projects ────────────────────────────────────────────────────────────
INSERT INTO project (id, docker_host_id, name, slug, description) VALUES
(1, 1, 'Demo Blog',      'demo-blog',      'Example two-service stack: nginx web + MariaDB database.'),
(2, 2, 'Internal Tools', 'internal-tools', 'Wiki + chat for the engineering team.'),
(3, 3, 'Online Shop',    'online-shop',    'Production e-commerce stack with Redis cache.');

-- ─── Services ────────────────────────────────────────────────────────────
INSERT INTO service (id, project_id, name, image, restart_policy, notes,
    command, working_dir, depends_on, build_context, dockerfile_content,
    healthcheck_cmd, healthcheck_interval, network_mode) VALUES
-- Demo Blog (project 1)
(1, 1, 'web',     'nginx:alpine',         'unless-stopped', 'Static web frontend.',
    'nginx -g \'daemon off;\'', NULL, NULL, NULL, NULL, NULL, '30s', NULL),
(2, 1, 'db',      'mariadb:10.11',        'always',         'Database for the blog.',
    NULL, NULL, NULL, NULL, NULL, NULL, '30s', NULL),
-- Internal Tools (project 2)
(3, 2, 'wiki',    'requarks/wiki:2',      'unless-stopped', 'Wiki.js — documentation hub.',
    NULL, NULL, NULL, NULL, NULL, NULL, '30s', NULL),
(4, 2, 'chat',    'rocketchat/rocket.chat:6', 'unless-stopped', 'Internal team chat.',
    NULL, NULL, NULL, NULL, NULL, NULL, '30s', NULL),
(5, 2, 'mongo',   'mongo:7',              'always',         'MongoDB for Rocket.Chat.',
    NULL, NULL, NULL, NULL, NULL, NULL, '30s', NULL),
-- Online Shop (project 3)
(6, 3, 'frontend', 'shop/frontend:1.4',   'unless-stopped', 'Customer-facing storefront.',
    NULL, '/app', NULL, './frontend',
    'FROM node:20-alpine\nWORKDIR /app\nCOPY . .\nRUN npm ci --production\nCMD ["node", "server.js"]',
    NULL, '30s', NULL),
(7, 3, 'api',      'shop/api:1.4',        'unless-stopped', 'REST API + business logic.',
    NULL, NULL, 'postgres,redis', NULL, NULL,
    'curl -f http://localhost:8080/health || exit 1', '30s', NULL),
(8, 3, 'postgres', 'postgres:16',         'always',         'Primary application database.',
    NULL, NULL, NULL, NULL, NULL, NULL, '30s', NULL),
(9, 3, 'redis',    'redis:7-alpine',      'always',         'Cache + session store.',
    NULL, NULL, NULL, NULL, NULL, NULL, '30s', NULL);

-- ─── Port mappings ───────────────────────────────────────────────────────
INSERT INTO port_mapping (service_id, host_port, container_port, protocol) VALUES
-- Demo Blog
(1, 8080, 80, 'tcp'),
-- Internal Tools
(3, 3000, 3000, 'tcp'),  -- wiki
(4, 3001, 3000, 'tcp'),  -- chat
-- Online Shop
(6, 80,   3000, 'tcp'),  -- frontend
(7, 8081, 8080, 'tcp');  -- api

-- ─── Environment variables ───────────────────────────────────────────────
INSERT INTO env_var (service_id, key_name, value, is_secret) VALUES
-- Demo Blog db
(2, 'MYSQL_ROOT_PASSWORD', 'change-me-secret', 1),
(2, 'MYSQL_DATABASE',      'blog',             0),
-- Wiki (uses postgres but here we keep it minimal)
(3, 'DB_TYPE',     'mongo',                                 0),
(3, 'DB_HOST',     'mongo',                                 0),
(3, 'DB_NAME',     'wiki',                                  0),
-- Rocket.Chat
(4, 'ROOT_URL',    'http://localhost:3001',                 0),
(4, 'MONGO_URL',   'mongodb://mongo:27017/rocketchat',      0),
(4, 'PORT',        '3000',                                  0),
-- Online Shop frontend
(6, 'API_BASE_URL',  'http://api:8080',                     0),
(6, 'STRIPE_PK',     'pk_test_51XXXX',                      0),
-- Online Shop api
(7, 'DB_DSN',        'pgsql:host=postgres;dbname=shop',     0),
(7, 'DB_USER',       'shop',                                0),
(7, 'DB_PASS',       'shop-prod-rotateme',                  1),
(7, 'REDIS_URL',     'redis://redis:6379/0',                0),
(7, 'JWT_SECRET',    'rotate-me-please',                    1),
-- Postgres
(8, 'POSTGRES_DB',       'shop',                            0),
(8, 'POSTGRES_USER',     'shop',                            0),
(8, 'POSTGRES_PASSWORD', 'shop-prod-rotateme',              1);

-- ─── Volumes ─────────────────────────────────────────────────────────────
INSERT INTO volume (service_id, host_path, container_path, mode) VALUES
-- Demo Blog
(1, './html',      '/usr/share/nginx/html',  'ro'),
(2, './db-data',   '/var/lib/mysql',         'rw'),
-- Internal Tools
(5, './mongo-data', '/data/db',              'rw'),
-- Online Shop
(8, './pg-data',    '/var/lib/postgresql/data', 'rw'),
(9, './redis-data', '/data',                 'rw');

-- ─── Web apps ────────────────────────────────────────────────────────────
INSERT INTO web_app (service_id, name, public_url, dns_name, notes,
    status, last_checked_at, last_http_code, last_duration_ms) VALUES
-- Demo Blog
(1, 'Blog Frontend',   'http://localhost:8080',  'blog.local',     'Demo public endpoint.',
    'up', NOW(), 200, 42),
-- Internal Tools
(3, 'Engineering Wiki','http://wiki.example.eu', 'wiki.example.eu', 'Documentation hub.',
    'unknown', NULL, NULL, NULL),
(4, 'Team Chat',       'http://chat.example.eu', 'chat.example.eu', 'Rocket.Chat instance.',
    'unknown', NULL, NULL, NULL),
-- Online Shop
(6, 'Shop Frontend',   'https://shop.example.com',     'shop.example.com',     'Customer storefront.',
    'unknown', NULL, NULL, NULL),
(7, 'Shop Public API', 'https://api.shop.example.com', 'api.shop.example.com', 'External integration API.',
    'unknown', NULL, NULL, NULL);
