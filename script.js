// ===================================================
//   BD FLEET TRACKER — LIVE VEHICLE TRACKING SYSTEM
//   Bangladesh (Dhaka) Fleet Management
// ===================================================

// ─── MAP INITIALIZATION ───────────────────────────
const map = L.map('map', {
    center: [23.8103, 90.4125],
    zoom: 13,
    zoomControl: true,
    attributionControl: false
});

// Map Layers
const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
});

const darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '© CARTO'
});

// Start with dark layer
darkLayer.addTo(map);
let currentLayerName = 'street';

// ─── VEHICLE DATA ─────────────────────────────────
const vehicles = {
    car: {
        id: 'car',
        name: 'টয়োটা করোলা',
        plate: 'বিএ-১৪৩২ · ঢাকা',
        driver: 'রহমান আ.',
        type: 'car',
        lat: 23.8103,
        lng: 90.4125,
        speed: 42,
        fuel: 68,
        status: 'running',
        heading: 'উত্তর-পূর্ব',
        altitude: 12,
        route: { from: 'মতিঝিল', to: 'উত্তরা', progress: 55, eta: '৩৮ মিনিট' },
        color: '#DC143C',
        trail: [],
        totalKm: 87
    },
    bus: {
        id: 'bus',
        name: 'বিআরটিসি সিটি বাস',
        plate: '১১-৫৮৭২ · ঢাকা',
        driver: 'করিম এম.',
        type: 'bus',
        lat: 23.7800,
        lng: 90.3800,
        speed: 0,
        fuel: 23,
        status: 'idle',
        heading: '--',
        altitude: 8,
        route: { from: 'মিরপুর', to: 'গুলশান', progress: 30, eta: 'থেমে আছে' },
        color: '#FF9800',
        trail: [],
        totalKm: 112
    },
    truck: {
        id: 'truck',
        name: 'ইসুজু কার্গো',
        plate: 'টিআর-০২৯১ · চট্টগ্রাম',
        driver: 'হাসান আর.',
        type: 'truck',
        lat: 23.8400,
        lng: 90.4500,
        speed: 61,
        fuel: 51,
        status: 'running',
        heading: 'উত্তর-পশ্চিম',
        altitude: 15,
        route: { from: 'পোর্ট টার্মিনাল', to: 'তেজগাঁও', progress: 70, eta: '২২ মিনিট' },
        color: '#9C27B0',
        trail: [],
        totalKm: 48
    }
};

let selectedVehicle = 'car';

