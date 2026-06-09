<?php

declare(strict_types=1);

namespace Manifesto\Repositories;

use Manifesto\Core\Database;
use Manifesto\Models\Project;

final class ProjectRepository
{
    /** @return array<int,array<string,mixed>> Projects with host_name and service_count. */
    public function allWithHost(): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT p.id, p.docker_host_id, p.name, p.slug, p.description,
                    p.created_at, p.updated_at,
                    h.name AS host_name,
                    COUNT(s.id) AS service_count
             FROM project p
             JOIN docker_host h ON h.id = p.docker_host_id
             LEFT JOIN service s ON s.project_id = p.id
             GROUP BY p.id, p.docker_host_id, p.name, p.slug, p.description,
                      p.created_at, p.updated_at, h.name
             ORDER BY p.name'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find(int $id): ?Project
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, docker_host_id, name, slug, description
             FROM project WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : Project::fromRow($row);
    }

    /** @return array<string,mixed>|null Full project row + host_name. */
    public function findWithHost(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT p.id, p.docker_host_id, p.name, p.slug, p.description,
                    p.created_at, p.updated_at,
                    h.name AS host_name
             FROM project p
             JOIN docker_host h ON h.id = p.docker_host_id
             WHERE p.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @return array<int,array<string,mixed>> Services of the project. */
    public function servicesOf(int $projectId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, name, image, restart_policy
             FROM service WHERE project_id = :project_id ORDER BY name'
        );
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /** Highest generated-file version for the project, 0 when none. */
    public function latestVersion(int $projectId): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT MAX(version_number) FROM generated_file WHERE project_id = :project_id'
        );
        $stmt->execute(['project_id' => $projectId]);
        return (int) $stmt->fetchColumn();
    }

    /** @param array{docker_host_id:int,name:string,slug:string,description:?string} $data */
    public function insert(array $data): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO project (docker_host_id, name, slug, description)
             VALUES (:docker_host_id, :name, :slug, :description)'
        );
        $stmt->execute([
            'docker_host_id' => $data['docker_host_id'],
            'name'           => $data['name'],
            'slug'           => $data['slug'],
            'description'    => $data['description'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @param array{docker_host_id:int,name:string,slug:string,description:?string} $data */
    public function update(int $id, array $data): bool
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE project
             SET docker_host_id = :docker_host_id, name = :name,
                 slug = :slug, description = :description
             WHERE id = :id'
        );
        return $stmt->execute([
            'docker_host_id' => $data['docker_host_id'],
            'name'           => $data['name'],
            'slug'           => $data['slug'],
            'description'    => $data['description'],
            'id'             => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM project WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM project WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** @return array<int,array<string,mixed>> Docker hosts for the <select>. */
    public function hostsForSelect(): array
    {
        $stmt = Database::pdo()->prepare('SELECT id, name FROM docker_host ORDER BY name');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
