<?php

declare(strict_types=1);

use App\Support\Auth;
use App\Support\Config;

$title = $title ?? app('APP_NAME', 'Yayi Youth Vanguard');
$currentUser = Auth::user();
$currentRole = Auth::role();
$isAuthView = str_contains($template, 'auth/');
$dashboardHref = in_array($currentRole, ['registered-member', 'member'], true) ? url('/member/dashboard') : url('/dashboard');
$dashboardLabel = in_array($currentRole, ['registered-member', 'member'], true) ? 'Member Dashboard' : 'Dashboard';
$sidebarItems = [
    ['label' => 'Dashboard', 'href' => $dashboardHref, 'roles' => null],
    ['label' => 'Members', 'href' => url('/admin/members'), 'roles' => ['super-admin', 'state-executive', 'lga-executive', 'ward-executive']],
    ['label' => 'News', 'href' => url('/admin/news'), 'roles' => ['super-admin']],
    ['label' => 'Executives', 'href' => url('/admin/executives'), 'roles' => ['super-admin', 'state-executive']],
    ['label' => 'Geography', 'href' => url('/admin/geography'), 'roles' => ['super-admin', 'state-executive']],
    ['label' => 'Polling Units', 'href' => url('/admin/polling-units'), 'roles' => ['super-admin', 'state-executive', 'lga-executive']],
    ['label' => 'Marshals', 'href' => url('/admin/marshals'), 'roles' => ['super-admin', 'state-executive', 'lga-executive']],
    ['label' => 'Candidates', 'href' => url('/admin/candidates'), 'roles' => ['super-admin', 'state-executive']],
    ['label' => 'Elections', 'href' => url('/elections'), 'roles' => ['super-admin', 'state-executive']],
    ['label' => 'Results', 'href' => url('/results'), 'roles' => ['super-admin', 'state-executive', 'lga-executive', 'polling-marshal']],
    ['label' => 'Reports', 'href' => url('/reports'), 'roles' => ['super-admin', 'state-executive', 'lga-executive', 'ward-executive']],
    ['label' => 'Notifications', 'href' => url('/notifications'), 'roles' => null],
    ['label' => 'Audit Logs', 'href' => url('/admin/audit-logs'), 'roles' => ['super-admin']],
    ['label' => 'Profile', 'href' => url('/profile'), 'roles' => null],
    ['label' => 'Settings', 'href' => url('/settings'), 'roles' => ['super-admin']],
];
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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/app.css'); ?>" rel="stylesheet">
    <link href="<?= url('assets/css/news.css'); ?>" rel="stylesheet">
    <link href="<?= url('assets/css/youth-vanguard.css'); ?>" rel="stylesheet">
</head>
<body class="app-shell app-theme yv-theme">
<a class="skip-link" href="#main-content">Skip to main content</a>
<?php if (!$isAuthView): ?>
    <header class="site-header">
        <div class="container-fluid site-header__inner">
            <a class="brand-mark" href="<?= url('/'); ?>">
                <span class="brand-mark__icon brand-mark__icon--logos" aria-hidden="true">
                    <img
                        class="brand-mark__logo-image"
                        src="<?= htmlspecialchars(url('assets/' . rawurlencode('WhatsApp Image 2026-07-23 at 1.17.59 PM.jpeg')), ENT_QUOTES, 'UTF-8'); ?>"
                        alt=""
                    >
                    <img
                        class="brand-mark__logo-image brand-mark__logo-image--secondary"
                        src="<?= htmlspecialchars(url('assets/' . rawurlencode('WhatsApp Image 2026-07-23 at 1.20.54 PM.jpeg')), ENT_QUOTES, 'UTF-8'); ?>"
                        alt=""
                    >
                </span>
                <span class="brand-mark__text">
                    <strong><?= htmlspecialchars((string) app('APP_NAME', 'Yayi Youth Vanguard'), ENT_QUOTES, 'UTF-8'); ?></strong>
                    <small>Membership and executives registration portal</small>
                </span>
            </a>
            <button
                class="site-menu-toggle btn btn--ghost d-lg-none"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mobileSiteMenu"
                aria-controls="mobileSiteMenu"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <i class="fa-solid fa-bars"></i>
            </button>
            <nav class="site-nav d-none d-lg-flex">
                <?php if (!$currentUser): ?>
                    <a class="site-nav__link" href="<?= url('/#about-platform'); ?>">About</a>
                <?php endif; ?>
                <?php if ($currentUser): ?>
                    <?php if (in_array($currentRole, ['super-admin', 'state-executive'], true)): ?>
                        <a class="site-nav__link" href="<?= url('/elections'); ?>">Elections</a>
                    <?php endif; ?>
                    <?php if (in_array($currentRole, ['super-admin', 'state-executive', 'lga-executive', 'polling-marshal'], true)): ?>
                        <a class="site-nav__link" href="<?= url('/results'); ?>">Results</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
            <div class="site-actions d-none d-lg-flex">
                <?php if ($currentUser): ?>
                    <a class="btn btn--ghost" href="<?= $dashboardHref; ?>"><?= htmlspecialchars($dashboardLabel, ENT_QUOTES, 'UTF-8'); ?></a>
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
        <div class="collapse d-lg-none site-mobile-menu" id="mobileSiteMenu">
            <div class="site-mobile-menu__inner">
                <nav class="site-mobile-menu__nav">
                    <?php if (!$currentUser): ?>
                        <a href="<?= url('/#about-platform'); ?>">About</a>
                    <?php endif; ?>
                    <?php if ($currentUser): ?>
                        <?php if (in_array($currentRole, ['super-admin', 'state-executive'], true)): ?>
                            <a href="<?= url('/elections'); ?>">Elections</a>
                        <?php endif; ?>
                        <?php if (in_array($currentRole, ['super-admin', 'state-executive', 'lga-executive', 'polling-marshal'], true)): ?>
                            <a href="<?= url('/results'); ?>">Results</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </nav>
                <?php if ($currentUser): ?>
                    <div class="site-mobile-menu__section">
                        <div class="site-mobile-menu__section-title">Operations Menu</div>
                        <div class="site-mobile-menu__operation-links">
                            <?php foreach ($sidebarItems as $item): ?>
                                <?php if ($item['roles'] === null || in_array($currentRole, $item['roles'], true)): ?>
                                    <a href="<?= $item['href']; ?>"><?= htmlspecialchars((string) $item['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="site-mobile-menu__actions">
                    <?php if ($currentUser): ?>
                        <a class="btn btn--ghost" href="<?= $dashboardHref; ?>">Dashboard</a>
                        <form method="post" action="<?= url('/logout'); ?>" class="d-grid">
                            <?= csrf_field(); ?>
                            <button class="btn btn--accent" type="submit">Logout</button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn--ghost" href="<?= url('/login'); ?>">Login</a>
                        <a class="btn btn--accent" href="<?= url('/register'); ?>">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>

<main class="app-main" id="main-content">
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
                    <div class="dashboard-sidebar__title">Operations Menu</div>
                </div>
                <div class="dashboard-sidebar__links">
                    <?php foreach ($sidebarItems as $item): ?>
                        <?php if ($item['roles'] === null || in_array($currentRole, $item['roles'], true)): ?>
                            <a href="<?= $item['href']; ?>"><?= htmlspecialchars((string) $item['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
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