// ─── CUSTOM MARKERS ───────────────────────────────
function createVehicleIcon(type, color, isActive) {
    const svgMap = {
        car: `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 24" width="44" height="24">
              <rect x="4" y="10" width="36" height="11" rx="3" fill="#0d1e2e" stroke="${color}" stroke-width="1.5"/>
              <path d="M8 10 L13 4 L31 4 L36 10" fill="#0a1520" stroke="${color}" stroke-width="1.2"/>
              <circle cx="10" cy="21" r="3.5" fill="#060d14" stroke="${color}" stroke-width="1.5"/>
              <circle cx="34" cy="21" r="3.5" fill="#060d14" stroke="${color}" stroke-width="1.5"/>
              <circle cx="10" cy="21" r="1.2" fill="${color}"/>
              <circle cx="34" cy="21" r="1.2" fill="${color}"/>
              <rect x="14" y="5.5" width="6" height="3.5" rx="0.8" fill="${color}" opacity="0.35"/>
              <rect x="24" y="5.5" width="6" height="3.5" rx="0.8" fill="${color}" opacity="0.35"/>
              <rect x="2" y="13" width="3" height="2" rx="0.5" fill="#ffe066" opacity="0.9"/>
              <rect x="39" y="13" width="3" height="2" rx="0.5" fill="#ff4455" opacity="0.7"/>
            </svg>`,
        bus: `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56 28" width="56" height="28">
              <rect x="2" y="4" width="52" height="20" rx="3" fill="#0d1e2e" stroke="${color}" stroke-width="1.5"/>
              <rect x="2" y="4" width="52" height="7" rx="2" fill="#0a1520" stroke="${color}" stroke-width="0.8"/>
              <rect x="6" y="6" width="6" height="3.5" rx="0.8" fill="${color}" opacity="0.35"/>
              <rect x="15" y="6" width="6" height="3.5" rx="0.8" fill="${color}" opacity="0.35"/>
              <rect x="24" y="6" width="6" height="3.5" rx="0.8" fill="${color}" opacity="0.35"/>
              <rect x="33" y="6" width="6" height="3.5" rx="0.8" fill="${color}" opacity="0.35"/>
              <rect x="42" y="6" width="6" height="3.5" rx="0.8" fill="${color}" opacity="0.35"/>
              <circle cx="10" cy="25" r="3" fill="#060d14" stroke="${color}" stroke-width="1.5"/>
              <circle cx="46" cy="25" r="3" fill="#060d14" stroke="${color}" stroke-width="1.5"/>
              <circle cx="10" cy="25" r="1" fill="${color}"/>
              <circle cx="46" cy="25" r="1" fill="${color}"/>
              <rect x="1" y="14" width="4" height="2.5" rx="0.5" fill="#ffe066" opacity="0.9"/>
              <rect x="51" y="14" width="4" height="2.5" rx="0.5" fill="#ff4455" opacity="0.7"/>
            </svg>`,
        truck: `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 28" width="60" height="28">
              <rect x="18" y="3" width="40" height="20" rx="2" fill="#0d1e2e" stroke="${color}" stroke-width="1.5"/>
              <rect x="2" y="11" width="18" height="12" rx="2" fill="#0d1e2e" stroke="${color}" stroke-width="1.5"/>
              <rect x="3" y="13" width="9" height="6" rx="0.8" fill="${color}" opacity="0.3"/>
              <circle cx="10" cy="25" r="3" fill="#060d14" stroke="${color}" stroke-width="1.5"/>
              <circle cx="43" cy="25" r="3" fill="#060d14" stroke="${color}" stroke-width="1.5"/>
              <circle cx="52" cy="25" r="3" fill="#060d14" stroke="${color}" stroke-width="1.5"/>
              <circle cx="10" cy="25" r="1" fill="${color}"/>
              <circle cx="43" cy="25" r="1" fill="${color}"/>
              <circle cx="52" cy="25" r="1" fill="${color}"/>
              <rect x="1" y="18" width="3" height="2" rx="0.5" fill="#ffe066" opacity="0.9"/>
            </svg>`
    };

    const size = type === 'bus' ? [56, 28] : type === 'truck' ? [60, 28] : [44, 24];
    const shadow = isActive ? `filter: drop-shadow(0 0 6px ${color}) drop-shadow(0 0 12px ${color}); opacity:1;` : `opacity:0.8;`;

    return L.divIcon({
        html: `<div style="${shadow}">${svgMap[type]}</div>`,
        iconSize: size,
        iconAnchor: [size[0] / 2, size[1]],
        popupAnchor: [0, -size[1]],
        className: ''
    });
}

// Pulse ring for active vehicle
function createPulseIcon(color) {
    return L.divIcon({
        html: `<div style="
            width:40px; height:40px;
            border-radius:50%;
            border: 2px solid ${color};
            animation: ping 1.5s ease-out infinite;
            position:absolute;
            top:-20px; left:-20px;
            opacity: 0.6;
        "></div>`,
        className: '',
        iconSize: [0, 0],
        iconAnchor: [0, 0]
    });
}

// ─── CREATE MARKERS & TRAILS ──────────────────────
const markers = {};
const trailPolylines = {};

function initMarkers() {
    Object.values(vehicles).forEach(v => {
        const isActive = v.id === selectedVehicle;
        markers[v.id] = L.marker([v.lat, v.lng], {
            icon: createVehicleIcon(v.type, v.color, isActive)
        }).addTo(map);

        markers[v.id].bindPopup(`
            <div style="font-family:'Share Tech Mono',monospace; line-height:1.6;">
                <div style="color:${v.color}; font-size:14px; letter-spacing:2px;">${v.name}</div>
                <div style="color:#aac; font-size:10px;">প্লেট: ${v.plate}</div>
                <div style="margin-top:6px;">
                    <span style="color:#888;">গতি:</span> <span style="color:#fff;">${v.speed} কিমি/ঘণ্টা</span><br>
                    <span style="color:#888;">চালক:</span> <span style="color:#fff;">${v.driver}</span><br>
                    <span style="color:#888;">জ্বালানি:</span> <span style="color:${v.fuel < 30 ? '#ff6600' : '#00e676'};">${v.fuel}%</span>
                </div>
            </div>
        `);

        markers[v.id].on('click', () => selectVehicle(v.id));

        // Initialize trail
        trailPolylines[v.id] = L.polyline([], {
            color: v.color,
            weight: 2,
            opacity: 0.5,
            dashArray: '4 6'
        }).addTo(map);

        v.trail.push([v.lat, v.lng]);
        trailPolylines[v.id].setLatLngs(v.trail);
    });
}

