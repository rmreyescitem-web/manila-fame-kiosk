<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manila FAME 2026 Comprehensive Floor Plan Kiosk</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <style>
        html, body {
            margin: 0; padding: 0; width: 100vw; height: 100vh; overflow: hidden;
            background-color: #120f0e; overscroll-behavior-y: none; touch-action: none;
            user-select: none; -webkit-user-select: none; font-family: system-ui, -apple-system, sans-serif;
        }
        .booth-rect { transition: all 0.2s ease; cursor: pointer; }
        .booth-rect:hover { filter: brightness(1.25); stroke: #38bdf8; stroke-width: 3px; }
        #pan-zoom-layer { transform-origin: 0 0; will-change: transform; }
    </style>
</head>
<body class="flex flex-col h-screen w-screen overflow-hidden bg-[#120f0e] text-amber-100">

    <!-- Header & Search Bar -->
    <header class="bg-[#1a1614] border-b border-[#2d2623] px-6 py-3 flex items-center justify-between shadow-xl z-30 shrink-0 h-16">
        <div class="flex items-center space-x-3">
            <span class="bg-amber-600 text-white font-bold px-3 py-1.5 rounded-lg text-xs tracking-wider shadow">MF 2026</span>
            <h1 class="text-base font-bold text-amber-100 tracking-wide">Manila FAME 2026 Comprehensive Floor Plan Kiosk</h1>
        </div>
        <div class="flex items-center space-x-4">
            <input type="text" id="search-input" placeholder="Search booth name, category..." 
                   class="px-4 py-2 border border-[#3d332f] rounded-lg text-sm w-80 focus:outline-none focus:ring-2 focus:ring-amber-500 bg-[#120f0e] text-amber-100 placeholder-amber-400/40 shadow-inner">
            <div class="text-xs font-semibold text-amber-200 bg-[#120f0e] px-3 py-2 rounded-lg border border-[#3d332f] flex items-center gap-1.5 shadow-sm">
                <span>📍</span> <span class="opacity-90">Main Entrance: Bottom Center</span>
            </div>
        </div>
    </header>

    <!-- Fullscreen Interactive Viewport Container -->
    <main id="map-viewport" class="relative flex-1 w-full h-full overflow-hidden bg-[#120f0e] cursor-grab active:cursor-grabbing">
        <svg id="floor-plan-svg" viewBox="0 0 1600 2100" preserveAspectRatio="xMidYMid meet" class="w-full h-full block">
            <g id="pan-zoom-layer">
                <!-- Hall Architectural Boundary (World Trade Center Metro Manila Layout) -->
                <rect x="80" y="50" width="1440" height="1950" fill="#1e1916" stroke="#4a3f38" stroke-width="6" rx="12"/>

                <!-- Top Perimeter: Loading Bay Infrastructure (Bays 1-9) -->
                <rect x="110" y="80" width="1380" height="140" fill="#26201c" stroke="#5c4d43" stroke-width="2" rx="6"/>
                <text x="800" y="125" font-size="16" font-weight="bold" fill="#d4bfae" text-anchor="middle">LOADING BAY INFRASTRUCTURE (BAYS 1 - 9) & ROLL-UP DOORS</text>
                <text x="800" y="150" font-size="11" fill="#a89382" text-anchor="middle">World Trade Center Metro Manila Top Perimeter Service Access</text>

                <!-- Top Amenities & Special Pavilions -->
                <rect x="110" y="245" width="240" height="130" fill="#3b1f1f" stroke="#fca5a5" stroke-width="2" rx="6"/>
                <text x="230" y="305" font-size="12" font-weight="bold" fill="#fca5a5" text-anchor="middle">BUYERS LOUNGE</text>
                <text x="230" y="325" font-size="10" fill="#fca5a5" opacity="0.8" text-anchor="middle">20.0m x 12.0m (240 sq.m)</text>

                <rect x="630" y="245" width="260" height="130" fill="#1e293b" stroke="#93c5fd" stroke-width="2" rx="6"/>
                <text x="760" y="305" font-size="12" font-weight="bold" fill="#93c5fd" text-anchor="middle">HOME AT FAME</text>
                <text x="760" y="325" font-size="10" fill="#93c5fd" opacity="0.8" text-anchor="middle">18.0m x 12.0m (216 sq.m)</text>

                <rect x="1330" y="245" width="160" height="130" fill="#292524" stroke="#d4bfae" stroke-width="2" rx="6"/>
                <text x="1410" y="305" font-size="12" font-weight="bold" fill="#d4bfae" text-anchor="middle">OBC BOOTH</text>
                <text x="1410" y="325" font-size="10" fill="#d4bfae" opacity="0.8" text-anchor="middle">9.0 sq.m</text>

                <!-- Central Pavilions & Special Features -->
                <rect x="1050" y="850" width="240" height="200" fill="#082f49" stroke="#38bdf8" stroke-width="2" rx="6"/>
                <text x="1170" y="945" font-size="13" font-weight="bold" fill="#38bdf8" text-anchor="middle">DESIGN COMMUNE</text>
                <text x="1170" y="965" font-size="10" fill="#38bdf8" opacity="0.8" text-anchor="middle">14.0m x 17.0m (238 sq.m)</text>

                <!-- Artisans Village 1-4 (Far-Left Aisles K & L) -->
                <g fill="#332924" stroke="#a89382" stroke-width="1.5">
                    <rect x="110" y="420" width="140" height="90" rx="4"/>
                    <rect x="110" y="530" width="140" height="90" rx="4"/>
                    <rect x="110" y="640" width="140" height="90" rx="4"/>
                    <rect x="110" y="750" width="140" height="90" rx="4"/>
                </g>
                <text x="180" y="470" font-size="11" font-weight="bold" fill="#d4bfae" text-anchor="middle">ARTISANS 1</text>
                <text x="180" y="580" font-size="11" font-weight="bold" fill="#d4bfae" text-anchor="middle">ARTISANS 2</text>
                <text x="180" y="690" font-size="11" font-weight="bold" fill="#d4bfae" text-anchor="middle">ARTISANS 3</text>
                <text x="180" y="800" font-size="11" font-weight="bold" fill="#d4bfae" text-anchor="middle">ARTISANS 4</text>

                <!-- Bottom Core & Special Exhibits -->
                <rect x="630" y="1560" width="340" height="80" fill="#292524" stroke="#a89382" stroke-width="2" rx="6"/>
                <text x="800" y="1595" font-size="12" font-weight="bold" fill="#d4bfae" text-anchor="middle">LOWER CORE EXHIBIT (18.0m x 4.0m)</text>
                <text x="800" y="1615" font-size="10" fill="#a89382" text-anchor="middle">Fashion, Christmas Settings & DCP Pavilions</text>

                <!-- Bottom Perimeter Services -->
                <rect x="110" y="1720" width="1380" height="60" fill="#26201c" stroke="#5c4d43" stroke-width="1.5" rx="6"/>
                <text x="800" y="1755" font-size="11" font-weight="bold" fill="#d4bfae" text-anchor="middle">BOTTOM PERIMETER SERVICES: RESTROOMS, LOUNGE, ESCALATORS, ATM, FIRST AID & BAGGAGE</text>

                <!-- Main Entrance Marker -->
                <g id="entrance-marker" transform="translate(800, 1920)">
                    <circle cx="0" cy="0" r="32" fill="#ef4444" opacity="0.3">
                        <animate attributeName="r" values="14;38;14" dur="2s" repeatCount="indefinite"/>
                        <animate attributeName="opacity" values="0.7;0;0.7" dur="2s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="0" cy="0" r="12" fill="#dc2626"/>
                    <text x="0" y="42" font-size="13" font-weight="bold" fill="#ef4444" text-anchor="middle">MAIN ENTRANCE (YOU ARE HERE)</text>
                </g>

                <!-- Alphanumeric Booth Grid (Distributed Rows A to I) -->
                <g id="booths-layer">
                    <g class="booth-group" data-search="a-6 a6 artisan furniture home decor">
                        <rect x="1350" y="450" width="90" height="60" rx="4" fill="#3b82f6" stroke="#94a3b8" stroke-width="1.5" class="booth-rect"
                            onclick="selectBooth('A-6', 'Artisan Furniture & Home Decor', 1350, 450, 90, 60)" />
                        <text x="1395" y="485" font-size="12" font-weight="bold" fill="#ffffff" text-anchor="middle" pointer-events="none">A-6</text>
                    </g>
                    <g class="booth-group" data-search="f-10 f10 contemporary lighting fixtures">
                        <rect x="680" y="750" width="90" height="60" rx="4" fill="#10b981" stroke="#94a3b8" stroke-width="1.5" class="booth-rect"
                            onclick="selectBooth('F-10', 'Contemporary Lighting & Fixtures', 680, 750, 90, 60)" />
                        <text x="725" y="785" font-size="12" font-weight="bold" fill="#ffffff" text-anchor="middle" pointer-events="none">F-10</text>
                    </g>
                    <g class="booth-group" data-search="k-36 k36 sustainable textiles weaving">
                        <rect x="300" y="1150" width="90" height="60" rx="4" fill="#8b5cf6" stroke="#94a3b8" stroke-width="1.5" class="booth-rect"
                            onclick="selectBooth('K-36', 'Sustainable Textiles & Weaving', 300, 1150, 90, 60)" />
                        <text x="345" y="1185" font-size="12" font-weight="bold" fill="#ffffff" text-anchor="middle" pointer-events="none">K-36</text>
                    </g>
                    <g class="booth-group" data-search="a-1 standard exhibition">
                        <rect x="1350" y="530" width="90" height="50" rx="4" fill="#3b82f6" class="booth-rect" onclick="selectBooth('A-1', 'General Exhibition', 1350, 530, 90, 50)" />
                        <text x="1395" y="560" font-size="11" font-weight="bold" fill="#fff" text-anchor="middle" pointer-events="none">A-1</text>
                    </g>
                    <g class="booth-group" data-search="b-12 home decor">
                        <rect x="1150" y="450" width="90" height="50" rx="4" fill="#f59e0b" class="booth-rect" onclick="selectBooth('B-12', 'Home & Lifestyle', 1150, 450, 90, 50)" />
                        <text x="1195" y="480" font-size="11" font-weight="bold" fill="#fff" text-anchor="middle" pointer-events="none">B-12</text>
                    </g>
                    <g class="booth-group" data-search="c-20 interior design">
                        <rect x="950" y="450" width="90" height="50" rx="4" fill="#ec4899" class="booth-rect" onclick="selectBooth('C-20', 'Interior Accents', 950, 450, 90, 50)" />
                        <text x="995" y="480" font-size="11" font-weight="bold" fill="#fff" text-anchor="middle" pointer-events="none">C-20</text>
                    </g>
                    <g class="booth-group" data-search="d-15 furniture">
                        <rect x="750" y="450" width="90" height="50" rx="4" fill="#14b8a6" class="booth-rect" onclick="selectBooth('D-15', 'Modern Furniture', 750, 450, 90, 50)" />
                        <text x="795" y="480" font-size="11" font-weight="bold" fill="#fff" text-anchor="middle" pointer-events="none">D-15</text>
                    </g>
                    <g class="booth-group" data-search="e-08 lighting">
                        <rect x="550" y="450" width="90" height="50" rx="4" fill="#6366f1" class="booth-rect" onclick="selectBooth('E-08', 'Lighting Solutions', 550, 450, 90, 50)" />
                        <text x="595" y="480" font-size="11" font-weight="bold" fill="#fff" text-anchor="middle" pointer-events="none">E-08</text>
                    </g>
                    <g class="booth-group" data-search="f-05 holiday decor">
                        <rect x="550" y="620" width="90" height="50" rx="4" fill="#10b981" class="booth-rect" onclick="selectBooth('F-05', 'Holiday & Seasonal', 550, 620, 90, 50)" />
                        <text x="595" y="650" font-size="11" font-weight="bold" fill="#fff" text-anchor="middle" pointer-events="none">F-05</text>
                    </g>
                    <g class="booth-group" data-search="g-14 fashion accessories">
                        <rect x="750" y="620" width="90" height="50" rx="4" fill="#84cc16" class="booth-rect" onclick="selectBooth('G-14', 'Fashion & Wearables', 750, 620, 90, 50)" />
                        <text x="795" y="650" font-size="11" font-weight="bold" fill="#fff" text-anchor="middle" pointer-events="none">G-14</text>
                    </g>
                    <g class="booth-group" data-search="h-22 Philippine crafts">
                        <rect x="950" y="1150" width="90" height="50" rx="4" fill="#06b6d4" class="booth-rect" onclick="selectBooth('H-22', 'Crafts & Souvenirs', 950, 1150, 90, 50)" />
                        <text x="995" y="1180" font-size="11" font-weight="bold" fill="#fff" text-anchor="middle" pointer-events="none">H-22</text>
                    </g>
                    <g class="booth-group" data-search="i-30 natural materials">
                        <rect x="1150" y="1150" width="90" height="50" rx="4" fill="#f43f5e" class="booth-rect" onclick="selectBooth('I-30', 'Natural Materials', 1150, 1150, 90, 50)" />
                        <text x="1195" y="1180" font-size="11" font-weight="bold" fill="#fff" text-anchor="middle" pointer-events="none">I-30</text>
                    </g>
                </g>

                <!-- Wayfinding Line Route -->
                <path id="wayfinding-path" d="" fill="none" stroke="#38bdf8" stroke-width="6" stroke-dasharray="10 6" class="hidden"/>
            </g>
        </svg>

        <!-- Floating Kiosk Quick Zoom Controls -->
        <div class="absolute bottom-6 left-6 flex flex-col gap-2 z-40">
            <button onclick="adjustZoom(0.2)" class="bg-[#1a1614] border border-[#3d332f] hover:bg-[#2d2623] text-amber-100 font-bold w-11 h-11 rounded-xl shadow-xl flex items-center justify-center text-xl transition">+</button>
            <button onclick="adjustZoom(-0.2)" class="bg-[#1a1614] border border-[#3d332f] hover:bg-[#2d2623] text-amber-100 font-bold w-11 h-11 rounded-xl shadow-xl flex items-center justify-center text-xl transition">-</button>
            <button onclick="resetMap()" class="bg-[#1a1614] border border-[#3d332f] hover:bg-[#2d2623] text-amber-100 font-bold w-11 h-11 rounded-xl shadow-xl flex items-center justify-center text-xs transition" title="Reset View">1:1</button>
        </div>
    </main>

    <!-- Details & Wayfinding Modal -->
    <div id="booth-modal" class="hidden absolute bottom-6 right-6 bg-[#1a1614] border border-[#3d332f] shadow-2xl rounded-2xl p-6 w-80 z-45 text-amber-100 backdrop-blur-md bg-opacity-95">
        <div class="flex justify-between items-start mb-3">
            <div>
                <span id="modal-booth-tag" class="bg-amber-600 text-white font-bold px-2.5 py-1 rounded-md text-[10px] uppercase tracking-wider">BOOTH</span>
                <h3 id="modal-title" class="font-bold text-lg text-amber-100 mt-1.5">Booth Details</h3>
            </div>
            <button onclick="closeModal()" class="text-amber-400 hover:text-white font-bold text-2xl px-1 leading-none">&times;</button>
        </div>
        <p id="modal-desc" class="text-xs text-amber-300/80 mb-4">Category: General Exhibition</p>
        <div class="text-xs font-semibold text-sky-300 bg-sky-950/80 border border-sky-800/60 p-3 rounded-xl text-center shadow-inner">
            Estimated Walking Time from Entrance: <span class="text-white font-bold">1.5 mins</span>
        </div>
    </div>

    <!-- JavaScript Interaction Logic -->
    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());

        let scale = 0.65, panning = false, pointX = 50, pointY = -50, startX = 0, startY = 0;
        const panLayer = document.getElementById('pan-zoom-layer');
        const viewport = document.getElementById('map-viewport');

        function updateTransform() {
            panLayer.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
        }
        
        function initMapCenter() {
            const width = viewport.clientWidth;
            const height = viewport.clientHeight;
            pointX = (width - (1600 * scale)) / 2;
            pointY = (height - (2100 * scale)) / 2;
            updateTransform();
        }

        window.addEventListener('load', initMapCenter);
        window.addEventListener('resize', initMapCenter);

        function resetMap() {
            scale = 0.65;
            initMapCenter();
        }

        function adjustZoom(amount) {
            scale = Math.min(Math.max(scale + amount, 0.3), 3.0);
            updateTransform();
        }

        // Pan and Mouse functionality
        viewport.addEventListener('mousedown', (e) => {
            panning = true; startX = e.clientX - pointX; startY = e.clientY - pointY;
        });
        window.addEventListener('mousemove', (e) => {
            if (!panning) return;
            pointX = e.clientX - startX; pointY = e.clientY - startY;
            updateTransform();
        });
        window.addEventListener('mouseup', () => panning = false);

        // Touch layout support for kiosk touchscreens
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

        function selectBooth(name, category, bx, by, bw, bh) {
            const entranceX = 800, entranceY = 1920;
            const targetX = bx + (bw / 2);
            const targetY = by + (bh / 2);
            
            const path = document.getElementById('wayfinding-path');
            path.setAttribute('d', `M ${entranceX} ${entranceY} L ${entranceX} 1650 L ${targetX} 1650 L ${targetX} ${targetY}`);
            path.classList.not('hidden') || path.classList.remove('hidden');

            document.getElementById('modal-booth-tag').innerText = `BOOTH ${name}`;
            document.getElementById('modal-title').innerText = name;
            document.getElementById('modal-desc').innerText = `Category: ${category || 'General Exhibition'}`;
            document.getElementById('booth-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('booth-modal').classList.add('hidden');
            document.getElementById('wayfinding-path').classList.add('hidden');
        }

        document.getElementById('search-input').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.booth-group').forEach(group => {
                const searchData = group.getAttribute('data-search');
                group.style.opacity = (searchData.includes(query) || query === '') ? '1' : '0.15';
            });
        });
    </script>
</body>
</html>