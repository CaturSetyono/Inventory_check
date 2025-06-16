<?php
// File ini akan mengambil data transaksi berdasarkan role pengguna dengan paginasi.

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

// --- AWAL PERUBAHAN PAGINASI ---

// 1. Konfigurasi Paginasi
$limit = 20; // Jumlah baris per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini, default ke 1
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit; // Hitung offset untuk query SQL

// --- AKHIR PERUBAHAN PAGINASI ---


try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn === null) {
        throw new Exception("Gagal terhubung ke database.");
    }

    // --- PERUBAHAN PAGINASI: Query untuk menghitung total baris ---
    // Query ini harus menggunakan filter (whereClause) yang sama dengan query utama
    $countSql = "SELECT COUNT(t.id) FROM transaksi t {$whereClause}";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $total_rows = $countStmt->fetchColumn();
    $total_pages = ceil($total_rows / $limit);
    // --- AKHIR PERUBAHAN PAGINASI ---

    // Bangun query SQL yang dinamis dengan LIMIT dan OFFSET
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
                t.tanggal DESC, t.id DESC
            LIMIT :limit OFFSET :offset"; // Tambahkan LIMIT dan OFFSET

    $stmt = $conn->prepare($sql);

    // --- PERUBAHAN PAGINASI: Bind parameter untuk LIMIT dan OFFSET ---
    // Kita perlu bind parameter secara manual karena execute() mungkin tidak menangani semua tipe data dengan benar
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
} finally {
    $conn = null;
}
