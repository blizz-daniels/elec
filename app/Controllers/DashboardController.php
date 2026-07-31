<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Database;
use App\Support\Request;

final class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requiresLogin();

        $roleSlug = strtolower(trim((string) Auth::role()));
        if ($roleSlug === 'registered-member' || $roleSlug === 'member') {
            redirect('/member/dashboard');
        }

        $pdo = Database::pdo();
        $dashboard = $this->dashboardForRole($pdo, $roleSlug);
        $this->view($dashboard['view'], $dashboard['data']);
    }

    private function dashboardForRole(\PDO $pdo, string $roleSlug): array
    {
        $common = $this->commonDashboardData($pdo);

        return match ($roleSlug) {
            'state-executive' => [
                'view' => 'dashboard/state-executive',
                'data' => array_merge($common, [
                    'title' => 'State Executive Dashboard',
                    'dashboardTitle' => 'State Executive Dashboard',
                    'dashboardSubtitle' => 'Statewide oversight for districts, LGAs, wards, polling units, and election activity.',
                    'roleLabel' => 'State Executive',
                ]),
            ],
            'lga-executive' => [
                'view' => 'dashboard/lga-executive',
                'data' => array_merge($common, [
                    'title' => 'LGA Executive Dashboard',
                    'dashboardTitle' => 'LGA Executive Dashboard',
                    'dashboardSubtitle' => 'Local-government operations, member approvals, polling units, and result handling.',
                    'roleLabel' => 'LGA Executive',
                ]),
            ],
            'ward-executive' => [
                'view' => 'dashboard/ward-executive',
                'data' => array_merge($common, [
                    'title' => 'Ward Executive Dashboard',
                    'dashboardTitle' => 'Ward Executive Dashboard',
                    'dashboardSubtitle' => 'Ward-level monitoring for members, polling activity, and result progress.',
                    'roleLabel' => 'Ward Executive',
                ]),
            ],
            'polling-marshal' => [
                'view' => 'dashboard/polling-marshal',
                'data' => array_merge($common, [
                    'title' => 'Polling Marshal Dashboard',
                    'dashboardTitle' => 'Polling Marshal Dashboard',
                    'dashboardSubtitle' => 'Polling-station assignment, result submission, and verification workflow.',
                    'roleLabel' => 'Polling Marshal',
                ]),
            ],
            default => [
                'view' => 'dashboard/index',
                'data' => array_merge($common, [
                    'title' => 'Super Admin Dashboard',
                    'dashboardTitle' => 'Super Admin Dashboard',
                    'dashboardSubtitle' => 'Full platform overview across membership, elections, results, and geography.',
                    'roleLabel' => 'Super Admin',
                ]),
            ],
        };
    }

    private function commonDashboardData(\PDO $pdo): array
    {
        $stats = [
            'members' => (int) $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn(),
            'approved_members' => (int) $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'approved'")->fetchColumn(),
            'polling_units' => (int) $pdo->query('SELECT COUNT(*) FROM polling_units')->fetchColumn(),
            'elections' => (int) $pdo->query('SELECT COUNT(*) FROM elections')->fetchColumn(),
            'open_elections' => (int) $pdo->query("SELECT COUNT(*) FROM elections WHERE status = 'open'")->fetchColumn(),
            'results_submitted' => (int) $pdo->query('SELECT COUNT(*) FROM vote_results')->fetchColumn(),
            'results_pending' => (int) $pdo->query("SELECT COUNT(*) FROM vote_results WHERE status IN ('submitted', 'pending')")->fetchColumn(),
            'results_verified' => (int) $pdo->query("SELECT COUNT(*) FROM vote_results WHERE status IN ('verified', 'approved', 'published')")->fetchColumn(),
            'districts' => (int) $pdo->query('SELECT COUNT(*) FROM senatorial_districts')->fetchColumn(),
            'lgas' => (int) $pdo->query('SELECT COUNT(*) FROM lgas')->fetchColumn(),
            'wards' => (int) $pdo->query('SELECT COUNT(*) FROM wards')->fetchColumn(),
        ];

        $progress = 0;
        if ($stats['polling_units'] > 0) {
            $progress = (int) round(($stats['results_verified'] / $stats['polling_units']) * 100);
        }

        $recentMembers = $pdo->query(
            'SELECT members.id, members.membership_number, members.surname, members.first_name, members.status,
                    COALESCE(lgas.name, "-") AS lga_name, members.created_at
             FROM members
             LEFT JOIN lgas ON lgas.id = members.lga_id
             ORDER BY members.id DESC
             LIMIT 5'
        )->fetchAll();

        $recentResults = $pdo->query(
            'SELECT vote_results.id, vote_results.status, vote_results.accredited_voters, vote_results.total_votes,
                    vote_results.created_at, elections.name AS election_name, polling_units.polling_name
             FROM vote_results
             INNER JOIN elections ON elections.id = vote_results.election_id
             INNER JOIN polling_units ON polling_units.id = vote_results.polling_unit_id
             ORDER BY vote_results.id DESC
             LIMIT 5'
        )->fetchAll();

        $statusBreakdown = $pdo->query(
            'SELECT status, COUNT(*) AS total
             FROM vote_results
             GROUP BY status
             ORDER BY total DESC'
        )->fetchAll();

        $assignedPollingUnits = [];
        $assignedCount = 0;
        $user = Auth::user() ?? [];
        $email = trim((string) ($user['email'] ?? ''));
        if ($email !== '') {
            $assignedStmt = $pdo->prepare(
                'SELECT polling_marshals.id AS marshal_id,
                        polling_units.polling_name,
                        polling_units.polling_code,
                        wards.name AS ward_name,
                        lgas.name AS lga_name,
                        polling_marshals.status AS marshal_status
                 FROM polling_marshals
                 INNER JOIN polling_units ON polling_units.id = polling_marshals.polling_unit_id
                 LEFT JOIN wards ON wards.id = polling_marshals.ward_id
                 LEFT JOIN lgas ON lgas.id = wards.lga_id
                 LEFT JOIN users ON users.id = polling_marshals.user_id
                 WHERE users.email = :email
                 ORDER BY polling_marshals.id DESC'
            );
            $assignedStmt->execute(['email' => $email]);
            $assignedPollingUnits = $assignedStmt->fetchAll();
            $assignedCount = count($assignedPollingUnits);
        }

        return [
            'stats' => $stats,
            'progress' => $progress,
            'recentMembers' => $recentMembers,
            'recentResults' => $recentResults,
            'statusBreakdown' => $statusBreakdown,
            'assignedPollingUnits' => $assignedPollingUnits,
            'assignedCount' => $assignedCount,
            'roleSlug' => strtolower(trim((string) Auth::role())),
        ];
    }
}