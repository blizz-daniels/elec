<?php

declare(strict_types=1);

namespace App\Models;

final class Member extends BaseModel
{
    protected string $table = 'members';

    public function nextMembershipNumber(): string
    {
        $stmt = $this->pdo->query("SELECT membership_number FROM members ORDER BY id DESC LIMIT 1");
        $last = $stmt?->fetchColumn();
        $number = (int) preg_replace('/\D+/', '', (string) $last);

        return sprintf('OGN%06d', $number + 1);
    }
}
