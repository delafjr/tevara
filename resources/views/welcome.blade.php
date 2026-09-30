<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users - Tevara API Client</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="min-h-full bg-slate-900 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white"
      x-data="usersPage({
          apiUrl: '{{ $apiUrl }}',
          initialData: {{ Js::from($initialData) }},
          initialError: {{ Js::from($initialError) }}
      })"
      x-init="initData()">

    <!-- Background glow decoration -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -right-40 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl"></div>
    </div>

    <!-- Top Navigation Bar -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">Tevara</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">API Client</span>
                    </div>
                    <p class="text-xs text-slate-400 hidden sm:block">User Management Dashboard</p>
                </div>
            </div>

            <!-- API Endpoint Pill & Refresh button -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <div class="hidden md:flex items-center space-x-2 bg-slate-800/80 border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-slate-300 shadow-inner">
                    <span class="px-1.5 py-0.5 font-bold uppercase rounded text-[10px] bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">GET</span>
                    <span class="font-mono text-slate-300 max-w-[280px] truncate" x-text="apiUrl"></span>
                </div>

                <button @click="hitApi()" 
                        :disabled="isLoading"
                        class="inline-flex items-center space-x-2 px-3.5 py-2 text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 transition duration-150 shadow-md shadow-indigo-600/30 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    <svg class="w-4 h-4" :class="{'animate-spin': isLoading}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="hidden sm:inline" x-text="isLoading ? 'Memuat...' : 'Hit API'">Hit API</span>
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Hero & API Banner -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-3">
                        Daftar Pengguna
                        <span class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700" 
                              x-text="filteredUsers.length + ' User'"></span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Data diambil langsung via method <span class="text-emerald-400 font-mono font-semibold">GET</span> dari endpoint <code class="px-1.5 py-0.5 bg-slate-800 rounded font-mono text-indigo-300 text-xs" x-text="apiUrl"></code>
                    </p>
                </div>

                <!-- API Status indicator -->
                <div class="flex items-center space-x-2 text-xs">
                    <span class="text-slate-400">Status API:</span>
                    <template x-if="responseSuccess">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                            200 OK (Connected)
                        </span>
                    </template>
                    <template x-if="!responseSuccess && !isLoading && errorMessage">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mr-1.5"></span>
                            Error Connection
                        </span>
                    </template>
                    <template x-if="isLoading">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5 animate-ping"></span>
                            Memuat Data...
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-slate-800/60 border border-slate-700/70 rounded-2xl p-5 backdrop-blur-sm shadow-sm hover:border-slate-600 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Users</span>
                    <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-3xl font-bold text-white tracking-tight" x-text="users.length"></span>
                    <span class="text-xs text-slate-400 ml-1">terdaftar</span>
                </div>
            </div>

            <div class="bg-slate-800/60 border border-slate-700/70 rounded-2xl p-5 backdrop-blur-sm shadow-sm hover:border-slate-600 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Role Admin</span>
                    <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-3xl font-bold text-purple-400 tracking-tight" x-text="countRole('admin')"></span>
                    <span class="text-xs text-slate-400 ml-1">administrator</span>
                </div>
            </div>

            <div class="bg-slate-800/60 border border-slate-700/70 rounded-2xl p-5 backdrop-blur-sm shadow-sm hover:border-slate-600 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Role Member</span>
                    <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-3xl font-bold text-emerald-400 tracking-tight" x-text="countRole('member')"></span>
                    <span class="text-xs text-slate-400 ml-1">anggota aktif</span>
                </div>
            </div>

            <div class="bg-slate-800/60 border border-slate-700/70 rounded-2xl p-5 backdrop-blur-sm shadow-sm hover:border-slate-600 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">API Message</span>
                    <span class="p-2 rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <p class="text-sm font-semibold text-slate-200 truncate" x-text="apiMessage || 'Tidak ada pesan'"></p>
                    <span class="text-xs text-slate-400 font-mono" x-text="lastFetched ? 'Diupdate ' + lastFetched : 'Belum dimuat'"></span>
                </div>
            </div>
        </div>

        <!-- Error Alert (If API fails) -->
        <template x-if="errorMessage">
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 flex items-start space-x-3">
                <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1 text-sm">
                    <p class="font-semibold text-rose-200">Gagal Mengambil Data Pengguna</p>
                    <p class="mt-1 text-xs text-rose-300/90" x-text="errorMessage"></p>
                    <p class="mt-2 text-xs text-rose-300/70">
                        Pastikan server backend API berjalan di <code class="px-1 py-0.5 bg-rose-950/60 rounded" x-text="apiUrl"></code>.
                    </p>
                </div>
                <button @click="hitApi()" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-500 text-white transition">
                    Coba Lagi
                </button>
            </div>
        </template>

        <!-- Main Card Section -->
        <div class="bg-slate-800/40 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden backdrop-blur-md">
            
            <!-- Controls Bar: Search, Filters & View Mode -->
            <div class="p-4 sm:p-5 border-b border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari nama, email, atau ID..."
                           class="w-full pl-10 pr-4 py-2 text-sm bg-slate-900/90 border border-slate-700 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>

                <!-- Role Filter Tabs & View Toggle -->
                <div class="flex items-center flex-wrap gap-2.5">
                    
                    <!-- Role Filter -->
                    <div class="inline-flex rounded-xl bg-slate-900/90 p-1 border border-slate-700/80 text-xs">
                        <button @click="selectedRole = 'all'"
                                :class="selectedRole === 'all' ? 'bg-indigo-600 text-white font-semibold shadow' : 'text-slate-400 hover:text-white'"
                                class="px-3 py-1.5 rounded-lg transition">
                            Semua
                        </button>
                        <button @click="selectedRole = 'admin'"
                                :class="selectedRole === 'admin' ? 'bg-purple-600 text-white font-semibold shadow' : 'text-slate-400 hover:text-white'"
                                class="px-3 py-1.5 rounded-lg transition">
                            Admin
                        </button>
                        <button @click="selectedRole = 'member'"
                                :class="selectedRole === 'member' ? 'bg-emerald-600 text-white font-semibold shadow' : 'text-slate-400 hover:text-white'"
                                class="px-3 py-1.5 rounded-lg transition">
                            Member
                        </button>
                    </div>

                    <!-- Layout Toggle (Table vs Cards) -->
                    <div class="inline-flex rounded-xl bg-slate-900/90 p-1 border border-slate-700/80 text-xs">
                        <button @click="viewMode = 'table'"
                                :class="viewMode === 'table' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white'"
                                title="Tampilan Tabel"
                                class="p-1.5 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </button>
                        <button @click="viewMode = 'grid'"
                                :class="viewMode === 'grid' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white'"
                                title="Tampilan Card Grid"
                                class="p-1.5 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                    </div>

                    <!-- JSON Modal/Drawer Toggle -->
                    <button @click="showJsonDrawer = !showJsonDrawer"
                            class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl bg-slate-900/90 border border-slate-700/80 text-xs text-slate-300 hover:text-white hover:border-slate-600 transition">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span>Raw JSON</span>
                    </button>
                </div>
            </div>

            <!-- Loading Skeleton -->
            <template x-if="isLoading">
                <div class="p-6 space-y-4 animate-pulse">
                    <div class="h-12 bg-slate-700/40 rounded-xl"></div>
                    <div class="h-12 bg-slate-700/30 rounded-xl"></div>
                    <div class="h-12 bg-slate-700/20 rounded-xl"></div>
                </div>
            </template>

            <!-- Table View Mode -->
            <template x-if="!isLoading && viewMode === 'table'">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-900/60 text-xs uppercase text-slate-400 font-semibold tracking-wider border-b border-slate-700/80">
                            <tr>
                                <th scope="col" class="py-3.5 px-6">ID</th>
                                <th scope="col" class="py-3.5 px-6">User</th>
                                <th scope="col" class="py-3.5 px-6">Email</th>
                                <th scope="col" class="py-3.5 px-6">Role</th>
                                <th scope="col" class="py-3.5 px-6 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <template x-for="user in filteredUsers" :key="user.id">
                                <tr class="hover:bg-slate-700/30 transition group">
                                    <td class="py-4 px-6 font-mono text-xs text-slate-400">
                                        <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-700 text-slate-300" x-text="'#' + user.id"></span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs text-white shadow"
                                                 :class="user.role === 'admin' ? 'bg-gradient-to-tr from-purple-600 to-indigo-600' : 'bg-gradient-to-tr from-emerald-600 to-teal-600'"
                                                 x-text="getInitials(user.name)">
                                            </div>
                                            <div>
                                                <div class="font-semibold text-white group-hover:text-indigo-400 transition" x-text="user.name"></div>
                                                <div class="text-xs text-slate-400 sm:hidden" x-text="user.email"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-slate-300 font-mono text-xs">
                                        <a :href="'mailto:' + user.email" class="hover:text-indigo-300 underline underline-offset-2 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span x-text="user.email"></span>
                                        </a>
                                    </td>
                                    <td class="py-4 px-6">
                                        <template x-if="user.role === 'admin'">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/30">
                                                <svg class="w-3 h-3 mr-1 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                Admin
                                            </span>
                                        </template>
                                        <template x-if="user.role !== 'admin'">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                                <svg class="w-3 h-3 mr-1 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Member
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <span class="inline-flex items-center text-xs text-emerald-400 font-medium">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 mr-1.5"></span>
                                            Active
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

            <!-- Card Grid View Mode -->
            <template x-if="!isLoading && viewMode === 'grid'">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <div class="bg-slate-900/80 border border-slate-700/80 hover:border-indigo-500/60 rounded-2xl p-5 transition duration-200 hover:shadow-xl hover:shadow-indigo-500/5 group flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-sm text-white shadow-lg"
                                         :class="user.role === 'admin' ? 'bg-gradient-to-tr from-purple-600 to-indigo-600 shadow-purple-500/20' : 'bg-gradient-to-tr from-emerald-600 to-teal-600 shadow-emerald-500/20'"
                                         x-text="getInitials(user.name)">
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-800 border border-slate-700 text-xs font-mono text-slate-300" x-text="'#' + user.id"></span>
                                </div>

                                <div class="mt-4">
                                    <h3 class="text-base font-bold text-white group-hover:text-indigo-400 transition" x-text="user.name"></h3>
                                    <a :href="'mailto:' + user.email" class="mt-1 text-xs text-slate-400 hover:text-indigo-300 font-mono block truncate" x-text="user.email"></a>
                                </div>
                            </div>

                            <div class="mt-5 pt-4 border-t border-slate-800 flex items-center justify-between">
                                <template x-if="user.role === 'admin'">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/30">
                                        Admin
                                    </span>
                                </template>
                                <template x-if="user.role !== 'admin'">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                        Member
                                    </span>
                                </template>

                                <span class="inline-flex items-center text-xs text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                                    Active
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!isLoading && filteredUsers.length === 0">
                <div class="py-16 text-center px-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center mx-auto text-slate-400 mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-white">Tidak ada pengguna ditemukan</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                        Coba sesuaikan kata kunci pencarian atau filter role yang Anda gunakan.
                    </p>
                    <button @click="searchQuery = ''; selectedRole = 'all'" class="mt-4 px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                        Reset Filter
                    </button>
                </div>
            </template>
        </div>

        <!-- Raw JSON API Response Drawer / Accordion -->
        <div class="mt-8 bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg" x-show="showJsonDrawer" x-transition>
            <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">RESPONSE</span>
                    <span class="text-xs font-semibold text-slate-300">Raw JSON dari API <span class="font-mono text-slate-400" x-text="apiUrl"></span></span>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="copyJson()" 
                            class="px-2.5 py-1 text-xs rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span x-text="copied ? 'Disalin!' : 'Copy JSON'"></span>
                    </button>
                </div>
            </div>
            <pre class="bg-slate-950/80 p-4 rounded-xl text-xs font-mono text-emerald-400 overflow-x-auto border border-slate-800/80 leading-relaxed max-h-96"
                 x-text="JSON.stringify(rawResponse, null, 2)"></pre>
        </div>

    </main>

    <footer class="mt-16 border-t border-slate-800 py-6 text-center text-xs text-slate-500">
        <p>Tevara &copy; {{ date('Y') }} &bull; Laravel + Tailwind CSS + Alpine.js API Client</p>
    </footer>

    <!-- Alpine.js Component Script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('usersPage', (config) => ({
                apiUrl: config.apiUrl || '',
                users: [],
                rawResponse: null,
                apiMessage: '',
                responseSuccess: false,
                isLoading: false,
                errorMessage: config.initialError || '',
                searchQuery: '',
                selectedRole: 'all',
                viewMode: 'table',
                showJsonDrawer: true,
                copied: false,
                lastFetched: '',

                initData() {
                    if (config.initialData && config.initialData.success) {
                        this.processResponse(config.initialData);
                    } else if (!config.initialError) {
                        this.hitApi();
                    }
                },

                async hitApi() {
                    this.isLoading = true;
                    this.errorMessage = '';
                    try {
                        const response = await fetch(this.apiUrl, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                        }

                        const data = await response.json();
                        this.processResponse(data);
                    } catch (err) {
                        console.error('API Fetch error:', err);
                        this.errorMessage = err.message || 'Terjadi kesalahan saat memanggil API.';
                        this.responseSuccess = false;
                    } finally {
                        this.isLoading = false;
                    }
                },

                processResponse(data) {
                    this.rawResponse = data;
                    this.responseSuccess = Boolean(data.success);
                    this.apiMessage = data.message || '';
                    this.users = Array.isArray(data.data) ? data.data : [];
                    
                    const now = new Date();
                    this.lastFetched = now.toLocaleTimeString();
                },

                get filteredUsers() {
                    return this.users.filter(user => {
                        const matchesRole = this.selectedRole === 'all' || user.role === this.selectedRole;
                        const q = this.searchQuery.toLowerCase().trim();
                        const matchesSearch = !q || 
                            user.name.toLowerCase().includes(q) || 
                            user.email.toLowerCase().includes(q) || 
                            String(user.id).includes(q);
                        return matchesRole && matchesSearch;
                    });
                },

                countRole(role) {
                    return this.users.filter(u => u.role === role).length;
                },

                getInitials(name) {
                    if (!name) return 'U';
                    const parts = name.trim().split(' ');
                    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
                    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
                },

                copyJson() {
                    if (!this.rawResponse) return;
                    navigator.clipboard.writeText(JSON.stringify(this.rawResponse, null, 2));
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2000);
                }
            }));
        });
    </script>
</body>
</html>
