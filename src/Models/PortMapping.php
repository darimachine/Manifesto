<?php

declare(strict_types=1);

namespace Manifesto\Models;

/** POPO/DTO — no logic (architecture invariant). */
final class PortMapping
{
    public function __construct(
        public int $id,
        public int $serviceId,
        public int $hostPort,
        public int $containerPort,
        public string $protocol,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['service_id'],
            (int) $row['host_port'],
            (int) $row['container_port'],
            $row['protocol'],
        );
    }
}
