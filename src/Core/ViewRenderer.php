<?php

declare(strict_types=1);

namespace Manifesto\Core;

use Manifesto\Repositories\TreeRepository;

/**
 * Renders src/Views/{template}.php inside a layout.
 * The 'app' layout automatically receives $currentUser, $flashes and
 * $sidebarTree — individual controllers never query the tree themselves.
 */
final class ViewRenderer
{
    private static string $viewsPath = __DIR__ . '/../Views';

    public static function render(string $template, array $data = [], string $layout = 'app'): string
    {
        $data['currentUser'] = Auth::user();
        $data['flashes']     = Session::pullFlashes();
        if ($layout === 'app') {
            $data['sidebarTree'] = (new TreeRepository())->fullTree();
        }

        $content = self::renderFile(self::file($template), $data);
        Session::clearOldInput();

        if ($layout === '') {
            return $content;
        }
        $data['content'] = $content;
        return self::renderFile(self::file('layouts/' . $layout), $data);
    }

    public static function exists(string $template): bool
    {
        return is_file(self::file($template));
    }

    private static function file(string $template): string
    {
        return self::$viewsPath . '/' . $template . '.php';
    }

    private static function renderFile(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
