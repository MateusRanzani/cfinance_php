<header class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold tracking-tight">Tipos de movimentacao</h1>
    <p class="mt-1 text-sm text-slate-600">Cadastre os tipos para usar em rendas e despesas.</p>
</header>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold">Novo tipo</h2>
    <form method="post" action="<?= $url('/tipos/add') ?>" class="mt-4 grid gap-3 md:grid-cols-3">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Categoria</span>
            <select class="w-full rounded-lg border border-slate-300 px-3 py-2" name="categoria" required>
                <option value="renda">Renda</option>
                <option value="despesa">Despesa</option>
            </select>
        </label>
        <label class="block text-sm md:col-span-2">
            <span class="mb-1 block font-medium text-slate-700">Nome</span>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="text" name="nome" placeholder="Ex.: Salario, Cartao, Aluguel" required>
        </label>
        <div class="md:col-span-3">
            <button class="icon-btn icon-btn--primary" type="submit">
                <span class="material-symbols-rounded" aria-hidden="true">add</span>
                <span class="icon-btn__text">Cadastrar tipo</span>
            </button>
        </div>
    </form>
</section>

<section class="grid gap-6 lg:grid-cols-2">
    <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-lg font-semibold">Tipos de renda</h2>
        <div class="mt-4 space-y-2">
            <?php if ($incomeTypes === []): ?>
                <p class="text-sm text-slate-500">Sem tipos de renda cadastrados.</p>
            <?php endif; ?>
            <?php foreach ($incomeTypes as $item): ?>
                <div class="rounded-lg border border-slate-200 p-3">
                    <form method="post" action="<?= $url('/tipos/update') ?>" class="flex flex-wrap items-end gap-2">
                        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="categoria" value="renda">
                        <input class="rounded border border-slate-300 px-3 py-1 text-sm" type="text" name="nome" value="<?= $e($item['nome']) ?>" required>
                        <button class="icon-btn icon-btn--neutral" type="submit" aria-label="Editar tipo de renda" title="Editar tipo de renda">
                            <span class="material-symbols-rounded" aria-hidden="true">edit</span>
                        </button>
                    </form>
                    <form method="post" action="<?= $url('/tipos/delete') ?>" class="mt-2" onsubmit="return confirm('Excluir tipo?')">
                        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button class="icon-btn icon-btn--danger" type="submit" aria-label="Excluir tipo de renda" title="Excluir tipo de renda">
                            <span class="material-symbols-rounded" aria-hidden="true">delete</span>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-lg font-semibold">Tipos de despesa</h2>
        <div class="mt-4 space-y-2">
            <?php if ($expenseTypes === []): ?>
                <p class="text-sm text-slate-500">Sem tipos de despesa cadastrados.</p>
            <?php endif; ?>
            <?php foreach ($expenseTypes as $item): ?>
                <div class="rounded-lg border border-slate-200 p-3">
                    <form method="post" action="<?= $url('/tipos/update') ?>" class="flex flex-wrap items-end gap-2">
                        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="categoria" value="despesa">
                        <input class="rounded border border-slate-300 px-3 py-1 text-sm" type="text" name="nome" value="<?= $e($item['nome']) ?>" required>
                        <button class="icon-btn icon-btn--neutral" type="submit" aria-label="Editar tipo de despesa" title="Editar tipo de despesa">
                            <span class="material-symbols-rounded" aria-hidden="true">edit</span>
                        </button>
                    </form>
                    <form method="post" action="<?= $url('/tipos/delete') ?>" class="mt-2" onsubmit="return confirm('Excluir tipo?')">
                        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button class="icon-btn icon-btn--danger" type="submit" aria-label="Excluir tipo de despesa" title="Excluir tipo de despesa">
                            <span class="material-symbols-rounded" aria-hidden="true">delete</span>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>
