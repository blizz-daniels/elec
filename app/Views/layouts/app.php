<?php

declare(strict_types=1);

use App\Support\Auth;
use App\Support\Config;

$title = $title ?? app('APP_NAME', 'Ogun Political System');
$currentUser = Auth::user();
$isAuthView = str_contains($template, 'auth/');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/app.css'); ?>" rel="stylesheet">
</head>
<body class="app-shell app-theme">
<?php if (!$isAuthView): ?>
    <header class="site-header">
        <div class="container-fluid site-header__inner">
            <a class="brand-mark" href="<?= url('/'); ?>">
                <span class="brand-mark__icon">OG</span>
                <span class="brand-mark__text">
                    <strong><?= htmlspecialchars((string) app('APP_NAME', 'Ogun Political System'), ENT_QUOTES, 'UTF-8'); ?></strong>
                    <small>Membership and election monitoring</small>
                </span>
            </a>
            <nav class="site-nav d-none d-lg-flex">
                <a class="site-nav__link" href="<?= url('/#about-platform'); ?>">About</a>
                <?php if ($currentUser): ?>
                    <a class="site-nav__link" href="<?= url('/member/dashboard'); ?>">Member Area</a>
                    <a class="site-nav__link" href="<?= url('/elections'); ?>">Elections</a>
                    <a class="site-nav__link" href="<?= url('/results'); ?>">Results</a>
                <?php endif; ?>
            </nav>
            <div class="site-actions">
                <?php if ($currentUser): ?>
                    <a class="btn btn--ghost" href="<?= url('/dashboard'); ?>">Dashboard</a>
                    <form method="post" action="<?= url('/logout'); ?>" class="d-inline">
                        <?= csrf_field(); ?>
                        <button class="btn btn--accent" type="submit">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn--ghost" href="<?= url('/login'); ?>">Login</a>
                    <a class="btn btn--accent" href="<?= url('/register'); ?>">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
<?php endif; ?>

<main class="app-main">
    <?php if ($message = flash('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($message = flash('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!$isAuthView && $currentUser): ?>
        <div class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="dashboard-sidebar__head">
                    <div class="dashboard-sidebar__eyebrow">Portal</div>
                    <div class="dashboard-sidebar__title">Management Menu</div>
                </div>
                <div class="dashboard-sidebar__links">
                    <a href="<?= url('/dashboard'); ?>">Dashboard</a>
                    <a href="<?= url('/admin/members'); ?>">Members</a>
                    <a href="<?= url('/admin/executives'); ?>">Executives</a>
                    <a href="<?= url('/admin/geography'); ?>">Geography</a>
                    <a href="<?= url('/admin/polling-units'); ?>">Polling Units</a>
                    <a href="<?= url('/admin/marshals'); ?>">Marshals</a>
                    <a href="<?= url('/admin/candidates'); ?>">Candidates</a>
                    <a href="<?= url('/elections'); ?>">Elections</a>
                    <a href="<?= url('/results'); ?>">Results</a>
                    <a href="<?= url('/reports'); ?>">Reports</a>
                    <a href="<?= url('/notifications'); ?>">Notifications</a>
                    <a href="<?= url('/admin/audit-logs'); ?>">Audit Logs</a>
                    <a href="<?= url('/profile'); ?>">Profile</a>
                    <a href="<?= url('/settings'); ?>">Settings</a>
                </div>
            </aside>
            <section class="dashboard-content">
                <?php require $viewFile; ?>
            </section>
        </div>
    <?php else: ?>
        <?php require $viewFile; ?>
    <?php endif; ?>
</main>
<?php if (!$isAuthView && !$currentUser): ?>
    <?php require Config::basePath('app/Views/partials/public-footer.php'); ?>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= url('assets/js/app.js'); ?>"></script>
</body>
</html>
