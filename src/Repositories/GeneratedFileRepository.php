<?php

declare(strict_types=1);

namespace Manifesto\Repositories;

use Manifesto\Core\Database;
use Manifesto\Models\GeneratedFile;
use Throwable;

final class GeneratedFileRepository
{
    /** Highest-version row for a given project + file type, or null if none exists. */
    public function latestForProject(int $projectId, string $fileType): ?GeneratedFile
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, project_id, file_type, version_number, content, created_at
             FROM generated_file
             WHERE project_id = :project_id AND file_type = :file_type
             ORDER BY version_number DESC
             LIMIT 1'
        );
        $stmt->execute(['project_id' => $projectId, 'file_type' => $fileType]);
        $row = $stmt->fetch();
        return $row === false ? null : GeneratedFile::fromRow($row);
    }

    /**
     * All generated files for a project, newest first (all types mixed).
     *
     * @return array<int,GeneratedFile>
     */
    public function historyForProject(int $projectId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, project_id, file_type, version_number, content, created_at
             FROM generated_file
             WHERE project_id = :project_id
             ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute(['project_id' => $projectId]);
        return array_map([GeneratedFile::class, 'fromRow'], $stmt->fetchAll());
    }

    /**
     * Highest version_number across all file types for the project.
     * Returns 0 when no rows exist yet.
     */
    public function latestVersionAcrossTypes(int $projectId): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT MAX(version_number) FROM generated_file WHERE project_id = :project_id'
        );
        $stmt->execute(['project_id' => $projectId]);
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?GeneratedFile
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, project_id, file_type, version_number, content, created_at
             FROM generated_file WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : GeneratedFile::fromRow($row);
    }

    /** Insert a single versioned file row and return its new auto-increment id. */
    public function insertNewVersion(
        int $projectId,
        string $fileType,
        int $versionNumber,
        string $content,
    ): int {
        $pdo  = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO generated_file (project_id, file_type, version_number, content)
             VALUES (:project_id, :file_type, :version_number, :content)'
        );
        $stmt->execute([
            'project_id'     => $projectId,
            'file_type'      => $fileType,
            'version_number' => $versionNumber,
            'content'        => $content,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Insert all three file types as a single new version inside a transaction.
     *
     * @param array{docker-compose:string,env:string,emmet:string} $contentByType
     * @return int The version number used for all three rows.
     */
    public function insertSetForProject(int $projectId, array $contentByType): int
    {
        $pdo     = Database::pdo();
        $version = $this->latestVersionAcrossTypes($projectId) + 1;

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO generated_file (project_id, file_type, version_number, content)
                 VALUES (:project_id, :file_type, :version_number, :content)'
            );
            foreach ($contentByType as $fileType => $content) {
                $stmt->execute([
                    'project_id'     => $projectId,
                    'file_type'      => $fileType,
                    'version_number' => $version,
                    'content'        => $content,
                ]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return $version;
    }
}
