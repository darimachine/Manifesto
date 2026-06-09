<?php

declare(strict_types=1);

namespace Manifesto\Controllers;

use Manifesto\Core\Request;
use Manifesto\Core\Response;
use Manifesto\Core\Session;
use Manifesto\Repositories\DockerHostRepository;
use Manifesto\Repositories\GeneratedFileRepository;
use Manifesto\Repositories\ProjectRepository;
use Manifesto\Repositories\ServiceChildrenRepository;
use Manifesto\Repositories\ServiceRepository;
use Throwable;

final class GenerationController
{
    private ProjectRepository $projects;
    private GeneratedFileRepository $files;
    private \Manifesto\Services\DockerComposeGenerator $compose;
    private \Manifesto\Services\EnvFileGenerator $envGen;
    private \Manifesto\Services\EmmetExporter $emmet;

    public function __construct()
    {
        $this->projects = new ProjectRepository();
        $children       = new ServiceChildrenRepository();
        $hosts          = new DockerHostRepository();
        $services       = new ServiceRepository();
        $this->files    = new GeneratedFileRepository();
        $this->compose  = new \Manifesto\Services\DockerComposeGenerator($this->projects, $children);
        $this->envGen   = new \Manifesto\Services\EnvFileGenerator($this->projects, $children);
        $this->emmet    = new \Manifesto\Services\EmmetExporter($hosts, $this->projects, $services, $children);
    }

    /**
     * POST /projects/{id}/generate
     * Runs all three generators and saves a new versioned set.
     */
    public function generate(Request $request, string $id): void
    {
        $projectId = (int) $id;
        $project   = $this->projects->findWithHost($projectId);
        if ($project === null) {
            Response::abort(404, 'Project not found.');
        }

        $services = $this->projects->servicesOf($projectId);
        if (count($services) === 0) {
            Session::flash('error', 'Add at least one service before generating files.');
            Response::redirect('/projects/' . $projectId);
        }

        try {
            $compose = $this->compose->generate($projectId);
            $env     = $this->envGen->generate($projectId);
            $emmet   = $this->emmet->export($projectId);
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
            Response::redirect('/projects/' . $projectId);
        }

        $version = $this->files->insertSetForProject($projectId, [
            'docker-compose' => $compose,
            'env'            => $env,
            'emmet'          => $emmet,
        ]);

        Session::flash('success', 'Generated v' . $version . ' of docker-compose.yml, .env and Emmet export.');
        Response::redirect('/projects/' . $projectId . '/files');
    }

    /**
     * GET /projects/{id}/files
     * Shows latest generated files and full version history.
     */
    public function files(Request $request, string $id): void
    {
        $projectId = (int) $id;
        $project   = $this->projects->findWithHost($projectId);
        if ($project === null) {
            Response::abort(404, 'Project not found.');
        }

        $compose = $this->files->latestForProject($projectId, 'docker-compose');
        $env     = $this->files->latestForProject($projectId, 'env');
        $emmet   = $this->files->latestForProject($projectId, 'emmet');
        $history = $this->files->historyForProject($projectId);

        Response::view('projects/files', [
            'title'   => 'Generated Files — ' . $project['name'],
            'project' => $project,
            'compose' => $compose,
            'env'     => $env,
            'emmet'   => $emmet,
            'history' => $history,
        ]);
    }

    /**
     * GET /projects/{id}/emmet
     * Shows the latest Emmet export for the project.
     */
    public function emmet(Request $request, string $id): void
    {
        $projectId = (int) $id;
        $project   = $this->projects->findWithHost($projectId);
        if ($project === null) {
            Response::abort(404, 'Project not found.');
        }

        $emmet = $this->files->latestForProject($projectId, 'emmet');

        Response::view('projects/emmet', [
            'title'   => 'Emmet Export — ' . $project['name'],
            'project' => $project,
            'emmet'   => $emmet,
        ]);
    }

    /**
     * GET /files/{id}/download
     * Streams a generated file as a download.
     */
    public function download(Request $request, string $id): void
    {
        $fileId = (int) $id;
        $file   = $this->files->findById($fileId);
        if ($file === null) {
            Response::abort(404, 'File not found.');
        }

        Response::download($file->content, $file->filename(), $file->mimeType());
    }
}
