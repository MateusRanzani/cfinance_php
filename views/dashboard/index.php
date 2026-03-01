<?php
$success = (string) ($flash['success'] ?? '');
$error = (string) ($flash['error'] ?? '');
$isAdmin = (($user['role'] ?? '') === 'admin');

$income = number_format((float) ($summary['income'] ?? 0), 2, ',', '.');
$expense = number_format((float) ($summary['expense'] ?? 0), 2, ',', '.');
$balance = number_format((float) ($summary['balance'] ?? 0), 2, ',', '.');
$projected = number_format((float) ($summary['projected_balance'] ?? 0), 2, ',', '.');
?>
<header class="mb-6 flex flex-col gap-3 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
        <p class="text-sm text-slate-600">
            User: <?= $e($user['name']) ?> (<?= $e($user['role']) ?>)
        </p>
    </div>
    <form method="post" action="<?= $url('/logout') ?>">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
            Logout
        </button>
    </form>
</header>

<?php if ($success !== ''): ?>
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
        <?= $e($success) ?>
    </div>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
        <?= $e($error) ?>
    </div>
<?php endif; ?>

<?php if ($isAdmin): ?>
    <section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <form method="get" action="<?= $url('/dashboard') ?>" class="grid gap-3 md:grid-cols-4">
            <label class="block text-sm">
                <span class="mb-1 block font-medium text-slate-700">User</span>
                <select name="user_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    <?php foreach ($users as $candidate): ?>
                        <option
                            value="<?= (int) $candidate['id'] ?>"
                            <?= ((int) $candidate['id'] === (int) $targetUserId) ? 'selected' : '' ?>
                        >
                            <?= $e($candidate['name']) ?> (<?= $e($candidate['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="block text-sm">
                <span class="mb-1 block font-medium text-slate-700">Year</span>
                <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="number" name="year" value="<?= (int) $year ?>">
            </label>
            <label class="block text-sm">
                <span class="mb-1 block font-medium text-slate-700">Month</span>
                <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="number" min="1" max="12" name="month" value="<?= (int) $month ?>">
            </label>
            <div class="flex items-end gap-2">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">Filter</button>
                <a class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="<?= $url('/users') ?>">Users</a>
            </div>
        </form>
    </section>
<?php endif; ?>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold">Monthly Budget (<?= (int) $month ?>/<?= (int) $year ?>)</h2>
    <form method="post" action="<?= $url('/budget/save') ?>" class="mt-4 grid gap-3 md:grid-cols-4">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <input type="hidden" name="year" value="<?= (int) $year ?>">
        <input type="hidden" name="month" value="<?= (int) $month ?>">
        <input type="hidden" name="user_id" value="<?= (int) $targetUserId ?>">
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Planned income</span>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="number" min="0" step="0.01" name="planned_income" value="<?= $e($budget['planned_income']) ?>">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Planned expense</span>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="number" min="0" step="0.01" name="planned_expense" value="<?= $e($budget['planned_expense']) ?>">
        </label>
        <div class="md:col-span-2 flex items-end">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">Save budget</button>
        </div>
    </form>

    <div class="mt-5 grid gap-3 md:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4"><h3 class="text-sm text-slate-600">Income</h3><p class="mt-1 text-xl font-bold">R$ <?= $income ?></p></article>
        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4"><h3 class="text-sm text-slate-600">Expense</h3><p class="mt-1 text-xl font-bold">R$ <?= $expense ?></p></article>
        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4"><h3 class="text-sm text-slate-600">Current balance</h3><p class="mt-1 text-xl font-bold">R$ <?= $balance ?></p></article>
        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4"><h3 class="text-sm text-slate-600">Projected balance</h3><p class="mt-1 text-xl font-bold">R$ <?= $projected ?></p></article>
    </div>
</section>

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold">New entry</h2>
    <form method="post" action="<?= $url('/entry/add') ?>" class="mt-4 grid gap-3 md:grid-cols-5">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <input type="hidden" name="budget_id" value="<?= (int) $budget['id'] ?>">
        <input type="hidden" name="year" value="<?= (int) $year ?>">
        <input type="hidden" name="month" value="<?= (int) $month ?>">
        <input type="hidden" name="user_id" value="<?= (int) $targetUserId ?>">
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Type</span>
            <select class="w-full rounded-lg border border-slate-300 px-3 py-2" name="type">
                <option value="renda">Income</option>
                <option value="despesa">Expense</option>
            </select>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Description</span>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="text" name="description" required>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Amount</span>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="number" min="0.01" step="0.01" name="amount" required>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium text-slate-700">Date</span>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2" type="date" name="entry_date" value="<?= $e($today) ?>" required>
        </label>
        <div class="flex items-end">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">Add</button>
        </div>
    </form>
</section>

<section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <h2 class="text-lg font-semibold">Entries</h2>
    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[900px] border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left">
                    <th class="p-2">Date</th>
                    <th class="p-2">Type</th>
                    <th class="p-2">Description</th>
                    <th class="p-2">Amount</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($entries === []): ?>
                    <tr><td class="p-2 text-slate-500" colspan="5">No entries for this month.</td></tr>
                <?php endif; ?>
                <?php foreach ($entries as $entry): ?>
                    <tr class="border-b border-slate-100 align-top">
                        <td class="p-2"><?= $e($entry['entry_date']) ?></td>
                        <td class="p-2"><?= $entry['type'] === 'renda' ? 'Income' : 'Expense' ?></td>
                        <td class="p-2"><?= $e($entry['description']) ?></td>
                        <td class="p-2">R$ <?= number_format((float) $entry['amount'], 2, ',', '.') ?></td>
                        <td class="p-2">
                            <div class="flex flex-wrap gap-2">
                                <form method="post" action="<?= $url('/entry/update') ?>" class="flex flex-wrap items-end gap-2">
                                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                    <input type="hidden" name="entry_id" value="<?= (int) $entry['id'] ?>">
                                    <input type="hidden" name="year" value="<?= (int) $year ?>">
                                    <input type="hidden" name="month" value="<?= (int) $month ?>">
                                    <input type="hidden" name="user_id" value="<?= (int) $targetUserId ?>">
                                    <input class="rounded-lg border border-slate-300 px-2 py-1" type="text" name="description" value="<?= $e($entry['description']) ?>" required>
                                    <input class="rounded-lg border border-slate-300 px-2 py-1" type="number" min="0.01" step="0.01" name="amount" value="<?= $e($entry['amount']) ?>" required>
                                    <input class="rounded-lg border border-slate-300 px-2 py-1" type="date" name="entry_date" value="<?= $e($entry['entry_date']) ?>" required>
                                    <button class="rounded-lg bg-slate-800 px-3 py-1 text-white hover:bg-slate-700" type="submit">Save</button>
                                </form>
                                <form method="post" action="<?= $url('/entry/delete') ?>" onsubmit="return confirm('Delete entry?')">
                                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                    <input type="hidden" name="entry_id" value="<?= (int) $entry['id'] ?>">
                                    <input type="hidden" name="year" value="<?= (int) $year ?>">
                                    <input type="hidden" name="month" value="<?= (int) $month ?>">
                                    <input type="hidden" name="user_id" value="<?= (int) $targetUserId ?>">
                                    <button class="rounded-lg bg-rose-600 px-3 py-1 text-white hover:bg-rose-500" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

