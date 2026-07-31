<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Member;
use App\Models\User;
use App\Support\Database;
use App\Support\Csrf;
use App\Support\FileUpload;
use App\Support\Request;
use App\Support\Validator;
use App\Support\Session;

final class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        $this->view('auth/login', ['title' => 'Login']);
    }

    public function login(Request $request): void
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid security token.');
            redirect('/login');
        }

        $user = (new User())->authenticate((string) $request->input('email'), (string) $request->input('password'));
        if (!$user) {
            flash('error', 'Invalid login credentials.');
            redirect('/login');
        }

        Session::set('auth_user', $user);
        session_regenerate_id(true);

        $roleSlug = strtolower(trim((string) ($user['role_slug'] ?? $user['role'] ?? '')));
        $roleName = strtolower(trim((string) ($user['role_name'] ?? '')));
        $isMember = $roleSlug === 'registered-member' || $roleName === 'registered member';

        if ($isMember) {
            redirect('/member/dashboard');
        }

        redirect('/dashboard');
    }

    public function showRegister(Request $request): void
    {
        $pdo = Database::pdo();

        $this->view('auth/register', [
            'title' => 'Register',
            'districts' => $pdo->query('SELECT id, name FROM senatorial_districts ORDER BY name')->fetchAll(),
            'lgas' => $pdo->query(
                'SELECT lgas.id, lgas.name, lgas.senatorial_district_id, senatorial_districts.name AS district_name
                 FROM lgas
                 INNER JOIN senatorial_districts ON senatorial_districts.id = lgas.senatorial_district_id
                 ORDER BY lgas.name'
            )->fetchAll(),
            'wards' => $pdo->query(
                'SELECT wards.id, wards.name, wards.lga_id, lgas.name AS lga_name
                 FROM wards
                 INNER JOIN lgas ON lgas.id = wards.lga_id
                 ORDER BY wards.name'
            )->fetchAll(),
            'pollingUnits' => array_map(
                static function (array $unit): array {
                    $parts = array_values(array_filter(explode('/', (string) ($unit['polling_code'] ?? '')), static fn (string $part): bool => $part !== ''));
                    $unit['polling_unit_no'] = $parts !== [] ? (string) end($parts) : '';
                    return $unit;
                },
                $pdo->query(
                    'SELECT polling_units.id,
                            polling_units.polling_name,
                            polling_units.polling_code,
                            polling_units.senatorial_district_id,
                            polling_units.lga_id,
                            polling_units.ward_id,
                            senatorial_districts.name AS district_name,
                            lgas.name AS lga_name,
                            wards.name AS ward_name
                     FROM polling_units
                     INNER JOIN senatorial_districts ON senatorial_districts.id = polling_units.senatorial_district_id
                     INNER JOIN lgas ON lgas.id = polling_units.lga_id
                     INNER JOIN wards ON wards.id = polling_units.ward_id
                     ORDER BY polling_units.polling_name'
                )->fetchAll()
            ),
        ]);
    }

    public function register(Request $request): void
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid security token.');
            redirect('/register');
        }

        $requiredFields = [
            'surname',
            'first_name',
            'phone',
            'email',
            'state_of_origin',
            'date_of_birth',
            'gender',
            'vin',
            'nin',
            'polling_unit_id',
            'password',
            'confirm_password',
        ];

        $errors = Validator::required($request->all(), $requiredFields);
        if ($errors !== []) {
            $this->rememberRegisterInput($request);
            flash('error', implode(' ', array_values($errors)));
            redirect('/register');
        }

        $email = trim((string) $request->input('email'));
        if (!Validator::email($email)) {
            $this->rememberRegisterInput($request);
            flash('error', 'Please enter a valid email address.');
            redirect('/register');
        }

        if ((string) $request->input('password') !== (string) $request->input('confirm_password')) {
            $this->rememberRegisterInput($request);
            flash('error', 'Passwords do not match.');
            redirect('/register');
        }

        $passport = $request->file('passport');
        if (!$passport || ($passport['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $this->rememberRegisterInput($request);
            flash('error', 'Passport attachment is required.');
            redirect('/register');
        }

        if (($passport['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $this->rememberRegisterInput($request);
            flash('error', 'Passport upload could not be processed. Please try again.');
            redirect('/register');
        }

        $passportPath = null;
        if (!Validator::maxBytes($passport, (int) app('UPLOAD_MAX_KB', 5120))) {
            $this->rememberRegisterInput($request);
            flash('error', 'Passport upload must be 5MB or smaller.');
            redirect('/register');
        }

        if (!Validator::mimeIn($passport, ['image/jpeg', 'image/png'])) {
            $this->rememberRegisterInput($request);
            flash('error', 'Passport upload must be a JPG, JPEG, or PNG image.');
            redirect('/register');
        }

        $pdo = Database::pdo();
        $pollingUnitId = (int) $request->input('polling_unit_id', 0);

        $pollingUnitStmt = $pdo->prepare(
            'SELECT polling_units.id,
                    polling_units.polling_name,
                    polling_units.polling_code,
                    polling_units.senatorial_district_id,
                    polling_units.lga_id,
                    polling_units.ward_id,
                    senatorial_districts.name AS district_name,
                    lgas.name AS lga_name,
                    wards.name AS ward_name
             FROM polling_units
             INNER JOIN senatorial_districts ON senatorial_districts.id = polling_units.senatorial_district_id
             INNER JOIN lgas ON lgas.id = polling_units.lga_id
             INNER JOIN wards ON wards.id = polling_units.ward_id
             WHERE polling_units.id = :id
             LIMIT 1'
        );
        $pollingUnitStmt->execute(['id' => $pollingUnitId]);
        $pollingUnit = $pollingUnitStmt->fetch();
        if (!$pollingUnit) {
            $this->rememberRegisterInput($request);
            flash('error', 'Select a valid polling unit.');
            redirect('/register');
        }

        $pollingUnitParts = array_values(array_filter(explode('/', (string) ($pollingUnit['polling_code'] ?? '')), static fn (string $part): bool => $part !== ''));
        $pollingUnit['polling_unit_no'] = $pollingUnitParts !== [] ? (string) end($pollingUnitParts) : '';

        $districtId = (int) $pollingUnit['senatorial_district_id'];
        $lgaId = (int) $pollingUnit['lga_id'];
        $wardId = (int) $pollingUnit['ward_id'];

        $existingEmailStmt = $pdo->prepare('SELECT id FROM members WHERE email = :email LIMIT 1');
        $existingEmailStmt->execute(['email' => $email]);
        if ($existingEmailStmt->fetch()) {
            $this->rememberRegisterInput($request);
            flash('error', 'That email address is already registered.');
            redirect('/register');
        }

        $passportPath = FileUpload::store($passport, 'uploads/passport');

        $member = new Member();
        $membershipNumber = $member->nextMembershipNumber();
        $password = password_hash((string) $request->input('password'), PASSWORD_DEFAULT);

        $member->create([
            'membership_number' => $membershipNumber,
            'surname' => (string) $request->input('surname'),
            'first_name' => (string) $request->input('first_name'),
            'other_name' => (string) $request->input('other_name'),
            'phone' => (string) $request->input('phone'),
            'email' => $email,
            'state_of_origin' => (string) $request->input('state_of_origin'),
            'state_of_residence' => 'Ogun Resident',
            'date_of_birth' => (string) $request->input('date_of_birth'),
            'gender' => (string) $request->input('gender'),
            'vin' => (string) $request->input('vin'),
            'nin' => (string) $request->input('nin'),
            'senatorial_district_id' => $districtId,
            'lga_id' => $lgaId,
            'ward_id' => $wardId,
            'polling_unit_id' => $pollingUnitId,
            'passport_path' => $passportPath,
            'password' => $password,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        foreach ([
            'surname', 'first_name', 'other_name', 'phone', 'email', 'state_of_origin', 'state_of_residence',
            'date_of_birth', 'gender', 'vin', 'nin', 'polling_unit_id',
        ] as $field) {
            Session::set('old.' . $field, null);
        }

        flash('success', 'Registration complete. Your membership number is ' . $membershipNumber);
        redirect('/login');
    }

    public function logout(Request $request): void
    {
        Session::destroy();
        redirect('/');
    }

    private function rememberRegisterInput(Request $request): void
    {
        foreach ([
            'surname', 'first_name', 'other_name', 'phone', 'email', 'state_of_origin', 'state_of_residence',
            'date_of_birth', 'gender', 'vin', 'nin', 'senatorial_district_id', 'lga_id', 'ward_id', 'polling_unit_id',
        ] as $field) {
            Session::set('old.' . $field, $request->input($field));
        }
    }
}
