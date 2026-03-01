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

    public function saveMonthlyBudget(int $userId, int $year, int $month, float $plannedIncome, float $plannedExpense): int
    {
        $this->validateMonthYear($year, $month);

        $query = $this->db->prepare(
            'INSERT INTO budgets (user_id, year, month, planned_income, planned_expense)
            VALUES (:user_id, :year, :month, :planned_income, :planned_expense)
            ON DUPLICATE KEY UPDATE planned_income = VALUES(planned_income), planned_expense = VALUES(planned_expense), updated_at = NOW()'
        );

        $query->execute([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month,
            'planned_income' => max(0, $plannedIncome),
            'planned_expense' => max(0, $plannedExpense),
        ]);

        $budget = $this->getBudgetForMonth($userId, $year, $month);
        if ($budget === null) {
            throw new \RuntimeException('Falha ao salvar orçamento.');
        }

        return (int) $budget['id'];
    }

    public function getBudgetForMonth(int $userId, int $year, int $month): ?array
    {
        $query = $this->db->prepare('SELECT * FROM budgets WHERE user_id = :user_id AND year = :year AND month = :month LIMIT 1');
        $query->execute(['user_id' => $userId, 'year' => $year, 'month' => $month]);
        $budget = $query->fetch();

        return $budget ?: null;
    }

    public function getBudgetById(int $id): ?array
    {
        $query = $this->db->prepare('SELECT * FROM budgets WHERE id = :id LIMIT 1');
        $query->execute(['id' => $id]);
        $budget = $query->fetch();

        return $budget ?: null;
    }

    public function listEntries(int $budgetId, array $viewer): array
    {
        if (($viewer['role'] ?? '') === 'admin') {
            $query = $this->db->prepare(
                'SELECT id, budget_id, user_id, type, description, amount, entry_date
                FROM entries WHERE budget_id = :budget_id
                ORDER BY entry_date DESC, id DESC'
            );
            $query->execute(['budget_id' => $budgetId]);
            return $query->fetchAll();
        }

        $query = $this->db->prepare(
            'SELECT id, budget_id, user_id, type, description, amount, entry_date
            FROM entries WHERE budget_id = :budget_id AND user_id = :user_id
            ORDER BY entry_date DESC, id DESC'
        );
        $query->execute(['budget_id' => $budgetId, 'user_id' => $viewer['id']]);
        return $query->fetchAll();
    }

    public function createEntry(array $actor, array $payload): void
    {
        $budget = $this->requireBudget((int) ($payload['budget_id'] ?? 0));
        $this->assertBudgetAccess($actor, $budget);

        $type = (string) ($payload['type'] ?? '');
        if (!in_array($type, ['renda', 'despesa'], true)) {
            throw new \RuntimeException('Tipo inválido.');
        }

        $description = trim((string) ($payload['description'] ?? ''));
        if ($description === '') {
            throw new \RuntimeException('Descrição é obrigatória.');
        }

        $amount = (float) ($payload['amount'] ?? 0);
        if ($amount <= 0) {
            throw new \RuntimeException('Valor deve ser maior que zero.');
        }

        $entryDate = (string) ($payload['entry_date'] ?? '');
        if (!$this->validDate($entryDate)) {
            throw new \RuntimeException('Data inválida.');
        }

        $query = $this->db->prepare(
            'INSERT INTO entries (budget_id, user_id, type, description, amount, entry_date)
            VALUES (:budget_id, :user_id, :type, :description, :amount, :entry_date)'
        );
        $query->execute([
            'budget_id' => (int) $budget['id'],
            'user_id' => (int) $budget['user_id'],
            'type' => $type,
            'description' => $description,
            'amount' => $amount,
            'entry_date' => $entryDate,
        ]);
    }

    public function updateEntry(array $actor, int $entryId, array $payload): void
    {
        $entry = $this->requireEntry($entryId);
        $budget = $this->requireBudget((int) $entry['budget_id']);
        $this->assertBudgetAccess($actor, $budget);

        $description = trim((string) ($payload['description'] ?? ''));
        if ($description === '') {
            throw new \RuntimeException('Descrição é obrigatória.');
        }

        $amount = (float) ($payload['amount'] ?? 0);
        if ($amount <= 0) {
            throw new \RuntimeException('Valor deve ser maior que zero.');
        }

        $entryDate = (string) ($payload['entry_date'] ?? '');
        if (!$this->validDate($entryDate)) {
            throw new \RuntimeException('Data inválida.');
        }

        $query = $this->db->prepare(
            'UPDATE entries
            SET description = :description, amount = :amount, entry_date = :entry_date, updated_at = NOW()
            WHERE id = :id'
        );
        $query->execute([
            'description' => $description,
            'amount' => $amount,
            'entry_date' => $entryDate,
            'id' => $entryId,
        ]);
    }

    public function deleteEntry(array $actor, int $entryId): void
    {
        $entry = $this->requireEntry($entryId);
        $budget = $this->requireBudget((int) $entry['budget_id']);
        $this->assertBudgetAccess($actor, $budget);

        $query = $this->db->prepare('DELETE FROM entries WHERE id = :id');
        $query->execute(['id' => $entryId]);
    }

    public function calculateSummary(array $entries, float $plannedIncome, float $plannedExpense): array
    {
        $income = 0.0;
        $expense = 0.0;

        foreach ($entries as $entry) {
            if (($entry['type'] ?? '') === 'renda') {
                $income += (float) $entry['amount'];
            } else {
                $expense += (float) $entry['amount'];
            }
        }

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'projected_balance' => $plannedIncome - $plannedExpense,
        ];
    }

    private function requireBudget(int $budgetId): array
    {
        $budget = $this->getBudgetById($budgetId);
        if ($budget === null) {
            throw new \RuntimeException('Orçamento não encontrado.');
        }

        return $budget;
    }

    private function requireEntry(int $entryId): array
    {
        $query = $this->db->prepare('SELECT * FROM entries WHERE id = :id LIMIT 1');
        $query->execute(['id' => $entryId]);
        $entry = $query->fetch();

        if (!$entry) {
            throw new \RuntimeException('Lançamento não encontrado.');
        }

        return $entry;
    }

    private function assertBudgetAccess(array $actor, array $budget): void
    {
        if (($actor['role'] ?? '') === 'admin') {
            return;
        }

        if ((int) $actor['id'] !== (int) $budget['user_id']) {
            throw new \RuntimeException('Sem permissão para esta operação.');
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
            throw new \RuntimeException('Ano/mês inválido.');
        }
    }
}
