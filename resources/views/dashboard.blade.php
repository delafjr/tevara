<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50/60 p-6 flex flex-col items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-primary/10 p-8 text-center">
        <div class="w-12 h-12 rounded-xl bg-success/10 text-success flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-text-primary">Dashboard</h1>
        <p class="text-sm text-text-secondary mt-2">Autentikasi berhasil. Selamat datang di dashboard Tevara.</p>
        <div class="mt-6">
            <a href="/auth/login" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-text-primary font-medium text-xs rounded-xl transition">
                Keluar / Kembali
            </a>
        </div>
    </div>
</body>
</html>
