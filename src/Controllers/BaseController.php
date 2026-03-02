<?php

declare(strict_types=1);

namespace App\Controllers;

use App\AuthService;
use App\Core\View;
use App\FinanceService;

abstract class BaseController
{
    public function __construct(
        protected AuthService $auth,
        protected FinanceService $finance,
        protected string $basePath
    ) {
    }

    protected function render(string $template, array $data = []): void
    {
        echo View::render($template, $data + ['basePath' => $this->basePath]);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . View::url($this->basePath, $path));
        exit;
    }

    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['csrf'];
    }

    protected function verifyCsrf(): void
    {
        $token = (string) ($_POST['csrf'] ?? '');
        $session = (string) ($_SESSION['csrf'] ?? '');

        if ($token === '' || $session === '' || !hash_equals($session, $token)) {
            throw new \RuntimeException('Token CSRF invalido.');
        }
    }

    protected function flash(string $kind, string $message): void
    {
        $_SESSION['flash'][$kind] = $message;
    }

    protected function consumeFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return is_array($flash) ? $flash : [];
    }

    protected function requireLogin(): array
    {
        $user = $this->auth->currentUser();
        if ($user === null) {
            $this->redirect('/login');
        }

        return $user;
    }

    protected function resolveTargetUserId(array $currentUser): int
    {
        if (($currentUser['role'] ?? '') === 'admin' && isset($_REQUEST['user_id'])) {
            return (int) $_REQUEST['user_id'];
        }

        return (int) $currentUser['id'];
    }
}
