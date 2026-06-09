<div class="auth-card">
    <div class="auth-logo"><span class="brand-mark">M</span></div>
    <h1>Manifesto — Sign in</h1>
    <p class="auth-tagline">Declare your infrastructure. Generate your stack.</p>

    <form method="post" action="<?= url('/login') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= old('username') ?>" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign in</button>
    </form>
</div>
