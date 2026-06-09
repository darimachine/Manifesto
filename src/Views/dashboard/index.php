<?php /** @var array{docker_host:int,project:int,service:int,web_app:int,generated_file:int} $counts */ ?>
<h1>Dashboard</h1>
<p class="muted">Overview of your declared infrastructure.</p>

<div class="stat-grid">
    <a class="stat-card" href="<?= url('/docker-hosts') ?>">
        <div class="stat-number"><?= $counts['docker_host'] ?></div>
        <div class="stat-label">Docker Hosts</div>
    </a>
    <a class="stat-card" href="<?= url('/projects') ?>">
        <div class="stat-number"><?= $counts['project'] ?></div>
        <div class="stat-label">Projects</div>
    </a>
    <div class="stat-card">
        <div class="stat-number"><?= $counts['service'] ?></div>
        <div class="stat-label">Services</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $counts['web_app'] ?></div>
        <div class="stat-label">Web Apps</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $counts['generated_file'] ?></div>
        <div class="stat-label">Generated Files</div>
    </div>
</div>

<?php if ($counts['docker_host'] === 0): ?>
    <div class="empty-state">
        <p>Start by declaring your first Docker host.</p>
        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
            <a class="btn btn-primary" href="<?= url('/docker-hosts/create') ?>">+ New Docker Host</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
