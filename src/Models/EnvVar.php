<?php

declare(strict_types=1);

namespace Manifesto\Models;

/** POPO/DTO — no logic (architecture invariant). */
final class EnvVar
{
    public function __construct(
        public int $id,
        public int $serviceId,
        public string $keyName,
        public ?string $value,
        public bool $isSecret,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['service_id'],
            $row['key_name'],
            $row['value'],
            (bool) $row['is_secret'],
        );
    }
}
