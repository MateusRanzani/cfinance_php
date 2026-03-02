<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;
use PDO;

final class FinanceService
{
    public function __construct(private PDO $db)
    {
    }

    public function buildDashboardData(array $viewer, int $targetUserId, int $year, int $month): array
    {
        $this->validateMonthYear($year, $month);
        $this->assertUserAccess($viewer, $targetUserId);

        $incomes = $this->listIncomesForMonth($targetUserId, $year, $month);
        $expenses = $this->listExpensesForMonth($targetUserId, $year, $month);

        $totals = [
            'income_planned' => $this->sumColumn($incomes, 'valor_planejado'),
            'income_real' => $this->sumColumn($incomes, 'valor_real'),
            'expense_planned' => $this->sumColumn($expenses, 'valor_planejado'),
            'expense_real' => $this->sumColumn($expenses, 'valor_real'),
        ];
        $totals['difference_total'] = ($totals['income_planned'] - $totals['income_real']) + ($totals['expense_planned'] - $totals['expense_real']);
        $totals['final_balance'] = $totals['income_real'] - $totals['expense_real'];

        $hasCurrentMonthData = $incomes !== [] || $expenses !== [];
        $copySuggestion = $this->buildCopySuggestion($targetUserId, $year, $month, $hasCurrentMonthData);

        return [
            'incomes' => $incomes,
            'expenses' => $expenses,
            'totals' => $totals,
            'available_months' => $this->listAvailableMonths($targetUserId, $year, $month),
            'copy_suggestion' => $copySuggestion,
            'fixed_incomes' => $this->listFixedIncomes($targetUserId),
            'fixed_expenses' => $this->listFixedExpenses($targetUserId),
            'income_types' => $this->listTypesByCategory('renda'),
            'expense_types' => $this->listTypesByCategory('despesa'),
        ];
    }

    public function createIncome(array $viewer, int $targetUserId, array $payload): void
    {
        $this->assertUserAccess($viewer, $targetUserId);
        $data = $this->validateEntryPayload($payload, 'renda');

        $query = $this->db->prepare(
            'INSERT INTO rendas (descricao, valor_planejado, valor_real, data_referencia, usuario_id, tipo_id)
             VALUES (:descricao, :valor_planejado, :valor_real, :data_referencia, :usuario_id, :tipo_id)'
        );
        $query->execute([
            'descricao' => $data['descricao'],
            'valor_planejado' => $data['valor_planejado'],
            'valor_real' => $data['valor_real'],
            'data_referencia' => $data['data_referencia'],
            'usuario_id' => $targetUserId,
            'tipo_id' => $data['tipo_id'],
        ]);
    }

    public function updateIncome(array $viewer, int $id, array $payload): void
    {
        $income = $this->requireIncome($id);
        $this->assertUserAccess($viewer, (int) $income['usuario_id']);
        $data = $this->validateEntryPayload($payload, 'renda');

        $query = $this->db->prepare(
            'UPDATE rendas
             SET descricao = :descricao, valor_planejado = :valor_planejado, valor_real = :valor_real, data_referencia = :data_referencia, tipo_id = :tipo_id, updated_at = NOW()
             WHERE id = :id'
        );
        $query->execute([
            'id' => $id,
            'descricao' => $data['descricao'],
            'valor_planejado' => $data['valor_planejado'],
            'valor_real' => $data['valor_real'],
            'data_referencia' => $data['data_referencia'],
            'tipo_id' => $data['tipo_id'],
        ]);
    }

    public function deleteIncome(array $viewer, int $id): void
    {
        $income = $this->requireIncome($id);
        $this->assertUserAccess($viewer, (int) $income['usuario_id']);
        $query = $this->db->prepare('DELETE FROM rendas WHERE id = :id');
        $query->execute(['id' => $id]);
    }

