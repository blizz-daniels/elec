<?php
$notifications = $notifications ?? [];
$memberCard = $memberCard ?? [];
$memberName = trim((string) ($memberCard['member_name'] ?? ''));
$membershipNumber = trim((string) ($memberCard['membership_number'] ?? ($memberCard['card_number'] ?? '')));
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Notifications</h1>
        <p class="text-muted mb-0">Your approval updates, membership card notices, and system messages.</p>
    </div>
</div>

<?php if ($membershipNumber !== ''): ?>
    <div class="alert alert-success border-success-subtle">
        <div class="fw-semibold mb-1">
            Hello <?= htmlspecialchars($memberName !== '' ? $memberName : 'Member', ENT_QUOTES, 'UTF-8'); ?>,
            your membership number is <?= htmlspecialchars($membershipNumber, ENT_QUOTES, 'UTF-8'); ?>.
        </div>
        <div class="small mb-0">
            Your membership card and QR code are ready below. If they have not been generated yet, they will be created when you download them.
        </div>
    </div>
<?php endif; ?>

<?php require App\Support\Config::basePath('app/Views/partials/member-card.php'); ?>

<div class="card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Recent Notifications</h2>
            <p class="text-muted mb-0">Latest in-app messages sent to your account.</p>
        </div>
    </div>

    <div class="list-group list-group-flush">
        <?php foreach ($notifications as $notification): ?>
            <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars((string) ($notification['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-muted small mb-1"><?= htmlspecialchars((string) ($notification['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-uppercase small text-muted"><?= htmlspecialchars((string) ($notification['channel'] ?? 'system'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="text-muted small text-nowrap"><?= !empty($notification['created_at']) ? htmlspecialchars(date('F j, Y g:i A', strtotime((string) $notification['created_at'])), ENT_QUOTES, 'UTF-8') : ''; ?></div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($notifications === []): ?>
            <div class="text-muted">No notifications yet.</div>
        <?php endif; ?>
    </div>
</div>
