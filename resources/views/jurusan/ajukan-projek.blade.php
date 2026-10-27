<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajukan Projek - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#F8F9FB] font-['Inter'] text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user');
    $userName = $user['name'];
@endphp

<div class="flex min-h-screen">

    {{-- Pemanggilan Sidebar (otomatis menyorot menu Ajukan Projek) --}}
    <x-sidebar active="ajukan" />

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen p-8 gap-6">
        
        {{-- Banner Header --}}
        <div class="bg-gradient-to-r from-white to-blue-50/30 rounded-2xl p-8 shadow-sm border border-slate-100">
            <h1 class="text-2xl font-bold text-[#1E293B]">Ajukan Projek <span class="text-[#F59E0B]">Baru</span></h1>
            <p class="text-slate-500 text-sm mt-1">Silakan lengkapi spesifikasi proyek Anda di bawah ini. Tim analis dan engineer TEVARA akan meninjau estimasi kebutuhan serta menghubungi Anda dalam waktu 1x24 jam kerja.</p>
        </div>

        <form class="space-y-6">
            <!-- Step 1: Informasi Klien -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold">1</span>
                    <div>
                        <h2 class="font-bold text-[#1E293B]">Informasi Klien & Instansi</h2>
                        <p class="text-xs text-slate-400">Identitas pemohon dan penanggung jawab proyek</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">NAMA KLIEN / INSTANSI *</label>
                        <input type="text" value="{{ $userName }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white focus:ring-[#0B3B82]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">NAMA PERUSAHAAN (OPSIONAL)</label>
                        <input type="text" placeholder="Jika instansi/badan usaha" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">KONTAK — NAMA PIC *</label>
                        <input type="text" value="{{ $userName }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">KONTAK — NO. WHATSAPP / HP *</label>
                        <input type="text" value="+62 812-3456-7890" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white">
                    </div>
                </div>
            </div>

            <!-- Step 2: Rincian Proyek -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold">2</span>
                    <div>
                        <h2 class="font-bold text-[#1E293B]">Rincian & Kebutuhan Proyek</h2>
                        <p class="text-xs text-slate-400">Deskripsikan cakupan dan sasaran pengembangan sistem</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">JUDUL PROYEK *</label>
                        <input type="text" placeholder="Contoh: Pengembangan Smart Monitoring Dashboard IoT Terpadu" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">KATEGORI PROYEK *</label>
                        <div class="grid grid-cols-5 gap-3">
                            <button type="button" class="p-3 border-2 border-[#0B3B82] bg-blue-50/50 text-[#0B3B82] rounded-xl font-semibold text-xs flex flex-col items-center gap-1">🌐 Web</button>
                            <button type="button" class="p-3 border border-slate-200 text-slate-600 rounded-xl font-semibold text-xs flex flex-col items-center gap-1 hover:bg-slate-50">📱 Mobile</button>
                            <button type="button" class="p-3 border border-slate-200 text-slate-600 rounded-xl font-semibold text-xs flex flex-col items-center gap-1 hover:bg-slate-50">💻 Desktop</button>
                            <button type="button" class="p-3 border border-slate-200 text-slate-600 rounded-xl font-semibold text-xs flex flex-col items-center gap-1 hover:bg-slate-50">⚙️ IoT</button>
                            <button type="button" class="p-3 border border-slate-200 text-slate-600 rounded-xl font-semibold text-xs flex flex-col items-center gap-1 hover:bg-slate-50">••• Other</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">LATAR BELAKANG & KEBUTUHAN PROYEK *</label>
                        <textarea rows="4" placeholder="Uraikan problem yang ingin diselesaikan, fitur prioritas, flow bisnis utama, serta integrasi apa saja yang dibutuhkan..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white"></textarea>
                    </div>
                </div>
            </div>

            <!-- Footer Submit -->
            <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100">
                <span class="text-xs text-slate-400">🛡️ Data terlindungi dengan enkripsi keamanan standar TEVARA.</span>
                <button type="button" class="bg-[#0B3B82] text-white px-6 py-3 rounded-xl font-semibold text-sm hover:bg-[#082a5c]">Ajukan Proyek Sekarang →</button>
            </div>
        </form>

    </div>
</div>

</body>
</html>