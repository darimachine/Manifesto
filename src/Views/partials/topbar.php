<?php /** @var array|null $currentUser */ ?>
<header class="topbar">
    <div class="topbar-title"><?= e($title ?? '') ?></div>
    <div class="topbar-user">
        <?php if ($currentUser): ?>
            <span class="badge badge-<?= $currentUser['role'] === 'admin' ? 'admin' : 'viewer' ?>">
                <?= e($currentUser['role']) ?>
            </span>
            <span class="topbar-username"><?= e($currentUser['display_name']) ?></span>
            <form method="post" action="<?= url('/logout') ?>" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-ghost btn-sm">Logout</button>
            </form>
        <?php endif; ?>
    </div>
</header>
