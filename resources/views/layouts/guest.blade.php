<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Control HSI - San Martín') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Encode+Sans:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-50">
    <div class="min-h-screen flex">

        <div class="hidden lg:flex lg:w-1/2 bg-white flex-col justify-between p-10 xl:p-16 relative">

            <div
                class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] opacity-40 pointer-events-none">
            </div>

            <div class="z-10">
                <x-logos.provincia-color class="h-16 xl:h-20 w-auto" />
            </div>

            <div class="flex flex-col items-center justify-center flex-grow my-8 z-10">

                <div class="mb-8">
                    <x-logos.hospital-color class="h-32 xl:h-40 w-auto drop-shadow-sm" />
                </div>

                <h1 class="text-4xl xl:text-5xl font-extrabold text-brand-cyan tracking-tight text-center mb-3">Control
                    de HSI</h1>
                <p class="font-secondary text-brand-gray-custom text-xl font-medium tracking-wide text-center">Hospital
                    Interzonal General de Agudos<br>Gral. San Martín</p>

                <div class="mt-8 flex justify-center">
                    <span
                        class="block w-80 h-1 bg-gradient-to-r from-brand-cyan via-brand-blue to-brand-pink rounded-full shadow-sm"></span>
                </div>
            </div>

            <div class="z-10"></div>
        </div>

        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12 md:p-24 bg-brand-cyan">

            <div class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-8 sm:p-10 relative overflow-hidden">

                <div class="h-1.5 w-full bg-brand-pink absolute top-0 left-0 shadow-sm"></div>

                {{ $slot }}

            </div>

            <div class="mt-8 text-sm text-brand-soft-100 font-secondary font-medium">
                &copy; {{ date('Y') }} División de Salud Digital
            </div>
        </div>

    </div>
</body>

</html>