<?php

declare(strict_types=1);

namespace Manifesto\Core;

/** Response helpers: render a view, redirect, JSON, abort, file download. */
final class Response
{
    public static function view(string $template, array $data = [], string $layout = 'app'): never
    {
        echo ViewRenderer::render($template, $data, $layout);
        exit;
    }

    /** Redirect to an app route ('/projects/5') or absolute URL. */
    public static function redirect(string $path): never
    {
        $target = str_starts_with($path, 'http') ? $path : url($path);
        header('Location: ' . $target, true, 302);
        exit;
    }

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Render errors/{code}.php and stop. */
    public static function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        $template = 'errors/' . $code;
        if (!ViewRenderer::exists($template)) {
            $template = 'errors/500';
        }
        echo ViewRenderer::render($template, ['message' => $message], 'auth');
        exit;
    }

    /** Stream text content as a file download. */
    public static function download(string $content, string $filename, string $mime = 'text/plain'): never
    {
        header('Content-Type: ' . $mime . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }
}
