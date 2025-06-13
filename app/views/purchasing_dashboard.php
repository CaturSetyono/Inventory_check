<?php
session_start();
require_once '../../config/Database.php';

// -- PENGATURAN PENGGUNA & PERAN --
/*
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Purchasing', 'Admin'])) {
    header('Location: ../Auth/login.php'); 
    exit;
}
*/

// Mock session data
if (!isset($_SESSION['nama_lengkap'])) {
    $_SESSION['nama_lengkap'] = 'Staff Purchasing';
    $_SESSION['role'] = 'Purchasing';
}

function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// === LOGIKA PENGAMBILAN DATA UNTUK DASHBOARD ===
$stats = [
    'total_pembelian_bulan_ini' => 0,
    'jumlah_transaksi_bulan_ini' => 0,
    'item_unik_dibeli_bulan_ini' => 0,
];
// Variabel $recentTransactions tidak diperlukan lagi
$lowStockItems = [];
$chartData = [];

try {
    $database = new Database();
    $conn = $database->getConnection();

    // 1. Ambil data untuk Stat Cards (Bulan Ini)
    $sql_stats = "SELECT 
                    SUM(jumlah * harga) AS total_pembelian,
                    COUNT(*) AS jumlah_transaksi,
                    COUNT(DISTINCT barang_id) AS item_unik
                FROM transaksi 
                WHERE tipe = 'masuk' AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
    $stmt_stats = $conn->prepare($sql_stats);
    $stmt_stats->execute();
    $result_stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    if ($result_stats) {
        $stats['total_pembelian_bulan_ini'] = $result_stats['total_pembelian'] ?? 0;
        $stats['jumlah_transaksi_bulan_ini'] = $result_stats['jumlah_transaksi'] ?? 0;
        $stats['item_unik_dibeli_bulan_ini'] = $result_stats['item_unik'] ?? 0;
    }

    // 2. Ambil 5 Barang dengan Stok Terendah (misal, di bawah 10)
    $sql_low_stock = "SELECT nama_barang, jumlah FROM barang WHERE jumlah <= 10 ORDER BY jumlah ASC LIMIT 5";
    $stmt_low_stock = $conn->prepare($sql_low_stock);
    $stmt_low_stock->execute();
    $lowStockItems = $stmt_low_stock->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Ambil data untuk Grafik (7 hari terakhir)
    $sql_chart = "SELECT 
                    DATE(tanggal) as tgl, 
                    SUM(jumlah * harga) as total 
                FROM transaksi 
                WHERE tipe = 'masuk' AND tanggal >= CURDATE() - INTERVAL 7 DAY 
                GROUP BY DATE(tanggal)
                ORDER BY tgl ASC";
    $stmt_chart = $conn->prepare($sql_chart);
    $stmt_chart->execute();
    $chartData = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

    // Format data untuk Chart.js
    $chartLabels = [];
    $chartValues = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chartLabels[] = date('d M', strtotime($date));
        $chartValues[$date] = 0;
    }
    foreach ($chartData as $data) {
        if (isset($chartValues[$data['tgl']])) {
            $chartValues[$data['tgl']] = (float)$data['total'];
        }
    }
    $chartValuesJSON = json_encode(array_values($chartValues));
    $chartLabelsJSON = json_encode($chartLabels);

} catch (Exception $e) {
    $error_message = "Gagal mengambil data dashboard: " . $e->getMessage();
} finally {
    $conn = null;
}

