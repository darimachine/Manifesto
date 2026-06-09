<?php

declare(strict_types=1);

namespace Manifesto\Controllers;

use Manifesto\Core\Database;
use Manifesto\Core\Request;
use Manifesto\Core\Response;

final class DashboardController
{
    public function index(Request $request): void
    {
        $pdo = Database::pdo();
        $counts = [];
        foreach (['docker_host', 'project', 'service', 'web_app', 'generated_file'] as $table) {
            $counts[$table] = (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        }
        Response::view('dashboard/index', ['counts' => $counts]);
    }
}
