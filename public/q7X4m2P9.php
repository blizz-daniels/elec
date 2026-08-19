<?php

declare(strict_types=1);

session_start();

$config = require __DIR__ . '/../config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $config['DB_HOST'] ?? 'localhost',
    $config['DB_PORT'] ?? '3306',
    $config['DB_DATABASE'] ?? ''
);

$pdo = new PDO(
    $dsn,
    $config['DB_USERNAME'] ?? '',
    $config['DB_PASSWORD'] ?? '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function normalizeIdentifier(?string $value): string
{
    $value = strtoupper(trim((string) $value));

    return preg_replace('/[^A-Z0-9]/', '', $value) ?? '';
}

function roleId(PDO $pdo, string $slug): int
{
    $stmt = $pdo->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    $roleId = (int) ($stmt->fetchColumn() ?: 0);

    if ($roleId <= 0) {
        throw new RuntimeException('Required role is missing: ' . $slug);
    }

    return $roleId;
}

function loadMemberByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare(
        'SELECT members.*, 
                polling_units.polling_name,
                polling_units.polling_code,
                polling_units.senatorial_district_id,
                polling_units.lga_id,
                polling_units.ward_id,
                senatorial_districts.name AS district_name,
                lgas.name AS lga_name,
                wards.name AS ward_name
         FROM members
         LEFT JOIN polling_units ON polling_units.id = members.polling_unit_id
         LEFT JOIN senatorial_districts ON senatorial_districts.id = polling_units.senatorial_district_id
         LEFT JOIN lgas ON lgas.id = polling_units.lga_id
         LEFT JOIN wards ON wards.id = polling_units.ward_id
         WHERE members.email = :email
         LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $member = $stmt->fetch();

    return $member ?: null;
}

function syncMarshalAccount(PDO $pdo, array $member, int $roleId): int
{
    $fullName = trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? ''));
    $email = trim((string) ($member['email'] ?? ''));
    $phone = trim((string) ($member['phone'] ?? ''));
    $password = (string) ($member['password'] ?? '');
    $passportPath = trim((string) ($member['passport_path'] ?? ''));

    if ($fullName === '' || $email === '' || $password === '') {
        throw new RuntimeException('Membership record is incomplete.');
    }

    $userStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $userStmt->execute(['email' => $email]);
    $userId = (int) ($userStmt->fetchColumn() ?: 0);

    if ($userId > 0) {
        $update = $pdo->prepare(
            'UPDATE users
             SET role_id = :role_id,
                 full_name = :full_name,
                 phone = :phone,
                 password = :password,
                 avatar_path = :avatar_path,
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $update->execute([
            'role_id' => $roleId,
            'full_name' => $fullName,
            'phone' => $phone !== '' ? $phone : null,
            'password' => $password,
            'avatar_path' => $passportPath !== '' ? $passportPath : null,
            'status' => 'active',
            'id' => $userId,
        ]);

        return $userId;
    }

    $insert = $pdo->prepare(
        'INSERT INTO users (role_id, full_name, email, phone, password, avatar_path, status)
         VALUES (:role_id, :full_name, :email, :phone, :password, :avatar_path, :status)'
    );
    $insert->execute([
        'role_id' => $roleId,
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone !== '' ? $phone : null,
        'password' => $password,
        'avatar_path' => $passportPath !== '' ? $passportPath : null,
        'status' => 'active',
    ]);

    return (int) $pdo->lastInsertId();
}

