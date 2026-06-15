<?php

declare(strict_types=1);

namespace Manifesto\Models;

/** POPO/DTO — no logic (architecture invariant). */
final class GeneratedFile
{
    public function __construct(
        public int $id,
        public int $projectId,
        public string $fileType,
        public int $versionNumber,
        public string $content,
        public string $createdAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['project_id'],
            $row['file_type'],
            (int) $row['version_number'],
            $row['content'],
            $row['created_at'],
        );
    }

    /** Conventional download filename for this file type. */
    public function filename(): string
    {
        return match ($this->fileType) {
            'docker-compose' => 'docker-compose.yml',
            'env'            => '.env',
            'dockerfile'     => 'Dockerfile',
            default          => 'emmet.txt',
        };
    }

    /** MIME type for the HTTP Content-Type header. */
    public function mimeType(): string
    {
        return match ($this->fileType) {
            'docker-compose' => 'application/x-yaml',
            default          => 'text/plain',
        };
    }
}
