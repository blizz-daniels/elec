<?php
$newsPosts = $newsPosts ?? [];
$newsExcerpt = static function (array $post): string {
    $text = trim((string) ($post['summary'] ?? ''));
    if ($text === '') {
        $text = trim(strip_tags((string) ($post['content'] ?? '')));
    }

    return $text;
};
?>

<section class="public-news">
    <div class="public-news__heading">
        <div class="section-heading__eyebrow">News board</div>
        <h1>Updates and announcements</h1>
        <p>Official communications from Yayi Youth Vanguard.</p>
    </div>

    <?php if ($newsPosts !== []): ?>
        <div class="news-archive-grid">
            <?php foreach ($newsPosts as $post): ?>
                <?php $imagePath = trim((string) ($post['image_path'] ?? '')); ?>
                <article class="news-archive-item <?= (int) ($post['is_priority'] ?? 0) === 1 ? 'news-archive-item--priority' : ''; ?>" id="news-<?= (int) $post['id']; ?>">
                    <?php if ($imagePath !== ''): ?>
                        <img class="news-archive-item__image" src="<?= htmlspecialchars(url($imagePath), ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                    <?php endif; ?>
                    <div class="news-archive-item__meta">
                        <div>
                            <?php if ((int) ($post['is_priority'] ?? 0) === 1): ?>
                                <span class="news-priority-label"><i class="fa-solid fa-bolt" aria-hidden="true"></i> Priority News</span>
                            <?php elseif ((int) ($post['is_pinned'] ?? 0) === 1): ?>
                                <span class="news-pin"><i class="fa-solid fa-thumbtack" aria-hidden="true"></i> Pinned</span>
                            <?php endif; ?>
                        </div>
                        <time datetime="<?= htmlspecialchars((string) $post['published_at'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars(date('F j, Y', strtotime((string) $post['published_at'])), ENT_QUOTES, 'UTF-8'); ?>
                        </time>
                    </div>
                    <h2><?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <?php if ($newsExcerpt($post) !== ''): ?>
                        <p class="news-archive-item__summary"><?= htmlspecialchars($newsExcerpt($post), ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <div class="news-archive-item__content"><?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')); ?></div>
                    <?php if (!empty($post['author_name'])): ?>
                        <div class="news-archive-item__author">Posted by <?= htmlspecialchars((string) $post['author_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="news-empty-state">No news has been published yet.</div>
    <?php endif; ?>
</section>