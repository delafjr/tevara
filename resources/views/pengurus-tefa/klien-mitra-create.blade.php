<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Perusahaan &amp; Mitra - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="klien-mitra" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- Breadcrumb Navigation --}}
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('pengurus-tefa.klien-mitra') }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Data Klien &amp; Mitra
                </a>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#0B3B82] bg-[#EAF3FF] border border-[#0B3B82]/20 px-3 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                    Form Registrasi Perusahaan
                </span>
            </div>

            {{-- Hero Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Teaching Factory · Polines
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Registrasi <span class="text-[#FFB800]">Perusahaan / Mitra</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5">Daftarkan akun perusahaan klien baru ke dalam sistem database Tevara</p>
                </div>
            </div>

            {{-- Global Alerts --}}
            @if (session('error'))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <p class="font-semibold">Mohon perbaiki kendala berikut:</p>
                    <ul class="mt-1.5 list-disc list-inside space-y-1 text-xs">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Registration Form --}}
            <form action="{{ route('pengurus-tefa.klien-mitra.registrasi.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Section 1: Profil & Akun Perusahaan --}}
                <div class="rounded-2xl border border-slate-100 bg-white p-7 shadow-sm space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0B3B82]/10 text-xs font-bold text-[#0B3B82]">1</span>
                            <h2 class="text-base font-bold text-[#1E293B]">Data Akun &amp; Profil Perusahaan</h2>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Nama perusahaan akan tersimpan di tabel user dan menjadi identitas utama akun klien.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Nama Perusahaan (users.name) --}}
                        <div class="md:col-span-2">
                            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Perusahaan / Instansi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                   placeholder="Contoh: PT. Digital Solusi Nusantara"
                                   class="w-full rounded-xl border @error('name') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email Perusahaan (users.email) --}}
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Email Resmi Perusahaan <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" required value="{{ old('email') }}"
                                   placeholder="info@perusahaan.com"
                                   class="w-full rounded-xl border @error('email') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nomor Telepon Kantor --}}
                        <div>
                            <label for="phone_number" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                No. Telepon Kantor / Hotline
                            </label>
                            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}"
                                   placeholder="Contoh: 024-76481234 atau 08123456789"
                                   class="w-full rounded-xl border @error('phone_number') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                            @error('phone_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tipe Klien --}}
                        <div class="md:col-span-2">
                            <label for="client_type" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kategori Mitra
                            </label>
                            <select id="client_type" name="client_type"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                                <option value="EXTERNAL" {{ old('client_type', 'EXTERNAL') === 'EXTERNAL' ? 'selected' : '' }}>Eksternal (Perusahaan Swasta / BUMN / Industri)</option>
                                <option value="INTERNAL" {{ old('client_type') === 'INTERNAL' ? 'selected' : '' }}>Internal (Unit Kerja / Jurusan Polines)</option>
                            </select>
                        </div>

                        {{-- Alamat Perusahaan --}}
                        <div class="md:col-span-2">
                            <label for="address" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Alamat Kantor / Instansi
                            </label>
                            <textarea id="address" name="address" rows="3"
                                      placeholder="Alamat lengkap instansi atau domisili kantor mitra..."
                                      class="w-full rounded-xl border @error('address') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password Akun --}}
                        <div x-data="{ show: false }">
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Password Akun <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" id="password" name="password" required minlength="8"
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

                        {{-- Konfirmasi Password --}}
                        <div x-data="{ show: false }">
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required minlength="8"
                                       placeholder="Ulangi password di atas"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50/60 pl-3.5 pr-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
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
                        </div>
                    </div>
                </div>

                {{-- Section 2: Person In Charge (PIC) - Opsional --}}
                <div class="rounded-2xl border border-slate-100 bg-white p-7 shadow-sm space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-xs font-bold text-emerald-700">2</span>
                                <h2 class="text-base font-bold text-[#1E293B]">Informasi PIC (Person in Charge)</h2>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">Opsional</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Jika diisi, akun PIC akan otomatis dibuatkan dan dikaitkan sebagai kontak perwakilan perusahaan.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Nama PIC --}}
                        <div>
                            <label for="pic_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Lengkap PIC
                            </label>
                            <input type="text" id="pic_name" name="pic_name" value="{{ old('pic_name') }}"
                                   placeholder="Contoh: Hendra Kusuma, S.Kom."
                                   class="w-full rounded-xl border @error('pic_name') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                            @error('pic_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Posisi / Jabatan PIC --}}
                        <div>
                            <label for="pic_position" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Posisi / Jabatan PIC
                            </label>
                            <input type="text" id="pic_position" name="pic_position" value="{{ old('pic_position') }}"
                                   placeholder="Contoh: IT Manager / Product Owner"
                                   class="w-full rounded-xl border @error('pic_position') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                            @error('pic_position')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email PIC --}}
                        <div>
                            <label for="pic_email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Email PIC
                            </label>
                            <input type="email" id="pic_email" name="pic_email" value="{{ old('pic_email') }}"
                                   placeholder="hendra@perusahaan.com"
                                   class="w-full rounded-xl border @error('pic_email') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                            @error('pic_email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Telepon PIC --}}
                        <div>
                            <label for="pic_phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nomor WhatsApp / Telepon PIC
                            </label>
                            <input type="text" id="pic_phone" name="pic_phone" value="{{ old('pic_phone') }}"
                                   placeholder="081234567890"
                                   class="w-full rounded-xl border @error('pic_phone') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
                            @error('pic_phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password Akun PIC (Opsional) --}}
                        <div x-data="{ show: false }">
                            <label for="pic_password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Password Akun PIC <span class="text-slate-400 font-normal">(opsional, default: Password123!)</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" id="pic_password" name="pic_password" minlength="8"
                                       placeholder="Minimal 8 karakter jika diatur sendiri"
                                       class="w-full rounded-xl border @error('pic_password') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror pl-3.5 pr-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
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
                            @error('pic_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Konfirmasi Password PIC --}}
                        <div x-data="{ show: false }">
                            <label for="pic_password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Ulangi Password PIC <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" id="pic_password_confirmation" name="pic_password_confirmation" minlength="8"
                                       placeholder="Ulangi password PIC"
                                       class="w-full rounded-xl border @error('pic_password_confirmation') border-red-300 bg-red-50/30 @else border-slate-200 bg-slate-50/60 @enderror pl-3.5 pr-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 transition-colors">
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
                            @error('pic_password_confirmation')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Action Card --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('pengurus-tefa.klien-mitra') }}"
                       class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-semibold transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#0B3B82] hover:bg-[#092d64] text-white text-xs font-semibold shadow-sm transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Daftarkan Perusahaan
                    </button>
                </div>
            </form>

        </main>
    </div>
</div>
</body>
</html>