// ─── SELECT VEHICLE ───────────────────────────────
function selectVehicle(id) {
    selectedVehicle = id;

    // Update sidebar cards
    document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('active'));
    document.querySelector(`.vehicle-card[data-id="${id}"]`)?.classList.add('active');

    const v = vehicles[id];

    // Update marker glows
    Object.entries(markers).forEach(([vid, marker]) => {
        marker.setIcon(createVehicleIcon(vehicles[vid].type, vehicles[vid].color, vid === id));
    });

    // Update telemetry panel
    updateTelemetry(v);

    // Pan to vehicle
    map.flyTo([v.lat, v.lng], 14, { duration: 1.2, easeLinearity: 0.3 });
}

function updateTelemetry(v) {
    document.getElementById('tele-name').textContent = v.name;
    document.getElementById('tele-name').style.color = v.color;
    document.getElementById('tele-plate').textContent = v.plate;

    const speedVal = v.speed;
    document.getElementById('tele-speed').textContent = speedVal;
    const speedPct = Math.min((speedVal / 120) * 100, 100);
    document.getElementById('speed-bar').style.width = speedPct + '%';

    document.getElementById('tele-heading').textContent = v.heading;
    document.getElementById('tele-alt').textContent = v.altitude;

    // Route
    document.getElementById('route-from').textContent = v.route.from;
    document.getElementById('route-to').textContent = v.route.to;
    document.getElementById('route-progress').style.width = v.route.progress + '%';
    document.getElementById('route-dot').style.left = v.route.progress + '%';
    document.getElementById('eta-value').textContent = v.route.eta;

    // Coords
    document.getElementById('display-lat').textContent = v.lat.toFixed(5);
    document.getElementById('display-lng').textContent = v.lng.toFixed(5);
}

// ─── MAP LAYERS ───────────────────────────────────
const layerButtons = document.querySelectorAll('.map-btn');

window.setMapLayer = function (type) {
    if (type === 'street') {
        map.removeLayer(darkLayer);
        streetLayer.addTo(map);
    } else if (type === 'satellite') {
        map.removeLayer(streetLayer);
        darkLayer.addTo(map);
    }
    layerButtons.forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
};

window.centerAll = function () {
    const bounds = Object.values(vehicles).map(v => [v.lat, v.lng]);
    map.flyToBounds(bounds, { padding: [40, 40], duration: 1.2 });
};

// ─── SIMULATION ENGINE ────────────────────────────
const HEADINGS = ['উত্তর', 'উত্তর-পূর্ব', 'পূর্ব', 'দে-পূ', 'দক্ষিণ', 'দ-প', 'পশ্চিম', 'উ-প'];
let km = 247;
let tick = 0;

