<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - HSI Control</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-secondary text-gray-800 antialiased min-h-screen flex items-center justify-center relative overflow-hidden">

    <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-brand-cyan opacity-20 blur-[100px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-blue-400 opacity-10 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMCwwLDAsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)]"></div>
    </div>

    <div class="relative z-10 w-full max-w-3xl px-6">
        <div class="bg-white/70 backdrop-blur-2xl rounded-[2rem] shadow-2xl border border-white/60 p-10 md:p-16 text-center relative overflow-hidden">
            
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[18rem] md:text-[24rem] font-black text-gray-900 opacity-[0.03] select-none -z-10 tracking-tighter leading-none">
                @yield('code')
            </div>

            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-cyan-50 to-white border border-cyan-100 text-brand-cyan mb-8 shadow-sm">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight mb-4 uppercase">
                @yield('title')
            </h1>
            
            <p class="text-base md:text-lg text-gray-500 mb-10 max-w-xl mx-auto leading-relaxed">
                @yield('message')
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border border-transparent text-sm font-bold rounded-xl text-white bg-brand-cyan hover:bg-cyan-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-cyan transition-all duration-200 shadow-lg shadow-cyan-500/30 uppercase tracking-wider">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Ir al Dashboard
                </a>
                
                <button onclick="window.history.back()" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border-2 border-gray-200 text-sm font-bold rounded-xl text-gray-600 bg-white/50 hover:bg-white hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-all duration-200 uppercase tracking-wider backdrop-blur-sm">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Regresar
                </button>
            </div>
        </div>
        
        <div class="text-center mt-8 text-[10px] sm:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">
            División de Salud Digital &bull; HIGA Gral. San Martín
        </div>
    </div>

</body>
</html>