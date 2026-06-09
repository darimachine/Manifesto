<?php

declare(strict_types=1);

namespace Manifesto\Core;

use Manifesto\Repositories\AppUserRepository;

/** Login / logout / role checks backed by the session. */
final class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $user = (new AppUserRepository())->findByUsername($username);
        if ($user === null || !password_verify($password, $user->passwordHash)) {
            return false;
        }
        Session::regenerate(); // prevent session fixation
        Session::set('user', [
            'id'           => $user->id,
            'username'     => $user->username,
            'role'         => $user->role,
            'display_name' => $user->displayName ?? $user->username,
        ]);
        return true;
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function check(): bool
    {
        return Session::get('user') !== null;
    }

    /** @return array{id:int,username:string,role:string,display_name:string}|null */
    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }
}
