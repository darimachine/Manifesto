<?php
/**
 * @var array<string,mixed> $project
 * @var array<int,array<string,mixed>> $services
 * @var int $latestVersion
 */
$projectId = (int) $project['id'];
$isAdmin   = ($currentUser['role'] ?? '') === 'admin';
?>
<div class="page-head">
    <h1><?= e($project['name']) ?></h1>
    <div class="page-actions">
        <?php if ($isAdmin): ?>
            <form method="post" action="<?= url('/projects/' . $projectId . '/generate') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">Generate files</button>
            </form>
        <?php endif; ?>
        <?php if ($latestVersion > 0): ?>
            <a class="btn btn-secondary" href="<?= url('/projects/' . $projectId . '/files') ?>">View files (v<?= $latestVersion ?>)</a>
            <a class="btn btn-secondary" href="<?= url('/projects/' . $projectId . '/export') ?>">⬇ Export JSON</a>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
            <a class="btn btn-secondary" href="<?= url('/projects/' . $projectId . '/edit') ?>">Edit</a>
            <form method="post" action="<?= url('/projects/' . $projectId . '/delete') ?>" data-confirm="Delete project &quot;<?= e($project['name']) ?>&quot; and all its services?">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Details</h2>
    <dl class="detail-list">
        <dt>Slug</dt>
        <dd><code><?= e($project['slug']) ?></code></dd>
        <dt>Docker host</dt>
        <dd><a href="<?= url('/docker-hosts/' . (int) $project['docker_host_id']) ?>"><?= e($project['host_name']) ?></a></dd>
        <dt>Description</dt>
        <dd><?= $project['description'] !== null && $project['description'] !== '' ? e($project['description']) : '<span class="muted">—</span>' ?></dd>
        <dt>Created</dt>
        <dd><?= e($project['created_at']) ?></dd>
        <dt>Updated</dt>
        <dd><?= e($project['updated_at']) ?></dd>
    </dl>
</div>

<div class="card">
    <div class="page-head">
        <h2 class="card-title">Services</h2>
        <?php if ($isAdmin): ?>
            <div class="page-actions">
                <a class="btn btn-primary btn-sm" href="<?= url('/projects/' . $projectId . '/services/create') ?>">+ Add Service</a>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($services === []): ?>
        <div class="empty-state">
            <p>No services declared for this project yet.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Restart policy</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><a href="<?= url('/services/' . (int) $service['id']) ?>"><?= e($service['name']) ?></a></td>
                        <td><code><?= e($service['image']) ?></code></td>
                        <td><span class="badge badge-type"><?= e($service['restart_policy']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
