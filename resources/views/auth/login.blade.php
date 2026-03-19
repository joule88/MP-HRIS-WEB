<!DOCTYPE html>
<html lang="id" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Absensi Digital</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
<style>
    body { font-family: 'Inter', sans-serif; }

    /* 1. MATIKAN IKON MATA BAWAAN */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear { display: none; }
    input::-webkit-credentials-auto-fill-button { visibility: hidden; pointer-events: none; position: absolute; right: 0; }

    /* 2. CUSTOM SWEETALERT MODERN (THE MAGIC CODE) */
    
    /* Container Pop-up */
    div:where(.swal2-container) div:where(.swal2-popup) {
        border-radius: 24px !important; /* Membulatkan sudut pop-up */
        padding: 32px !important;
        border: 1px solid #f1f5f9;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    }

    /* Backdrop Blur */
    div:where(.swal2-container).swal2-backdrop-show, 
    div:where(.swal2-container).swal2-noanimation {
        background: rgba(19, 15, 38, 0.4) !important;
        backdrop-filter: blur(8px) !important; /* Efek kaca buram */
    }

    /* Judul */
    div:where(.swal2-container) h2:where(.swal2-title) {
        font-size: 1.25rem !important; /* text-xl */
        font-weight: 700 !important;
        color: #1e293b !important;
        padding: 0 !important;
        margin-bottom: 8px !important;
    }

    /* Teks Isi */
    div:where(.swal2-container) .swal2-html-container {
        font-size: 0.875rem !important; /* text-sm */
        color: #64748b !important;
        margin: 0 !important;
        line-height: 1.6 !important;
    }

    /* Tombol Confirm (Full Width & Navy) */
    div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm {
        background-color: #130F26 !important;
        color: #fff !important;
        width: 100% !important; /* Lebar Penuh */
        padding: 14px !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        margin: 24px 0 0 0 !important;
        box-shadow: 0 4px 6px -1px rgba(19, 15, 38, 0.2) !important;
        transition: all 0.2s ease !important;
    }
    div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm:hover {
        background-color: #1e1b3a !important;
        transform: translateY(-2px);
    }
    div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm:focus {
        box-shadow: 0 0 0 4px rgba(19, 15, 38, 0.1) !important;
    }

    /* Custom Icon Animation (Opsional: Mempercantik icon error bawaan) */
    .swal2-icon.swal2-error {
        border-color: #fee2e2 !important; /* Merah sangat muda */
        color: #ef4444 !important; /* Merah standard */
    }
    .swal2-icon.swal2-error [class^=swal2-x-mark-line] {
        background-color: #ef4444 !important;
    }
</style>
</head>
<body class="h-full antialiased text-slate-900">

    <div class="flex min-h-screen">
        
        <div class="hidden lg:flex lg:w-1/2 relative justify-center items-center bg-[#130F26] overflow-hidden">
            <img src="{{ asset('assets/images/auth/auth-img.png') }}" 
                 class="absolute inset-0 h-full w-full object-cover" 
                 alt="Background Office">
        </div>

        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24 bg-white">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-[#130F26] tracking-tight">Sign in</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Masuk menggunakan akun administrator Anda.
                    </p>
                </div>

                <form id="login-form" action="{{ route('login.post') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                        <div class="mt-1 relative">
                            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                                class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#130F26] focus:border-[#130F26] sm:text-sm transition-all duration-200"
                                placeholder="masukkan email anda">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#130F26] focus:border-[#130F26] sm:text-sm transition-all duration-200 pr-10"
                                placeholder="••••••••">
                            
                            <button type="button" id="toggle-password-btn" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer focus:outline-none" style="background: transparent; border: none;">
                                <svg id="eye-icon" class="h-5 w-5 text-slate-400 hover:text-[#130F26] transition-colors" style="display: block;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                
                                <svg id="eye-off-icon" class="h-5 w-5 text-slate-400 hover:text-[#130F26] transition-colors" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.058 10.058 0 01-3.7 5.59m-2.2-2.2l3.59 3.59" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-[#130F26] hover:bg-[#130F26]/90 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#130F26] transition-all duration-200 transform hover:-translate-y-0.5">
                            Sign in
                        </button>
                    </div>

                </form>

                <div class="mt-8 text-center">
                    <p class="text-xs text-slate-400">
                        &copy; {{ date('Y') }} Menjadi Pengaruh Group. All rights reserved.
                    </p>
                </div>

            </div>
        </div>
    </div>

    <div class="flash-data-error" data-message="{{ session('error') ?? $errors->first() }}"></div>
    <div class="flash-data-success" data-message="{{ session('success') }}"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    <script src="{{ asset('js/login-script.js') }}"></script>

</body>
</html>
