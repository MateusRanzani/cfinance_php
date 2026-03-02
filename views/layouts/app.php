<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title ?? 'Financas do Casal') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">
    <?php $isAuthenticated = isset($_SESSION['user']) && is_array($_SESSION['user']); ?>
    <?php if ($isAuthenticated): ?>
        <?php $activeMenu = (string) ($activeMenu ?? 'dashboard'); ?>
        <?php
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        $csrfLayout = (string) $_SESSION['csrf'];
        $userLayout = $_SESSION['user'] ?? [];
        $roleLabel = (($userLayout['role'] ?? '') === 'admin') ? 'Administrador' : 'Padrao';
        ?>
        <div class="flex min-h-screen">
            <aside class="flex w-64 flex-col border-r border-slate-200 bg-white p-4">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Menu</p>
                    <h2 class="mt-1 text-lg font-bold">
                        <a href="<?= $url('/dashboard') ?>">Financas</a>
                    </h2>
                </div>
                <nav class="space-y-1">
                    <a
                        href="<?= $url('/dashboard') ?>"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $activeMenu === 'dashboard' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                        Dashboard
                    </a>
                    <a
                        href="<?= $url('/rendas') ?>"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $activeMenu === 'rendas' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                        Renda
                    </a>
                    <a
                        href="<?= $url('/despesas') ?>"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $activeMenu === 'despesas' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                        Despesa
                    </a>
                    <a
                        href="<?= $url('/tipos') ?>"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $activeMenu === 'tipos' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                        Tipos
                    </a>
                    <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                        <a
                            href="<?= $url('/users') ?>"
                            class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $activeMenu === 'users' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            Usuarios
                        </a>
                    <?php endif; ?>
                </nav>

                <div class="mt-auto border-t border-slate-200 pt-4">
                    <p class="text-xs text-slate-500">Usuario logado</p>
                    <p class="text-sm font-semibold text-slate-900"><?= $e((string) ($userLayout['name'] ?? '')) ?></p>
                    <p class="text-xs text-slate-600"><?= $e((string) ($userLayout['email'] ?? '')) ?></p>
                    <p class="mt-1 text-xs text-slate-600"><?= $e($roleLabel) ?></p>
                    <form method="post" action="<?= $url('/logout') ?>" class="mt-3">
                        <input type="hidden" name="csrf" value="<?= $e($csrfLayout) ?>">
                        <button class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">
                            Sair
                        </button>
                    </form>
                </div>
            </aside>
            <main class="flex-1 p-4 md:p-8">
                <div class="mx-auto w-full max-w-6xl">
                    <?= $content ?>
                </div>
            </main>
        </div>
    <?php else: ?>
        <main class="mx-auto w-full max-w-7xl p-4 md:p-8">
            <?= $content ?>
        </main>
    <?php endif; ?>
</body>

</html>
