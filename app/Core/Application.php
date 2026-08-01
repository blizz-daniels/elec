<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\AdminModuleController;
use App\Controllers\DashboardController;
use App\Controllers\ElectionController;
use App\Controllers\HomeController;
use App\Controllers\MemberController;
use App\Controllers\NotificationController;
use App\Controllers\ReportController;
use App\Controllers\ResultController;
use App\Controllers\SettingsController;
use App\Core\Router;
use App\Support\Config;
use App\Support\Session;

final class Application
{
    public function __construct(private readonly string $basePath)
    {
    }

    public function run(): void
    {
        $this->boot();
        $this->routes()->dispatch();
    }

    private function boot(): void
    {
        Config::setBasePath($this->basePath);
        Session::start();
    }

    private function routes(): Router
    {
        $router = new Router();

        $router->get('/', [HomeController::class, 'index']);
        $router->get('/about', [HomeController::class, 'about']);
        $router->get('/leadership', [HomeController::class, 'leadership']);
        $router->get('/membership', [HomeController::class, 'membership']);
        $router->get('/election-monitoring', [HomeController::class, 'electionMonitoring']);
        $router->get('/news', [HomeController::class, 'news']);
        $router->get('/contact', [HomeController::class, 'contact']);
        $router->get('/faqs', [HomeController::class, 'faqs']);

        $router->get('/login', [AuthController::class, 'showLogin']);
        $router->post('/login', [AuthController::class, 'login']);
        $router->get('/register', [AuthController::class, 'showRegister']);
        $router->post('/register', [AuthController::class, 'register']);
        $router->post('/logout', [AuthController::class, 'logout']);

        $router->get('/dashboard', [DashboardController::class, 'index']);
        $router->get('/member/dashboard', [MemberController::class, 'dashboard']);
        $router->get('/member/card/pdf', [MemberController::class, 'downloadCardPdf']);
        $router->get('/member/card/qr', [MemberController::class, 'showCardQr']);
        $router->get('/member/card/qr/download', [MemberController::class, 'downloadCardQr']);
        $router->get('/admin/members', [AdminModuleController::class, 'members']);
        $router->post('/admin/members', [AdminModuleController::class, 'members']);
        $router->get('/admin/executives', [AdminModuleController::class, 'executives']);
        $router->post('/admin/executives', [AdminModuleController::class, 'executives']);
        $router->get('/admin/geography', [AdminModuleController::class, 'geography']);
        $router->post('/admin/geography', [AdminModuleController::class, 'geography']);
        $router->get('/admin/polling-units', [AdminModuleController::class, 'pollingUnits']);
        $router->post('/admin/polling-units', [AdminModuleController::class, 'pollingUnits']);
        $router->get('/admin/marshals', [AdminModuleController::class, 'marshals']);
        $router->post('/admin/marshals', [AdminModuleController::class, 'marshals']);
        $router->get('/admin/candidates', [AdminModuleController::class, 'candidates']);
        $router->post('/admin/candidates', [AdminModuleController::class, 'candidates']);
        $router->get('/admin/audit-logs', [AdminModuleController::class, 'auditLogs']);
        $router->get('/profile', [AdminModuleController::class, 'profile']);
        $router->get('/elections', [ElectionController::class, 'index']);
        $router->post('/elections', [ElectionController::class, 'index']);
        $router->get('/results', [ResultController::class, 'index']);
        $router->post('/results', [ResultController::class, 'index']);
        $router->get('/reports', [ReportController::class, 'index']);
        $router->get('/notifications', [NotificationController::class, 'index']);
        $router->get('/settings', [SettingsController::class, 'index']);

        return $router;
    }
}

