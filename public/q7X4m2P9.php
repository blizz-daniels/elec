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

function tableExists(PDO $pdo, string $table): bool
{
    try {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function tableColumns(PDO $pdo, string $table): array
{
    static $cache = [];
    if (isset($cache[$table])) {
        return $cache[$table];
    }

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
        $cache[$table] = array_map(static fn(array $row): string => (string) $row['Field'], $stmt->fetchAll());
    } catch (Throwable $e) {
        $cache[$table] = [];
    }

    return $cache[$table];
}

function firstMatch(array $columns, array $candidates): ?string
{
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $columns, true)) {
            return $candidate;
        }
    }

    return null;
}

function dynamicInsert(PDO $pdo, string $table, array $payload): int
{
    $columns = tableColumns($pdo, $table);
    if ($columns === []) {
        throw new RuntimeException("Unable to read table {$table}.");
    }

    $fields = [];
    $values = [];
    foreach ($payload as $column => $value) {
        if (in_array($column, $columns, true)) {
            $fields[] = "`{$column}`";
            $values[] = $value;
        }
    }

    if ($fields === []) {
        throw new RuntimeException("No usable columns found for {$table}.");
    }

    $sql = sprintf(
        'INSERT INTO `%s` (%s) VALUES (%s)',
        $table,
        implode(', ', $fields),
        implode(', ', array_fill(0, count($fields), '?'))
    );

    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    return (int) $pdo->lastInsertId();
}

function resolveRoleId(PDO $pdo): ?int
{
    if (!tableExists($pdo, 'roles')) {
        return null;
    }

    $columns = tableColumns($pdo, 'roles');
    $roleColumn = firstMatch($columns, ['slug', 'code', 'name', 'role_name']);
    if ($roleColumn === null) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id FROM roles WHERE {$roleColumn} IN (?, ?, ?) ORDER BY id ASC LIMIT 1");
    $stmt->execute(['polling-marshal', 'polling_marshal', 'marshal']);
    $id = $stmt->fetchColumn();

    return $id !== false ? (int) $id : null;
}

function pollingUnits(PDO $pdo): array
{
    if (!tableExists($pdo, 'polling_units')) {
        return [];
    }

    $cols = tableColumns($pdo, 'polling_units');
    $select = ['id'];
    foreach ([
        'polling_unit_no',
        'polling_unit_number',
        'polling_unit_code',
        'polling_unit_name',
        'ward_id',
        'ward',
        'ward_name',
        'lga_id',
        'lga',
        'lga_name',
        'senatorial_district_id',
        'senatorial_district',
        'senatorial_district_name',
    ] as $candidate) {
        if (in_array($candidate, $cols, true)) {
            $select[] = $candidate;
        }
    }

    $sql = 'SELECT ' . implode(', ', array_map(static fn(string $c): string => "`{$c}`", $select)) . ' FROM polling_units ORDER BY id ASC';
    return $pdo->query($sql)->fetchAll();
}

function rowLabel(array $row): string
{
    $parts = [];
    foreach ([
        'polling_unit_no',
        'polling_unit_number',
        'polling_unit_code',
        'polling_unit_name',
        'ward_name',
        'ward',
        'lga_name',
        'lga',
        'senatorial_district_name',
        'senatorial_district',
    ] as $key) {
        if (!empty($row[$key])) {
            $parts[] = (string) $row[$key];
        }
    }

    return $parts === [] ? 'Polling Unit #' . (string) ($row['id'] ?? '') : implode(' | ', $parts);
}

