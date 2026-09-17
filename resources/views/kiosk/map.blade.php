<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manila FAME Floor Plan Kiosk - August 27, 2026 Version</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html, body {
            margin: 0; padding: 0; width: 100vw; height: 100vh; overflow: hidden;
            background-color: #f8fafc; overscroll-behavior-y: none; touch-action: none;
            user-select: none; -webkit-user-select: none; font-family: system-ui, -apple-system, sans-serif;
        }
        .booth-rect { transition: all 0.2s ease; cursor: pointer; }
        .booth-rect:hover { filter: brightness(0.9); stroke: #334155; stroke-width: 2.5px; }
        .booth-rect.active-booth { stroke: #0f172a; stroke-width: 3.5px; filter: drop-shadow(0 0 6px rgba(15, 23, 42, 0.3)); }
        #pan-zoom-layer { transform-origin: 0 0; will-change: transform; }
        @keyframes dash { to { stroke-dashoffset: -30; } }
        .animate-path { animation: dash 1.5s linear infinite; }
    </style>
</head>
<body class="flex flex-col h-screen w-screen overflow-hidden bg-slate-50 text-slate-700">

    <!-- Header & Search Bar -->
    <header class="bg-white border-b border-slate-200 px-6 py-2.5 flex items-center justify-between shadow-xs z-30 shrink-0 h-14">
        <div class="flex items-center space-x-3">
            <span class="bg-slate-800 text-white font-semibold px-2.5 py-1 rounded-md text-xs tracking-wide">MF 2026</span>
            <h1 class="text-sm font-semibold text-slate-800 tracking-tight">Manila FAME Exhibition Floor Plan (As of August 27, 2026)</h1>
        </div>
        <div class="flex items-center space-x-4">
            <input type="text" id="search-input" placeholder="Search booth number (e.g., A-01, H-25), exhibitor..." 
                   class="px-3.5 py-1.5 border border-slate-300 rounded-md text-xs w-72 focus:outline-none focus:ring-1 focus:ring-slate-500 bg-slate-50 text-slate-800 placeholder-slate-400">
            <div class="text-xs font-medium text-slate-600 bg-white px-3 py-1.5 rounded-md border border-slate-200 flex items-center gap-1.5">
                <span>📍</span> <span class="opacity-80">Main Entrance: Upper/Central Portion</span>
            </div>
        </div>
    </header>

    <!-- Filter Categories Bar -->
    <div class="bg-white border-b border-slate-200 px-6 py-2 flex items-center space-x-2 overflow-x-auto shadow-xs z-20 shrink-0">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider mr-2">Zones & Areas:</span>
        <button onclick="filterCategory('all')" class="filter-btn px-3 py-1 rounded-md text-xs font-medium bg-slate-800 text-white transition">All Zones</button>
        <button onclick="filterCategory('home')" class="filter-btn px-3 py-1 rounded-md text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Home at FAME</button>
        <button onclick="filterCategory('artisans')" class="filter-btn px-3 py-1 rounded-md text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Artisans Villages</button>
        <button onclick="filterCategory('design')" class="filter-btn px-3 py-1 rounded-md text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Design Commune</button>
        <button onclick="filterCategory('fashion')" class="filter-btn px-3 py-1 rounded-md text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Fashion</button>
        <button onclick="filterCategory('christmas')" class="filter-btn px-3 py-1 rounded-md text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Christmas Setting</button>
        <button onclick="filterCategory('buyers')" class="filter-btn px-3 py-1 rounded-md text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Buyers Lounge</button>
    </div>

    <!-- Interactive Map Viewport Container -->
    <main id="map-viewport" class="relative flex-1 w-full h-full overflow-hidden bg-slate-100 cursor-grab active:cursor-grabbing">
        <!-- SVG Canvas with exact scale aspect ratio -->
        <svg id="floor-plan-svg" viewBox="0 0 1800 2600" preserveAspectRatio="xMidYMid meet" class="w-full h-full block">
            <defs>
                <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="2" stdDeviation="4" flood-opacity="0.04"/>
                </filter>
            </defs>
            <g id="pan-zoom-layer">
                <!-- Main Hall Boundary -->
                <rect x="80" y="40" width="1640" height="2480" fill="#ffffff" stroke="#cbd5e1" stroke-width="4" rx="8" filter="url(#shadow)"/>

                <!-- North / Upper Service Area & Roll-Up Doors -->
                <rect x="110" y="60" width="1580" height="65" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="1.5" rx="4"/>
                <text x="900" y="98" font-size="11" font-weight="600" fill="#64748b" text-anchor="middle" letter-spacing="1">LOADING BAY / ROLL-UP DOORS / RAMPS</text>

                <!-- Top Special Facilities & Lounges -->
                <g id="top-infrastructure">
                    <!-- Buyers Lounge (20.0m x 12.0m - 240 sq.m.) -->
                    <rect x="110" y="140" width="220" height="90" fill="#f8fafc" stroke="#cbd5e1" stroke-width="1.5" rx="4"/>
                    <text x="220" y="180" font-size="10.5" font-weight="600" fill="#334155" text-anchor="middle">BUYERS LOUNGE</text>
                    <text x="220" y="198" font-size="8" font-weight="400" fill="#64748b" text-anchor="middle">20.0m x 12.0m (240 sq.m.)</text>

                    <!-- Home at FAME (18.0m x 12.0m - 216 sq.m.) -->
                    <rect x="620" y="140" width="260" height="90" fill="#f8fafc" stroke="#cbd5e1" stroke-width="1.5" rx="4"/>
                    <text x="750" y="180" font-size="10.5" font-weight="600" fill="#334155" text-anchor="middle">HOME AT FAME</text>
                    <text x="750" y="198" font-size="8" font-weight="400" fill="#64748b" text-anchor="middle">18.0m x 12.0m (216 sq.m.)</text>

                    <!-- Main Entrance & Baggage Counter (Upper/Central portion) -->
                    <rect x="850" y="2350" width="100" height="40" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="900" y="2375" font-size="9.5" font-weight="700" fill="#334155" text-anchor="middle">MAIN ENTRANCE</text>
                </g>

                <!-- Left Wing Special Areas (Artisans Villages 1-4, Create Lab, Likhang Filipino) -->
                <g id="left-wing-sections">
                    <rect x="110" y="250" width="280" height="2080" fill="#fafafa" stroke="#cbd5e1" stroke-width="1.5" rx="6"/>

                    <!-- Artisans Village 1 (12.0m x 4.5m - 54 sq.m.) -->
                    <rect x="130" y="380" width="240" height="55" fill="#f1f5f9" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="250" y="404" font-size="9.5" font-weight="600" fill="#334155" text-anchor="middle">ARTISANS VILLAGE 1</text>
                    <text x="250" y="420" font-size="7.5" font-weight="400" fill="#64748b" text-anchor="middle">12.0m x 4.5m (54 sq.m.)</text>

                    <!-- Artisans Village 2 (12.0m x 4.5m - 54 sq.m.) -->
                    <rect x="130" y="620" width="240" height="55" fill="#f1f5f9" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="250" y="644" font-size="9.5" font-weight="600" fill="#334155" text-anchor="middle">ARTISANS VILLAGE 2</text>
                    <text x="250" y="660" font-size="7.5" font-weight="400" fill="#64748b" text-anchor="middle">12.0m x 4.5m (54 sq.m.)</text>

                    <!-- Artisans Village 3 (12.0m x 4.5m - 54 sq.m.) -->
                    <rect x="130" y="980" width="240" height="55" fill="#f1f5f9" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="250" y="1004" font-size="9.5" font-weight="600" fill="#334155" text-anchor="middle">ARTISANS VILLAGE 3</text>
                    <text x="250" y="1020" font-size="7.5" font-weight="400" fill="#64748b" text-anchor="middle">12.0m x 4.5m (54 sq.m.)</text>

                    <!-- Artisans Village 4 (12.0m x 4.5m - 54 sq.m.) -->
                    <rect x="130" y="1220" width="240" height="55" fill="#f1f5f9" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="250" y="1244" font-size="9.5" font-weight="600" fill="#334155" text-anchor="middle">ARTISANS VILLAGE 4</text>
                    <text x="250" y="1260" font-size="7.5" font-weight="400" fill="#64748b" text-anchor="middle">12.0m x 4.5m (54 sq.m.)</text>

                    <!-- Create Lab (12.0m x 4.0m - 48 sq.m.) -->
                    <rect x="130" y="2100" width="240" height="50" fill="#f1f5f9" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="250" y="2122" font-size="9.5" font-weight="600" fill="#334155" text-anchor="middle">CREATE LAB</text>
                    <text x="250" y="2138" font-size="7.5" font-weight="400" fill="#64748b" text-anchor="middle">12.0m x 4.0m (48 sq.m.)</text>

                    <!-- Likhang Filipino (24.20 sq.m.) -->
                    <rect x="130" y="2165" width="160" height="40" fill="#f1f5f9" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="210" y="2182" font-size="8.5" font-weight="600" fill="#334155" text-anchor="middle">LIKHANG FILIPINO</text>
                    <text x="210" y="2195" font-size="7" font-weight="400" fill="#64748b" text-anchor="middle">24.20 sq.m.</text>
                </g>

                <!-- Central Design Commune Pavilion (14.0m x 17.0m - 238 sq.m.) -->
                <rect x="980" y="1120" width="260" height="150" fill="#f8fafc" stroke="#64748b" stroke-width="2" rx="6"/>
                <text x="1110" y="1190" font-size="11.5" font-weight="600" fill="#334155" text-anchor="middle">DESIGN COMMUNE</text>
                <text x="1110" y="1210" font-size="8.5" font-weight="400" fill="#64748b" text-anchor="middle">14.0m x 17.0m (238 sq.m.)</text>

                <!-- Lower / South Special Feature Areas -->
                <g id="lower-pavilions">
                    <!-- Components (18.0m x 4.0m - 72 sq.m.) -->
                    <rect x="420" y="2100" width="160" height="50" fill="#f8fafc" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="500" y="2122" font-size="9" font-weight="600" fill="#334155" text-anchor="middle">COMPONENTS</text>
                    <text x="500" y="2138" font-size="7.5" font-weight="400" fill="#64748b" text-anchor="middle">18.0m x 4.0m (72 sq.m.)</text>

                    <!-- Fashion (3.0m x 12.0m - 36 sq.m.) -->
                    <rect x="680" y="2100" width="120" height="50" fill="#f8fafc" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="740" y="2122" font-size="9" font-weight="600" fill="#334155" text-anchor="middle">FASHION</text>
                    <text x="740" y="2138" font-size="7.5" font-weight="400" fill="#64748b" text-anchor="middle">3.0m x 12.0m (36 sq.m.)</text>

                    <!-- Christmas Setting (3.0m x 12.0m - 36 sq.m.) -->
                    <rect x="830" y="2100" width="150" height="50" fill="#f8fafc" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="905" y="2120" font-size="8.5" font-weight="600" fill="#334155" text-anchor="middle">CHRISTMAS SETTING</text>
                    <text x="905" y="2135" font-size="7.5" font-weight="400" fill="#64748b" text-anchor="middle">3.0m x 12.0m (36 sq.m.)</text>

                    <!-- DCP / Concessionaire C/O WTC (18.0m x 4.0m - 72 sq.m.) -->
                    <rect x="1010" y="2100" width="140" height="50" fill="#f8fafc" stroke="#94a3b8" stroke-width="1.5" rx="4"/>
                    <text x="1080" y="2122" font-size="9" font-weight="600" fill="#334155" text-anchor="middle">CONCESSIONAIRE / DCP</text>
                    <text x="1080" y="2138" font-size="7.5" font-weight="400" fill="#64748b" text-anchor="middle">18.0m x 4.0m (72 sq.m.)</text>
                </g>

                <!-- Dynamic Exhibition Booths Layer (Zones A through L) -->
                <g id="booths-layer">
                    @foreach($booths as $booth)
                        @php
                            $boothName = trim($booth->name);
                            $block = strtoupper($booth->block);
                            // Extract numeric portion of the booth number for vertical positioning
                            preg_match('/\d+/', $booth->booth_number, $matches);
                            $num = isset($matches[0]) ? (int)$matches[0] : 1;
                            
                            $boothWidth = 26;
                            $boothHeight = 18;
                            $gapY = 21;

                            // Architectural column placement mapping right (A) to left (L) accurately matching the official floor plan blueprint
                            switch($block) {
                                case 'A': $posX = 1580; $posY = 250 + (($num - 1) * $gapY); break;
                                case 'B': $posX = 1460; $posY = 250 + (($num - 1) * $gapY); break;
                                case 'C': $posX = 1340; $posY = 250 + (($num - 1) * $gapY); break;
                                case 'D': $posX = 1220; $posY = 250 + (($num - 1) * $gapY); break;
                                case 'E': $posX = 1100; $posY = 250 + (($num - 1) * $gapY); break;
                                case 'F': $posX = 980;  $posY = 250 + (($num - 1) * $gapY); break;
                                case 'G': $posX = 860;  $posY = 250 + (($num - 1) * $gapY); break;
                                case 'H': $posX = 740;  $posY = 250 + (($num - 1) * $gapY); break;
                                case 'I': $posX = 620;  $posY = 250 + (($num - 1) * $gapY); break;
                                case 'J': $posX = 500;  $posY = 250 + (($num - 1) * $gapY); break;
                                case 'K': $posX = 410;  $posY = 250 + (($num - 1) * $gapY); break;
                                case 'L': $posX = 80;   $posY = 250 + (($num - 1) * $gapY); break;
                                default:  
                                    $posX = 800 + (($loop->index % 10) * 32); 
                                    $posY = 1800 + (floor($loop->index / 10) * 20);
                            }

                            $fillColor = '#475569';
                            $exhibitorId = $booth->exhibitor_id ?? 'Unassigned';
                            $tag = $booth->tag ?? 'Standard';
                            $sizeInfo = $booth->size ? "Size: {$booth->size}" : '';
                            $catClass = strtolower($booth->category);
                        @endphp

                        <g class="booth-group transition-opacity duration-300" data-category="{{ $catClass }}" data-search="{{ strtolower($boothName) }} exhibitor-{{ strtolower($exhibitorId) }} {{ strtolower($tag) }}">
                            <rect id="booth-rect-{{ $loop->index }}" 
                                  x="{{ $posX }}" y="{{ $posY }}" width="{{ $boothWidth }}" height="{{ $boothHeight }}" rx="2" 
                                  fill="{{ $fillColor }}" stroke="#1e293b" stroke-width="1" class="booth-rect shadow-xs" 
                                  onclick="selectBooth(this, '{{ $boothName }}', '{{ $exhibitorId }}', '{{ $tag }}', '{{ $sizeInfo }}', {{ $posX }}, {{ $posY }}, {{ $boothWidth }}, {{ $boothHeight }})" />
                            
                            <text x="{{ $posX + ($boothWidth / 2) }}" y="{{ $posY + ($boothHeight / 2) + 2.5 }}" 
                                  font-size="5.5" font-weight="600" fill="#ffffff" text-anchor="middle" pointer-events="none">
                                {{ $boothName }}
                            </text>
                        </g>
                    @endforeach
                </g>

                <!-- Wayfinding Route Path -->
                <path id="wayfinding-path" d="" fill="none" stroke="#334155" stroke-width="5" stroke-dasharray="8 6" class="hidden animate-path"/>
            </g>
        </svg>

        <!-- Zoom Controls -->
        <div class="absolute bottom-6 left-6 flex flex-col gap-2 z-40 bg-white/90 backdrop-blur-md border border-slate-200 p-1.5 rounded-xl shadow-md">
            <button onclick="adjustZoom(0.2)" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-base flex items-center justify-center transition">+</button>
            <button onclick="adjustZoom(-0.2)" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-base flex items-center justify-center transition">-</button>
            <div class="h-px bg-slate-200 my-0.5"></div>
            <button onclick="resetMap()" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-xs flex items-center justify-center transition">1:1</button>
        </div>
    </main>

    <!-- Details Modal -->
    <div id="booth-modal" class="hidden absolute bottom-6 right-6 bg-white/95 backdrop-blur-xl border border-slate-200 shadow-xl rounded-2xl p-5 w-88 z-45 text-slate-700">
        <div class="flex justify-between items-start mb-2.5">
            <div>
                <span id="modal-booth-tag" class="bg-slate-800 text-white font-medium px-2 py-0.5 rounded text-[10px] uppercase tracking-wide">BOOTH</span>
                <h3 id="modal-title" class="font-bold text-base text-slate-800 mt-1">Booth Details</h3>
            </div>
            <button onclick="closeModal()" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-lg font-bold transition">&times;</button>
        </div>
        
        <p id="modal-desc" class="text-xs text-slate-500 mb-2.5 font-medium">Exhibitor ID: --</p>
        
        <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl mb-2 shadow-2xs">
            <div class="text-[11px] font-semibold text-slate-700 uppercase tracking-wide mb-1 flex items-center justify-between">
                <span>🚶 Route Direction</span>
                <span id="modal-time" class="text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200">~1.0 min</span>
            </div>
            <p id="modal-directions" class="text-xs text-slate-600 leading-relaxed font-normal">
                Enter through the upper central entrance and follow the block corridors to your destination.
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());

        let scale = 0.48, panning = false, pointX = 50, pointY = -50, startX = 0, startY = 0;
        const panLayer = document.getElementById('pan-zoom-layer');
        const viewport = document.getElementById('map-viewport');

        function updateTransform() {
            panLayer.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
        }
        
        function initMapCenter() {
            const width = viewport.clientWidth;
            const height = viewport.clientHeight;
            pointX = (width - (1800 * scale)) / 2;
            pointY = (height - (2600 * scale)) / 2;
            updateTransform();
        }

        window.addEventListener('load', initMapCenter);
        window.addEventListener('resize', initMapCenter);

        function resetMap() {
            scale = 0.48;
            initMapCenter();
        }

        function adjustZoom(amount) {
            scale = Math.min(Math.max(scale + amount, 0.3), 3.0);
            updateTransform();
        }

        viewport.addEventListener('mousedown', (e) => {
            panning = true; startX = e.clientX - pointX; startY = e.clientY - pointY;
        });
        window.addEventListener('mousemove', (e) => {
            if (!panning) return;
            pointX = e.clientX - startX; pointY = e.clientY - startY;
            updateTransform();
        });
        window.addEventListener('mouseup', () => panning = false);

        viewport.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                panning = true;
                startX = e.touches[0].clientX - pointX;
                startY = e.touches[0].clientY - pointY;
            }
        });
        viewport.addEventListener('touchmove', (e) => {
            if (!panning || e.touches.length !== 1) return;
            pointX = e.touches[0].clientX - startX;
            pointY = e.touches[0].clientY - startY;
            updateTransform();
        });
        viewport.addEventListener('touchend', () => panning = false);

        viewport.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomIntensity = 0.1;
            if (e.deltaY < 0) {
                scale = Math.min(scale * (1 + zoomIntensity), 3.0);
            } else {
                scale = Math.max(scale * (1 - zoomIntensity), 0.3);
            }
            updateTransform();
        }, { passive: false });

        let activeRect = null;
        function selectBooth(element, name, exhibitorId, tag, sizeInfo, bx, by, bw, bh) {
            if (activeRect) activeRect.classList.remove('active-booth');
            activeRect = element;
            activeRect.classList.add('active-booth');

            const entranceX = 900, entranceY = 2380;
            const targetX = bx + (bw / 2);
            const targetY = by + (bh / 2);
            
            const path = document.getElementById('wayfinding-path');
            path.setAttribute('d', `M ${entranceX} ${entranceY} L ${targetX} ${entranceY} L ${targetX} ${targetY}`);
            path.classList.remove('hidden');

            document.getElementById('modal-booth-tag').innerText = tag || 'BOOTH';
            document.getElementById('modal-title').innerText = `Booth ${name}`;
            document.getElementById('modal-desc').innerText = `Exhibitor ID: ${exhibitorId} ${sizeInfo ? ' | ' + sizeInfo : ''}`;
            
            const distance = Math.hypot(targetX - entranceX, targetY - entranceY);
            const mins = Math.max(0.5, (distance / 500)).toFixed(1);
            document.getElementById('modal-time').innerText = `~${mins} mins`;

            document.getElementById('modal-directions').innerText = `From the Main Entrance, head north up the main aisle to reach Booth ${name}.`;

            document.getElementById('booth-modal').classList.remove('hidden');
        }

        function closeModal() {
            if (activeRect) activeRect.classList.remove('active-booth');
            document.getElementById('booth-modal').classList.add('hidden');
            document.getElementById('wayfinding-path').classList.add('hidden');
        }

        function filterCategory(category) {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('bg-slate-800', 'text-white');
                btn.classList.add('bg-slate-100', 'text-slate-600');
            });
            event.target.classList.remove('bg-slate-100', 'text-slate-600');
            event.target.classList.add('bg-slate-800', 'text-white');

            document.querySelectorAll('.booth-group').forEach(group => {
                const boothCategory = group.getAttribute('data-category');
                if (category === 'all' || boothCategory === category) {
                    group.style.opacity = '1';
                } else {
                    group.style.opacity = '0.15';
                }
            });
        }

        document.getElementById('search-input').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.booth-group').forEach(group => {
                const searchData = group.getAttribute('data-search');
                group.style.opacity = (searchData.includes(query) || query === '') ? '1' : '0.15';
            });
        });
    </script>
</body>
</html>