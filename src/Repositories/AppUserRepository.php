<?php

declare(strict_types=1);

namespace Manifesto\Repositories;

use Manifesto\Core\Database;
use Manifesto\Models\AppUser;

final class AppUserRepository
{
    public function findByUsername(string $username): ?AppUser
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, username, password_hash, role, display_name
             FROM app_user WHERE username = :username LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();
        return $row === false ? null : AppUser::fromRow($row);
    }
}
