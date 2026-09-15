<section class="auth-hero">
    <div class="auth-hero__panel">
        <div class="hero-badge mb-3">Secure portal access</div>
        <h1 class="hero-title auth-hero__title">Welcome back to Yayi Youth Vanguard.</h1>
        <p class="hero-text auth-hero__text">
            Sign in to manage members, monitor elections, approve submissions, and continue your dashboard workflow.
        </p>
    </div>
    <div class="auth-card auth-card--theme">
        <div class="auth-card__head">
            <span class="info-label">Login</span>
            <h2>Access your account</h2>
            <p>Sign in securely to continue with your registration, membership, or operations workspace.</p>
        </div>
        <form method="post" action="<?= url('/login'); ?>" class="auth-form">
            <?= csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="login-email">Email address</label>
                <input class="form-control form-control-lg" id="login-email" type="email" name="email" autocomplete="email" placeholder="Enter email address" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="login-password">Password</label>
                <input class="form-control form-control-lg" id="login-password" type="password" name="password" autocomplete="current-password" placeholder="Enter password" required>
            </div>
            <button class="btn btn--accent w-100" type="submit">Sign In</button>
        </form>
        <div class="auth-card__footer">
            <a href="<?= url('/register'); ?>">Need a membership account? Register here.</a>
        </div>
    </div>
</section>
