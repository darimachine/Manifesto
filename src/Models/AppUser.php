<?php

declare(strict_types=1);

namespace Manifesto\Models;

/** POPO/DTO — no logic (architecture invariant). */
final class AppUser
{
    public function __construct(
        public int $id,
        public string $username,
        public string $passwordHash,
        public string $role,
        public ?string $displayName,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            $row['username'],
            $row['password_hash'],
            $row['role'],
            $row['display_name'],
        );
    }
}
