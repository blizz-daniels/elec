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
        if ($user && password_verify($password, (string) $user['password'])) {
            $status = strtolower(trim((string) ($user['status'] ?? 'active')));
            if ($status === '' || $status === 'active') {
                return $user;
            }
        }

        $member = $this->findApprovedMemberByEmail($email);
        if (!$member || !password_verify($password, (string) $member['password'])) {
            return null;
        }

        $this->ensureRegisteredMemberAccount($member);

        return $this->findByEmail($email);
    }

    private function findApprovedMemberByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM members
             WHERE email = :email
               AND status = :status
             LIMIT 1'
        );
        $stmt->execute([
            'email' => $email,
            'status' => 'approved',
        ]);
        $member = $stmt->fetch();

        return $member ?: null;
    }

    private function ensureRegisteredMemberAccount(array $member): void
    {
        $roleId = $this->roleId('registered-member');
        $fullName = trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? ''));
        $email = trim((string) ($member['email'] ?? ''));
        $phone = trim((string) ($member['phone'] ?? ''));
        $password = (string) ($member['password'] ?? '');

        if ($fullName === '' || $email === '' || $password === '') {
            throw new \RuntimeException('Member data is incomplete.');
        }

        $userStmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $userStmt->execute(['email' => $email]);
        $userId = (int) ($userStmt->fetchColumn() ?: 0);

        if ($userId > 0) {
            $updateUser = $this->pdo->prepare(
                'UPDATE users
                 SET role_id = :role_id,
                     full_name = :full_name,
                     phone = :phone,
                     password = :password,
                     status = :status,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $updateUser->execute([
                'role_id' => $roleId,
                'full_name' => $fullName,
                'phone' => $phone !== '' ? $phone : null,
                'password' => $password,
                'status' => 'active',
                'id' => $userId,
            ]);

            return;
        }

        $insertUser = $this->pdo->prepare(
            'INSERT INTO users (role_id, full_name, email, phone, password, status)
             VALUES (:role_id, :full_name, :email, :phone, :password, :status)'
        );
        $insertUser->execute([
            'role_id' => $roleId,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'password' => $password,
            'status' => 'active',
        ]);
    }

    private function roleId(string $slug): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $roleId = (int) ($stmt->fetchColumn() ?: 0);

        if ($roleId <= 0) {
            throw new \RuntimeException('Required role is missing: ' . $slug);
        }

        return $roleId;
    }
}