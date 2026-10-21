@props(['active' => 'dashboard'])

@php
    $user = session('user') ?? [];
    $allowedRoles = \App\Services\ApiService::getUserRoles($user);
    $currentRole = session('active_role') ?? ($allowedRoles[0] ?? 'klien');

    // Pastikan currentRole ada di role yang diizinkan user
    if (!empty($allowedRoles) && !in_array($currentRole, $allowedRoles, true)) {
        $currentRole = $allowedRoles[0];
    }

    $userName = $user['name'] ?? 'User';

    // Ekstrak 2 huruf inisial dari nama
    $cleanWords = array_values(array_filter(
        explode(' ', $userName),
        fn($w) => !empty($w) && !str_contains($w, '.') && !str_contains($w, ',')
    ));
    if (count($cleanWords) >= 2) {
        $initials = mb_substr($cleanWords[0], 0, 1) . mb_substr($cleanWords[1], 0, 1);
    } elseif (count($cleanWords) === 1) {
        $initials = mb_substr($cleanWords[0], 0, 2);
    } else {
        $initials = mb_substr($userName, 0, 2);
    }
    $initials = strtoupper($initials);

    $rolesConfig = [
        'klien' => [
            'label' => 'Klien',
            'sub'   => 'Klien & Mitra',
        ],
        'mahasiswa' => [
            'label' => 'Mahasiswa',
            'sub'   => 'Mahasiswa',
        ],
        'pm' => [
            'label' => 'Project Manager',
            'sub'   => 'Project Manager',
        ],
        'dosen' => [
            'label' => 'Dosen',
            'sub'   => 'Dosen Pengampu',
        ],
        'pengurus_tefa' => [
            'label' => 'Pengurus TeFa',
            'sub'   => 'Pengelola TeFa',
        ],
        'jurusan' => [
            'label' => 'Jurusan',
            'sub'   => 'Pihak Jurusan',
        ],
    ];

    // Filter hanya role yang dimiliki user dari BE
    $availableRoles = array_intersect_key($rolesConfig, array_flip($allowedRoles));
    if (empty($availableRoles)) {
        $availableRoles = [$currentRole => $rolesConfig[$currentRole] ?? ['label' => ucfirst($currentRole), 'sub' => '']];
    }

    $roleLabel = $rolesConfig[$currentRole]['label'] ?? ucfirst($currentRole);

    // Menu sidebar sesuai spesifikasi tiap role
    $menus = [
        'klien' => [
            ['id' => 'dashboard',    'label' => 'Dashboard',             'icon' => 'dashboard',      'href' => route('dashboard')],
            ['id' => 'ajukan-projek','label' => 'Ajukan Projek',         'icon' => 'ajukan-projek',  'href' => '#'],
            ['id' => 'projek-saya',  'label' => 'Projek Saya',           'icon' => 'project',        'href' => '#'],
            ['id' => 'tagihan',      'label' => 'Tagihan & Pembayaran',  'icon' => 'tagihan',        'href' => '#'],
        ],
        'mahasiswa' => [
            ['id' => 'dashboard',    'label' => 'Dashboard',             'icon' => 'dashboard',      'href' => route('dashboard')],
            ['id' => 'project',      'label' => 'Project',               'icon' => 'project',        'href' => route('mahasiswa.project')],
            ['id' => 'task-saya',    'label' => 'Task Saya',             'icon' => 'task',           'href' => route('mahasiswa.task')],
        ],
        'pm' => [
            ['id' => 'dashboard',    'label' => 'Dashboard',             'icon' => 'dashboard',      'href' => route('dashboard')],
            ['id' => 'project',      'label' => 'Project',               'icon' => 'project',        'href' => '#'],
            ['id' => 'anggota-tim',  'label' => 'Anggota & Tim',         'icon' => 'team',           'href' => '#'],
            ['id' => 'task-timeline','label' => 'Task & Timeline',       'icon' => 'task',           'href' => '#'],
        ],
        'dosen' => [
            ['id' => 'dashboard',    'label' => 'Dashboard',             'icon' => 'dashboard',      'href' => route('dashboard')],
            ['id' => 'project',      'label' => 'Project',               'icon' => 'project',        'href' => '#'],
            ['id' => 'anggota-tim',  'label' => 'Anggota & Tim',         'icon' => 'team',           'href' => '#'],
            ['id' => 'task-timeline','label' => 'Task & Timeline',       'icon' => 'task',           'href' => '#'],
        ],
        'pengurus_tefa' => [
            ['id' => 'dashboard',         'label' => 'Dashboard',         'icon' => 'dashboard',      'href' => route('dashboard')],
            ['id' => 'projek',            'label' => 'Projek',            'icon' => 'project',        'href' => route('pengurus.projek')],
            ['id' => 'klien-mitra',       'label' => 'Klien & Mitra',     'icon' => 'team',           'href' => route('pengurus.klien-mitra')],
            ['id' => 'dosen',             'label' => 'Dosen',             'icon' => 'team',           'href' => route('pengurus.dosen')],
            ['id' => 'mahasiswa',         'label' => 'Mahasiswa',         'icon' => 'team',           'href' => route('pengurus.mahasiswa')],
            ['id' => 'validasi-proposal', 'label' => 'Validasi Proposal', 'icon' => 'proposal',       'href' => route('pengurus.validasi-proposal')],
            ['id' => 'keuangan',          'label' => 'Keuangan',          'icon' => 'tagihan',        'href' => route('pengurus.keuangan')],
            ['id' => 'monitoring',        'label' => 'Monitoring',        'icon' => 'monitoring',     'href' => route('pengurus.monitoring')],
        ],
        'jurusan' => [
            ['id' => 'dashboard',    'label' => 'Dashboard',             'icon' => 'dashboard',      'href' => route('dashboard')],
            ['id' => 'ajukan-projek','label' => 'Ajukan Projek',         'icon' => 'ajukan-projek',  'href' => '#'],
            ['id' => 'projek-saya',  'label' => 'Projek Saya',           'icon' => 'project',        'href' => '#'],
            ['id' => 'tagihan',      'label' => 'Tagihan & Pembayaran',  'icon' => 'tagihan',        'href' => '#'],
        ],
    ];

    $currentMenu = $menus[$currentRole] ?? $menus['klien'];
