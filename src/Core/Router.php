<?php

declare(strict_types=1);

namespace Manifesto\Core;

/**
 * Regex router. Routes come from config/routes.php as:
 *   [METHOD, '/pattern/{id}', Controller::class, 'action', ACCESS]
 * ACCESS: 'guest' (no login), 'auth' (any logged user), 'admin' (admin only).
 * Access control is enforced HERE, centrally.
 */
final class Router
{
    /** @param array<int,array{0:string,1:string,2:class-string,3:string,4?:string}> $routes */
    public function __construct(private readonly array $routes)
    {
    }

    public function dispatch(Request $request): never
    {
        foreach ($this->routes as $route) {
            [$method, $pattern, $class, $action] = $route;
            $access = $route[4] ?? 'auth';

            if ($method !== $request->method) {
                continue;
            }
            $params = $this->match($pattern, $request->path);
            if ($params === null) {
                continue;
            }

            $this->enforceAccess($access);
            $request->setRouteParams($params);

            $controller = new $class();
            $controller->$action($request, ...array_values($params));
            exit;
        }

        Response::abort(404, 'Page not found: ' . $request->path);
    }

    /** @return array<string,string>|null Named params or null when no match. */
    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        if (!preg_match('#^' . $regex . '$#', $path, $m)) {
            return null;
        }
        return array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    private function enforceAccess(string $access): void
    {
        if ($access === 'guest') {
            return;
        }
        if (!Auth::check()) {
            Response::redirect('/login');
        }
        if ($access === 'admin' && Auth::role() !== 'admin') {
            Response::abort(403, 'Admin role required for this action.');
        }
    }
}
