<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Request;

final class MemberController extends Controller
{
    public function dashboard(Request $request): void
    {
        Auth::requiresLogin();
        $this->view('member/dashboard', ['title' => 'Member Dashboard']);
    }
}
