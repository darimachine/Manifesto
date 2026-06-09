<?php
/**
 * Sidebar with the infrastructure tree.
 * @var array $sidebarTree  DockerHost → Project → Service → WebApp (from ViewRenderer)
 */
$tree = $sidebarTree ?? [];
?>
<aside class="sidebar">
    <a class="sidebar-brand" href="<?= url('/') ?>">
        <span class="brand-mark">M</span> Manifesto
    </a>

    <nav class="sidebar-nav">
        <a href="<?= url('/docker-hosts') ?>">Docker Hosts</a>
        <a href="<?= url('/projects') ?>">Projects</a>
    </nav>

    <div class="sidebar-tree">
        <div class="tree-title">Infrastructure</div>
        <?php if ($tree === []): ?>
            <p class="tree-empty">No hosts yet.</p>
        <?php endif; ?>
        <?php foreach ($tree as $host): ?>
            <details open>
                <summary>
                    <a class="tree-link tree-host" href="<?= url('/docker-hosts/' . $host['id']) ?>">🖥 <?= e($host['name']) ?></a>
                </summary>
                <?php foreach ($host['projects'] as $project): ?>
                    <details open class="tree-indent">
                        <summary>
                            <a class="tree-link tree-project" href="<?= url('/projects/' . $project['id']) ?>">📦 <?= e($project['name']) ?></a>
                        </summary>
                        <?php foreach ($project['services'] as $service): ?>
                            <details class="tree-indent">
                                <summary>
                                    <a class="tree-link tree-service" href="<?= url('/services/' . $service['id']) ?>">⚙ <?= e($service['name']) ?></a>
                                </summary>
                                <?php foreach ($service['webapps'] as $webapp): ?>
                                    <a class="tree-link tree-webapp tree-indent" href="<?= url('/webapps/' . $webapp['id']) ?>">🌐 <?= e($webapp['name']) ?></a>
                                <?php endforeach; ?>
                            </details>
                        <?php endforeach; ?>
                    </details>
                <?php endforeach; ?>
            </details>
        <?php endforeach; ?>
    </div>
</aside>
