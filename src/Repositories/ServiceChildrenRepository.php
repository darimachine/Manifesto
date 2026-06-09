<?php

declare(strict_types=1);

namespace Manifesto\Repositories;

use Manifesto\Core\Database;
use Manifesto\Models\EnvVar;
use Manifesto\Models\PortMapping;
use Manifesto\Models\Volume;
use Throwable;

/** Inline child collections of a Service: port mappings, env vars, volumes. */
final class ServiceChildrenRepository
{
    /** @return PortMapping[] */
    public function portsOf(int $serviceId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, service_id, host_port, container_port, protocol
             FROM port_mapping WHERE service_id = :service_id ORDER BY id'
        );
        $stmt->execute(['service_id' => $serviceId]);
        return array_map([PortMapping::class, 'fromRow'], $stmt->fetchAll());
    }

    /** @return EnvVar[] */
    public function envsOf(int $serviceId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, service_id, key_name, value, is_secret
             FROM env_var WHERE service_id = :service_id ORDER BY id'
        );
        $stmt->execute(['service_id' => $serviceId]);
        return array_map([EnvVar::class, 'fromRow'], $stmt->fetchAll());
    }

    /** @return Volume[] */
    public function volumesOf(int $serviceId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, service_id, host_path, container_path, mode
             FROM volume WHERE service_id = :service_id ORDER BY id'
        );
        $stmt->execute(['service_id' => $serviceId]);
        return array_map([Volume::class, 'fromRow'], $stmt->fetchAll());
    }

    /**
     * Replace ALL children of a service atomically (delete + re-insert).
     * Rows with empty key fields or invalid ports are silently skipped.
     *
     * @param array<int,array{host_port:string,container_port:string,protocol:string}>   $ports
     * @param array<int,array{key_name:string,value:string,is_secret:string}>            $envs
     * @param array<int,array{host_path:string,container_path:string,mode:string}>       $volumes
     */
    public function syncAll(int $serviceId, array $ports, array $envs, array $volumes): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            foreach (['port_mapping', 'env_var', 'volume'] as $table) {
                $stmt = $pdo->prepare("DELETE FROM `$table` WHERE service_id = :service_id");
                $stmt->execute(['service_id' => $serviceId]);
            }

            $insertPort = $pdo->prepare(
                'INSERT INTO port_mapping (service_id, host_port, container_port, protocol)
                 VALUES (:service_id, :host_port, :container_port, :protocol)'
            );
            foreach ($ports as $port) {
                $hostPort      = (int) ($port['host_port'] ?? 0);
                $containerPort = (int) ($port['container_port'] ?? 0);
                if ($hostPort < 1 || $hostPort > 65535 || $containerPort < 1 || $containerPort > 65535) {
                    continue;
                }
                $protocol = $port['protocol'] ?? 'tcp';
                $insertPort->execute([
                    'service_id'     => $serviceId,
                    'host_port'      => $hostPort,
                    'container_port' => $containerPort,
                    'protocol'       => in_array($protocol, ['tcp', 'udp'], true) ? $protocol : 'tcp',
                ]);
            }

            $insertEnv = $pdo->prepare(
                'INSERT INTO env_var (service_id, key_name, value, is_secret)
                 VALUES (:service_id, :key_name, :value, :is_secret)'
            );
            foreach ($envs as $env) {
                $keyName = trim((string) ($env['key_name'] ?? ''));
                if ($keyName === '') {
                    continue;
                }
                $insertEnv->execute([
                    'service_id' => $serviceId,
                    'key_name'   => $keyName,
                    'value'      => (string) ($env['value'] ?? ''),
                    'is_secret'  => ((string) ($env['is_secret'] ?? '0')) === '1' ? 1 : 0,
                ]);
            }

            $insertVolume = $pdo->prepare(
                'INSERT INTO volume (service_id, host_path, container_path, mode)
                 VALUES (:service_id, :host_path, :container_path, :mode)'
            );
            foreach ($volumes as $volume) {
                $hostPath      = trim((string) ($volume['host_path'] ?? ''));
                $containerPath = trim((string) ($volume['container_path'] ?? ''));
                if ($hostPath === '' || $containerPath === '') {
                    continue;
                }
                $mode = $volume['mode'] ?? 'rw';
                $insertVolume->execute([
                    'service_id'     => $serviceId,
                    'host_path'      => $hostPath,
                    'container_path' => $containerPath,
                    'mode'           => in_array($mode, ['rw', 'ro'], true) ? $mode : 'rw',
                ]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
