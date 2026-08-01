<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Config;
use App\Support\Database;
use App\Support\Request;
use App\Support\Csrf;

final class AdminModuleController extends Controller
{
    public function members(Request $request): void
    {
        Auth::requiresRole(['super-admin', 'state-executive', 'lga-executive', 'ward-executive']);
        $pdo = Database::pdo();

        if ($request->method() === 'POST') {
            $this->handleMemberPost($request, $pdo);
        }

        $status = trim((string) $request->input('status', ''));
        $lgaId = (int) $request->input('lga_id', 0);

        $sql = 'SELECT members.*, lgas.name AS lga_name, wards.name AS ward_name, polling_units.polling_name,
                       EXISTS(
                           SELECT 1
                           FROM users
                           INNER JOIN polling_marshals ON polling_marshals.user_id = users.id
                           WHERE users.email = members.email
                       ) AS is_marshal
                FROM members
                LEFT JOIN lgas ON lgas.id = members.lga_id
                LEFT JOIN wards ON wards.id = members.ward_id
                LEFT JOIN polling_units ON polling_units.id = members.polling_unit_id
                WHERE 1=1';
        $params = [];

        if ($status !== '') {
            $sql .= ' AND members.status = :status';
            $params['status'] = $status;
        }

        if ($lgaId > 0) {
            $sql .= ' AND members.lga_id = :lga_id';
            $params['lga_id'] = $lgaId;
        }

        $sql .= ' ORDER BY members.id DESC LIMIT 200';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $this->view('admin/members', [
            'title' => 'Members',
            'members' => $stmt->fetchAll(),
            'lgas' => $pdo->query('SELECT id, name FROM lgas ORDER BY name')->fetchAll(),
            'pollingUnits' => $pdo->query('SELECT id, polling_name, ward_id FROM polling_units ORDER BY polling_name')->fetchAll(),
            'status' => $status,
            'selectedLgaId' => $lgaId,
        ]);
    }

    public function geography(Request $request): void
    {
        Auth::requiresRole(['super-admin', 'state-executive']);
        $pdo = Database::pdo();

        if ($request->method() === 'POST') {
            $this->handleGeographyPost($request, $pdo);
        }

        $this->view('admin/geography', [
            'title' => 'Geography',
            'districts' => $pdo->query('SELECT * FROM senatorial_districts ORDER BY id')->fetchAll(),
            'lgas' => $pdo->query(
                'SELECT lgas.*, senatorial_districts.name AS district_name
                 FROM lgas
                 INNER JOIN senatorial_districts ON senatorial_districts.id = lgas.senatorial_district_id
                 ORDER BY lgas.id'
            )->fetchAll(),
            'wards' => $pdo->query(
                'SELECT wards.*, lgas.name AS lga_name
                 FROM wards
                 INNER JOIN lgas ON lgas.id = wards.lga_id
                 ORDER BY wards.id'
            )->fetchAll(),
            'pollingUnits' => $pdo->query(
                'SELECT polling_units.*, senatorial_districts.name AS district_name, lgas.name AS lga_name, wards.name AS ward_name
                 FROM polling_units
                 INNER JOIN senatorial_districts ON senatorial_districts.id = polling_units.senatorial_district_id
                 INNER JOIN lgas ON lgas.id = polling_units.lga_id
                 INNER JOIN wards ON wards.id = polling_units.ward_id
                 ORDER BY polling_units.id DESC'
            )->fetchAll(),
        ]);
    }

