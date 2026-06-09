<?php

declare(strict_types=1);

namespace Manifesto\Controllers;

use Manifesto\Core\Request;
use Manifesto\Core\Response;
use Manifesto\Core\Session;
use Manifesto\Repositories\WebAppRepository;

final class WebAppController
{
    private WebAppRepository $webApps;

    public function __construct()
    {
        $this->webApps = new WebAppRepository();
    }

    public function create(Request $request, string $serviceId): void
    {
        $context = $this->webApps->serviceContext((int) $serviceId);
        if ($context === null) {
            Response::abort(404, 'Service not found.');
        }
        Response::view('webapps/create', [
            'title'   => 'New Web App',
            'context' => $context,
        ]);
    }

    public function store(Request $request, string $serviceId): void
    {
        $sid = (int) $serviceId;
        $context = $this->webApps->serviceContext($sid);
        if ($context === null) {
            Response::abort(404, 'Service not found.');
        }
        $data = $this->validated($request, '/services/' . $sid . '/webapps/create');
        $id = $this->webApps->insert(['service_id' => $sid] + $data);
        Session::flash('success', 'Web app "' . $data['name'] . '" created.');
        Response::redirect('/webapps/' . $id);
    }

    public function show(Request $request, string $id): void
    {
        $webApp = $this->webApps->findWithContext((int) $id);
        if ($webApp === null) {
            Response::abort(404, 'Web app not found.');
        }
        Response::view('webapps/show', [
            'title'  => $webApp['name'],
            'webApp' => $webApp,
        ]);
    }

    public function edit(Request $request, string $id): void
    {
        $webApp = $this->webApps->findWithContext((int) $id);
        if ($webApp === null) {
            Response::abort(404, 'Web app not found.');
        }
        Response::view('webapps/edit', [
            'title'  => 'Edit ' . $webApp['name'],
            'webApp' => $webApp,
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $webAppId = (int) $id;
        if ($this->webApps->find($webAppId) === null) {
            Response::abort(404, 'Web app not found.');
        }
        $data = $this->validated($request, '/webapps/' . $webAppId . '/edit');
        $this->webApps->update($webAppId, $data);
        Session::flash('success', 'Web app "' . $data['name'] . '" updated.');
        Response::redirect('/webapps/' . $webAppId);
    }

    public function destroy(Request $request, string $id): void
    {
        $webApp = $this->webApps->find((int) $id);
        if ($webApp === null) {
            Response::abort(404, 'Web app not found.');
        }
        $this->webApps->delete($webApp->id);
        Session::flash('success', 'Web app "' . $webApp->name . '" deleted.');
        Response::redirect('/services/' . $webApp->serviceId);
    }

    /**
     * Validate form input; on failure flash errors + old input and
     * redirect back to $backTo (never returns in that case).
     *
     * @return array{name:string,public_url:?string,dns_name:?string,notes:?string}
     */
    private function validated(Request $request, string $backTo): array
    {
        $name      = $request->input('name', '') ?? '';
        $publicUrl = $request->input('public_url', '') ?? '';
        $dnsName   = $request->input('dns_name', '') ?? '';
        $notes     = $request->input('notes', '') ?? '';

        $errors = [];
        if ($name === '') {
            $errors[] = 'Name is required.';
        } elseif (mb_strlen($name) > 128) {
            $errors[] = 'Name must be 128 characters or less.';
        }
        if ($publicUrl !== '' && filter_var($publicUrl, FILTER_VALIDATE_URL) === false) {
            $errors[] = 'Public URL must be a valid URL (e.g. http://localhost:8080).';
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                Session::flash('error', $error);
            }
            Session::flashOldInput($request->all());
            Response::redirect($backTo);
        }

        return [
            'name'       => $name,
            'public_url' => $publicUrl === '' ? null : $publicUrl,
            'dns_name'   => $dnsName === '' ? null : $dnsName,
            'notes'      => $notes === '' ? null : $notes,
        ];
    }
}
