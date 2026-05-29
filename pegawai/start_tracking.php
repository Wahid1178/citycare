<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Pegawai Lapangan']);

$id = $_GET['id'] ?? '';

if (empty($id)) {
    die("ID laporan tidak ditemukan.");
}

$laporan = $laporanCollection->findOne([
    '_id' => new MongoDB\BSON\ObjectId($id),
    'pegawai_id' => $_SESSION['user']['id']
]);

if (!$laporan) {
    die("Laporan tidak ditemukan atau bukan tugas Anda.");
}

$tujuanLat = (float)($laporan['latitude_tujuan'] ?? 0);
$tujuanLng = (float)($laporan['longitude_tujuan'] ?? 0);

include __DIR__ . '/../partials/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css">

<div class="page-header">
    <h1>Mulai Tracking Perjalanan</h1>
    <p>Pegawai sedang menuju lokasi laporan: <b><?= safe($laporan['judul']) ?></b></p>
</div>

<div class="card">
    <div class="live-badge">
        <div class="live-dot"></div>
        LIVE TRACKING AKTIF
    </div>

    <div id="map" style="height:560px;border-radius:24px;margin-top:20px;"></div>
    <div id="trackingInfo" class="tracking-card" style="margin-top:16px;"></div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

<script>
const laporanId = <?= json_encode($id) ?>;
const tujuanLat = <?= json_encode($tujuanLat) ?>;
const tujuanLng = <?= json_encode($tujuanLng) ?>;

const map = L.map('map').setView([tujuanLat, tujuanLng], 14);

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

let pegawaiMarker = null;
let tujuanMarker = L.marker([tujuanLat, tujuanLng], {
    icon: destinationIcon
}).addTo(map).bindPopup('Tujuan Laporan');

let routingControl = null;
let fallbackLine = null;

function tampilkanRute(lat, lng) {
    if (routingControl) {
        map.removeControl(routingControl);
        routingControl = null;
    }

    if (fallbackLine) {
        map.removeLayer(fallbackLine);
        fallbackLine = null;
    }

    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(lat, lng),
            L.latLng(tujuanLat, tujuanLng)
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
                weight: 7,
                opacity: 0.9
            }]
        },
        createMarker: function() {
            return null;
        }
    }).addTo(map);

    routingControl.on('routesfound', function(e) {
        const route = e.routes[0];

        document.getElementById('trackingInfo').innerHTML = `
            <b>🏍️ Pegawai sedang menuju lokasi</b><br>
            Jarak rute: ${(route.summary.totalDistance / 1000).toFixed(2)} km<br>
            Estimasi waktu: ${Math.ceil(route.summary.totalTime / 60)} menit
        `;
    });

    routingControl.on('routingerror', function() {
        fallbackLine = L.polyline(
            [
                [lat, lng],
                [tujuanLat, tujuanLng]
            ],
            {
                color: '#ef4444',
                weight: 5,
                dashArray: '10,8'
            }
        ).addTo(map);

        document.getElementById('trackingInfo').innerHTML = `
            <b>Rute jalan gagal dimuat.</b><br>
            Ditampilkan garis cadangan. Pastikan internet aktif.
        `;
    });
}

navigator.geolocation.watchPosition(async function(position) {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;

    if (pegawaiMarker) {
        pegawaiMarker.setLatLng([lat, lng]);
    } else {
        pegawaiMarker = L.marker([lat, lng], {
            icon: vehicleIcon
        }).addTo(map).bindPopup('Pegawai Lapangan');
    }

    await fetch('/pegawai/update_tracking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            laporan_id: laporanId,
            latitude: lat,
            longitude: lng
        })
    });

    tampilkanRute(lat, lng);

}, function(error) {
    alert('GPS gagal diakses. Izinkan lokasi pada browser.');
}, {
    enableHighAccuracy: true,
    maximumAge: 0,
    timeout: 10000
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>