$errors = [];
$success = false;
$now = date('Y-m-d H:i:s');
$units = pollingUnits($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname = trim((string) ($_POST['surname'] ?? ''));
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $otherName = trim((string) ($_POST['other_name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $stateOfOrigin = trim((string) ($_POST['state_of_origin'] ?? ''));
    $dateOfBirth = trim((string) ($_POST['date_of_birth'] ?? ''));
    $gender = trim((string) ($_POST['gender'] ?? ''));
    $vin = trim((string) ($_POST['vin'] ?? ''));
    $nin = trim((string) ($_POST['nin'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $unitId = (int) ($_POST['polling_unit_id'] ?? 0);
    $fullName = trim($surname . ' ' . $firstName . ' ' . $otherName);

    if ($surname === '') {
        $errors[] = 'Surname is required.';
    }
    if ($firstName === '') {
        $errors[] = 'First name is required.';
    }
    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($stateOfOrigin === '') {
        $errors[] = 'State of origin is required.';
    }
    if ($dateOfBirth === '') {
        $errors[] = 'Date of birth is required.';
    }
    if ($gender === '') {
        $errors[] = 'Sex is required.';
    }
    if ($vin === '') {
        $errors[] = 'VIN is required.';
    }
    if ($nin === '') {
        $errors[] = 'NIN is required.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }
    if ($unitId <= 0) {
        $errors[] = 'Please choose a polling unit.';
    }

    $photoPath = null;
    $photoField = $_FILES['passport'] ?? $_FILES['photo'] ?? null;
    if (is_array($photoField) && ($photoField['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if (($photoField['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors[] = 'Passport upload failed.';
        } else {
            $ext = strtolower(pathinfo((string) ($photoField['name'] ?? ''), PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $errors[] = 'Passport must be JPG, JPEG, PNG, or WEBP.';
            } else {
                $uploadDir = __DIR__ . '/uploads/marshal-photos';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                    $errors[] = 'Unable to create upload folder.';
                } else {
                    $fileName = 'marshal_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    if (!move_uploaded_file((string) ($photoField['tmp_name'] ?? ''), $uploadDir . DIRECTORY_SEPARATOR . $fileName)) {
                        $errors[] = 'Unable to save the uploaded passport.';
                    } else {
                        $photoPath = 'uploads/marshal-photos/' . $fileName;
                    }
                }
            }
        }
    } else {
        $errors[] = 'Passport attachment is required.';
    }

    if ($errors === []) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            $errors[] = 'That email address is already registered.';
        }
    }

    if ($errors === [] && tableExists($pdo, 'members')) {
        $stmt = $pdo->prepare('SELECT id FROM members WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            $errors[] = 'That email address already exists as a member.';
        }
    }

    $selectedUnit = null;
    foreach ($units as $unit) {
        if ((int) $unit['id'] === $unitId) {
            $selectedUnit = $unit;
            break;
        }
    }
    if ($errors === [] && $selectedUnit === null) {
        $errors[] = 'The selected polling unit was not found.';
    }

    if ($errors === []) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $roleId = resolveRoleId($pdo);

        $userId = dynamicInsert($pdo, 'users', [
            'name' => $fullName,
            'full_name' => $fullName,
            'surname' => $surname,
            'first_name' => $firstName,
            'other_name' => $otherName,
            'email' => $email,
            'phone' => $phone,
            'state_of_origin' => $stateOfOrigin,
            'state_of_residence' => 'Ogun Resident',
            'date_of_birth' => $dateOfBirth,
            'gender' => $gender,
            'vin' => $vin,
            'nin' => $nin,
            'password' => $hashed,
            'role_id' => $roleId,
            'status' => 'pending',
            'approved' => 0,
            'is_active' => 1,
            'email_verified_at' => $now,
            'photo' => $photoPath,
            'avatar' => $photoPath,
            'profile_photo' => $photoPath,
            'photo_path' => $photoPath,
            'passport' => $photoPath,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $wardId = null;
        foreach (['ward_id', 'ward'] as $candidate) {
            if (!empty($selectedUnit[$candidate]) && str_contains($candidate, '_id')) {
                $wardId = (int) $selectedUnit[$candidate];
                break;
            }
        }

        dynamicInsert($pdo, 'polling_marshals', [
            'user_id' => $userId,
            'name' => $fullName,
            'full_name' => $fullName,
            'surname' => $surname,
            'first_name' => $firstName,
            'other_name' => $otherName,
            'email' => $email,
            'phone' => $phone,
            'state_of_origin' => $stateOfOrigin,
            'date_of_birth' => $dateOfBirth,
            'gender' => $gender,
            'vin' => $vin,
            'nin' => $nin,
            'polling_unit_id' => $unitId,
            'ward_id' => $wardId,
            'status' => 'active',
            'approved' => 1,
            'is_active' => 1,
            'photo' => $photoPath,
            'avatar' => $photoPath,
            'profile_photo' => $photoPath,
            'photo_path' => $photoPath,
            'passport' => $photoPath,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $success = true;
    }
}

$selectedUnitId = (string) ($_POST['polling_unit_id'] ?? '');
$selectedUnitData = null;
foreach ($units as $unit) {
    if ((string) $unit['id'] === $selectedUnitId) {
        $selectedUnitData = $unit;
        break;
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Polling Marshal Registration</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6f8fb; color: #1f2937; }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 32px 20px 60px; }
        .card { background: #fff; border-radius: 18px; box-shadow: 0 8px 30px rgba(0,0,0,.08); padding: 24px; }
        .layout { display: grid; grid-template-columns: 1.2fr .8fr; gap: 18px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
        label { font-weight: 700; font-size: 14px; }
        input, select { width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 15px; background: #fff; }
        input:focus, select:focus { outline: none; border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
        .actions { margin-top: 18px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        button { border: none; border-radius: 12px; background: #16a34a; color: #fff; padding: 13px 18px; font-weight: 700; cursor: pointer; }
        button:hover { background: #15803d; }
        .alert { padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; }
        .alert-error { background: #fef2f2; color: #991b1b; }
        .alert-success { background: #ecfdf5; color: #166534; }
        .muted { color: #6b7280; font-size: 14px; }
        .small { font-size: 13px; color: #6b7280; }
        .code { font-family: Consolas, monospace; background: #f3f4f6; padding: 4px 8px; border-radius: 8px; }
        .summary { border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 14px; padding: 16px; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px 14px; }
        .summary-item .label { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
        .summary-item .value { font-weight: 700; font-size: 14px; word-break: break-word; }
        .search-wrap { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .search-tools { display: flex; flex-direction: column; gap: 6px; }
        .list-select { min-height: 340px; }
        .welcome { display: flex; flex-direction: column; gap: 10px; }
        .welcome h1 { margin: 0; font-size: 32px; line-height: 1.15; }
        .welcome p { margin: 0; color: #4b5563; }
        @media (max-width: 980px) {
            .layout, .grid, .summary-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:18px;">
            <div>
                <div class="code">q7X4m2P9.php</div>
            </div>
        </div>

        <?php if ($errors !== []): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= h($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">Marshal registration completed successfully.</div>
        <?php endif; ?>

        <div class="layout">
            <div>
                <div class="welcome" style="margin-bottom: 18px;">
                    <h1>Welcome polling marshals, input your details.</h1>
                    <p>Complete the registration form below and choose the correct polling unit from the searchable list.</p>
                </div>

                <form method="post" enctype="multipart/form-data">
                    <div class="grid">
                        <div class="field">
                            <label for="surname">Surname</label>
                            <input id="surname" name="surname" placeholder="Surname" required value="<?= h($_POST['surname'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="first_name">First Name</label>
                            <input id="first_name" name="first_name" placeholder="First Name" required value="<?= h($_POST['first_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="other_name">Other Name</label>
                            <input id="other_name" name="other_name" placeholder="Other Name" value="<?= h($_POST['other_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="phone">Mobile No</label>
                            <input id="phone" name="phone" placeholder="Mobile No" required value="<?= h($_POST['phone'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="email">Email Address</label>
                            <input id="email" name="email" type="email" placeholder="Email Address" required value="<?= h($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="state_of_origin">State of Origin</label>
                            <input id="state_of_origin" name="state_of_origin" placeholder="State of Origin" required value="<?= h($_POST['state_of_origin'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="state_of_residence">State of Residence</label>
                            <input id="state_of_residence" name="state_of_residence" value="Ogun Resident" readonly>
                        </div>
                        <div class="field">
                            <label for="date_of_birth">Date of Birth</label>
                            <input id="date_of_birth" name="date_of_birth" type="date" required value="<?= h($_POST['date_of_birth'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="gender">Sex</label>
                            <select id="gender" name="gender" required>
                                <option value="">Sex</option>
                                <option value="male" <?= (string) ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?= (string) ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?= (string) ($_POST['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="vin">Voter Identification Number (VIN)</label>
                            <input id="vin" name="vin" placeholder="Voter Identification Number (VIN)" required value="<?= h($_POST['vin'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="nin">NIN</label>
                            <input id="nin" name="nin" placeholder="NIN" required value="<?= h($_POST['nin'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="field search-wrap">
                        <label for="polling_search">Voting Polling Unit</label>
                        <div class="search-tools">
                            <input id="polling_search" type="text" placeholder="Search by polling unit no, code, ward, LGA, district, or unit name">
                            <div class="small">Search by any part of the unit number, code, ward, LGA, district, or unit name.</div>
                        </div>
                    </div>

                    <div class="field">
                        <select id="polling_unit_id" name="polling_unit_id" class="list-select" required>
                            <option value="">Select polling unit</option>
                            <?php foreach ($units as $unit): ?>
                                <?php $label = rowLabel($unit); ?>
                                <option
                                    value="<?= (int) $unit['id'] ?>"
                                    data-search="<?= h(strtolower($label)) ?>"
                                    data-search-polling-no="<?= h((string) ($unit['polling_unit_no'] ?? $unit['polling_unit_number'] ?? '')) ?>"
                                    data-search-polling-code="<?= h((string) ($unit['polling_unit_code'] ?? '')) ?>"
                                    data-search-name="<?= h((string) ($unit['polling_unit_name'] ?? '')) ?>"
                                    data-search-ward="<?= h((string) ($unit['ward_name'] ?? $unit['ward'] ?? '')) ?>"
                                    data-search-lga="<?= h((string) ($unit['lga_name'] ?? $unit['lga'] ?? '')) ?>"
                                    data-search-district="<?= h((string) ($unit['senatorial_district_name'] ?? $unit['senatorial_district'] ?? '')) ?>"
                                    <?= $selectedUnitId === (string) $unit['id'] ? 'selected' : '' ?>
                                >
                                    <?= h($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="summary" id="pollingUnitSummary">
                        <div class="summary-grid">
                            <div class="summary-item"><div class="label">Polling Unit No</div><div class="value" data-summary-field="polling_no">-</div></div>
                            <div class="summary-item"><div class="label">Polling Unit Code</div><div class="value" data-summary-field="polling_code">-</div></div>
                            <div class="summary-item"><div class="label">Polling Unit</div><div class="value" data-summary-field="name">-</div></div>
                            <div class="summary-item"><div class="label">Ward</div><div class="value" data-summary-field="ward">-</div></div>
                            <div class="summary-item"><div class="label">LGA</div><div class="value" data-summary-field="lga">-</div></div>
                            <div class="summary-item"><div class="label">Senatorial District</div><div class="value" data-summary-field="district">-</div></div>
                        </div>
                    </div>

                    <div class="grid" style="margin-top: 16px;">
                        <div class="field">
                            <label for="password">Password</label>
                            <input id="password" name="password" type="password" placeholder="Password" required>
                        </div>
                        <div class="field">
                            <label for="confirm_password">Confirm Password</label>
                            <input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm Password" required>
                        </div>
                        <div class="field" style="grid-column: 1 / -1;">
                            <label for="passport">Passport Attachment (jpg, jpeg, png, max 5MB)</label>
                            <input id="passport" name="passport" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png,image/webp" required>
                        </div>
                    </div>

                    <div class="field" style="margin-top: 8px;">
                        <label style="display:flex;gap:10px;align-items:center;font-weight:600;">
                            <input type="checkbox" required style="width:auto;"> I agree to the terms
                        </label>
                    </div>

                    <div class="actions">
                        <button type="submit">Create Marshal Account</button>
                        <span class="small">This shared link sends marshals straight into the polling marshal registration flow.</span>
                    </div>
                </form>
            </div>

            <div>
                <div class="card" style="padding:18px;box-shadow:none;border:1px solid #e5e7eb;">
                    <h3 style="margin-top:0;">Marshal Account Card</h3>
                    <p class="muted">When the marshal completes this form, the account is created and tied to the selected polling unit.</p>
                    <p class="muted">Use the searchable polling-unit box to quickly narrow the 5000+ entries by number, code, ward, LGA, or district.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const search = document.getElementById('polling_search');
    const select = document.getElementById('polling_unit_id');
    const summary = document.getElementById('pollingUnitSummary');
    if (!search || !select || !summary) {
        return;
    }

    const initialSelected = <?= json_encode($selectedUnitId) ?>;
    let selectedId = initialSelected;
    const summaryFields = summary.querySelectorAll('[data-summary-field]');
    const allOptions = Array.from(select.options).slice(1).map((option) => ({
        value: option.value,
        text: option.textContent || '',
        search: option.getAttribute('data-search') || '',
        pollingNo: option.getAttribute('data-search-polling-no') || '',
        pollingCode: option.getAttribute('data-search-polling-code') || '',
        name: option.getAttribute('data-search-name') || '',
        ward: option.getAttribute('data-search-ward') || '',
        lga: option.getAttribute('data-search-lga') || '',
        district: option.getAttribute('data-search-district') || ''
    }));

    const updateSummary = () => {
        const selected = select.selectedOptions[0];
        const values = selected ? {
            polling_no: selected.dataset.searchPollingNo || '',
            polling_code: selected.dataset.searchPollingCode || '',
            name: selected.dataset.searchName || '',
            ward: selected.dataset.searchWard || '',
            lga: selected.dataset.searchLga || '',
            district: selected.dataset.searchDistrict || '',
        } : {
            polling_no: '', polling_code: '', name: '', ward: '', lga: '', district: ''
        };

        summaryFields.forEach((field) => {
            const key = field.dataset.summaryField || '';
            field.textContent = values[key] || '-';
        });
    };

    const render = () => {
        const query = search.value.trim().toLowerCase();
        select.innerHTML = '<option value="">Select polling unit</option>';

        allOptions.forEach((item) => {
            if (!query || item.search.includes(query)) {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.text;
                option.setAttribute('data-search', item.search);
                option.setAttribute('data-search-polling-no', item.pollingNo);
                option.setAttribute('data-search-polling-code', item.pollingCode);
                option.setAttribute('data-search-name', item.name);
                option.setAttribute('data-search-ward', item.ward);
                option.setAttribute('data-search-lga', item.lga);
                option.setAttribute('data-search-district', item.district);
                if (item.value === selectedId) {
                    option.selected = true;
                }
                select.appendChild(option);
            }
        });

        updateSummary();
    };

    search.addEventListener('input', render);
    select.addEventListener('change', () => {
        selectedId = select.value;
        updateSummary();
    });

    render();
})();
</script>
</body>
</html>