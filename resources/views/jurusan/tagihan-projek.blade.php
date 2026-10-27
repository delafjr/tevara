<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tagihan Projek - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#F8F9FB] font-['Inter'] text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user');
    $userName = $user['name'];
@endphp

<div class="flex min-h-screen">

    {{-- Pemanggilan Sidebar (otomatis menyorot menu Tagihan & Pembayaran) --}}
    <x-sidebar active="tagihan" />

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen p-8 gap-6">

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-[#1E293B]">Tagihan & Pembayaran Proyek</h1>
                <p class="text-slate-500 text-sm">Pantau status penagihan termin proyek (SPK), riwayat pembayaran terverifikasi, dan unggah bukti transfer termin secara terintegrasi dengan akuntansi Politeknik.</p>
            </div>

            {{-- Alert Banner --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold">🔔</div>
                    <div>
                        <p class="text-xs font-bold text-amber-800">PERLU TINDAKAN KLIEN · Jatuh Tempo: 15 Okt 2026</p>
                        <h3 class="font-bold text-amber-900">Unggah Bukti Pembayaran Termin Berjalan (Termin 2)</h3>
                    </div>
                </div>
                <button class="bg-[#0B3B82] text-white px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-[#082a5c]">Unggah Bukti Bayar Sekarang</button>
            </div>

            {{-- Section Rekening --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-slate-100 space-y-2">
                    <span class="text-xs font-semibold text-slate-400">BANK MANDIRI (GIRO)</span>
                    <p class="text-lg font-bold text-[#1E293B]">142-00-9928172-1</p>
                    <p class="text-xs text-slate-500">a.n. Teaching Factory Politeknik</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-100 space-y-2">
                    <span class="text-xs font-semibold text-slate-400">BCA VIRTUAL ACCOUNT</span>
                    <p class="text-lg font-bold text-[#1E293B]">88720-08123456789</p>
                    <p class="text-xs text-slate-500">a.n. TEVARA - {{ $userName }}</p>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>