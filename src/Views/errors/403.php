<div class="error-page">
    <div class="error-code">403</div>
    <h1>Forbidden</h1>
    <p class="muted"><?= e($message ?: 'You do not have permission to perform this action.') ?></p>
    <a class="btn btn-primary" href="<?= url('/') ?>">Back to dashboard</a>
</div>
