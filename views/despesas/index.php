<?php $advancedFiltersAtivos = ((string) ($dataInicioSelecionada ?? '') !== '' || (string) ($dataFimSelecionada ?? '') !== ''); ?>
<style>
    .expense-red-table {
        background: #fff1f2;
        border-color: #fecdd3;
    }
    .expense-red-table .app-table thead th {
        background: #ffe4e6;
    }
    .expense-red-table .app-table tbody tr:nth-child(even) td {
        background: #fff5f6;
    }
    .expense-red-table .app-table tbody tr:hover td {
        background: #ffe4e6;
    }
</style>
<header class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold tracking-tight">Despesas</h1>
</header>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <form method="get" action="<?= $url('/despesas') ?>" class="space-y-3">
        <div class="grid gap-3 md:grid-cols-3">
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
        <div class="flex flex-wrap items-center gap-2">
            <button id="btn-adicionar-despesa-linha" class="icon-btn icon-btn--primary" type="button">
                <span class="material-symbols-rounded" aria-hidden="true">add</span>
                <span class="icon-btn__text">Adicionar despesa</span>
            </button>
            <button id="btn-adicionar-despesa-fixa-linha" class="icon-btn icon-btn--primary" type="button">
                <span class="material-symbols-rounded" aria-hidden="true">add</span>
                <span class="icon-btn__text">Adicionar fixa</span>
            </button>
        </div>
    </div>
    <div class="mt-4">
        <h3 class="text-base font-semibold">Despesas fixas mensais</h3>
        <div class="app-table-wrap expense-red-table mt-3">
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
    </div>
    <div class="mt-4 pt-2">
        <h3 class="text-base font-semibold">Despesas do mes</h3>
    </div>
    <div class="app-table-wrap mt-3">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Data</th><th>Tipo</th><th>Descricao</th><th>Planejado</th><th>Real</th><th>Diferenca</th><th>Acoes</th>
                </tr>
            </thead>
            <tbody id="despesas-tbody">
                <?php if ($expenses === []): ?><tr id="despesas-empty-row"><td class="text-slate-500" colspan="7">Sem despesas no mes.</td></tr><?php endif; ?>
                <?php foreach ($expenses as $item): ?>
                    <?php $diff = (float) $item['valor_planejado'] - (float) $item['valor_real']; ?>
                    <tr
                        class="expense-data-row"
                        data-id="<?= (int) $item['id'] ?>"
                        data-data-referencia="<?= $e((string) $item['data_referencia']) ?>"
                        data-tipo-id="<?= (int) ($item['tipo_id'] ?? 0) ?>"
                        data-descricao="<?= $e((string) $item['descricao']) ?>"
                        data-valor-planejado="<?= $e((string) $item['valor_planejado']) ?>"
                        data-valor-real="<?= $e((string) $item['valor_real']) ?>"
                    >
                        <td><?= $e($dateBr($item['data_referencia'])) ?></td>
                        <td><?= $e((string) ($item['tipo_nome'] ?? '-')) ?></td>
                        <td><?= $e($item['descricao']) ?></td>
                        <td>R$ <?= number_format((float) $item['valor_planejado'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format((float) $item['valor_real'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($diff, 2, ',', '.') ?></td>
                        <td class="actions-cell">
                            <div class="flex flex-wrap gap-2">
                                <button class="btn-editar-despesa icon-btn icon-btn--neutral" type="button" aria-label="Editar despesa" title="Editar despesa">
                                    <span class="material-symbols-rounded" aria-hidden="true">edit</span>
                                </button>
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

    <?php
    $expenseTypesJson = (string) json_encode(
        array_map(
            static fn (array $t): array => ['id' => (int) $t['id'], 'nome' => (string) $t['nome']],
            $expenseTypes
        ),
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
    ?>
    <script id="expense-types-json" type="application/json"><?= $expenseTypesJson ?></script>
    <template id="nova-despesa-row-template">
        <tr class="expense-new-row">
            <td>
                <input class="w-full rounded border border-slate-300 px-2 py-1" type="date" name="data_referencia" value="<?= $e($mesSelecionado . '-01') ?>" required form="__FORM_ID__">
            </td>
            <td>
                <select class="w-full rounded border border-slate-300 px-2 py-1" name="tipo_id" form="__FORM_ID__">
                    <option value="">Tipo</option>
                    <?php foreach ($expenseTypes as $type): ?>
                        <option value="<?= (int) $type['id'] ?>"><?= $e($type['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input class="w-full rounded border border-slate-300 px-2 py-1" type="text" name="descricao" placeholder="Descricao" required form="__FORM_ID__">
            </td>
            <td>
                <input class="w-full rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_planejado" required form="__FORM_ID__">
            </td>
            <td>
                <input class="w-full rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_real" required form="__FORM_ID__">
            </td>
            <td class="text-slate-400">-</td>
            <td class="actions-cell">
                <form id="__FORM_ID__" method="post" action="<?= $url('/despesas/add') ?>" class="flex flex-wrap gap-2">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
                    <input type="hidden" name="data_inicio" value="<?= $e((string) ($dataInicioSelecionada ?? '')) ?>">
                    <input type="hidden" name="data_fim" value="<?= $e((string) ($dataFimSelecionada ?? '')) ?>">
                    <input type="hidden" name="destino" value="despesas">
                    <button class="icon-btn icon-btn--neutral" type="submit" aria-label="Salvar nova despesa" title="Salvar nova despesa">
                        <span class="material-symbols-rounded" aria-hidden="true">save</span>
                        <span class="icon-btn__text">Salvar</span>
                    </button>
                    <button class="icon-btn icon-btn--danger btn-cancel-nova-despesa" type="button" aria-label="Cancelar nova despesa" title="Cancelar nova despesa">
                        <span class="material-symbols-rounded" aria-hidden="true">delete</span>
                    </button>
                </form>
            </td>
        </tr>
    </template>
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
                        <span class="material-symbols-rounded" aria-hidden="true">save</span>
                        <span class="icon-btn__text">Salvar</span>
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
    const addButton = document.getElementById('btn-adicionar-despesa-linha');
    const tbody = document.getElementById('despesas-tbody');
    const template = document.getElementById('nova-despesa-row-template');
    const expenseTypesNode = document.getElementById('expense-types-json');
    let expenseTypes = [];
    try {
        expenseTypes = expenseTypesNode ? JSON.parse(expenseTypesNode.textContent || '[]') : [];
    } catch (e) {
        expenseTypes = [];
    }
    if (!addButton || !tbody || !template) return;

    const toggleEmptyState = () => {
        const empty = document.getElementById('despesas-empty-row');
        const hasDataRows = tbody.querySelector('.expense-data-row, .expense-new-row');
        if (!empty) return;
        empty.style.display = hasDataRows ? 'none' : '';
    };

    const escAttr = (value) =>
        String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('"', '&quot;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;');

    const buildTypeOptions = (selectedId) => {
        let html = '<option value="">Tipo</option>';
        expenseTypes.forEach((type) => {
            const selected = Number(selectedId) === Number(type.id) ? ' selected' : '';
            html += `<option value="${Number(type.id)}"${selected}>${escAttr(type.nome)}</option>`;
        });
        return html;
    };

    const bindCancel = (row) => {
        const cancelBtn = row.querySelector('.btn-cancel-nova-despesa');
        if (!cancelBtn) return;
        cancelBtn.addEventListener('click', () => {
            row.remove();
            toggleEmptyState();
        });
    };

    addButton.addEventListener('click', () => {
        const formId = 'nova-despesa-form-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
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

    tbody.addEventListener('click', (event) => {
        const editBtn = event.target.closest('.btn-editar-despesa');
        if (!editBtn) return;

        const row = editBtn.closest('.expense-data-row');
        if (!row) return;

        const originalRow = row.cloneNode(true);
        const formId = 'editar-despesa-form-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

        const editRow = document.createElement('tr');
        editRow.className = 'expense-edit-row';
        editRow.innerHTML = `
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="date" name="data_referencia" value="${escAttr(row.dataset.dataReferencia || '')}" required form="${formId}"></td>
            <td><select class="w-full rounded border border-slate-300 px-2 py-1" name="tipo_id" form="${formId}">${buildTypeOptions(row.dataset.tipoId || '')}</select></td>
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="text" name="descricao" value="${escAttr(row.dataset.descricao || '')}" required form="${formId}"></td>
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_planejado" value="${escAttr(row.dataset.valorPlanejado || '0')}" required form="${formId}"></td>
            <td><input class="w-full rounded border border-slate-300 px-2 py-1" type="number" step="0.01" min="0" name="valor_real" value="${escAttr(row.dataset.valorReal || '0')}" required form="${formId}"></td>
            <td class="text-slate-400">-</td>
            <td class="actions-cell">
                <form id="${formId}" method="post" action="<?= $url('/despesas/update') ?>" class="flex flex-wrap gap-2">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
                    <input type="hidden" name="data_inicio" value="<?= $e((string) ($dataInicioSelecionada ?? '')) ?>">
                    <input type="hidden" name="data_fim" value="<?= $e((string) ($dataFimSelecionada ?? '')) ?>">
                    <input type="hidden" name="destino" value="despesas">
                    <input type="hidden" name="id" value="${Number(row.dataset.id || 0)}">
                    <button class="icon-btn icon-btn--neutral" type="submit" aria-label="Salvar edicao de despesa" title="Salvar edicao de despesa">
                        <span class="material-symbols-rounded" aria-hidden="true">save</span>
                        <span class="icon-btn__text">Salvar</span>
                    </button>
                    <button class="btn-cancel-edicao-despesa icon-btn icon-btn--muted" type="button" aria-label="Cancelar edicao de despesa" title="Cancelar edicao de despesa">
                        <span class="material-symbols-rounded" aria-hidden="true">close</span>
                    </button>
                </form>
            </td>
        `;

        row.replaceWith(editRow);
        if (typeof window.initDatePickers === 'function') {
            window.initDatePickers(editRow);
        }

        const cancelEditBtn = editRow.querySelector('.btn-cancel-edicao-despesa');
        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', () => {
                editRow.replaceWith(originalRow);
                toggleEmptyState();
            });
        }
    });
})();
</script>

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
