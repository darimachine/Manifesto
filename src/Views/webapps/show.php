<?php /** @var array<string,mixed> $webApp */ ?>
<div class="page-head">
    <div>
        <h1><?= e($webApp['name']) ?></h1>
        <p class="muted">
            <a href="<?= url('/projects/' . $webApp['project_id']) ?>"><?= e($webApp['project_name']) ?></a>
            &rsaquo;
            <a href="<?= url('/services/' . $webApp['service_id']) ?>"><?= e($webApp['service_name']) ?></a>
        </p>
    </div>
    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
        <div class="page-actions">
            <a class="btn btn-secondary" href="<?= url('/webapps/' . $webApp['id'] . '/edit') ?>">Edit</a>
            <form method="post" action="<?= url('/webapps/' . $webApp['id'] . '/delete') ?>"
                  class="inline-form" data-confirm="Delete this web app?">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="card-title">Details</h2>
    <dl class="detail-list">
        <dt>Public URL</dt>
        <dd>
            <?php if (($webApp['public_url'] ?? '') !== '' && $webApp['public_url'] !== null): ?>
                <a href="<?= e($webApp['public_url']) ?>" target="_blank" rel="noopener"><?= e($webApp['public_url']) ?></a>
            <?php else: ?>
                <span class="muted">—</span>
            <?php endif; ?>
        </dd>

        <dt>DNS name</dt>
        <dd>
            <?php if (($webApp['dns_name'] ?? '') !== '' && $webApp['dns_name'] !== null): ?>
                <?= e($webApp['dns_name']) ?>
            <?php else: ?>
                <span class="muted">—</span>
            <?php endif; ?>
        </dd>

        <dt>Notes</dt>
        <dd>
            <?php if (($webApp['notes'] ?? '') !== '' && $webApp['notes'] !== null): ?>
                <?= nl2br(e($webApp['notes'])) ?>
            <?php else: ?>
                <span class="muted">—</span>
            <?php endif; ?>
        </dd>

        <dt>Created</dt>
        <dd><?= e($webApp['created_at']) ?></dd>

        <dt>Updated</dt>
        <dd><?= e($webApp['updated_at']) ?></dd>
    </dl>
</div>
