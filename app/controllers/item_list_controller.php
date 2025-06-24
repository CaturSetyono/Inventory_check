<?php
// File: app/controllers/daftar_barang_controller.php

// Pastikan session selalu dimulai di awal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. MEMUAT DEPENDENSI & KONFIGURASI
require_once '../../config/Database.php'; 

// 2. FUNGSI BANTU (HELPERS)
// Fungsi untuk 'escaping' output HTML agar aman dari serangan XSS
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 3. LOGIKA UTAMA

// -- PENGATURAN PENGGUNA & PERAN --
// Ini adalah data simulasi, di lingkungan produksi, Anda akan menggunakan data sesi asli.
if (!isset($_SESSION['loggedin'])) {
    // Data dummy jika tidak ada sesi login
    $_SESSION['nama_lengkap'] = 'Staf Gudang';
    $_SESSION['role'] = 'Purchasing';
}

// -- LOGIKA PAGINATION & FILTER --
$limit = 30; // Jumlah item per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit; // Menghitung offset untuk query SQL

// -- PENGAMBILAN DATA DARI DATABASE --
$items = [];
$total_items = 0;
$total_pages = 0;
$error_message = null;

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Query pertama: Hitung total barang yang stoknya TIDAK NOL untuk pagination
    $total_stmt = $conn->query("SELECT COUNT(id) FROM barang WHERE jumlah > 0");
    $total_items = $total_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    // Pastikan halaman tidak melebihi total halaman yang ada
    if ($page > $total_pages && $total_pages > 0) {
        $page = $total_pages;
        $offset = ($page - 1) * $limit;
    }

    // Query kedua: Ambil data barang yang stoknya TIDAK NOL sesuai halaman saat ini
    $query = "SELECT id, nama_barang, jumlah, harga_beli, tanggal 
              FROM barang 
              WHERE jumlah > 0
              ORDER BY tanggal DESC, id DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Gagal mengambil data dari database: " . $e->getMessage();
}

// 4. MEMUAT FILE TAMPILAN (VIEW)
// Setelah semua logika selesai dan data siap, controller memanggil file view.
// Semua variabel di atas ($items, $page, dll.) akan tersedia di file view.
require_once '../model/item_list.php';