    public function createExpense(array $viewer, int $targetUserId, array $payload): void
    {
        $this->assertUserAccess($viewer, $targetUserId);
        $data = $this->validateEntryPayload($payload, 'despesa');

        $query = $this->db->prepare(
            'INSERT INTO despesas (descricao, valor_planejado, valor_real, data_referencia, usuario_id, tipo_id)
             VALUES (:descricao, :valor_planejado, :valor_real, :data_referencia, :usuario_id, :tipo_id)'
        );
        $query->execute([
            'descricao' => $data['descricao'],
            'valor_planejado' => $data['valor_planejado'],
            'valor_real' => $data['valor_real'],
            'data_referencia' => $data['data_referencia'],
            'usuario_id' => $targetUserId,
            'tipo_id' => $data['tipo_id'],
        ]);
    }

    public function updateExpense(array $viewer, int $id, array $payload): void
    {
        $expense = $this->requireExpense($id);
        $this->assertUserAccess($viewer, (int) $expense['usuario_id']);
        $data = $this->validateEntryPayload($payload, 'despesa');

        $query = $this->db->prepare(
            'UPDATE despesas
             SET descricao = :descricao, valor_planejado = :valor_planejado, valor_real = :valor_real, data_referencia = :data_referencia, tipo_id = :tipo_id, updated_at = NOW()
             WHERE id = :id'
        );
        $query->execute([
            'id' => $id,
            'descricao' => $data['descricao'],
            'valor_planejado' => $data['valor_planejado'],
            'valor_real' => $data['valor_real'],
            'data_referencia' => $data['data_referencia'],
            'tipo_id' => $data['tipo_id'],
        ]);
    }

    public function deleteExpense(array $viewer, int $id): void
    {
        $expense = $this->requireExpense($id);
        $this->assertUserAccess($viewer, (int) $expense['usuario_id']);
        $query = $this->db->prepare('DELETE FROM despesas WHERE id = :id');
        $query->execute(['id' => $id]);
    }

    public function createFixedIncome(array $viewer, int $targetUserId, array $payload): void
    {
        $this->assertUserAccess($viewer, $targetUserId);
        $data = $this->validateFixedPayload($payload, 'renda');

        $query = $this->db->prepare(
            'INSERT INTO rendas_fixas (usuario_id, descricao, valor_planejado, valor_real, tipo_id, dia_referencia)
             VALUES (:usuario_id, :descricao, :valor_planejado, :valor_real, :tipo_id, :dia_referencia)'
        );
        $query->execute([
            'usuario_id' => $targetUserId,
            'descricao' => $data['descricao'],
            'valor_planejado' => $data['valor_planejado'],
            'valor_real' => $data['valor_real'],
            'tipo_id' => $data['tipo_id'],
            'dia_referencia' => $data['dia_referencia'],
        ]);
    }

    public function deleteFixedIncome(array $viewer, int $id): void
    {
        $item = $this->requireFixedIncome($id);
        $this->assertUserAccess($viewer, (int) $item['usuario_id']);
        $query = $this->db->prepare('DELETE FROM rendas_fixas WHERE id = :id');
        $query->execute(['id' => $id]);
    }

    public function createFixedExpense(array $viewer, int $targetUserId, array $payload): void
    {
        $this->assertUserAccess($viewer, $targetUserId);
        $data = $this->validateFixedPayload($payload, 'despesa');

        $query = $this->db->prepare(
            'INSERT INTO despesas_fixas (usuario_id, descricao, valor_planejado, valor_real, tipo_id, dia_referencia)
             VALUES (:usuario_id, :descricao, :valor_planejado, :valor_real, :tipo_id, :dia_referencia)'
        );
        $query->execute([
            'usuario_id' => $targetUserId,
            'descricao' => $data['descricao'],
            'valor_planejado' => $data['valor_planejado'],
            'valor_real' => $data['valor_real'],
            'tipo_id' => $data['tipo_id'],
            'dia_referencia' => $data['dia_referencia'],
        ]);
    }

    public function deleteFixedExpense(array $viewer, int $id): void
    {
        $item = $this->requireFixedExpense($id);
        $this->assertUserAccess($viewer, (int) $item['usuario_id']);
        $query = $this->db->prepare('DELETE FROM despesas_fixas WHERE id = :id');
        $query->execute(['id' => $id]);
    }

