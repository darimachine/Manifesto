<?php

declare(strict_types=1);

namespace Manifesto\Models;

/** POPO/DTO — no logic (architecture invariant). */
final class Volume
{
    public function __construct(
        public int $id,
        public int $serviceId,
        public string $hostPath,
        public string $containerPath,
        public string $mode,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['service_id'],
            $row['host_path'],
            $row['container_path'],
            $row['mode'],
        );
    }
}
