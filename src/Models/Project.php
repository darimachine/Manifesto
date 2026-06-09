<?php

declare(strict_types=1);

namespace Manifesto\Models;

/** POPO/DTO — no logic (architecture invariant). */
final class Project
{
    public function __construct(
        public int $id,
        public int $dockerHostId,
        public string $name,
        public string $slug,
        public ?string $description,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['docker_host_id'],
            $row['name'],
            $row['slug'],
            $row['description'],
        );
    }
}
