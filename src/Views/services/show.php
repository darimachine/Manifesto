<?php
/**
 * @var array $service   Service row + project_name
 * @var \Manifesto\Models\PortMapping[] $ports
 * @var \Manifesto\Models\EnvVar[] $envs
 * @var \Manifesto\Models\Volume[] $volumes
 * @var array<int,array{id:int,name:string}> $webapps
 */
$isAdmin = ($currentUser['role'] ?? '') === 'admin';
?>
<div class="page-head">
    <div>
        <h1><?= e($service['name']) ?></h1>
        <p class="muted">
            Service in project
            <a href="<?= url('/projects/' . $service['project_id']) ?>"><?= e($service['project_name']) ?></a>
        </p>
    </div>
    <?php if ($isAdmin): ?>
        <div class="page-actions">
            <a class="btn btn-secondary" href="<?= url('/services/' . $service['id'] . '/edit') ?>">Edit</a>
            <form class="inline-form" method="post"
                  action="<?= url('/services/' . $service['id'] . '/delete') ?>"
                  data-confirm="Delete this service and all its ports, env vars and volumes?">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="card-title">Details</h2>
    <dl class="detail-list">
        <dt>Image</dt>
        <dd><code><?= e($service['image']) ?></code></dd>
        <dt>Restart policy</dt>
        <dd><?= e($service['restart_policy']) ?></dd>
        <dt>Notes</dt>
        <dd><?= $service['notes'] !== null && $service['notes'] !== '' ? e($service['notes']) : '<span class="muted">—</span>' ?></dd>
        <dt>Created</dt>
        <dd><?= e($service['created_at']) ?></dd>
        <dt>Updated</dt>
        <dd><?= e($service['updated_at']) ?></dd>
    </dl>
</div>

<div class="card">
    <h2 class="card-title">Port mappings</h2>
    <?php if ($ports === []): ?>
        <p class="muted">No port mappings declared.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Host port</th>
                    <th>Container port</th>
                    <th>Protocol</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($ports as $port): ?>
                    <tr>
                        <td><?= e((string) $port->hostPort) ?></td>
                        <td><?= e((string) $port->containerPort) ?></td>
                        <td><?= e($port->protocol) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="card-title">Environment variables</h2>
    <?php if ($envs === []): ?>
        <p class="muted">No environment variables declared.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                    <th>Secret</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($envs as $env): ?>
                    <tr>
                        <td><code><?= e($env->keyName) ?></code></td>
                        <td><?= $env->isSecret ? '••••••' : e($env->value) ?></td>
                        <td><?= $env->isSecret ? '<span class="badge badge-secret">secret</span>' : '<span class="muted">—</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="card-title">Volumes</h2>
    <?php if ($volumes === []): ?>
        <p class="muted">No volumes declared.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Host path</th>
                    <th>Container path</th>
                    <th>Mode</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($volumes as $volume): ?>
                    <tr>
                        <td><code><?= e($volume->hostPath) ?></code></td>
                        <td><code><?= e($volume->containerPath) ?></code></td>
                        <td><?= e($volume->mode) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="card-title">Web apps</h2>
    <?php if ($webapps === []): ?>
        <p class="muted">No web apps in this service yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($webapps as $webapp): ?>
                <li><a href="<?= url('/webapps/' . $webapp['id']) ?>"><?= e($webapp['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <p style="margin-top:.75rem;">
            <a class="btn btn-primary btn-sm" href="<?= url('/services/' . $service['id'] . '/webapps/create') ?>">+ Add Web App</a>
        </p>
    <?php endif; ?>
</div>
