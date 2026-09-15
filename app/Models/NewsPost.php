<?php

declare(strict_types=1);

namespace App\Models;

final class NewsPost extends BaseModel
{
    protected string $table = 'news_posts';

    public function published(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->pdo->prepare(
            "SELECT news_posts.*, users.full_name AS author_name
             FROM news_posts
             LEFT JOIN users ON users.id = news_posts.author_id
             WHERE news_posts.status = 'published'
               AND news_posts.published_at IS NOT NULL
               AND news_posts.published_at <= NOW()
             ORDER BY news_posts.is_pinned DESC, news_posts.published_at DESC, news_posts.id DESC
             LIMIT {$limit}"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function forAdmin(string $status = ''): array
    {
        $params = [];
        $sql = 'SELECT news_posts.*, users.full_name AS author_name
                FROM news_posts
                LEFT JOIN users ON users.id = news_posts.author_id';

        if (in_array($status, ['draft', 'published'], true)) {
            $sql .= ' WHERE news_posts.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY news_posts.updated_at DESC, news_posts.id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function updatePost(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE news_posts
             SET title = :title,
                 summary = :summary,
                 content = :content,
                 status = :status,
                 is_pinned = :is_pinned,
                 published_at = :published_at,
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute($data + ['id' => $id]);
    }

    public function updatePublicationStatus(int $id, string $status, ?string $publishedAt): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE news_posts
             SET status = :status, published_at = :published_at, updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'published_at' => $publishedAt,
        ]);
    }

    public function remove(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM news_posts WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}