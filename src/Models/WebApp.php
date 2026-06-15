<?php

declare(strict_types=1);

namespace Manifesto\Models;

/** POPO/DTO — no logic (architecture invariant). */
final class WebApp
{
    public function __construct(
        public int $id,
        public int $serviceId,
        public string $name,
        public ?string $publicUrl,
        public ?string $dnsName,
        public ?string $notes,
        public string $status,
        public ?string $lastStatusChange,
        public ?string $lastCheckedAt,
        public ?int $lastHttpCode,
        public ?int $lastDurationMs,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['service_id'],
            $row['name'],
            $row['public_url'],
            $row['dns_name'],
            $row['notes'],
            $row['status'] ?? 'unknown',
            $row['last_status_change'] ?? null,
            $row['last_checked_at'] ?? null,
            isset($row['last_http_code']) && $row['last_http_code'] !== null ? (int) $row['last_http_code'] : null,
            isset($row['last_duration_ms']) && $row['last_duration_ms'] !== null ? (int) $row['last_duration_ms'] : null,
        );
    }
}
