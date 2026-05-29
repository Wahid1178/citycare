<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Masyarakat']);

$userId = $_SESSION['user']['id'];
$keyword = $_GET['keyword'] ?? '';
$status = $_GET['status'] ?? '';
$kategori = $_GET['kategori'] ?? '';

$filter = ['user_id' => $userId];

if (!empty($keyword)) {
    $filter['judul'] = new MongoDB\BSON\Regex($keyword, 'i');
}

if (!empty($status)) {
    $filter['status_final'] = $status;
}

if (!empty($kategori)) {
    $filter['kategori'] = $kategori;
}

$data = $laporanCollection->find(
    $filter,
    ['sort' => ['created_at' => -1]]
);

$kategoriList = $kategoriCollection->find(
    ['status' => 'Aktif'],
    ['sort' => ['nama_kategori' => 1]]
);

include __DIR__ . '/../partials/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css">

<div class="page-header">
    <h1>Laporan Saya</h1>
    <p>Daftar seluruh laporan yang pernah Anda kirimkan.</p>
</div>

<div class="card">
    <form method="GET" class="filter-grid">
        <input 
            type="text" 
            name="keyword" 
            placeholder="Cari judul..." 
            value="<?= safe($keyword) ?>"
        >

        <select name="kategori">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategoriList as $kat): ?>
                <option 
                    value="<?= safe($kat['nama_kategori']) ?>" 
                    <?= $kategori == $kat['nama_kategori'] ? 'selected' : '' ?>
                >
                    <?= safe($kat['nama_kategori']) ?>
                </option>
            <?php endforeach; ?>
            <option value="Lainnya" <?= $kategori == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
        </select>

        <select name="status">
            <option value="">Semua Status</option>
            <?php foreach ([
                'Belum Selesai',
                'Menunggu Verifikasi Humas',
                'Menunggu Verifikasi Masyarakat',
                'Selesai Final',
                'Perlu Perbaikan Pegawai',
                'Ditolak'
            ] as $s): ?>
                <option value="<?= $s ?>" <?= $status == $s ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filter</button>
        <a href="/masyarakat/laporan_saya.php" class="btn gray">Reset</a>
        <a href="/masyarakat/tambah_laporan.php" class="btn green">+ Baru</a>
    </form>
</div>

<div class="card">
    <table>
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Jumlah Titik</th>
            <th>Status</th>
            <th>Prioritas</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($data as $laporan): ?>
        <tr>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['wilayah']) ?></td>
            <td><?= safe($laporan['jumlah_titik']) ?></td>

            <td>
                <?php
                $statusFinal = $laporan['status_final'] ?? 'Belum Selesai';

                $classStatus = 'status-menunggu';
                $icon = '⏳';

                if ($statusFinal == 'Belum Selesai') {
                    $classStatus = 'status-proses';
                    $icon = '🛠️';
                } elseif ($statusFinal == 'Menunggu Verifikasi Humas') {
                    $classStatus = 'status-verifikasi';
                    $icon = '📋';
                } elseif ($statusFinal == 'Menunggu Verifikasi Masyarakat') {
                    $classStatus = 'status-verifikasi';
                    $icon = '🏠';
                } elseif ($statusFinal == 'Selesai Final') {
                    $classStatus = 'status-selesai';
                    $icon = '✅';
                } elseif ($statusFinal == 'Perlu Perbaikan Pegawai') {
                    $classStatus = 'status-perbaikan';
                    $icon = '🔧';
                } elseif ($statusFinal == 'Ditolak') {
                    $classStatus = 'status-ditolak';
                    $icon = '❌';
                }
                ?>

                <div class="status-badge <?= $classStatus ?>">
                    <span><?= $icon ?></span>
                    <span><?= safe($statusFinal) ?></span>
                </div>
            </td>

            <td><?= safe($laporan['prioritas']) ?></td>

            <td class="actions">
                <a 
                    class="btn gray" 
                    href="/masyarakat/detail_laporan.php?id=<?= $laporan['_id'] ?>"
                >
                    Detail
                </a>

                <?php if (($laporan['status_humas'] ?? '') == 'Menunggu Validasi'): ?>
                    <a 
                        class="btn orange" 
                        href="/masyarakat/edit_laporan.php?id=<?= $laporan['_id'] ?>"
                    >
                        Edit
                    </a>

                    <a 
                        class="btn red" 
                        onclick="return confirm('Yakin hapus laporan?')" 
                        href="/masyarakat/hapus_laporan.php?id=<?= $laporan['_id'] ?>"
                    >
                        Hapus
                    </a>
                <?php endif; ?>

                <button 
                    type="button" 
                    class="btn green"
                    onclick='bukaTracking(
                        <?= json_encode((string)$laporan["_id"]) ?>,
                        <?= json_encode($laporan["latitude_tujuan"] ?? "") ?>,
                        <?= json_encode($laporan["longitude_tujuan"] ?? "") ?>
                    )'
                >
                    Lihat Tracking
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div id="trackingModal" class="tracking-modal">
    <div class="tracking-box">
        <div class="tracking-header">
            <h2>Live Tracking Pegawai</h2>
            <button onclick="tutupTracking()" class="btn red">Tutup</button>
        </div>

        <div class="live-badge">
            <div class="live-dot"></div>
            TRACKING REALTIME
        </div>

        <div id="mapTracking" style="height:520px;border-radius:22px;margin-top:18px;"></div>
        <div id="trackingInfo" class="tracking-card" style="margin-top:16px;"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

