<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\MemberCardService;
use App\Support\Auth;
use App\Support\Config;
use App\Support\Database;
use App\Support\Request;

final class MemberController extends Controller
{
    public function dashboard(Request $request): void
    {
        Auth::requiresLogin();

        if (!in_array(Auth::role(), ['registered-member', 'polling-marshal'], true)) {
            redirect('/dashboard');
        }

        $pdo = Database::pdo();
        $currentUser = Auth::user() ?? [];
        $email = trim((string) ($currentUser['email'] ?? ''));
        $member = $this->memberByEmail($pdo, $email);

        $profileStmt = $pdo->prepare(
            'SELECT members.*, lgas.name AS lga_name, wards.name AS ward_name, polling_units.polling_name, polling_units.polling_code,
                    users.full_name AS user_name, users.email AS user_email, users.phone AS user_phone, users.status AS user_status,
                    roles.name AS role_name, roles.slug AS role_slug, users.last_login_at
             FROM users
             LEFT JOIN roles ON roles.id = users.role_id
             LEFT JOIN members ON members.email = users.email
             LEFT JOIN lgas ON lgas.id = members.lga_id
             LEFT JOIN wards ON wards.id = members.ward_id
             LEFT JOIN polling_units ON polling_units.id = members.polling_unit_id
             WHERE users.email = :email
             LIMIT 1'
        );
        $profileStmt->execute(['email' => $email]);
        $profile = $profileStmt->fetch() ?: $currentUser;

        $memberCard = $this->memberCardData($pdo, $member);

        $this->view('member/dashboard', [
            'title' => 'Member Dashboard',
            'profile' => $profile,
            'memberCard' => $memberCard,
        ]);
    }

    public function downloadCardPdf(Request $request): void
    {
        $this->outputCardAsset(true);
    }

    public function showCardQr(Request $request): void
    {
        $this->outputCardAsset(false);
    }

    public function downloadCardQr(Request $request): void
    {
        $this->outputCardAsset(true);
    }

    private function outputCardAsset(bool $asAttachment): void
    {
        Auth::requiresLogin();

        if (!in_array(Auth::role(), ['registered-member', 'polling-marshal'], true)) {
            redirect('/dashboard');
        }

        $pdo = Database::pdo();
        $currentUser = Auth::user() ?? [];
        $email = trim((string) ($currentUser['email'] ?? ''));
        $member = $this->memberByEmail($pdo, $email);
        if ($member === []) {
            flash('error', 'Member record not found.');
            redirect('/member/dashboard');
        }

        $memberId = (int) ($member['id'] ?? 0);
        if ($memberId <= 0) {
            flash('error', 'Member record not found.');
            redirect('/member/dashboard');
        }

        try {
            $card = (new MemberCardService())->issueForMember($memberId, true);
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/member/dashboard');
        }

        $assetType = $asAttachment ? 'pdf' : 'qr';
        $field = $assetType === 'pdf' ? 'pdf_path' : 'qr_code_path';
        $mime = $assetType === 'pdf' ? 'application/pdf' : 'image/svg+xml';
        $cardNumber = (string) ($card['card_number'] ?? $member['membership_number'] ?? 'card');
        $cardNumber = preg_replace('/[^A-Za-z0-9_-]+/', '-', $cardNumber) ?: 'card';
        $fileName = $assetType === 'pdf'
            ? 'member-card-' . $cardNumber . '.pdf'
            : 'member-card-' . $cardNumber . '.svg';

        $relativePath = trim((string) ($card[$field] ?? ''));
        $absolutePath = $relativePath !== '' ? Config::basePath('public/' . $relativePath) : '';

        if ($absolutePath === '' || !is_file($absolutePath)) {
            flash('error', 'The requested membership card file could not be generated.');
            redirect('/member/dashboard');
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($absolutePath));
        header('Content-Disposition: ' . ($asAttachment ? 'attachment' : 'inline') . '; filename="' . $fileName . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($absolutePath);
        exit;
    }

    private function memberByEmail(\PDO $pdo, string $email): array
    {
        if ($email === '') {
            return [];
        }

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

        return $memberStmt->fetch() ?: [];
    }

    private function memberCardData(\PDO $pdo, array $member): array
    {
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

                return [
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

        if ($membershipNumber !== '') {
            return [
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

        return [];
    }
}



