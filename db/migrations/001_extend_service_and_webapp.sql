-- Migration 001: extend service and web_app tables, update generated_file ENUM
-- Idempotent — safe to run multiple times on an existing installation.
USE manifesto;

-- ─── service: command ────────────────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'service' AND COLUMN_NAME = 'command');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE service ADD COLUMN command VARCHAR(512) NULL AFTER notes',
    'SELECT "service.command exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── service: working_dir ────────────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'service' AND COLUMN_NAME = 'working_dir');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE service ADD COLUMN working_dir VARCHAR(255) NULL AFTER command',
    'SELECT "service.working_dir exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── service: depends_on ─────────────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'service' AND COLUMN_NAME = 'depends_on');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE service ADD COLUMN depends_on VARCHAR(512) NULL COMMENT ''Comma-separated service names'' AFTER working_dir',
    'SELECT "service.depends_on exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── service: build_context ──────────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'service' AND COLUMN_NAME = 'build_context');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE service ADD COLUMN build_context VARCHAR(255) NULL COMMENT ''Path to Dockerfile context, e.g. ./api'' AFTER depends_on',
    'SELECT "service.build_context exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── service: dockerfile_content ─────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'service' AND COLUMN_NAME = 'dockerfile_content');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE service ADD COLUMN dockerfile_content LONGTEXT NULL COMMENT ''Inline Dockerfile content'' AFTER build_context',
    'SELECT "service.dockerfile_content exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── service: healthcheck_cmd ────────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'service' AND COLUMN_NAME = 'healthcheck_cmd');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE service ADD COLUMN healthcheck_cmd VARCHAR(512) NULL AFTER dockerfile_content',
    'SELECT "service.healthcheck_cmd exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── service: healthcheck_interval ───────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'service' AND COLUMN_NAME = 'healthcheck_interval');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE service ADD COLUMN healthcheck_interval VARCHAR(32) NULL DEFAULT ''30s'' AFTER healthcheck_cmd',
    'SELECT "service.healthcheck_interval exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── service: network_mode ───────────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'service' AND COLUMN_NAME = 'network_mode');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE service ADD COLUMN network_mode VARCHAR(64) NULL AFTER healthcheck_interval',
    'SELECT "service.network_mode exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── web_app: status ─────────────────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'web_app' AND COLUMN_NAME = 'status');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE web_app ADD COLUMN status ENUM(''unknown'',''up'',''down'',''error'') NOT NULL DEFAULT ''unknown'' AFTER notes',
    'SELECT "web_app.status exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── web_app: last_status_change ─────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'web_app' AND COLUMN_NAME = 'last_status_change');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE web_app ADD COLUMN last_status_change TIMESTAMP NULL AFTER status',
    'SELECT "web_app.last_status_change exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── web_app: last_checked_at ────────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'web_app' AND COLUMN_NAME = 'last_checked_at');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE web_app ADD COLUMN last_checked_at TIMESTAMP NULL AFTER last_status_change',
    'SELECT "web_app.last_checked_at exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── web_app: last_http_code ─────────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'web_app' AND COLUMN_NAME = 'last_http_code');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE web_app ADD COLUMN last_http_code INT NULL AFTER last_checked_at',
    'SELECT "web_app.last_http_code exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── web_app: last_duration_ms ───────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'manifesto' AND TABLE_NAME = 'web_app' AND COLUMN_NAME = 'last_duration_ms');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE web_app ADD COLUMN last_duration_ms INT NULL AFTER last_http_code',
    'SELECT "web_app.last_duration_ms exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─── generated_file: extend ENUM to include 'dockerfile' ─────────────────────
-- MODIFY COLUMN is safe to re-run; it is a no-op if the value is already present.
ALTER TABLE generated_file
    MODIFY COLUMN file_type ENUM('docker-compose','env','emmet','dockerfile') NOT NULL;
