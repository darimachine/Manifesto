<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Manifesto') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="auth-body">
    <main class="auth-card-wrap">
        <?php require __DIR__ . '/../partials/flash-messages.php'; ?>
        <?= $content ?>
    </main>
</body>
</html>
