<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-white font-sans antialiased text-[#1E293B]">
    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- ===== LEFT COLUMN: BRAND HERO WITH BACKGROUND IMAGE ===== --}}
        <div class="hidden lg:flex lg:w-1/2 relative bg-cover bg-center items-center justify-center p-12 text-center select-none"
             style="background-image: url('{{ asset('images/bg_login_gelap.png') }}');">
            
            <div class="relative z-10 max-w-lg flex flex-col items-center">
                {{-- Logo --}}
                <img src="{{ asset('images/tevara-logo-vertical.png') }}" 
                     alt="TEVARA Knowledge into Action" 
                     class="w-52 xl:w-60 h-auto object-contain mb-8">

                {{-- Yellow accent bar --}}
                <div class="w-44 h-1.5 bg-[#FFB800] rounded-full mb-8"></div>

                {{-- Tagline Heading --}}
                <h1 class="text-3xl xl:text-4xl font-extrabold text-[#0B254A] tracking-tight leading-snug">
                    Dari Kampus<br>Menuju Dampak
                </h1>

                {{-- Subtext --}}
                <p class="mt-6 text-sm xl:text-base text-slate-500 font-medium leading-relaxed max-w-sm">
                    TEVARA menghubungkan dunia akademik dengan kebutuhan industri melalui kolaborasi, proyek nyata, dan peningkatan kompetensi.
                </p>
            </div>
        </div>

        {{-- ===== RIGHT COLUMN: LOGIN FORM ===== --}}
        <div class="flex-1 flex flex-col justify-between px-6 sm:px-12 lg:px-16 xl:px-20 py-8 lg:py-10 bg-white min-h-screen">
            
            {{-- Top Navigation: Kembali & Logo --}}
            <div class="flex items-center justify-between w-full">
                {{-- Button Kembali redirect ke Main Page --}}
                <a href="{{ url('/') }}" 
                   class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-[#0B254A] transition-colors group">
                    <svg class="w-4 h-4 text-slate-500 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    <span>Kembali</span>
                </a>

                {{-- Logo Top Right --}}
                <a href="{{ url('/') }}" class="block">
                    <img src="{{ asset('images/tevara-logo.png') }}" 
                         alt="TEVARA Knowledge into Action" 
                         class="h-8 lg:h-9 w-auto object-contain">
                </a>
            </div>

            {{-- Form Content Center --}}
            <div class="w-full max-w-[440px] mx-auto my-auto py-10">
                <div class="mb-8">
                    <h2 class="text-3xl xl:text-[34px] font-extrabold text-[#0B254A] tracking-tight">
                        Selamat Datang
                    </h2>
                    <p class="text-sm font-medium text-slate-500 mt-2">
                        Masuk ke Dashboard TEVARA
                    </p>
                </div>

                {{-- Alert Errors --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="/auth/login" method="POST" class="space-y-5">
                    @csrf

                    {{-- Email Input --}}
                    <div>
                        <label for="email" class="block text-sm font-bold text-[#0B254A] mb-2">
                            Email<span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus
                               placeholder="john.doe@example.com"
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm text-[#0B254A] placeholder:text-slate-400 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/15 transition">
                    </div>

                    {{-- Password Input with toggle visibility --}}
                    <div x-data="{ showPassword: false }">
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-bold text-[#0B254A]">
                                Kata Sandi<span class="text-red-500">*</span>
                            </label>
                            <a href="#" class="text-sm font-bold text-[#0B3B82] hover:underline">
                                Lupa kata sandi?
                            </a>
                        </div>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder="***********"
                                   class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-300 text-sm text-[#0B254A] placeholder:text-slate-400 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/15 transition [&::-ms-reveal]:hidden [&::-ms-clear]:hidden">
                            <button type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                    aria-label="Lihat kata sandi">
                                {{-- Eye icon (when hidden) --}}
                                <svg x-show="!showPassword" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                {{-- Eye slash icon (when visible) --}}
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-3.5 px-4 bg-[#0B3B82] hover:bg-[#082e66] text-white font-bold text-sm rounded-xl transition shadow-xs cursor-pointer">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>

            {{-- Bottom spacer --}}
            <div class="w-full text-center py-2 text-xs text-slate-400 select-none">
                &nbsp;
            </div>

        </div>

    </div>
</body>
</html>
