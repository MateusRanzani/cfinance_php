<?php
$success = (string) ($flash['success'] ?? '');
$error = (string) ($flash['error'] ?? '');
$perfil = (($user['role'] ?? '') === 'admin') ? 'Administrador' : 'Padrao';

$nomesMeses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Marco',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro',
];
$mesAtualLabel = ($nomesMeses[$month] ?? (string) $month) . ' de ' . $year;
$formatarMesAno = static function (string $yearMonth, array $nomesMeses): string {
    $parts = explode('-', $yearMonth);
    if (count($parts) !== 2) {
        return $yearMonth;
    }
    $ano = (int) $parts[0];
    $mes = (int) $parts[1];
    return ($nomesMeses[$mes] ?? (string) $mes) . ' de ' . $ano;
};

$totIncomePlanned = (float) ($totals['income_planned'] ?? 0);
$totIncomeReal = (float) ($totals['income_real'] ?? 0);
$totExpensePlanned = (float) ($totals['expense_planned'] ?? 0);
$totExpenseReal = (float) ($totals['expense_real'] ?? 0);
$totIncomeDifference = $totIncomePlanned - $totIncomeReal;
$totExpenseDifference = $totExpensePlanned - $totExpenseReal;
$finalBalance = (float) ($totals['final_balance'] ?? 0);
$saldoStatus = $finalBalance > 0 ? 'Positivo' : ($finalBalance < 0 ? 'Negativo' : 'Neutro');
$saldoStatusClass = $finalBalance > 0 ? 'text-emerald-700' : ($finalBalance < 0 ? 'text-rose-700' : 'text-slate-600');
?>
<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <div class="mb-4">
        <h1 class="text-2xl font-bold tracking-tight">Dashboard - <?= $e($mesAtualLabel) ?></h1>
        <p class="text-sm text-slate-600"><?= $e($perfil) ?></p>
    </div>

    <form method="get" action="<?= $url('/dashboard') ?>" class="grid gap-3 md:grid-cols-4">
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Mes</span>
            <select name="mes" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                <?php foreach ($availableMonths as $monthOption): ?>
                    <option value="<?= $e($monthOption) ?>" <?= ((string) $monthOption === (string) $mesSelecionado) ? 'selected' : '' ?>>
                        <?= $e($formatarMesAno((string) $monthOption, $nomesMeses)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="flex items-end">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">Filtrar</button>
        </div>
    </form>
</section>

<?php if ($success !== ''): ?>
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700"><?= $e($success) ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"><?= $e($error) ?></div>
<?php endif; ?>

<?php if ($copySuggestion !== null): ?>
    <section class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
        <p class="text-sm text-amber-900">Nao ha registros em <?= $e($mesAtualLabel) ?>. Deseja copiar a estrutura de <?= $e((string) $copySuggestion['label']) ?>?</p>
        <form method="post" action="<?= $url('/mes/copiar-anterior') ?>" class="mt-3">
            <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
            <input type="hidden" name="mes" value="<?= $e($mesSelecionado) ?>">
            <input type="hidden" name="destino" value="dashboard">
            <button class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500" type="submit">Copiar estrutura do mes anterior</button>
        </form>
    </section>
<?php endif; ?>

<section class="mb-2 grid gap-3 md:grid-cols-2">
    <article class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
        <h3 class="text-xs text-emerald-700">Renda</h3>
        <p class="mt-1 text-lg font-bold text-emerald-700">Real: R$ <?= number_format($totIncomeReal, 2, ',', '.') ?></p>
        <p class="mt-1 text-xs text-emerald-700">Planejada: R$ <?= number_format($totIncomePlanned, 2, ',', '.') ?></p>
        <p class="mt-1 text-xs text-emerald-700">Diferenca: R$ <?= number_format($totIncomeDifference, 2, ',', '.') ?></p>
    </article>
    <article class="rounded-xl border border-orange-200 bg-orange-50 p-4 shadow-sm">
        <h3 class="text-xs text-orange-700">Despesa</h3>
        <p class="mt-1 text-lg font-bold text-orange-900">Real: R$ <?= number_format($totExpenseReal, 2, ',', '.') ?></p>
        <p class="mt-1 text-xs text-orange-700">Planejada: R$ <?= number_format($totExpensePlanned, 2, ',', '.') ?></p>
        <p class="mt-1 text-xs text-orange-700">Diferenca: R$ <?= number_format($totExpenseDifference, 2, ',', '.') ?></p>
    </article>
</section>

<section class="mb-6">
    <div class="w-full border-b border-slate-300 pb-2 text-right">
            <p class="text-xs text-slate-600">Saldo final: <span class="font-semibold <?= $saldoStatusClass ?>"><?= $e($saldoStatus) ?></span></p>
            <p class="text-xl font-bold text-slate-900">R$ <?= number_format($finalBalance, 2, ',', '.') ?></p>
    </div>
</section>

<section class="grid gap-6 lg:grid-cols-2">
    <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-lg font-semibold">Despesas do mes</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[420px] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left">
                        <th class="p-2">Data</th>
                        <th class="p-2">Descricao</th>
                        <th class="p-2">Planejado</th>
                        <th class="p-2">Real</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($expenses === []): ?>
                        <tr><td class="p-2 text-slate-500" colspan="4">Sem despesas no mes.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($expenses as $item): ?>
                        <tr class="border-b border-slate-100">
                            <td class="p-2"><?= $e($item['data_referencia']) ?></td>
                            <td class="p-2"><?= $e($item['descricao']) ?></td>
                            <td class="p-2">R$ <?= number_format((float) $item['valor_planejado'], 2, ',', '.') ?></td>
                            <td class="p-2">R$ <?= number_format((float) $item['valor_real'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-lg font-semibold">Rendas do mes</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[420px] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left">
                        <th class="p-2">Data</th>
                        <th class="p-2">Descricao</th>
                        <th class="p-2">Planejado</th>
                        <th class="p-2">Real</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($incomes === []): ?>
                        <tr><td class="p-2 text-slate-500" colspan="4">Sem rendas no mes.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($incomes as $item): ?>
                        <tr class="border-b border-slate-100">
                            <td class="p-2"><?= $e($item['data_referencia']) ?></td>
                            <td class="p-2"><?= $e($item['descricao']) ?></td>
                            <td class="p-2">R$ <?= number_format((float) $item['valor_planejado'], 2, ',', '.') ?></td>
                            <td class="p-2">R$ <?= number_format((float) $item['valor_real'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
