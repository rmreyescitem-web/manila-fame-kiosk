<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Interactive FAME+ Floor Plan Kiosk</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        header, footer, .fixed-ui-lock {
            touch-action: none;
            -webkit-user-select: none;
            user-select: none;
        }
        body {
            touch-action: pan-x pan-y pinch-zoom;
        }
        .floor-grid-wrapper {
            transform-origin: top left;
            transition: transform 0.1s ease-out;
            position: relative;
        }
        .floor-grid {
            display: grid;
            grid-template-columns: repeat(40, minmax(32px, 1fr));
            grid-template-rows: repeat(72, minmax(32px, 1fr));
            gap: 2px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 1rem;
            box-shadow: inset 0 2px 6px 0 rgba(0, 0, 0, 0.05);
            position: relative;
        }
        .booth-tile {
            min-height: 32px;
            min-width: 32px;
            box-sizing: border-box;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .wall-tile {
            background-color: #0f172a;
            border: none !important;
            border-radius: 0px;
            margin: -1px;
            z-index: 10;
        }
        @keyframes dash {
            to { stroke-dashoffset: -24; }
        }
        .path-animated {
            stroke-dasharray: 8, 6;
            animation: dash 0.8s linear infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(219, 39, 119, 0.4); }
            70% { transform: scale(1); box-shadow: 0 0 0 14px rgba(219, 39, 119, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(219, 39, 119, 0); }
        }
        .booth-active-pulse {
            animation: pulse-ring 2s infinite;
        }
    </style>
</head>
<body class="bg-white font-sans antialiased h-screen flex flex-col overflow-hidden select-none text-slate-800" x-data="kioskApp()">

    <!-- Bottom Control & Search Bar -->
    <header class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 text-slate-800 px-6 py-4 flex justify-between items-center z-50 shadow-lg gap-4">
        <div class="flex items-center space-x-4 shrink-0">
            <div class="bg-slate-50 p-1.5 rounded-xl shadow-sm border border-slate-200 flex items-center justify-center">
                <img src="{{ asset('images/manila_fame_logo_black.jpg') }}" alt="Manila FAME Logo" class="h-9 w-auto object-contain pointer-events-none">
            </div>
            <div class="hidden sm:block">
                <h1 class="text-sm font-bold tracking-wider text-slate-900">EVENT FLOOR PLAN</h1>
                <p class="text-[10px] text-slate-500">Interactive Directory</p>
            </div>
        </div>

        <div class="flex items-center space-x-3 flex-1 max-w-xl justify-center relative">
            <button @click="resetToEntrance" class="flex items-center space-x-2 bg-teal-600 hover:bg-teal-500 text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-md transition-all active:scale-95 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Main Entrance</span>
            </button>

            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    @input.debounce.300ms="searchBooth"
                    placeholder="Search booth (e.g., L-19, Artisans Village, Create Lab)..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all shadow-inner"
                >
                <div x-show="searchResults.length > 0" class="absolute left-0 right-0 bottom-full mb-2 bg-white border border-slate-200 text-slate-800 rounded-xl shadow-xl max-h-72 overflow-y-auto z-50 divide-y divide-slate-100">
                    <template x-for="item in searchResults" :key="item.id">
                        <div class="p-3 hover:bg-slate-50 cursor-pointer flex justify-between items-center transition-colors">
                            <div>
                                <span class="font-bold text-sm text-pink-600 block" x-text="item.booth_code"></span>
                                <span class="text-slate-500 text-xs" x-text="item.section"></span>
                            </div>
                            <button @click="navigateToBooth(item)" class="bg-pink-600 hover:bg-pink-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow">
                                Get Directions
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3 shrink-0">
            <div class="flex items-center bg-slate-100 border border-slate-200 rounded-xl p-1 shadow-inner">
                <button @click="zoomOut" title="Zoom Out" class="p-2 hover:bg-white text-slate-700 rounded-lg transition shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </button>
                <span class="px-3 text-xs font-bold text-slate-600 min-w-[3rem] text-center" x-text="Math.round(zoomLevel * 100) + '%'"></span>
                <button @click="zoomIn" title="Zoom In" class="p-2 hover:bg-white text-slate-700 rounded-lg transition shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
                <button @click="resetZoom" title="Reset Zoom" class="px-2.5 py-1 text-xs font-semibold text-slate-500 hover:text-slate-800 transition border-l border-slate-200 ml-1">Reset</button>
            </div>

            <div class="bg-slate-50 p-1.5 rounded-xl shadow-sm border border-slate-200 hidden md:flex items-center justify-center">
                <img src="{{ asset('images/dti-citem-colored-logo.png') }}" alt="DTI-CITEM Logo" class="h-9 w-auto object-contain pointer-events-none">
            </div>
        </div>
    </header>

    <!-- Map Viewport -->
    <main id="mapContainer" class="flex-1 overflow-auto p-8 pb-28 bg-white relative flex justify-center items-start">
        <div class="floor-grid-wrapper" :style="`transform: scale(${zoomLevel});`">
            <div class="floor-grid border border-slate-200 shadow-xl relative" id="floorGridElement">
                
                <!-- SVG Wayfinding (Aisle-Only Line Navigation) -->
                <svg id="wayfindingSvg" class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: 100; overflow: visible;">
                    <path id="routePath" d="" fill="none" stroke="#db2777" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" class="path-animated" />
                </svg>

                @php
                    function excelColToInt($colStr) {$colStr = strtoupper($colStr);$len = strlen($colStr);$num = 0;
                        for ($i = 0; $i < $len; $i++) {
                            $num =$num * 26 + (ord($colStr[$i]) - ord('A') + 1);
                        }
                        return $num;
                    }

                    function getGridStyle($startCell, $endCell = null,$boothCode = '') {
                        if (empty($startCell) || !preg_match('/([A-Z]+)(\d+)/', trim($startCell),$startMatches)) {
                            return 'display: none;';
                        }

                        $startCol = excelColToInt($startMatches[1]);
                        $startRow = intval($startMatches[2]);

                        if ($endCell && preg_match('/([A-Z]+)(\d+)/', trim($endCell),$endMatches)) {
                            $endCol = excelColToInt($endMatches[1]);
                            $endRow = intval($endMatches[2]);
                            $colSpan = max(1, ($endCol - $startCol) + 1);$rowSpan = max(1, ($endRow -$startRow) + 1);

                            return "grid-column: {$startCol} / span {$colSpan}; grid-row: {$startRow} / span {$rowSpan};";
                        }

                        return "grid-column: {$startCol}; grid-row: {$startRow};";
                    }
                @endphp

                <!-- Walls -->
                @isset($walls)
                    @foreach($walls as $wall)
                        <div class="wall-tile shadow-none" style="{{ getGridStyle($wall->start_cell,$wall->end_cell) }}"></div>
                    @endforeach
                @endisset

                <!-- Booths & Facilities -->
                @foreach($booths as $booth)
                    @php
                        $boothCode = trim((string) ($booth->booth_code ?? ''));
                        $upperCode = strtoupper($boothCode);
                        $type =$booth->type ?? 'booth';
                        $isMerged = isset($booth->is_merged) &&$booth->is_merged;
                        
                        $isFacility = in_array($upperCode, ['CREATE LAB', 'LUMI CANDLES', 'STUDIO BG', 'LIKHANG FILIPINO', 'COMFORT ROOM', 'BUYERS LOUNGE', 'FAME TALKS', 'CHRISTMAS SETTING', 'FOOD KIOSK', 'CONCESSIONAIRE', 'LOADING BAY']) || $type === 'facility';
                        $isArtisansVillage = in_array($upperCode, ['ARTISANS VILLAGE 1', 'ARTISANS VILLAGE 2', 'ARTISANS VILLAGE 3', 'ARTISANS VILLAGE 4']);

                        if ($type === 'wall' || $upperCode === 'WALL') {$boothColorClass = 'bg-slate-900 border-slate-900 pointer-events-none select-none';
                        } elseif ($type === 'aisle' || $upperCode === 'AISLE') {$boothColorClass = 'bg-transparent border-transparent pointer-events-none select-none text-transparent';
                        } elseif ($type === 'entrance' || stripos($upperCode, 'MAIN ENTRANCE') !== false) {$boothColorClass = 'bg-teal-600 text-white font-bold border-teal-500 shadow-teal-500/20';
                        } elseif ($isFacility) {$boothColorClass = 'bg-orange-100 text-orange-900 border-orange-300 font-semibold';
                        } elseif ($isMerged) {
                            $boothColorClass = 'bg-indigo-50 text-indigo-900 font-bold border-indigo-200 hover:bg-indigo-100';                         } else {$boothColorClass = 'bg-white hover:bg-slate-50 text-slate-700 border-slate-300';
                        }

                        if ($isArtisansVillage) {$boothColorClass .= ' pointer-events-none select-none opacity-80';
                        }

                        $startCell =$booth->start_cell ?? '';
                        $endCell =$booth->end_cell ?? null;
                    @endphp

                    <div 
                        id="booth-{{ $booth->id }}"
                        data-code="{{ $upperCode }}"
                        data-type="{{ $type }}"
                        data-start="{{ $startCell }}"
                        style="{{ getGridStyle($startCell, $endCell,$boothCode) }}"
                        class="booth-tile border text-[9px] text-center flex items-center justify-center font-semibold rounded-md transition-all duration-300 shadow-sm leading-tight p-0.5 {{ $boothColorClass }} {{ !in_array($type, ['wall', 'aisle']) &&$upperCode !== 'WALL' && $upperCode !== 'AISLE' && !$isArtisansVillage ? 'cursor-pointer select-none' : '' }}"
                        @if(!in_array($type, ['wall', 'aisle']) &&$upperCode !== 'WALL' && $upperCode !== 'AISLE' && !$isArtisansVillage)
                            :class="{
                                'bg-pink-600 text-white border-pink-500 scale-105 shadow-lg ring-4 ring-pink-300 z-40 booth-active-pulse': activeBoothId === {{ $booth->id }}
                            }"
                            @click="selectBoothFromMap({{ $booth->id }}, '{{ addslashes($boothCode) }}', '{{ addslashes($booth->section ?? '') }}')"
                        @endif
                    >
                        <span class="truncate block w-full text-center px-0.5 overflow-hidden whitespace-nowrap">{{ $boothCode }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    <!-- Info Panel -->
    <div x-show="selectedBoothInfo" x-transition class="absolute bottom-24 left-6 bg-white/95 backdrop-blur-md border border-slate-200 text-slate-800 p-5 rounded-2xl shadow-xl z-40 max-w-sm flex items-start space-x-4 fixed-ui-lock">
        <div class="bg-slate-100 border border-slate-200 p-3 rounded-xl mt-1 text-pink-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
        </div>
        <div class="flex-1">
            <div class="flex justify-between items-start">
                <span class="text-xs text-pink-600 font-semibold uppercase tracking-wider">Wayfinding Route</span>
                <span class="text-[10px] bg-teal-100 text-teal-800 border border-teal-200 px-2 py-0.5 rounded-full font-medium">From Main Entrance</span>
            </div>
            <h4 class="text-base font-bold text-slate-900 mt-0.5" x-text="selectedBoothInfo?.code"></h4>
            <p class="text-xs text-slate-500" x-text="selectedBoothInfo?.section"></p>
            <div class="mt-3 pt-3 border-t border-slate-100 flex space-x-2">
                <button @click="$nextTick(() => drawRouteToActive())" class="flex-1 bg-pink-600 hover:bg-pink-500 text-white py-1.5 px-3 rounded-lg text-xs font-semibold shadow transition">
                    Show Path
                </button>
                <button @click="clearRoute()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 py-1.5 px-3 rounded-lg text-xs font-semibold transition border border-slate-200">
                    Clear Path
                </button>
            </div>
        </div>
        <button @click="clearRoute(); selectedBoothInfo = null; activeBoothId = null;" class="text-slate-400 hover:text-slate-600 p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- JavaScript Pathfinding & Kiosk Logic -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kioskApp', () => ({
            searchQuery: '',
            searchResults: [],
            activeBoothId: null,
            selectedBoothInfo: null,
            zoomLevel: 1,
            minZoom: 0.5,
            maxZoom: 2.0,

            init() {
                document.addEventListener('contextmenu', event => event.preventDefault());

                window.addEventListener('resize', () => {
                    this.redrawRouteIfActive();
                });

                setTimeout(() => {
                    this.resetToEntrance();
                }, 500);
            },

            zoomIn() {
                if (this.zoomLevel < this.maxZoom) {
                    this.zoomLevel = parseFloat((this.zoomLevel + 0.15).toFixed(2));
                    this.redrawRouteIfActive();
                }
            },

            zoomOut() {
                if (this.zoomLevel > this.minZoom) {
                    this.zoomLevel = parseFloat((this.zoomLevel - 0.15).toFixed(2));
                    this.redrawRouteIfActive();
                }
            },

            resetZoom() {
                this.zoomLevel = 1.0;
                this.redrawRouteIfActive();
            },

            redrawRouteIfActive() {
                if (this.activeBoothId) {
                    this.$nextTick(() => this.drawRouteToActive());
                }
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
                            this.searchResults = data.data.filter(item => {
                                const code = (item.booth_code || '').toUpperCase();
                                return !['ARTISANS VILLAGE 1', 'ARTISANS VILLAGE 2', 'ARTISANS VILLAGE 3', 'ARTISANS VILLAGE 4'].includes(code);
                            });
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
                        element.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
                    }
                    this.drawRouteToActive();
                });
            },

            selectBoothFromMap(id, code, section) {
                this.activeBoothId = id;
                this.selectedBoothInfo = { code, section };
                this.$nextTick(() => {
                    this.drawRouteToActive();
                });
            },

            resetToEntrance() {
                this.clearRoute();
                const mainEntranceEl = document.querySelector('[data-code*="MAIN ENTRANCE"]');
                if (mainEntranceEl) {
                    mainEntranceEl.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
                    mainEntranceEl.classList.add('ring-4', 'ring-teal-400', 'scale-105');
                    setTimeout(() => {
                        mainEntranceEl.classList.remove('ring-4', 'ring-teal-400', 'scale-105');
                    }, 2500);
                }
            },

            drawRouteToActive() {
                const grid = document.getElementById('floorGridElement');
                const routePath = document.getElementById('routePath');
                const targetEl = document.getElementById(`booth-${this.activeBoothId}`);
                const entranceEl = document.querySelector('[data-type="entrance"]') || document.querySelector('[data-code*="MAIN ENTRANCE"]');

                if (!targetEl || !entranceEl || !grid || !routePath) return;

                const COLS = 40;
                const ROWS = 72;

                const getGridArea = (el) => {
                    const style = window.getComputedStyle(el);
                    const colStart = parseInt(style.gridColumnStart) || 1;
                    const rowStart = parseInt(style.gridRowStart) || 1;
                    
                    let colSpan = 1;
                    let rowSpan = 1;

                    if (style.gridColumnEnd.includes('span')) {
                        colSpan = parseInt(style.gridColumnEnd.replace('span', '')) || 1;
                    } else if (parseInt(style.gridColumnEnd)) {
                        colSpan = (parseInt(style.gridColumnEnd) - colStart) || 1;
                    }

                    if (style.gridRowEnd.includes('span')) {
                        rowSpan = parseInt(style.gridRowEnd.replace('span', '')) || 1;
                    } else if (parseInt(style.gridRowEnd)) {
                        rowSpan = (parseInt(style.gridRowEnd) - rowStart) || 1;
                    }

                    return { colStart, rowStart, colSpan, rowSpan };
                };

                // Grid matrix: 1 = Non-walkable (Booths, Walls), 0 = Walkable Aisle
                const gridMatrix = Array.from({ length: ROWS + 1 }, () => Array(COLS + 1).fill(0));
                const tileElementMap = new Map();

                const allTiles = grid.querySelectorAll('.wall-tile, .booth-tile');
                allTiles.forEach(el => {
                    const area = getGridArea(el);
                    const isAisle = el.getAttribute('data-type') === 'aisle' || el.getAttribute('data-code') === 'AISLE';

                    if (!isAisle) {
                        for (let r = area.rowStart; r < area.rowStart + area.rowSpan; r++) {
                            for (let c = area.colStart; c < area.colStart + area.colSpan; c++) {
                                if (r <= ROWS && c <= COLS) {
                                    gridMatrix[r][c] = 1;
                                }
                            }
                        }
                    } else {
                        for (let r = area.rowStart; r < area.rowStart + area.rowSpan; r++) {
                            for (let c = area.colStart; c < area.colStart + area.colSpan; c++) {
                                if (r <= ROWS && c <= COLS) {
                                    tileElementMap.set(`${c},${r}`, el);
                                }
                            }
                        }
                    }
                });

                const startArea = getGridArea(entranceEl);
                const endArea = getGridArea(targetEl);

                // Find closest walkable aisle cell adjacent to booth/entrance
                const findNearestAisleCell = (area) => {
                    let closest = null;
                    let minDistance = Infinity;

                    const centerCol = area.colStart + Math.floor((area.colSpan - 1) / 2);
                    const centerRow = area.rowStart + Math.floor((area.rowSpan - 1) / 2);

                    for (let r = area.rowStart - 2; r <= area.rowStart + area.rowSpan + 1; r++) {
                        for (let c = area.colStart - 2; c <= area.colStart + area.colSpan + 1; c++) {
                            if (r >= 1 && r <= ROWS && c >= 1 && c <= COLS && gridMatrix[r][c] === 0) {
                                const dist = Math.hypot(c - centerCol, r - centerRow);
                                if (dist < minDistance) {
                                    minDistance = dist;
                                    closest = { col: c, row: r };
                                }
                            }
                        }
                    }

                    return closest || { col: centerCol, row: centerRow };
                };

                const startAisleNode = findNearestAisleCell(startArea);
                const endAisleNode = findNearestAisleCell(endArea);

                // BFS Algorithm: Guarantees shortest path across aisle network
                const queue = [startAisleNode];
                const visited = new Set([`${startAisleNode.col},${startAisleNode.row}`]);
                const cameFrom = new Map();

                let found = false;

                while (queue.length > 0) {
                    const current = queue.shift();
                    const currentKey = `${current.col},${current.row}`;

                    if (current.col === endAisleNode.col && current.row === endAisleNode.row) {
                        found = true;
                        break;
                    }

                    const directions = [
                        { col: current.col, row: current.row - 1 },
                        { col: current.col + 1, row: current.row },
                        { col: current.col, row: current.row + 1 },
                        { col: current.col - 1, row: current.row }
                    ];

                    for (const next of directions) {
                        if (
                            next.col < 1 || next.col > COLS ||
                            next.row < 1 || next.row > ROWS ||
                            gridMatrix[next.row][next.col] === 1
                        ) {
                            continue;
                        }

                        const nextKey = `${next.col},${next.row}`;
                        if (!visited.has(nextKey)) {
                            visited.add(nextKey);
                            cameFrom.set(nextKey, currentKey);
                            queue.push(next);
                        }
                    }
                }

                // Reconstruct Shortest Raw Path
                const rawPath = [];
                if (found) {
                    let currKey = `${endAisleNode.col},${endAisleNode.row}`;
                    while (currKey) {
                        const [c, r] = currKey.split(',').map(Number);
                        rawPath.unshift({ col: c, row: r });
                        currKey = cameFrom.get(currKey);
                    }
                }

                // Compress Path: Keep only corner points (direction changes) for clean, straight corridor vectors
                const simplifiedPath = [];
                if (rawPath.length > 0) {
                    simplifiedPath.push(rawPath[0]);
                    for (let i = 1; i < rawPath.length - 1; i++) {
                        const prev = rawPath[i - 1];
                        const curr = rawPath[i];
                        const next = rawPath[i + 1];

                        const dirX1 = curr.col - prev.col;
                        const dirY1 = curr.row - prev.row;
                        const dirX2 = next.col - curr.col;
                        const dirY2 = next.row - curr.row;

                        if (dirX1 !== dirX2 || dirY1 !== dirY2) {
                            simplifiedPath.push(curr);
                        }
                    }
                    simplifiedPath.push(rawPath[rawPath.length - 1]);
                }

                // Pixel coordinates calculation relative to grid element
                const gridRect = grid.getBoundingClientRect();

                const getAisleCenterPixel = (col, row) => {
                    const tileEl = tileElementMap.get(`${col},${row}`);
                    if (tileEl) {
                        const rect = tileEl.getBoundingClientRect();
                        return {
                            x: (rect.left + rect.width / 2 - gridRect.left) / this.zoomLevel,
                            y: (rect.top + rect.height / 2 - gridRect.top) / this.zoomLevel
                        };
                    }
                    const colWidth = (grid.clientWidth - 40 - (COLS - 1) * 2) / COLS;
                    const rowHeight = (grid.clientHeight - 40 - (ROWS - 1) * 2) / ROWS;
                    return {
                        x: 20 + (col - 1) * (colWidth + 2) + colWidth / 2,
                        y: 20 + (row - 1) * (rowHeight + 2) + rowHeight / 2
                    };
                };

                const getElementCenter = (el) => {
                    const rect = el.getBoundingClientRect();
                    return {
                        x: (rect.left + rect.width / 2 - gridRect.left) / this.zoomLevel,
                        y: (rect.top + rect.height / 2 - gridRect.top) / this.zoomLevel
                    };
                };

                const entranceCenter = getElementCenter(entranceEl);
                const targetCenter = getElementCenter(targetEl);

                let pathData = `M ${entranceCenter.x} ${entranceCenter.y} `;

                if (simplifiedPath.length > 0) {
                    simplifiedPath.forEach(node => {
                        const pt = getAisleCenterPixel(node.col, node.row);
                        pathData += `L ${pt.x} ${pt.y} `;
                    });
                }

                pathData += `L ${targetCenter.x} ${targetCenter.y}`;

                routePath.setAttribute('d', pathData.trim());
            },

            clearRoute() {
                const routePath = document.getElementById('routePath');
                if (routePath) routePath.setAttribute('d', '');
            }
        }));
    });
</script>
</body>
</html>