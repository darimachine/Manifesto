-- Manifesto — database schema (9 tables, scope per DECISIONS.md D-10)
-- MySQL / MariaDB · InnoDB · utf8mb4

CREATE DATABASE IF NOT EXISTS manifesto
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE manifesto;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS generated_file;
DROP TABLE IF EXISTS web_app;
DROP TABLE IF EXISTS volume;
DROP TABLE IF EXISTS env_var;
DROP TABLE IF EXISTS port_mapping;
DROP TABLE IF EXISTS service;
DROP TABLE IF EXISTS project;
DROP TABLE IF EXISTS docker_host;
DROP TABLE IF EXISTS app_user;
SET FOREIGN_KEY_CHECKS = 1;

-- Dashboard users (login)
CREATE TABLE app_user (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(64)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','viewer') NOT NULL DEFAULT 'viewer',
    display_name  VARCHAR(128) NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_app_user_username (username)
) ENGINE=InnoDB;

-- Docker host — standalone entity (no VM/HW parents, see D-10)
CREATE TABLE docker_host (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(128) NOT NULL,
    ip_address     VARCHAR(45)  NULL,
    os             VARCHAR(128) NULL,
    docker_version VARCHAR(32)  NULL,
    notes          TEXT         NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Project = one Docker Compose project
CREATE TABLE project (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    docker_host_id INT UNSIGNED NOT NULL,
    name           VARCHAR(128) NOT NULL,
    slug           VARCHAR(128) NOT NULL,
    description    TEXT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_project_slug (slug),
    KEY idx_project_host (docker_host_id),
    CONSTRAINT fk_project_host FOREIGN KEY (docker_host_id)
        REFERENCES docker_host (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Service = one container in the compose project
CREATE TABLE service (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id     INT UNSIGNED NOT NULL,
    name           VARCHAR(128) NOT NULL,
    image          VARCHAR(255) NOT NULL,
    restart_policy ENUM('no','always','on-failure','unless-stopped') NOT NULL DEFAULT 'unless-stopped',
    notes          TEXT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_service_name_per_project (project_id, name),
    CONSTRAINT fk_service_project FOREIGN KEY (project_id)
        REFERENCES project (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE port_mapping (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id     INT UNSIGNED NOT NULL,
    host_port      INT UNSIGNED NOT NULL,
    container_port INT UNSIGNED NOT NULL,
    protocol       ENUM('tcp','udp') NOT NULL DEFAULT 'tcp',
    KEY idx_port_service (service_id),
    CONSTRAINT fk_port_service FOREIGN KEY (service_id)
        REFERENCES service (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE env_var (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id INT UNSIGNED NOT NULL,
    key_name   VARCHAR(128) NOT NULL,
    value      TEXT NULL,
    is_secret  TINYINT(1) NOT NULL DEFAULT 0,
    KEY idx_env_service (service_id),
    CONSTRAINT fk_env_service FOREIGN KEY (service_id)
        REFERENCES service (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE volume (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id     INT UNSIGNED NOT NULL,
    host_path      VARCHAR(255) NOT NULL,
    container_path VARCHAR(255) NOT NULL,
    mode           ENUM('rw','ro') NOT NULL DEFAULT 'rw',
    KEY idx_volume_service (service_id),
    CONSTRAINT fk_volume_service FOREIGN KEY (service_id)
        REFERENCES service (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Publicly reachable application attached to a service
CREATE TABLE web_app (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id INT UNSIGNED NOT NULL,
    name       VARCHAR(128) NOT NULL,
    public_url VARCHAR(255) NULL,
    dns_name   VARCHAR(255) NULL,
    notes      TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_webapp_service (service_id),
    CONSTRAINT fk_webapp_service FOREIGN KEY (service_id)
        REFERENCES service (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Snapshot of every generated file (DB is the source of truth)
CREATE TABLE generated_file (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id     INT UNSIGNED NOT NULL,
    file_type      ENUM('docker-compose','env','emmet') NOT NULL,
    version_number INT UNSIGNED NOT NULL DEFAULT 1,
    content        LONGTEXT NOT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_genfile_project (project_id, version_number),
    CONSTRAINT fk_genfile_project FOREIGN KEY (project_id)
        REFERENCES project (id) ON DELETE CASCADE
) ENGINE=InnoDB;
