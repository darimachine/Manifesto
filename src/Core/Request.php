<?php

declare(strict_types=1);

namespace Manifesto\Core;

/**
 * HTTP request wrapper. Also detects the base path so the app
 * works from ANY subfolder (e.g. /w26/manifesto-xxx/) with zero config.
 */
final class Request
{
    private static ?string $basePath = null;

    /** @param array<string,string> $routeParams */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $get,
        private readonly array $post,
        private array $routeParams = [],
    ) {
    }

    public static function capture(): self
    {
        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            self::detectPath(),
            $_GET,
            $_POST,
        );
    }

    /**
     * Base URL prefix of the app WITHOUT the /public part.
     * '' when the app is the document root (Docker),
     * '/w26/manifesto-xxx' when deployed in an XAMPP subfolder.
     */
    public static function basePath(): string
    {
        if (self::$basePath === null) {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
            $scriptDir = rtrim($scriptDir, '/');
            if (str_ends_with($scriptDir, '/public')) {
                $scriptDir = substr($scriptDir, 0, -strlen('/public'));
            }
            self::$basePath = $scriptDir === '/' ? '' : $scriptDir;
        }
        return self::$basePath;
    }

    /** Route path relative to the app: '/projects/5'. */
    private static function detectPath(): string
    {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = self::basePath();
        foreach ([$base . '/public', $base] as $prefix) {
            if ($prefix !== '' && str_starts_with($uri, $prefix)) {
                $uri = substr($uri, strlen($prefix));
                break;
            }
        }
        $uri = '/' . ltrim($uri, '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    public function input(string $key, ?string $default = null): ?string
    {
        $value = $this->post[$key] ?? $this->get[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    /** Raw value (arrays allowed — used by inline child-row forms). */
    public function raw(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key, ?string $default = null): ?string
    {
        return $this->routeParams[$key] ?? $default;
    }
}
