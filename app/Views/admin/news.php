<?php
$posts = $posts ?? [];
$editingPost = $editingPost ?? null;
$status = (string) ($status ?? '');
$isEditing = is_array($editingPost);
$postStatus = (string) ($editingPost['status'] ?? 'draft');
$editingImage = trim((string) ($editingPost['image_path'] ?? ''));
$currentPriorityPost = null;
foreach ($posts as $post) {
    if ((int) ($post['is_priority'] ?? 0) === 1) {
        $currentPriorityPost = $post;
        break;
    }
}
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

<?php if ($currentPriorityPost !== null): ?>
    <div class="news-priority-notice mb-4" role="status">
        <i class="fa-solid fa-bolt" aria-hidden="true"></i>
        <div>
            <strong>Current Priority News</strong>
            <span><?= htmlspecialchars((string) $currentPriorityPost['title'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
<?php endif; ?>

<div class="card p-4 mb-4 news-editor">
    <h2 class="h5 mb-1"><?= $isEditing ? 'Edit news post' : 'Create news post'; ?></h2>
    <p class="text-muted small mb-3">Priority News occupies the homepage feature position. Selecting a new one automatically returns the previous priority story to normal news.</p>
    <form method="post" action="<?= url('/admin/news'); ?>" enctype="multipart/form-data" class="row g-3">
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
        <div class="col-lg-7">
            <label class="form-label" for="news-image">Add photo</label>
            <input class="form-control" id="news-image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">JPG, PNG, or WebP. Maximum 5MB. Photos are automatically cropped and optimized for the news layout.</div>
            <?php if ($editingImage !== ''): ?>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="news-remove-image" name="remove_image" value="1">
                    <label class="form-check-label" for="news-remove-image">Remove the current photo</label>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-5">
            <div class="news-image-preview <?= $editingImage !== '' ? 'is-visible' : ''; ?>" id="news-image-preview">
                <img
                    id="news-image-preview-image"
                    src="<?= $editingImage !== '' ? htmlspecialchars(url($editingImage), ENT_QUOTES, 'UTF-8') : ''; ?>"
                    alt="Selected news photo preview"
                    <?= $editingImage === '' ? 'hidden' : ''; ?>
                >
                <span id="news-image-preview-empty" <?= $editingImage !== '' ? 'hidden' : ''; ?>>Photo preview</span>
            </div>
        </div>
        <div class="col-12 news-editor__options">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="news-priority" name="is_priority" value="1" <?= (int) ($editingPost['is_priority'] ?? 0) === 1 ? 'checked' : ''; ?>>
                <label class="form-check-label" for="news-priority">
                    <strong>Priority News</strong>
                    <span>Feature this story prominently on the homepage. Only one published post can be priority at a time.</span>
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="news-pinned" name="is_pinned" value="1" <?= (int) ($editingPost['is_pinned'] ?? 0) === 1 ? 'checked' : ''; ?>>
                <label class="form-check-label" for="news-pinned">Pin this post above other normal published news</label>
            </div>
        </div>
        <div class="col-12 d-flex align-items-center justify-content-end flex-wrap gap-2">
            <?php if ($isEditing): ?>
                <a class="btn btn-outline-secondary" href="<?= url('/admin/news'); ?>">Cancel</a>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit"><?= $isEditing ? 'Save changes' : 'Save post'; ?></button>
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
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($post['image_path'])): ?>
                                    <img class="news-admin-thumbnail" src="<?= htmlspecialchars(url((string) $post['image_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy">
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-muted small">
                                        <?php if ((int) ($post['is_priority'] ?? 0) === 1): ?><span class="news-priority-label">Priority News</span><?php endif; ?>
                                        <?= (int) ($post['is_pinned'] ?? 0) === 1 ? 'Pinned | ' : ''; ?>
                                        <?= htmlspecialchars((string) ($post['author_name'] ?? 'Unknown author'), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
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

<script>
(() => {
    const input = document.getElementById('news-image');
    const preview = document.getElementById('news-image-preview');
    const previewImage = document.getElementById('news-image-preview-image');
    const previewEmpty = document.getElementById('news-image-preview-empty');
    if (!input || !preview || !previewImage || !previewEmpty) {
        return;
    }

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) {
            return;
        }

        previewImage.src = URL.createObjectURL(file);
        previewImage.hidden = false;
        previewEmpty.hidden = true;
        preview.classList.add('is-visible');
    });
})();
</script>