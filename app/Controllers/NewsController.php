<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\NewsPost;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\Request;

final class NewsController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requiresRole(['super-admin']);
        $posts = new NewsPost();

        if ($request->method() === 'POST') {
            $this->handlePost($request, $posts);
        }

        $status = trim((string) $request->input('status', ''));
        if (!in_array($status, ['draft', 'published'], true)) {
            $status = '';
        }

        $editId = (int) $request->input('edit', 0);
        $editingPost = $editId > 0 ? $posts->find($editId) : null;
        if ($editId > 0 && $editingPost === null) {
            flash('error', 'News post not found.');
            redirect('/admin/news');
        }

        $this->view('admin/news', [
            'title' => 'News',
            'posts' => $posts->forAdmin($status),
            'editingPost' => $editingPost,
            'status' => $status,
        ]);
    }

    private function handlePost(Request $request, NewsPost $posts): void
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid security token.');
            redirect('/admin/news');
        }

        $action = (string) $request->input('action', '');
        $postId = (int) $request->input('post_id', 0);

        if ($action === 'save') {
            $this->savePost($request, $posts, $postId);
            return;
        }

        if ($postId <= 0) {
            flash('error', 'A valid news post is required.');
            redirect('/admin/news');
        }

        $post = $posts->find($postId);
        if ($post === null) {
            flash('error', 'News post not found.');
            redirect('/admin/news');
        }

        if ($action === 'delete') {
            $posts->remove($postId);
            $this->logActivity('news.deleted', $postId, (string) $post['title']);
            flash('success', 'News post deleted.');
            redirect('/admin/news');
        }

        if ($action === 'set_status') {
            $status = (string) $request->input('status', '');
            if (!in_array($status, ['draft', 'published'], true)) {
                flash('error', 'Invalid publication status.');
                redirect('/admin/news');
            }

            $publishedAt = $status === 'published'
                ? ((string) ($post['published_at'] ?? '') ?: date('Y-m-d H:i:s'))
                : null;
            $posts->updatePublicationStatus($postId, $status, $publishedAt);
            $this->logActivity('news.' . $status, $postId, (string) $post['title']);
            flash('success', $status === 'published' ? 'News post published.' : 'News post returned to draft.');
            redirect('/admin/news');
        }

        flash('error', 'Unknown news action.');
        redirect('/admin/news');
    }

    private function savePost(Request $request, NewsPost $posts, int $postId): void
    {
        $title = trim((string) $request->input('title', ''));
        $summary = trim((string) $request->input('summary', ''));
        $content = trim((string) $request->input('content', ''));
        $status = (string) $request->input('status', 'draft');
        $isPinned = (int) $request->input('is_pinned', 0) === 1 ? 1 : 0;

        if ($title === '' || mb_strlen($title) > 190) {
            flash('error', 'Enter a title of up to 190 characters.');
            redirect($postId > 0 ? '/admin/news?edit=' . $postId : '/admin/news');
        }
        if (mb_strlen($summary) > 500) {
            flash('error', 'The summary may not exceed 500 characters.');
            redirect($postId > 0 ? '/admin/news?edit=' . $postId : '/admin/news');
        }
        if ($content === '' || mb_strlen($content) > 20000) {
            flash('error', 'Enter news content of up to 20,000 characters.');
            redirect($postId > 0 ? '/admin/news?edit=' . $postId : '/admin/news');
        }
        if (!in_array($status, ['draft', 'published'], true)) {
            flash('error', 'Invalid publication status.');
            redirect($postId > 0 ? '/admin/news?edit=' . $postId : '/admin/news');
        }

        $existing = $postId > 0 ? $posts->find($postId) : null;
        if ($postId > 0 && $existing === null) {
            flash('error', 'News post not found.');
            redirect('/admin/news');
        }

        $publishedAt = $status === 'published'
            ? ((string) ($existing['published_at'] ?? '') ?: date('Y-m-d H:i:s'))
            : null;
        $data = [
            'title' => $title,
            'summary' => $summary !== '' ? $summary : null,
            'content' => $content,
            'status' => $status,
            'is_pinned' => $isPinned,
            'published_at' => $publishedAt,
        ];

        if ($existing !== null) {
            $posts->updatePost($postId, $data);
            $this->logActivity('news.updated', $postId, $title);
            flash('success', 'News post updated.');
        } else {
            $currentUser = Auth::user() ?? [];
            $postId = $posts->create($data + ['author_id' => (int) ($currentUser['id'] ?? 0)]);
            $this->logActivity('news.created', $postId, $title);
            flash('success', $status === 'published' ? 'News post published.' : 'News draft saved.');
        }

        redirect('/admin/news');
    }

    private function logActivity(string $action, int $postId, string $title): void
    {
        $currentUser = Auth::user() ?? [];
        $stmt = Database::pdo()->prepare(
            'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, device, metadata)
             VALUES (:user_id, :action, :entity_type, :entity_id, :ip_address, :device, :metadata)'
        );
        $stmt->execute([
            'user_id' => (int) ($currentUser['id'] ?? 0) ?: null,
            'action' => $action,
            'entity_type' => 'news_post',
            'entity_id' => $postId,
            'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
            'device' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
            'metadata' => json_encode(['title' => $title], JSON_UNESCAPED_SLASHES),
        ]);
    }
}