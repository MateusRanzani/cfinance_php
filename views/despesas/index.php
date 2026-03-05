<?php $advancedFiltersAtivos = ((string) ($dataInicioSelecionada ?? '') !== '' || (string) ($dataFimSelecionada ?? '') !== ''); ?>
<header class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold tracking-tight">Despesas</h1>
</header>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <form method="get" action="<?= $url('/despesas') ?>" class="space-y-3">
        <div class="grid gap-3 md:grid-cols-4">
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-900">Mes</span>
            <input
                type="text"
                name="mes"
                value="<?= $e($mesSelecionado) ?>"
                data-month-picker="true"
                class="w-full rounded-lg border border-slate-300 px-3 py-2"
            >
        </label>
        <div class="flex items-end">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">Filtrar</button>
        </div>
        </div>
        <details class="rounded-lg border border-slate-200 p-3" <?= $advancedFiltersAtivos ? 'open' : '' ?>>
            <summary class="cursor-pointer text-sm font-semibold text-slate-700">Filtros avancados</summary>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700">Data inicial</span>
                    <input
                        type="text"
                        name="data_inicio"
                        value="<?= $e((string) ($dataInicioSelecionada ?? '')) ?>"
                        data-datepicker="true"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2"
                        placeholder="dd/mm/aaaa"
                    >
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700">Data final</span>
                    <input
                        type="text"
                        name="data_fim"
                        value="<?= $e((string) ($dataFimSelecionada ?? '')) ?>"
                        data-datepicker="true"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2"
                        placeholder="dd/mm/aaaa"
                    >
                </label>
                <div class="flex items-end">
                    <a href="<?= $url('/despesas?mes=' . urlencode((string) $mesSelecionado)) ?>" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Limpar avancado</a>
                </div>
            </div>
        </details>
    </form>