@endphp

<aside data-role="{{ $currentRole }}" class="w-64 min-h-screen bg-[#F8FAFC] border-r border-slate-200/70 flex flex-col fixed top-0 left-0 z-30 select-none">

    {{-- ===== LOGO TEVARA ===== --}}
    <div class="px-6 py-6 border-b border-slate-200/50 flex items-center">
        <a href="{{ route('dashboard') }}" class="block">
            <img src="{{ asset('images/logo-tevara.png') }}" alt="TEVARA Knowledge into Action" class="h-8 w-auto max-w-[185px] object-contain">
        </a>
    </div>

    {{-- ===== NAV MENU ===== --}}
    <nav class="flex-1 px-3.5 py-4 space-y-1.5 overflow-y-auto">
        @foreach($currentMenu as $item)
            @php
                $isActive = ($active === $item['id']);
            @endphp
            <a href="{{ $item['href'] }}"
               class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-2xl text-[13.5px] transition-all duration-150 {{ $isActive ? 'bg-[#DDE9FA] text-[#2563EB] font-semibold' : 'text-slate-500 hover:text-slate-900 hover:bg-white/70 font-medium' }}">
                <span class="w-5 h-5 flex-shrink-0 {{ $isActive ? 'text-[#2563EB]' : 'text-slate-400' }}">
                    @if($item['icon'] === 'dashboard')
                        {{-- Bento / Grid 4-square icon --}}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <rect width="7" height="7" x="3" y="3" rx="1.5"/>
                            <rect width="7" height="7" x="14" y="3" rx="1.5"/>
                            <rect width="7" height="7" x="14" y="14" rx="1.5"/>
                            <rect width="7" height="7" x="3" y="14" rx="1.5"/>
                        </svg>
                    @elseif($item['icon'] === 'project')
                        {{-- Folder icon --}}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <path d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                        </svg>
                    @elseif($item['icon'] === 'team')
                        {{-- Users / Team icon --}}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    @elseif($item['icon'] === 'task')
                        @if($currentRole === 'mahasiswa')
                            {{-- Circular Task Icon matching student design --}}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                                <circle cx="12" cy="12" r="9"/>
                                <polyline points="9 12 11.5 14.5 15.5 9.5"/>
                            </svg>
                        @else
                            {{-- Task & Timeline / Calendar checklist icon --}}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                                <rect width="18" height="18" x="3" y="4" rx="2"/>
                                <path d="M16 2v4"/>
                                <path d="M8 2v4"/>
                                <path d="M3 10h18"/>
                                <path d="m9 16 2 2 4-4"/>
                            </svg>
                        @endif
                    @elseif($item['icon'] === 'ajukan-projek')
                        {{-- Plus circle icon --}}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 8v8"/>
                            <path d="M8 12h8"/>
                        </svg>
                    @elseif($item['icon'] === 'tagihan')
                        {{-- Credit card / billing icon --}}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <rect width="20" height="14" x="2" y="5" rx="2"/>
                            <line x1="2" x2="22" y1="10" y2="10"/>
                        </svg>
                    @elseif($item['icon'] === 'proposal')
                        {{-- Clipboard check icon --}}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <rect x="8" y="2" width="8" height="4" rx="1"/>
                            <path d="m9 14 2 2 4-4"/>
                        </svg>
                    @elseif($item['icon'] === 'monitoring')
                        {{-- Monitoring / chart icon --}}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <path d="M3 3v18h18"/>
                            <path d="m19 9-5 5-4-4-3 3"/>
                        </svg>
                    @endif
                </span>
                <span class="flex-1 truncate">                <span class="sidebar-label">{{ $item['label'] }}</span></span>
            </a>
        @endforeach
    </nav>

    {{-- ===== PROFILE SECTION & ROLE SWITCHER ===== --}}
    <div class="p-3.5 mt-auto border-t border-slate-200/50" x-data="{ openRoleMenu: false }">
        <div class="relative">

            {{-- POPUP ROLE SWITCHER (Appears above the profile card) --}}
            <div
                x-show="openRoleMenu"
                x-cloak
                @click.outside="openRoleMenu = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                class="absolute bottom-full mb-2.5 left-0 right-0 bg-white rounded-2xl shadow-xl shadow-slate-300/40 border border-slate-100 p-2 z-50 text-slate-700"
            >
                <div class="px-2.5 py-1.5 flex items-center justify-between border-b border-slate-100 mb-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Switch Role</span>
                    <span class="text-[10px] font-semibold text-[#0B3B82] bg-[#EAF3FF] px-2 py-0.5 rounded-full">{{ count($availableRoles) }} Role</span>
                </div>

                {{-- Role Choices --}}
                <div class="space-y-0.5 py-1">
                    @foreach($availableRoles as $rKey => $rMeta)
                        @php
                            $isCurrent = ($currentRole === $rKey);
                        @endphp
                        <a
                            href="{{ route('switch-role.get', ['role' => $rKey]) }}"
                            class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ $isCurrent ? 'bg-[#EAF3FF] text-[#0B3B82]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full {{ $isCurrent ? 'bg-[#0B3B82]' : 'bg-slate-300' }}"></span>
                                <span>{{ $rMeta['label'] }}</span>
                            </div>
                            @if($isCurrent)
                                <svg class="w-4 h-4 text-[#0B3B82]" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </a>
                    @endforeach
                </div>

                {{-- Divider --}}
                <div class="h-px bg-slate-100 my-1"></div>

                {{-- Logout Action --}}
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Keluar / Logout</span>
                    </button>
                </form>
            </div>

            {{-- PROFILE BUTTON CARD (Triggers Pop-up) --}}
            <button
                @click="openRoleMenu = !openRoleMenu"
                type="button"
                class="w-full bg-white rounded-2xl p-3 flex items-center gap-3 shadow-xs border border-slate-100 hover:border-slate-200 transition-all duration-150 text-left cursor-pointer group"
                title="Klik untuk ganti role"
            >
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    {{-- Initials Badge --}}
                    <div class="w-10 h-10 rounded-xl bg-[#0B1E3F] flex items-center justify-center text-white font-bold text-xs tracking-wider flex-shrink-0 shadow-xs">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-bold text-[#0F172A] leading-tight truncate">{{ $userName }}</p>
                        <p class="text-[11px] text-slate-400 font-normal leading-tight mt-0.5 truncate">{{ $roleLabel }}</p>
                    </div>
                </div>
            </button>

        </div>
    </div>

</aside>
