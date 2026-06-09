<?php

declare(strict_types=1);

namespace Manifesto\Controllers;

use Manifesto\Core\Request;
use Manifesto\Core\Response;
use Manifesto\Core\Session;
use Manifesto\Repositories\ServiceChildrenRepository;
use Manifesto\Repositories\ServiceRepository;

final class ServiceController
{
    private const RESTART_POLICIES = ['no', 'always', 'on-failure', 'unless-stopped'];

    private ServiceRepository $services;
    private ServiceChildrenRepository $children;

    public function __construct()
    {
        $this->services = new ServiceRepository();
        $this->children = new ServiceChildrenRepository();
    }

    public function create(Request $request, string $projectId): void
    {
        $projectName = $this->services->projectName((int) $projectId);
        if ($projectName === null) {
            Response::abort(404, 'Project not found.');
        }

        Response::view('services/create', [
            'title'       => 'New Service',
            'projectId'   => (int) $projectId,
            'projectName' => $projectName,
        ]);
    }

    public function store(Request $request, string $projectId): void
    {
        $pid = (int) $projectId;
        if ($this->services->projectName($pid) === null) {
            Response::abort(404, 'Project not found.');
        }

        $data   = $this->serviceInput($request);
        $errors = $this->validate($data, $pid, null);

        if ($errors !== []) {
            foreach ($errors as $error) {
                Session::flash('error', $error);
            }
            Session::flashOldInput($request->all());
            Response::redirect('/projects/' . $pid . '/services/create');
        }

        $data['project_id'] = $pid;
        $id = $this->services->insert($data);
        $this->children->syncAll(
            $id,
            $this->normalizeRows($request->raw('ports'), ['host_port', 'container_port', 'protocol']),
            $this->normalizeRows($request->raw('envs'), ['key_name', 'value', 'is_secret']),
            $this->normalizeRows($request->raw('volumes'), ['host_path', 'container_path', 'mode']),
        );

        Session::flash('success', 'Service "' . $data['name'] . '" created.');
        Response::redirect('/services/' . $id);
    }

    public function show(Request $request, string $id): void
    {
        $service = $this->services->findWithProject((int) $id);
        if ($service === null) {
            Response::abort(404, 'Service not found.');
        }

        Response::view('services/show', [
            'title'   => $service['name'],
            'service' => $service,
            'ports'   => $this->children->portsOf((int) $id),
            'envs'    => $this->children->envsOf((int) $id),
            'volumes' => $this->children->volumesOf((int) $id),
            'webapps' => $this->services->webAppsOf((int) $id),
        ]);
    }

    public function edit(Request $request, string $id): void
    {
        $service = $this->services->findWithProject((int) $id);
        if ($service === null) {
            Response::abort(404, 'Service not found.');
        }

        Response::view('services/edit', [
            'title'   => 'Edit ' . $service['name'],
            'service' => $service,
            'ports'   => $this->children->portsOf((int) $id),
            'envs'    => $this->children->envsOf((int) $id),
            'volumes' => $this->children->volumesOf((int) $id),
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $service = $this->services->findWithProject((int) $id);
        if ($service === null) {
            Response::abort(404, 'Service not found.');
        }

        $data   = $this->serviceInput($request);
        $errors = $this->validate($data, (int) $service['project_id'], (int) $id);

        if ($errors !== []) {
            foreach ($errors as $error) {
                Session::flash('error', $error);
            }
            Session::flashOldInput($request->all());
            Response::redirect('/services/' . (int) $id . '/edit');
        }

        $this->services->update((int) $id, $data);
        $this->children->syncAll(
            (int) $id,
            $this->normalizeRows($request->raw('ports'), ['host_port', 'container_port', 'protocol']),
            $this->normalizeRows($request->raw('envs'), ['key_name', 'value', 'is_secret']),
            $this->normalizeRows($request->raw('volumes'), ['host_path', 'container_path', 'mode']),
        );

        Session::flash('success', 'Service "' . $data['name'] . '" updated.');
        Response::redirect('/services/' . (int) $id);
    }

    public function destroy(Request $request, string $id): void
    {
        $service = $this->services->find((int) $id);
        if ($service === null) {
            Response::abort(404, 'Service not found.');
        }

        $this->services->delete((int) $id);
        Session::flash('success', 'Service "' . $service->name . '" deleted.');
        Response::redirect('/projects/' . $service->projectId);
    }

    /** @return array{name:string,image:string,restart_policy:string,notes:?string} */
    private function serviceInput(Request $request): array
    {
        $restartPolicy = $request->input('restart_policy', 'unless-stopped') ?? 'unless-stopped';
        if (!in_array($restartPolicy, self::RESTART_POLICIES, true)) {
            $restartPolicy = 'unless-stopped';
        }
        $notes = $request->input('notes', '') ?? '';

        return [
            'name'           => $request->input('name', '') ?? '',
            'image'          => $request->input('image', '') ?? '',
            'restart_policy' => $restartPolicy,
            'notes'          => $notes === '' ? null : $notes,
        ];
    }

    /** @return string[] Validation error messages (empty array = valid). */
    private function validate(array $data, int $projectId, ?int $ignoreId): array
    {
        $errors = [];
        if ($data['name'] === '') {
            $errors[] = 'Name is required.';
        } elseif ($this->services->nameExistsInProject($data['name'], $projectId, $ignoreId)) {
            $errors[] = 'A service named "' . $data['name'] . '" already exists in this project.';
        }
        if ($data['image'] === '') {
            $errors[] = 'Image is required.';
        }
        return $errors;
    }

    /**
     * Turn the parallel-array form shape (ports[host_port][], ports[container_port][], ...)
     * into a list of per-row assoc arrays for ServiceChildrenRepository::syncAll().
     *
     * @param string[] $fields
     * @return array<int,array<string,string>>
     */
    private function normalizeRows(mixed $raw, array $fields): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $first = $raw[$fields[0]] ?? null;
        if (!is_array($first)) {
            return [];
        }

        $rows = [];
        foreach (array_keys($first) as $i) {
            $row = [];
            foreach ($fields as $field) {
                $value = $raw[$field][$i] ?? '';
                $row[$field] = is_string($value) ? trim($value) : '';
            }
            $rows[] = $row;
        }
        return $rows;
    }
}
