<section class="mx-auto mt-10 max-w-2xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold tracking-tight"><?= $e($title ?? 'Error') ?></h1>
    <p class="mt-3 text-slate-700"><?= $e($message ?? 'Unexpected error.') ?></p>
    <a class="mt-5 inline-block rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="<?= $url('/') ?>">
        Back
    </a>
</section>

