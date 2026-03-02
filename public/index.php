<?php

declare(strict_types=1);

// Evita timeout de execucao no runtime da aplicacao.
@ini_set('max_execution_time', '0');
@set_time_limit(0);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\AppKernel;

$app = new AppKernel();
$app->run();
