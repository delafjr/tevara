<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validasi Proposal - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php $user = $user ?? session('user'); $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0]; @endphp
<div class="flex min-h-screen">
    <x-sidebar active="validasi-proposal" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <header class="bg-white border-b border-slate-100 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <p class="text-xs text-slate-400 font-medium">Pengurus TEFA</p>
                <p class="text-[15px] font-semibold text-[#1E293B] leading-tight">Validasi Proposal</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                <div class="w-px h-4 bg-slate-200"></div>
                <form action="{{ route('logout') }}" method="POST">@csrf
                    <button type="submit" class="text-xs text-slate-500 hover:text-[#0B3B82] font-medium transition-colors">Logout</button>
                </form>
            </div>
        </header>
        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#eef4ff] via-[#f0f5ff] to-[#eef4ff] px-8 py-7 border border-[#dde8ff] shadow-sm">
                <div class="absolute right-40 top-1/2 -translate-y-1/2 w-52 h-52 rounded-full bg-[#FFB800]/10 blur-2xl"></div>
                <div class="absolute right-24 -bottom-10 w-40 h-40 rounded-full bg-[#FFB800]/20"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div class="max-w-lg">
                        <p class="flex items-center gap-1.5 text-[#64748b] text-xs font-semibold tracking-widest mb-2 uppercase">Teaching Factory · Polines</p>
                        <h1 class="text-[#0f172a] text-2xl font-bold leading-snug">Validasi Proposal Projek</h1>
                        <p class="text-[#64748b] text-sm mt-2 leading-relaxed">Tinjau, setujui, atau tolak proposal projek yang masuk dari klien dan tim internal.</p>
                    </div>
                    <div class="flex items-center gap-2 bg-white/10 border border-white/20 rounded-xl px-5 py-3.5">
                        <div class="text-center">
                            <p class="text-3xl font-bold text-[#FFB800]">7</p>
                            <p class="text-blue-200 text-xs">Menunggu Validasi</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-4 gap-4">
                @foreach([['7','Menunggu Validasi','#F59E0B'],['18','Disetujui','#16A34A'],['3','Dikembalikan','#EF4444'],['28','Total Proposal','#0B3B82']] as [$v,$l,$c])
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <p class="text-3xl font-bold text-[#1E293B] leading-none">{{ $v }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $l }}</p>
                    <div class="h-1 rounded-full mt-3" style="background:{{ $c }}30"><div class="h-full rounded-full w-3/4" style="background:{{ $c }}"></div></div>
                </div>
                @endforeach
            </div>

            {{-- Proposal Cards - Menunggu Validasi --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <p class="font-semibold text-[#1E293B] text-[15px]">Proposal Menunggu Validasi</p>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">7 Proposal</span>
                </div>
                <div class="grid grid-cols-3 gap-5">
                    @php
                    $proposals = [
                        ['title'=>'Sistem Manajemen Parkir Cerdas','client'=>'Pemkot Semarang','pm'=>'Ari Wibowo','cat'=>'IoT','budget'=>'Rp 12.000.000','date'=>'18 Okt 2026','mhs'=>9,'duration'=>'4 bulan','desc'=>'Pengembangan sistem parkir berbasis sensor dan IoT untuk area perkotaan Semarang.'],
                        ['title'=>'Platform E-Learning Pesantren','client'=>'Yayasan Al-Hikmah','pm'=>'Siti Rahayu','cat'=>'Website','budget'=>'Rp 8.500.000','date'=>'17 Okt 2026','mhs'=>6,'duration'=>'3 bulan','desc'=>'Sistem pembelajaran online berbasis web untuk membantu santri belajar dari rumah.'],
                        ['title'=>'Aplikasi Absensi Wajah','client'=>'PT. Sejahtera','pm'=>'Budi Prasetyo','cat'=>'Aplikasi','budget'=>'Rp 15.000.000','date'=>'16 Okt 2026','mhs'=>5,'duration'=>'3 bulan','desc'=>'Aplikasi presensi karyawan menggunakan teknologi face recognition berbasis deep learning.'],
                        ['title'=>'Website Profil UMKM Kreatif','client'=>'Dinas UMKM Jateng','pm'=>'Nadia Putri','cat'=>'Website','budget'=>'Rp 6.000.000','date'=>'15 Okt 2026','mhs'=>4,'duration'=>'2 bulan','desc'=>'Platform digital untuk mempromosikan produk UMKM kreatif di Jawa Tengah.'],
                        ['title'=>'Sistem Monitoring Greenhouse','client'=>'CV. Agro Jaya','pm'=>'Rizky Pratama','cat'=>'IoT','budget'=>'Rp 18.000.000','date'=>'14 Okt 2026','mhs'=>7,'duration'=>'5 bulan','desc'=>'Monitoring suhu, kelembapan, dan nutrisi tanaman secara real-time menggunakan IoT.'],
                        ['title'=>'Aplikasi Manajemen Koperasi','client'=>'Koperasi Warga Jaya','pm'=>'Thomas Elang','cat'=>'Aplikasi','budget'=>'Rp 9.500.000','date'=>'13 Okt 2026','mhs'=>5,'duration'=>'3 bulan','desc'=>'Sistem pengelolaan simpan pinjam dan anggota koperasi secara digital dan terintegrasi.'],
                        ['title'=>'Dashboard Analytics Toko','client'=>'Toko Santosa Jaya','pm'=>'Dimas Setiawan','cat'=>'Website','budget'=>'Rp 7.000.000','date'=>'12 Okt 2026','mhs'=>4,'duration'=>'2 bulan','desc'=>'Dashboard analitik penjualan dan stok barang dengan visualisasi data yang komprehensif.'],
                    ];
                    $catColor=['IoT'=>'bg-amber-50 text-amber-700','Website'=>'bg-blue-50 text-blue-700','Aplikasi'=>'bg-purple-50 text-purple-700'];
                    @endphp
                    @foreach($proposals as $p)
                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:border-slate-200 hover:shadow-md transition-all">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg {{ $catColor[$p['cat']] ?? 'bg-slate-100 text-slate-600' }}">{{ $p['cat'] }}</span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ $p['date'] }}</span>
                            </div>
                            <h3 class="font-bold text-[#1E293B] text-[13px] leading-tight mb-1">{{ $p['title'] }}</h3>
                            <p class="text-[11px] text-slate-500 leading-snug line-clamp-2">{{ $p['desc'] }}</p>
                        </div>
                        <div class="px-5 py-3.5 space-y-1.5">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/></svg>
                                <span class="text-[11px] text-slate-600">{{ $p['client'] }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                <span class="text-[11px] text-slate-600">PM: {{ $p['pm'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    <span class="text-[11px] font-bold text-[#0B3B82]">{{ $p['budget'] }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                                    <span class="text-[11px] text-slate-500">{{ $p['mhs'] }} mhs · {{ $p['duration'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-5 pb-4 flex gap-2">
                            <button class="flex-1 bg-[#0B3B82] text-white text-[12px] font-bold py-2 rounded-lg hover:bg-blue-900 transition-colors">
                                ✓ Setujui
                            </button>
                            <button class="px-3 bg-slate-100 text-slate-600 text-[12px] font-semibold py-2 rounded-lg hover:bg-slate-200 transition-colors">
                                Tinjau
                            </button>
                            <button class="px-3 bg-red-50 text-red-600 text-[12px] font-semibold py-2 rounded-lg hover:bg-red-100 transition-colors">
                                ✕
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Riwayat Proposal --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <p class="font-semibold text-[#1E293B] text-[15px]">Riwayat Validasi</p>
                    <span class="text-xs text-slate-400">21 proposal telah diproses</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Proposal</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Klien</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Tanggal</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Anggaran</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Hasil</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php
                            $history = [
                                ['name'=>'Sistem Informasi UMKM','client'=>'PT. ABC Indonesia','date'=>'5 Okt 2026','budget'=>'Rp 12.000.000','hasil'=>'Disetujui'],
                                ['name'=>'Smart Farming IoT','client'=>'CV. Tani Maju','date'=>'2 Okt 2026','budget'=>'Rp 20.000.000','hasil'=>'Disetujui'],
                                ['name'=>'Redesign Website Kampus','client'=>'Polines','date'=>'28 Sep 2026','budget'=>'Rp 5.000.000','hasil'=>'Dikembalikan'],
                                ['name'=>'Aplikasi Inventori','client'=>'PT. Maju Sentosa','date'=>'20 Sep 2026','budget'=>'Rp 14.000.000','hasil'=>'Disetujui'],
                            ];
                            $hStyle=['Disetujui'=>'bg-green-50 text-green-700','Dikembalikan'=>'bg-red-50 text-red-700'];
                            @endphp
                            @foreach($history as $h)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3 text-[13px] font-semibold text-[#1E293B]">{{ $h['name'] }}</td>
                                <td class="px-3 py-3 text-[12px] text-slate-600">{{ $h['client'] }}</td>
                                <td class="px-3 py-3 text-[12px] text-slate-500">{{ $h['date'] }}</td>
                                <td class="px-3 py-3 text-[12px] font-semibold text-[#1E293B]">{{ $h['budget'] }}</td>
                                <td class="px-3 py-3"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $hStyle[$h['hasil']] ?? 'bg-slate-100 text-slate-600' }}">{{ $h['hasil'] }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400 font-medium">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang · Teaching Factory</p>
        </footer>
    </div>
</div>
</body>
</html>
