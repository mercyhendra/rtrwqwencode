<?php
/**
 * Script untuk insert sample data pengumuman
 * Akses via browser: http://localhost/rt-rw-system/insert_sample_pengumuman.php
 */

require_once __DIR__ . '/api/config.php';

echo "<h2>Insert Sample Data Pengumuman</h2>";
echo "<pre>";

try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n\n";
    
    // Check if data already exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pengumuman");
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "⚠ Tabel pengumuman sudah ada {$result['count']} data.\n";
        echo "  Kosongkan tabel dulu atau abaikan jika ingin tetap insert.\n\n";
    }
    
    // Sample data
    $sampleData = [
        [
            'judul' => 'Kerja Bakti Minggu Ini',
            'kategori' => 'umum',
            'isi' => "Diberitahukan kepada seluruh warga RT 001/RW 001 untuk mengikuti kerja bakti membersihkan selokan dan lingkungan sekitar.\n\n📅 Hari: Minggu, 18 Februari 2025\n⏰ Waktu: 07:00 WIB\n📍 Lokasi: Balai Warga",
            'tanggal_acara' => '2025-02-18 07:00:00',
            'lokasi_acara' => 'Balai Warga',
            'dibuat_oleh' => 1,
            'created_at' => '2025-02-15 10:30:00',
            'views' => 125
        ],
        [
            'judul' => 'Pembayaran Iuran Bulan Ini',
            'kategori' => 'penting',
            'isi' => "Diharapkan kepada seluruh warga untuk membayar iuran kebersihan dan keamanan bulan Februari 2025.\n\n💵 Jumlah: Rp 50.000\n📅 Batas: 25 Februari 2025\n📍 Pembayaran: Bendahara RT",
            'tanggal_acara' => '2025-02-25 23:59:59',
            'lokasi_acara' => 'Bendahara RT',
            'dibuat_oleh' => 1,
            'created_at' => '2025-02-14 14:00:00',
            'views' => 98
        ],
        [
            'judul' => 'Rapat RT Bulanan',
            'kategori' => 'warga',
            'isi' => "Undangan rapat bulanan untuk seluruh pengurus RT dan perwakilan warga.\n\n📅 Hari: Sabtu, 17 Februari 2025\n⏰ Waktu: 19:00 WIB\n📍 Lokasi: Rumah Pak RT",
            'tanggal_acara' => '2025-02-17 19:00:00',
            'lokasi_acara' => 'Rumah Pak RT',
            'dibuat_oleh' => 1,
            'created_at' => '2025-02-12 09:00:00',
            'views' => 76
        ],
        [
            'judul' => 'Waspada Demam Berdarah',
            'kategori' => 'darurat',
            'isi' => "Diberitahukan agar seluruh warga meningkatkan kewaspadaan terhadap penyakit demam berdarah.\n\n🔸 Lakukan 3M Plus\n🔸 Gunakan kelambu\n🔸 Segera ke puskesmas jika ada gejala",
            'tanggal_acara' => null,
            'lokasi_acara' => null,
            'dibuat_oleh' => 1,
            'created_at' => '2025-02-10 16:45:00',
            'views' => 200
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO pengumuman (judul, kategori, isi, tanggal_acara, lokasi_acara, dibuat_oleh, created_at, views) 
        VALUES (:judul, :kategori, :isi, :tanggal_acara, :lokasi_acara, :dibuat_oleh, :created_at, :views)
    ");
    
    $inserted = 0;
    foreach ($sampleData as $data) {
        try {
            $stmt->execute($data);
            echo "✓ Insert: {$data['judul']}\n";
            $inserted++;
        } catch (PDOException $e) {
            echo "✗ Gagal insert '{$data['judul']}': {$e->getMessage()}\n";
        }
    }
    
    echo "\n";
    echo "=================================\n";
    echo "Selesai! {$inserted} data berhasil insert.\n";
    echo "=================================\n\n";
    
    // Verify
    $stmt = $pdo->query("SELECT id, judul, kategori, created_at FROM pengumuman ORDER BY created_at DESC");
    echo "Data saat ini:\n";
    while ($row = $stmt->fetch()) {
        echo "  [{$row['id']}] {$row['judul']} ({$row['kategori']}) - {$row['created_at']}\n";
    }
    
    echo "\n<a href='pengumuman.php'>Lihat Halaman Pengumuman</a>";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage();
}

echo "</pre>";
