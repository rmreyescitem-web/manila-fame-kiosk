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
        }
        .floor-grid {
            display: grid;
            grid-template-columns: repeat(39, minmax(36px, 1fr));
            grid-template-rows: repeat(49, minmax(36px, 1fr));
            gap: 3px;
            background-color: #ffffff;
            padding: 24px;
            border-radius: 1rem;
            box-shadow: inset 0 2px 6px 0 rgba(0, 0, 0, 0.05);
            position: relative;
        }
        .wall-tile {
            background-color: #000000;
            border: none !important;
            border-radius: 0px;
            margin: -1.5px;
            z-index: 10;
        }
        .aisle-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #475569;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            user-select: none;
        }
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
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(219, 39, 119, 0.4); }
            70% { transform: scale(1); box-shadow: 0 0 0 14px rgba(219, 39, 119, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(219, 39, 119, 0); }
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
                    placeholder="Search booth (e.g., L-19, Artisans Village)..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all shadow-inner"
                >
                <!-- Search results drop UP -->
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
            <!-- Header Zoom Controls -->
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
                
                <!-- SVG Wayfinding Route Overlay -->
                <svg class="absolute inset-0 w-full h-full pointer-events-none z-30" id="wayfindingSvg" style="display: none;">
                    <defs>
                        <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="1" stdDeviation="2" flood-color="#db2777" flood-opacity="0.3"/>
                        </filter>
                        <marker id="arrow" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 10 5 L 0 9 z" fill="#db2777"/>
                        </marker>
                    </defs>
                    <path id="routePath" d="" fill="none" stroke="#db2777" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" filter="url(#glow)" class="path-animated" marker-end="url(#arrow)" />
                </svg>

                @php
                    function excelColToInt($colStr) {$colStr = strtoupper($colStr);$len = strlen($colStr);$num = 0;
                        for ($i = 0; $i < $len; $i++) {
                            $num =$num * 26 + (ord($colStr[$i]) - ord('A') + 1);
                        }
                        return $num;
                    }

                    function getGridStyle($startCell,$endCell = null) {
                        if (empty($startCell) || !preg_match('/([A-Z]+)(\d+)/', trim($startCell),$startMatches)) {
                            return 'display: none;';
                        }

                        $startCol = excelColToInt($startMatches[1]);
                        $startRow = intval($startMatches[2]);

                        if ($endCell && preg_match('/([A-Z]+)(\d+)/', trim($endCell), $endMatches)) {$endCol = excelColToInt($endMatches[1]);$endRow = intval($endMatches[2]);$colSpan = ($endCol -$startCol) + 1;
                            $rowSpan = ($endRow -$startRow) + 1;

                            return "grid-column: {$startCol} / span {$colSpan}; grid-row: {$startRow} / span {$rowSpan};";
                        }

                        return "grid-column: {$startCol}; grid-row: {$startRow};";
                    }
                @endphp

                <!-- Structural Walls / Perimeter Blocks -->
                @isset($walls)
                    @foreach($walls as $wall)
                        <div class="wall-tile shadow-none" style="{{ getGridStyle($wall->start_cell,$wall->end_cell) }}"></div>
                    @endforeach
                @endisset

                <!-- Aisles & Walkway Labels -->
                <div class="aisle-label" style="grid-column: 10 / 14; grid-row: 5 / 6;"></div>
                <div class="aisle-label" style="grid-column: 20 / 25; grid-row: 25 / 26;"></div>

                <!-- Exhibition Booths, Walls, Facilities & Aisles Grid -->
                @foreach($booths as $booth)
                    @php
                        $boothCode = trim((string) ($booth->booth_code ?? ''));
                        $type = $booth->type ?? 'booth';
                        $isMerged = isset($booth->is_merged) && $booth->is_merged;
                        
                        if ($type === 'wall') {
                            $boothColorClass = 'bg-slate-900 border-slate-900 pointer-events-none select-none';
                        } elseif ($type === 'aisle') {
                            $boothColorClass = 'bg-transparent border-transparent pointer-events-none select-none text-transparent';
                        } elseif ($type === 'entrance' || stripos($boothCode, 'MAIN ENTRANCE') !== false) {
                            $boothColorClass = 'bg-teal-600 text-white font-bold border-teal-500 shadow-teal-500/20';
                        } elseif ($type === 'facility') {
                            $boothColorClass = 'bg-amber-100 text-amber-900 border-amber-300';
                        } elseif ($isMerged) {
                            $boothColorClass = 'bg-indigo-50 text-indigo-900 font-bold border-indigo-200 hover:bg-indigo-100';
                        } else {
                            $boothColorClass = 'bg-white hover:bg-slate-50 text-slate-700 border-slate-300';
                        }

                        $startCell = $booth->start_cell ?? '';
                        $endCell = $booth->end_cell ?? null;
                    @endphp

                    <div 
                        id="booth-{{ $booth->id }}"
                        data-code="{{ strtoupper($boothCode) }}"
                        data-type="{{ $type }}"
                        data-start="{{ $startCell }}"
                        style="{{ getGridStyle($startCell,$endCell) }}"
                        class="booth-tile border text-[10px] text-center flex items-center justify-center font-semibold rounded-lg transition-all duration-300 shadow-sm {{ $boothColorClass }} {{ !in_array($type, ['wall', 'aisle']) ? 'cursor-pointer select-none' : '' }}"
                        @if(!in_array($type, ['wall', 'aisle']))
                            :class="{
                                'bg-pink-600 text-white border-pink-500 scale-105 shadow-lg ring-4 ring-pink-300 z-40 booth-active-pulse': activeBoothId === {{ $booth->id }}
                            }"
                            @click="selectBoothFromMap({{ $booth->id }}, '{{ addslashes($boothCode) }}', '{{ addslashes($booth->section ?? '') }}')"
                        @endif
                    >
                        <span class="truncate px-1">{{ $boothCode }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    <!-- Floating Wayfinding Panel -->
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
                <button @click="drawRouteToActive()" class="flex-1 bg-pink-600 hover:bg-pink-500 text-white py-1.5 px-3 rounded-lg text-xs font-semibold shadow transition">
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

    <!-- AlpineJS Logic -->
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

                    history.pushState(null, null, location.href);
                    window.addEventListener('popstate', () => {
                        history.pushState(null, null, location.href);
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
                    const mainEntranceEl = document.querySelector('[data-code*="MAIN ENTRANCE"]');
                    if (mainEntranceEl) {
                        mainEntranceEl.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'center'
                        });
                        mainEntranceEl.classList.add('ring-4', 'ring-teal-400', 'scale-105');
                        setTimeout(() => {
                            mainEntranceEl.classList.remove('ring-4', 'ring-teal-400', 'scale-105');
                        }, 2500);
                    }
                },

                drawRouteToActive() {
    const svg = document.getElementById('wayfindingSvg');
    const grid = document.getElementById('floorGridElement');
    const targetEl = document.getElementById(`booth-${this.activeBoothId}`);
    
    const entranceEl = document.querySelector('[data-type="entrance"]') || 
                       document.querySelector('[data-code*="MAIN ENTRANCE"]');

    if (!targetEl || !entranceEl || !svg || !grid) return;

    svg.style.display = 'block';
    svg.setAttribute('width', grid.offsetWidth);
    svg.setAttribute('height', grid.offsetHeight);

    const COLS = 39;
    const ROWS = 49;

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

    const gridMatrix = Array.from({ length: ROWS + 1 }, () => Array(COLS + 1).fill(0));
    const tileElementMap = Array.from({ length: ROWS + 1 }, () => Array(COLS + 1).fill(null));

    const startArea = getGridArea(entranceEl);
    const endArea = getGridArea(targetEl);

    const startNode = {
        col: Math.floor(startArea.colStart + (startArea.colSpan - 1) / 2),
        row: Math.floor(startArea.rowStart + (startArea.rowSpan - 1) / 2)
    };
    const endNode = {
        col: Math.floor(endArea.colStart + (endArea.colSpan - 1) / 2),
        row: Math.floor(endArea.rowStart + (endArea.rowSpan - 1) / 2)
    };

    const allTiles = grid.querySelectorAll('.wall-tile, .booth-tile');
    allTiles.forEach(el => {
        const area = getGridArea(el);
        const isTargetElement = (el === targetEl || el === entranceEl);
        
        // Block structural walls, non-aisle booths, and facilities EXCEPT when it is the selected destination
        const isObstacle = !isTargetElement && (
            el.classList.contains('wall-tile') || 
            el.getAttribute('data-type') === 'wall' || 
            el.getAttribute('data-type') === 'facility' || 
            (el.classList.contains('booth-tile') && 
             el.getAttribute('data-type') !== 'aisle' && 
             el.getAttribute('data-type') !== 'entrance')
        );

        for (let r = area.rowStart; r < area.rowStart + area.rowSpan; r++) {
            for (let c = area.colStart; c < area.colStart + area.colSpan; c++) {
                if (r <= ROWS && c <= COLS) {
                    gridMatrix[r][c] = isObstacle ? 1 : 0;
                    tileElementMap[r][c] = el;
                }
            }
        }
    });

    // Explicitly unblock the start and end grid cells
    for (let r = startArea.rowStart; r < startArea.rowStart + startArea.colSpan; r++) {
        for (let c = startArea.colStart; c < startArea.colStart + startArea.colSpan; c++) {
            if (r <= ROWS && c <= COLS) gridMatrix[r][c] = 0;
        }
    }
    for (let r = endArea.rowStart; r < endArea.rowStart + endArea.rowSpan; r++) {
        for (let c = endArea.colStart; c < endArea.colStart + endArea.colSpan; c++) {
            if (r <= ROWS && c <= COLS) gridMatrix[r][c] = 0;
        }
    }

    const openSet = [];
    const closedSet = new Set();
    const cameFrom = new Map();

    const gScore = {};
    const fScore = {};

    const nodeKey = (n) => `${n.col},${n.row}`;
    const heuristic = (a, b) => Math.abs(a.col - b.col) + Math.abs(a.row - b.row);

    const startKey = nodeKey(startNode);
    gScore[startKey] = 0;
    fScore[startKey] = heuristic(startNode, endNode);
    openSet.push({ ...startNode, f: fScore[startKey] });

    let finalPath = [];

    while (openSet.length > 0) {
        openSet.sort((a, b) => a.f - b.f);
        const current = openSet.shift();
        const currentKey = nodeKey(current);

        if (current.col === endNode.col && current.row === endNode.row) {
            let tempKey = currentKey;
            while (cameFrom.has(tempKey)) {
                const [c, r] = tempKey.split(',').map(Number);
                finalPath.unshift({ col: c, row: r });
                tempKey = cameFrom.get(tempKey);
            }
            finalPath.unshift(startNode);
            break;
        }

        closedSet.add(currentKey);

        const neighbors = [
            { col: current.col, row: current.row - 1 }, // Up
            { col: current.col + 1, row: current.row }, // Right
            { col: current.col, row: current.row + 1 }, // Down
            { col: current.col - 1, row: current.row }  // Left
        ];

        for (const neighbor of neighbors) {
            if (
                neighbor.col < 1 || neighbor.col > COLS ||
                neighbor.row < 1 || neighbor.row > ROWS ||
                gridMatrix[neighbor.row][neighbor.col] === 1
            ) {
                continue;
            }

            const neighborKey = nodeKey(neighbor);
            if (closedSet.has(neighborKey)) continue;

            let turnPenalty = 0;
            if (cameFrom.has(currentKey)) {
                const prevKey = cameFrom.get(currentKey);
                const [pc, pr] = prevKey.split(',').map(Number);
                const prevDirX = current.col - pc;
                const prevDirY = current.row - pr;
                const newDirX = neighbor.col - current.col;
                const newDirY = neighbor.row - current.row;

                if (prevDirX !== newDirX || prevDirY !== newDirY) {
                    turnPenalty = 2;
                }
            } else {
                const isVerticalEntrance = startArea.rowSpan > startArea.colSpan;
                const isVerticalStep = neighbor.col === current.col;
                if ((isVerticalEntrance && !isVerticalStep) || (!isVerticalEntrance && isVerticalStep)) {
                    turnPenalty = 5;
                }
            }

            const tentativeG = (gScore[currentKey] ?? Infinity) + 1 + turnPenalty;

            if (tentativeG < (gScore[neighborKey] ?? Infinity)) {
                cameFrom.set(neighborKey, currentKey);
                gScore[neighborKey] = tentativeG;
                fScore[neighborKey] = tentativeG + heuristic(neighbor, endNode);

                if (!openSet.some(n => n.col === neighbor.col && n.row === neighbor.row)) {
                    openSet.push({ ...neighbor, f: fScore[neighborKey] });
                }
            }
        }
    }

    const getExactTileCenter = (col, row, defaultEl) => {
        const tileEl = tileElementMap[row][col] || defaultEl;
        if (tileEl) {
            const area = getGridArea(tileEl);
            const tileWidth = tileEl.offsetWidth / area.colSpan;
            const tileHeight = tileEl.offsetHeight / area.rowSpan;
            
            const colOffset = (col - area.colStart) + 0.5;
            const rowOffset = (row - area.rowStart) + 0.5;

            return {
                x: tileEl.offsetLeft + (colOffset * tileWidth),
                y: tileEl.offsetTop + (rowOffset * tileHeight)
            };
        }

        const fallbackCellWidth = grid.offsetWidth / COLS;
        const fallbackCellHeight = grid.offsetHeight / ROWS;
        return {
            x: (col - 0.5) * fallbackCellWidth,
            y: (row - 0.5) * fallbackCellHeight
        };
    };

    const simplifyPath = (nodes) => {
        if (nodes.length <= 2) return nodes;
        const simplified = [nodes[0]];

        for (let i = 1; i < nodes.length - 1; i++) {
            const prev = nodes[i - 1];
            const curr = nodes[i];
            const next = nodes[i + 1];

            const dx1 = curr.col - prev.col;
            const dy1 = curr.row - prev.row;
            const dx2 = next.col - curr.col;
            const dy2 = next.row - curr.row;

            if (dx1 !== dx2 || dy1 !== dy2) {
                simplified.push(curr);
            }
        }
        simplified.push(nodes[nodes.length - 1]);
        return simplified;
    };

    if (finalPath.length > 0) {
        const smoothedPath = simplifyPath(finalPath);
        const startPos = getExactTileCenter(smoothedPath[0].col, smoothedPath[0].row, entranceEl);
        
        let pathData = `M ${startPos.x} ${startPos.y} `;

        for (let i = 1; i < smoothedPath.length; i++) {
            const pos = getExactTileCenter(smoothedPath[i].col, smoothedPath[i].row, i === smoothedPath.length - 1 ? targetEl : null);
            
            const prevPos = getExactTileCenter(smoothedPath[i - 1].col, smoothedPath[i - 1].row, null);
            if (smoothedPath[i].col === smoothedPath[i - 1].col) {
                pos.x = prevPos.x;
            }
            if (smoothedPath[i].row === smoothedPath[i - 1].row) {
                pos.y = prevPos.y;
            }

            pathData += `L ${pos.x} ${pos.y} `;
        }
        
        const routePath = document.getElementById('routePath');
        if (routePath) {
            routePath.setAttribute('d', pathData.trim());
        }
    }
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