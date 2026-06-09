<?php
/**
 * @var array                          $service  id, project_id, name, image, restart_policy, notes, project_name
 * @var \Manifesto\Models\PortMapping[] $ports
 * @var \Manifesto\Models\EnvVar[]      $envs
 * @var \Manifesto\Models\Volume[]      $volumes
 * @var string                         $title
 */
$isAdmin = ($currentUser['role'] ?? '') === 'admin';
?>
<div class="page-head">
    <div>
        <h1>Edit Service</h1>
        <p class="muted">Project: <a href="<?= url('/projects/' . (int) $service['project_id']) ?>"><?= e($service['project_name']) ?></a></p>
    </div>
</div>

<form method="post" action="<?= url('/services/' . (int) $service['id']) ?>">
    <?= csrf_field() ?>

    <div class="card">
        <h2 class="card-title">Basics</h2>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" value="<?= e(old('name', $service['name'])) ?>" maxlength="128" required autofocus>
            </div>
            <div class="form-group">
                <label for="image">Docker image *</label>
                <input type="text" id="image" name="image" value="<?= e(old('image', $service['image'])) ?>" maxlength="255" required>
            </div>
        </div>

        <div class="form-group">
            <label for="restart_policy">Restart policy</label>
            <select id="restart_policy" name="restart_policy">
                <?php
                $currentPolicy = old('restart_policy', $service['restart_policy']);
                foreach (['no', 'always', 'on-failure', 'unless-stopped'] as $policy):
                ?>
                    <option value="<?= e($policy) ?>" <?= $currentPolicy === $policy ? 'selected' : '' ?>><?= e($policy) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3"><?= e(old('notes', $service['notes'] ?? '')) ?></textarea>
        </div>
    </div>

    <div class="card">
        <div class="page-head">
            <h2 class="card-title">Port mappings</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-add-row="port" data-target="#ports-rows">+ Add port</button>
        </div>
        <div class="child-rows" id="ports-rows">
            <?php if ($ports !== []): ?>
                <?php foreach ($ports as $port): ?>
                    <div class="child-row">
                        <input type="number" name="ports[host_port][]" value="<?= (int) $port->hostPort ?>" min="1" max="65535">
                        <input type="number" name="ports[container_port][]" value="<?= (int) $port->containerPort ?>" min="1" max="65535">
                        <select name="ports[protocol][]">
                            <option value="tcp" <?= $port->protocol === 'tcp' ? 'selected' : '' ?>>tcp</option>
                            <option value="udp" <?= $port->protocol === 'udp' ? 'selected' : '' ?>>udp</option>
                        </select>
                        <button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="child-row">
                    <input type="number" name="ports[host_port][]" placeholder="8080" min="1" max="65535">
                    <input type="number" name="ports[container_port][]" placeholder="80" min="1" max="65535">
                    <select name="ports[protocol][]">
                        <option value="tcp">tcp</option>
                        <option value="udp">udp</option>
                    </select>
                    <button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="page-head">
            <h2 class="card-title">Environment variables</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-add-row="env" data-target="#envs-rows">+ Add variable</button>
        </div>
        <div class="child-rows" id="envs-rows">
            <?php if ($envs !== []): ?>
                <?php foreach ($envs as $env): ?>
                    <div class="child-row">
                        <input type="text" name="envs[key_name][]" value="<?= e($env->keyName) ?>">
                        <input type="text" name="envs[value][]" value="<?= e($env->value) ?>">
                        <label>
                            <input type="hidden" name="envs[is_secret][]" value="<?= $env->isSecret ? '1' : '0' ?>" class="env-secret-hidden">
                            <input type="checkbox" <?= $env->isSecret ? 'checked' : '' ?> onchange="this.previousElementSibling.value = this.checked ? '1' : '0'"> secret
                        </label>
                        <button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="child-row">
                    <input type="text" name="envs[key_name][]" placeholder="MYSQL_ROOT_PASSWORD">
                    <input type="text" name="envs[value][]" placeholder="value">
                    <label>
                        <input type="hidden" name="envs[is_secret][]" value="0" class="env-secret-hidden">
                        <input type="checkbox" onchange="this.previousElementSibling.value = this.checked ? '1' : '0'"> secret
                    </label>
                    <button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="page-head">
            <h2 class="card-title">Volumes</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-add-row="volume" data-target="#volumes-rows">+ Add volume</button>
        </div>
        <div class="child-rows" id="volumes-rows">
            <?php if ($volumes !== []): ?>
                <?php foreach ($volumes as $volume): ?>
                    <div class="child-row">
                        <input type="text" name="volumes[host_path][]" value="<?= e($volume->hostPath) ?>">
                        <input type="text" name="volumes[container_path][]" value="<?= e($volume->containerPath) ?>">
                        <select name="volumes[mode][]">
                            <option value="rw" <?= $volume->mode === 'rw' ? 'selected' : '' ?>>rw</option>
                            <option value="ro" <?= $volume->mode === 'ro' ? 'selected' : '' ?>>ro</option>
                        </select>
                        <button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="child-row">
                    <input type="text" name="volumes[host_path][]" placeholder="./data">
                    <input type="text" name="volumes[container_path][]" placeholder="/var/lib/mysql">
                    <select name="volumes[mode][]">
                        <option value="rw">rw</option>
                        <option value="ro">ro</option>
                    </select>
                    <button type="button" class="btn btn-ghost btn-sm row-remove" aria-label="Remove">×</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="form-footer">
            <button type="submit" class="btn btn-primary">Save service</button>
            <a class="btn btn-secondary" href="<?= url('/services/' . (int) $service['id']) ?>">Cancel</a>
        </div>
    </div>

</form>

<?php if ($isAdmin): ?>
    <div class="card">
        <h2 class="card-title">Danger zone</h2>
        <form method="post" action="<?= url('/services/' . (int) $service['id'] . '/delete') ?>"
              data-confirm="Delete service &quot;<?= e($service['name']) ?>&quot; and all its ports, env vars and volumes?">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">Delete service</button>
        </form>
    </div>
<?php endif; ?>

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
