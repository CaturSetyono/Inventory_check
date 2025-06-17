<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/Database.php';

// Guard: Hanya Admin yang bisa mengakses halaman ini
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../Auth/login.php');
    exit;
}

// Fungsi helper untuk escaping HTML
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$db_error = null;
$trending_items = [];
$period_label = 'Seluruh Waktu'; // Default label periode
$filter_period = $_GET['period'] ?? 'all_time'; // Default filter

try {
    $database = new Database();
    $db = $database->getConnection();

    // Logika filter periode
    $date_clause = "";
    $params = [];

    switch ($filter_period) {
        case 'last_7_days':
            $date_clause = "AND t.tanggal >= CURDATE() - INTERVAL 7 DAY";
            $period_label = '7 Hari Terakhir';
            break;
        case 'this_month':
            $date_clause = "AND MONTH(t.tanggal) = MONTH(CURDATE()) AND YEAR(t.tanggal) = YEAR(CURDATE())";
            $period_label = 'Bulan Ini';
            break;
        case 'this_year':
            $date_clause = "AND YEAR(t.tanggal) = YEAR(CURDATE())";
            $period_label = 'Tahun Ini';
            break;
        case 'all_time':
        default:
            $date_clause = ""; // Tidak ada filter tanggal
            $period_label = 'Seluruh Waktu';
            break;
    }

    // --- MODIFIKASI QUERY DI SINI ---
    // Kita akan LEFT JOIN dengan tabel barang, tapi GROUP BY nama_barang
    // karena transaksi mereferensi ke ID barang per batch.
    // Kita perlu pastikan nama_barang yang di-JOIN itu adalah nama barang dari batch yang terkait transaksi.
    $query = "SELECT
                b.nama_barang,
                SUM(t.jumlah) as total_terjual,
                SUM(t.harga * t.jumlah) as total_revenue_from_transaction_price -- Menggunakan harga dari transaksi, bukan harga_beli dari barang
              FROM
                transaksi t
              JOIN
                barang b ON t.barang_id = b.id -- Join ke tabel barang untuk mendapatkan nama_barang dari ID batch
              WHERE
                t.tipe = 'keluar'
                {$date_clause}
              GROUP BY
                b.nama_barang -- GROUP BY nama_barang, BUKAN b.id, agar semua batch dengan nama sama terakumulasi
              ORDER BY
                total_terjual DESC, total_revenue_from_transaction_price DESC
              LIMIT 10"; // Ambil 10 barang terlaris

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $trending_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $db_error = "Error database: " . $e->getMessage();
}

// Terakhir, panggil view-nya
require_once '../model/trending_sales.php';