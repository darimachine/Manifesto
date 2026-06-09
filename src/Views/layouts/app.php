<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Manifesto') ?> — Manifesto</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/../partials/topbar.php'; ?>
        <main class="app-content">
            <?php require __DIR__ . '/../partials/flash-messages.php'; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
