<?php

declare(strict_types=1);

namespace Manifesto\Repositories;

use Manifesto\Core\Database;
use Manifesto\Models\Service;

final class ServiceRepository
{
    public function find(int $id): ?Service
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, project_id, name, image, restart_policy, notes, created_at, updated_at
             FROM service WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : Service::fromRow($row);
    }

    /** Service row + project_name (for breadcrumbs/links), or null. */
    public function findWithProject(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT s.id, s.project_id, s.name, s.image, s.restart_policy, s.notes,
                    s.created_at, s.updated_at, p.name AS project_name
             FROM service s
             INNER JOIN project p ON p.id = s.project_id
             WHERE s.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @param array{project_id:int,name:string,image:string,restart_policy:string,notes:?string} $data */
    public function insert(array $data): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO service (project_id, name, image, restart_policy, notes)
             VALUES (:project_id, :name, :image, :restart_policy, :notes)'
        );
        $stmt->execute([
            'project_id'     => $data['project_id'],
            'name'           => $data['name'],
            'image'          => $data['image'],
            'restart_policy' => $data['restart_policy'],
            'notes'          => $data['notes'],
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array{name:string,image:string,restart_policy:string,notes:?string} $data */
    public function update(int $id, array $data): bool
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE service
             SET name = :name, image = :image, restart_policy = :restart_policy, notes = :notes
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'             => $id,
            'name'           => $data['name'],
            'image'          => $data['image'],
            'restart_policy' => $data['restart_policy'],
            'notes'          => $data['notes'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM service WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /** UNIQUE(project_id, name) check; $ignoreId excludes the row being edited. */
    public function nameExistsInProject(string $name, int $projectId, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM service WHERE project_id = :project_id AND name = :name';
        $params = ['project_id' => $projectId, 'name' => $name];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function projectName(int $projectId): ?string
    {
        $stmt = Database::pdo()->prepare('SELECT name FROM project WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $projectId]);
        $name = $stmt->fetchColumn();
        return $name === false ? null : (string) $name;
    }

    /** @return array<int,array{id:int,name:string}> Web apps of a service (show page). */
    public function webAppsOf(int $serviceId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, name FROM web_app WHERE service_id = :service_id ORDER BY name'
        );
        $stmt->execute(['service_id' => $serviceId]);
        return $stmt->fetchAll();
    }
}
