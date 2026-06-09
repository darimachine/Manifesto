<?php

declare(strict_types=1);

/**
 * Global view/url helpers. Loaded by the front controller.
 * INVARIANT: every link, form action and asset in Views goes through
 * url()/asset() so the app works from any subfolder (course requirement).
 */

use Manifesto\Core\Csrf;
use Manifesto\Core\Request;
use Manifesto\Core\Session;

/** HTML-escape (XSS protection). */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Absolute (host-relative) URL for an app route: url('/projects/5'). */
function url(string $path = '/'): string
{
    $base = Request::basePath();
    return $base . '/' . ltrim($path, '/');
}

/** URL for a file inside public/assets/. */
function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}

/** Hidden CSRF input — REQUIRED inside every <form method="post">. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(Csrf::getToken()) . '">';
}

/** Previously submitted value (re-fill forms after validation errors). */
function old(string $key, ?string $default = null): string
{
    $old = Session::get('_old_input', []);
    return e($old[$key] ?? $default ?? '');
}
