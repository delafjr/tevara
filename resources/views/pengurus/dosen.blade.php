<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dosen - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php $user = $user ?? session('user'); $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0]; @endphp
<div class="flex min-h-screen">
    <x-sidebar active="dosen" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <header class="bg-white border-b border-slate-100 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <p class="text-xs text-slate-400 font-medium">Pengurus TEFA</p>
                <p class="text-[15px] font-semibold text-[#1E293B] leading-tight">Data Dosen</p>
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
                        <h1 class="text-[#0f172a] text-2xl font-bold leading-snug">Dosen Pembimbing & PM</h1>
                        <p class="text-[#64748b] text-sm mt-2 leading-relaxed">Kelola data dosen yang berperan sebagai pembimbing dan project manager di TeFa.</p>
                    </div>
                    <a href="#" class="flex items-center gap-2 bg-[#FFB800] text-[#1E293B] font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-yellow-300 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Dosen
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-4 gap-4">
                @foreach([['18','Total Dosen','#0B3B82'],['12','Aktif Mengajar','#16A34A'],['8','Project Manager','#6366F1'],['3','Jurusan','#F59E0B']] as [$v,$l,$c])
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <p class="text-3xl font-bold text-[#1E293B] leading-none">{{ $v }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $l }}</p>
                    <div class="h-1 rounded-full mt-3" style="background:{{ $c }}30"><div class="h-full rounded-full w-3/4" style="background:{{ $c }}"></div></div>
                </div>
                @endforeach
            </div>

            {{-- Tabel Dosen --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Daftar Dosen</p>
                        <p class="text-xs text-slate-400 mt-0.5">18 dosen terdaftar di Teaching Factory</p>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" placeholder="Cari dosen..." class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82] w-48">
                        <select class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82]">
                            <option>Semua Jurusan</option><option>TI</option><option>Elektro</option><option>Mesin</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Dosen</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">NIP</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Jurusan</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Keahlian</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Projek Aktif</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Total Mahasiswa</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Status</th>
                                <th class="px-3 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php
                            $dosens = [
                                ['name'=>'Dimas Setiawan, S.Kom., M.T.','nip'=>'198501012010011001','jurusan'=>'TI','keahlian'=>'Web Development','projek'=>2,'mhs'=>15,'status'=>'PM Aktif'],
                                ['name'=>'Rizky Pratama, S.T., M.Eng.','nip'=>'198703152012041002','jurusan'=>'Elektro','keahlian'=>'IoT & Embedded','projek'=>1,'mhs'=>6,'status'=>'PM Aktif'],
                                ['name'=>'Nadia Putri, S.Ds., M.Ds.','nip'=>'199001202015042003','jurusan'=>'TI','keahlian'=>'UI/UX Design','projek'=>1,'mhs'=>4,'status'=>'PM Aktif'],
                                ['name'=>'Thomas Elang, S.Kom., M.Cs.','nip'=>'198612102014011004','jurusan'=>'TI','keahlian'=>'Database & Aplikasi','projek'=>1,'mhs'=>5,'status'=>'PM Aktif'],
                                ['name'=>'Ari Wibowo, S.T., M.T.','nip'=>'197809082005011005','jurusan'=>'Mesin','keahlian'=>'Otomasi & Kontrol','projek'=>1,'mhs'=>9,'status'=>'PM Aktif'],
                                ['name'=>'Siti Rahayu, S.Kom., M.T.','nip'=>'199205172018042006','jurusan'=>'TI','keahlian'=>'E-Commerce','projek'=>1,'mhs'=>6,'status'=>'PM Aktif'],
                                ['name'=>'Budi Prasetyo, M.Kom.','nip'=>'198811292016041007','jurusan'=>'TI','keahlian'=>'Computer Vision','projek'=>1,'mhs'=>5,'status'=>'PM Aktif'],
                                ['name'=>'Erna Kusumawati, M.Pd.','nip'=>'197506102000032008','jurusan'=>'TI','keahlian'=>'Pembimbing','projek'=>0,'mhs'=>0,'status'=>'Pembimbing'],
                                ['name'=>'Hartono, S.T., M.T.','nip'=>'197312152001011009','jurusan'=>'Mesin','keahlian'=>'Manufaktur','projek'=>0,'mhs'=>0,'status'=>'Pembimbing'],
                            ];
                            $stStyle=['PM Aktif'=>'bg-blue-50 text-blue-700','Pembimbing'=>'bg-green-50 text-green-700'];
                            @endphp
                            @foreach($dosens as $d)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#0B3B82]/10 flex items-center justify-center text-[11px] font-bold text-[#0B3B82] flex-shrink-0">{{ strtoupper(substr($d['name'],0,1)).strtoupper(substr(explode(' ',$d['name'])[1]??'',0,1)) }}</div>
                                        <p class="font-semibold text-[#1E293B] text-[13px] leading-tight">{{ $d['name'] }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-[11px] text-slate-500 font-mono">{{ $d['nip'] }}</td>
                                <td class="px-3 py-3.5"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600">{{ $d['jurusan'] }}</span></td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-600">{{ $d['keahlian'] }}</td>
                                <td class="px-3 py-3.5 text-[12px] font-bold text-[#0B3B82] text-center">{{ $d['projek'] }}</td>
                                <td class="px-3 py-3.5 text-[12px] font-semibold text-slate-700 text-center">{{ $d['mhs'] }}</td>
                                <td class="px-3 py-3.5"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $stStyle[$d['status']] ?? 'bg-slate-100 text-slate-600' }}">{{ $d['status'] }}</span></td>
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
            </div>
        </main>
        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400 font-medium">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang · Teaching Factory</p>
        </footer>
    </div>
</div>
</body>
</html>
