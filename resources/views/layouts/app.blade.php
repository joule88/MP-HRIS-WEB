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

                    <div class="flex items-center gap-6">
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
