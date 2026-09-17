<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFIO Kiosk - Event Floor Plan</title>
    <!-- Tailwind CSS CDN for rapid styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js for lightweight reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">
    <header class="bg-slate-800 border-b border-slate-700 p-4 shadow-md flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded uppercase tracking-wider">Kiosk Mode</span>
            <h1 class="text-xl font-bold tracking-wide">MFIO Event Floor Plan & Directory</h1>
        </div>
        <div class="text-sm text-slate-400">
            Touch a booth or search below for details
        </div>
    </header>

    <main class="flex-1 p-6 max-w-7xl mx-auto w-full">
        @yield('content')
    </main>

    <footer class="bg-slate-800 border-t border-slate-700 py-3 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} MFIO Event Management System. All Rights Reserved.
    </footer>
</body>
</html>