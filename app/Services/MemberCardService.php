<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Config;
use App\Support\Database;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

final class MemberCardService
{
    public function issueForMember(int $memberId, bool $force = false): array
    {
        $pdo = Database::pdo();
        $member = $this->fetchMember($pdo, $memberId);
        if (!$member) {
            throw new \RuntimeException('Member record not found.');
        }

        $status = strtolower(trim((string) ($member['status'] ?? '')));
        if (!in_array($status, ['approved', 'active'], true)) {
            throw new \RuntimeException('Member card can only be issued after approval.');
        }

        $existingCard = $this->fetchExistingCard($pdo, $memberId);
        if ($existingCard && !$force && $this->filesExist($existingCard)) {
            return $this->decorateCard($member, $existingCard);
        }

        $membershipNumber = trim((string) ($member['membership_number'] ?? ''));
        if ($membershipNumber === '') {
            throw new \RuntimeException('Membership number is missing.');
        }

        $cardNumber = $membershipNumber;
        $slug = $this->safeFileName($membershipNumber);
        $qrRelative = 'uploads/member-cards/qr/' . $slug . '.svg';
        $pdfRelative = 'uploads/member-cards/pdf/' . $slug . '.pdf';
        $qrAbsolute = Config::basePath('public/' . $qrRelative);
        $pdfAbsolute = Config::basePath('public/' . $pdfRelative);

        $this->ensureDirectory(dirname($qrAbsolute));
        $this->ensureDirectory(dirname($pdfAbsolute));

        $qrPayload = json_encode([
            'membership_number' => $membershipNumber,
            'card_number' => $cardNumber,
            'name' => trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? '')),
            'email' => trim((string) ($member['email'] ?? '')),
            'status' => 'approved',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $svg = $this->buildQrSvg($qrPayload ?: $membershipNumber);
        file_put_contents($qrAbsolute, $svg);

        $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);
        $pdfHtml = $this->renderPdfHtml($member, $cardNumber, $qrDataUri);
        $pdfBytes = (new PdfService())->render($pdfHtml);
        file_put_contents($pdfAbsolute, $pdfBytes);

        $issuedAt = date('Y-m-d H:i:s');
        $cardId = 0;
        if ($existingCard) {
            $updateCard = $pdo->prepare(
                'UPDATE member_cards
                 SET card_number = :card_number,
                     qr_code_path = :qr_code_path,
                     pdf_path = :pdf_path,
                     issued_at = :issued_at,
                     expires_at = :expires_at,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $updateCard->execute([
                'card_number' => $cardNumber,
                'qr_code_path' => $qrRelative,
                'pdf_path' => $pdfRelative,
                'issued_at' => $issuedAt,
                'expires_at' => null,
                'id' => (int) $existingCard['id'],
            ]);
            $cardId = (int) $existingCard['id'];
        } else {
            $insertCard = $pdo->prepare(
                'INSERT INTO member_cards (member_id, card_number, qr_code_path, pdf_path, issued_at, expires_at)
                 VALUES (:member_id, :card_number, :qr_code_path, :pdf_path, :issued_at, :expires_at)'
            );
            $insertCard->execute([
                'member_id' => $memberId,
                'card_number' => $cardNumber,
                'qr_code_path' => $qrRelative,
                'pdf_path' => $pdfRelative,
                'issued_at' => $issuedAt,
                'expires_at' => null,
            ]);
            $cardId = (int) $pdo->lastInsertId();
        }

        $updateMember = $pdo->prepare('UPDATE members SET qr_code_path = :qr_code_path, updated_at = NOW() WHERE id = :id');
        $updateMember->execute([
            'qr_code_path' => $qrRelative,
            'id' => $memberId,
        ]);

        $notificationId = null;
        if (!empty($member['user_id'])) {
            $notificationId = $this->createNotification($pdo, (int) $member['user_id'], $member);
        }

        return [
            'id' => $cardId,
            'member_id' => $memberId,
            'card_number' => $cardNumber,
            'qr_code_path' => $qrRelative,
            'qr_url' => url($qrRelative),
            'pdf_path' => $pdfRelative,
            'pdf_url' => url($pdfRelative),
            'issued_at' => $issuedAt,
            'notification_id' => $notificationId,
        ];
    }

