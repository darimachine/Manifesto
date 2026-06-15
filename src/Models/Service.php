<?php

declare(strict_types=1);

namespace Manifesto\Models;

/** POPO/DTO — no logic (architecture invariant). */
final class Service
{
    public function __construct(
        public int $id,
        public int $projectId,
        public string $name,
        public string $image,
        public string $restartPolicy,
        public ?string $notes,
        public ?string $command,
        public ?string $workingDir,
        public ?string $dependsOn,
        public ?string $buildContext,
        public ?string $dockerfileContent,
        public ?string $healthcheckCmd,
        public ?string $healthcheckInterval,
        public ?string $networkMode,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['project_id'],
            $row['name'],
            $row['image'],
            $row['restart_policy'],
            $row['notes'],
            $row['command'] ?? null,
            $row['working_dir'] ?? null,
            $row['depends_on'] ?? null,
            $row['build_context'] ?? null,
            $row['dockerfile_content'] ?? null,
            $row['healthcheck_cmd'] ?? null,
            $row['healthcheck_interval'] ?? null,
            $row['network_mode'] ?? null,
            $row['created_at'],
            $row['updated_at'],
        );
    }
}
