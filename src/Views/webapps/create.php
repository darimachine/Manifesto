<?php /** @var array<string,mixed> $context */ ?>
<div class="page-head">
    <div>
        <h1>New Web App</h1>
        <p class="muted">
            <a href="<?= url('/projects/' . $context['project_id']) ?>"><?= e($context['project_name']) ?></a>
            &rsaquo;
            <a href="<?= url('/services/' . $context['service_id']) ?>"><?= e($context['service_name']) ?></a>
        </p>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Web app on <?= e($context['service_name']) ?></h2>
    <form method="post" action="<?= url('/services/' . $context['service_id'] . '/webapps') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" value="<?= old('name') ?>" maxlength="128" required autofocus>
        </div>
        <div class="form-group">
            <label for="public_url">Public URL</label>
            <input type="url" id="public_url" name="public_url" value="<?= old('public_url') ?>">
            <p class="field-hint">e.g. http://localhost:8080</p>
        </div>
        <div class="form-group">
            <label for="dns_name">DNS name</label>
            <input type="text" id="dns_name" name="dns_name" value="<?= old('dns_name') ?>">
        </div>
        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4"><?= old('notes') ?></textarea>
        </div>
        <div class="form-footer">
            <button type="submit" class="btn btn-primary">Create Web App</button>
            <a class="btn btn-secondary" href="<?= url('/services/' . $context['service_id']) ?>">Cancel</a>
        </div>
    </form>
</div>