<script>
let mapTracking = null;
let pegawaiMarker = null;
let tujuanMarker = null;
let routingControl = null;
let fallbackLine = null;
let trackingInterval = null;

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

function bukaTracking(laporanId, tujuanLat, tujuanLng) {
    const tLat = parseFloat(tujuanLat);
    const tLng = parseFloat(tujuanLng);

    document.getElementById('trackingModal').style.display = 'flex';
    document.getElementById('trackingInfo').innerHTML = 'Memuat tracking...';

    if (!tLat || !tLng) {
        document.getElementById('trackingInfo').innerHTML = `
            <b>Lokasi tujuan belum tersedia.</b><br>
            Pastikan laporan dibuat dengan memilih titik lokasi di peta.
        `;
        return;
    }

    setTimeout(() => {
        if (trackingInterval) {
            clearInterval(trackingInterval);
        }

        if (mapTracking) {
            mapTracking.remove();
        }

        pegawaiMarker = null;
        tujuanMarker = null;
        routingControl = null;
        fallbackLine = null;

        mapTracking = L.map('mapTracking').setView([tLat, tLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(mapTracking);

        tujuanMarker = L.marker([tLat, tLng], {
            icon: destinationIcon
        }).addTo(mapTracking).bindPopup('Lokasi Laporan');

        loadTracking(laporanId, tLat, tLng);

        trackingInterval = setInterval(() => {
            loadTracking(laporanId, tLat, tLng);
        }, 3000);

    }, 300);
}

async function loadTracking(laporanId, tLat, tLng) {
    try {
        const response = await fetch('/masyarakat/tracking_data.php?id=' + encodeURIComponent(laporanId));
        const data = await response.json();

        if (!data.success) {
            document.getElementById('trackingInfo').innerHTML = `
                <b>Tracking belum tersedia.</b><br>
                ${data.message ?? 'Pegawai belum memulai perjalanan.'}
            `;
            return;
        }

        const lat = parseFloat(data.latitude);
        const lng = parseFloat(data.longitude);

        if (!lat || !lng) {
            document.getElementById('trackingInfo').innerHTML = `
                <b>Lokasi pegawai belum masuk.</b><br>
                Pegawai harus membuka menu <b>Mulai Tracking</b> dan mengizinkan GPS.
            `;
            return;
        }

        if (!pegawaiMarker) {
            pegawaiMarker = L.marker([lat, lng], {
                icon: vehicleIcon
            }).addTo(mapTracking).bindPopup('Pegawai Lapangan');
        } else {
            pegawaiMarker.setLatLng([lat, lng]);
        }

        if (routingControl) {
            mapTracking.removeControl(routingControl);
            routingControl = null;
        }

        if (fallbackLine) {
            mapTracking.removeLayer(fallbackLine);
            fallbackLine = null;
        }

        routingControl = L.Routing.control({
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
                    weight: 7,
                    opacity: 0.9
                }]
            },
            createMarker: function() {
                return null;
            }
        }).addTo(mapTracking);

        routingControl.on('routesfound', function(e) {
            const route = e.routes[0];

            document.getElementById('trackingInfo').innerHTML = `
                <b>🏍️ ${data.pegawai_nama ?? 'Pegawai'} sedang menuju lokasi</b><br>
                Status: ${data.status_tracking ?? '-'}<br>
                Jarak rute: ${(route.summary.totalDistance / 1000).toFixed(2)} km<br>
                Estimasi waktu: ${Math.ceil(route.summary.totalTime / 60)} menit<br>
                Update terakhir: ${data.updated_at ?? '-'}
            `;
        });

        routingControl.on('routingerror', function() {
            if (fallbackLine) {
                mapTracking.removeLayer(fallbackLine);
            }

            fallbackLine = L.polyline(
                [
                    [lat, lng],
                    [tLat, tLng]
                ],
                {
                    color: '#ef4444',
                    weight: 5,
                    dashArray: '10,8'
                }
            ).addTo(mapTracking);

            document.getElementById('trackingInfo').innerHTML = `
                <b>Rute jalan gagal dimuat.</b><br>
                Ditampilkan garis cadangan. Pastikan koneksi internet aktif.
            `;
        });

    } catch (error) {
        document.getElementById('trackingInfo').innerHTML = `
            <b>Gagal mengambil data tracking.</b><br>
            Cek file <b>masyarakat/tracking_data.php</b> dan koneksi internet.
        `;
    }
}

function tutupTracking() {
    document.getElementById('trackingModal').style.display = 'none';

    if (trackingInterval) {
        clearInterval(trackingInterval);
        trackingInterval = null;
    }

    if (mapTracking) {
        mapTracking.remove();
        mapTracking = null;
    }

    pegawaiMarker = null;
    tujuanMarker = null;
    routingControl = null;
    fallbackLine = null;
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>