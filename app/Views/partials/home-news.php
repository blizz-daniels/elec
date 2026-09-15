<?php
$newsPosts = $newsPosts ?? [];
$featuredPost = $newsPosts[0] ?? null;
$newsItems = array_slice($newsPosts, 1);
$newsExcerpt = static function (array $post, int $length = 180): string {
    $text = trim((string) ($post['summary'] ?? ''));
    if ($text === '') {
        $text = trim(strip_tags((string) ($post['content'] ?? '')));
    }

    return mb_strimwidth($text, 0, $length, '...');
};
$newsDate = static function (array $post): string {
    $publishedAt = (string) ($post['published_at'] ?? '');
    return $publishedAt !== '' ? date('F j, Y', strtotime($publishedAt)) : '';
};
$newsUrl = static fn (array $post): string => url('/news') . '#news-' . (int) ($post['id'] ?? 0);
?>
<?php if ($featuredPost !== null): ?>
    <?php $featuredImage = trim((string) ($featuredPost['image_path'] ?? '')); ?>
    <section class="news-section">
        <div class="section-heading" style="padding:0;">
            <div class="section-heading__eyebrow">News</div>
            <h2>Updates and announcements</h2>
        </div>
        <div class="news-grid">
            <article class="news-feature <?= (int) ($featuredPost['is_priority'] ?? 0) === 1 ? 'news-feature--priority' : ''; ?>">
                <?php if ($featuredImage !== ''): ?>
                    <a class="news-feature__media" href="<?= htmlspecialchars($newsUrl($featuredPost), ENT_QUOTES, 'UTF-8'); ?>" aria-label="Read <?= htmlspecialchars((string) $featuredPost['title'], ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="<?= htmlspecialchars(url($featuredImage), ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars((string) $featuredPost['title'], ENT_QUOTES, 'UTF-8'); ?>" fetchpriority="high">
                    </a>
                <?php else: ?>
                    <div class="news-feature__media news-feature__media--placeholder" aria-hidden="true">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                <?php endif; ?>
                <div class="news-feature__body">
                    <div class="news-feature__eyebrow">
                        <?= (int) ($featuredPost['is_priority'] ?? 0) === 1 ? 'Priority News' : ((int) ($featuredPost['is_pinned'] ?? 0) === 1 ? 'Pinned update' : 'Latest update'); ?>
                    </div>
                    <h3><?= htmlspecialchars((string) $featuredPost['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="news-item__meta"><span><?= htmlspecialchars($newsDate($featuredPost), ENT_QUOTES, 'UTF-8'); ?></span></div>
                    <p><?= htmlspecialchars($newsExcerpt($featuredPost, 320), ENT_QUOTES, 'UTF-8'); ?></p>
                    <a class="news-link" href="<?= htmlspecialchars($newsUrl($featuredPost), ENT_QUOTES, 'UTF-8'); ?>">Read more <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </article>
            <div class="news-list">
                <?php foreach ($newsItems as $post): ?>
                    <?php $imagePath = trim((string) ($post['image_path'] ?? '')); ?>
                    <article class="news-item">
                        <?php if ($imagePath !== ''): ?>
                            <img class="news-item__image" src="<?= htmlspecialchars(url($imagePath), ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy">
                        <?php endif; ?>
                        <div class="news-item__body">
                            <h3><?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?= htmlspecialchars($newsExcerpt($post), ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="news-item__meta"><span><?= htmlspecialchars($newsDate($post), ENT_QUOTES, 'UTF-8'); ?></span><a href="<?= htmlspecialchars($newsUrl($post), ENT_QUOTES, 'UTF-8'); ?>">Read more</a></div>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if ($newsItems === []): ?>
                    <div class="news-item news-item--empty">More announcements will appear here as they are published.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>