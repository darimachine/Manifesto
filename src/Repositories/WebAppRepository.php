<?php

declare(strict_types=1);

namespace Manifesto\Repositories;

use Manifesto\Core\Database;
use Manifesto\Models\WebApp;

final class WebAppRepository
{
    public function find(int $id): ?WebApp
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, service_id, name, public_url, dns_name, notes
             FROM web_app WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : WebApp::fromRow($row);
    }

    /**
     * Web app row JOINed with its service and project (for breadcrumbs).
     *
     * @return array<string,mixed>|null
     */
    public function findWithContext(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT w.id, w.service_id, w.name, w.public_url, w.dns_name,
                    w.notes, w.created_at, w.updated_at,
                    s.name AS service_name,
                    p.id   AS project_id, p.name AS project_name
             FROM web_app w
             JOIN service s ON s.id = w.service_id
             JOIN project p ON p.id = s.project_id
             WHERE w.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @param array<string,mixed> $data */
    public function insert(array $data): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO web_app (service_id, name, public_url, dns_name, notes)
             VALUES (:service_id, :name, :public_url, :dns_name, :notes)'
        );
        $stmt->execute([
            'service_id' => $data['service_id'],
            'name'       => $data['name'],
            'public_url' => $data['public_url'],
            'dns_name'   => $data['dns_name'],
            'notes'      => $data['notes'],
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): bool
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE web_app
             SET name = :name, public_url = :public_url,
                 dns_name = :dns_name, notes = :notes
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'         => $id,
            'name'       => $data['name'],
            'public_url' => $data['public_url'],
            'dns_name'   => $data['dns_name'],
            'notes'      => $data['notes'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM web_app WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Service + its project (for the create form breadcrumb).
     *
     * @return array<string,mixed>|null
     */
    public function serviceContext(int $serviceId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT s.id   AS service_id, s.name AS service_name,
                    p.id   AS project_id, p.name AS project_name
             FROM service s
             JOIN project p ON p.id = s.project_id
             WHERE s.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $serviceId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
