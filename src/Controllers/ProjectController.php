<?php

declare(strict_types=1);

namespace Manifesto\Controllers;

use Manifesto\Core\Request;
use Manifesto\Core\Response;
use Manifesto\Core\Session;
use Manifesto\Repositories\ProjectRepository;

final class ProjectController
{
    private ProjectRepository $projects;

    public function __construct()
    {
        $this->projects = new ProjectRepository();
    }

    public function index(Request $request): void
    {
        Response::view('projects/index', [
            'title'    => 'Projects',
            'projects' => $this->projects->allWithHost(),
        ]);
    }

    public function create(Request $request): void
    {
        Response::view('projects/create', [
            'title' => 'New Project',
            'hosts' => $this->projects->hostsForSelect(),
        ]);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request, null, '/projects/create');
        $id   = $this->projects->insert($data);

        Session::flash('success', 'Project "' . $data['name'] . '" created.');
        Response::redirect('/projects/' . $id);
    }

    public function show(Request $request, string $id): void
    {
        $projectId = (int) $id;
        $project   = $this->projects->findWithHost($projectId);
        if ($project === null) {
            Response::abort(404, 'Project not found.');
        }

        Response::view('projects/show', [
            'title'         => $project['name'],
            'project'       => $project,
            'services'      => $this->projects->servicesOf($projectId),
            'latestVersion' => $this->projects->latestVersion($projectId),
        ]);
    }

    public function edit(Request $request, string $id): void
    {
        $projectId = (int) $id;
        $project   = $this->projects->find($projectId);
        if ($project === null) {
            Response::abort(404, 'Project not found.');
        }

        Response::view('projects/edit', [
            'title'   => 'Edit Project',
            'project' => $project,
            'hosts'   => $this->projects->hostsForSelect(),
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $projectId = (int) $id;
        if ($this->projects->find($projectId) === null) {
            Response::abort(404, 'Project not found.');
        }

        $data = $this->validate($request, $projectId, '/projects/' . $projectId . '/edit');
        $this->projects->update($projectId, $data);

        Session::flash('success', 'Project "' . $data['name'] . '" updated.');
        Response::redirect('/projects/' . $projectId);
    }

    public function destroy(Request $request, string $id): void
    {
        $projectId = (int) $id;
        $project   = $this->projects->find($projectId);
        if ($project === null) {
            Response::abort(404, 'Project not found.');
        }

        $this->projects->delete($projectId);

        Session::flash('success', 'Project "' . $project->name . '" deleted.');
        Response::redirect('/projects');
    }

    /**
     * Validate form input; on failure flash + redirect back to the form.
     * @return array{docker_host_id:int,name:string,slug:string,description:?string}
     */
    private function validate(Request $request, ?int $ignoreId, string $backTo): array
    {
        $name         = $request->input('name', '') ?? '';
        $slug         = $request->input('slug', '') ?? '';
        $description  = $request->input('description', '') ?? '';
        $dockerHostId = (int) ($request->input('docker_host_id', '') ?? '');

        if ($name === '') {
            $this->fail($request, $backTo, 'Project name is required.');
        }
        if (mb_strlen($name) > 128) {
            $this->fail($request, $backTo, 'Project name must be at most 128 characters.');
        }
        if ($dockerHostId <= 0) {
            $this->fail($request, $backTo, 'Please choose a Docker host.');
        }

        if ($slug === '') {
            $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
        }
        if ($slug === '') {
            $this->fail($request, $backTo, 'Could not derive a slug from the name — please enter one.');
        }
        if (mb_strlen($slug) > 128) {
            $this->fail($request, $backTo, 'Slug must be at most 128 characters.');
        }
        if ($this->projects->slugExists($slug, $ignoreId)) {
            $this->fail($request, $backTo, 'Slug "' . $slug . '" is already taken.');
        }

        return [
            'docker_host_id' => $dockerHostId,
            'name'           => $name,
            'slug'           => $slug,
            'description'    => $description === '' ? null : $description,
        ];
    }

    private function fail(Request $request, string $backTo, string $message): never
    {
        Session::flash('error', $message);
        Session::flashOldInput($request->all());
        Response::redirect($backTo);
    }
}
