<?php
$profile = $profile ?? [];
$displayName = trim((string) ($profile['full_name'] ?? $profile['user_name'] ?? ''));
$roleName = trim((string) ($profile['role_name'] ?? $profile['role'] ?? $profile['role_slug'] ?? 'Member'));
$email = trim((string) ($profile['user_email'] ?? $profile['email'] ?? ''));
$phone = trim((string) ($profile['user_phone'] ?? $profile['phone'] ?? ''));
$status = trim((string) ($profile['user_status'] ?? $profile['status'] ?? ''));
$membershipNumber = trim((string) ($profile['membership_number'] ?? ''));
$lgaName = trim((string) ($profile['lga_name'] ?? ''));
$wardName = trim((string) ($profile['ward_name'] ?? ''));
$pollingUnitName = trim((string) ($profile['polling_name'] ?? ''));
$pollingUnitCode = trim((string) ($profile['polling_code'] ?? ''));
$lastLogin = !empty($profile['last_login_at']) ? date('F j, Y g:i A', strtotime((string) $profile['last_login_at'])) : 'Never';
$photoUrl = trim((string) ($profile['passport_path'] ?? $profile['avatar_path'] ?? ''));
$avatar = strtoupper(substr($displayName !== '' ? $displayName : 'M', 0, 1));
$memberCard = $memberCard ?? [];
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Member Dashboard</h1>
        <p class="text-muted mb-0">Your account summary at a glance.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <div class="card p-4 shadow-sm member-profile-card">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center bg-success-subtle text-success fw-bold member-profile-card__avatar">
                    <?php if ($photoUrl !== ''): ?>
                        <img src="<?= htmlspecialchars(url($photoUrl), ENT_QUOTES, 'UTF-8'); ?>" alt="Profile photo" class="w-100 h-100 member-profile-card__photo">
                    <?php else: ?>
                        <?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="text-uppercase small text-muted fw-semibold mb-1">Account</div>
                    <h2 class="h4 mb-1"><?= htmlspecialchars($displayName !== '' ? $displayName : 'Member', ENT_QUOTES, 'UTF-8'); ?></h2>
                    <div class="text-muted"><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100 member-profile-card__detail">
                        <div class="small text-muted mb-1">Membership Number</div>
                        <div class="fw-semibold"><?= htmlspecialchars($membershipNumber !== '' ? $membershipNumber : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100 member-profile-card__detail">
                        <div class="small text-muted mb-1">Status</div>
                        <div class="fw-semibold text-capitalize"><?= htmlspecialchars($status !== '' ? $status : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100 member-profile-card__detail">
                        <div class="small text-muted mb-1">Email</div>
                        <div class="fw-semibold"><?= htmlspecialchars($email !== '' ? $email : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100 member-profile-card__detail">
                        <div class="small text-muted mb-1">Phone</div>
                        <div class="fw-semibold"><?= htmlspecialchars($phone !== '' ? $phone : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100 member-profile-card__detail">
                        <div class="small text-muted mb-1">LGA</div>
                        <div class="fw-semibold"><?= htmlspecialchars($lgaName !== '' ? $lgaName : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100 member-profile-card__detail">
                        <div class="small text-muted mb-1">Ward</div>
                        <div class="fw-semibold"><?= htmlspecialchars($wardName !== '' ? $wardName : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="border rounded-3 p-3 h-100 member-profile-card__detail">
                        <div class="small text-muted mb-1">Polling Unit</div>
                        <div class="fw-semibold"><?= htmlspecialchars($pollingUnitName !== '' ? $pollingUnitName : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($pollingUnitCode !== '' ? $pollingUnitCode : '', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="border rounded-3 p-3 h-100 member-profile-card__detail">
                        <div class="small text-muted mb-1">Last Login</div>
                        <div class="fw-semibold"><?= htmlspecialchars($lastLogin, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            </div>

            <?php require App\Support\Config::basePath('app/Views/partials/member-card.php'); ?>

            <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div class="text-muted small">Welcome back, <?= htmlspecialchars($displayName !== '' ? $displayName : 'member', ENT_QUOTES, 'UTF-8'); ?>.</div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-primary" href="<?= url('/profile'); ?>">Profile</a>
                    <form method="post" action="<?= url('/logout'); ?>" class="d-inline">
                        <?= csrf_field(); ?>
                        <button class="btn btn-primary" type="submit">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
