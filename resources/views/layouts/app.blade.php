<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .turbo-progress-bar {
            background-color: #130F26;
            height: 3px;
        }

        .shimmer-overlay {
            position: fixed;
            z-index: 100;
            background: #F8FAFC;
            padding: 2rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            overflow: hidden;
            border-top-left-radius: 1rem;
        }

        .shimmer-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .shimmer-block {
            background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 0.75rem;
        }

        .shimmer-block.rounded-full {
            border-radius: 9999px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }
    </style>

    <script>
        (function () {
            if (window.__shimmerInitialized) return;
            window.__shimmerInitialized = true;

            var MIN_DISPLAY_MS = 300;
            var startTime = 0;

            var routeMap = {
                '/dashboard': 'shimmer-dashboard',
                '/pegawai': 'shimmer-pegawai',
                '/presensi': 'shimmer-presensi',
                '/laporan': 'shimmer-presensi',
                '/lembur': 'shimmer-presensi',
                '/penggunaan-poin': 'shimmer-presensi',
                '/pengajuan-izin': 'shimmer-presensi',
                '/face': 'shimmer-presensi',
                '/pengumuman': 'shimmer-pengumuman',
                '/jadwal': 'shimmer-calendar',
                '/role': 'shimmer-master',
                '/divisi': 'shimmer-master',
                '/jabatan': 'shimmer-master',
                '/shift': 'shimmer-master',
                '/kantor': 'shimmer-master',
                '/hari-libur': 'shimmer-master',
                '/profile': 'shimmer-form',
                '/cuti': 'shimmer-presensi',
                '/surat-izin': 'shimmer-presensi',
                '/tukar-shift': 'shimmer-presensi',
                '/notifikasi': 'shimmer-presensi',
            };

            function getShimmerId(url) {
                try {
                    var path = new URL(url).pathname;
                    if (path.match(/\/(create|edit)/) || path.match(/\/\d+\/edit/)) {
                        return 'shimmer-form';
                    }
                    for (var route in routeMap) {
                        if (path === route || path.startsWith(route + '/')) {
                            return routeMap[route];
                        }
                    }
                } catch (e) { }
                return 'shimmer-master';
            }

            var fallbackTimer = null;

            function hideAll() {
                var scrollContainer = document.getElementById('main-scroll-container');
                if (scrollContainer) scrollContainer.style.overflow = '';
                document.body.style.overflow = '';

                document.querySelectorAll('.shimmer-overlay').forEach(function (el) {
                    el.classList.remove('active');
                });
                if (fallbackTimer) { clearTimeout(fallbackTimer); fallbackTimer = null; }
            }

            function hideAfterPaint() {
                var elapsed = Date.now() - startTime;
                var remaining = Math.max(0, MIN_DISPLAY_MS - elapsed);

                if (remaining > 0) {
                    setTimeout(function() {
                        requestAnimationFrame(function() {
                            requestAnimationFrame(hideAll);
                        });
                    }, remaining);
                } else {
                    requestAnimationFrame(function() {
                        requestAnimationFrame(hideAll);
                    });
                }
            }

            document.addEventListener('turbo:before-cache', function() {
                var sidebar = document.querySelector('aside .overflow-y-auto');
                if (sidebar) {
                    sessionStorage.setItem('sidebarScroll', sidebar.scrollTop);
                }
                hideAll();
            });

            document.addEventListener('turbo:visit', function (e) {
                hideAll();

                window.scrollTo(0, 0);
                var mainScrollContainer = document.getElementById('main-scroll-container');
                if (mainScrollContainer) {
                    mainScrollContainer.scrollTop = 0;
                }

                startTime = Date.now();
                var id = getShimmerId(e.detail.url);
                var el = document.getElementById(id);
                if (el) {
                    var scrollContainer = document.getElementById('main-scroll-container');
                    var targetContent = document.getElementById('main-content');
                    if (targetContent && scrollContainer) {
                        scrollContainer.scrollTop = 0;

                        var rect = targetContent.getBoundingClientRect();
                        el.style.top = rect.top + 'px';
                        el.style.left = rect.left + 'px';
                        el.style.width = rect.width + 'px';
                        el.style.height = rect.height + 'px';

                        scrollContainer.style.overflow = 'hidden';
                        document.body.style.overflow = 'hidden';
                    }
                    el.classList.add('active');
                }
                fallbackTimer = setTimeout(hideAll, 8000);
            });

            document.addEventListener('turbo:before-render', function (event) {
                var activeShimmers = document.querySelectorAll('.shimmer-overlay.active');
                if (activeShimmers.length > 0 && event.detail.newBody) {
                    activeShimmers.forEach(function(el) {
                        var newEl = event.detail.newBody.querySelector('#' + el.id);
                        if (newEl) {
                            newEl.classList.add('active');
                            newEl.style.top = el.style.top;
                            newEl.style.left = el.style.left;
                            newEl.style.width = el.style.width;
                            newEl.style.height = el.style.height;

                            var newScroll = event.detail.newBody.querySelector('#main-scroll-container');
                            if (newScroll) newScroll.style.overflow = 'hidden';
                            event.detail.newBody.style.overflow = 'hidden';
                        }
                    });
                }
            });

            document.addEventListener('turbo:render', function() {
                hideAfterPaint();
            });

            document.addEventListener('turbo:load', function() {
                var sidebar = document.querySelector('aside .overflow-y-auto');
                if (sidebar && sessionStorage.getItem('sidebarScroll')) {
                    sidebar.scrollTop = sessionStorage.getItem('sidebarScroll');
                }

                hideAfterPaint();
            });
        })();
    </script>

    @yield('style')
