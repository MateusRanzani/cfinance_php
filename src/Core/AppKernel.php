<?php

declare(strict_types=1);

namespace App\Core;

use App\AuthService;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Database;
use App\FinanceService;
use PDOException;

final class AppKernel
{
    private Router $router;
    private string $basePath;

    public function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/config.php';

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $db = Database::fromConfig($config['db']);
        $auth = new AuthService($db);
        $finance = new FinanceService($db);

        $this->basePath = View::detectBasePath();
        $this->router = new Router();

        $authController = new AuthController($auth, $finance, $this->basePath);
        $dashboardController = new DashboardController($auth, $finance, $this->basePath);
        $userController = new UserController($auth, $finance, $this->basePath);

        $this->router->get('/', function () use ($auth): void {
            $target = $auth->currentUser() === null ? '/login' : '/dashboard';
            header('Location: ' . View::url($this->basePath, $target));
            exit;
        });

        $this->router->get('/login', [$authController, 'showLogin']);
        $this->router->post('/login', [$authController, 'login']);
        $this->router->post('/logout', [$authController, 'logout']);

        $this->router->get('/dashboard', [$dashboardController, 'index']);
        $this->router->get('/rendas', [$dashboardController, 'incomesPage']);
        $this->router->get('/despesas', [$dashboardController, 'expensesPage']);
        $this->router->get('/tipos', [$dashboardController, 'typesPage']);
        $this->router->get('/metas', [$dashboardController, 'goalsPage']);

        $this->router->post('/rendas/add', [$dashboardController, 'addIncome']);
        $this->router->post('/rendas/update', [$dashboardController, 'updateIncome']);
        $this->router->post('/rendas/delete', [$dashboardController, 'deleteIncome']);

        $this->router->post('/despesas/add', [$dashboardController, 'addExpense']);
        $this->router->post('/despesas/update', [$dashboardController, 'updateExpense']);
        $this->router->post('/despesas/delete', [$dashboardController, 'deleteExpense']);

        $this->router->post('/mes/copiar-anterior', [$dashboardController, 'copyPreviousMonth']);

        $this->router->post('/fixos/rendas/add', [$dashboardController, 'addFixedIncome']);
        $this->router->post('/fixos/rendas/delete', [$dashboardController, 'deleteFixedIncome']);
        $this->router->post('/fixos/despesas/add', [$dashboardController, 'addFixedExpense']);
        $this->router->post('/fixos/despesas/delete', [$dashboardController, 'deleteFixedExpense']);
        $this->router->post('/fixos/aplicar', [$dashboardController, 'applyFixed']);
        $this->router->post('/tipos/add', [$dashboardController, 'addType']);
        $this->router->post('/tipos/update', [$dashboardController, 'updateType']);
        $this->router->post('/tipos/delete', [$dashboardController, 'deleteType']);
        $this->router->post('/metas/add', [$dashboardController, 'addGoal']);
        $this->router->post('/metas/aporte', [$dashboardController, 'addGoalContribution']);
        $this->router->post('/metas/delete', [$dashboardController, 'deleteGoal']);

        $this->router->get('/users', [$userController, 'index']);
        $this->router->post('/users/create', [$userController, 'create']);
        $this->router->post('/users/update', [$userController, 'update']);
        $this->router->post('/users/update-role', [$userController, 'updateRole']);
        $this->router->post('/users/delete', [$userController, 'delete']);
    }

    public function run(): void
    {
        try {
            $method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
            $path = $this->resolvePath();
            $this->router->dispatch($method, $path);
        } catch (PDOException) {
            $isDebug = filter_var((string) ($_ENV['APP_DEBUG'] ?? 'false'), FILTER_VALIDATE_BOOLEAN);
            http_response_code(500);
            echo View::render('errors/generic', [
                'title' => 'Erro de banco',
                'message' => $isDebug
                    ? 'Falha ao conectar no MySQL. Revise config/config.php e .env.'
                    : 'Falha ao conectar no MySQL. Revise config/config.php.',
                'basePath' => $this->basePath,
            ]);
        } catch (\Throwable $exception) {
            http_response_code(500);
            echo View::render('errors/generic', [
                'title' => 'Erro interno',
                'message' => $exception->getMessage(),
                'basePath' => $this->basePath,
            ]);
        }
    }

    private function resolvePath(): string
    {
        $uriPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $uriPath = is_string($uriPath) ? $uriPath : '/';

        if ($this->basePath !== '/' && str_starts_with($uriPath, $this->basePath)) {
            $uriPath = substr($uriPath, strlen($this->basePath));
        }

        if ($uriPath === '' || $uriPath === false) {
            return '/';
        }

        return $uriPath;
    }
}
