<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">
@php
    $user = $user ?? session('user') ?? [];
    $userName = $user['name'] ?? 'Pengguna';
    $userEmail = $user['email'] ?? '—';
    $userPhone = $user['phone_number'] ?? '—';

    // Inisial avatar
    $cleanWords = array_values(array_filter(
        explode(' ', (string) $userName),
        fn($w) => !empty($w) && !str_contains($w, '.') && !str_contains($w, ',')
    ));
    if (count($cleanWords) >= 2) {
        $initials = mb_substr($cleanWords[0], 0, 1) . mb_substr($cleanWords[1], 0, 1);
    } elseif (count($cleanWords) === 1) {
        $initials = mb_substr($cleanWords[0], 0, 2);
    } else {
        $initials = mb_substr((string) $userName, 0, 2);
    }
    $initials = strtoupper(trim($initials));
    if ($initials === '') {
        $initials = 'U';
    }

    $roleLabels = [
        'klien'         => 'Klien & Mitra',
        'mahasiswa'     => 'Mahasiswa',
        'pm'            => 'Project Manager',
        'dosen'         => 'Dosen',
        'pengurus_tefa' => 'Pengurus TeFa',
        'jurusan'       => 'Jurusan Polines',
    ];

    $allRoles = $allRoles ?? \App\Services\ApiService::getUserRoles($user);
    $effectiveRoles = $effectiveRoles ?? \App\Services\ApiService::getEffectiveRoles($user);
    $activeRole = $activeRole ?? session('active_role') ?? ($effectiveRoles[0] ?? 'klien');
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="profile" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 flex items-center justify-between text-xs text-emerald-800">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 flex items-center justify-between text-xs text-red-800">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span class="font-semibold">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{-- Breadcrumb Navigation --}}
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Dashboard
                </a>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#0B3B82] bg-[#EAF3FF] border border-[#0B3B82]/20 px-3 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                    Akun Terverifikasi
                </span>
            </div>

            {{-- Hero Identity Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-70 pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-6">
                    {{-- Avatar Initial --}}
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-full flex items-center justify-center font-extrabold text-2xl tracking-wider select-none shadow-md"
                             style="width: 80px; height: 80px; min-width: 80px; min-height: 80px; border-radius: 9999px; background: linear-gradient(135deg, #0B3B82 0%, #1E56A0 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 1.625rem; font-weight: 800; box-shadow: 0 4px 14px rgba(11, 59, 130, 0.25); border: 3px solid #FFFFFF;">
                            {{ $initials }}
                        </div>
                    </div>

                    {{-- User Details --}}
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-2xl font-extrabold text-[#1E293B] tracking-tight truncate">
                                {{ $userName }}
                            </h1>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B3B82] bg-[#EAF3FF] px-3 py-1 rounded-full border border-[#0B3B82]/10">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82] animate-pulse"></span>
                                Role Aktif: {{ $roleLabels[$activeRole] ?? ucfirst($activeRole) }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500 pt-1">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                                {{ $userEmail }}
                            </span>

                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                                {{ $userPhone }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Multi-Role Overview Card --}}
            <div class="rounded-3xl border border-slate-100 bg-white p-7 shadow-sm space-y-5">
                <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#0B3B82]"></span>
                            <h2 class="text-base font-bold text-[#1E293B]">Hak Akses &amp; Multi-Role Anda</h2>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            @if (count($effectiveRoles) > 1)
                                Akun Anda memiliki <span class="font-bold text-[#0B3B82]">{{ count($effectiveRoles) }} role aktif</span>. Anda dapat beralih ruang kerja sesuai tanggung jawab Anda.
                            @else
                                Akun Anda saat ini memiliki <span class="font-bold text-[#0B3B82]">1 role</span> terdaftar dalam sistem.
                            @endif
                        </p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 self-start sm:self-auto">
                        Total Role: {{ count($effectiveRoles) }}
                    </span>
                </div>

                {{-- Role Cards Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($effectiveRoles as $r)
                        @php
                            $isActive = ($activeRole === $r);
                        @endphp
                        <div class="rounded-2xl border {{ $isActive ? 'border-[#0B3B82] bg-[#F8FAFC] shadow-sm ring-1 ring-[#0B3B82]/20' : 'border-slate-100 bg-white hover:border-slate-200' }} p-5 transition-all flex flex-col justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-2 text-xs font-bold {{ $isActive ? 'text-[#0B3B82]' : 'text-slate-700' }}">
                                        <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-[#0B3B82]' : 'bg-slate-300' }}"></span>
                                        {{ $roleLabels[$r] ?? ucfirst($r) }}
                                    </span>
                                    @if ($isActive)
                                        <span class="text-[10px] font-bold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-full">
                                            Sedang Digunakan
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-500 leading-relaxed">
                                    @if ($r === 'pengurus_tefa')
                                        Mengelola proposal, validasi proyek, manajemen BAST, dan registrasi klien.
                                    @elseif ($r === 'dosen')
                                        Membimbing proyek, memantau kemajuan mahasiswa, dan review UAT.
                                    @elseif ($r === 'pm')
                                        Memimpin koordinasi sprint, pembagian task modul, dan persetujuan deliverable.
                                    @elseif ($r === 'mahasiswa')
                                        Mengerjakan task proyek, update progres pengerjaan, dan pengujian.
                                    @elseif ($r === 'klien')
                                        Mengajukan proyek, evaluasi dokumen BAST, pembayaran, dan sign-off UAT.
                                    @elseif ($r === 'jurusan')
                                        Mengajukan proyek internal Polines dan monitoring pelaksanaan TeFa.
                                    @else
                                        Hak akses operasional sistem Tevara.
                                    @endif
                                </p>
                            </div>

                            @if (!$isActive)
                                <a href="{{ route('switch-role.get', ['role' => $r]) }}"
                                   class="inline-flex items-center justify-center gap-1.5 w-full py-2 rounded-xl text-xs font-semibold text-[#0B3B82] bg-[#EAF3FF] hover:bg-[#d8e8fe] transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                    </svg>
                                    Beralih ke Role Ini
                                </a>
                            @else
                                <div class="py-1 text-[11px] font-semibold text-emerald-600 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Sesi aktif saat ini
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Detail Profil Berdasarkan Role --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Informasi Akademik / Instansi --}}
                <div class="rounded-3xl border border-slate-100 bg-white p-7 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-sm font-bold text-[#1E293B]">Informasi Entitas &amp; Akademik</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Data pelengkap identitas sesuai role yang Anda miliki.</p>
                    </div>

                    <dl class="space-y-4 text-xs">
                        {{-- Data Mahasiswa jika ada --}}
                        @if (!empty($user['nim']) || in_array('mahasiswa', $allRoles, true))
                            <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">NIM</dt>
                                <dd class="font-bold font-mono text-slate-800">{{ $user['nim'] ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Program Studi</dt>
                                <dd class="font-semibold text-slate-800">{{ $user['study_program'] ?? '—' }}</dd>
                            </div>
                        @endif

                        {{-- Data Dosen jika ada --}}
                        @if (!empty($user['nidn']) || in_array('dosen', $allRoles, true) || in_array('pm', $allRoles, true))
                            <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">NIDN</dt>
                                <dd class="font-bold font-mono text-slate-800">{{ $user['nidn'] ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Jurusan / Departemen</dt>
                                <dd class="font-semibold text-slate-800">{{ $user['department'] ?? 'Teknik Elektro / TI' }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Project Manager</dt>
                                <dd class="font-semibold text-slate-800">
                                    @if (!empty($user['is_pm']))
                                        <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-bold">Terdaftar Aktif</span>
                                    @else
                                        <span class="text-slate-500">Tidak</span>
                                    @endif
                                </dd>
                            </div>
                        @endif

                        {{-- Data Klien / Perusahaan jika ada --}}
                        @if (!empty($user['company_name']) || in_array('klien', $allRoles, true))
                            <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Instansi / Perusahaan</dt>
                                <dd class="font-bold text-slate-800">{{ $user['company_name'] ?? $user['name'] }}</dd>
                            </div>
                            @if (!empty($user['company_email']))
                                <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                    <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Email Perusahaan</dt>
                                    <dd class="font-semibold text-slate-800">{{ $user['company_email'] }}</dd>
                                </div>
                            @endif
                            @if (!empty($user['position']))
                                <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                    <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Jabatan / Posisi</dt>
                                    <dd class="font-semibold text-slate-800">{{ $user['position'] }}</dd>
                                </div>
                            @endif
                        @endif

                        {{-- Default info jika pengurus tefa murni --}}
                        @if (in_array('pengurus_tefa', $allRoles, true) && empty($user['nim']) && empty($user['nidn']))
                            <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Unit Kerja</dt>
                                <dd class="font-semibold text-slate-800">Pengelola Teaching Factory Polines</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Status & Informasi Keamanan Akun --}}
                <div class="rounded-3xl border border-slate-100 bg-white p-7 shadow-sm space-y-5 flex flex-col justify-between">
                    <div class="space-y-5">
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-sm font-bold text-[#1E293B]">Informasi Sistem &amp; Keamanan</h3>
                            <p class="mt-0.5 text-xs text-slate-500">Status autentikasi dan riwayat pendaftaran akun.</p>
                        </div>

                        <dl class="space-y-4 text-xs">
                            <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Status Akun</dt>
                                <dd class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </dd>
                            </div>

                            <div class="flex items-center justify-between py-1 border-b border-slate-50">
                                <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Terdaftar Sejak</dt>
                                <dd class="font-semibold text-slate-800">
                                    {{ !empty($user['created_at']) ? \Carbon\Carbon::parse($user['created_at'])->translatedFormat('d M Y, H:i') : '—' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400">Ingin keluar dari akun ini?</span>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700 hover:underline">
                                Logout dari Aplikasi
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            {{-- Card Ubah Password --}}
            <div class="rounded-3xl border border-slate-100 bg-white p-7 shadow-sm space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0B3B82]"></span>
                        <h2 class="text-base font-bold text-[#1E293B]">Keamanan &amp; Ubah Password</h2>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Perbarui password akun Anda secara berkala. Password minimal harus terdiri dari 8 karakter.</p>
                </div>

                <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-5 max-w-2xl">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Password Baru --}}
                        <div x-data="{ show: false }">
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'"
                                       id="password"
                                       name="password"
                                       required
                                       minlength="8"
                                       placeholder="Minimal 8 karakter"
                                       class="w-full rounded-xl border @error('password') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror pl-3.5 pr-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none" tabindex="-1" aria-label="Toggle password visibility">
                                    <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ulangi / Retype Password --}}
                        <div x-data="{ show: false }">
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Ulangi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       required
                                       minlength="8"
                                       placeholder="Ketik ulang password baru"
                                       class="w-full rounded-xl border @error('password_confirmation') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror pl-3.5 pr-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none" tabindex="-1" aria-label="Toggle password visibility">
                                    <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-start pt-1">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0B3B82] hover:bg-[#1E56A0] text-white text-xs font-semibold shadow-sm transition-all duration-150 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            <span>Simpan Password Baru</span>
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>
</div>
</body>
</html>
