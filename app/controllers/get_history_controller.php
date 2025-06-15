<?php
// File ini akan mengambil data transaksi berdasarkan role pengguna.

require_once '../../config/Database.php';

// Ambil role dari session. Default ke 'Guest' jika tidak ada.
$userRole = $_SESSION['role'] ?? 'Guest';

// Siapkan variabel untuk query dan judul halaman
$whereClause = "";
$params = [];
$pageTitle = "Riwayat Transaksi";
$pageSubtitle = "Semua catatan transaksi yang terekam dalam sistem.";

// Tentukan filter query dan judul berdasarkan role
if ($userRole === 'Purchasing') {
    $whereClause = "WHERE t.tipe = :tipe";
    $params[':tipe'] = 'masuk';
    $pageTitle = "Riwayat Pembelian";
    $pageSubtitle = "Semua catatan pembelian barang yang masuk ke inventaris.";
} elseif ($userRole === 'Sales') {
    $whereClause = "WHERE t.tipe = :tipe";
    $params[':tipe'] = 'keluar';
    $pageTitle = "Riwayat Penjualan";
    $pageSubtitle = "Semua catatan penjualan barang yang keluar dari inventaris.";
}
// Untuk Admin, $whereClause tetap kosong, jadi semua tipe transaksi akan diambil.

$transactions = [];
$error_message = null;

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn === null) {
        throw new Exception("Gagal terhubung ke database.");
    }

    // Bangun query SQL yang dinamis
    $sql = "SELECT 
                t.tanggal, 
                b.nama_barang, 
                t.jumlah, 
                t.harga,
                t.tipe, -- Ambil kolom tipe untuk ditampilkan
                (t.jumlah * t.harga) AS total_harga
            FROM 
                transaksi t
            JOIN 
                barang b ON t.barang_id = b.id
            {$whereClause} -- Sisipkan klausa WHERE dinamis di sini
            ORDER BY 
                t.tanggal DESC, t.id DESC";

    $stmt = $conn->prepare($sql);
    // Jalankan dengan parameter yang sesuai (jika ada)
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
} finally {
    $conn = null;
}