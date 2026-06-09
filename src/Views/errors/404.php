<div class="error-page">
    <div class="error-code">404</div>
    <h1>Page not found</h1>
    <p class="muted"><?= e($message ?? 'The page you are looking for does not exist.') ?></p>
    <a class="btn btn-primary" href="<?= url('/') ?>">Back to dashboard</a>
</div>