setlocale(LC_TIME, 'id_ID.utf8', 'id_ID.UTF-8', 'id_ID', 'IND.utf8', 'IND', 'Indonesian');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Purchasing - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
</head>
<body class="bg-slate-100">

    <div class="relative min-h-screen md:flex">
        <?php 
        $currentPage = 'dashboard';
        include '../components/sidebar.php'; 
        ?>

        <div id="main-content" class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm p-4 h-16 flex justify-between items-center z-10">
                <button id="sidebar-toggle" class="text-gray-600 hover:text-gray-900 focus:outline-none bg-slate-200/70 hover:bg-slate-300 w-10 h-10 rounded-full flex items-center justify-center">
                    <i id="sidebar-toggle-icon" class="fas fa-chevron-left text-xl"></i>
                </button>
                <div class="relative">
                    <button id="profile-button" class="flex items-center space-x-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap']) ?>&background=0ea5e9&color=fff&size=128" alt="Avatar" class="w-10 h-10 rounded-full border-2 border-slate-300">
                        <div class="hidden md:block text-right">
                            <span class="font-semibold text-gray-800 text-sm"><?= e($_SESSION['nama_lengkap']) ?></span>
                            <span class="block text-xs text-gray-500"><?= e($_SESSION['role']) ?></span>
                        </div>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="container mx-auto">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-slate-800">Halo, <?= e(explode(' ', $_SESSION['nama_lengkap'])[0]) ?>!</h1>
                        <p class="mt-1 text-slate-600">Hari ini, <?= strftime('%A, %d %B %Y') ?>.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-2xl shadow-md flex items-center space-x-4 transition-transform duration-300 hover:scale-105 hover:shadow-lg">
                            <div class="bg-sky-100 p-4 rounded-xl"><i class="fas fa-wallet text-2xl text-sky-500"></i></div>
                            <div>
                                <p class="text-sm text-slate-500">Pembelian Bulan Ini</p>
                                <p class="text-2xl font-bold text-slate-800">Rp <?= number_format($stats['total_pembelian_bulan_ini'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-md flex items-center space-x-4 transition-transform duration-300 hover:scale-105 hover:shadow-lg">
                            <div class="bg-teal-100 p-4 rounded-xl"><i class="fas fa-exchange-alt text-2xl text-teal-500"></i></div>
                            <div>
                                <p class="text-sm text-slate-500">Transaksi Bulan Ini</p>
                                <p class="text-2xl font-bold text-slate-800"><?= $stats['jumlah_transaksi_bulan_ini'] ?></p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-md flex items-center space-x-4 transition-transform duration-300 hover:scale-105 hover:shadow-lg">
                             <div class="bg-indigo-100 p-4 rounded-xl"><i class="fas fa-box text-2xl text-indigo-500"></i></div>
                            <div>
                                <p class="text-sm text-slate-500">Jenis Barang Dibeli</p>
                                <p class="text-2xl font-bold text-slate-800"><?= $stats['item_unik_dibeli_bulan_ini'] ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-2xl shadow-md mb-8">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4">Tren Pembelian (7 Hari Terakhir)</h3>
                        <div class="relative h-80 md:h-96">
                            <canvas id="purchaseChart"></canvas>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-white p-6 rounded-2xl shadow-md">
                            <h3 class="text-lg font-semibold text-slate-800 mb-4">Peringatan Stok Menipis</h3>
                            <ul class="space-y-3">
                                <?php if(empty($lowStockItems)): ?>
                                    <li class="text-center text-slate-500 py-4">Stok aman!</li>
                                <?php else: ?>
                                    <?php foreach($lowStockItems as $item): ?>
                                    <li class="flex justify-between items-center">
                                        <span class="text-slate-700"><?= e($item['nama_barang']) ?></span>
                                        <span class="font-bold text-red-500 bg-red-100 px-2 py-1 rounded-full text-xs"><?= e($item['jumlah']) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="bg-sky-500 p-6 rounded-2xl shadow-lg text-white transition-transform duration-300 hover:scale-105 flex flex-col justify-center">
                            <h3 class="text-lg font-semibold">Siap Mencatat?</h3>
                            <p class="mt-1 opacity-90">Mulai catat transaksi pembelian baru dari pemasok.</p>
                            <a href="../model/add_purchasing.php" class="inline-block mt-4 bg-white/20 hover:bg-white/30 font-semibold py-2 px-4 rounded-lg self-start">
                                Tambah Transaksi &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../asset/lib/purchase.js"></script>
    <script>
        const ctx = document.getElementById('purchaseChart');
        if (ctx) { 
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= $chartLabelsJSON ?? '[]' ?>,
                    datasets: [{
                        label: 'Total Pembelian',
                        data: <?= $chartValuesJSON ?? '[]' ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, /* ... tooltip ... */ },
                    scales: { /* ... scales ... */ }
                }
            });
        }
    </script>
</body>
</html>