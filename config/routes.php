<?php

declare(strict_types=1);

use Manifesto\Controllers\AuthController;
use Manifesto\Controllers\DashboardController;
use Manifesto\Controllers\DockerHostController;
use Manifesto\Controllers\GenerationController;
use Manifesto\Controllers\ProjectController;
use Manifesto\Controllers\ServiceController;
use Manifesto\Controllers\WebAppController;

/**
 * [METHOD, pattern, Controller, action, access]
 * access: 'guest' | 'auth' (default) | 'admin'
 * All mutations (POST) that change data are 'admin' — enforced by the Router.
 */
return [
    // Auth
    ['GET',  '/login',  AuthController::class, 'showLogin', 'guest'],
    ['POST', '/login',  AuthController::class, 'login',     'guest'],
    ['POST', '/logout', AuthController::class, 'logout',    'auth'],

    // Dashboard
    ['GET', '/', DashboardController::class, 'index', 'auth'],

    // Docker hosts
    ['GET',  '/docker-hosts',             DockerHostController::class, 'index',  'auth'],
    ['GET',  '/docker-hosts/create',      DockerHostController::class, 'create', 'admin'],
    ['POST', '/docker-hosts',             DockerHostController::class, 'store',  'admin'],
    ['GET',  '/docker-hosts/{id}',        DockerHostController::class, 'show',   'auth'],
    ['GET',  '/docker-hosts/{id}/edit',   DockerHostController::class, 'edit',   'admin'],
    ['POST', '/docker-hosts/{id}',        DockerHostController::class, 'update', 'admin'],
    ['POST', '/docker-hosts/{id}/delete', DockerHostController::class, 'destroy','admin'],

    // Projects
    ['GET',  '/projects',             ProjectController::class, 'index',  'auth'],
    ['GET',  '/projects/create',      ProjectController::class, 'create', 'admin'],
    ['POST', '/projects',             ProjectController::class, 'store',  'admin'],
    ['GET',  '/projects/{id}',        ProjectController::class, 'show',   'auth'],
    ['GET',  '/projects/{id}/edit',   ProjectController::class, 'edit',   'admin'],
    ['POST', '/projects/{id}',        ProjectController::class, 'update', 'admin'],
    ['POST', '/projects/{id}/delete', ProjectController::class, 'destroy','admin'],

    // Services (created inside a project)
    ['GET',  '/projects/{projectId}/services/create', ServiceController::class, 'create', 'admin'],
    ['POST', '/projects/{projectId}/services',        ServiceController::class, 'store',  'admin'],
    ['GET',  '/services/{id}',        ServiceController::class, 'show',    'auth'],
    ['GET',  '/services/{id}/edit',   ServiceController::class, 'edit',    'admin'],
    ['POST', '/services/{id}',        ServiceController::class, 'update',  'admin'],
    ['POST', '/services/{id}/delete', ServiceController::class, 'destroy', 'admin'],

    // Web apps (created inside a service)
    ['GET',  '/services/{serviceId}/webapps/create', WebAppController::class, 'create', 'admin'],
    ['POST', '/services/{serviceId}/webapps',        WebAppController::class, 'store',  'admin'],
    ['GET',  '/webapps/{id}',        WebAppController::class, 'show',    'auth'],
    ['GET',  '/webapps/{id}/edit',   WebAppController::class, 'edit',    'admin'],
    ['POST', '/webapps/{id}',        WebAppController::class, 'update',  'admin'],
    ['POST', '/webapps/{id}/delete', WebAppController::class, 'destroy', 'admin'],

    // Generation
    ['POST', '/projects/{id}/generate',       GenerationController::class, 'generate', 'admin'],
    ['GET',  '/projects/{id}/files',          GenerationController::class, 'files',    'auth'],
    ['GET',  '/projects/{id}/emmet',          GenerationController::class, 'emmet',    'auth'],
    ['GET',  '/files/{id}/download',          GenerationController::class, 'download', 'auth'],
];
