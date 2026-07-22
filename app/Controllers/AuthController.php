<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Member;
use App\Models\User;
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

        redirect('/dashboard');
    }

    public function showRegister(Request $request): void
    {
        $this->view('auth/register', ['title' => 'Register']);
    }

    public function register(Request $request): void
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid security token.');
            redirect('/register');
        }

        if ((string) $request->input('password') !== (string) $request->input('confirm_password')) {
            flash('error', 'Passwords do not match.');
            redirect('/register');
        }

        $passportPath = null;
        $passport = $request->file('passport');
        if ($passport && ($passport['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (!Validator::maxBytes($passport, (int) app('UPLOAD_MAX_KB', 100))) {
                flash('error', 'Passport upload must be 100KB or smaller.');
                redirect('/register');
            }

            if (!Validator::mimeIn($passport, ['image/jpeg', 'image/png'])) {
                flash('error', 'Passport upload must be a JPG, JPEG, or PNG image.');
                redirect('/register');
            }

            $passportPath = FileUpload::store($passport, 'uploads/passport');
        }

        $member = new Member();
        $membershipNumber = $member->nextMembershipNumber();
        $password = password_hash((string) $request->input('password'), PASSWORD_DEFAULT);

        $member->create([
            'membership_number' => $membershipNumber,
            'surname' => (string) $request->input('surname'),
            'first_name' => (string) $request->input('first_name'),
            'other_name' => (string) $request->input('other_name'),
            'phone' => (string) $request->input('phone'),
            'email' => (string) $request->input('email'),
            'state_of_origin' => (string) $request->input('state_of_origin'),
            'state_of_residence' => (string) $request->input('state_of_residence', 'Ogun State'),
            'date_of_birth' => (string) $request->input('date_of_birth'),
            'gender' => (string) $request->input('gender'),
            'vin' => (string) $request->input('vin'),
            'occupation' => (string) $request->input('occupation'),
            'residential_address' => (string) $request->input('residential_address'),
            'lga_id' => (int) $request->input('lga_id'),
            'ward_id' => (int) $request->input('ward_id'),
            'polling_unit_id' => (int) $request->input('polling_unit_id'),
            'passport_path' => $passportPath,
            'password' => $password,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        flash('success', 'Registration complete. Your membership number is ' . $membershipNumber);
        redirect('/login');
    }

    public function logout(Request $request): void
    {
        Session::destroy();
        redirect('/');
    }
}
