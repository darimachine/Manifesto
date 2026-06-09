<?php

declare(strict_types=1);

namespace Manifesto\Repositories;

use Manifesto\Core\Database;
use Manifesto\Models\DockerHost;

final class DockerHostRepository
{
    /**
     * All hosts with the number of projects on each.
     *
     * @return array<int,array<string,mixed>> Assoc rows incl. project_count.
     */
    public function allWithProjectCounts(): array
    {
        $sql = 'SELECT h.id, h.name, h.ip_address, h.os, h.docker_version,
                       h.notes, h.created_at, h.updated_at,
                       COUNT(p.id) AS project_count
                FROM docker_host h
                LEFT JOIN project p ON p.docker_host_id = h.id
                GROUP BY h.id
                ORDER BY h.name';
        return Database::pdo()->query($sql)->fetchAll();
    }

    public function find(int $id): ?DockerHost
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, name, ip_address, os, docker_version, notes, created_at, updated_at
             FROM docker_host WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : DockerHost::fromRow($row);
    }

    /** @param array<string,mixed> $data */
    public function insert(array $data): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO docker_host (name, ip_address, os, docker_version, notes)
             VALUES (:name, :ip_address, :os, :docker_version, :notes)'
        );
        $stmt->execute([
            'name'           => $data['name'],
            'ip_address'     => $data['ip_address'],
            'os'             => $data['os'],
            'docker_version' => $data['docker_version'],
            'notes'          => $data['notes'],
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): bool
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE docker_host
             SET name = :name, ip_address = :ip_address, os = :os,
                 docker_version = :docker_version, notes = :notes
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'             => $id,
            'name'           => $data['name'],
            'ip_address'     => $data['ip_address'],
            'os'             => $data['os'],
            'docker_version' => $data['docker_version'],
            'notes'          => $data['notes'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM docker_host WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function hasProjects(int $id): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM project WHERE docker_host_id = :id'
        );
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
