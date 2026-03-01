<?php

declare(strict_types=1);

namespace App\Controllers;

final class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if ($this->auth->currentUser() !== null) {
            $this->redirect('/dashboard');
        }

        $flash = $this->consumeFlash();
        $this->render('auth/login', [
            'title' => 'Login',
            'flash' => $flash,
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (!$this->auth->login($email, $password)) {
            $this->flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        $this->auth->logout();
        $this->redirect('/login');
    }
}

