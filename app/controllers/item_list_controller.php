<?php
session_start();

// 1. MEMUAT DEPENDENSI & KONFIGURASI
// Path dari 'app/controllers/' ke 'config/' adalah '../../config/'
require_once '../../config/Database.php'; 

// 2. FUNGSI BANTU (HELPERS)
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 3. LOGIKA UTAMA (OTAK DARI HALAMAN)

// Pengaturan Pengguna & Peran (Guard)
// Aktifkan blok ini di lingkungan produksi
/*
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Purchasing', 'Admin', 'Sales'])) {
    header('Location: ../views/Auth/login.php'); 
    exit;
}
*/
if (!isset($_SESSION['nama_lengkap'])) {
    $_SESSION['nama_lengkap'] = 'Staff Gudang';
    $_SESSION['role'] = 'Purchasing';
}

// Logika Pagination
$limit = 30;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

// Variabel untuk menampung data yang akan dikirim ke View
$items = [];
$total_items = 0;
$total_pages = 0;
$error_message = null;

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Query untuk menghitung total semua barang
    $total_stmt = $conn->query("SELECT COUNT(id) FROM barang");
    $total_items = $total_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    // Query untuk mengambil data barang per halaman
    $query = "SELECT id, nama_barang, jumlah, harga_beli, tanggal 
              FROM barang 
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
// Setelah semua data siap, panggil file View-nya.
// Controller memberikan semua variabel yang sudah diolah ke View.
// Path dari 'app/controllers/' ke 'app/model/' adalah '../model/'
require_once '../model/item_list_view.php';