<?php /** @var array<int,array<string,mixed>> $hosts */ ?>
<?php $isAdmin = ($currentUser['role'] ?? '') === 'admin'; ?>

<div class="page-head">
    <h1>Docker Hosts</h1>
    <?php if ($isAdmin): ?>
        <div class="page-actions">
            <a class="btn btn-primary" href="<?= url('/docker-hosts/create') ?>">+ New Host</a>
        </div>
    <?php endif; ?>
</div>

<?php if (count($hosts) === 0): ?>
    <div class="empty-state">
        <p>No Docker hosts declared yet.</p>
        <?php if ($isAdmin): ?>
            <a class="btn btn-primary" href="<?= url('/docker-hosts/create') ?>">+ New Host</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>IP address</th>
                        <th>OS</th>
                        <th>Docker version</th>
                        <th>Projects</th>
                        <?php if ($isAdmin): ?><th></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hosts as $host): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/docker-hosts/' . $host['id']) ?>"><?= e($host['name']) ?></a>
                            </td>
                            <td><?= $host['ip_address'] !== null ? e($host['ip_address']) : '<span class="muted">—</span>' ?></td>
                            <td><?= $host['os'] !== null ? e($host['os']) : '<span class="muted">—</span>' ?></td>
                            <td><?= $host['docker_version'] !== null ? e($host['docker_version']) : '<span class="muted">—</span>' ?></td>
                            <td><?= (int) $host['project_count'] ?></td>
                            <?php if ($isAdmin): ?>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn-secondary btn-sm" href="<?= url('/docker-hosts/' . $host['id'] . '/edit') ?>">Edit</a>
                                        <form method="post" action="<?= url('/docker-hosts/' . $host['id'] . '/delete') ?>" class="inline-form" data-confirm="Delete this host?">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