</section>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Lancamentos de despesa</h2>
        <form method="post" action="<?= $url('/fixos/aplicar') ?>">
            <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
            <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
            <input type="hidden" name="data_inicio" value="<?= $e((string) ($dataInicioSelecionada ?? '')) ?>">
            <input type="hidden" name="data_fim" value="<?= $e((string) ($dataFimSelecionada ?? '')) ?>">
            <input type="hidden" name="destino" value="despesas">
            <button class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600" type="submit">Aplicar fixos no mes</button>
        </form>
    </div>
    <form method="post" action="<?= $url('/despesas/add') ?>" class="mt-4 grid gap-3 md:grid-cols-6">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
        <input type="hidden" name="data_inicio" value="<?= $e((string) ($dataInicioSelecionada ?? '')) ?>">
            <input type="hidden" name="data_fim" value="<?= $e((string) ($dataFimSelecionada ?? '')) ?>">
        <input type="hidden" name="destino" value="despesas">
        <select class="rounded-lg border border-slate-300 px-3 py-2" name="tipo_id">
            <option value="">Tipo</option>
            <?php foreach ($expenseTypes as $type): ?>
                <option value="<?= (int) $type['id'] ?>"><?= $e($type['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="text" name="descricao" placeholder="Descricao" required>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="number" step="0.01" min="0" name="valor_planejado" placeholder="Planejado" required>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="number" step="0.01" min="0" name="valor_real" placeholder="Real" required>
        <input class="rounded-lg border border-slate-300 px-3 py-2" type="date" name="data_referencia" value="<?= $e($mesSelecionado . '-01') ?>" required>
        <button class="icon-btn icon-btn--primary" type="submit">
            <span class="material-symbols-rounded" aria-hidden="true">add</span>
            <span class="icon-btn__text">Adicionar despesa</span>
        </button>
    </form>
    <div class="app-table-wrap mt-4">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Data</th><th>Tipo</th><th>Descricao</th><th>Planejado</th><th>Real</th><th>Diferenca</th><th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($expenses === []): ?><tr><td class="text-slate-500" colspan="7">Sem despesas no mes.</td></tr><?php endif; ?>
                <?php foreach ($expenses as $item): ?>
                    <?php $diff = (float) $item['valor_planejado'] - (float) $item['valor_real']; ?>
                    <tr>
                        <td><?= $e($dateBr($item['data_referencia'])) ?></td>
                        <td><?= $e((string) ($item['tipo_nome'] ?? '-')) ?></td>
                        <td><?= $e($item['descricao']) ?></td>
                        <td>R$ <?= number_format((float) $item['valor_planejado'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format((float) $item['valor_real'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($diff, 2, ',', '.') ?></td>
                        <td class="actions-cell">
                            <div class="flex flex-wrap gap-2">
                                <form method="post" action="<?= $url('/despesas/update') ?>" class="flex flex-wrap gap-2">
                                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                    <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
                                    <input type="hidden" name="data_inicio" value="<?= $e((string) ($dataInicioSelecionada ?? '')) ?>">
            <input type="hidden" name="data_fim" value="<?= $e((string) ($dataFimSelecionada ?? '')) ?>">
                                    <input type="hidden" name="destino" value="despesas">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <select class="rounded border border-slate-300 px-2 py-1" name="tipo_id">
                                        <option value="" <?= ((int) ($item['tipo_id'] ?? 0) === 0) ? 'selected' : '' ?>>Sem tipo</option>
                                        <?php foreach ($expenseTypes as $type): ?>
                                            <option value="<?= (int) $type['id'] ?>" <?= ((int) $item['tipo_id'] === (int) $type['id']) ? 'selected' : '' ?>>
                                                <?= $e($type['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input class="rounded border border-slate-300 px-2 py-1" type="text" name="descricao" value="<?= $e($item['descricao']) ?>" required>
                                    <input class="rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_planejado" value="<?= $e($item['valor_planejado']) ?>" required>
                                    <input class="rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_real" value="<?= $e($item['valor_real']) ?>" required>
                                    <input class="rounded border border-slate-300 px-2 py-1" type="date" name="data_referencia" value="<?= $e($item['data_referencia']) ?>" required>
                                    <button class="icon-btn icon-btn--neutral" type="submit" aria-label="Editar despesa" title="Editar despesa">
                                        <span class="material-symbols-rounded" aria-hidden="true">edit</span>
                                    </button>
                                </form>
                                <form method="post" action="<?= $url('/despesas/delete') ?>" onsubmit="return confirm('Excluir despesa?')">
                                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                    <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
                                    <input type="hidden" name="data_inicio" value="<?= $e((string) ($dataInicioSelecionada ?? '')) ?>">
                                    <input type="hidden" name="data_fim" value="<?= $e((string) ($dataFimSelecionada ?? '')) ?>">
                                    <input type="hidden" name="destino" value="despesas">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <button class="icon-btn icon-btn--danger" type="submit" aria-label="Excluir despesa" title="Excluir despesa">
                                        <span class="material-symbols-rounded" aria-hidden="true">delete</span>
                                    </button>
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
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Despesas fixas mensais</h2>
        <button id="btn-adicionar-despesa-fixa-linha" class="icon-btn icon-btn--primary" type="button">
            <span class="material-symbols-rounded" aria-hidden="true">add</span>
            <span class="icon-btn__text">Adicionar fixa</span>
        </button>
    </div>
    <div class="app-table-wrap mt-4">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Tipo</th><th>Descricao</th><th>Planejado</th><th>Real</th><th>Dia</th><th>Inicio</th><th>Fim</th><th>Acoes</th>
                </tr>
            </thead>
            <tbody id="despesas-fixas-tbody">
                <?php if ($fixedExpenses === []): ?><tr id="despesas-fixas-empty-row"><td class="text-slate-500" colspan="8">Sem despesas fixas.</td></tr><?php endif; ?>
                <?php foreach ($fixedExpenses as $item): ?>
                    <tr>
                        <td><?= $e((string) ($item['tipo_nome'] ?? '-')) ?></td>
                        <td><?= $e((string) $item['descricao']) ?></td>
                        <td>R$ <?= number_format((float) $item['valor_planejado'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format((float) $item['valor_real'], 2, ',', '.') ?></td>
                        <td><?= (int) $item['dia_referencia'] ?></td>
                        <td><?= $e($dateBr((string) $item['inicio_vigencia'])) ?></td>
                        <td><?= $e((string) ($item['fim_vigencia'] ?? '') !== '' ? $dateBr((string) $item['fim_vigencia']) : 'Sem fim') ?></td>
                        <td class="actions-cell">
                            <form method="post" action="<?= $url('/fixos/despesas/delete') ?>" onsubmit="return confirm('Excluir despesa fixa?')">
                                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
                                <input type="hidden" name="data_inicio" value="<?= $e((string) ($dataInicioSelecionada ?? '')) ?>">
                                <input type="hidden" name="data_fim" value="<?= $e((string) ($dataFimSelecionada ?? '')) ?>">
                                <input type="hidden" name="destino" value="despesas">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button class="icon-btn icon-btn--danger" type="submit" aria-label="Excluir despesa fixa" title="Excluir despesa fixa">
                                    <span class="material-symbols-rounded" aria-hidden="true">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <template id="nova-despesa-fixa-row-template">
        <tr class="expense-fixed-new-row">
            <td>
                <select class="w-full rounded border border-slate-300 px-2 py-1" name="tipo_id" form="__FORM_ID__">
                    <option value="">Tipo</option>
                    <?php foreach ($expenseTypes as $type): ?>
                        <option value="<?= (int) $type['id'] ?>"><?= $e($type['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="text" name="descricao" required form="__FORM_ID__"></td>
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_planejado" required form="__FORM_ID__"></td>
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_real" required form="__FORM_ID__"></td>
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="number" min="1" max="31" name="dia_referencia" value="10" required form="__FORM_ID__"></td>
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="date" name="inicio_vigencia" value="<?= $e($mesSelecionado . '-01') ?>" required form="__FORM_ID__"></td>
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="date" name="fim_vigencia" form="__FORM_ID__"></td>
            <td class="actions-cell">
                <form id="__FORM_ID__" method="post" action="<?= $url('/fixos/despesas/add') ?>" class="flex flex-wrap gap-2">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
                    <input type="hidden" name="data_inicio" value="<?= $e((string) ($dataInicioSelecionada ?? '')) ?>">
                    <input type="hidden" name="data_fim" value="<?= $e((string) ($dataFimSelecionada ?? '')) ?>">
                    <input type="hidden" name="destino" value="despesas">
                    <button class="icon-btn icon-btn--neutral" type="submit" aria-label="Salvar despesa fixa" title="Salvar despesa fixa">
                        <span class="material-symbols-rounded" aria-hidden="true">edit</span>
                    </button>
                    <button class="btn-cancel-nova-despesa-fixa icon-btn icon-btn--muted" type="button" aria-label="Cancelar despesa fixa" title="Cancelar despesa fixa">
                        <span class="material-symbols-rounded" aria-hidden="true">close</span>
                    </button>
                </form>
            </td>
        </tr>
    </template>
</section>

<script>
(() => {
    const addButton = document.getElementById('btn-adicionar-despesa-fixa-linha');
    const tbody = document.getElementById('despesas-fixas-tbody');
    const template = document.getElementById('nova-despesa-fixa-row-template');
    if (!addButton || !tbody || !template) return;

    const toggleEmptyState = () => {
        const empty = document.getElementById('despesas-fixas-empty-row');
        const hasRows = tbody.querySelector('tr:not(#despesas-fixas-empty-row)');
        if (!empty) return;
        empty.style.display = hasRows ? 'none' : '';
    };

    const bindCancel = (row) => {
        const cancelBtn = row.querySelector('.btn-cancel-nova-despesa-fixa');
        if (!cancelBtn) return;
        cancelBtn.addEventListener('click', () => {
            row.remove();
            toggleEmptyState();
        });
    };

    addButton.addEventListener('click', () => {
        const formId = 'nova-despesa-fixa-form-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
        const html = template.innerHTML.replaceAll('__FORM_ID__', formId);
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html.trim();
        const newRow = wrapper.querySelector('tr');
        if (!newRow) return;
        tbody.prepend(newRow);
        bindCancel(newRow);
        if (typeof window.initDatePickers === 'function') {
            window.initDatePickers(newRow);
        }
        toggleEmptyState();
    });
})();
</script>


