<?php

declare(strict_types=1);

namespace App\Controllers;

final class UserController extends BaseController
{
    public function index(): void
    {
        $user = $this->requireLogin();
        $this->auth->requireAdmin($user);

        $this->render('users/index', [
            'title' => 'Users',
            'admin' => $user,
            'users' => $this->auth->listUsers(),
            'flash' => $this->consumeFlash(),
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function create(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();
        $this->auth->requireAdmin($user);

        try {
            $this->auth->createUser(
                trim((string) ($_POST['name'] ?? '')),
                trim((string) ($_POST['email'] ?? '')),
                (string) ($_POST['password'] ?? ''),
                (string) ($_POST['role'] ?? 'padrao')
            );
            $this->flash('success', 'User created.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirect('/users');
    }

    public function updateRole(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();
        $this->auth->requireAdmin($user);

        try {
            $this->auth->updateUserRole(
                $user,
                (int) ($_POST['user_id'] ?? 0),
                (string) ($_POST['role'] ?? 'padrao')
            );
            $this->flash('success', 'Role updated.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirect('/users');
    }

    public function delete(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();
        $this->auth->requireAdmin($user);

        try {
            $this->auth->deleteUser($user, (int) ($_POST['user_id'] ?? 0));
            $this->flash('success', 'User removed.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirect('/users');
    }
}