    public function applyFixedForMonth(array $viewer, int $targetUserId, int $year, int $month): array
    {
        $this->validateMonthYear($year, $month);
        $this->assertUserAccess($viewer, $targetUserId);

        $incomeInserted = 0;
        foreach ($this->listFixedIncomes($targetUserId) as $item) {
            if ($this->existsIncomeFromFixedInMonth((int) $item['id'], $targetUserId, $year, $month)) {
                continue;
            }
            $referenceDate = $this->buildReferenceDate($year, $month, (int) $item['dia_referencia']);
            $query = $this->db->prepare(
                'INSERT INTO rendas (descricao, valor_planejado, valor_real, data_referencia, usuario_id, tipo_id, renda_fixa_id)
                 VALUES (:descricao, :valor_planejado, :valor_real, :data_referencia, :usuario_id, :tipo_id, :renda_fixa_id)'
            );
            $query->execute([
                'descricao' => (string) $item['descricao'],
                'valor_planejado' => (float) $item['valor_planejado'],
                'valor_real' => (float) $item['valor_real'],
                'data_referencia' => $referenceDate,
                'usuario_id' => $targetUserId,
                'tipo_id' => $item['tipo_id'] !== null ? (int) $item['tipo_id'] : null,
                'renda_fixa_id' => (int) $item['id'],
            ]);
            $incomeInserted++;
        }

        $expenseInserted = 0;
        foreach ($this->listFixedExpenses($targetUserId) as $item) {
            if ($this->existsExpenseFromFixedInMonth((int) $item['id'], $targetUserId, $year, $month)) {
                continue;
            }
            $referenceDate = $this->buildReferenceDate($year, $month, (int) $item['dia_referencia']);
            $query = $this->db->prepare(
                'INSERT INTO despesas (descricao, valor_planejado, valor_real, data_referencia, usuario_id, tipo_id, despesa_fixa_id)
                 VALUES (:descricao, :valor_planejado, :valor_real, :data_referencia, :usuario_id, :tipo_id, :despesa_fixa_id)'
            );
            $query->execute([
                'descricao' => (string) $item['descricao'],
                'valor_planejado' => (float) $item['valor_planejado'],
                'valor_real' => (float) $item['valor_real'],
                'data_referencia' => $referenceDate,
                'usuario_id' => $targetUserId,
                'tipo_id' => $item['tipo_id'] !== null ? (int) $item['tipo_id'] : null,
                'despesa_fixa_id' => (int) $item['id'],
            ]);
            $expenseInserted++;
        }

        return ['rendas' => $incomeInserted, 'despesas' => $expenseInserted];
    }

    public function copyPreviousMonthStructure(array $viewer, int $targetUserId, int $year, int $month): array
    {
        $this->validateMonthYear($year, $month);
        $this->assertUserAccess($viewer, $targetUserId);

        $current = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $previous = $current->modify('-1 month');
        $prevYear = (int) $previous->format('Y');
        $prevMonth = (int) $previous->format('n');

        $insertedIncomes = $this->copyMonthEntries('rendas', $targetUserId, $prevYear, $prevMonth, $year, $month);
        $insertedExpenses = $this->copyMonthEntries('despesas', $targetUserId, $prevYear, $prevMonth, $year, $month);

        return ['rendas' => $insertedIncomes, 'despesas' => $insertedExpenses];
    }

    public function listMovementTypes(): array
    {
        return [
            'renda' => $this->listTypesByCategory('renda'),
            'despesa' => $this->listTypesByCategory('despesa'),
        ];
    }

    public function createMovementType(array $viewer, array $payload): void
    {
        $this->assertAuthenticated($viewer);
        $data = $this->validateTypePayload($payload);

        $query = $this->db->prepare(
            'INSERT INTO tipos_movimentacao (categoria, nome) VALUES (:categoria, :nome)'
        );

        try {
            $query->execute([
                'categoria' => $data['categoria'],
                'nome' => $data['nome'],
            ]);
        } catch (\Throwable) {
            throw new \RuntimeException('Esse tipo ja existe para a categoria selecionada.');
        }
    }

