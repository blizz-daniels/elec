<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\FileUpload;
use App\Support\Request;
use App\Support\Validator;

final class ResultController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requiresRole(['super-admin', 'state-executive', 'lga-executive', 'polling-marshal']);
        $pdo = Database::pdo();

        if ($request->method() === 'POST') {
            $this->handlePost($request, $pdo);
        }

        $user = Auth::user() ?? [];
        $assignedPollingUnits = [];
        if (($user['role_slug'] ?? '') === 'polling-marshal' && !empty($user['id'])) {
            $stmt = $pdo->prepare(
                'SELECT polling_units.id, polling_units.polling_name
                 FROM polling_marshals
                 INNER JOIN polling_units ON polling_units.id = polling_marshals.polling_unit_id
                 WHERE polling_marshals.user_id = :user_id
                 ORDER BY polling_units.polling_name'
            );
            $stmt->execute(['user_id' => $user['id']]);
            $assignedPollingUnits = $stmt->fetchAll();
        }

        if ($assignedPollingUnits === []) {
            $assignedPollingUnits = $pdo->query('SELECT id, polling_name FROM polling_units ORDER BY polling_name')->fetchAll();
        }

        $results = $pdo->query(
            'SELECT vote_results.*,
                    elections.name AS election_name,
                    elections.status AS election_status,
                    polling_units.polling_name,
                    polling_units.polling_code,
                    COALESCE(attachment_stats.total_attachments, 0) AS attachment_count
             FROM vote_results
             INNER JOIN elections ON elections.id = vote_results.election_id
             INNER JOIN polling_units ON polling_units.id = vote_results.polling_unit_id
             LEFT JOIN (
                SELECT related_id, COUNT(*) AS total_attachments
                FROM attachments
                WHERE related_type = "vote_result"
                GROUP BY related_id
             ) AS attachment_stats ON attachment_stats.related_id = vote_results.id
             ORDER BY vote_results.id DESC'
        )->fetchAll();

        $this->view('results/index', [
            'title' => 'Results',
            'results' => $results,
            'elections' => $pdo->query('SELECT id, name, status FROM elections ORDER BY id DESC')->fetchAll(),
            'pollingUnits' => $assignedPollingUnits,
        ]);
    }

    private function handlePost(Request $request, \PDO $pdo): void
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid security token.');
            redirect('/results');
        }

        $action = (string) $request->input('action', '');
        $user = Auth::user() ?? [];

        if ($action !== 'submit_result' && ($user['role_slug'] ?? '') === 'polling-marshal') {
            flash('error', 'You are not allowed to review results.');
            redirect('/results');
        }

        try {
            switch ($action) {
                case 'submit_result':
                    $this->submitResult($request, $pdo);
                    break;

                case 'review':
                    $this->reviewResult($request, $pdo);
                    break;

                case 'delete':
                    $id = (int) $request->input('result_id', 0);
                    if ($id <= 0) {
                        throw new \RuntimeException('Invalid result.');
                    }

                    $stmt = $pdo->prepare('DELETE FROM vote_results WHERE id = :id');
                    $stmt->execute(['id' => $id]);
                    flash('success', 'Result deleted.');
                    break;

                default:
                    flash('error', 'Unsupported action.');
            }
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }

        redirect('/results');
    }

    private function submitResult(Request $request, \PDO $pdo): void
    {
        $required = Validator::required($request->all(), [
            'election_id',
            'polling_unit_id',
            'accredited_voters',
            'total_votes',
            'rejected_votes',
            'cancelled_votes',
        ]);
        if ($required !== []) {
            throw new \RuntimeException(reset($required));
        }

        $user = Auth::user() ?? [];
        $pollingUnitId = (int) $request->input('polling_unit_id', 0);

        if (($user['role_slug'] ?? '') === 'polling-marshal' && !empty($user['id'])) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM polling_marshals WHERE user_id = :user_id AND polling_unit_id = :polling_unit_id');
            $stmt->execute([
                'user_id' => $user['id'],
                'polling_unit_id' => $pollingUnitId,
            ]);
            if ((int) $stmt->fetchColumn() === 0) {
                throw new \RuntimeException('You are not assigned to this polling unit.');
            }
        }

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO vote_results
                    (election_id, polling_unit_id, submitted_by, status, accredited_voters, total_votes, rejected_votes, cancelled_votes, submitted_at, remarks)
                 VALUES
                    (:election_id, :polling_unit_id, :submitted_by, :status, :accredited_voters, :total_votes, :rejected_votes, :cancelled_votes, NOW(), :remarks)'
            );
            $stmt->execute([
                'election_id' => (int) $request->input('election_id', 0),
                'polling_unit_id' => $pollingUnitId,
                'submitted_by' => $user['id'] ?? null,
                'status' => 'submitted',
                'accredited_voters' => (int) $request->input('accredited_voters', 0),
                'total_votes' => (int) $request->input('total_votes', 0),
                'rejected_votes' => (int) $request->input('rejected_votes', 0),
                'cancelled_votes' => (int) $request->input('cancelled_votes', 0),
                'remarks' => trim((string) $request->input('remarks', '')) ?: null,
            ]);

            $resultId = (int) $pdo->lastInsertId();
            $attachment = $request->file('attachment');
            if ($attachment && ($attachment['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (!Validator::maxBytes($attachment, 2048)) {
                    throw new \RuntimeException('Result attachment must be 2MB or smaller.');
                }

                if (!Validator::mimeIn($attachment, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])) {
                    throw new \RuntimeException('Result attachment must be a PDF or image.');
                }

                $path = FileUpload::store($attachment, 'uploads/results');
                $attachStmt = $pdo->prepare(
                    'INSERT INTO attachments (related_type, related_id, file_name, file_path, mime_type, file_size)
                     VALUES (:related_type, :related_id, :file_name, :file_path, :mime_type, :file_size)'
                );
                $attachStmt->execute([
                    'related_type' => 'vote_result',
                    'related_id' => $resultId,
                    'file_name' => (string) ($attachment['name'] ?? 'attachment'),
                    'file_path' => $path,
                    'mime_type' => (string) (mime_content_type((string) ($attachment['tmp_name'] ?? '')) ?: 'application/octet-stream'),
                    'file_size' => (int) ($attachment['size'] ?? 0),
                ]);
            }

            $pdo->commit();
            flash('success', 'Result submitted.');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    private function reviewResult(Request $request, \PDO $pdo): void
    {
        $resultId = (int) $request->input('result_id', 0);
        $status = (string) $request->input('status', '');
        if ($resultId <= 0 || !in_array($status, ['verified', 'approved', 'published', 'rejected'], true)) {
            throw new \RuntimeException('Invalid review request.');
        }

        $timestamps = [
            'verified' => 'verified_at',
            'approved' => 'approved_at',
            'published' => 'published_at',
        ];

        $set = 'status = :status';
        $params = [
            'status' => $status,
            'id' => $resultId,
        ];

        if (isset($timestamps[$status])) {
            $set .= ', ' . $timestamps[$status] . ' = NOW()';
        }

        $stmt = $pdo->prepare("UPDATE vote_results SET {$set}, updated_at = NOW() WHERE id = :id");
        $stmt->execute($params);

        flash('success', 'Result updated.');
    }
}