</head>

<body class="font-sans antialiased bg-[#F8FAFC] text-slate-900" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden relative">

        <div x-show="sidebarOpen" x-transition.opacity
            class="fixed inset-0 z-[50] bg-slate-900/50 lg:hidden backdrop-blur-sm" @click="sidebarOpen = false"></div>

        @include('layouts.sidebar')

        <div id="main-scroll-container"
            class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden bg-[#F8FAFC]">

            <header
                class="sticky top-0 z-[40] w-full px-4 md:px-8 py-4 md:py-5 bg-white/70 backdrop-blur-lg shadow-[0_2px_15px_-3px_rgba(0,0,0,0.04)] border-b border-slate-200/60 transition-all duration-300">
                <div class="flex items-center justify-between max-w-screen-2xl mx-auto gap-4">
                    <div class="flex items-center gap-4">
                        
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden p-2 text-slate-500 hover:bg-slate-100 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <h2
                            class="text-xl md:text-2xl font-bold text-slate-800 tracking-tight whitespace-nowrap overflow-hidden text-ellipsis">
                            @yield('title', 'Dashboard')
                        </h2>
                    </div>

                    <div class="flex items-center gap-4 md:gap-6">

                        {{-- Bell Notifikasi (HRD & Manager only, Supervisor tidak punya notifikasi) --}}
                        @php
                            $roleNotif = strtolower(Auth::user()->roles->first()?->nama_role ?? '');
                            $canSeeNotif = in_array($roleNotif, ['hrd', 'manager']);
                        @endphp
                        @if($canSeeNotif)
                        <div class="relative" x-data="notifDropdown()" @click.away="open = false">
                            <button @click="toggle()" class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                                <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold text-white bg-red-500 rounded-full ring-2 ring-white">0</span>
                            </button>

                            {{-- Dropdown Panel --}}
                            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                class="absolute right-0 mt-2 w-96 bg-white rounded-2xl shadow-2xl border border-slate-200/60 overflow-hidden z-50" style="display: none;">

                                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-800">Notifikasi</h3>
                                        <p class="text-xs text-slate-400 mt-0.5" x-text="unreadCount > 0 ? unreadCount + ' belum dibaca' : 'Semua sudah dibaca'"></p>
                                    </div>
                                    <button x-show="unreadCount > 0" @click="markAllRead()" class="text-xs font-semibold text-primary hover:underline">Tandai semua dibaca</button>
                                </div>

                                <div class="max-h-80 overflow-y-auto" id="notif-dropdown-list">
                                    <template x-if="items.length === 0">
                                        <div class="px-5 py-10 text-center">
                                            <svg class="w-12 h-12 mx-auto text-slate-200 mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                            <p class="text-sm text-slate-400">Belum ada notifikasi</p>
                                        </div>
                                    </template>
                                    <template x-for="item in items" :key="item.id">
                                        <button @click="readItem(item)" class="w-full text-left px-5 py-3.5 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 flex gap-3" :class="{ 'bg-blue-50/40': !item.is_read }">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-sm" :class="getIconClass(item.tipe)" x-html="getIcon(item.tipe)"></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-slate-800 truncate" x-text="item.judul"></p>
                                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-2" x-text="item.pesan"></p>
                                                <p class="text-[11px] text-slate-400 mt-1" x-text="item.waktu"></p>
                                            </div>
                                            <span x-show="!item.is_read" class="flex-shrink-0 mt-2 w-2 h-2 rounded-full bg-blue-500"></span>
                                        </button>
                                    </template>
                                </div>

                                <a href="{{ route('notifikasi.index') }}" class="block px-5 py-3 text-center text-xs font-semibold text-primary hover:bg-slate-50 border-t border-slate-100 transition-colors" data-turbo="false">
                                    Lihat Semua Notifikasi
                                </a>
                            </div>
                        </div>
                        @endif

                        <div class="text-right hidden md:block">
                            <p class="text-sm font-bold text-slate-800">{{ Auth::user()->nama_lengkap ?? 'Guest' }}</p>
                            <p class="text-xs text-slate-500 font-medium">{{ Auth::user()->email ?? 'user@example.com'
                                }}</p>
                        </div>

                        <div
                            class="h-11 w-11 rounded-full bg-gradient-to-br from-[#130F26] to-[#2B2545] text-white flex items-center justify-center font-bold text-sm shadow-lg ring-4 ring-slate-50">
                            {{ substr(Auth::user()->nama_lengkap ?? 'U', 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>

            <main id="main-content" class="relative w-full max-w-screen-2xl mx-auto p-4 md:p-8 lg:p-10 min-h-screen">

                <div id="shimmer-dashboard" class="shimmer-overlay">
                    
                    <div class="mb-8">
                        <div class="shimmer-block h-8 w-72 mb-2"></div>
                        <div class="shimmer-block h-4 w-48"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="shimmer-block h-24"></div>
                        <div class="shimmer-block h-24"></div>
                        <div class="shimmer-block h-24"></div>
                        <div class="shimmer-block h-24"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="shimmer-block h-72"></div>
                        <div class="lg:col-span-2 space-y-4">
                            <div class="shimmer-block h-48"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="shimmer-block h-40"></div>
                                <div class="shimmer-block h-40"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="shimmer-pegawai" class="shimmer-overlay">
                    <div class="shimmer-block h-8 w-40 mb-6"></div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="shimmer-block h-20"></div>
                        <div class="shimmer-block h-20"></div>
                        <div class="shimmer-block h-20"></div>
                        <div class="shimmer-block h-20"></div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-xl border border-slate-200 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="shimmer-block h-10 w-36"></div>
                            <div class="shimmer-block h-10 w-36"></div>
                            <div class="shimmer-block h-10 w-32"></div>
                            <div class="flex-1"></div>
                            <div class="shimmer-block h-10 w-52"></div>
                            <div class="shimmer-block h-10 w-28"></div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="p-4 space-y-1">
                            <div class="shimmer-block h-10 w-full mb-2"></div>
                            @for($i = 0; $i < 5; $i++)
                                <div class="flex items-center gap-4 py-3">
                                    <div class="shimmer-block rounded-full h-12 w-12 flex-shrink-0"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="shimmer-block h-4 w-40"></div>
                                        <div class="shimmer-block h-3 w-56"></div>
                                    </div>
                                    <div class="shimmer-block h-4 w-32"></div>
                                    <div class="shimmer-block h-6 w-16"></div>
                                    <div class="flex gap-2">
                                        <div class="shimmer-block h-8 w-8"></div>
                                        <div class="shimmer-block h-8 w-8"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div id="shimmer-presensi" class="shimmer-overlay">
                    
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <div class="shimmer-block h-8 w-48 mb-2"></div>
                            <div class="shimmer-block h-4 w-64"></div>
                        </div>
                        <div class="flex gap-3">
                            <div class="shimmer-block h-10 w-36"></div>
                            <div class="shimmer-block h-10 w-36"></div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="p-4 space-y-1">
                            <div class="shimmer-block h-10 w-full mb-2"></div>
                            @for($i = 0; $i < 6; $i++)
                                <div class="flex items-center gap-4 py-3">
                                    <div class="shimmer-block rounded-full h-10 w-10 flex-shrink-0"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="shimmer-block h-4 w-36"></div>
                                        <div class="shimmer-block h-3 w-48"></div>
                                    </div>
                                    <div class="shimmer-block h-4 w-24"></div>
                                    <div class="shimmer-block h-6 w-20"></div>
                                    <div class="shimmer-block h-6 w-20"></div>
                                    <div class="shimmer-block h-8 w-16"></div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div id="shimmer-calendar" class="shimmer-overlay">
                    
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <div class="shimmer-block h-8 w-48 mb-2"></div>
                            <div class="shimmer-block h-4 w-72"></div>
                        </div>
                        <div class="flex gap-3">
                            <div class="shimmer-block h-10 w-36"></div>
                            <div class="shimmer-block h-10 w-36"></div>
                            <div class="shimmer-block h-10 w-28"></div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-xl border border-slate-200 mb-6 flex gap-6">
                        <div class="shimmer-block h-4 w-20"></div>
                        <div class="flex gap-4">
                            <div class="shimmer-block h-4 w-16"></div>
                            <div class="shimmer-block h-4 w-16"></div>
                            <div class="shimmer-block h-4 w-16"></div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex gap-2">
                                <div class="shimmer-block h-9 w-9"></div>
                                <div class="shimmer-block h-9 w-9"></div>
                                <div class="shimmer-block h-9 w-20"></div>
                            </div>
                            <div class="shimmer-block h-7 w-40"></div>
                            <div class="shimmer-block h-9 w-28"></div>
                        </div>
                        <div class="grid grid-cols-7 gap-px bg-slate-200 rounded-lg overflow-hidden">
                            @for($i = 0; $i < 7; $i++)
                                <div class="shimmer-block h-8 rounded-none"></div>
                            @endfor
                            @for($i = 0; $i < 35; $i++)
                                <div class="bg-white p-2 h-24">
                                    <div class="shimmer-block h-4 w-6 mb-2"></div>
                                    @if($i % 3 == 0)
                                        <div class="shimmer-block h-4 w-full"></div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div id="shimmer-master" class="shimmer-overlay">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <div class="shimmer-block h-8 w-40 mb-2"></div>
                            <div class="shimmer-block h-4 w-64"></div>
                        </div>
                        <div class="flex gap-3">
                            <div class="shimmer-block h-10 w-48"></div>
                            <div class="shimmer-block h-10 w-28"></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-3">
                        <div class="shimmer-block h-10 w-full"></div>
                        <div class="shimmer-block h-12 w-full"></div>
                        <div class="shimmer-block h-12 w-full"></div>
                        <div class="shimmer-block h-12 w-full"></div>
                        <div class="shimmer-block h-12 w-full"></div>
                        <div class="shimmer-block h-12 w-full"></div>
                    </div>
                </div>

                <div id="shimmer-pengumuman" class="shimmer-overlay">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <div class="shimmer-block h-8 w-48 mb-2"></div>
                            <div class="shimmer-block h-4 w-60"></div>
                        </div>
                        <div class="shimmer-block h-10 w-44"></div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="p-4 space-y-1">
                            <div class="shimmer-block h-10 w-full mb-2"></div>
                            @for($i = 0; $i < 6; $i++)
                                <div class="flex flex-col py-3 border-b border-slate-100 last:border-0">
                                    <div class="shimmer-block h-5 w-1/3 mb-2"></div>
                                    <div class="shimmer-block h-4 w-2/3 mb-2"></div>
                                    <div class="flex gap-4">
                                        <div class="shimmer-block h-4 w-20"></div>
                                        <div class="shimmer-block h-4 w-24"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div id="shimmer-form" class="shimmer-overlay">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <div class="shimmer-block h-8 w-52 mb-2"></div>
                            <div class="shimmer-block h-4 w-72"></div>
                        </div>
                        <div class="shimmer-block h-10 w-24"></div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white p-6 rounded-2xl border border-slate-200">
                                <div class="shimmer-block h-6 w-40 mb-4"></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <div class="shimmer-block h-4 w-20"></div>
                                        <div class="shimmer-block h-10 w-full"></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="shimmer-block h-4 w-24"></div>
                                        <div class="shimmer-block h-10 w-full"></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="shimmer-block h-4 w-16"></div>
                                        <div class="shimmer-block h-10 w-full"></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="shimmer-block h-4 w-28"></div>
                                        <div class="shimmer-block h-10 w-full"></div>
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <div class="shimmer-block h-4 w-24"></div>
                                    <div class="shimmer-block h-20 w-full"></div>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-2xl border border-slate-200">
                                <div class="shimmer-block h-6 w-36 mb-4"></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <div class="shimmer-block h-4 w-16"></div>
                                        <div class="shimmer-block h-10 w-full"></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="shimmer-block h-4 w-20"></div>
                                        <div class="shimmer-block h-10 w-full"></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="shimmer-block h-4 w-24"></div>
                                        <div class="shimmer-block h-10 w-full"></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="shimmer-block h-4 w-28"></div>
                                        <div class="shimmer-block h-10 w-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="shimmer-block h-52"></div>
                            <div class="shimmer-block h-12 w-full"></div>
                            <div class="shimmer-block h-12 w-full"></div>
                        </div>
                    </div>
                </div>

                <x-breadcrumb />
                @yield('content')
            </main>

        </div>
    </div>

    <script src="{{ asset('js/dashboard-script.js') }}"></script>

    <div id="swal-data" data-success="{{ session('success') }}" data-error="{{ session('error') }}"
        data-errors='@json($errors->all())'>
    </div>

    <script src="{{ asset('js/notifications.js') }}" data-turbo-eval="always"></script>
    <script src="{{ asset('js/form-handler.js') }}" data-turbo-eval="always"></script>

    @yield('script')

</body>

</html>
