<?php

declare(strict_types=1);

namespace Manifesto\Core;

/** Secure session wrapper + flash messages. */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_set_cookie_params([
            'path'     => (Request::basePath() ?: '') . '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('manifesto_session');
        session_start();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** Flash a one-request message: type is success|error|info. */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    /** @return array<int,array{type:string,message:string}> */
    public static function pullFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    /** Keep submitted input for one request (form re-fill). */
    public static function flashOldInput(array $input): void
    {
        unset($input['_csrf_token'], $input['password']);
        $_SESSION['_old_input'] = $input;
    }

    public static function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }
}
