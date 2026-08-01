<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Config;
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
                'SELECT polling_units.id,
                        polling_units.polling_name,
                        polling_units.polling_code,
                        polling_units.senatorial_district_id,
                        polling_units.lga_id,
                        polling_units.ward_id,
                        senatorial_districts.name AS district_name,
                        lgas.name AS lga_name,
                        wards.name AS ward_name
                 FROM polling_marshals
                 INNER JOIN polling_units ON polling_units.id = polling_marshals.polling_unit_id
                 LEFT JOIN senatorial_districts ON senatorial_districts.id = polling_units.senatorial_district_id
                 LEFT JOIN lgas ON lgas.id = polling_units.lga_id
                 LEFT JOIN wards ON wards.id = polling_units.ward_id
                 WHERE polling_marshals.user_id = :user_id
                 ORDER BY polling_units.polling_name'
            );
            $stmt->execute(['user_id' => $user['id']]);
            $assignedPollingUnits = $stmt->fetchAll();
        }

        if ($assignedPollingUnits === []) {
            $assignedPollingUnits = $pdo->query(
                'SELECT polling_units.id,
                        polling_units.polling_name,
                        polling_units.polling_code,
                        polling_units.senatorial_district_id,
                        polling_units.lga_id,
                        polling_units.ward_id,
                        senatorial_districts.name AS district_name,
                        lgas.name AS lga_name,
                        wards.name AS ward_name
                 FROM polling_units
                 LEFT JOIN senatorial_districts ON senatorial_districts.id = polling_units.senatorial_district_id
                 LEFT JOIN lgas ON lgas.id = polling_units.lga_id
                 LEFT JOIN wards ON wards.id = polling_units.ward_id
                 ORDER BY polling_units.polling_name'
            )->fetchAll();
        }

        $results = $pdo->query(
            'SELECT vote_results.*, 
                    elections.name AS election_name,
                    elections.status AS election_status,
                    polling_units.polling_name,
                    polling_units.polling_code,
                    COALESCE(attachment_stats.total_attachments, 0) AS attachment_count,
                    latest_attachment.id AS attachment_id,
                    latest_attachment.file_name AS attachment_file_name,
                    latest_attachment.file_path AS attachment_file_path,
                    latest_attachment.mime_type AS attachment_mime_type,
                    latest_attachment.file_size AS attachment_file_size
             FROM vote_results
             INNER JOIN elections ON elections.id = vote_results.election_id
             INNER JOIN polling_units ON polling_units.id = vote_results.polling_unit_id
             LEFT JOIN (
                SELECT related_id, COUNT(*) AS total_attachments
                FROM attachments
                WHERE related_type = "vote_result"
                GROUP BY related_id
             ) AS attachment_stats ON attachment_stats.related_id = vote_results.id
             LEFT JOIN attachments AS latest_attachment ON latest_attachment.id = (
                SELECT a.id
                FROM attachments a
                WHERE a.related_type = "vote_result" AND a.related_id = vote_results.id
                ORDER BY a.id DESC
                LIMIT 1
             )
             ORDER BY vote_results.id DESC'
        )->fetchAll();

        $this->view('results/index', [
            'title' => 'Results',
            'results' => $results,
            'elections' => $pdo->query('SELECT id, name, status FROM elections ORDER BY id DESC')->fetchAll(),
            'pollingUnits' => $assignedPollingUnits,
        ]);
    }

    public function attachment(Request $request): void
    {
        Auth::requiresRole(['super-admin', 'state-executive', 'lga-executive', 'polling-marshal']);
        $pdo = Database::pdo();
        $attachmentId = (int) $request->input('id', 0);
        $mode = (string) $request->input('mode', 'open');

        if ($attachmentId <= 0) {
            flash('error', 'Attachment not found.');
            redirect('/results');
        }

        $stmt = $pdo->prepare(
            'SELECT id, file_name, file_path, mime_type, file_size
             FROM attachments
             WHERE id = :id AND related_type = :related_type
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $attachmentId,
            'related_type' => 'vote_result',
        ]);
        $attachment = $stmt->fetch();
        if (!$attachment) {
            flash('error', 'Attachment not found.');
            redirect('/results');
        }

        $relativePath = trim((string) ($attachment['file_path'] ?? ''));
        $absolutePath = $relativePath !== '' ? Config::basePath('public/' . $relativePath) : '';
        if ($absolutePath === '' || !is_file($absolutePath)) {
            flash('error', 'Attachment file is missing from the server.');
            redirect('/results');
        }

        $fileName = basename(trim((string) ($attachment['file_name'] ?? 'attachment')) ?: 'attachment');
        $mime = trim((string) ($attachment['mime_type'] ?? 'application/octet-stream')) ?: 'application/octet-stream';
        $disposition = $mode === 'download' ? 'attachment' : 'inline';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($absolutePath));
        header('Content-Disposition: ' . $disposition . '; filename="' . str_replace('"', '', $fileName) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($absolutePath);
        exit;
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

