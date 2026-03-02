<?php

declare(strict_types=1);

namespace App;

use PDO;

final class AuthService
{
    public function __construct(private PDO $db)
    {
    }

    public function login(string $email, string $password): bool
    {
        $query = $this->db->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
        $query->execute(['email' => $email]);
        $user = $query->fetch();

        if (!$user || !password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
        ];

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public function currentUser(): ?array
    {
        $user = $_SESSION['user'] ?? null;

        if (!is_array($user) || empty($user['id'])) {
            return null;
        }

        return $user;
    }

    public function requireLogin(): array
    {
        $user = $this->currentUser();
        if ($user === null) {
            header('Location: /login');
            exit;
        }

        return $user;
    }

    public function requireAdmin(array $user): void
    {
        if (($user['role'] ?? '') !== 'admin') {
            throw new \RuntimeException('Acesso permitido apenas para administrador.');
        }
    }

    public function listUsers(): array
    {
        return $this->db->query('SELECT id, name, email, role FROM users ORDER BY name')->fetchAll();
    }

    public function createUser(string $name, string $email, string $password, string $role): void
    {
        if ($name === '' || $email === '' || $password === '') {
            throw new \RuntimeException('Nome, e-mail e senha sao obrigatorios.');
        }

        if (!in_array($role, ['admin', 'padrao'], true)) {
            throw new \RuntimeException('Perfil invalido.');
        }

        $query = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)'
        );
        $query->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
    }

    public function updateUserRole(array $actor, int $targetUserId, string $role): void
    {
        if (!in_array($role, ['admin', 'padrao'], true)) {
            throw new \RuntimeException('Perfil invalido.');
        }

        if ((int) $actor['id'] === $targetUserId && $role !== 'admin') {
            throw new \RuntimeException('Voce nao pode remover seu proprio privilegio de admin.');
        }

        $query = $this->db->prepare('UPDATE users SET role = :role WHERE id = :id');
        $query->execute(['role' => $role, 'id' => $targetUserId]);
    }

    public function updateUser(array $actor, int $targetUserId, string $name, string $email, string $role, string $password = ''): void
    {
        if ($targetUserId <= 0) {
            throw new \RuntimeException('Usuario invalido.');
        }

        $name = trim($name);
        $email = trim($email);
        if ($name === '' || $email === '') {
            throw new \RuntimeException('Nome e e-mail sao obrigatorios.');
        }

        if (!in_array($role, ['admin', 'padrao'], true)) {
            throw new \RuntimeException('Perfil invalido.');
        }

        if ((int) $actor['id'] === $targetUserId && $role !== 'admin') {
            throw new \RuntimeException('Voce nao pode remover seu proprio privilegio de admin.');
        }

        if ($password !== '') {
            $query = $this->db->prepare(
                'UPDATE users
                 SET name = :name, email = :email, role = :role, password_hash = :password_hash
                 WHERE id = :id'
            );
            $query->execute([
                'id' => $targetUserId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            return;
        }

        $query = $this->db->prepare(
            'UPDATE users
             SET name = :name, email = :email, role = :role
             WHERE id = :id'
        );
        $query->execute([
            'id' => $targetUserId,
            'name' => $name,
            'email' => $email,
            'role' => $role,
        ]);
    }

    public function deleteUser(array $actor, int $targetUserId): void
    {
        if ((int) $actor['id'] === $targetUserId) {
            throw new \RuntimeException('Voce nao pode excluir seu proprio usuario.');
        }

        $query = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $query->execute(['id' => $targetUserId]);
    }
}
