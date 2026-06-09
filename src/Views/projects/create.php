<?php /** @var array<int,array<string,mixed>> $hosts */ ?>
<div class="page-head">
    <h1>New Project</h1>
</div>

<div class="card">
    <form method="post" action="<?= url('/projects') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="docker_host_id">Docker host</label>
            <select id="docker_host_id" name="docker_host_id" required>
                <option value="">— choose a host —</option>
                <?php foreach ($hosts as $host): ?>
                    <option value="<?= (int) $host['id'] ?>" <?= old('docker_host_id') === (string) (int) $host['id'] ? 'selected' : '' ?>><?= e($host['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" maxlength="128" required autofocus>
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" value="<?= old('slug') ?>" maxlength="128">
                <p class="muted">Leave empty to auto-generate from name.</p>
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= old('description') ?></textarea>
        </div>
        <div class="form-footer">
            <button type="submit" class="btn btn-primary">Create Project</button>
            <a class="btn btn-secondary" href="<?= url('/projects') ?>">Cancel</a>
        </div>
    </form>
</div>
