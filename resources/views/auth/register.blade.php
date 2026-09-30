<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50/60 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-primary/10 p-8">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-extrabold text-primary">Buat Akun Tevara</h1>
            <p class="text-sm text-text-secondary mt-1">Daftarkan akun baru Anda</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-error/20 rounded-xl text-xs text-error">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/auth/register" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold text-text-primary mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    placeholder="nama@email.com"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-text-primary mb-1">Kata Sandi</label>
                <input type="password" id="password" name="password" minlength="8" required
                    placeholder="Minimal 8 karakter"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
            </div>

            <button type="submit"
                class="w-full py-2.5 px-4 bg-primary hover:bg-primary/90 text-white font-semibold text-sm rounded-xl transition shadow-md shadow-primary/25 cursor-pointer">
                Daftar
            </button>
        </form>

        <p class="mt-6 text-center text-xs text-text-secondary">
            Sudah punya akun?
            <a href="/auth/login" class="font-bold text-primary hover:underline">Masuk di sini</a>
        </p>
    </div>
</body>
</html>
