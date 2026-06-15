<?php /** @var array{docker_host:int,project:int,service:int,web_app:int,generated_file:int} $counts */ ?>
<h1>Dashboard</h1>
<p class="muted">Overview of your declared infrastructure.</p>

<div class="card">
    <details <?= ($counts['project'] ?? 0) === 0 ? 'open' : '' ?>>
        <summary style="cursor:pointer;font-weight:700;font-size:1.1rem;">
            📚 What is Manifesto? (click to expand)
        </summary>
        <div style="margin-top:1rem;">
            <p>Manifesto organizes your Docker infrastructure in 4 levels:</p>
            <ol>
                <li><strong>🖥 Docker Host</strong> — the server where Docker runs (your laptop, AWS EC2, etc.)</li>
                <li><strong>📦 Project</strong> — one Docker Compose stack (= one <code>docker-compose.yml</code> file)</li>
                <li><strong>⚙ Service</strong> — one container in the project (e.g. <code>web</code>, <code>db</code>)</li>
                <li><strong>🌐 Web App</strong> — publicly reachable endpoint of a service</li>
            </ol>
            <p>Define your infrastructure here, then click <strong>Generate files</strong> to produce a valid <code>docker-compose.yml</code>, <code>.env</code> and Emmet text export.</p>
            <p style="margin-top:1rem;">
                <a href="<?= url('/docker-hosts/create') ?>" class="btn btn-primary btn-sm">Create your first Docker Host →</a>
                <a href="<?= url('/projects/import') ?>" class="btn btn-secondary btn-sm">⬆ Or import from JSON</a>
            </p>
        </div>
    </details>
</div>

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