    public function updateMovementType(array $viewer, int $id, array $payload): void
    {
        $this->assertAuthenticated($viewer);
        $existing = $this->requireMovementType($id);
        $data = $this->validateTypePayload([
            'categoria' => $payload['categoria'] ?? $existing['categoria'],
            'nome' => $payload['nome'] ?? $existing['nome'],
        ]);

        $query = $this->db->prepare(
            'UPDATE tipos_movimentacao SET categoria = :categoria, nome = :nome, updated_at = NOW() WHERE id = :id'
        );
        try {
            $query->execute([
                'id' => $id,
                'categoria' => $data['categoria'],
                'nome' => $data['nome'],
            ]);
        } catch (\Throwable) {
            throw new \RuntimeException('Esse tipo ja existe para a categoria selecionada.');
        }
    }

    public function deleteMovementType(array $viewer, int $id): void
    {
        $this->assertAuthenticated($viewer);
        $this->requireMovementType($id);

        $query = $this->db->prepare(
            'SELECT
                (SELECT COUNT(*) FROM rendas WHERE tipo_id = :id1) +
                (SELECT COUNT(*) FROM despesas WHERE tipo_id = :id2) +
                (SELECT COUNT(*) FROM rendas_fixas WHERE tipo_id = :id3) +
                (SELECT COUNT(*) FROM despesas_fixas WHERE tipo_id = :id4) AS total'
        );
        $query->execute(['id1' => $id, 'id2' => $id, 'id3' => $id, 'id4' => $id]);
        if ((int) $query->fetchColumn() > 0) {
            throw new \RuntimeException('Tipo em uso. Altere os lancamentos antes de excluir.');
        }

        $delete = $this->db->prepare('DELETE FROM tipos_movimentacao WHERE id = :id');
        $delete->execute(['id' => $id]);
    }

    private function listIncomesForMonth(int $userId, int $year, int $month): array
    {
        $query = $this->db->prepare(
            'SELECT r.id, r.descricao, r.valor_planejado, r.valor_real, r.data_referencia, r.usuario_id, r.tipo_id, t.nome AS tipo_nome
             FROM rendas r
             LEFT JOIN tipos_movimentacao t ON t.id = r.tipo_id
             WHERE r.usuario_id = :usuario_id AND YEAR(r.data_referencia) = :ano AND MONTH(r.data_referencia) = :mes
             ORDER BY r.data_referencia ASC, r.id ASC'
        );
        $query->execute(['usuario_id' => $userId, 'ano' => $year, 'mes' => $month]);
        return $query->fetchAll();
    }

    private function listExpensesForMonth(int $userId, int $year, int $month): array
    {
        $query = $this->db->prepare(
            'SELECT d.id, d.descricao, d.valor_planejado, d.valor_real, d.data_referencia, d.usuario_id, d.tipo_id, t.nome AS tipo_nome
             FROM despesas d
             LEFT JOIN tipos_movimentacao t ON t.id = d.tipo_id
             WHERE d.usuario_id = :usuario_id AND YEAR(d.data_referencia) = :ano AND MONTH(d.data_referencia) = :mes
             ORDER BY d.data_referencia ASC, d.id ASC'
        );
        $query->execute(['usuario_id' => $userId, 'ano' => $year, 'mes' => $month]);
        return $query->fetchAll();
    }

    private function listAvailableMonths(int $userId, int $year, int $month): array
    {
        $query = $this->db->prepare(
            "SELECT DISTINCT ym FROM (
                SELECT DATE_FORMAT(data_referencia, '%Y-%m') AS ym FROM rendas WHERE usuario_id = :usuario_id_r
                UNION ALL
                SELECT DATE_FORMAT(data_referencia, '%Y-%m') AS ym FROM despesas WHERE usuario_id = :usuario_id_d
             ) base
             WHERE ym IS NOT NULL
             ORDER BY ym DESC"
        );
        $query->execute([
            'usuario_id_r' => $userId,
            'usuario_id_d' => $userId,
        ]);
        $months = array_map(static fn (array $row): string => (string) $row['ym'], $query->fetchAll());

        $current = sprintf('%04d-%02d', $year, $month);
        if (!in_array($current, $months, true)) {
            array_unshift($months, $current);
        }

