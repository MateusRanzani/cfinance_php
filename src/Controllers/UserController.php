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
            'title' => 'Usuarios',
            'activeMenu' => 'users',
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
            $this->flash('success', 'Usuario criado.');
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
            $this->flash('success', 'Perfil atualizado.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirect('/users');
    }

    public function update(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();
        $this->auth->requireAdmin($user);

        try {
            $this->auth->updateUser(
                $user,
                (int) ($_POST['user_id'] ?? 0),
                (string) ($_POST['name'] ?? ''),
                (string) ($_POST['email'] ?? ''),
                (string) ($_POST['role'] ?? 'padrao'),
                (string) ($_POST['password'] ?? '')
            );
            $this->flash('success', 'Usuario atualizado.');
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
            $this->flash('success', 'Usuario removido.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirect('/users');
    }
}