function syncPollingMarshal(PDO $pdo, array $member, int $userId): void
{
    $pollingUnitId = (int) ($member['polling_unit_id'] ?? 0);
    $wardId = (int) ($member['ward_id'] ?? 0);
    $phone = trim((string) ($member['phone'] ?? ''));
    $email = trim((string) ($member['email'] ?? ''));
    $passportPath = trim((string) ($member['passport_path'] ?? ''));

    if ($pollingUnitId <= 0 || $wardId <= 0) {
        throw new RuntimeException('Your member profile is missing polling unit or ward details.');
    }

    $marshalStmt = $pdo->prepare('SELECT id FROM polling_marshals WHERE user_id = :user_id LIMIT 1');
    $marshalStmt->execute(['user_id' => $userId]);
    $marshalId = (int) ($marshalStmt->fetchColumn() ?: 0);

    if ($marshalId > 0) {
        $update = $pdo->prepare(
            'UPDATE polling_marshals
             SET polling_unit_id = :polling_unit_id,
                 ward_id = :ward_id,
                 photo_path = :photo_path,
                 phone = :phone,
                 email = :email,
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $update->execute([
            'polling_unit_id' => $pollingUnitId,
            'ward_id' => $wardId,
            'photo_path' => $passportPath !== '' ? $passportPath : null,
            'phone' => $phone !== '' ? $phone : null,
            'email' => $email,
            'status' => 'active',
            'id' => $marshalId,
        ]);

        return;
    }

    $insert = $pdo->prepare(
        'INSERT INTO polling_marshals (user_id, polling_unit_id, ward_id, photo_path, phone, email, status)
         VALUES (:user_id, :polling_unit_id, :ward_id, :photo_path, :phone, :email, :status)'
    );
    $insert->execute([
        'user_id' => $userId,
        'polling_unit_id' => $pollingUnitId,
        'ward_id' => $wardId,
        'photo_path' => $passportPath !== '' ? $passportPath : null,
        'phone' => $phone !== '' ? $phone : null,
        'email' => $email,
        'status' => 'active',
    ]);
}

$errors = [];
$success = false;
$memberName = '';
$memberPollingUnit = '';
$memberWard = '';
$memberLga = '';
$memberDistrict = '';
$memberMembershipNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $nin = trim((string) ($_POST['nin'] ?? ''));
    $vin = trim((string) ($_POST['vin'] ?? ''));

    if ($email === '' || $password === '' || $nin === '' || $vin === '') {
        $errors[] = 'Email, password, NIN, and VIN are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($errors === []) {
        try {
            $member = loadMemberByEmail($pdo, $email);
            if (!$member) {
                throw new RuntimeException('No approved membership record was found for this email address.');
            }

            $memberStatus = strtolower(trim((string) ($member['status'] ?? '')));
            if (!in_array($memberStatus, ['approved', 'active'], true)) {
                throw new RuntimeException('This membership has not been approved yet.');
            }

            $memberPassword = (string) ($member['password'] ?? '');
            if ($memberPassword === '' || !password_verify($password, $memberPassword)) {
                throw new RuntimeException('The email or password does not match the membership record.');
            }

            if (normalizeIdentifier($nin) !== normalizeIdentifier((string) ($member['nin'] ?? ''))) {
                throw new RuntimeException('The NIN does not match the membership record.');
            }

            if (normalizeIdentifier($vin) !== normalizeIdentifier((string) ($member['vin'] ?? ''))) {
                throw new RuntimeException('The VIN does not match the membership record.');
            }

            $roleId = roleId($pdo, 'polling-marshal');
            $userId = syncMarshalAccount($pdo, $member, $roleId);
            syncPollingMarshal($pdo, $member, $userId);

            $memberName = trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? ''));
            $memberPollingUnit = trim((string) ($member['polling_name'] ?? ''));
            $memberWard = trim((string) ($member['ward_name'] ?? ''));
            $memberLga = trim((string) ($member['lga_name'] ?? ''));
            $memberDistrict = trim((string) ($member['district_name'] ?? ''));
            $memberMembershipNumber = trim((string) ($member['membership_number'] ?? ''));
            $success = true;
        } catch (Throwable $exception) {
            $errors[] = 'Marshal registration failed: ' . $exception->getMessage();
        }
    }
}