function simulateVehicles() {
    tick++;

    Object.values(vehicles).forEach(v => {
        if (v.status === 'idle') {
            v.speed = 0;
            return;
        }

        // Simulate movement
        const drift = 0.0008;
        v.lat += (Math.random() - 0.5) * drift;
        v.lng += (Math.random() - 0.5) * drift;

        // Speed fluctuation
        const baseSpeed = v.type === 'car' ? 42 : v.type === 'truck' ? 55 : 38;
        v.speed = Math.max(10, Math.round(baseSpeed + (Math.random() - 0.5) * 20));

        // Heading
        v.heading = HEADINGS[Math.floor(Math.random() * HEADINGS.length)];

        // Route progress
        v.route.progress = Math.min(95, v.route.progress + Math.random() * 0.5);
        const minsLeft = Math.max(1, Math.round((100 - v.route.progress) * 0.8));
        v.route.eta = minsLeft + ' মিনিট';

        // Update marker
        markers[v.id].setLatLng([v.lat, v.lng]);
        markers[v.id].setIcon(createVehicleIcon(v.type, v.color, v.id === selectedVehicle));

        // Update trail (keep last 30 points)
        v.trail.push([v.lat, v.lng]);
        if (v.trail.length > 30) v.trail.shift();
        trailPolylines[v.id].setLatLngs(v.trail);

        // Update sidebar card speed
        const speedEl = document.getElementById(`${v.id}-speed`);
        if (speedEl) speedEl.textContent = toBengaliNumber(v.speed) + ' কিমি/ঘণ্টা';
    });

    // Update selected vehicle telemetry
    updateTelemetry(vehicles[selectedVehicle]);

    // Update total km
    km += Math.random() * 0.3;
    document.getElementById('total-km').textContent = toBengaliNumber(Math.round(km)) + ' কিমি';

    // Avg speed
    const running = Object.values(vehicles).filter(v => v.status === 'running');
    const avg = running.length ? Math.round(running.reduce((a, v) => a + v.speed, 0) / running.length) : 0;
    document.getElementById('avg-speed-footer').textContent = toBengaliNumber(avg) + ' কিমি/ঘণ্টা';

    // Add to history log
    if (tick % 2 === 0) {
        addHistoryEntry();
    }
}

// ─── HISTORY LOG ──────────────────────────────────
const historyEvents = [
    'গতি আপডেট রেকর্ড করা হয়েছে',
    'জিপিএস লক নিশ্চিত করা হয়েছে',
    'রুট চেকপয়েন্ট অতিক্রম করেছে',
    'সিগন্যাল শক্তি: ৪জি',
    'অবস্থান আপডেট করা হয়েছে'
];

function addHistoryEntry() {
    const v = vehicles[selectedVehicle];
    const list = document.getElementById('history-list');
    const now = new Date();
    const time = now.toTimeString().slice(0, 5);

    const event = v.status === 'idle'
        ? 'গাড়ি অলস · ইঞ্জিন চালু'
        : `গতি: ${toBengaliNumber(v.speed)} কিমি/ঘণ্টা · ${historyEvents[Math.floor(Math.random() * historyEvents.length)]}`;

    const item = document.createElement('div');
    item.className = 'history-item';
    item.style.opacity = '0';
    item.style.transform = 'translateX(-8px)';
    item.innerHTML = `<span class="h-time">${toBengaliNumber(time)}</span><span class="h-event">${event}</span>`;

    list.insertBefore(item, list.firstChild);

    // Fade in
    requestAnimationFrame(() => {
        item.style.transition = 'all 0.4s ease';
        item.style.opacity = '1';
        item.style.transform = 'translateX(0)';
    });

    // Keep max 8 items
    while (list.children.length > 8) {
        list.removeChild(list.lastChild);
    }
}

// ─── CLOCK ────────────────────────────────────────
function updateClock() {
    const now = new Date();
    const time = now.toTimeString().slice(0, 8);
    const dateStr = now.toLocaleDateString('bn-BD', {
        weekday: 'short', month: 'short', day: 'numeric'
    }).toUpperCase();

    document.getElementById('clock').textContent = toBengaliNumber(time);
    document.getElementById('date-display').textContent = dateStr;
}

// ─── BENGALI NUMBER CONVERTER ────────────────────
function toBengaliNumber(num) {
    const bengaliDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return String(num).replace(/\d/g, digit => bengaliDigits[digit]);
}

// ─── ADD STYLE FOR PING ANIMATION ────────────────
const style = document.createElement('style');
style.textContent = `
    @keyframes ping {
        0% { transform: scale(0.5); opacity: 0.8; }
        100% { transform: scale(2.5); opacity: 0; }
    }
`;
document.head.appendChild(style);

// ─── INITIALIZE ───────────────────────────────────
function init() {
    initMarkers();
    updateClock();
    updateTelemetry(vehicles[selectedVehicle]);

    // Initial map zoom to fit all vehicles
    const bounds = Object.values(vehicles).map(v => [v.lat, v.lng]);
    map.fitBounds(bounds, { padding: [60, 60] });

    // Timers
    setInterval(updateClock, 1000);
    setInterval(simulateVehicles, 5000);
}

window.selectVehicle = selectVehicle;
window.setMapLayer = setMapLayer;
window.centerAll = centerAll;

window.addEventListener('load', init);