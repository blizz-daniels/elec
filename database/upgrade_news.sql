CREATE TABLE IF NOT EXISTS news_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id BIGINT UNSIGNED NULL,
    title VARCHAR(190) NOT NULL,
    summary VARCHAR(500) NULL,
    content MEDIUMTEXT NOT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_news_posts_public (status, published_at),
    INDEX idx_news_posts_pinned (is_pinned, published_at),
    CONSTRAINT fk_news_posts_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
