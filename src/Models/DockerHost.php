<?php

declare(strict_types=1);

namespace Manifesto\Models;

/** POPO/DTO — no logic (architecture invariant). */
final class DockerHost
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $ipAddress,
        public ?string $os,
        public ?string $dockerVersion,
        public ?string $notes,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            $row['name'],
            $row['ip_address'],
            $row['os'],
            $row['docker_version'],
            $row['notes'],
            $row['created_at'],
            $row['updated_at'],
        );
    }
}
