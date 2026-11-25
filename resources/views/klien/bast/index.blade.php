<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAST &amp; Serah Terima - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F4F7FB] font-sans text-[#1E293B] antialiased">
<div class="flex min-h-screen">
    <x-sidebar active="bast" />

    <div class="ml-64 flex min-h-screen flex-1 flex-col">
        <main class="flex-1 px-8 py-7 space-y-6"
              x-data="{
                  searchQuery: '',
                  selectedStatus: 'all',
                  currentPage: 1,
                  perPage: 10,
                  projects: @js(array_values($projects ?? [])),
                  get filteredProjects() {
                      return this.projects.filter(p => {
                          const q = this.searchQuery.toLowerCase().trim();
                          const code = (p.project_code || p.proposal_code || ('PRSL-' + p.id)).toLowerCase();
                          const title = (p.title || '').toLowerCase();
                          const statusStr = (p.status || '').toLowerCase();
                          const uatRes = (p.latest_uat_result || '').toLowerCase();

                          const matchQuery = !q || code.includes(q) || title.includes(q) || statusStr.includes(q);
                          if (!matchQuery) return false;

                          if (this.selectedStatus === 'all') return true;
                          if (this.selectedStatus === 'approved') return !!p.client_approved;
                          if (this.selectedStatus === 'ready_approval') return !p.client_approved && !!p.bast_prepared;
                          if (this.selectedStatus === 'eligible') return !p.client_approved && !p.bast_prepared && !!p.bast_eligible;
                          if (this.selectedStatus === 'not_eligible') return !p.client_approved && !p.bast_prepared && !p.bast_eligible;
                          if (this.selectedStatus === 'completed') return statusStr === 'completed';
                          return true;
                      });
                  },
                  get totalPages() {
                      return Math.ceil(this.filteredProjects.length / this.perPage) || 1;
                  },
                  get paginatedProjects() {
                      const start = (this.currentPage - 1) * this.perPage;
                      return this.filteredProjects.slice(start, start + this.perPage);
                  },
                  resetPagination() {
                      this.currentPage = 1;
                  }
              }">

            {{-- Header Banner Serupa dengan Projek Saya --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Workspace Klien
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        BAST &amp; <span class="text-[#FFB800]">Serah Terima</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5">Tinjau kelayakan serah terima, status pengesahan UAT, dan setujui dokumen BAST resmi</p>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div role="status" class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p class="text-sm font-medium leading-relaxed">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error') || ($loadError ?? null))
                <div role="alert" class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <p class="text-sm font-medium leading-relaxed">{{ session('error') ?? $loadError }}</p>
                </div>
            @endif

            {{-- Filter & Pencarian Bar --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    {{-- Pencarian --}}
                    <div class="relative flex-1 max-w-md">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               x-model="searchQuery"
                               @input="resetPagination()"
                               placeholder="Cari nama proyek, kode proyek, atau status..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-xs font-medium text-slate-800 placeholder-slate-400 transition focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10" />
                    </div>

                    {{-- Filter Dropdown Status --}}
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">Filter Status:</span>
                        <select x-model="selectedStatus"
                                @change="resetPagination()"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold text-slate-700 transition focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 cursor-pointer">
                            <option value="all">Semua BAST</option>
                            <option value="approved">BAST Disetujui</option>
                            <option value="ready_approval">Menunggu Persetujuan</option>
                            <option value="eligible">UAT Lulus (Belum Disiapkan)</option>
                            <option value="not_eligible">Belum Memenuhi Syarat</option>
                            <option value="completed">Status: Completed</option>
                        </select>
                    </div>
                </div>

                {{-- Status Count Bar --}}
                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-500">
                    <p>
                        Menampilkan <span class="font-bold text-slate-800" x-text="paginatedProjects.length"></span> dari
                        <span class="font-bold text-slate-800" x-text="filteredProjects.length"></span> proyek
                    </p>
                    <template x-if="searchQuery || selectedStatus !== 'all'">
                        <button type="button"
                                @click="searchQuery = ''; selectedStatus = 'all'; resetPagination()"
                                class="text-xs font-bold text-[#0B3B82] hover:underline cursor-pointer">
                            Reset Filter
                        </button>
                    </template>
                </div>
            </div>

            {{-- Empty State jika data kosong --}}
            <template x-if="filteredProjects.length === 0">
                <div class="rounded-2xl border border-slate-100 bg-white px-8 py-12 text-center shadow-xs">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <h2 class="font-bold text-slate-800 text-base">Tidak Ada Dokumen BAST Ditemukan</h2>
                    <p class="mt-1 text-xs text-slate-500">Tidak ada proyek yang sesuai dengan kriteria pencarian atau filter yang dipilih.</p>
                </div>
            </template>

            {{-- Daftar Kartu BAST Ringkas (Ukuran pas serupa Projek Saya) --}}
            <div class="space-y-3" x-show="filteredProjects.length > 0">
                <template x-for="project in paginatedProjects" :key="project.id">
                    <article class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:border-slate-200 hover:shadow-md">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                            {{-- Bagian Kiri: Info Proyek & Metadata --}}
                            <div class="flex items-start gap-3.5 flex-1 min-w-0">
                                {{-- Ikon Proyek --}}
                                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-[#EAF3FF] text-[#0B3B82] shadow-2xs">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>

                                <div class="space-y-1 flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs font-bold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-md"
                                              x-text="project.project_code || project.proposal_code || ('PRSL-' + project.id)">
                                        </span>
                                        <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-md"
                                              x-text="project.status || 'Status'">
                                        </span>

                                        {{-- Badge Status BAST --}}
                                        <template x-if="project.client_approved">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                                BAST Disetujui
                                            </span>
                                        </template>
                                        <template x-if="!project.client_approved && project.bast_prepared">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                                Menunggu Persetujuan BAST
                                            </span>
                                        </template>
                                        <template x-if="!project.client_approved && !project.bast_prepared && project.bast_eligible">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                UAT Lulus &amp; Disahkan
                                            </span>
                                        </template>
                                        <template x-if="!project.client_approved && !project.bast_prepared && !project.bast_eligible">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Belum Memenuhi Syarat BAST
                                            </span>
                                        </template>
                                    </div>

                                    <h2 class="text-base font-bold text-slate-800 leading-snug truncate"
                                        x-text="project.title || 'Proyek tanpa judul'">
                                    </h2>

                                    {{-- Info Baris Ringkas: UAT & Garansi --}}
                                    <div class="flex items-center gap-4 text-xs text-slate-500 pt-1 flex-wrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="text-slate-400">Hasil UAT:</span>
                                            <span class="font-bold"
                                                  :class="(project.latest_uat_result || '').toUpperCase() === 'PASSED' ? 'text-emerald-700' : 'text-slate-700'"
                                                  x-text="project.latest_uat_result ? project.latest_uat_result.toUpperCase() : 'Belum ada UAT'">
                                            </span>
                                        </div>
                                        <span class="text-slate-300">&bull;</span>
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="text-slate-400">Masa Garansi:</span>
                                            <span class="font-bold text-slate-700"
                                                  x-text="(project.warranty && project.warranty.has_warranty) ? (project.warranty.warranty_months || project.warranty_months || 0) + ' Bulan' : (project.warranty_months ? project.warranty_months + ' Bulan' : 'Tidak ada garansi')">
                                            </span>
                                        </div>
                                        <template x-if="project.bast_number">
                                            <div class="inline-flex items-center gap-1.5">
                                                <span class="text-slate-300">&bull;</span>
                                                <span class="text-slate-400">No. BAST:</span>
                                                <span class="font-mono font-bold text-slate-700" x-text="project.bast_number"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Bagian Kanan: Aksi & Tombol Responsif --}}
                            <div class="flex items-center gap-3 flex-shrink-0 pt-2 lg:pt-0">
                                {{-- Jika BAST sudah disetujui -> Tombol Unduh PDF BAST --}}
                                <template x-if="project.client_approved">
                                    <a :href="'/klien/projek-saya/' + project.id + '/bast/pdf'"
                                       class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white shadow-2xs hover:shadow-sm transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                        </svg>
                                        <span>Unduh PDF BAST</span>
                                    </a>
                                </template>

                                {{-- Jika siap disetujui (BAST prepared) -> Tombol Tindakan Persetujuan --}}
                                <template x-if="!project.client_approved && project.bast_prepared">
                                    <a :href="'/klien/bast/' + project.id"
                                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:shadow-md transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                        </svg>
                                        <span>Tinjau &amp; Setujui BAST</span>
                                    </a>
                                </template>

                                {{-- Jika belum disiapkan/belum lulus -> Tombol Lihat Projek Saya --}}
                                <template x-if="!project.client_approved && !project.bast_prepared">
                                    <a :href="'/klien/projek-saya/' + project.id"
                                       class="inline-flex items-center gap-2 rounded-xl bg-[#EAF3FF] hover:bg-[#D9E9FF] text-[#0B3B82] px-4 py-2.5 text-xs font-bold transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        </svg>
                                        <span>Detail Projek</span>
                                    </a>
                                </template>
                            </div>
                        </div>

                        {{-- Alert Keterangan Pendek jika Belum Memenuhi Syarat --}}
                        <template x-if="!project.client_approved && !project.bast_eligible">
                            <div class="mt-3.5 border-t border-slate-100 pt-3 text-xs text-slate-500 flex items-center gap-2">
                                <svg class="h-4 w-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                                </svg>
                                <span x-text="(project.latest_uat_result || '').toUpperCase() !== 'PASSED' ? 'BAST belum dapat disetujui karena laporan UAT terbaru belum lulus.' : 'Menunggu pengesahan hasil UAT terbaru dari Pengurus TeFa.'"></span>
                            </div>
                        </template>
                        <template x-if="!project.client_approved && project.bast_eligible && !project.bast_prepared">
                            <div class="mt-3.5 border-t border-slate-100 pt-3 text-xs text-slate-500 flex items-center gap-2">
                                <svg class="h-4 w-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                                </svg>
                                <span>Hasil UAT telah lulus. Menunggu Pengurus TeFa menyiapkan dokumen BAST resmi.</span>
                            </div>
                        </template>
                    </article>
                </template>
            </div>

            {{-- Navigasi Pagination (Maks 10 Card Per Halaman) --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-white px-6 py-4 shadow-xs"
                 x-show="filteredProjects.length > 0">
                <div class="text-xs text-slate-500">
                    Halaman <span class="font-bold text-slate-800" x-text="currentPage"></span> dari <span class="font-bold text-slate-800" x-text="totalPages"></span>
                </div>

                <div class="flex items-center gap-2" x-show="totalPages > 1">
                    {{-- Tombol Sebelumnya --}}
                    <button type="button"
                            @click="currentPage > 1 ? currentPage-- : null"
                            :disabled="currentPage === 1"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                        Sebelumnya
                    </button>

                    {{-- Tombol Nomor Halaman --}}
                    <div class="flex items-center gap-1">
                        <template x-for="p in totalPages" :key="p">
                            <button type="button"
                                    @click="currentPage = p"
                                    class="h-8 w-8 rounded-lg text-xs font-bold transition flex items-center justify-center cursor-pointer"
                                    :class="currentPage === p ? 'bg-[#0B3B82] text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'"
                                    x-text="p">
                            </button>
                        </template>
                    </div>

                    {{-- Tombol Selanjutnya --}}
                    <button type="button"
                            @click="currentPage < totalPages ? currentPage++ : null"
                            :disabled="currentPage === totalPages"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                        Selanjutnya
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </div>
            </div>

        </main>
    </div>
</div>
</body>
</html>