<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once '../../config/Database.php';

// Guard: Hanya Admin yang bisa mengakses halaman laporan
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
$total_sales_value = 0;
$total_purchases_value = 0;
$sales_per_month = [];
$purchases_per_month = [];

try {
    $database = new Database();
    $db = $database->getConnection();

    // --- Data untuk Ringkasan Total Penjualan dan Pembelian ---
    $query_total_sales = "SELECT COALESCE(SUM(jumlah * harga), 0) FROM transaksi WHERE tipe = 'keluar'";
    $stmt_total_sales = $db->prepare($query_total_sales);
    $stmt_total_sales->execute();
    $total_sales_value = $stmt_total_sales->fetchColumn();

    $query_total_purchases = "SELECT COALESCE(SUM(jumlah * harga), 0) FROM transaksi WHERE tipe = 'masuk'";
    $stmt_total_purchases = $db->prepare($query_total_purchases);
    $stmt_total_purchases->execute();
    $total_purchases_value = $stmt_total_purchases->fetchColumn();

    // --- Data untuk Grafik Tren Penjualan per Bulan (12 Bulan Terakhir) ---
    // Penjualan
    $query_sales_month = "SELECT 
                            DATE_FORMAT(tanggal, '%Y-%m') as month,
                            SUM(jumlah * harga) as total_value
                          FROM transaksi
                          WHERE tipe = 'keluar'
                          GROUP BY month
                          ORDER BY month DESC
                          LIMIT 12"; // Ambil 12 bulan terakhir
    $stmt_sales_month = $db->prepare($query_sales_month);
    $stmt_sales_month->execute();
    $sales_data_raw = $stmt_sales_month->fetchAll(PDO::FETCH_ASSOC);

    // Pembelian
    $query_purchases_month = "SELECT 
                                DATE_FORMAT(tanggal, '%Y-%m') as month,
                                SUM(jumlah * harga) as total_value
                              FROM transaksi
                              WHERE tipe = 'masuk'
                              GROUP BY month
                              ORDER BY month DESC
                              LIMIT 12"; // Ambil 12 bulan terakhir
    $stmt_purchases_month = $db->prepare($query_purchases_month);
    $stmt_purchases_month->execute();
    $purchases_data_raw = $stmt_purchases_month->fetchAll(PDO::FETCH_ASSOC);

    // Format data untuk Chart.js (label bulan dan nilai)
    $all_months = [];
    $current_date = new DateTime();
    for ($i = 0; $i < 12; $i++) {
        $month_key = $current_date->format('Y-m');
        $all_months[$month_key] = 0; // Inisialisasi dengan 0
        $current_date->modify('-1 month');
    }
    $all_months = array_reverse($all_months); // Urutkan dari bulan terlama ke terbaru

    // Isi nilai penjualan
    foreach ($sales_data_raw as $data) {
        if (isset($all_months[$data['month']])) {
            $all_months[$data['month']] = (float)$data['total_value'];
        }
    }
    $sales_per_month['labels'] = json_encode(array_map(function($m) {
        return date('M Y', strtotime($m . '-01')); // Format: Jun 2024
    }, array_keys($all_months)));
    $sales_per_month['values'] = json_encode(array_values($all_months));

    // Reset dan isi nilai pembelian
    $all_months_purchases = [];
    $current_date = new DateTime();
    for ($i = 0; $i < 12; $i++) {
        $month_key = $current_date->format('Y-m');
        $all_months_purchases[$month_key] = 0;
        $current_date->modify('-1 month');
    }
    $all_months_purchases = array_reverse($all_months_purchases);

    foreach ($purchases_data_raw as $data) {
        if (isset($all_months_purchases[$data['month']])) {
            $all_months_purchases[$data['month']] = (float)$data['total_value'];
        }
    }
    $purchases_per_month['labels'] = json_encode(array_map(function($m) {
        return date('M Y', strtotime($m . '-01'));
    }, array_keys($all_months_purchases)));
    $purchases_per_month['values'] = json_encode(array_values($all_months_purchases));


} catch (PDOException $e) {
    $db_error = "Error koneksi database: " . $e->getMessage();
}

// Terakhir, panggil model-nya
require_once '../model/report_sales.php';