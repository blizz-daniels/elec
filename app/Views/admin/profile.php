<?php
$user = $user ?? [];
$displayName = trim((string) ($user['full_name'] ?? ''));
$roleName = trim((string) ($user['role_name'] ?? $user['role'] ?? $user['role_slug'] ?? 'Member'));
$email = trim((string) ($user['email'] ?? ''));
$phone = trim((string) ($user['phone'] ?? ''));
$status = trim((string) ($user['status'] ?? ''));
$lastLogin = !empty($user['last_login_at']) ? date('F j, Y g:i A', strtotime((string) $user['last_login_at'])) : 'Never';
$avatar = strtoupper(substr($displayName !== '' ? $displayName : 'U', 0, 1));
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Profile</h1>
        <p class="text-muted mb-0">A simple account card for your signed-in user.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="card p-4 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success-subtle text-success fw-bold" style="width:72px;height:72px;font-size:1.5rem;">
                    <?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div>
                    <div class="text-uppercase small text-muted fw-semibold mb-1">Account</div>
                    <h2 class="h4 mb-1"><?= htmlspecialchars($displayName !== '' ? $displayName : 'User', ENT_QUOTES, 'UTF-8'); ?></h2>
                    <div class="text-muted"><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-1">Email</div>
                        <div class="fw-semibold"><?= htmlspecialchars($email !== '' ? $email : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-1">Phone</div>
                        <div class="fw-semibold"><?= htmlspecialchars($phone !== '' ? $phone : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-1">Status</div>
                        <div class="fw-semibold text-capitalize"><?= htmlspecialchars($status !== '' ? $status : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-1">Last Login</div>
                        <div class="fw-semibold"><?= htmlspecialchars($lastLogin, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div class="text-muted small">
                    Signed in as <?= htmlspecialchars($displayName !== '' ? $displayName : 'user', ENT_QUOTES, 'UTF-8'); ?>.
                </div>
                <a class="btn btn-outline-primary" href="<?= url('/dashboard'); ?>">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>
