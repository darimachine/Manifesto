<?php
/**
 * @var array<string,mixed>              $project
 * @var \Manifesto\Models\GeneratedFile|null $emmet
 * @var string                           $title
 */
$projectId = (int) $project['id'];
?>
<div class="page-head">
    <div>
        <h1>Emmet export</h1>
        <p class="muted">Project: <a href="<?= url('/projects/' . $projectId) ?>"><?= e($project['name']) ?></a></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-secondary" href="<?= url('/projects/' . $projectId . '/files') ?>">Back to files</a>
        <?php if ($emmet !== null): ?>
            <a href="<?= url('/files/' . (int) $emmet->id . '/download') ?>" class="btn btn-secondary">Download</a>
            <button type="button" class="btn btn-secondary" id="copy-emmet">Copy to clipboard</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($emmet !== null): ?>
    <div class="card">
        <div class="page-head">
            <h2 class="card-title">Emmet notation <span class="muted">(v<?= (int) $emmet->versionNumber ?>)</span></h2>
        </div>
        <pre class="code-preview"><?= e($emmet->content) ?></pre>
    </div>
<?php else: ?>
    <div class="card">
        <div class="empty-state">
            <p>No Emmet export generated yet.</p>
            <p><a class="btn btn-primary" href="<?= url('/projects/' . $projectId . '/files') ?>">Go to files page to generate</a></p>
        </div>
    </div>
<?php endif; ?>

<?php if ($emmet !== null): ?>
<script>
document.getElementById('copy-emmet')?.addEventListener('click', async function () {
    const text = document.querySelector('.code-preview')?.innerText ?? '';
    try {
        await navigator.clipboard.writeText(text);
        alert('Copied!');
    } catch (e) {
        alert('Copy failed — select the text manually.');
    }
});
</script>
<?php endif; ?>
