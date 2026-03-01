<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $basePath = (string) ($data['basePath'] ?? self::detectBasePath());
        $title = (string) ($data['title'] ?? 'Finance App');
        $content = self::renderPartial($template, $data + ['basePath' => $basePath]);

        $layoutData = [
            'title' => $title,
            'content' => $content,
            'basePath' => $basePath,
        ];

        return self::renderLayout('layouts/app', $layoutData);
    }

    public static function detectBasePath(): string
    {
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        return $basePath === '' ? '/' : $basePath;
    }

    public static function url(string $basePath, string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            $path = '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        $basePath = rtrim($basePath, '/');
        if ($basePath === '' || $basePath === '/') {
            return $path;
        }

        return $basePath . $path;
    }

    private static function renderLayout(string $layout, array $data): string
    {
        return self::renderFile(self::viewPath($layout), $data);
    }

    private static function renderPartial(string $template, array $data): string
    {
        return self::renderFile(self::viewPath($template), $data);
    }

    private static function viewPath(string $template): string
    {
        $path = dirname(__DIR__, 2) . '/views/' . $template . '.php';
        if (!is_file($path)) {
            throw new RuntimeException('View not found: ' . $template);
        }

        return $path;
    }

    private static function renderFile(string $path, array $data): string
    {
        extract($data, EXTR_SKIP);

        $e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $url = static fn (string $path = '/'): string => self::url((string) ($basePath ?? '/'), $path);

        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}

