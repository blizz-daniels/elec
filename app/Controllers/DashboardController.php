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

        $role = strtolower(trim((string) Auth::role()));
        if ($role === 'registered-member' || $role === 'member') {
            redirect('/member/dashboard');
        }

        $pdo = Database::pdo();

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

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'progress' => $progress,
            'recentMembers' => $recentMembers,
            'recentResults' => $recentResults,
            'statusBreakdown' => $statusBreakdown,
        ]);
    }
}
