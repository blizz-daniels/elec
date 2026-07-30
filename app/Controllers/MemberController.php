<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Database;
use App\Support\Request;

final class MemberController extends Controller
{
    public function dashboard(Request $request): void
    {
        Auth::requiresLogin();

        if (Auth::role() !== 'registered-member') {
            redirect('/dashboard');
        }

        $pdo = Database::pdo();
        $currentUser = Auth::user();
        $email = trim((string) ($currentUser['email'] ?? ''));

        $stmt = $pdo->prepare(
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
        $stmt->execute(['email' => $email]);
        $profile = $stmt->fetch() ?: $currentUser;

        $this->view('member/dashboard', [
            'title' => 'Member Dashboard',
            'profile' => $profile,
        ]);
    }
}
