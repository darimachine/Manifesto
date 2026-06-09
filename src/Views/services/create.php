<?php
/**
 * @var int    $projectId
 * @var string $projectName
 * @var string $title
 */
?>
<div class="page-head">
    <div>
        <h1>New Service</h1>
        <p class="muted">Project: <a href="<?= url('/projects/' . $projectId) ?>"><?= e($projectName) ?></a></p>
    </div>
</div>

<form method="post" action="<?= url('/projects/' . $projectId . '/services') ?>">
    <?= csrf_field() ?>

    <div class="card">
        <h2 class="card-title">Basics</h2>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" maxlength="128" required autofocus placeholder="web">
            </div>
            <div class="form-group">
                <label for="image">Docker image *</label>
                <input type="text" id="image" name="image" value="<?= old('image') ?>" maxlength="255" required placeholder="nginx:alpine">
            </div>
        </div>

        <div class="form-group">
            <label for="restart_policy">Restart policy</label>
            <select id="restart_policy" name="restart_policy">
                <option value="no"             <?= old('restart_policy', 'unless-stopped') === 'no'             ? 'selected' : '' ?>>no</option>
                <option value="always"         <?= old('restart_policy', 'unless-stopped') === 'always'         ? 'selected' : '' ?>>always</option>
                <option value="on-failure"     <?= old('restart_policy', 'unless-stopped') === 'on-failure'     ? 'selected' : '' ?>>on-failure</option>
                <option value="unless-stopped" <?= old('restart_policy', 'unless-stopped') === 'unless-stopped' ? 'selected' : '' ?>>unless-stopped</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3"><?= old('notes') ?></textarea>
        </div>
    </div>

    <div class="card">
        <div class="page-head">
            <h2 class="card-title">Port mappings</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-add-row="port" data-target="#ports-rows">+ Add port</button>
        </div>
        <div class="child-rows" id="ports-rows">
            <div class="child-row">
                <input type="number" name="ports[host_port][]" placeholder="8080" min="1" max="65535">
                <input type="number" name="ports[container_port][]" placeholder="80" min="1" max="65535">
                <select name="ports[protocol][]">
                    <option value="tcp">tcp</option>
                    <option value="udp">udp</option>
                </select>
                <button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="page-head">
            <h2 class="card-title">Environment variables</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-add-row="env" data-target="#envs-rows">+ Add variable</button>
        </div>
        <div class="child-rows" id="envs-rows">
            <div class="child-row">
                <input type="text" name="envs[key_name][]" placeholder="MYSQL_ROOT_PASSWORD">
                <input type="text" name="envs[value][]" placeholder="value">
                <label>
                    <input type="hidden" name="envs[is_secret][]" value="0" class="env-secret-hidden">
                    <input type="checkbox" onchange="this.previousElementSibling.value = this.checked ? '1' : '0'"> secret
                </label>
                <button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="page-head">
            <h2 class="card-title">Volumes</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-add-row="volume" data-target="#volumes-rows">+ Add volume</button>
        </div>
        <div class="child-rows" id="volumes-rows">
            <div class="child-row">
                <input type="text" name="volumes[host_path][]" placeholder="./data">
                <input type="text" name="volumes[container_path][]" placeholder="/var/lib/mysql">
                <select name="volumes[mode][]">
                    <option value="rw">rw</option>
                    <option value="ro">ro</option>
                </select>
                <button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="form-footer">
            <button type="submit" class="btn btn-primary">Create service</button>
            <a class="btn btn-secondary" href="<?= url('/projects/' . $projectId) ?>">Cancel</a>
        </div>
    </div>

</form>

<script>
(function () {
    function makePortRow() {
        const div = document.createElement('div');
        div.className = 'child-row';
        div.innerHTML =
            '<input type="number" name="ports[host_port][]" placeholder="8080" min="1" max="65535">' +
            '<input type="number" name="ports[container_port][]" placeholder="80" min="1" max="65535">' +
            '<select name="ports[protocol][]"><option value="tcp">tcp</option><option value="udp">udp</option></select>' +
            '<button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>';
        return div;
    }

    function makeEnvRow() {
        const div = document.createElement('div');
        div.className = 'child-row';
        div.innerHTML =
            '<input type="text" name="envs[key_name][]" placeholder="MYSQL_ROOT_PASSWORD">' +
            '<input type="text" name="envs[value][]" placeholder="value">' +
            '<label>' +
            '<input type="hidden" name="envs[is_secret][]" value="0" class="env-secret-hidden">' +
            '<input type="checkbox" onchange="this.previousElementSibling.value = this.checked ? \'1\' : \'0\'"> secret' +
            '</label>' +
            '<button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>';
        return div;
    }

    function makeVolumeRow() {
        const div = document.createElement('div');
        div.className = 'child-row';
        div.innerHTML =
            '<input type="text" name="volumes[host_path][]" placeholder="./data">' +
            '<input type="text" name="volumes[container_path][]" placeholder="/var/lib/mysql">' +
            '<select name="volumes[mode][]"><option value="rw">rw</option><option value="ro">ro</option></select>' +
            '<button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>';
        return div;
    }

    const factories = { port: makePortRow, env: makeEnvRow, volume: makeVolumeRow };

    document.querySelectorAll('[data-add-row]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.querySelector(btn.dataset.target);
            const factory = factories[btn.dataset.addRow];
            if (target && factory) {
                target.appendChild(factory());
            }
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('row-remove')) {
            const row = e.target.closest('.child-row');
            if (row) { row.remove(); }
        }
    });
}());
</script>
