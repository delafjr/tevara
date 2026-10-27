@props(['active' => 'dashboard', 'title' => 'TEVARA'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - TEVARA</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] font-sans text-slate-800 antialiased min-h-screen">
    <div class="flex min-h-screen">
        <x-sidebar :active="$active" />
        
        <main class="flex-1 ml-64 min-h-screen flex flex-col px-8 py-8 lg:px-10 lg:py-9">
            <div class="w-full max-w-[1400px] mx-auto space-y-6">
                {{ $slot }}
            </div>
            
            <footer class="mt-auto pt-10 pb-4 text-center text-xs text-slate-400">
                © {{ date('Y') }} TEVARA · Politeknik Negeri Semarang
            </footer>
        </main>
    </div>
</body>
</html>
