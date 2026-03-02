<?php
$success = (string) ($flash['success'] ?? '');
$error = (string) ($flash['error'] ?? '');
?>
<header class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold tracking-tight">Rendas</h1>
</header>

<?php if ($success !== ''): ?>
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700"><?= $e($success) ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"><?= $e($error) ?></div>
<?php endif; ?>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <form method="get" action="<?= $url('/rendas') ?>" class="grid gap-3 md:grid-cols-4">
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-900">Mes</span>
            <select name="mes" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                <?php foreach ($availableMonths as $monthOption): ?>
                    <?php $parts = explode('-', (string) $monthOption); $optLabel = (count($parts) === 2) ? sprintf('%02d/%04d', (int) $parts[1], (int) $parts[0]) : (string) $monthOption; ?>
                    <option value="<?= $e($monthOption) ?>" <?= ((string) $monthOption === (string) $mesSelecionado) ? 'selected' : '' ?>><?= $e($optLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="flex items-end">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">Filtrar</button>
        </div>
    </form>
</section>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Lancamentos de renda</h2>
        <form method="post" action="<?= $url('/fixos/aplicar') ?>">
            <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
            <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
            <input type="hidden" name="destino" value="rendas">
            <button class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600" type="submit">Aplicar fixos no mes</button>
        </form>
    </div>
    <form method="post" action="<?= $url('/rendas/add') ?>" class="mt-4 grid gap-3 md:grid-cols-6">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
        <input type="hidden" name="destino" value="rendas">
        <select class="rounded-lg border border-slate-300 px-3 py-2" name="tipo_id" required>
            <option value="">Tipo</option>
            <?php foreach ($incomeTypes as $type): ?>
                <option value="<?= (int) $type['id'] ?>"><?= $e($type['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="text" name="descricao" placeholder="Descricao" required>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="number" step="0.01" min="0" name="valor_planejado" placeholder="Planejado" required>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="number" step="0.01" min="0" name="valor_real" placeholder="Real" required>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="date" name="data_referencia" value="<?= $e($mesSelecionado . '-01') ?>" required>
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">Adicionar renda</button>
    </form>
    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[980px] border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left">
                    <th class="p-2">Data</th><th class="p-2">Tipo</th><th class="p-2">Descricao</th><th class="p-2">Planejado</th><th class="p-2">Real</th><th class="p-2">Diferenca</th><th class="p-2">Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($incomes === []): ?><tr><td class="p-2 text-slate-500" colspan="7">Sem rendas no mes.</td></tr><?php endif; ?>
                <?php foreach ($incomes as $item): ?>
                    <?php $diff = (float) $item['valor_planejado'] - (float) $item['valor_real']; ?>
                    <tr class="border-b border-slate-100 align-top">
                        <td class="p-2"><?= $e($item['data_referencia']) ?></td>
                        <td class="p-2"><?= $e((string) ($item['tipo_nome'] ?? '-')) ?></td>
                        <td class="p-2"><?= $e($item['descricao']) ?></td>
                        <td class="p-2">R$ <?= number_format((float) $item['valor_planejado'], 2, ',', '.') ?></td>
                        <td class="p-2">R$ <?= number_format((float) $item['valor_real'], 2, ',', '.') ?></td>
                        <td class="p-2">R$ <?= number_format($diff, 2, ',', '.') ?></td>
                        <td class="p-2">
                            <div class="flex flex-wrap gap-2">
                                <form method="post" action="<?= $url('/rendas/update') ?>" class="flex flex-wrap gap-2">
                                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                    <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
                                    <input type="hidden" name="destino" value="rendas">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <select class="rounded border border-slate-300 px-2 py-1" name="tipo_id" required>
                                        <?php foreach ($incomeTypes as $type): ?>
                                            <option value="<?= (int) $type['id'] ?>" <?= ((int) $item['tipo_id'] === (int) $type['id']) ? 'selected' : '' ?>>
                                                <?= $e($type['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input class="rounded border border-slate-300 px-2 py-1" type="text" name="descricao" value="<?= $e($item['descricao']) ?>" required>
                                    <input class="rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_planejado" value="<?= $e($item['valor_planejado']) ?>" required>
                                    <input class="rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_real" value="<?= $e($item['valor_real']) ?>" required>
                                    <input class="rounded border border-slate-300 px-2 py-1" type="date" name="data_referencia" value="<?= $e($item['data_referencia']) ?>" required>
                                    <button class="rounded bg-slate-800 px-3 py-1 text-white hover:bg-slate-700" type="submit">Salvar</button>
                                </form>
                                <form method="post" action="<?= $url('/rendas/delete') ?>" onsubmit="return confirm('Excluir renda?')">
                                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                    <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
                                    <input type="hidden" name="destino" value="rendas">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <button class="rounded bg-rose-600 px-3 py-1 text-white hover:bg-rose-500" type="submit">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold">Rendas fixas mensais</h2>
    <form method="post" action="<?= $url('/fixos/rendas/add') ?>" class="mt-4 grid gap-3 md:grid-cols-6">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
        <input type="hidden" name="destino" value="rendas">
        <select class="rounded-lg border border-slate-300 px-3 py-2" name="tipo_id" required>
            <option value="">Tipo</option>
            <?php foreach ($incomeTypes as $type): ?>
                <option value="<?= (int) $type['id'] ?>"><?= $e($type['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="text" name="descricao" placeholder="Descricao" required>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="number" step="0.01" min="0" name="valor_planejado" placeholder="Planejado" required>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="number" step="0.01" min="0" name="valor_real" placeholder="Real" required>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="number" min="1" max="31" name="dia_referencia" placeholder="Dia" value="5" required>
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">Adicionar fixa</button>
    </form>
    <div class="mt-4 space-y-2">
        <?php if ($fixedIncomes === []): ?><p class="text-sm text-slate-500">Sem rendas fixas.</p><?php endif; ?>
        <?php foreach ($fixedIncomes as $item): ?>
            <form method="post" action="<?= $url('/fixos/rendas/delete') ?>" class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm">
                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
                <input type="hidden" name="destino" value="rendas">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <span class="font-medium"><?= $e($item['descricao']) ?></span>
                <span>Tipo: <?= $e((string) ($item['tipo_nome'] ?? '-')) ?></span>
                <span>Planejado: R$ <?= number_format((float) $item['valor_planejado'], 2, ',', '.') ?></span>
                <span>Real: R$ <?= number_format((float) $item['valor_real'], 2, ',', '.') ?></span>
                <span>Dia: <?= (int) $item['dia_referencia'] ?></span>
                <button class="ml-auto rounded bg-rose-600 px-3 py-1 text-white hover:bg-rose-500" type="submit">Excluir</button>
            </form>
        <?php endforeach; ?>
    </div>
</section>
