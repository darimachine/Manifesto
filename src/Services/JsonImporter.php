<?php

declare(strict_types=1);

namespace Manifesto\Services;

use Manifesto\Core\Database;
use Manifesto\Repositories\DockerHostRepository;
use Manifesto\Repositories\ProjectRepository;
use Manifesto\Repositories\ServiceChildrenRepository;
use Manifesto\Repositories\ServiceRepository;

/**
 * Imports a project from JSON produced by JsonExporter.
 * Transactional — rolls back on any failure.
 */
final class JsonImporter
{
    public function __construct(
        private DockerHostRepository $hosts,
        private ProjectRepository $projects,
        private ServiceRepository $services,
        private ServiceChildrenRepository $children,
    ) {
    }

    /**
     * Validates and imports a project from the decoded JSON payload.
     *
     * Slug collisions are resolved automatically by appending "-imported-N".
     * Docker hosts are found by name or created if missing.
     * All DB writes are wrapped in a single transaction.
     *
     * @param array<string,mixed> $data Decoded JSON (from json_decode(..., true))
     * @throws \RuntimeException on validation or import failure
     * @return int New project ID
     */
    public function importProject(array $data): int
    {
        if (($data['format_version'] ?? '') !== '1.0') {
            throw new \RuntimeException('Unsupported format version. Expected "1.0".');
        }
        if (!isset($data['project']) || !is_array($data['project'])) {
            throw new \RuntimeException('Missing "project" object in import payload.');
        }

        $proj = $data['project'];

        if (empty($proj['name']) || !is_string($proj['name'])) {
            throw new \RuntimeException('Project "name" is required and must be a string.');
        }
        if (empty($proj['slug']) || !is_string($proj['slug'])) {
            throw new \RuntimeException('Project "slug" is required and must be a string.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            // Disambiguate slug when it already exists.
            $slug   = $proj['slug'];
            $suffix = 0;
            while ($this->projects->slugExists($slug)) {
                $suffix++;
                $slug = $proj['slug'] . '-imported-' . $suffix;
            }

            $hostName = (string) ($proj['docker_host']['name'] ?? 'imported-host');
            $hostId   = $this->findOrCreateHost($hostName, $pdo);

            $projectName = $proj['name'];
            if ($suffix > 0) {
                $projectName .= ' (imported)';
            }

            $projectId = $this->projects->insert([
                'docker_host_id' => $hostId,
                'name'           => $projectName,
                'slug'           => $slug,
                'description'    => isset($proj['description']) ? (string) $proj['description'] : null,
            ]);

            foreach (($proj['services'] ?? []) as $svcData) {
                if (!is_array($svcData)) {
                    continue;
                }

                $serviceId = $this->services->insert([
                    'project_id'          => $projectId,
                    'name'                => (string) ($svcData['name'] ?? ''),
                    'image'               => (string) ($svcData['image'] ?? ''),
                    'restart_policy'      => (string) ($svcData['restart_policy'] ?? 'unless-stopped'),
                    'notes'               => isset($svcData['notes']) ? (string) $svcData['notes'] : null,
                    'command'             => isset($svcData['command']) ? (string) $svcData['command'] : null,
                    'working_dir'         => isset($svcData['working_dir']) ? (string) $svcData['working_dir'] : null,
                    'depends_on'          => isset($svcData['depends_on']) ? (string) $svcData['depends_on'] : null,
                    'build_context'       => isset($svcData['build_context']) ? (string) $svcData['build_context'] : null,
                    'dockerfile_content'  => isset($svcData['dockerfile_content']) ? (string) $svcData['dockerfile_content'] : null,
                    'healthcheck_cmd'     => isset($svcData['healthcheck_cmd']) ? (string) $svcData['healthcheck_cmd'] : null,
                    'healthcheck_interval' => isset($svcData['healthcheck_interval']) ? (string) $svcData['healthcheck_interval'] : null,
                    'network_mode'        => isset($svcData['network_mode']) ? (string) $svcData['network_mode'] : null,
                ]);

                $ports = array_map(fn ($p) => [
                    'host_port'      => (string) ($p['host_port'] ?? ''),
                    'container_port' => (string) ($p['container_port'] ?? ''),
                    'protocol'       => (string) ($p['protocol'] ?? 'tcp'),
                ], is_array($svcData['ports'] ?? null) ? $svcData['ports'] : []);

                $envs = array_map(fn ($e) => [
                    'key_name'  => (string) ($e['key_name'] ?? ''),
                    'value'     => (string) ($e['value'] ?? ''),
                    'is_secret' => !empty($e['is_secret']) ? '1' : '0',
                ], is_array($svcData['env_vars'] ?? null) ? $svcData['env_vars'] : []);

                $volumes = array_map(fn ($v) => [
                    'host_path'      => (string) ($v['host_path'] ?? ''),
                    'container_path' => (string) ($v['container_path'] ?? ''),
                    'mode'           => (string) ($v['mode'] ?? 'rw'),
                ], is_array($svcData['volumes'] ?? null) ? $svcData['volumes'] : []);

                $this->children->syncAll($serviceId, $ports, $envs, $volumes);

                // Web apps — import name only (minimal, read-only export contract).
                $webApps = is_array($svcData['web_apps'] ?? null) ? $svcData['web_apps'] : [];
                foreach ($webApps as $wa) {
                    if (!is_array($wa) || empty($wa['name'])) {
                        continue;
                    }
                    $pdo->prepare(
                        'INSERT INTO web_app (service_id, name) VALUES (:sid, :name)'
                    )->execute(['sid' => $serviceId, 'name' => (string) $wa['name']]);
                }
            }

            $pdo->commit();
            return $projectId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw new \RuntimeException('Import failed: ' . $e->getMessage(), 0, $e);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Returns the ID of an existing docker_host by name, or inserts one and
     * returns its new ID.
     */
    private function findOrCreateHost(string $name, \PDO $pdo): int
    {
        $stmt = $pdo->prepare('SELECT id FROM docker_host WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $pdo->prepare(
            'INSERT INTO docker_host (name, notes) VALUES (:name, :notes)'
        )->execute([
            'name'  => $name,
            'notes' => 'Auto-created by JSON import',
        ]);

        return (int) $pdo->lastInsertId();
    }
}
