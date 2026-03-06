<header class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold tracking-tight">Metas financeiras</h1>
    <p class="mt-1 text-sm text-slate-600">Exemplo: casamento, entrada de casa, viagem ou quitacao de divida.</p>
</header>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold">Nova meta</h2>
    <form method="post" action="<?= $url('/metas/add') ?>" class="mt-4 grid gap-3 md:grid-cols-3">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <input type="hidden" name="mes" value="<?= $e((string) ($mesSelecionado ?? date('Y-m'))) ?>">
        <input type="hidden" name="destino" value="metas">

        <label class="block text-sm md:col-span-2">
            <span class="mb-1 block font-medium text-slate-700">Nome</span>
            <input
                type="text"
                name="nome"
                required
                placeholder="Casamento"
                class="w-full rounded-lg border border-slate-300 px-3 py-2"
            >
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Prioridade</span>
            <select name="prioridade" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="alta">Alta</option>
                <option value="media" selected>Media</option>
                <option value="baixa">Baixa</option>
            </select>
        </label>

        <label class="block text-sm md:col-span-3">
            <span class="mb-1 block font-medium text-slate-700">Descricao (opcional)</span>
            <input
                type="text"
                name="descricao"
                placeholder="Reserva para buffet, fotografia e lua de mel"
                class="w-full rounded-lg border border-slate-300 px-3 py-2"
            >
        </label>

        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Valor alvo (R$)</span>
            <input type="number" step="0.01" min="0.01" name="valor_alvo" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Valor atual (R$)</span>
            <input type="number" step="0.01" min="0" name="valor_atual" value="0" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Aporte mensal planejado (R$)</span>
            <input type="number" step="0.01" min="0" name="aporte_mensal_planejado" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </label>

        <label class="block text-sm md:col-span-2">
            <span class="mb-1 block font-medium text-slate-700">Prazo</span>
            <input type="date" name="prazo" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </label>
        <div class="flex items-end">
            <button class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">
                Cadastrar meta
            </button>
        </div>
    </form>
</section>

<section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold">Metas cadastradas</h2>
    <div class="app-table-wrap mt-4">
        <table class="app-table min-w-[860px]">
            <thead>
                <tr>
                    <th>Meta</th>
                    <th>Prioridade</th>
                    <th>Progresso</th>
                    <th>Prazo</th>
                    <th>Status</th>
                    <th>Projecao</th>
                    <th>Aporte</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (($goals ?? []) === []): ?>
                    <tr>
                        <td class="text-slate-500" colspan="8">Nenhuma meta cadastrada.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach (($goals ?? []) as $goal): ?>
                    <?php
                    $priorityRaw = (string) ($goal['prioridade'] ?? 'media');
                    $priorityClass = $priorityRaw === 'alta'
                        ? 'bg-rose-100 text-rose-700'
                        : ($priorityRaw === 'baixa' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700');
                    $statusRaw = (string) ($goal['status'] ?? 'Em andamento');
                    $statusClass = $statusRaw === 'Concluida'
                        ? 'text-emerald-700'
                        : ($statusRaw === 'Atrasada' ? 'text-rose-700' : 'text-slate-700');
                    $progress = (float) ($goal['percentual_concluido'] ?? 0);
                    ?>
                    <tr>
                        <td>
                            <p class="font-semibold text-slate-900"><?= $e((string) $goal['nome']) ?></p>
                            <?php if (trim((string) ($goal['descricao'] ?? '')) !== ''): ?>
                                <p class="text-xs text-slate-600"><?= $e((string) $goal['descricao']) ?></p>
                            <?php endif; ?>
                            <p class="mt-1 text-xs text-slate-600">
                                Atual: R$ <?= number_format((float) $goal['valor_atual'], 2, ',', '.') ?> /
                                Alvo: R$ <?= number_format((float) $goal['valor_alvo'], 2, ',', '.') ?>
                            </p>
                            <p class="text-xs text-slate-600">
                                Faltam: R$ <?= number_format((float) ($goal['valor_faltante'] ?? 0), 2, ',', '.') ?>
                            </p>
                        </td>
                        <td>
                            <span class="inline-block rounded-full px-2 py-1 text-xs font-semibold <?= $priorityClass ?>">
                                <?= $e(ucfirst($priorityRaw)) ?>
                            </span>
                        </td>
                        <td>
                            <div class="h-2.5 w-44 rounded-full bg-slate-200">
                                <div class="h-2.5 rounded-full bg-slate-900" style="width: <?= $e((string) min(100, max(0, $progress))) ?>%"></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-700"><?= number_format($progress, 2, ',', '.') ?>%</p>
                        </td>
                        <td><?= $e($dateBr((string) $goal['prazo'])) ?></td>
                        <td class="font-semibold <?= $statusClass ?>"><?= $e($statusRaw) ?></td>
                        <td>
                            <?php if (!empty($goal['projecao_conclusao'])): ?>
                                <p class="text-sm text-slate-800"><?= $e($dateBr((string) $goal['projecao_conclusao'])) ?></p>
                            <?php else: ?>
                                <p class="text-sm text-slate-500">Sem projecao</p>
                            <?php endif; ?>
                            <p class="text-xs text-slate-500">
                                Aporte plano: R$ <?= number_format((float) ($goal['aporte_mensal_planejado'] ?? 0), 2, ',', '.') ?>/mes
                            </p>
                        </td>
                        <td>
                            <form method="post" action="<?= $url('/metas/aporte') ?>" class="flex items-center gap-2">
                                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                <input type="hidden" name="mes" value="<?= $e((string) ($mesSelecionado ?? date('Y-m'))) ?>">
                                <input type="hidden" name="destino" value="metas">
                                <input type="hidden" name="id" value="<?= (int) $goal['id'] ?>">
                                <input type="number" step="0.01" min="0.01" name="valor_aporte" placeholder="0,00" required class="w-24 rounded-lg border border-slate-300 px-2 py-1">
                                <button class="rounded-lg bg-slate-900 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-700" type="submit">Aportar</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="<?= $url('/metas/delete') ?>" onsubmit="return confirm('Excluir meta?')">
                                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                <input type="hidden" name="mes" value="<?= $e((string) ($mesSelecionado ?? date('Y-m'))) ?>">
                                <input type="hidden" name="destino" value="metas">
                                <input type="hidden" name="id" value="<?= (int) $goal['id'] ?>">
                                <button class="icon-btn icon-btn--danger" type="submit" aria-label="Excluir meta" title="Excluir meta">
                                    <span class="material-symbols-rounded" aria-hidden="true">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
