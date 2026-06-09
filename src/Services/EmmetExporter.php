<?php

declare(strict_types=1);

namespace Manifesto\Services;

use Manifesto\Models\DockerHost;
use Manifesto\Models\EnvVar;
use Manifesto\Models\PortMapping;
use Manifesto\Models\Volume;
use Manifesto\Repositories\DockerHostRepository;
use Manifesto\Repositories\ProjectRepository;
use Manifesto\Repositories\ServiceChildrenRepository;
use Manifesto\Repositories\ServiceRepository;

/**
 * Generates a human-readable UTF-8 tree of the full project hierarchy using
 * box-drawing characters. Stateless: every call to export() is self-contained.
 *
 * Example output:
 *
 *   local-dev [ip=127.0.0.1, docker=28.0]
 *   └─ demo-blog
 *      ├─ web [image=nginx:alpine, restart=unless-stopped]
 *      │  ├─ port: 8080:80/tcp
 *      │  ├─ env: (none)
 *      │  ├─ volume: ./html:/usr/share/nginx/html (ro)
 *      │  └─ webapp: Blog Frontend
 *      └─ db [image=mariadb:10.11, restart=always]
 *         ├─ env: MYSQL_ROOT_PASSWORD=•••••• (secret), MYSQL_DATABASE=blog
 *         ├─ volume: ./db-data:/var/lib/mysql (rw)
 *         └─ webapp: (none)
 *
 * Secret env-var values are masked with •••••• (six bullets, U+2022).
 * Box-drawing chars: └─  ├─  │  — all in the BMP, safe for utf8mb4 and any
 * modern terminal or browser.
 */
final class EmmetExporter
{
    // Box-drawing constants for clarity.
    private const BRANCH  = '├─ ';
    private const LAST    = '└─ ';
    private const PIPE    = '│  ';
    private const BLANK   = '   ';
    private const MASK    = '••••••';

    public function __construct(
        private DockerHostRepository $hosts,
        private ProjectRepository $projects,
        private ServiceRepository $services,
        private ServiceChildrenRepository $children,
    ) {
    }

    /** @throws \RuntimeException when the project or its host does not exist */
    public function export(int $projectId): string
    {
        $project = $this->projects->find($projectId);
        if ($project === null) {
            throw new \RuntimeException("Project #{$projectId} not found.");
        }

        $host = $this->hosts->find($project->dockerHostId);
        if ($host === null) {
            throw new \RuntimeException(
                "Docker host #{$project->dockerHostId} referenced by project not found."
            );
        }

        $serviceRows = $this->projects->servicesOf($projectId);

        $lines = [];

        // ── Host line ─────────────────────────────────────────────────────────
        $hostAttrs = $this->buildHostAttrs($host);
        $lines[] = $host->name . ($hostAttrs !== '' ? ' [' . $hostAttrs . ']' : '');

        // ── Project line ──────────────────────────────────────────────────────
        $lines[] = self::LAST . $project->name;

        $serviceCount = count($serviceRows);

        foreach ($serviceRows as $index => $row) {
            $isLastService   = ($index === $serviceCount - 1);
            $serviceId       = (int) $row['id'];
            $serviceName     = (string) $row['name'];
            $image           = (string) $row['image'];
            $restartPolicy   = (string) $row['restart_policy'];

            // Indent prefix under the project node.
            $servicePrefix = self::BLANK;

            $serviceConnector = $isLastService ? self::LAST : self::BRANCH;
            $lines[] = $servicePrefix . $serviceConnector
                . $serviceName
                . ' [image=' . $image . ', restart=' . $restartPolicy . ']';

            // Child lines are indented one further level under this service.
            $childPrefix = $servicePrefix . ($isLastService ? self::BLANK : self::PIPE);

            $ports   = $this->children->portsOf($serviceId);
            $envVars = $this->children->envsOf($serviceId);
            $volumes = $this->children->volumesOf($serviceId);
            $webApps = $this->services->webAppsOf($serviceId);

            // Determine how many child sections to draw so we can pick the
            // right connector for each.
            $sections = [];
            $sections[] = ['type' => 'ports',   'data' => $ports];
            $sections[] = ['type' => 'envs',     'data' => $envVars];
            $sections[] = ['type' => 'volumes',  'data' => $volumes];
            $sections[] = ['type' => 'webapps',  'data' => $webApps];

            $sectionCount = count($sections);

            foreach ($sections as $sIdx => $section) {
                $isLastSection = ($sIdx === $sectionCount - 1);
                $connector     = $isLastSection ? self::LAST : self::BRANCH;

                switch ($section['type']) {
                    case 'ports':
                        $lines[] = $childPrefix . $connector . $this->renderPorts($section['data']);
                        break;

                    case 'envs':
                        $lines[] = $childPrefix . $connector . $this->renderEnvs($section['data']);
                        break;

                    case 'volumes':
                        $lines[] = $childPrefix . $connector . $this->renderVolumes($section['data']);
                        break;

                    case 'webapps':
                        $lines[] = $childPrefix . $connector . $this->renderWebApps($section['data']);
                        break;
                }
            }
        }

        return implode("\n", $lines) . "\n";
    }

    // -------------------------------------------------------------------------
    // Private rendering helpers
    // -------------------------------------------------------------------------

    /** Builds the [ip=…, docker=…] attribute string for a host. */
    private function buildHostAttrs(DockerHost $host): string
    {
        $parts = [];
        if ($host->ipAddress !== null && $host->ipAddress !== '') {
            $parts[] = 'ip=' . $host->ipAddress;
        }
        if ($host->dockerVersion !== null && $host->dockerVersion !== '') {
            $parts[] = 'docker=' . $host->dockerVersion;
        }
        if ($host->os !== null && $host->os !== '') {
            $parts[] = 'os=' . $host->os;
        }
        return implode(', ', $parts);
    }

    /** @param PortMapping[] $ports */
    private function renderPorts(array $ports): string
    {
        if ($ports === []) {
            return 'port: (none)';
        }
        $formatted = array_map(
            fn($p) => 'port: ' . $p->hostPort . ':' . $p->containerPort . '/' . $p->protocol,
            $ports
        );
        return implode(', ', $formatted);
    }

    /** @param EnvVar[] $envVars */
    private function renderEnvs(array $envVars): string
    {
        if ($envVars === []) {
            return 'env: (none)';
        }
        $parts = [];
        foreach ($envVars as $env) {
            $displayValue = $env->isSecret
                ? self::MASK . ' (secret)'
                : (string) ($env->value ?? '');
            $parts[] = $env->keyName . '=' . $displayValue;
        }
        return 'env: ' . implode(', ', $parts);
    }

    /** @param Volume[] $volumes */
    private function renderVolumes(array $volumes): string
    {
        if ($volumes === []) {
            return 'volume: (none)';
        }
        $formatted = array_map(
            fn($v) => 'volume: ' . $v->hostPath . ':' . $v->containerPath . ' (' . $v->mode . ')',
            $volumes
        );
        return implode(', ', $formatted);
    }

    /**
     * @param array<int,array{id:int,name:string}> $webApps
     * Rendered from ServiceRepository::webAppsOf() which returns [id, name].
     */
    private function renderWebApps(array $webApps): string
    {
        if ($webApps === []) {
            return 'webapp: (none)';
        }
        $names = array_column($webApps, 'name');
        return 'webapp: ' . implode(', ', $names);
    }
}
