<?php
$success = (string) ($flash['success'] ?? '');
$error = (string) ($flash['error'] ?? '');
?>
<header class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold tracking-tight">Gestao de Usuarios</h1>
    <p class="mt-1 text-sm text-slate-600">Administrador logado: <?= $e($admin['name']) ?></p>
</header>

<?php if ($success !== ''): ?>
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
        <?= $e($success) ?>
    </div>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
        <?= $e($error) ?>
    </div>
<?php endif; ?>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold">Criar usuario</h2>
    <form method="post" action="<?= $url('/users/create') ?>" class="mt-4 grid gap-3 md:grid-cols-4">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-900">Nome</span>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="text" name="name" required>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-900">E-mail</span>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="email" name="email" required>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-900">Senha</span>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="password" name="password" required>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-900">Perfil</span>
            <select class="w-full rounded-lg border border-slate-300 px-3 py-2" name="role">
                <option value="padrao">padrao</option>
                <option value="admin">admin</option>
            </select>
        </label>
        <div class="md:col-span-4">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">
                Criar
            </button>
        </div>
    </form>
</section>

<section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold">Usuarios cadastrados</h2>
    <p class="mt-1 text-xs text-slate-500">Para manter a senha atual, deixe o campo de senha em branco na edicao.</p>
    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[980px] border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left">
                    <th class="p-2">Nome</th>
                    <th class="p-2">E-mail</th>
                    <th class="p-2">Senha</th>
                    <th class="p-2">Perfil</th>
                    <th class="p-2">Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $item): ?>
                    <tr class="border-b border-slate-100 align-top">
                        <td class="p-2" colspan="4">
                            <form method="post" action="<?= $url('/users/update') ?>" class="grid gap-2 md:grid-cols-4">
                                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                <input type="hidden" name="user_id" value="<?= (int) $item['id'] ?>">
                                <input class="rounded-lg border border-slate-300 px-2 py-1" type="text" name="name" value="<?= $e($item['name']) ?>" required>
                                <input class="rounded-lg border border-slate-300 px-2 py-1" type="email" name="email" value="<?= $e($item['email']) ?>" required>
                                <input class="rounded-lg border border-slate-300 px-2 py-1" type="password" name="password" placeholder="Nova senha (opcional)">
                                <select class="rounded-lg border border-slate-300 px-2 py-1" name="role">
                                    <option value="admin" <?= $item['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                                    <option value="padrao" <?= $item['role'] === 'padrao' ? 'selected' : '' ?>>padrao</option>
                                </select>
                                <div class="md:col-span-4">
                                    <button class="rounded-lg bg-slate-800 px-3 py-1 text-white hover:bg-slate-700" type="submit">Salvar alteracoes</button>
                                </div>
                            </form>
                        </td>
                        <td class="p-2">
                            <form method="post" action="<?= $url('/users/delete') ?>" onsubmit="return confirm('Excluir usuario?')">
                                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                <input type="hidden" name="user_id" value="<?= (int) $item['id'] ?>">
                                <button class="rounded-lg bg-rose-600 px-3 py-1 text-white hover:bg-rose-500" type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
