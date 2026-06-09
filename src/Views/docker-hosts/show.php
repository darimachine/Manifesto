<?php /** @var \Manifesto\Models\DockerHost $host */ ?>
<?php $isAdmin = ($currentUser['role'] ?? '') === 'admin'; ?>

<div class="page-head">
    <h1><?= e($host->name) ?></h1>
    <?php if ($isAdmin): ?>
        <div class="page-actions">
            <a class="btn btn-secondary" href="<?= url('/docker-hosts/' . $host->id . '/edit') ?>">Edit</a>
            <form method="post" action="<?= url('/docker-hosts/' . $host->id . '/delete') ?>" class="inline-form" data-confirm="Delete this host?">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="card-title">Host details</h2>
    <dl class="detail-list">
        <dt>Name</dt>
        <dd><?= e($host->name) ?></dd>

        <dt>IP address</dt>
        <dd><?= $host->ipAddress !== null ? e($host->ipAddress) : '<span class="muted">—</span>' ?></dd>

        <dt>OS</dt>
        <dd><?= $host->os !== null ? e($host->os) : '<span class="muted">—</span>' ?></dd>

        <dt>Docker version</dt>
        <dd><?= $host->dockerVersion !== null ? e($host->dockerVersion) : '<span class="muted">—</span>' ?></dd>

        <dt>Notes</dt>
        <dd><?= $host->notes !== null ? nl2br(e($host->notes)) : '<span class="muted">—</span>' ?></dd>

        <dt>Created</dt>
        <dd class="muted"><?= e($host->createdAt) ?></dd>

        <dt>Updated</dt>
        <dd class="muted"><?= e($host->updatedAt) ?></dd>
    </dl>
</div>
