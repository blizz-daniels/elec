<?php

declare(strict_types=1);

namespace App\Models;

final class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT users.*, roles.slug AS role_slug, roles.name AS role_name
             FROM users
             LEFT JOIN roles ON roles.id = users.role_id
             WHERE users.email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user || !password_verify($password, (string) $user['password'])) {
            return null;
        }

        return $user;
    }
}
