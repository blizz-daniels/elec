<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Request;

final class ReportController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requiresLogin();
        $this->view('reports/index', ['title' => 'Reports']);
    }
}
