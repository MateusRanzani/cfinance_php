<?php

declare(strict_types=1);

namespace App;

use App\Core\AppKernel;

final class Application
{
    private AppKernel $kernel;

    public function __construct()
    {
        $this->kernel = new AppKernel();
    }

    public function run(): void
    {
        $this->kernel->run();
    }
}

