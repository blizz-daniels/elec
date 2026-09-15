-- Adds optional image and single-feature controls to existing news records.
-- Existing articles remain valid: both new fields have safe defaults.
ALTER TABLE news_posts
    ADD COLUMN image_path VARCHAR(255) NULL AFTER is_pinned,
    ADD COLUMN is_priority TINYINT(1) NOT NULL DEFAULT 0 AFTER image_path,
    ADD INDEX idx_news_posts_priority (is_priority, status, published_at);

-- A draft cannot remain the active homepage priority item.
UPDATE news_posts
SET is_priority = 0
WHERE status <> 'published';