    private function fetchMember(\PDO $pdo, int $memberId): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT members.*, users.id AS user_id, users.full_name AS account_full_name, users.email AS account_email,
                    lgas.name AS lga_name, wards.name AS ward_name, polling_units.polling_name, polling_units.polling_code
             FROM members
             LEFT JOIN users ON users.email = members.email
             LEFT JOIN lgas ON lgas.id = members.lga_id
             LEFT JOIN wards ON wards.id = members.ward_id
             LEFT JOIN polling_units ON polling_units.id = members.polling_unit_id
             WHERE members.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $memberId]);
        $member = $stmt->fetch();

        return $member ?: null;
    }

    private function fetchExistingCard(\PDO $pdo, int $memberId): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT *
             FROM member_cards
             WHERE member_id = :member_id
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute(['member_id' => $memberId]);
        $card = $stmt->fetch();

        return $card ?: null;
    }

    private function filesExist(array $card): bool
    {
        $qrPath = trim((string) ($card['qr_code_path'] ?? ''));
        $pdfPath = trim((string) ($card['pdf_path'] ?? ''));

        return $qrPath !== '' && $pdfPath !== ''
            && is_file(Config::basePath('public/' . $qrPath))
            && is_file(Config::basePath('public/' . $pdfPath));
    }

    private function decorateCard(array $member, array $card): array
    {
        return [
            'id' => (int) ($card['id'] ?? 0),
            'member_id' => (int) ($card['member_id'] ?? 0),
            'card_number' => (string) ($card['card_number'] ?? ''),
            'qr_code_path' => (string) ($card['qr_code_path'] ?? ''),
            'qr_url' => url((string) ($card['qr_code_path'] ?? '')),
            'pdf_path' => (string) ($card['pdf_path'] ?? ''),
            'pdf_url' => url((string) ($card['pdf_path'] ?? '')),
            'issued_at' => (string) ($card['issued_at'] ?? ''),
            'member_name' => trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? '')),
            'lga_name' => (string) ($member['lga_name'] ?? ''),
            'ward_name' => (string) ($member['ward_name'] ?? ''),
            'polling_name' => (string) ($member['polling_name'] ?? ''),
        ];
    }

    private function buildQrSvg(string $payload): string
    {
        $builder = new Builder(
            writer: new SvgWriter(),
            writerOptions: [
                SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
            ],
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 320,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $result = $builder->build();

        return $result->getString();
    }

    private function renderPdfHtml(array $member, string $cardNumber, string $qrDataUri): string
    {
        $memberName = trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? ''));
        $membershipNumber = htmlspecialchars((string) ($member['membership_number'] ?? ''), ENT_QUOTES, 'UTF-8');
        $memberName = htmlspecialchars($memberName !== '' ? $memberName : 'Member', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim((string) ($member['email'] ?? '')), ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars(trim((string) ($member['phone'] ?? '')), ENT_QUOTES, 'UTF-8');
        $lga = htmlspecialchars(trim((string) ($member['lga_name'] ?? '-')), ENT_QUOTES, 'UTF-8');
        $ward = htmlspecialchars(trim((string) ($member['ward_name'] ?? '-')), ENT_QUOTES, 'UTF-8');
        $pollingUnit = htmlspecialchars(trim((string) ($member['polling_name'] ?? '-')), ENT_QUOTES, 'UTF-8');
        $role = htmlspecialchars('Registered Member', ENT_QUOTES, 'UTF-8');
        $issuedAt = htmlspecialchars(date('F j, Y g:i A'), ENT_QUOTES, 'UTF-8');
        $qr = htmlspecialchars($qrDataUri, ENT_QUOTES, 'UTF-8');
        $cardNumber = htmlspecialchars($cardNumber, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #163021;
            margin: 0;
            background: #f5f8f3;
        }
        .card {
            border: 2px solid #1f7a42;
            border-radius: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #eff8f1 100%);
            padding: 22px;
        }
        .brand {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(31, 122, 66, 0.18);
        }
        .brand h1 {
            font-size: 22px;
            margin: 0;
            line-height: 1.15;
            color: #12522c;
        }
        .brand .sub {
            font-size: 11px;
            color: #5a6d5f;
            margin-top: 4px;
        }
        .badge {
            text-align: right;
            font-size: 12px;
            color: #1f7a42;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .main {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .details, .qr {
            display: table-cell;
            vertical-align: top;
        }
        .details {
            width: 62%;
            padding-right: 18px;
        }
        .qr {
            width: 38%;
            text-align: center;
        }
        .name {
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 8px 0;
            color: #0f2d1b;
        }
        .grid {
            margin-top: 14px;
        }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .cell {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
            vertical-align: top;
        }
        .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6d7d71;
            margin-bottom: 4px;
        }
        .value {
            font-size: 14px;
            font-weight: 700;
            color: #163021;
        }
        .qr-box {
            display: inline-block;
            border: 1px solid rgba(31, 122, 66, 0.2);
            background: #fff;
            padding: 12px;
            border-radius: 16px;
        }
        .qr-box img {
            width: 210px;
            height: 210px;
            display: block;
        }
        .footer {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px solid rgba(31, 122, 66, 0.18);
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 11px;
            color: #5a6d5f;
        }
        .card-number {
            font-weight: 700;
            color: #12522c;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <div>
                <h1>Yayi Youth Vanguard Membership Card</h1>
                <div class="sub">Official membership and polling unit registration card</div>
            </div>
            <div class="badge">Approved Member</div>
        </div>

        <div class="main">
            <div class="details">
                <div class="name">{$memberName}</div>
                <div class="grid">
                    <div class="row">
                        <div class="cell">
                            <div class="label">Membership Number</div>
                            <div class="value">{$membershipNumber}</div>
                        </div>
                        <div class="cell">
                            <div class="label">Card Number</div>
                            <div class="value">{$cardNumber}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell">
                            <div class="label">Role</div>
                            <div class="value">{$role}</div>
                        </div>
                        <div class="cell">
                            <div class="label">Issued</div>
                            <div class="value">{$issuedAt}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell">
                            <div class="label">Email</div>
                            <div class="value">{$email}</div>
                        </div>
                        <div class="cell">
                            <div class="label">Phone</div>
                            <div class="value">{$phone}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell">
                            <div class="label">LGA</div>
                            <div class="value">{$lga}</div>
                        </div>
                        <div class="cell">
                            <div class="label">Ward</div>
                            <div class="value">{$ward}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell" style="width:100%;">
                            <div class="label">Polling Unit</div>
                            <div class="value">{$pollingUnit}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="qr">
                <div class="qr-box">
                    <img src="{$qr}" alt="QR code for membership card">
                </div>
            </div>
        </div>

        <div class="footer">
            <div>Scan the QR code to verify the member details.</div>
            <div class="card-number">Membership Card</div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function createNotification(\PDO $pdo, int $userId, array $member): int
    {
        $memberName = trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? ''));
        $title = 'Membership card issued';
        $message = sprintf(
            'Hello %s, your membership number is %s. Your membership card and QR code are ready for download.',
            $memberName !== '' ? $memberName : 'Member',
            (string) ($member['membership_number'] ?? '')
        );

        $stmt = $pdo->prepare(
            'INSERT INTO notifications (user_id, title, message, channel, status)
             VALUES (:user_id, :title, :message, :channel, :status)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'channel' => 'system',
            'status' => 'unread',
        ]);

        return (int) $pdo->lastInsertId();
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create card storage directory.');
        }
    }

    private function safeFileName(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?: '';
        $value = trim($value, '-_');

        return $value !== '' ? $value : 'card-' . time();
    }
}