    public function pollingUnits(Request $request): void
    {
        Auth::requiresRole(['super-admin', 'state-executive', 'lga-executive']);
        $pdo = Database::pdo();

        if ($request->method() === 'POST') {
            $this->handlePollingUnitPost($request, $pdo);
        }

        $this->view('admin/polling-units', [
            'title' => 'Polling Units',
            'pollingUnits' => $pdo->query(
                'SELECT polling_units.*, senatorial_districts.name AS district_name, lgas.name AS lga_name, wards.name AS ward_name
                 FROM polling_units
                 INNER JOIN senatorial_districts ON senatorial_districts.id = polling_units.senatorial_district_id
                 INNER JOIN lgas ON lgas.id = polling_units.lga_id
                 INNER JOIN wards ON wards.id = polling_units.ward_id
                 ORDER BY polling_units.id DESC'
            )->fetchAll(),
            'districts' => $pdo->query('SELECT id, name FROM senatorial_districts ORDER BY name')->fetchAll(),
            'lgas' => $pdo->query('SELECT id, name FROM lgas ORDER BY name')->fetchAll(),
            'wards' => $pdo->query('SELECT id, name FROM wards ORDER BY name')->fetchAll(),
        ]);
    }

    public function executives(Request $request): void
    {
        Auth::requiresRole(['super-admin', 'state-executive']);
        $pdo = Database::pdo();

        $this->view('admin/executives', [
            'title' => 'Executives',
            'stateExecutives' => $pdo->query(
                'SELECT state_executives.*, users.full_name AS user_name
                 FROM state_executives
                 LEFT JOIN users ON users.id = state_executives.user_id
                 ORDER BY state_executives.id DESC'
            )->fetchAll(),
            'senatorialExecutives' => $pdo->query(
                'SELECT senatorial_executives.*, senatorial_districts.name AS district_name, users.full_name AS user_name
                 FROM senatorial_executives
                 INNER JOIN senatorial_districts ON senatorial_districts.id = senatorial_executives.senatorial_district_id
                 LEFT JOIN users ON users.id = senatorial_executives.user_id
                 ORDER BY senatorial_executives.id DESC'
            )->fetchAll(),
            'lgaExecutives' => $pdo->query(
                'SELECT lga_executives.*, lgas.name AS lga_name, users.full_name AS user_name
                 FROM lga_executives
                 INNER JOIN lgas ON lgas.id = lga_executives.lga_id
                 LEFT JOIN users ON users.id = lga_executives.user_id
                 ORDER BY lga_executives.id DESC'
            )->fetchAll(),
            'wardExecutives' => $pdo->query(
                'SELECT ward_executives.*, wards.name AS ward_name, users.full_name AS user_name
                 FROM ward_executives
                 INNER JOIN wards ON wards.id = ward_executives.ward_id
                 LEFT JOIN users ON users.id = ward_executives.user_id
                 ORDER BY ward_executives.id DESC'
            )->fetchAll(),
        ]);
    }

    public function marshals(Request $request): void
    {
        Auth::requiresRole(['super-admin', 'state-executive', 'lga-executive']);
        $pdo = Database::pdo();

        $this->view('admin/marshals', [
            'title' => 'Marshals',
            'marshals' => $pdo->query(
                'SELECT polling_marshals.*, polling_units.polling_name, wards.name AS ward_name, lgas.name AS lga_name, users.full_name AS user_name
                 FROM polling_marshals
                 INNER JOIN polling_units ON polling_units.id = polling_marshals.polling_unit_id
                 INNER JOIN wards ON wards.id = polling_marshals.ward_id
                 INNER JOIN lgas ON lgas.id = wards.lga_id
                 LEFT JOIN users ON users.id = polling_marshals.user_id
                 ORDER BY polling_marshals.id DESC'
            )->fetchAll(),
        ]);
    }

    public function candidates(Request $request): void
    {
        Auth::requiresRole(['super-admin', 'state-executive']);
        $pdo = Database::pdo();

        $this->view('admin/candidates', [
            'title' => 'Candidates',
            'candidates' => $pdo->query(
                'SELECT candidates.*, elections.name AS election_name
                 FROM candidates
                 INNER JOIN elections ON elections.id = candidates.election_id
                 ORDER BY candidates.id DESC'
            )->fetchAll(),
        ]);
    }

