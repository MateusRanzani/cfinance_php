<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title ?? 'Financas do Casal') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0">
    <style>
        .material-symbols-rounded {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            font-size: 1.1rem;
            line-height: 1;
        }
        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            border-radius: 0.5rem;
            padding: 0.4rem 0.55rem;
            color: #fff;
            transition: background-color .15s ease;
        }
        .icon-btn--neutral {
            background: #1e293b;
        }
        .icon-btn--neutral:hover {
            background: #334155;
        }
        .icon-btn--danger {
            background: #e11d48;
        }
        .icon-btn--danger:hover {
            background: #f43f5e;
        }
        .icon-btn--primary {
            background: #0f172a;
        }
        .icon-btn--primary:hover {
            background: #334155;
        }
        .icon-btn--muted {
            background: #e2e8f0;
            color: #334155;
        }
        .icon-btn--muted:hover {
            background: #cbd5e1;
        }
        .icon-btn__text {
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1;
        }
        .app-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: auto;
            background: #fff;
        }
        .app-table-wrap--plain {
            border: 0;
            border-radius: 0;
            background: transparent;
        }
        .app-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.875rem;
            min-width: 680px;
        }
        .app-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            letter-spacing: 0.01em;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.7rem 0.75rem;
            text-align: left;
        }
        .app-table tbody td {
            border-bottom: 1px solid #f1f5f9;
            padding: 0.7rem 0.75rem;
            vertical-align: top;
            color: #0f172a;
        }
        .app-table tbody tr:nth-child(even) td {
            background: #fcfdff;
        }
        .app-table tbody tr:hover td {
            background: #f8fafc;
        }
        .app-table .actions-cell {
            min-width: 260px;
        }
        .toast-stack {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 60;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            width: min(92vw, 360px);
        }
        .app-toast {
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            background: #ffffff;
            overflow: hidden;
            opacity: 1;
            transform: translateY(0);
            transition: opacity .25s ease, transform .25s ease;
        }
        .app-toast__body {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.75rem 0.8rem;
        }
        .app-toast__text {
            flex: 1;
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: #0f172a;
        }
        .app-toast__close {
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
            padding: 0.1rem 0.2rem;
        }
        .app-toast--success {
            border-color: #86efac;
            background: #f0fdf4;
        }
        .app-toast--error {
            border-color: #fda4af;
            background: #fff1f2;
        }
        .app-toast.hide {
            opacity: 0;
            transform: translateY(-8px);
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">
    <?php
    $flash = is_array($flash ?? null) ? $flash : [];
    $toastMessages = [];
    if (is_string($flash['success'] ?? null) && trim((string) $flash['success']) !== '') {
        $toastMessages[] = ['type' => 'success', 'message' => (string) $flash['success']];
    }
    if (is_string($flash['error'] ?? null) && trim((string) $flash['error']) !== '') {
        $toastMessages[] = ['type' => 'error', 'message' => (string) $flash['error']];
    }
    ?>
    <?php if ($toastMessages !== []): ?>
        <div class="toast-stack" id="toast-stack">
            <?php foreach ($toastMessages as $idx => $toast): ?>
                <div class="app-toast app-toast--<?= $e($toast['type']) ?>" data-toast-id="<?= (int) $idx ?>">
                    <div class="app-toast__body">
                        <div class="app-toast__text"><?= $e($toast['message']) ?></div>
                        <button type="button" class="app-toast__close" aria-label="Fechar" onclick="window.closeToast(<?= (int) $idx ?>)">x</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <script>
            window.closeToast = function (id) {
                var el = document.querySelector('[data-toast-id="' + id + '"]');
                if (!el) return;
                el.classList.add('hide');
                setTimeout(function () { el.remove(); }, 260);
            };
            setTimeout(function () {
                document.querySelectorAll('#toast-stack .app-toast').forEach(function (el) {
                    el.classList.add('hide');
                    setTimeout(function () { el.remove(); }, 260);
                });
            }, 4500);
        </script>
    <?php endif; ?>
    <?php $isAuthenticated = isset($_SESSION['user']) && is_array($_SESSION['user']); ?>
    <?php if ($isAuthenticated): ?>
        <?php $activeMenu = (string) ($activeMenu ?? ''); ?>
        <?php
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        $csrfLayout = (string) $_SESSION['csrf'];
        $userLayout = $_SESSION['user'] ?? [];
        $roleLabel = (($userLayout['role'] ?? '') === 'admin') ? 'Administrador' : 'Padrao';
        $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $requestPath = is_string($requestPath) ? $requestPath : '/';
        $basePath = (string) ($basePath ?? '/');
        if ($basePath !== '/' && str_starts_with($requestPath, $basePath)) {
            $requestPath = (string) substr($requestPath, strlen($basePath));
        }
        if ($requestPath === '') {
            $requestPath = '/';
        }
        $isActive = static function (string $menuKey, string $routePrefix) use ($activeMenu, $requestPath): bool {
            if ($activeMenu !== '') {
                return $activeMenu === $menuKey;
            }
            return $requestPath === $routePrefix || str_starts_with($requestPath, $routePrefix . '/');
        };
        ?>
        <div class="min-h-screen">
            <aside class="fixed left-0 top-0 z-30 flex h-screen w-64 flex-col border-r border-slate-200 bg-white p-4">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Menu</p>
                    <h2 class="mt-1 text-lg font-bold">
                        <a href="<?= $url('/dashboard') ?>">Financas</a>
                    </h2>
                </div>
                <nav class="space-y-1">
                    <a
                        href="<?= $url('/dashboard') ?>"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $isActive('dashboard', '/dashboard') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                        Dashboard
                    </a>
                    <a
                        href="<?= $url('/rendas') ?>"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $isActive('rendas', '/rendas') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                        Renda
                    </a>
                    <a
                        href="<?= $url('/despesas') ?>"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $isActive('despesas', '/despesas') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                        Despesa
                    </a>
                    <a
                        href="<?= $url('/tipos') ?>"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $isActive('tipos', '/tipos') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                        Tipos
                    </a>
                    <a
                        href="<?= $url('/metas') ?>"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $isActive('metas', '/metas') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                        Metas
                    </a>
                    <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                        <a
                            href="<?= $url('/users') ?>"
                            class="block rounded-lg px-3 py-2 text-sm font-semibold <?= $isActive('users', '/users') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
                            Usuarios
                        </a>
                    <?php endif; ?>
                </nav>

                <div class="mt-auto border-t border-slate-200 pt-4">
                    <p class="text-xs text-slate-500">Usuario logado</p>
                    <p class="text-sm font-semibold text-slate-900"><?= $e((string) ($userLayout['name'] ?? '')) ?></p>
                    <p class="text-xs text-slate-600"><?= $e((string) ($userLayout['email'] ?? '')) ?></p>
                    <p class="mt-1 text-xs text-slate-600"><?= $e($roleLabel) ?></p>
                    <form method="post" action="<?= $url('/logout') ?>" class="mt-3">
                        <input type="hidden" name="csrf" value="<?= $e($csrfLayout) ?>">
                        <button class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="submit">
                            Sair
                        </button>
                    </form>
                </div>
            </aside>
            <main class="ml-64 p-4 md:p-8">
                <div class="mx-auto w-full max-w-6xl">
                    <?= $content ?>
                </div>
            </main>
        </div>
    <?php else: ?>
        <main class="mx-auto w-full max-w-7xl p-4 md:p-8">
            <?= $content ?>
        </main>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script>
        window.applyDateMask = function (input) {
            if (!input || input.dataset.dateMaskBound === '1') return;
            input.dataset.dateMaskBound = '1';
            input.setAttribute('placeholder', 'dd/mm/aaaa');
            input.addEventListener('input', function () {
                var digits = this.value.replace(/\D/g, '').slice(0, 8);
                if (digits.length > 4) {
                    this.value = digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4);
                    return;
                }
                if (digits.length > 2) {
                    this.value = digits.slice(0, 2) + '/' + digits.slice(2);
                    return;
                }
                this.value = digits;
            });
        };
        window.initDatePickers = function (root) {
            const scope = root || document;
            const inputs = scope.querySelectorAll('input[type="date"], input[data-datepicker="true"]');
            inputs.forEach((input) => {
                if (input._flatpickr) return;
                flatpickr(input, {
                    locale: flatpickr.l10ns.pt,
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    allowInput: true,
                    onReady: function (selectedDates, dateStr, instance) {
                        var visibleInput = instance.altInput || instance.input;
                        window.applyDateMask(visibleInput);
                    }
                });
            });
        };
        window.initMonthPickers = function (root) {
            const scope = root || document;
            const inputs = scope.querySelectorAll('input[data-month-picker="true"]');
            inputs.forEach((input) => {
                if (input._flatpickr) return;
                flatpickr(input, {
                    locale: flatpickr.l10ns.pt,
                    dateFormat: 'Y-m',
                    altInput: true,
                    altFormat: 'F Y',
                    allowInput: false,
                    plugins: [
                        new monthSelectPlugin({
                            shorthand: false,
                            dateFormat: 'Y-m',
                            altFormat: 'F Y'
                        })
                    ]
                });
            });
        };
        window.initPlannedRealAutofill = function () {
            const debounceTimers = new WeakMap();
            const getFieldByName = function (form, name) {
                if (!form) return null;
                let field = form.querySelector('input[name="' + name + '"]');
                if (field) return field;
                if (!form.id) return null;
                field = document.querySelector('input[name="' + name + '"][form="' + form.id + '"]');
                return field;
            };
            const resolveForm = function (input) {
                if (!input) return null;
                if (input.form) return input.form;
                const formId = input.getAttribute('form');
                if (!formId) return null;
                return document.getElementById(formId);
            };
            const syncRealWithPlanned = function (form, force) {
                if (!form) return;
                const plannedInput = getFieldByName(form, 'valor_planejado');
                const realInput = getFieldByName(form, 'valor_real');
                if (!plannedInput || !realInput) return;
                const isRealManual = realInput.dataset.realManual === '1';
                if (force || !isRealManual || String(realInput.value || '').trim() === '') {
                    realInput.value = plannedInput.value;
                }
            };

            document.addEventListener('input', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLInputElement)) return;
                if (target.name === 'valor_real') {
                    if (String(target.value || '').trim() === '') {
                        delete target.dataset.realManual;
                    } else {
                        target.dataset.realManual = '1';
                    }
                    return;
                }
                if (target.name !== 'valor_planejado') return;
                const form = resolveForm(target);
                const existingTimer = debounceTimers.get(target);
                if (existingTimer) {
                    clearTimeout(existingTimer);
                }
                const timer = setTimeout(function () {
                    syncRealWithPlanned(form, false);
                }, 300);
                debounceTimers.set(target, timer);
            });

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                syncRealWithPlanned(form, false);
            });
        };
        document.addEventListener('DOMContentLoaded', function () {
            window.initDatePickers(document);
            window.initMonthPickers(document);
            window.initPlannedRealAutofill();
            document.addEventListener('change', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLInputElement)) return;
                if (!target.matches('input[data-month-picker="true"]')) return;
                const form = target.closest('form');
                if (!form) return;
                form.submit();
            });
        });
    </script>
</body>

</html>