$oldEmail = htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$oldNin = htmlspecialchars((string) ($_POST['nin'] ?? ''), ENT_QUOTES, 'UTF-8');
$oldVin = htmlspecialchars((string) ($_POST['vin'] ?? ''), ENT_QUOTES, 'UTF-8');

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Polling Marshal Invite</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: linear-gradient(180deg, #f4f7fb 0%, #eef3f8 100%); color: #1f2937; }
        .wrap { max-width: 980px; margin: 0 auto; padding: 32px 20px 60px; }
        .shell { background: #fff; border-radius: 20px; box-shadow: 0 12px 35px rgba(0,0,0,.08); overflow: hidden; }
        .hero { padding: 28px 28px 20px; background: linear-gradient(135deg, #0f766e 0%, #15803d 100%); color: #fff; }
        .hero h1 { margin: 0 0 10px; font-size: 32px; line-height: 1.1; }
        .hero p { margin: 0; max-width: 680px; color: rgba(255,255,255,.9); }
        .body { padding: 28px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        label { font-weight: 700; font-size: 14px; }
        input { width: 100%; box-sizing: border-box; padding: 13px 14px; border: 1px solid #d1d5db; border-radius: 12px; font-size: 15px; background: #fff; }
        input:focus { outline: none; border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
        .actions { margin-top: 18px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        button { border: none; border-radius: 12px; background: #16a34a; color: #fff; padding: 13px 18px; font-weight: 700; cursor: pointer; }
        button:hover { background: #15803d; }
        .alert { padding: 12px 14px; border-radius: 12px; margin-bottom: 16px; }
        .alert-error { background: #fef2f2; color: #991b1b; }
        .alert-success { background: #ecfdf5; color: #166534; }
        .note { color: #6b7280; font-size: 14px; }
        .summary { margin-top: 18px; border: 1px solid #e5e7eb; border-radius: 16px; background: #f9fafb; padding: 18px; }
        .summary-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 16px; }
        .summary-item .label { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
        .summary-item .value { font-size: 14px; font-weight: 700; word-break: break-word; }
        .footer { margin-top: 18px; color: #6b7280; font-size: 13px; }
        .meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
        .meta .card { border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px; background: #fff; }
        .meta .label { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
        .meta .value { font-weight: 700; }
        @media (max-width: 720px) { .grid, .summary-grid, .meta { grid-template-columns: 1fr; } .hero h1 { font-size: 26px; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="shell">
        <div class="hero">
            <h1>Welcome polling marshals, input your details.</h1>
            <p>This invite verifies your existing membership record. Use the same email, password, NIN, and VIN already attached to your member profile.</p>
        </div>

        <div class="body">
            <?php if ($errors !== []): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?= h($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Marshal registration completed successfully.
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="grid">
                    <div class="field">
                        <label for="email">Email Address</label>
                        <input id="email" name="email" type="email" placeholder="Email Address" required value="<?= $oldEmail; ?>">
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" placeholder="Password" required>
                    </div>
                    <div class="field">
                        <label for="nin">NIN</label>
                        <input id="nin" name="nin" type="text" placeholder="NIN" required value="<?= $oldNin; ?>">
                    </div>
                    <div class="field">
                        <label for="vin">VIN</label>
                        <input id="vin" name="vin" type="text" placeholder="VIN" required value="<?= $oldVin; ?>">
                    </div>
                </div>

                <div class="actions">
                    <button type="submit">Verify and Register Marshal</button>
                    <div class="note">The invite uses the polling unit already stored in your approved member profile.</div>
                </div>
            </form>

            <?php if ($success): ?>
                <div class="summary">
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="label">Member Name</div>
                            <div class="value"><?= h($memberName !== '' ? $memberName : '-'); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Membership Number</div>
                            <div class="value"><?= h($memberMembershipNumber !== '' ? $memberMembershipNumber : '-'); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Polling Unit</div>
                            <div class="value"><?= h($memberPollingUnit !== '' ? $memberPollingUnit : '-'); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Ward</div>
                            <div class="value"><?= h($memberWard !== '' ? $memberWard : '-'); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="label">LGA</div>
                            <div class="value"><?= h($memberLga !== '' ? $memberLga : '-'); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Senatorial District</div>
                            <div class="value"><?= h($memberDistrict !== '' ? $memberDistrict : '-'); ?></div>
                        </div>
                    </div>
                    <div class="footer">You can now sign in with the same email and password. Your account has been promoted to polling marshal.</div>
                </div>
            <?php else: ?>
                <div class="meta">
                    <div class="card">
                        <div class="label">What this does</div>
                        <div class="value">Checks your membership details and upgrades the account to marshal.</div>
                    </div>
                    <div class="card">
                        <div class="label">What it uses</div>
                        <div class="value">Your existing member record, not a new full registration form.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>