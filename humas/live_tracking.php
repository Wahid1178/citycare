<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Humas']);

$trackingList = $trackingCollection->find(
    [],
    ['sort' => ['updated_at' => -1]]
);

include __DIR__ . '/../partials/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css">

<div class="page-header">
    <h1>Live Tracking Pegawai</h1>
    <p>Pantau perjalanan pegawai lapangan menuju lokasi laporan secara realtime.</p>
</div>

<div class="card">
    <label>Pilih Tracking Laporan</label>
    <select id="pilihTracking">
        <option value="">Tampilkan Semua Tracking</option>

        <?php foreach ($trackingList as $track): ?>
            <option value="<?= safe($track['laporan_id']) ?>">
                <?= safe($track['pegawai_nama'] ?? 'Pegawai') ?> -
                <?= safe($track['judul_laporan'] ?? 'Laporan') ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Peta Tracking</h2>
        <div id="map" style="height:620px;border-radius:24px;overflow:hidden;"></div>
    </div>

    <div class="card">
        <h2>Informasi Pegawai</h2>
        <div id="pegawaiList"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

<script>
const map = L.map('map').setView([-0.947083, 100.417181], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
}).addTo(map);

const vehicleIcon = L.divIcon({
    html: `<div style="font-size:36px;">🏍️</div>`,
    className: '',
    iconSize: [42, 42]
});

const destinationIcon = L.divIcon({
    html: `<div style="font-size:36px;">📍</div>`,
    className: '',
    iconSize: [42, 42]
});

let pegawaiMarkers = {};
let tujuanMarkers = {};
let routeControls = {};
let fallbackLines = {};

function clearMap() {
    Object.values(pegawaiMarkers).forEach(marker => map.removeLayer(marker));
    Object.values(tujuanMarkers).forEach(marker => map.removeLayer(marker));
    Object.values(routeControls).forEach(route => map.removeControl(route));
    Object.values(fallbackLines).forEach(line => map.removeLayer(line));

    pegawaiMarkers = {};
    tujuanMarkers = {};
    routeControls = {};
    fallbackLines = {};
}

async function loadTracking() {
    const laporanId = document.getElementById('pilihTracking').value;

    let url = '/humas/tracking_data.php';
    if (laporanId !== '') {
        url += '?laporan_id=' + encodeURIComponent(laporanId);
    }

    const response = await fetch(url);
    const data = await response.json();

    clearMap();

    const pegawaiList = document.getElementById('pegawaiList');
    pegawaiList.innerHTML = '';

    if (data.length === 0) {
        pegawaiList.innerHTML = `
            <div class="tracking-card">
                <h3>Belum ada data tracking</h3>
                <p>Pastikan Humas sudah assign pegawai dan pegawai sudah mulai tracking.</p>
            </div>
        `;
        return;
    }

    data.forEach(item => {
        const lat = parseFloat(item.latitude);
        const lng = parseFloat(item.longitude);
        const tLat = parseFloat(item.latitude_tujuan);
        const tLng = parseFloat(item.longitude_tujuan);

        if (!tLat || !tLng) {
            pegawaiList.innerHTML += `
                <div class="tracking-card">
                    <h3>${item.pegawai_nama}</h3>
                    <p>Koordinat tujuan belum tersedia. Pastikan masyarakat memilih titik lokasi di peta.</p>
                </div>
            `;
            return;
        }

        if (!lat || !lng) {
            pegawaiList.innerHTML += `
                <div class="tracking-card">
                    <h3>${item.pegawai_nama}</h3>
                    <p>
                        <b>Laporan:</b> ${item.judul_laporan}<br>
                        <b>Status:</b> ${item.status_tracking}<br>
                        Pegawai belum memulai perjalanan.
                    </p>
                </div>
            `;

            tujuanMarkers[item.laporan_id] = L.marker([tLat, tLng], {
                icon: destinationIcon
            }).addTo(map).bindPopup('Tujuan Laporan');

            map.setView([tLat, tLng], 14);
            return;
        }

        pegawaiMarkers[item.pegawai_id] = L.marker([lat, lng], {
            icon: vehicleIcon
        }).addTo(map).bindPopup(`
            <b>${item.pegawai_nama}</b><br>
            ${item.judul_laporan}<br>
            ${item.status_tracking}
        `);

        tujuanMarkers[item.laporan_id] = L.marker([tLat, tLng], {
            icon: destinationIcon
        }).addTo(map).bindPopup('Tujuan Laporan');

        routeControls[item.pegawai_id] = L.Routing.control({
            waypoints: [
                L.latLng(lat, lng),
                L.latLng(tLat, tLng)
            ],
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1'
            }),
            routeWhileDragging: false,
            draggableWaypoints: false,
            addWaypoints: false,
            fitSelectedRoutes: true,
            show: false,
            lineOptions: {
                styles: [{
                    color: '#2563eb',
                    weight: 6,
                    opacity: 0.9
                }]
            },
            createMarker: function() {
                return null;
            }
        }).addTo(map);

        routeControls[item.pegawai_id].on('routesfound', function(e) {
            const route = e.routes[0];

            pegawaiList.innerHTML += `
                <div class="tracking-card">
                    <h3>🏍️ ${item.pegawai_nama}</h3>
                    <p>
                        <b>Laporan:</b> ${item.judul_laporan}<br>
                        <b>Status:</b> ${item.status_tracking}<br>
                        <b>Jarak Rute:</b> ${(route.summary.totalDistance / 1000).toFixed(2)} km<br>
                        <b>Estimasi:</b> ${Math.ceil(route.summary.totalTime / 60)} menit<br>
                        <b>Update:</b> ${item.updated_at}
                    </p>
                </div>
            `;
        });

        routeControls[item.pegawai_id].on('routingerror', function() {
            fallbackLines[item.pegawai_id] = L.polyline(
                [
                    [lat, lng],
                    [tLat, tLng]
                ],
                {
                    color: '#ef4444',
                    weight: 5,
                    dashArray: '10,8'
                }
            ).addTo(map);

            pegawaiList.innerHTML += `
                <div class="tracking-card">
                    <h3>🏍️ ${item.pegawai_nama}</h3>
                    <p>
                        Rute jalan gagal dimuat. Ditampilkan garis cadangan.<br>
                        Pastikan internet aktif.
                    </p>
                </div>
            `;
        });
    });
}

document.getElementById('pilihTracking').addEventListener('change', loadTracking);

loadTracking();
setInterval(loadTracking, 5000);
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>