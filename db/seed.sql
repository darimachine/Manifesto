-- Manifesto — seed data
-- Logins: admin / admin123  ·  viewer / viewer123
USE manifesto;

INSERT INTO app_user (username, password_hash, role, display_name) VALUES
('admin',  '$2y$10$O8lIjZtI0PQvAA.NnV47EOlQkvdCfP//LAPGKpGA1CX3cX4igV6vK', 'admin',  'Administrator'),
('viewer', '$2y$10$RLYazPD0dRJV0XQyAY3zquott/zovY2fThQc7qBABEpoN12RsBgV6', 'viewer', 'Read-only Viewer');

INSERT INTO docker_host (id, name, ip_address, os, docker_version, notes) VALUES
(1, 'local-dev', '127.0.0.1', 'Ubuntu 24.04', '28.0', 'Demo Docker host (laptop).');

INSERT INTO project (id, docker_host_id, name, slug, description) VALUES
(1, 1, 'Demo Blog', 'demo-blog', 'Example two-service stack: nginx web + MariaDB database.');

INSERT INTO service (id, project_id, name, image, restart_policy, notes) VALUES
(1, 1, 'web', 'nginx:alpine',   'unless-stopped', 'Static web frontend.'),
(2, 1, 'db',  'mariadb:10.11',  'always',         'Database for the blog.');

INSERT INTO port_mapping (service_id, host_port, container_port, protocol) VALUES
(1, 8080, 80, 'tcp');

INSERT INTO env_var (service_id, key_name, value, is_secret) VALUES
(2, 'MYSQL_ROOT_PASSWORD', 'change-me-secret', 1),
(2, 'MYSQL_DATABASE',      'blog',             0);

INSERT INTO volume (service_id, host_path, container_path, mode) VALUES
(1, './html',    '/usr/share/nginx/html', 'ro'),
(2, './db-data', '/var/lib/mysql',        'rw');

INSERT INTO web_app (service_id, name, public_url, dns_name, notes) VALUES
(1, 'Blog Frontend', 'http://localhost:8080', 'blog.local', 'Demo public endpoint.');
