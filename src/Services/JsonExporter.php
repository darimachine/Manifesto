<?php

declare(strict_types=1);

namespace Manifesto\Services;

use Manifesto\Repositories\DockerHostRepository;
use Manifesto\Repositories\ProjectRepository;
use Manifesto\Repositories\ServiceChildrenRepository;
use Manifesto\Repositories\ServiceRepository;

/**
 * Exports a Project (with full hierarchy) to a JSON-serializable associative array.
 * Stateless — every call to exportProject() is self-contained.
 */
final class JsonExporter
{
    public function __construct(
        private DockerHostRepository $hosts,
        private ProjectRepository $projects,
        private ServiceRepository $services,
        private ServiceChildrenRepository $children,
    ) {
    }

    /**
     * Returns a JSON-serializable array representing the full project hierarchy.
     *
     * @throws \RuntimeException when the project does not exist
     * @return array<string,mixed>
     */
    public function exportProject(int $projectId): array
    {
        $project = $this->projects->findWithHost($projectId);
        if ($project === null) {
            throw new \RuntimeException("Project #{$projectId} not found.");
        }

        $serviceRows = $this->projects->servicesOf($projectId);
        $services    = [];

        foreach ($serviceRows as $row) {
            $serviceId = (int) $row['id'];
            $service   = $this->services->find($serviceId);
            if ($service === null) {
                continue;
            }

            $ports = array_map(fn ($p) => [
                'host_port'      => $p->hostPort,
                'container_port' => $p->containerPort,
                'protocol'       => $p->protocol,
            ], $this->children->portsOf($serviceId));

            $envs = array_map(fn ($e) => [
                'key_name'  => $e->keyName,
                'value'     => $e->value,
                'is_secret' => $e->isSecret,
            ], $this->children->envsOf($serviceId));

            $volumes = array_map(fn ($v) => [
                'host_path'      => $v->hostPath,
                'container_path' => $v->containerPath,
                'mode'           => $v->mode,
            ], $this->children->volumesOf($serviceId));

            $webAppRows = $this->services->webAppsOf($serviceId);

            $services[] = [
                'name'                => $service->name,
                'image'               => $service->image,
                'restart_policy'      => $service->restartPolicy,
                'notes'               => $service->notes,
                // Extended fields — Group 1 adds these to the Service model;
                // null-safe access ensures backward compatibility in the interim.
                'command'             => $service->command ?? null,
                'working_dir'         => $service->workingDir ?? null,
                'depends_on'          => $service->dependsOn ?? null,
                'build_context'       => $service->buildContext ?? null,
                'dockerfile_content'  => $service->dockerfileContent ?? null,
                'healthcheck_cmd'     => $service->healthcheckCmd ?? null,
                'healthcheck_interval' => $service->healthcheckInterval ?? null,
                'network_mode'        => $service->networkMode ?? null,
                'ports'               => $ports,
                'env_vars'            => $envs,
                'volumes'             => $volumes,
                'web_apps'            => array_map(
                    fn ($w) => ['name' => $w['name']],
                    $webAppRows
                ),
            ];
        }

        return [
            'format_version' => '1.0',
            'exported_at'    => date('c'),
            'project'        => [
                'name'        => $project['name'],
                'slug'        => $project['slug'],
                'description' => $project['description'],
                'docker_host' => [
                    'name' => $project['host_name'],
                ],
                'services'    => $services,
            ],
        ];
    }
}
