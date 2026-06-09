<?php /** @var \Manifesto\Models\DockerHost $host */ ?>
<div class="page-head">
    <h1>Edit <?= e($host->name) ?></h1>
</div>

<div class="card">
    <form method="post" action="<?= url('/docker-hosts/' . $host->id) ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" value="<?= old('name', $host->name) ?>" maxlength="128" required autofocus>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ip_address">IP address</label>
                <input type="text" id="ip_address" name="ip_address" value="<?= old('ip_address', $host->ipAddress) ?>" maxlength="45">
            </div>
            <div class="form-group">
                <label for="docker_version">Docker version</label>
                <input type="text" id="docker_version" name="docker_version" value="<?= old('docker_version', $host->dockerVersion) ?>" maxlength="32">
            </div>
        </div>

        <div class="form-group">
            <label for="os">OS</label>
            <input type="text" id="os" name="os" value="<?= old('os', $host->os) ?>" maxlength="128">
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4"><?= old('notes', $host->notes) ?></textarea>
        </div>

        <div class="form-footer">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a class="btn btn-secondary" href="<?= url('/docker-hosts/' . $host->id) ?>">Cancel</a>
        </div>
    </form>
</div>
