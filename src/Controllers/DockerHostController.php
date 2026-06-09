<?php

declare(strict_types=1);

namespace Manifesto\Controllers;

use Manifesto\Core\Request;
use Manifesto\Core\Response;
use Manifesto\Core\Session;
use Manifesto\Models\DockerHost;
use Manifesto\Repositories\DockerHostRepository;

final class DockerHostController
{
    private DockerHostRepository $hosts;

    public function __construct()
    {
        $this->hosts = new DockerHostRepository();
    }

    public function index(Request $request): void
    {
        Response::view('docker-hosts/index', [
            'title' => 'Docker Hosts',
            'hosts' => $this->hosts->allWithProjectCounts(),
        ]);
    }

    public function create(Request $request): void
    {
        Response::view('docker-hosts/create', [
            'title' => 'New Docker Host',
        ]);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request, '/docker-hosts/create');
        $id   = $this->hosts->insert($data);

        Session::flash('success', 'Docker host created.');
        Response::redirect('/docker-hosts/' . $id);
    }

    public function show(Request $request, string $id): void
    {
        $host = $this->findOrAbort((int) $id);

        Response::view('docker-hosts/show', [
            'title' => $host->name,
            'host'  => $host,
        ]);
    }

    public function edit(Request $request, string $id): void
    {
        $host = $this->findOrAbort((int) $id);

        Response::view('docker-hosts/edit', [
            'title' => 'Edit ' . $host->name,
            'host'  => $host,
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $hostId = (int) $id;
        $this->findOrAbort($hostId);

        $data = $this->validate($request, '/docker-hosts/' . $hostId . '/edit');
        $this->hosts->update($hostId, $data);

        Session::flash('success', 'Docker host updated.');
        Response::redirect('/docker-hosts/' . $hostId);
    }

    public function destroy(Request $request, string $id): void
    {
        $hostId = (int) $id;
        $this->findOrAbort($hostId);

        if ($this->hosts->hasProjects($hostId)) {
            Session::flash('error', 'Cannot delete a host that still has projects.');
            Response::redirect('/docker-hosts/' . $hostId);
        }

        $this->hosts->delete($hostId);
        Session::flash('success', 'Docker host deleted.');
        Response::redirect('/docker-hosts');
    }

    private function findOrAbort(int $id): DockerHost
    {
        $host = $this->hosts->find($id);
        if ($host === null) {
            Response::abort(404, 'Docker host not found.');
        }
        return $host;
    }

    /**
     * Validates the form; on error flashes + redirects back to the form.
     *
     * @return array{name:string,ip_address:?string,os:?string,docker_version:?string,notes:?string}
     */
    private function validate(Request $request, string $backTo): array
    {
        $name = $request->input('name', '') ?? '';

        $error = null;
        if ($name === '') {
            $error = 'Name is required.';
        } elseif (mb_strlen($name) > 128) {
            $error = 'Name must be at most 128 characters.';
        }

        if ($error !== null) {
            Session::flash('error', $error);
            Session::flashOldInput($request->all());
            Response::redirect($backTo);
        }

        return [
            'name'           => $name,
            'ip_address'     => $this->nullable($request->input('ip_address', '')),
            'os'             => $this->nullable($request->input('os', '')),
            'docker_version' => $this->nullable($request->input('docker_version', '')),
            'notes'          => $this->nullable($request->input('notes', '')),
        ];
    }

    private function nullable(?string $value): ?string
    {
        return ($value === null || $value === '') ? null : $value;
    }
}
