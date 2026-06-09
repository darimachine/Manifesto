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
        );
    }
}
