<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Support\Auth;
use App\Support\Request;

final class AdminModuleController extends Controller
{
    public function members(Request $request): void
    {
        $this->renderModule('Members', 'Member registration, approvals, exports, and card management.');
    }

    public function executives(Request $request): void
    {
        $this->renderModule('Executives', 'State, senatorial, LGA, and ward executive management.');
    }

    public function geography(Request $request): void
    {
        $this->renderModule('Geographic Structure', 'Senatorial districts, LGAs, wards, and polling units.');
    }

    public function pollingUnits(Request $request): void
    {
        $this->renderModule('Polling Units', 'Polling codes, GPS metadata, and marshal assignments.');
    }

    public function marshals(Request $request): void
    {
        $this->renderModule('Polling Marshals', 'Marshal onboarding, login assignment, and deployment.');
    }

    public function candidates(Request $request): void
    {
        $this->renderModule('Candidates', 'Election candidates, positions, parties, and biographies.');
    }

    public function auditLogs(Request $request): void
    {
        $this->renderModule('Audit Logs', 'Login, edit, approval, and submission history.');
    }

    public function profile(Request $request): void
    {
        $this->renderModule('Profile', 'User profile and security settings.');
    }

    private function renderModule(string $title, string $description): void
    {
        Auth::requiresLogin();
        $this->view('admin/module', compact('title', 'description'));
    }
}
