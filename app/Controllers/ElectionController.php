<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\Request;

final class ElectionController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requiresRole(['super-admin', 'state-executive']);
        $pdo = Database::pdo();

        if ($request->method() === 'POST') {
            $this->handlePost($request, $pdo);
        }

        $elections = $pdo->query(
            'SELECT elections.*,
                    COUNT(DISTINCT candidates.id) AS candidate_count,
                    COUNT(DISTINCT vote_results.id) AS result_count
             FROM elections
             LEFT JOIN candidates ON candidates.election_id = elections.id
             LEFT JOIN vote_results ON vote_results.election_id = elections.id
             GROUP BY elections.id
             ORDER BY elections.id DESC'
        )->fetchAll();

        $this->view('elections/index', [
            'title' => 'Elections',
            'elections' => $elections,
        ]);
    }

    private function handlePost(Request $request, \PDO $pdo): void
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid security token.');
            redirect('/elections');
        }

        $action = (string) $request->input('action', '');
        $user = Auth::user() ?? [];

        try {
            switch ($action) {
                case 'create':
                    $name = trim((string) $request->input('name'));
                    $date = trim((string) $request->input('election_date'));
                    $type = trim((string) $request->input('election_type'));
                    if ($name === '' || $date === '' || $type === '') {
                        throw new \RuntimeException('Election name, date, and type are required.');
                    }

                    $stmt = $pdo->prepare(
                        'INSERT INTO elections (name, election_date, election_type, status, description, created_by)
                         VALUES (:name, :election_date, :election_type, :status, :description, :created_by)'
                    );
                    $stmt->execute([
                        'name' => $name,
                        'election_date' => $date,
                        'election_type' => $type,
                        'status' => 'draft',
                        'description' => trim((string) $request->input('description', '')) ?: null,
                        'created_by' => $user['id'] ?? null,
                    ]);

                    flash('success', 'Election created.');
                    break;

                case 'status':
                    $id = (int) $request->input('election_id', 0);
                    $status = (string) $request->input('status', 'draft');
                    $allowed = ['draft', 'open', 'closed', 'archived'];
                    if ($id <= 0 || !in_array($status, $allowed, true)) {
                        throw new \RuntimeException('Invalid election status.');
                    }

                    $stmt = $pdo->prepare('UPDATE elections SET status = :status, updated_at = NOW() WHERE id = :id');
                    $stmt->execute([
                        'status' => $status,
                        'id' => $id,
                    ]);

                    flash('success', 'Election status updated.');
                    break;

                case 'delete':
                    $id = (int) $request->input('election_id', 0);
                    if ($id <= 0) {
                        throw new \RuntimeException('Invalid election.');
                    }

                    $stmt = $pdo->prepare('DELETE FROM elections WHERE id = :id');
                    $stmt->execute(['id' => $id]);

                    flash('success', 'Election deleted.');
                    break;

                default:
                    flash('error', 'Unsupported action.');
            }
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }

        redirect('/elections');
    }
}
