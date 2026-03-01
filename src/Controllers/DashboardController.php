<?php

declare(strict_types=1);

namespace App\Controllers;

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $user = $this->requireLogin();
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));
        $targetUserId = $this->resolveTargetUserId($user);

        $budget = $this->finance->getBudgetForMonth($targetUserId, $year, $month);
        if ($budget === null) {
            $budgetId = $this->finance->saveMonthlyBudget($targetUserId, $year, $month, 0, 0);
            $budget = $this->finance->getBudgetById($budgetId);
        }

        if ($budget === null) {
            throw new \RuntimeException('Could not load budget.');
        }

        $entries = $this->finance->listEntries((int) $budget['id'], $user);
        $summary = $this->finance->calculateSummary(
            $entries,
            (float) $budget['planned_income'],
            (float) $budget['planned_expense']
        );

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'year' => $year,
            'month' => $month,
            'targetUserId' => $targetUserId,
            'budget' => $budget,
            'entries' => $entries,
            'summary' => $summary,
            'users' => ($user['role'] ?? '') === 'admin' ? $this->auth->listUsers() : [],
            'flash' => $this->consumeFlash(),
            'csrf' => $this->csrfToken(),
            'today' => date('Y-m-d'),
        ]);
    }

    public function saveBudget(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();

        $year = (int) ($_POST['year'] ?? date('Y'));
        $month = (int) ($_POST['month'] ?? date('n'));
        $plannedIncome = (float) ($_POST['planned_income'] ?? 0);
        $plannedExpense = (float) ($_POST['planned_expense'] ?? 0);
        $targetUserId = $this->resolveTargetUserId($user);

        try {
            $this->finance->saveMonthlyBudget($targetUserId, $year, $month, $plannedIncome, $plannedExpense);
            $this->flash('success', 'Budget saved.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectDashboard($year, $month, $targetUserId);
    }

    public function addEntry(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();

        $targetUserId = $this->resolveTargetUserId($user);
        $year = (int) ($_POST['year'] ?? date('Y'));
        $month = (int) ($_POST['month'] ?? date('n'));

        $payload = [
            'budget_id' => (int) ($_POST['budget_id'] ?? 0),
            'type' => (string) ($_POST['type'] ?? ''),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'entry_date' => (string) ($_POST['entry_date'] ?? ''),
        ];

        try {
            $this->finance->createEntry($user, $payload);
            $this->flash('success', 'Entry created.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectDashboard($year, $month, $targetUserId);
    }

    public function updateEntry(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();

        $targetUserId = $this->resolveTargetUserId($user);
        $year = (int) ($_POST['year'] ?? date('Y'));
        $month = (int) ($_POST['month'] ?? date('n'));
        $entryId = (int) ($_POST['entry_id'] ?? 0);

        $payload = [
            'description' => trim((string) ($_POST['description'] ?? '')),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'entry_date' => (string) ($_POST['entry_date'] ?? ''),
        ];

        try {
            $this->finance->updateEntry($user, $entryId, $payload);
            $this->flash('success', 'Entry updated.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectDashboard($year, $month, $targetUserId);
    }

    public function deleteEntry(): void
    {
        $this->verifyCsrf();
        $user = $this->requireLogin();

        $targetUserId = $this->resolveTargetUserId($user);
        $year = (int) ($_POST['year'] ?? date('Y'));
        $month = (int) ($_POST['month'] ?? date('n'));
        $entryId = (int) ($_POST['entry_id'] ?? 0);

        try {
            $this->finance->deleteEntry($user, $entryId);
            $this->flash('success', 'Entry removed.');
        } catch (\Throwable $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $this->redirectDashboard($year, $month, $targetUserId);
    }

    private function redirectDashboard(int $year, int $month, int $userId): never
    {
        $this->redirect('/dashboard?year=' . $year . '&month=' . $month . '&user_id=' . $userId);
    }
}

