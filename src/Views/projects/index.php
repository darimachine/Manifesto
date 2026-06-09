<?php /** @var array<int,array<string,mixed>> $projects */ ?>
<div class="page-head">
    <h1>Projects</h1>
    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
        <div class="page-actions">
            <a class="btn btn-primary" href="<?= url('/projects/create') ?>">+ New Project</a>
        </div>
    <?php endif; ?>
</div>

<?php if ($projects === []): ?>
    <div class="empty-state">
        <p>No projects declared yet.</p>
        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
            <a class="btn btn-primary" href="<?= url('/projects/create') ?>">+ New Project</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Host</th>
                    <th>Services</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><a href="<?= url('/projects/' . (int) $project['id']) ?>"><?= e($project['name']) ?></a></td>
                        <td><code><?= e($project['slug']) ?></code></td>
                        <td><?= e($project['host_name']) ?></td>
                        <td><?= (int) $project['service_count'] ?></td>
                        <td>
                            <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                                <div class="actions">
                                    <a class="btn btn-secondary btn-sm" href="<?= url('/projects/' . (int) $project['id'] . '/edit') ?>">Edit</a>
                                    <form method="post" action="<?= url('/projects/' . (int) $project['id'] . '/delete') ?>" data-confirm="Delete project &quot;<?= e($project['name']) ?>&quot; and all its services?">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
