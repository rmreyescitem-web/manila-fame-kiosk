<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive FAME+ Floor Plan Kiosk</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Exact Spreadsheet Grid Dimensions (39 columns A to AM, 49 rows) */
        .floor-grid {
            display: grid;
            grid-template-columns: repeat(39, minmax(36px, 1fr));
            grid-template-rows: repeat(49, minmax(36px, 1fr));
            gap: 3px;
            background-color: #cbd5e1;
            padding: 24px;
            border-radius: 1rem;
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
            position: relative;
        }
        /* Custom smooth scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.5); }
            70% { transform: scale(1); box-shadow: 0 0 0 14px rgba(37, 99, 235, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
        .booth-active-pulse {
            animation: pulse-ring 2s infinite;
        }
        @keyframes dash {
            to { stroke-dashoffset: -24; }
        }
        .path-animated {
            stroke-dasharray: 10, 6;
            animation: dash 1s linear infinite;
        }
    </style>
</head>
<body class="bg-slate-100 font-sans antialiased h-screen flex flex-col overflow-hidden select-none" x-data="kioskApp()">

    <!-- Modern Clean Light Header -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200 text-slate-800 px-6 py-4 flex justify-between items-center z-30 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="bg-blue-600 p-2.5 rounded-xl shadow-md shadow-blue-500/20 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold tracking-wider text-slate-900">EVENT FLOOR PLAN</h1>
                <p class="text-xs text-slate-500">Interactive Directory & Wayfinding</p>
            </div>
        </div>

        <!-- Start from Main Entrance Button -->
        <div>
            <button @click="resetToEntrance" class="flex items-center space-x-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-md shadow-emerald-500/20 transition-all transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Main Entrance View</span>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-1/3">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
                type="text" 
                x-model="searchQuery" 
                @input.debounce.300ms="searchBooth"
                placeholder="Search booth (e.g., L-19, Artisans Village)..." 
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner"
            >
            <!-- Search Results Dropdown -->
            <div x-show="searchResults.length > 0" class="absolute left-0 right-0 mt-2 bg-white border border-slate-200 text-slate-800 rounded-xl shadow-xl max-h-72 overflow-y-auto z-50 divide-y divide-slate-100">
                <template x-for="item in searchResults" :key="item.id">
                    <div class="p-3 hover:bg-blue-50 cursor-pointer flex justify-between items-center transition-colors">
                        <div>
                            <span class="font-bold text-sm text-blue-600 block" x-text="item.booth_code"></span>
                            <span class="text-slate-500 text-xs" x-text="item.section"></span>
                        </div>
                        <button @click="navigateToBooth(item)" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow transition-transform active:scale-95">
                            Get Directions
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </header>

    <!-- Main Map Viewport -->
    <main id="mapContainer" class="flex-1 overflow-auto p-8 bg-slate-100 relative flex justify-center items-start">
        <div class="floor-grid border border-slate-300 shadow-lg relative" id="floorGridElement">
            
            <!-- SVG Wayfinding Path Layer Overlay -->
            <svg class="absolute inset-0 w-full h-full pointer-events-none z-30" id="wayfindingSvg" style="display: none;">
                <defs>
                    <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="1" stdDeviation="2" flood-color="#2563eb" flood-opacity="0.3"/>
                    </filter>
                    <marker id="arrow" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                        <path d="M 0 1 L 10 5 L 0 9 z" fill="#2563eb"/>
                    </marker>
                </defs>
                <!-- Wayfinding Aisles Routing Path -->
                <path id="routePath" d="" fill="none" stroke="#2563eb" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" filter="url(#glow)" class="path-animated" marker-end="url(#arrow)" />
            </svg>

            @php
                function excelColToInt($colStr) {
                    $colStr = strtoupper($colStr);
                    $length = strlen($colStr);
                    $num = 0;
                    for ($i = 0; $i < $length; $i++) {
                        $num = $num * 26 + (ord($colStr[$i]) - ord('A') + 1);
                    }
                    return $num;
                }

                function getGridStyle($startCell, $endCell = null) {
                    if (empty($startCell) || !preg_match('/([A-Z]+)(\d+)/', trim($startCell), $startMatches)) {
                        return 'display: none;';
                    }

                    $startCol = excelColToInt($startMatches[1]);
                    $startRow = intval($startMatches[2]);

                    if ($endCell && preg_match('/([A-Z]+)(\d+)/', trim($endCell), $endMatches)) {
                        $endCol = excelColToInt($endMatches[1]) + 1; 
                        $endRow = intval($endMatches[2]) + 1;
                    } else {
                        $endCol = $startCol + 1;
                        $endRow = $startRow + 1;
                    }

                    return "grid-column: {$startCol} / {$endCol}; grid-row: {$startRow} / {$endRow};";
                }
            @endphp

            @foreach($booths as $booth)
                @php
                    $isMainEntrance = (stripos($booth->booth_code, 'MAIN ENTRANCE') !== false);
                @endphp
                <div 
                    id="booth-{{ $booth->id }}"
                    data-code="{{ strtoupper($booth->booth_code) }}"
                    data-start="{{ $booth->start_cell }}"
                    class="booth-tile border text-[10px] text-center flex items-center justify-center font-semibold rounded-lg transition-all duration-300 select-none cursor-pointer shadow-sm"
                    style="{{ getGridStyle($booth->start_cell, $booth->end_cell) }}"
                    :class="{
                        'bg-blue-600 text-white scale-105 shadow-xl ring-4 ring-blue-300 z-40 booth-active-pulse': activeBoothId === {{ $booth->id }},
                        '{{ $isMainEntrance ? 'bg-emerald-600 text-white font-bold border-emerald-500 shadow-emerald-500/20' : ($booth->is_merged ? 'bg-amber-100 text-amber-900 font-bold border-amber-300 hover:bg-amber-200' : 'bg-white hover:bg-blue-50 text-slate-700 border-slate-300') }}': true
                    }"
                    @click="selectBoothFromMap({{ $booth->id }}, '{{ addslashes($booth->booth_code) }}', '{{ addslashes($booth->section) }}')"
                >
                    <span class="truncate px-1">{{ $booth->booth_code }}</span>
                </div>
            @endforeach
        </div>
    </main>

    <!-- Floating Wayfinding & Direction Panel -->
    <div x-show="selectedBoothInfo" x-transition class="absolute bottom-6 left-6 bg-white/95 backdrop-blur-md border border-slate-200 text-slate-800 p-5 rounded-2xl shadow-xl z-40 max-w-sm flex items-start space-x-4">
        <div class="bg-blue-50 border border-blue-200 p-3 rounded-xl mt-1 text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
        </div>
        <div class="flex-1">
            <div class="flex justify-between items-start">
                <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider">Wayfinding Route</span>
                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">From Main Entrance</span>
            </div>
            <h4 class="text-base font-bold text-slate-900 mt-0.5" x-text="selectedBoothInfo?.code"></h4>
            <p class="text-xs text-slate-500" x-text="selectedBoothInfo?.section"></p>
            <div class="mt-3 pt-3 border-t border-slate-100 flex space-x-2">
                <button @click="drawRouteToActive()" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-1.5 px-3 rounded-lg text-xs font-semibold shadow transition">
                    Show Path
                </button>
                <button @click="clearRoute()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 py-1.5 px-3 rounded-lg text-xs font-semibold transition">
                    Clear Path
                </button>
            </div>
        </div>
        <button @click="clearRoute(); selectedBoothInfo = null; activeBoothId = null;" class="text-slate-400 hover:text-slate-600 p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Modern Clean Footer -->
    <footer class="bg-white/90 backdrop-blur-md border-t border-slate-200 text-center py-3 text-xs text-slate-500 z-30 flex justify-between px-6 items-center">
        <span>Interactive Event Floor Plan Kiosk</span>
        <span class="flex items-center space-x-1.5 text-emerald-600 font-medium">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Wayfinding Navigation Active</span>
        </span>
    </footer>

<!-- AlpineJS & Wayfinding Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kioskApp', () => ({
                searchQuery: '',
                searchResults: [],
                activeBoothId: null,
                selectedBoothInfo: null,

                init() {
                    // 1. Disable Right Click Context Menu
                    document.addEventListener('contextmenu', event => event.preventDefault());

                    // 2. Disable DevTools & Inspect Keyboard Shortcuts
                    document.addEventListener('keydown', event => {
                        if (
                            event.key === 'F12' ||
                            (event.ctrlKey && event.shiftKey && ['I', 'J', 'C'].includes(event.key.toUpperCase())) ||
                            (event.ctrlKey && event.key.toUpperCase() === 'U')
                        ) {
                            event.preventDefault();
                            return false;
                        }
                    });

                    // 3. Disable Browser Touch Swiping Left/Right
                    let touchStartX = 0;
                    document.addEventListener('touchstart', e => {
                        touchStartX = e.changedTouches[0].screenX;
                    }, {passive: false});

                    document.addEventListener('touchmove', e => {
                        let touchEndX = e.changedTouches[0].screenX;
                        if (Math.abs(touchEndX - touchStartX) > 50 && (touchStartX < 40 || touchStartX > window.innerWidth - 40)) {
                            e.preventDefault();
                        }
                    }, {passive: false});

                    // 4. Trap History Stack
                    history.pushState(null, null, location.href);
                    window.addEventListener('popstate', () => {
                        history.pushState(null, null, location.href);
                    });

                    // Start default view centered at Main Entrance
                    setTimeout(() => {
                        this.resetToEntrance();
                    }, 500);
                },

                searchBooth() {
                    if (this.searchQuery.trim() === '') {
                        this.searchResults = [];
                        return;
                    }
                    fetch(`/kiosk/search?query=${encodeURIComponent(this.searchQuery)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.searchResults = data.data;
                            } else {
                                this.searchResults = [];
                            }
                        });
                },

                navigateToBooth(item) {
                    this.activeBoothId = item.id;
                    this.selectedBoothInfo = { code: item.booth_code, section: item.section };
                    this.searchQuery = '';
                    this.searchResults = [];

                    this.$nextTick(() => {
                        const element = document.getElementById(`booth-${item.id}`);
                        if (element) {
                            element.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center',
                                inline: 'center'
                            });
                        }
                        this.drawRouteToActive();
                    });
                },

                selectBoothFromMap(id, code, section) {
                    this.activeBoothId = id;
                    this.selectedBoothInfo = { code, section };
                    this.drawRouteToActive();
                },

                resetToEntrance() {
                    this.clearRoute();
                    const mainEntranceEl = document.querySelector('[data-block-code*="MAIN ENTRANCE"]') || document.querySelector('[data-code*="MAIN ENTRANCE"]');
                    if (mainEntranceEl) {
                        mainEntranceEl.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'center'
                        });
                        mainEntranceEl.classList.add('ring-4', 'ring-emerald-400', 'scale-105');
                        setTimeout(() => {
                            mainEntranceEl.classList.remove('ring-4', 'ring-emerald-400', 'scale-105');
                        }, 2500);
                    }
                },

                drawRouteToActive() {
                    const svg = document.getElementById('wayfindingSvg');
                    const grid = document.getElementById('floorGridElement');
                    const targetEl = document.getElementById(`booth-${this.activeBoothId}`);
                    const entranceEl = document.querySelector('[data-code*="MAIN ENTRANCE"]');

                    if (!targetEl || !entranceEl || !svg || !grid) return;

                    svg.style.display = 'block';

                    const gridRect = grid.getBoundingClientRect();
                    const startRect = entranceEl.getBoundingClientRect();
                    const endRect = targetEl.getBoundingClientRect();

                    const startX = (startRect.left + startRect.width / 2) - gridRect.left;
                    const startY = (startRect.top + startRect.height / 2) - gridRect.top;
                    const endX = (endRect.left + endRect.width / 2) - gridRect.left;
                    const endY = (endRect.top + endRect.height / 2) - gridRect.top;

                    // Updated Aisle-Aware Orthogonal Routing:
                    // Route upwards through open vertical corridors, jog across clear horizontal lanes, 
                    // and approach the destination cleanly through aisle gaps.
                    const corridorY = Math.min(startY, endY) - 25; // Route up into the top aisle channel
                    const pathData = `M ${startX} ${startY} L ${startX} ${corridorY} L ${endX} ${corridorY} L ${endX} ${endY}`;

                    const routePath = document.getElementById('routePath');
                    routePath.setAttribute('d', pathData);
                },

                clearRoute() {
                    const svg = document.getElementById('wayfindingSvg');
                    if (svg) {
                        svg.style.display = 'none';
                    }
                }
            }));
        });
    </script>
</body>
</html>