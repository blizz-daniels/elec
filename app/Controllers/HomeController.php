<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\NewsPost;
use App\Support\Request;

final class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('home/index', [
            'title' => 'Home',
            'newsPosts' => (new NewsPost())->published(4),
        ]);
    }

    public function about(Request $request): void
    {
        $this->view('home/about', ['title' => 'About']);
    }

    public function leadership(Request $request): void
    {
        $this->view('home/leadership', ['title' => 'Leadership']);
    }

    public function membership(Request $request): void
    {
        $this->view('home/membership', ['title' => 'Membership']);
    }

    public function electionMonitoring(Request $request): void
    {
        $this->view('home/election-monitoring', ['title' => 'Marshal Monitoring']);
    }

    public function news(Request $request): void
    {
        $this->view('home/news', [
            'title' => 'News',
            'newsPosts' => (new NewsPost())->published(),
        ]);
    }

    public function contact(Request $request): void
    {
        $this->view('home/contact', ['title' => 'Contact']);
    }

    public function faqs(Request $request): void
    {
        $this->view('home/faqs', ['title' => 'FAQs']);
    }
}
