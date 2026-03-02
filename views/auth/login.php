<?php
$error = (string) ($flash['error'] ?? '');
?>
<section class="mx-auto mt-12 max-w-md rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold tracking-tight">Financas do Casal</h1>
    <p class="mt-1 text-sm text-slate-600">Entre para continuar</p>

    <?php if ($error !== ''): ?>
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
            <?= $e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $url('/login') ?>" class="mt-6 space-y-4">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-900">E-mail</span>
            <input
                class="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-slate-500"
                type="email"
                name="email"
                required
            >
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-900">Senha</span>
            <input
                class="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-slate-500"
                type="password"
                name="password"
                required
            >
        </label>

        <button
            type="submit"
            class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"
        >
            Entrar
        </button>
    </form>
</section>

