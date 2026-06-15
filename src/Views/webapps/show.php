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
    <div class="page-actions">
        <form method="post" action="<?= url('/webapps/' . $webApp['id'] . '/check') ?>" class="inline-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-secondary">🩺 Check status</button>
        </form>
        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
            <a class="btn btn-secondary" href="<?= url('/webapps/' . $webApp['id'] . '/edit') ?>">Edit</a>
            <form method="post" action="<?= url('/webapps/' . $webApp['id'] . '/delete') ?>"
                  class="inline-form" data-confirm="Delete this web app?">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Details</h2>
    <dl class="detail-list">
        <dt>Status</dt>
        <dd>
            <?php $status = $webApp['status'] ?? 'unknown'; ?>
            <span class="status-badge status-<?= e($status) ?>">
                <?= $status === 'up' ? '🟢 UP' : ($status === 'down' ? '🔴 DOWN' : ($status === 'error' ? '⚠️ ERROR' : '⚪ UNKNOWN')) ?>
            </span>
            <?php if (!empty($webApp['last_checked_at'])): ?>
                <span class="muted" style="margin-left:.5rem;font-size:.85rem;">
                    Last checked: <?= e($webApp['last_checked_at']) ?>
                    <?php if (!empty($webApp['last_http_code'])): ?>
                        · HTTP <?= (int) $webApp['last_http_code'] ?>
                        · <?= (int) $webApp['last_duration_ms'] ?>ms
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </dd>

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