        return array_values(array_unique($months));
    }

    private function buildCopySuggestion(int $userId, int $year, int $month, bool $hasCurrentData): ?array
    {
        if ($hasCurrentData) {
            return null;
        }

        $current = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $previous = $current->modify('-1 month');
        $prevYear = (int) $previous->format('Y');
        $prevMonth = (int) $previous->format('n');

        $hasPrevious = $this->monthHasRecords($userId, $prevYear, $prevMonth);
        if (!$hasPrevious) {
            return null;
        }

        return [
            'year' => $prevYear,
            'month' => $prevMonth,
            'label' => $previous->format('m/Y'),
        ];
    }

    private function monthHasRecords(int $userId, int $year, int $month): bool
    {
        $query = $this->db->prepare(
            'SELECT
                (SELECT COUNT(*) FROM rendas WHERE usuario_id = :usuario_id_r AND YEAR(data_referencia) = :ano_r AND MONTH(data_referencia) = :mes_r) +
                (SELECT COUNT(*) FROM despesas WHERE usuario_id = :usuario_id_d AND YEAR(data_referencia) = :ano_d AND MONTH(data_referencia) = :mes_d) AS total'
        );
        $query->execute([
            'usuario_id_r' => $userId,
            'ano_r' => $year,
            'mes_r' => $month,
            'usuario_id_d' => $userId,
            'ano_d' => $year,
            'mes_d' => $month,
        ]);
        return (int) $query->fetchColumn() > 0;
    }

    private function copyMonthEntries(string $table, int $userId, int $fromYear, int $fromMonth, int $toYear, int $toMonth): int
    {
        $sourceQuery = $this->db->prepare(
            "SELECT descricao, valor_planejado, data_referencia, tipo_id
             FROM {$table}
             WHERE usuario_id = :usuario_id AND YEAR(data_referencia) = :ano AND MONTH(data_referencia) = :mes
             ORDER BY id ASC"
        );
        $sourceQuery->execute(['usuario_id' => $userId, 'ano' => $fromYear, 'mes' => $fromMonth]);
        $items = $sourceQuery->fetchAll();

        $inserted = 0;
        foreach ($items as $item) {
            $day = (int) (new DateTimeImmutable((string) $item['data_referencia']))->format('d');
            $targetDate = $this->buildReferenceDate($toYear, $toMonth, $day);

            $existsQuery = $this->db->prepare(
                "SELECT COUNT(*) FROM {$table}
                 WHERE usuario_id = :usuario_id AND descricao = :descricao AND data_referencia = :data_referencia"
            );
            $existsQuery->execute([
                'usuario_id' => $userId,
                'descricao' => (string) $item['descricao'],
                'data_referencia' => $targetDate,
            ]);
            if ((int) $existsQuery->fetchColumn() > 0) {
                continue;
            }

            $insertQuery = $this->db->prepare(
                "INSERT INTO {$table} (descricao, valor_planejado, valor_real, data_referencia, usuario_id, tipo_id)
                 VALUES (:descricao, :valor_planejado, :valor_real, :data_referencia, :usuario_id, :tipo_id)"
            );
            $insertQuery->execute([
                'descricao' => (string) $item['descricao'],
                'valor_planejado' => (float) $item['valor_planejado'],
                'valor_real' => 0,
                'data_referencia' => $targetDate,
                'usuario_id' => $userId,
                'tipo_id' => $item['tipo_id'] !== null ? (int) $item['tipo_id'] : null,
            ]);
            $inserted++;
        }

        return $inserted;
    }

    private function listFixedIncomes(int $userId): array
    {
        $query = $this->db->prepare(
            'SELECT rf.id, rf.descricao, rf.valor_planejado, rf.valor_real, rf.tipo_id, rf.dia_referencia, rf.usuario_id, t.nome AS tipo_nome
             FROM rendas_fixas rf
             LEFT JOIN tipos_movimentacao t ON t.id = rf.tipo_id
             WHERE rf.usuario_id = :usuario_id
             ORDER BY rf.descricao ASC'
        );
        $query->execute(['usuario_id' => $userId]);
        return $query->fetchAll();
    }

    private function listFixedExpenses(int $userId): array
    {
        $query = $this->db->prepare(
            'SELECT df.id, df.descricao, df.valor_planejado, df.valor_real, df.tipo_id, df.dia_referencia, df.usuario_id, t.nome AS tipo_nome
             FROM despesas_fixas df
             LEFT JOIN tipos_movimentacao t ON t.id = df.tipo_id
             WHERE df.usuario_id = :usuario_id
             ORDER BY df.descricao ASC'
        );
        $query->execute(['usuario_id' => $userId]);
        return $query->fetchAll();
    }

    private function requireIncome(int $id): array
    {
        $query = $this->db->prepare('SELECT * FROM rendas WHERE id = :id LIMIT 1');
        $query->execute(['id' => $id]);
        $item = $query->fetch();
        if (!$item) {
            throw new \RuntimeException('Renda nao encontrada.');
        }

        return $item;
    }

    private function requireExpense(int $id): array
    {
        $query = $this->db->prepare('SELECT * FROM despesas WHERE id = :id LIMIT 1');
        $query->execute(['id' => $id]);
        $item = $query->fetch();
        if (!$item) {
            throw new \RuntimeException('Despesa nao encontrada.');
        }

        return $item;
    }

    private function requireFixedIncome(int $id): array
    {
        $query = $this->db->prepare('SELECT * FROM rendas_fixas WHERE id = :id LIMIT 1');
        $query->execute(['id' => $id]);
        $item = $query->fetch();
        if (!$item) {
            throw new \RuntimeException('Renda fixa nao encontrada.');
        }

        return $item;
    }

    private function requireFixedExpense(int $id): array
    {
        $query = $this->db->prepare('SELECT * FROM despesas_fixas WHERE id = :id LIMIT 1');
        $query->execute(['id' => $id]);
        $item = $query->fetch();
        if (!$item) {
            throw new \RuntimeException('Despesa fixa nao encontrada.');
        }

        return $item;
    }

    private function existsIncomeFromFixedInMonth(int $fixedId, int $userId, int $year, int $month): bool
    {
        $query = $this->db->prepare(
            'SELECT COUNT(*) FROM rendas
             WHERE usuario_id = :usuario_id AND renda_fixa_id = :fixo_id
               AND YEAR(data_referencia) = :ano AND MONTH(data_referencia) = :mes'
        );
        $query->execute([
            'usuario_id' => $userId,
            'fixo_id' => $fixedId,
            'ano' => $year,
            'mes' => $month,
        ]);

        return (int) $query->fetchColumn() > 0;
    }

    private function existsExpenseFromFixedInMonth(int $fixedId, int $userId, int $year, int $month): bool
    {
        $query = $this->db->prepare(
            'SELECT COUNT(*) FROM despesas
             WHERE usuario_id = :usuario_id AND despesa_fixa_id = :fixo_id
               AND YEAR(data_referencia) = :ano AND MONTH(data_referencia) = :mes'
        );
        $query->execute([
            'usuario_id' => $userId,
            'fixo_id' => $fixedId,
            'ano' => $year,
            'mes' => $month,
        ]);

        return (int) $query->fetchColumn() > 0;
    }

    private function buildReferenceDate(int $year, int $month, int $day): string
    {
        $lastDay = (int) (new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->format('t');
        $safeDay = max(1, min($day, $lastDay));
        return sprintf('%04d-%02d-%02d', $year, $month, $safeDay);
    }

    private function validateFixedPayload(array $payload, string $expectedCategory): array
    {
        $description = trim((string) ($payload['descricao'] ?? ''));
        if ($description === '') {
            throw new \RuntimeException('Descricao obrigatoria.');
        }

        $planned = (float) ($payload['valor_planejado'] ?? 0);
        $real = (float) ($payload['valor_real'] ?? 0);
        if ($planned < 0 || $real < 0) {
            throw new \RuntimeException('Valores nao podem ser negativos.');
        }

        $day = (int) ($payload['dia_referencia'] ?? 1);
        if ($day < 1 || $day > 31) {
            throw new \RuntimeException('Dia de referencia invalido.');
        }

        $typeId = (int) ($payload['tipo_id'] ?? 0);
        $type = $this->requireMovementType($typeId);
        if ($type['categoria'] !== $expectedCategory) {
            throw new \RuntimeException('Tipo invalido para a categoria selecionada.');
        }

        return [
            'descricao' => $description,
            'valor_planejado' => $planned,
            'valor_real' => $real,
            'tipo_id' => $typeId,
            'dia_referencia' => $day,
        ];
    }

    private function validateEntryPayload(array $payload, string $expectedCategory): array
    {
        $payload['categoria_tipo'] = $expectedCategory;
        return $this->validateEntryPayloadBase($payload);
    }

    private function validateEntryPayloadBase(array $payload): array
    {
        $description = trim((string) ($payload['descricao'] ?? ''));
        if ($description === '') {
            throw new \RuntimeException('Descricao obrigatoria.');
        }

        $planned = (float) ($payload['valor_planejado'] ?? 0);
        $real = (float) ($payload['valor_real'] ?? 0);

        if ($planned < 0 || $real < 0) {
            throw new \RuntimeException('Valores nao podem ser negativos.');
        }

        $referenceDate = (string) ($payload['data_referencia'] ?? '');
        if (!$this->validDate($referenceDate)) {
            throw new \RuntimeException('Data de referencia invalida.');
        }

        $typeId = (int) ($payload['tipo_id'] ?? 0);
        if ($typeId <= 0) {
            throw new \RuntimeException('Tipo obrigatorio.');
        }
        $type = $this->requireMovementType($typeId);
        $expectedCategory = (string) ($payload['categoria_tipo'] ?? '');
        if ($expectedCategory !== '' && $type['categoria'] !== $expectedCategory) {
            throw new \RuntimeException('Tipo invalido para a categoria selecionada.');
        }

        return [
            'descricao' => $description,
            'valor_planejado' => $planned,
            'valor_real' => $real,
            'data_referencia' => $referenceDate,
            'tipo_id' => $typeId,
        ];
    }

    private function listTypesByCategory(string $category): array
    {
        $query = $this->db->prepare(
            'SELECT id, categoria, nome
             FROM tipos_movimentacao
             WHERE categoria = :categoria
             ORDER BY nome ASC'
        );
        $query->execute(['categoria' => $category]);
        return $query->fetchAll();
    }

    private function validateTypePayload(array $payload): array
    {
        $category = (string) ($payload['categoria'] ?? '');
        if (!in_array($category, ['renda', 'despesa'], true)) {
            throw new \RuntimeException('Categoria invalida.');
        }

        $name = trim((string) ($payload['nome'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Nome do tipo obrigatorio.');
        }

        return [
            'categoria' => $category,
            'nome' => substr($name, 0, 120),
        ];
    }

    private function requireMovementType(int $id): array
    {
        if ($id <= 0) {
            throw new \RuntimeException('Tipo obrigatorio.');
        }

        $query = $this->db->prepare('SELECT id, categoria, nome FROM tipos_movimentacao WHERE id = :id LIMIT 1');
        $query->execute(['id' => $id]);
        $item = $query->fetch();
        if (!$item) {
            throw new \RuntimeException('Tipo nao encontrado.');
        }

        return $item;
    }

    private function sumColumn(array $rows, string $column): float
    {
        $sum = 0.0;
        foreach ($rows as $row) {
            $sum += (float) ($row[$column] ?? 0);
        }
        return $sum;
    }

    private function assertUserAccess(array $viewer, int $targetUserId): void
    {
        if (($viewer['role'] ?? '') === 'admin') {
            return;
        }

        if ((int) $viewer['id'] !== $targetUserId) {
            throw new \RuntimeException('Sem permissao para acessar este usuario.');
        }
    }

    private function assertAuthenticated(array $viewer): void
    {
        if (!isset($viewer['id'])) {
            throw new \RuntimeException('Usuario nao autenticado.');
        }
    }

    private function validDate(string $date): bool
    {
        $d = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }

    private function validateMonthYear(int $year, int $month): void
    {
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            throw new \RuntimeException('Ano/mes invalido.');
        }
    }
}
