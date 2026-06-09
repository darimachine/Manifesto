<?php

declare(strict_types=1);

namespace Manifesto\Repositories;

use Manifesto\Core\Database;

/**
 * Builds the full sidebar tree:
 * DockerHost → Project → Service → WebApp.
 * Called by ViewRenderer for every 'app' layout render.
 */
final class TreeRepository
{
    /**
     * @return array<int,array{id:int,name:string,projects:array<int,array{
     *   id:int,name:string,services:array<int,array{
     *     id:int,name:string,webapps:array<int,array{id:int,name:string}>}>}>}>
     */
    public function fullTree(): array
    {
        $pdo = Database::pdo();

        $hosts = [];
        foreach ($pdo->query('SELECT id, name FROM docker_host ORDER BY name') as $row) {
            $row['projects'] = [];
            $hosts[(int) $row['id']] = $row;
        }

        $projects = [];
        foreach ($pdo->query('SELECT id, docker_host_id, name FROM project ORDER BY name') as $row) {
            $row['services'] = [];
            $projects[(int) $row['id']] = $row;
        }

        $services = [];
        foreach ($pdo->query('SELECT id, project_id, name FROM service ORDER BY name') as $row) {
            $row['webapps'] = [];
            $services[(int) $row['id']] = $row;
        }

        foreach ($pdo->query('SELECT id, service_id, name FROM web_app ORDER BY name') as $row) {
            $sid = (int) $row['service_id'];
            if (isset($services[$sid])) {
                $services[$sid]['webapps'][] = $row;
            }
        }
        foreach ($services as $service) {
            $pid = (int) $service['project_id'];
            if (isset($projects[$pid])) {
                $projects[$pid]['services'][] = $service;
            }
        }
        foreach ($projects as $project) {
            $hid = (int) $project['docker_host_id'];
            if (isset($hosts[$hid])) {
                $hosts[$hid]['projects'][] = $project;
            }
        }

        return array_values($hosts);
    }
}
