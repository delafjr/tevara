<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Projek - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php $user = $user ?? session('user'); $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0]; @endphp
<div class="flex min-h-screen">
    <x-sidebar active="projek" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <header class="bg-white border-b border-slate-100 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <p class="text-xs text-slate-400 font-medium">Pengurus TEFA</p>
                <p class="text-[15px] font-semibold text-[#1E293B] leading-tight">Manajemen Projek</p>
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
                        <h1 class="text-[#0f172a] text-2xl font-bold leading-snug">Kelola Semua Projek TeFa</h1>
                        <p class="text-[#64748b] text-sm mt-2 leading-relaxed">Pantau status, progress, dan tim untuk setiap projek yang berjalan.</p>
                    </div>
                    <a href="#" class="flex items-center gap-2 bg-[#FFB800] text-[#1E293B] font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-yellow-300 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Projek
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-4 gap-4">
                @foreach([['28','Total Projek','#0B3B82'],['17','Sedang Berjalan','#16A34A'],['4','Dalam Review','#F59E0B'],['7','Proposal Masuk','#6366F1']] as [$v,$l,$c])
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <p class="text-3xl font-bold text-[#1E293B] leading-none">{{ $v }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $l }}</p>
                    <div class="h-1 rounded-full mt-3" style="background:{{ $c }}30"><div class="h-full rounded-full w-3/4" style="background:{{ $c }}"></div></div>
                </div>
                @endforeach
            </div>

            {{-- Filter + Tabel --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Daftar Projek</p>
                        <p class="text-xs text-slate-400 mt-0.5">Semua projek yang terdaftar</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" placeholder="Cari projek..." class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82] w-48">
                        <select class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82]">
                            <option>Semua Status</option>
                            <option>Berjalan</option><option>Review</option><option>Selesai</option><option>Proposal</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Projek</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Klien / Mitra</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">PM / Dosen</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Mahasiswa</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Status</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3 w-32">Progress</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Deadline</th>
                                <th class="px-3 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php
                            $projects = [
                                ['name'=>'Sistem Informasi UMKM','client'=>'PT. ABC Indonesia','pm'=>'Dimas Setiawan','mhs'=>8,'cat'=>'Website','status'=>'Berjalan','prog'=>75,'dl'=>'12 Okt 2026'],
                                ['name'=>'Smart Farming IoT','client'=>'CV. Tani Maju','pm'=>'Rizky Pratama','mhs'=>6,'cat'=>'IoT','status'=>'Review','prog'=>60,'dl'=>'5 Nov 2026'],
                                ['name'=>'Website Company Profile','client'=>'PT. DEF Solusi','pm'=>'Nadia Putri','mhs'=>4,'cat'=>'Website','status'=>'Selesai','prog'=>100,'dl'=>'—'],
                                ['name'=>'Aplikasi Inventori','client'=>'PT. Maju Sentosa','pm'=>'Thomas Elang','mhs'=>5,'cat'=>'Aplikasi','status'=>'Proposal','prog'=>25,'dl'=>'18 Nov 2026'],
                                ['name'=>'Monitoring Produksi','client'=>'CV. Tani Maju','pm'=>'Dimas Setiawan','mhs'=>7,'cat'=>'Aplikasi','status'=>'Berjalan','prog'=>40,'dl'=>'25 Nov 2026'],
                                ['name'=>'Sistem Parkir Cerdas','client'=>'Pemkot Semarang','pm'=>'Ari Wibowo','mhs'=>9,'cat'=>'IoT','status'=>'Berjalan','prog'=>55,'dl'=>'1 Des 2026'],
                                ['name'=>'E-Commerce UMKM Batik','client'=>'Koperasi Batik','pm'=>'Siti Rahayu','mhs'=>6,'cat'=>'Website','status'=>'Review','prog'=>80,'dl'=>'10 Nov 2026'],
                                ['name'=>'Absensi Digital','client'=>'SMK Negeri 1','pm'=>'Budi Prasetyo','mhs'=>5,'cat'=>'Aplikasi','status'=>'Berjalan','prog'=>30,'dl'=>'20 Des 2026'],
                            ];
                            $ss=['Berjalan'=>'bg-blue-50 text-blue-700','Review'=>'bg-amber-50 text-amber-700','Selesai'=>'bg-green-50 text-green-700','Proposal'=>'bg-purple-50 text-purple-700'];
                            $pc=fn($p)=>$p>=100?'#16A34A':($p>=60?'#0B3B82':($p>=30?'#F59E0B':'#DC2626'));
                            @endphp
                            @foreach($projects as $p)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3.5">
                                    <p class="font-semibold text-[#1E293B] text-[13px] leading-tight">{{ $p['name'] }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $p['cat'] }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-600">{{ $p['client'] }}</td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-600">{{ $p['pm'] }}</td>
                                <td class="px-3 py-3.5"><span class="text-[12px] font-semibold text-[#0B3B82]">{{ $p['mhs'] }} mhs</span></td>
                                <td class="px-3 py-3.5"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $ss[$p['status']] ?? 'bg-slate-100 text-slate-600' }}">{{ $p['status'] }}</span></td>
                                <td class="px-3 py-3.5 w-32">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full" style="width:{{ $p['prog'] }}%;background:{{ $pc($p['prog']) }}"></div>
                                        </div>
                                        <span class="text-[11px] font-semibold text-slate-500 w-7 text-right">{{ $p['prog'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-500">{{ $p['dl'] }}</td>
                                <td class="px-3 py-3.5">
                                    <button class="p-1 rounded hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/></svg>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3.5 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-400">Menampilkan 8 dari 28 projek</p>
                    <div class="flex items-center gap-1">
                        <button class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-500 hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors">← Prev</button>
                        <button class="px-3 py-1.5 text-xs rounded-lg bg-[#0B3B82] text-white">1</button>
                        <button class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-500 hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors">2</button>
                        <button class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-500 hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors">3</button>
                        <button class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-500 hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors">Next →</button>
                    </div>
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
