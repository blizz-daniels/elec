<?php
$posts = $posts ?? [];
$editingPost = $editingPost ?? null;
$status = (string) ($status ?? '');
$isEditing = is_array($editingPost);
$postStatus = (string) ($editingPost['status'] ?? 'draft');
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">News</h1>
        <p class="text-muted mb-0">Create and control the announcements shown on the public news boards.</p>
    </div>
    <?php if ($isEditing): ?>
        <a class="btn btn-outline-secondary" href="<?= url('/admin/news'); ?>">New post</a>
    <?php endif; ?>
</div>

<div class="card p-4 mb-4">
    <h2 class="h5 mb-3"><?= $isEditing ? 'Edit news post' : 'Create news post'; ?></h2>
    <form method="post" action="<?= url('/admin/news'); ?>" class="row g-3">
        <?= csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="post_id" value="<?= (int) ($editingPost['id'] ?? 0); ?>">

        <div class="col-md-8">
            <label class="form-label" for="news-title">Title</label>
            <input class="form-control" id="news-title" name="title" maxlength="190" required value="<?= htmlspecialchars((string) ($editingPost['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="news-status">Publication status</label>
            <select class="form-select" id="news-status" name="status">
                <option value="draft" <?= $postStatus === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?= $postStatus === 'published' ? 'selected' : ''; ?>>Published</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="news-summary">Summary</label>
            <textarea class="form-control" id="news-summary" name="summary" maxlength="500" rows="3" placeholder="A short preview for the homepage news board."><?= htmlspecialchars((string) ($editingPost['summary'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="news-content">News content</label>
            <textarea class="form-control" id="news-content" name="content" maxlength="20000" rows="10" required><?= htmlspecialchars((string) ($editingPost['content'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="news-pinned" name="is_pinned" value="1" <?= (int) ($editingPost['is_pinned'] ?? 0) === 1 ? 'checked' : ''; ?>>
                <label class="form-check-label" for="news-pinned">Pin this post above other published news</label>
            </div>
            <div class="d-flex gap-2">
                <?php if ($isEditing): ?>
                    <a class="btn btn-outline-secondary" href="<?= url('/admin/news'); ?>">Cancel</a>
                <?php endif; ?>
                <button class="btn btn-primary" type="submit"><?= $isEditing ? 'Save changes' : 'Save post'; ?></button>
            </div>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="h5 mb-0">All news posts</h2>
        <form method="get" action="<?= url('/admin/news'); ?>" class="d-flex align-items-center gap-2">
            <label class="visually-hidden" for="news-filter">Status</label>
            <select class="form-select form-select-sm" id="news-filter" name="status">
                <option value="">All statuses</option>
                <option value="draft" <?= $status === 'draft' ? 'selected' : ''; ?>>Drafts</option>
                <option value="published" <?= $status === 'published' ? 'selected' : ''; ?>>Published</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" type="submit">Filter</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="text-muted small">
                                <?= (int) ($post['is_pinned'] ?? 0) === 1 ? 'Pinned | ' : ''; ?>
                                <?= htmlspecialchars((string) ($post['author_name'] ?? 'Unknown author'), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </td>
                        <td><span class="badge text-bg-<?= $post['status'] === 'published' ? 'success' : 'secondary'; ?>"><?= htmlspecialchars(ucfirst((string) $post['status']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td><?= !empty($post['published_at']) ? htmlspecialchars(date('M j, Y g:i A', strtotime((string) $post['published_at'])), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                        <td class="text-end">
                            <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                                <a class="btn btn-sm btn-outline-primary" href="<?= url('/admin/news?edit=' . (int) $post['id']); ?>">Edit</a>
                                <form method="post" action="<?= url('/admin/news'); ?>">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="action" value="set_status">
                                    <input type="hidden" name="post_id" value="<?= (int) $post['id']; ?>">
                                    <input type="hidden" name="status" value="<?= $post['status'] === 'published' ? 'draft' : 'published'; ?>">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit"><?= $post['status'] === 'published' ? 'Unpublish' : 'Publish'; ?></button>
                                </form>
                                <form method="post" action="<?= url('/admin/news'); ?>" onsubmit="return confirm('Delete this news post?');">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="post_id" value="<?= (int) $post['id']; ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($posts === []): ?>
                    <tr><td colspan="4" class="text-muted">No news posts found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>