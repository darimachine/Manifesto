<?php /** @var string $title */ ?>
<div class="page-head">
    <h1>Import Project from JSON</h1>
    <a class="btn btn-secondary" href="<?= url('/projects') ?>">Back to Projects</a>
</div>

<div class="card">
    <h2 class="card-title">Upload Manifesto JSON file</h2>
    <p class="muted">
        Upload a previously exported <code>.manifesto.json</code> file.
        The project will be imported with all its services, ports, environment
        variables, volumes and web apps. If a project with the same slug exists,
        a unique slug will be generated automatically.
    </p>

    <form method="post" action="<?= url('/projects/import') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="json_file">JSON file</label>
            <input type="file" id="json_file" name="json_file" accept=".json,application/json" required>
        </div>
        <div class="form-footer">
            <button type="submit" class="btn btn-primary">Import project</button>
            <a class="btn btn-secondary" href="<?= url('/projects') ?>">Cancel</a>
        </div>
    </form>
</div>

<div class="card">
    <h2 class="card-title">Expected format</h2>
    <pre class="code-preview">{
  "format_version": "1.0",
  "exported_at": "2026-06-14T12:00:00+00:00",
  "project": {
    "name": "...",
    "slug": "...",
    "description": "...",
    "docker_host": { "name": "..." },
    "services": [ ... ]
  }
}</pre>
</div>
