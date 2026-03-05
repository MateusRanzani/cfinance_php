<?php

declare(strict_types=1);

namespace App\Controllers;

final class DashboardController extends BaseController
{
    public function index(): void
    {
        [$user, $targetUserId, $year, $month, $selectedMonth, $startDate, $endDate] = $this->resolveContextFromRequest();
        $dashboard = $this->finance->buildDashboardData($user, $targetUserId, $year, $month, $startDate, $endDate);

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'activeMenu' => 'dashboard',
            'user' => $user,
            'targetUserId' => $targetUserId,
            'year' => $year,
            'month' => $month,
            'mesSelecionado' => $selectedMonth,
            'dataInicioSelecionada' => $startDate ?? '',
            'dataFimSelecionada' => $endDate ?? '',
            'users' => ($user['role'] ?? '') === 'admin' ? $this->auth->listUsers() : [],
            'flash' => $this->consumeFlash(),
            'csrf' => $this->csrfToken(),
            'availableMonths' => $dashboard['available_months'],
            'totals' => $dashboard['totals'],
            'incomes' => $dashboard['incomes'],
            'expenses' => $dashboard['expenses'],
            'fixedIncomesActive' => $dashboard['fixed_incomes_active'],
            'fixedExpensesActive' => $dashboard['fixed_expenses_active'],
            'copySuggestion' => $dashboard['copy_suggestion'],
        ]);
    }

    public function incomesPage(): void
    {
        [$user, $targetUserId, $year, $month, $selectedMonth, $startDate, $endDate] = $this->resolveContextFromRequest();
        $dashboard = $this->finance->buildDashboardData($user, $targetUserId, $year, $month, $startDate, $endDate);

        $this->render('rendas/index', [
            'title' => 'Rendas',
            'activeMenu' => 'rendas',
            'user' => $user,
            'targetUserId' => $targetUserId,
            'mesSelecionado' => $selectedMonth,
            'dataInicioSelecionada' => $startDate ?? '',
            'dataFimSelecionada' => $endDate ?? '',
            'users' => ($user['role'] ?? '') === 'admin' ? $this->auth->listUsers() : [],
            'flash' => $this->consumeFlash(),
            'csrf' => $this->csrfToken(),
            'availableMonths' => $dashboard['available_months'],
            'incomes' => $dashboard['incomes'],
            'fixedIncomes' => $dashboard['fixed_incomes_active'],
            'incomeTypes' => $dashboard['income_types'],
        ]);
    }

    public function expensesPage(): void
    {
        [$user, $targetUserId, $year, $month, $selectedMonth, $startDate, $endDate] = $this->resolveContextFromRequest();
        $dashboard = $this->finance->buildDashboardData($user, $targetUserId, $year, $month, $startDate, $endDate);

        $this->render('despesas/index', [
            'title' => 'Despesas',
            'activeMenu' => 'despesas',
            'user' => $user,
            'targetUserId' => $targetUserId,
            'mesSelecionado' => $selectedMonth,
            'dataInicioSelecionada' => $startDate ?? '',
            'dataFimSelecionada' => $endDate ?? '',
            'users' => ($user['role'] ?? '') === 'admin' ? $this->auth->listUsers() : [],
            'flash' => $this->consumeFlash(),
            'csrf' => $this->csrfToken(),
            'availableMonths' => $dashboard['available_months'],
            'expenses' => $dashboard['expenses'],
            'fixedExpenses' => $dashboard['fixed_expenses_active'],
            'expenseTypes' => $dashboard['expense_types'],
        ]);
    }

    public function typesPage(): void
    {
        $user = $this->requireLogin();
        $types = $this->finance->listMovementTypes();

        $this->render('tipos/index', [
            'title' => 'Tipos',
            'activeMenu' => 'tipos',
            'user' => $user,
            'flash' => $this->consumeFlash(),
            'csrf' => $this->csrfToken(),
            'incomeTypes' => $types['renda'],
            'expenseTypes' => $types['despesa'],
        ]);
    }

    public function addIncome(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $planned = (float) ($_POST['valor_planejado'] ?? 0);
            $this->finance->createIncome($user, $targetUserId, [
                'descricao' => (string) ($_POST['descricao'] ?? ''),
                'tipo_id' => (int) ($_POST['tipo_id'] ?? 0),
                'valor_planejado' => $planned,
                'valor_real' => $this->resolveRealValue($planned, $_POST['valor_real'] ?? null),
                'data_referencia' => (string) ($_POST['data_referencia'] ?? ''),
            ]);
            $this->flash('success', 'Renda cadastrada.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'rendas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function updateIncome(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $planned = (float) ($_POST['valor_planejado'] ?? 0);
            $this->finance->updateIncome($user, (int) ($_POST['id'] ?? 0), [
                'descricao' => (string) ($_POST['descricao'] ?? ''),
                'tipo_id' => (int) ($_POST['tipo_id'] ?? 0),
                'valor_planejado' => $planned,
                'valor_real' => $this->resolveRealValue($planned, $_POST['valor_real'] ?? null),
                'data_referencia' => (string) ($_POST['data_referencia'] ?? ''),
            ]);
            $this->flash('success', 'Renda atualizada.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'rendas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function deleteIncome(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $this->finance->deleteIncome($user, (int) ($_POST['id'] ?? 0));
            $this->flash('success', 'Renda removida.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'rendas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function addExpense(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $planned = (float) ($_POST['valor_planejado'] ?? 0);
            $this->finance->createExpense($user, $targetUserId, [
                'descricao' => (string) ($_POST['descricao'] ?? ''),
                'tipo_id' => (int) ($_POST['tipo_id'] ?? 0),
                'valor_planejado' => $planned,
                'valor_real' => $this->resolveRealValue($planned, $_POST['valor_real'] ?? null),
                'data_referencia' => (string) ($_POST['data_referencia'] ?? ''),
            ]);
            $this->flash('success', 'Despesa cadastrada.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'despesas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function updateExpense(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $planned = (float) ($_POST['valor_planejado'] ?? 0);
            $this->finance->updateExpense($user, (int) ($_POST['id'] ?? 0), [
                'descricao' => (string) ($_POST['descricao'] ?? ''),
                'tipo_id' => (int) ($_POST['tipo_id'] ?? 0),
                'valor_planejado' => $planned,
                'valor_real' => $this->resolveRealValue($planned, $_POST['valor_real'] ?? null),
                'data_referencia' => (string) ($_POST['data_referencia'] ?? ''),
            ]);
            $this->flash('success', 'Despesa atualizada.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'despesas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function deleteExpense(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $this->finance->deleteExpense($user, (int) ($_POST['id'] ?? 0));
            $this->flash('success', 'Despesa removida.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'despesas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function copyPreviousMonth(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $result = $this->finance->copyPreviousMonthStructure($user, $targetUserId, $year, $month);
            $this->flash('success', sprintf('Estrutura copiada: %d rendas e %d despesas.', $result['rendas'], $result['despesas']));
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'dashboard'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function addFixedIncome(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $planned = (float) ($_POST['valor_planejado'] ?? 0);
            $this->finance->createFixedIncome($user, $targetUserId, [
                'descricao' => (string) ($_POST['descricao'] ?? ''),
                'tipo_id' => (int) ($_POST['tipo_id'] ?? 0),
                'valor_planejado' => $planned,
                'valor_real' => $this->resolveRealValue($planned, $_POST['valor_real'] ?? null),
                'dia_referencia' => (int) ($_POST['dia_referencia'] ?? 1),
                'inicio_vigencia' => (string) ($_POST['inicio_vigencia'] ?? ''),
                'fim_vigencia' => (string) ($_POST['fim_vigencia'] ?? ''),
            ]);
            $this->flash('success', 'Renda fixa cadastrada.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'rendas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function deleteFixedIncome(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $this->finance->deleteFixedIncome($user, (int) ($_POST['id'] ?? 0));
            $this->flash('success', 'Renda fixa removida.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'rendas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function addFixedExpense(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $planned = (float) ($_POST['valor_planejado'] ?? 0);
            $this->finance->createFixedExpense($user, $targetUserId, [
                'descricao' => (string) ($_POST['descricao'] ?? ''),
                'tipo_id' => (int) ($_POST['tipo_id'] ?? 0),
                'valor_planejado' => $planned,
                'valor_real' => $this->resolveRealValue($planned, $_POST['valor_real'] ?? null),
                'dia_referencia' => (int) ($_POST['dia_referencia'] ?? 1),
                'inicio_vigencia' => (string) ($_POST['inicio_vigencia'] ?? ''),
                'fim_vigencia' => (string) ($_POST['fim_vigencia'] ?? ''),
            ]);
            $this->flash('success', 'Despesa fixa cadastrada.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'despesas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function deleteFixedExpense(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $this->finance->deleteFixedExpense($user, (int) ($_POST['id'] ?? 0));
            $this->flash('success', 'Despesa fixa removida.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'despesas'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function applyFixed(): void
    {
        $this->verifyCsrf();
        [$user, $targetUserId, $year, $month, $startDate, $endDate] = $this->resolveContextFromPost();

        try {
            $result = $this->finance->applyFixedForMonth($user, $targetUserId, $year, $month);
            $this->flash('success', sprintf('Fixos aplicados: %d rendas e %d despesas.', $result['rendas'], $result['despesas']));
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectByDestination((string) ($_POST['destino'] ?? 'dashboard'), $year, $month, $targetUserId, $startDate, $endDate);
    }

    public function addType(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();

        try {
            $this->finance->createMovementType($user, [
                'categoria' => (string) ($_POST['categoria'] ?? ''),
                'nome' => (string) ($_POST['nome'] ?? ''),
            ]);
            $this->flash('success', 'Tipo cadastrado.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirect('/tipos');
    }

    public function updateType(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();

        try {
            $this->finance->updateMovementType($user, (int) ($_POST['id'] ?? 0), [
                'categoria' => (string) ($_POST['categoria'] ?? ''),
                'nome' => (string) ($_POST['nome'] ?? ''),
            ]);
            $this->flash('success', 'Tipo atualizado.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirect('/tipos');
    }

    public function deleteType(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();

        try {
            $this->finance->deleteMovementType($user, (int) ($_POST['id'] ?? 0));
            $this->flash('success', 'Tipo removido.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirect('/tipos');
    }

    private function resolveContextFromRequest(): array
    {
        $user = $this->requireLogin();
        $targetUserId = (int) $user['id'];
        [$year, $month] = $this->resolveYearMonth((string) ($_GET['mes'] ?? date('Y-m')));
        $startDate = $this->resolveDateFilter((string) ($_GET['data_inicio'] ?? ''));
        $endDate = $this->resolveDateFilter((string) ($_GET['data_fim'] ?? ''));

        if ($startDate !== null && $endDate !== null && $startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [$user, $targetUserId, $year, $month, sprintf('%04d-%02d', $year, $month), $startDate, $endDate];
    }

    private function resolveContextFromPost(): array
    {
        $user = $this->requireLogin();
        $targetUserId = (int) $user['id'];
        [$year, $month] = $this->resolveYearMonth((string) ($_POST['mes'] ?? date('Y-m')));
        $startDate = $this->resolveDateFilter((string) ($_POST['data_inicio'] ?? ''));
        $endDate = $this->resolveDateFilter((string) ($_POST['data_fim'] ?? ''));

        if ($startDate !== null && $endDate !== null && $startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [$user, $targetUserId, $year, $month, $startDate, $endDate];
    }

    private function resolveYearMonth(string $yearMonth): array
    {
        if (!preg_match('/^\d{4}\-\d{2}$/', $yearMonth)) {
            return [(int) date('Y'), (int) date('n')];
        }

        [$year, $month] = array_map('intval', explode('-', $yearMonth));
        if ($month < 1 || $month > 12) {
            return [(int) date('Y'), (int) date('n')];
        }

        return [$year, $month];
    }

    private function redirectByDestination(string $destination, int $year, int $month, int $userId, ?string $startDate = null, ?string $endDate = null): never
    {
        $path = '/dashboard';
        if ($destination === 'rendas') {
            $path = '/rendas';
        } elseif ($destination === 'despesas') {
            $path = '/despesas';
        }

        $query = '?mes=' . sprintf('%04d-%02d', $year, $month);
        if ($startDate !== null && $startDate !== '') {
            $query .= '&data_inicio=' . urlencode($startDate);
        }
        if ($endDate !== null && $endDate !== '') {
            $query .= '&data_fim=' . urlencode($endDate);
        }
        $this->redirect($path . $query);
    }

    private function resolveDateFilter(string $date): ?string
    {
        if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date) !== 1) {
            return null;
        }

        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return null;
        }

        return $date;
    }

    private function resolveRealValue(float $planned, mixed $realRaw): float
    {
        if (!is_string($realRaw) || trim($realRaw) === '') {
            return $planned;
        }

        return (float) $realRaw;
    }
}