    public function auditLogs(Request $request): void
    {
        Auth::requiresRole(['super-admin']);
        $this->renderModule('Audit Logs', 'Login, edit, approval, and submission history.');
    }

    public function profile(Request $request): void
    {
        Auth::requiresLogin();
        $pdo = Database::pdo();
        $currentUser = Auth::user();
        $userId = (int) ($currentUser['id'] ?? 0);
        $email = trim((string) ($currentUser['email'] ?? ''));

        $stmt = $pdo->prepare(
            'SELECT users.*, roles.name AS role_name, roles.slug AS role_slug,
                    members.passport_path AS passport_path,
                    members.membership_number AS membership_number,
                    members.surname AS member_surname,
                    members.first_name AS member_first_name,
                    members.other_name AS member_other_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             LEFT JOIN members ON members.email = users.email
             WHERE users.id = :id OR users.email = :email
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $userId,
            'email' => $email,
        ]);

        $user = $stmt->fetch() ?: $currentUser;
        $photoPath = trim((string) ($user['passport_path'] ?? $user['avatar_path'] ?? ''));
        $photoUrl = $photoPath !== '' ? url($photoPath) : '';

        $this->view('admin/profile', [
            'title' => 'Profile',
            'user' => $user,
            'photoPath' => $photoPath,
            'photoUrl' => $photoUrl,
        ]);
    }

    private function handleMemberPost(Request $request, \PDO $pdo): void
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid security token.');
            redirect('/admin/members');
        }

        $action = (string) $request->input('action', '');
        $memberId = (int) $request->input('member_id', 0);
        if ($memberId <= 0) {
            flash('error', 'A valid member is required.');
            redirect('/admin/members');
        }

        try {
            if ($action === 'promote_marshal') {
                $pollingUnitId = (int) $request->input('polling_unit_id', 0);
                if ($pollingUnitId <= 0) {
                    throw new \RuntimeException('Select a polling unit before promoting the member.');
                }

                $pdo->beginTransaction();
                $this->promoteMemberToMarshal($pdo, $memberId, $pollingUnitId);
                $pdo->commit();

                flash('success', 'Member promoted to polling marshal.');
                redirect('/admin/members');
            }

            if ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM members WHERE id = :id');
                $stmt->execute(['id' => $memberId]);
                flash('success', 'Member deleted.');
                redirect('/admin/members');
            }

            $status = $action === 'reject' ? 'rejected' : 'approved';
            $stmt = $pdo->prepare('UPDATE members SET status = :status, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'status' => $status,
                'id' => $memberId,
            ]);


            if ($status === 'approved') {
                $this->syncMemberUserAccount($pdo, $memberId, 'registered-member', 'active');
            } else {
                $this->syncMemberUserAccount($pdo, $memberId, 'registered-member', 'inactive', false);
            }

            try {
                (new \App\Services\MemberCardService())->issueForMember($memberId);
            } catch (\Throwable $cardError) {
                flash('error', 'Member updated, but the membership card could not be generated yet.');
            }

            flash('success', 'Member updated.');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash('error', 'Could not update the member record.');
        }

        redirect('/admin/members');
    }

    private function promoteMemberToMarshal(\PDO $pdo, int $memberId, int $pollingUnitId): void
    {
        $memberStmt = $pdo->prepare('SELECT * FROM members WHERE id = :id LIMIT 1');
        $memberStmt->execute(['id' => $memberId]);
        $member = $memberStmt->fetch();
        if (!$member) {
            throw new \RuntimeException('Member record not found.');
        }

        $pollingUnitStmt = $pdo->prepare('SELECT * FROM polling_units WHERE id = :id LIMIT 1');
        $pollingUnitStmt->execute(['id' => $pollingUnitId]);
        $pollingUnit = $pollingUnitStmt->fetch();
        if (!$pollingUnit) {
            throw new \RuntimeException('Polling unit not found.');
        }

        $roleId = $this->roleId($pdo, 'polling-marshal');
        $fullName = trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? ''));
        $email = trim((string) ($member['email'] ?? ''));
        $phone = trim((string) ($member['phone'] ?? ''));
        $password = (string) ($member['password'] ?? '');
        $wardId = (int) ($member['ward_id'] ?? 0);
        if ($wardId <= 0) {
            $wardId = (int) ($pollingUnit['ward_id'] ?? 0);
        }

        if ($fullName === '' || $email === '' || $password === '') {
            throw new \RuntimeException('Member data is incomplete.');
        }
        if ($wardId <= 0) {
            throw new \RuntimeException('Unable to determine the ward for this marshal.');
        }

        $userId = $this->syncMemberUserAccount($pdo, $memberId, 'polling-marshal', 'active');

        $marshalStmt = $pdo->prepare('SELECT id FROM polling_marshals WHERE user_id = :user_id LIMIT 1');
        $marshalStmt->execute(['user_id' => $userId]);
        $marshalId = (int) ($marshalStmt->fetchColumn() ?: 0);

        if ($marshalId > 0) {
            $updateMarshal = $pdo->prepare(
                'UPDATE polling_marshals
                 SET polling_unit_id = :polling_unit_id,
                     ward_id = :ward_id,
                     phone = :phone,
                     email = :email,
                     status = :status,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $updateMarshal->execute([
                'polling_unit_id' => $pollingUnitId,
                'ward_id' => $wardId,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email,
                'status' => 'active',
                'id' => $marshalId,
            ]);
        } else {
            $insertMarshal = $pdo->prepare(
                'INSERT INTO polling_marshals (user_id, polling_unit_id, ward_id, phone, email, status)
                 VALUES (:user_id, :polling_unit_id, :ward_id, :phone, :email, :status)'
            );
            $insertMarshal->execute([
                'user_id' => $userId,
                'polling_unit_id' => $pollingUnitId,
                'ward_id' => $wardId,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email,
                'status' => 'active',
            ]);
        }

        $updateMember = $pdo->prepare('UPDATE members SET status = :status, updated_at = NOW() WHERE id = :id');
        $updateMember->execute([
            'status' => 'approved',
            'id' => $memberId,
        ]);
        try {
            (new \App\Services\MemberCardService())->issueForMember($memberId);
        } catch (\Throwable $cardError) {
            flash('error', 'Member promoted, but the membership card could not be generated yet.');
        }

    }

    private function syncMemberUserAccount(\PDO $pdo, int $memberId, string $roleSlug, string $status = 'active', bool $createIfMissing = true): int
    {
        $memberStmt = $pdo->prepare('SELECT * FROM members WHERE id = :id LIMIT 1');
        $memberStmt->execute(['id' => $memberId]);
        $member = $memberStmt->fetch();
        if (!$member) {
            throw new \RuntimeException('Member record not found.');
        }

        $roleId = $this->roleId($pdo, $roleSlug);
        $fullName = trim((string) ($member['surname'] ?? '') . ' ' . (string) ($member['first_name'] ?? '') . ' ' . (string) ($member['other_name'] ?? ''));
        $email = trim((string) ($member['email'] ?? ''));
        $phone = trim((string) ($member['phone'] ?? ''));
        $password = (string) ($member['password'] ?? '');

        if ($fullName === '' || $email === '' || $password === '') {
            throw new \RuntimeException('Member data is incomplete.');
        }

        $userStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $userStmt->execute(['email' => $email]);
        $userId = (int) ($userStmt->fetchColumn() ?: 0);

        if ($userId > 0) {
            $updateUser = $pdo->prepare(
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
                'status' => $status,
                'id' => $userId,
            ]);

            return $userId;
        }

        if (!$createIfMissing) {
            return 0;
        }

        $insertUser = $pdo->prepare(
            'INSERT INTO users (role_id, full_name, email, phone, password, status)
             VALUES (:role_id, :full_name, :email, :phone, :password, :status)'
        );
        $insertUser->execute([
            'role_id' => $roleId,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'password' => $password,
            'status' => $status,
        ]);

        return (int) $pdo->lastInsertId();
    }

    private function roleId(\PDO $pdo, string $slug): int
    {
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $roleId = (int) ($stmt->fetchColumn() ?: 0);

        if ($roleId <= 0) {
            throw new \RuntimeException('Required role is missing: ' . $slug);
        }

        return $roleId;
    }

    private function handleGeographyPost(Request $request, \PDO $pdo): void
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid security token.');
            redirect('/admin/geography');
        }

        $action = (string) $request->input('action', '');

        try {
            switch ($action) {
                case 'add_district':
                    $name = trim((string) $request->input('district_name'));
                    if ($name === '') {
                        throw new \RuntimeException('District name is required.');
                    }

                    $stmt = $pdo->prepare('INSERT INTO senatorial_districts (name, slug) VALUES (:name, :slug)');
                    $stmt->execute([
                        'name' => $name,
                        'slug' => $this->slugify($name),
                    ]);
                    flash('success', 'Senatorial district added.');
                    break;

                case 'add_lga':
                    $name = trim((string) $request->input('lga_name'));
                    $districtId = (int) $request->input('senatorial_district_id', 0);
                    if ($name === '' || $districtId <= 0) {
                        throw new \RuntimeException('LGA name and district are required.');
                    }

                    $stmt = $pdo->prepare(
                        'INSERT INTO lgas (senatorial_district_id, name, code) VALUES (:senatorial_district_id, :name, :code)'
                    );
                    $stmt->execute([
                        'senatorial_district_id' => $districtId,
                        'name' => $name,
                        'code' => (string) $request->input('lga_code', $this->slugify($name)),
                    ]);
                    flash('success', 'LGA added.');
                    break;

                case 'add_ward':
                    $name = trim((string) $request->input('ward_name'));
                    $lgaId = (int) $request->input('lga_id', 0);
                    if ($name === '' || $lgaId <= 0) {
                        throw new \RuntimeException('Ward name and LGA are required.');
                    }

                    $stmt = $pdo->prepare('INSERT INTO wards (lga_id, name, code) VALUES (:lga_id, :name, :code)');
                    $stmt->execute([
                        'lga_id' => $lgaId,
                        'name' => $name,
                        'code' => (string) $request->input('ward_code', $this->slugify($name)),
                    ]);
                    flash('success', 'Ward added.');
                    break;

                case 'delete':
                    $entity = (string) $request->input('entity', '');
                    $id = (int) $request->input('entity_id', 0);
                    $map = [
                        'district' => 'senatorial_districts',
                        'lga' => 'lgas',
                        'ward' => 'wards',
                    ];
                    if (!isset($map[$entity]) || $id <= 0) {
                        throw new \RuntimeException('Invalid geography record.');
                    }

                    $stmt = $pdo->prepare('DELETE FROM ' . $map[$entity] . ' WHERE id = :id');
                    $stmt->execute(['id' => $id]);
                    flash('success', 'Record deleted.');
                    break;

                default:
                    flash('error', 'Unsupported action.');
            }
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }

        redirect('/admin/geography');
    }

    private function handlePollingUnitPost(Request $request, \PDO $pdo): void
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid security token.');
            redirect('/admin/polling-units');
        }

        $action = (string) $request->input('action', '');

        try {
            switch ($action) {
                case 'add_polling_unit':
                    $name = trim((string) $request->input('polling_name'));
                    $code = trim((string) $request->input('polling_code'));
                    $districtId = (int) $request->input('senatorial_district_id', 0);
                    $lgaId = (int) $request->input('lga_id', 0);
                    $wardId = (int) $request->input('ward_id', 0);
                    if ($name === '' || $code === '' || $districtId <= 0 || $lgaId <= 0 || $wardId <= 0) {
                        throw new \RuntimeException('Polling unit, district, LGA, ward, and code are required.');
                    }

                    $stmt = $pdo->prepare(
                        'INSERT INTO polling_units
                            (senatorial_district_id, lga_id, ward_id, polling_code, polling_name, latitude, longitude, gps_address)
                         VALUES
                            (:senatorial_district_id, :lga_id, :ward_id, :polling_code, :polling_name, :latitude, :longitude, :gps_address)'
                    );
                    $stmt->execute([
                        'senatorial_district_id' => $districtId,
                        'lga_id' => $lgaId,
                        'ward_id' => $wardId,
                        'polling_code' => $code,
                        'polling_name' => $name,
                        'latitude' => $request->input('latitude') !== null && $request->input('latitude') !== '' ? (float) $request->input('latitude') : null,
                        'longitude' => $request->input('longitude') !== null && $request->input('longitude') !== '' ? (float) $request->input('longitude') : null,
                        'gps_address' => trim((string) $request->input('gps_address', '')) ?: null,
                    ]);
                    flash('success', 'Polling unit added.');
                    break;

                case 'delete':
                    $id = (int) $request->input('entity_id', 0);
                    if ($id <= 0) {
                        throw new \RuntimeException('Invalid polling unit.');
                    }

                    $stmt = $pdo->prepare('DELETE FROM polling_units WHERE id = :id');
                    $stmt->execute(['id' => $id]);
                    flash('success', 'Polling unit deleted.');
                    break;

                case 'import_polling_units_csv':
                    $csvPath = Config::basePath('polliong unit.csv');
                    if (!is_file($csvPath)) {
                        throw new \RuntimeException('Polling unit CSV file was not found.');
                    }

                    $counts = $this->importPollingUnitsFromCsv($pdo, $csvPath);
                    flash(
                        'success',
                        sprintf('Imported %d polling units from the CSV file.', $counts['polling_units'])
                    );
                    break;

                default:
                    flash('error', 'Unsupported action.');
            }
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }

        redirect('/admin/polling-units');
    }

    private function importPollingUnitsFromCsv(\PDO $pdo, string $csvPath): array
    {
        set_time_limit(0);

        $handle = fopen($csvPath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open the polling unit CSV file.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new \RuntimeException('The polling unit CSV file is empty.');
        }

        $header = array_map(static fn ($value): string => trim((string) $value), $header);
        $rowCount = 0;

        $pdo->beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null] || $row === []) {
                    continue;
                }

                $record = array_combine($header, $row);
                if ($record === false) {
                    continue;
                }

                $districtName = $this->normalizeDistrictName(trim((string) ($record['Senatorial District'] ?? '')));
                $lgaName = $this->normalizeLgaName(trim((string) ($record['Local Government'] ?? '')));
                $wardName = trim((string) ($record['Ward'] ?? ''));
                $pollingName = trim((string) ($record['Polling Unit'] ?? ''));
                $stateCode = trim((string) ($record['State Code'] ?? ''));
                $lgaCode = trim((string) ($record['LGA Code'] ?? ''));
                $wardCode = trim((string) ($record['Ward Code'] ?? ''));
                $pollingCode = trim((string) ($record['Full Polling Unit Code'] ?? ''));

                if ($districtName === '' || $lgaName === '' || $wardName === '' || $pollingName === '') {
                    continue;
                }

                $districtId = $this->upsertDistrict($pdo, $districtName);
                $lgaId = $this->upsertLga(
                    $pdo,
                    $districtId,
                    $lgaName,
                    $stateCode !== '' && $lgaCode !== '' ? $stateCode . '/' . $lgaCode : $this->slugify($lgaName)
                );
                $wardId = $this->upsertWard(
                    $pdo,
                    $lgaId,
                    $wardName,
                    $stateCode !== '' && $lgaCode !== '' && $wardCode !== ''
                        ? $stateCode . '/' . $lgaCode . '/' . $wardCode
                        : $this->slugify($lgaName . ' ' . $wardName)
                );

                $this->upsertPollingUnit(
                    $pdo,
                    $districtId,
                    $lgaId,
                    $wardId,
                    $pollingCode !== '' ? $pollingCode : $stateCode . '/' . $lgaCode . '/' . $wardCode . '/' . $this->slugify($pollingName),
                    $pollingName
                );

                $rowCount++;
            }

            $pdo->commit();
            fclose($handle);

            return ['polling_units' => $rowCount];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            fclose($handle);
            throw $e;
        }
    }

    private function upsertDistrict(\PDO $pdo, string $name): int
    {
        $candidates = array_values(array_unique(array_filter([
            $name,
            $this->districtAliasName($name),
        ])));

        $districtId = $this->findRecordIdByNames($pdo, 'senatorial_districts', $candidates);
        if ($districtId > 0) {
            $stmt = $pdo->prepare('UPDATE senatorial_districts SET name = :name, slug = :slug, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'name' => $name,
                'slug' => $this->slugify($name),
                'id' => $districtId,
            ]);

            return $districtId;
        }

        $stmt = $pdo->prepare('INSERT INTO senatorial_districts (name, slug) VALUES (:name, :slug)');
        $stmt->execute([
            'name' => $name,
            'slug' => $this->slugify($name),
        ]);

        return (int) $pdo->lastInsertId();
    }

    private function upsertLga(\PDO $pdo, int $districtId, string $name, string $code): int
    {
        $candidates = array_values(array_unique(array_filter([
            $name,
            $this->lgaAliasName($name),
        ])));

        $lgaId = $this->findRecordIdByNames($pdo, 'lgas', $candidates);
        if ($lgaId > 0) {
            $stmt = $pdo->prepare(
                'UPDATE lgas
                 SET senatorial_district_id = :senatorial_district_id,
                     name = :name,
                     code = :code,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'senatorial_district_id' => $districtId,
                'name' => $name,
                'code' => $code,
                'id' => $lgaId,
            ]);

            return $lgaId;
        }

        $stmt = $pdo->prepare('INSERT INTO lgas (senatorial_district_id, name, code) VALUES (:senatorial_district_id, :name, :code)');
        $stmt->execute([
            'senatorial_district_id' => $districtId,
            'name' => $name,
            'code' => $code,
        ]);

        return (int) $pdo->lastInsertId();
    }

    private function upsertWard(\PDO $pdo, int $lgaId, string $name, string $code): int
    {
        $stmt = $pdo->prepare('SELECT id FROM wards WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        $wardId = (int) ($stmt->fetchColumn() ?: 0);

        if ($wardId > 0) {
            $update = $pdo->prepare(
                'UPDATE wards
                 SET lga_id = :lga_id,
                     name = :name,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $update->execute([
                'lga_id' => $lgaId,
                'name' => $name,
                'id' => $wardId,
            ]);

            return $wardId;
        }

        $insert = $pdo->prepare('INSERT INTO wards (lga_id, name, code) VALUES (:lga_id, :name, :code)');
        $insert->execute([
            'lga_id' => $lgaId,
            'name' => $name,
            'code' => $code,
        ]);

        return (int) $pdo->lastInsertId();
    }

    private function upsertPollingUnit(\PDO $pdo, int $districtId, int $lgaId, int $wardId, string $code, string $name): int
    {
        $stmt = $pdo->prepare('SELECT id FROM polling_units WHERE polling_code = :polling_code LIMIT 1');
        $stmt->execute(['polling_code' => $code]);
        $pollingUnitId = (int) ($stmt->fetchColumn() ?: 0);

        if ($pollingUnitId > 0) {
            $update = $pdo->prepare(
                'UPDATE polling_units
                 SET senatorial_district_id = :senatorial_district_id,
                     lga_id = :lga_id,
                     ward_id = :ward_id,
                     polling_name = :polling_name,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $update->execute([
                'senatorial_district_id' => $districtId,
                'lga_id' => $lgaId,
                'ward_id' => $wardId,
                'polling_name' => $name,
                'id' => $pollingUnitId,
            ]);

            return $pollingUnitId;
        }

        $insert = $pdo->prepare(
            'INSERT INTO polling_units (senatorial_district_id, lga_id, ward_id, polling_code, polling_name, latitude, longitude, gps_address)
             VALUES (:senatorial_district_id, :lga_id, :ward_id, :polling_code, :polling_name, NULL, NULL, NULL)'
        );
        $insert->execute([
            'senatorial_district_id' => $districtId,
            'lga_id' => $lgaId,
            'ward_id' => $wardId,
            'polling_code' => $code,
            'polling_name' => $name,
        ]);

        return (int) $pdo->lastInsertId();
    }

    private function findRecordIdByNames(\PDO $pdo, string $table, array $names): int
    {
        $names = array_values(array_unique(array_filter(array_map('trim', $names), static fn (string $value): bool => $value !== '')));
        if ($names === []) {
            return 0;
        }

        $placeholders = [];
        $params = [];
        foreach ($names as $index => $candidate) {
            $key = 'candidate_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $candidate;
        }

        $stmt = $pdo->prepare('SELECT id FROM ' . $table . ' WHERE name IN (' . implode(', ', $placeholders) . ') LIMIT 1');
        $stmt->execute($params);

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function normalizeDistrictName(string $name): string
    {
        $aliases = [
            'abeokuta senatorial district' => 'Ogun Central',
            'ijebu senatorial district' => 'Ogun East',
            'remo senatorial district' => 'Ogun West',
        ];

        $key = strtolower(preg_replace('/\s+/', ' ', trim($name)) ?: '');

        return $aliases[$key] ?? $name;
    }

    private function districtAliasName(string $name): string
    {
        $aliases = [
            'Ogun Central' => 'Abeokuta Senatorial District',
            'Ogun East' => 'Ijebu Senatorial District',
            'Ogun West' => 'Remo Senatorial District',
        ];

        return $aliases[$name] ?? $name;
    }

    private function normalizeLgaName(string $name): string
    {
        $aliases = [
            'ado odo-ota' => 'Ado-Odo/Ota',
            'ado odo ota' => 'Ado-Odo/Ota',
            'ota' => 'Ado-Odo/Ota',
            'ado-odo/ota' => 'Ado-Odo/Ota',
            'ado-odo ota' => 'Ado-Odo/Ota',
            'egbado north' => 'Yewa North',
            'egbado south' => 'Yewa South',
            'imeko/afon' => 'Imeko Afon',
            'obafemi/owode' => 'Obafemi Owode',
            'ogun water side' => 'Ogun Waterside',
            'ogun waterside' => 'Ogun Waterside',
            'ijebu north east' => 'Ijebu North-East',
            'ijebu north-east' => 'Ijebu North-East',
            'ijebu ode' => 'Ijebu-Ode',
        ];

        $key = strtolower(preg_replace('/\s+/', ' ', trim($name)) ?: '');

        return $aliases[$key] ?? $name;
    }

    private function lgaAliasName(string $name): string
    {
        $aliases = [
            'Ado-Odo/Ota' => 'Ado Odo-Ota',
            'Ota' => 'Ado-Odo/Ota',
            'Yewa North' => 'Egbado North',
            'Yewa South' => 'Egbado South',
            'Imeko Afon' => 'Imeko/Afon',
            'Obafemi Owode' => 'Obafemi/Owode',
            'Ogun Waterside' => 'Ogun Water Side',
            'Ijebu North-East' => 'Ijebu North East',
            'Ijebu-Ode' => 'Ijebu Ode',
        ];

        return $aliases[$name] ?? $name;
    }

    private function renderModule(string $title, string $description): void
    {
        $this->view('admin/module', compact('title', 'description'));
    }

    private function slugify(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?: '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'item-' . time();
    }
}
