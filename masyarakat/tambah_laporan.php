<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Masyarakat']);

$kategoriList = $kategoriCollection->find(
    ['status' => 'Aktif'],
    ['sort' => ['nama_kategori' => 1]]
);

include __DIR__ . '/../partials/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<div class="page-header">
    <h1>Buat Laporan Baru</h1>
    <p>Klik titik lokasi di peta agar petugas dapat menemukan lokasi laporan dengan tepat.</p>
</div>

<div class="card">
    <form action="/masyarakat/simpan_laporan.php" method="POST" enctype="multipart/form-data">

        <div class="form-grid">
            <div>
                <label>Judul Laporan</label>
                <input 
                    type="text" 
                    name="judul" 
                    placeholder="Contoh: Jalan rusak di depan sekolah" 
                    required
                >
            </div>

            <div>
                <label>Kategori</label>
                <select name="kategori" required>
                    <?php foreach ($kategoriList as $kategori): ?>
                        <option value="<?= safe($kategori['nama_kategori']) ?>">
                            <?= safe($kategori['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <div>
                <label>Wilayah/Kecamatan</label>
                <input 
                    type="text" 
                    name="wilayah" 
                    placeholder="Contoh: Kecamatan Melati" 
                    required
                >
            </div>

            <div>
                <label>Jumlah Titik Masalah</label>
                <input 
                    type="number" 
                    name="jumlah_titik" 
                    min="1" 
                    placeholder="Contoh: 2" 
                    required
                >
            </div>

            <div>
                <label>Estimasi Dampak</label>
                <select name="dampak" required>
                    <option value="Rendah">Rendah</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Tinggi">Tinggi</option>
                </select>
            </div>

            <div>
                <label>Prioritas</label>
                <select name="prioritas" required>
                    <option value="Rendah">Rendah</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Tinggi">Tinggi</option>
                    <option value="Darurat">Darurat</option>
                </select>
            </div>
        </div>

        <label>Alamat Lengkap Lokasi</label>
        <textarea 
            name="alamat_lokasi" 
            rows="3" 
            placeholder="Contoh: Jl. Merdeka depan SD Melati, dekat minimarket..." 
            required
        ></textarea>

        <label>Pilih Titik Lokasi di Peta</label>
        <div class="map-helper">
            Klik titik lokasi masalah pada peta. Marker akan muncul otomatis.
        </div>

        <div id="map-picker" class="map-picker"></div>

        <input type="hidden" name="latitude_tujuan" id="latitude_tujuan" required>
        <input type="hidden" name="longitude_tujuan" id="longitude_tujuan" required>

        <label>Deskripsi Laporan</label>
        <textarea 
            name="deskripsi" 
            rows="5" 
            placeholder="Jelaskan kondisi masalah secara singkat dan jelas..." 
            required
        ></textarea>

        <label>Foto Bukti Laporan</label>
        <input type="file" name="foto" accept="image/*">

        <div style="margin-top:20px;">
            <button type="submit">Kirim Laporan</button>
            <a href="/masyarakat/dashboard.php" class="btn gray">Kembali</a>
        </div>

    </form>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
const map = L.map('map-picker').setView([-0.947083, 100.417181], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
}).addTo(map);

let marker = null;

map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;

    document.getElementById('latitude_tujuan').value = lat;
    document.getElementById('longitude_tujuan').value = lng;

    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng]).addTo(map);
    }

    marker.bindPopup("Lokasi laporan dipilih").openPopup();
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>