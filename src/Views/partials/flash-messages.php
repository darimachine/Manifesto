<?php /** @var array $flashes */ ?>
<?php foreach (($flashes ?? []) as $flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
