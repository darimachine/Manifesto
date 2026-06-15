<?php
/**
 * @var array<string,mixed>              $project
 * @var \Manifesto\Models\GeneratedFile|null $compose
 * @var \Manifesto\Models\GeneratedFile|null $env
 * @var \Manifesto\Models\GeneratedFile|null $emmet
 * @var \Manifesto\Models\GeneratedFile[]    $history
 * @var string                           $title
 */
$projectId  = (int) $project['id'];
$isAdmin    = ($currentUser['role'] ?? '') === 'admin';

// Determine next version number from history
$maxVersion = 0;
foreach ($history as $file) {
    if ($file->versionNumber > $maxVersion) {
        $maxVersion = $file->versionNumber;
    }
}
$nextVersion = $maxVersion + 1;
?>
<div class="page-head">
    <div>
        <h1>Generated files</h1>
        <p class="muted">Project: <a href="<?= url('/projects/' . $projectId) ?>"><?= e($project['name']) ?></a></p>
    </div>
    <?php if ($isAdmin): ?>
        <div class="page-actions">
            <form method="post" action="<?= url('/projects/' . $projectId . '/generate') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">Regenerate (creates v<?= $nextVersion ?>)</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="page-head">
        <h2 class="card-title">
            docker-compose.yml
            <?php if ($compose !== null): ?>
                <span class="muted">(v<?= (int) $compose->versionNumber ?>)</span>
            <?php endif; ?>
        </h2>
        <?php if ($compose !== null): ?>
            <a href="<?= url('/files/' . (int) $compose->id . '/download') ?>" class="btn btn-secondary btn-sm">Download</a>
        <?php endif; ?>
    </div>
    <?php if ($compose !== null): ?>
        <pre class="code-preview"><?= e($compose->content) ?></pre>
    <?php else: ?>
        <p class="muted">Not generated yet. Click Regenerate above.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="page-head">
        <h2 class="card-title">
            .env
            <?php if ($env !== null): ?>
                <span class="muted">(v<?= (int) $env->versionNumber ?>)</span>
            <?php endif; ?>
        </h2>
        <?php if ($env !== null): ?>
            <a href="<?= url('/files/' . (int) $env->id . '/download') ?>" class="btn btn-secondary btn-sm">Download</a>
        <?php endif; ?>
    </div>
    <?php if ($env !== null): ?>
        <pre class="code-preview"><?= e($env->content) ?></pre>
    <?php else: ?>
        <p class="muted">Not generated yet. Click Regenerate above.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="page-head">
        <h2 class="card-title">
            Emmet export
            <?php if ($emmet !== null): ?>
                <span class="muted">(v<?= (int) $emmet->versionNumber ?>)</span>
            <?php endif; ?>
        </h2>
        <div class="page-actions">
            <?php if ($emmet !== null): ?>
                <a href="<?= url('/projects/' . $projectId . '/emmet') ?>" class="btn btn-secondary btn-sm">Full view</a>
                <a href="<?= url('/files/' . (int) $emmet->id . '/download') ?>" class="btn btn-secondary btn-sm">Download</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($emmet !== null): ?>
        <pre class="code-preview"><?= e($emmet->content) ?></pre>
    <?php else: ?>
        <p class="muted">Not generated yet. Click Regenerate above.</p>
    <?php endif; ?>
</div>

<?php
// Filter dockerfiles from history
$dockerfiles = array_filter($history, fn($f) => $f->fileType === 'dockerfile');
?>

<?php if ($dockerfiles !== []): ?>
<div class="card">
    <h2 class="card-title">Generated Dockerfiles</h2>
    <?php foreach ($dockerfiles as $df): ?>
        <details style="margin-bottom:1rem;">
            <summary style="cursor:pointer;font-weight:600;">
                📄 v<?= (int) $df->versionNumber ?>
                <a href="<?= url('/files/' . $df->id . '/download') ?>" class="btn btn-ghost btn-sm" style="float:right;">Download</a>
            </summary>
            <pre class="code-preview" style="margin-top:.75rem;"><?= e($df->content) ?></pre>
        </details>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <h2 class="card-title">Generation history</h2>
    <?php if ($history === []): ?>
        <p class="muted">No files generated yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Version</th>
                    <th>Type</th>
                    <th>Created at</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $file): ?>
                    <tr>
                        <td>v<?= (int) $file->versionNumber ?></td>
                        <td><code><?= e($file->fileType) ?></code></td>
                        <td><?= e($file->createdAt) ?></td>
                        <td>
                            <a href="<?= url('/files/' . (int) $file->id . '/download') ?>" class="btn btn-ghost btn-sm">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
