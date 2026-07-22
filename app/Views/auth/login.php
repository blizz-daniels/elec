<div class="auth-grid">
    <div class="auth-card">
        <h1 class="h3 mb-3">Login</h1>
        <form method="post" action="<?= url('/login'); ?>">
            <?= csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Sign In</button>
        </form>
    </div>
</div>
