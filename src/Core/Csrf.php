<?php

declare(strict_types=1);

namespace Manifesto\Core;

/** CSRF token: generated per session, verified centrally for every POST. */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function getToken(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    public static function verify(?string $submitted): bool
    {
        return is_string($submitted)
            && $submitted !== ''
            && hash_equals(self::getToken(), $submitted);
    }
}
