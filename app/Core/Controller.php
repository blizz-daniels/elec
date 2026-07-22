<?php

declare(strict_types=1);

namespace App\Core;

use App\Support\Response;

abstract class Controller
{
    protected function view(string $template, array $data = [], int $status = 200): void
    {
        Response::view($template, $data, $status);
    }
}
