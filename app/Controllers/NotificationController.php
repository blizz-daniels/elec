<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Config;
use App\Support\Database;
use App\Support\Request;

final class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requiresLogin();

        $pdo = Database::pdo();
        $currentUser = Auth::user() ?? [];
        $email = trim((string) ($currentUser['email'] ?? ''));
        $userId = (int) ($currentUser['id'] ?? 0);

        $notificationsStmt = $pdo->prepare(
            'SELECT id, title, message, channel, status, created_at
             FROM notifications
             WHERE user_id = :user_id OR user_id IS NULL
             ORDER BY id DESC
             LIMIT 25'
        );
        $notificationsStmt->execute(['user_id' => $userId]);
        $notifications = $notificationsStmt->fetchAll();

        $member = [];
        if ($email !== '') {
            $memberStmt = $pdo->prepare(
                'SELECT members.*, lgas.name AS lga_name, wards.name AS ward_name, polling_units.polling_name, polling_units.polling_code
                 FROM members
                 LEFT JOIN lgas ON lgas.id = members.lga_id
                 LEFT JOIN wards ON wards.id = members.ward_id
                 LEFT JOIN polling_units ON polling_units.id = members.polling_unit_id
                 WHERE members.email = :email
                 LIMIT 1'
            );
            $memberStmt->execute(['email' => $email]);
            $member = $memberStmt->fetch() ?: [];
        }

        $memberCard = [];
        $memberId = (int) ($member['id'] ?? 0);
        $membershipNumber = trim((string) ($member['membership_number'] ?? ''));
        $memberName = trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? ''));

        if ($memberId > 0) {
            $cardStmt = $pdo->prepare(
                'SELECT *
                 FROM member_cards
                 WHERE member_id = :member_id
                 ORDER BY id DESC
                 LIMIT 1'
            );
            $cardStmt->execute(['member_id' => $memberId]);
            $card = $cardStmt->fetch();

            if ($card) {
                $issuedAt = !empty($card['issued_at']) ? date('F j, Y g:i A', strtotime((string) $card['issued_at'])) : '';
                $qrPath = trim((string) ($card['qr_code_path'] ?? ''));
                $pdfPath = trim((string) ($card['pdf_path'] ?? ''));
                $qrFile = $qrPath !== '' ? Config::basePath('public/' . $qrPath) : '';
                $pdfFile = $pdfPath !== '' ? Config::basePath('public/' . $pdfPath) : '';

                $memberCard = [
                    'id' => (int) ($card['id'] ?? 0),
                    'membership_number' => $membershipNumber,
                    'card_number' => (string) ($card['card_number'] ?? $membershipNumber),
                    'qr_code_path' => $qrPath,
                    'qr_url' => $qrFile !== '' && is_file($qrFile) ? url($qrPath) : url('/member/card/qr'),
                    'qr_download_url' => url('/member/card/qr/download'),
                    'pdf_path' => $pdfPath,
                    'pdf_url' => $pdfFile !== '' && is_file($pdfFile) ? url($pdfPath) : url('/member/card/pdf'),
                    'pdf_download_url' => url('/member/card/pdf'),
                    'issued_at' => (string) ($card['issued_at'] ?? ''),
                    'issued_at_label' => $issuedAt,
                    'member_name' => $memberName,
                ];
            }
        }

        if ($memberCard === [] && $membershipNumber !== '') {
            $memberCard = [
                'id' => 0,
                'membership_number' => $membershipNumber,
                'card_number' => $membershipNumber,
                'qr_code_path' => '',
                'qr_url' => url('/member/card/qr'),
                'qr_download_url' => url('/member/card/qr/download'),
                'pdf_path' => '',
                'pdf_url' => url('/member/card/pdf'),
                'pdf_download_url' => url('/member/card/pdf'),
                'issued_at' => '',
                'issued_at_label' => 'Generated when you download the card',
                'member_name' => $memberName,
            ];
        }

        $this->view('notifications/index', [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'memberCard' => $memberCard,
        ]);
    }
}
