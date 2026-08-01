<?php
$memberCard = $memberCard ?? [];
$cardTitle = $cardTitle ?? 'Membership Card';
$cardSubtitle = $cardSubtitle ?? 'Your QR code and downloadable membership card.';
$pdfUrl = trim((string) ($memberCard['pdf_url'] ?? ''));
$pdfDownloadUrl = trim((string) ($memberCard['pdf_download_url'] ?? $pdfUrl));
$qrUrl = trim((string) ($memberCard['qr_url'] ?? ''));
$qrDownloadUrl = trim((string) ($memberCard['qr_download_url'] ?? $qrUrl));
$membershipNumber = trim((string) ($memberCard['membership_number'] ?? ($memberCard['card_number'] ?? '')));
$issuedAtLabel = trim((string) ($memberCard['issued_at_label'] ?? ''));
$memberName = trim((string) ($memberCard['member_name'] ?? ''));
?>

<div class="card p-4 shadow-sm border-success-subtle mt-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <div class="text-uppercase small text-muted fw-semibold mb-1">Card</div>
            <h2 class="h5 mb-1"><?= htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="text-muted mb-0"><?= htmlspecialchars($cardSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <?php if ($membershipNumber !== ''): ?>
            <span class="badge text-bg-success-subtle text-success border border-success-subtle"><?= htmlspecialchars($membershipNumber, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
    </div>

    <?php if ($membershipNumber === '' && $qrUrl === '' && $pdfUrl === ''): ?>
        <div class="alert alert-light border mb-0">
            Your membership card will appear here after approval.
        </div>
    <?php else: ?>
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <?php if ($memberName !== '' || $membershipNumber !== ''): ?>
                    <div class="border rounded-3 p-3 mb-3 bg-light">
                        <div class="small text-muted mb-1">Member</div>
                        <div class="fw-semibold"><?= htmlspecialchars($memberName !== '' ? $memberName : 'Member', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="small text-muted mt-2">Membership Number</div>
                        <div class="fw-semibold"><?= htmlspecialchars($membershipNumber !== '' ? $membershipNumber : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small text-muted mb-1">Card Number</div>
                            <div class="fw-semibold"><?= htmlspecialchars($membershipNumber !== '' ? $membershipNumber : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small text-muted mb-1">Issued</div>
                            <div class="fw-semibold"><?= htmlspecialchars($issuedAtLabel !== '' ? $issuedAtLabel : 'Generated when you download the card', ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <?php if ($pdfDownloadUrl !== ''): ?>
                        <a class="btn btn-success" href="<?= htmlspecialchars($pdfDownloadUrl, ENT_QUOTES, 'UTF-8'); ?>">Download PDF</a>
                    <?php endif; ?>
                    <?php if ($qrDownloadUrl !== ''): ?>
                        <a class="btn btn-outline-success" href="<?= htmlspecialchars($qrDownloadUrl, ENT_QUOTES, 'UTF-8'); ?>">Download QR</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <?php if ($qrUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Membership QR code" class="img-fluid border rounded-4 bg-white p-3" style="max-width: 220px;">
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
