<?php
session_start();
require_once '../../config/Database.php';

// --- PENGATURAN PENGGUNA & FUNGSI HELPER ---
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Sales', 'Admin'])) {
    header('Location: ../../Auth/login.php');
    exit;
}
if (!isset($_SESSION['nama_lengkap'])) { // Mock data untuk development
    $_SESSION['nama_lengkap'] = 'Sales Person';
    $_SESSION['role'] = 'Sales';
}
if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// --- LOGIKA PENGAMBILAN DATA ---
$db_error = null;
$stats = [
    'penjualan_harian' => 0,
    'transaksi_harian' => 0,
    'penjualan_mingguan' => 0,
    'penjualan_bulanan' => 0,
    'avg_transaksi' => 0,
];
$recent_transactions = [];
$top_product_today = null;
$chart_labels = [];
$chart_values = [];

try {
    $database = new Database();
    $db = $database->getConnection();
    $today = date('Y-m-d');
    $start_of_week = date('Y-m-d', strtotime('monday this week'));
    $start_of_month = date('Y-m-01');

    // 1. QUERY EFISIEN: Ambil beberapa metrik penjualan sekaligus
    $query_stats = "
        SELECT
            COALESCE(SUM(CASE WHEN tanggal = :today THEN (jumlah * harga) ELSE 0 END), 0) as penjualan_harian,
            COALESCE(SUM(CASE WHEN tanggal = :today THEN 1 ELSE 0 END), 0) as transaksi_harian,
            COALESCE(SUM(CASE WHEN tanggal >= :start_week THEN (jumlah * harga) ELSE 0 END), 0) as penjualan_mingguan,
            COALESCE(SUM(CASE WHEN tanggal >= :start_month THEN (jumlah * harga) ELSE 0 END), 0) as penjualan_bulanan
        FROM transaksi
        WHERE tipe = 'keluar' AND tanggal >= :start_month
    ";
    $stmt_stats = $db->prepare($query_stats);
    $stmt_stats->execute([
        ':today' => $today,
        ':start_week' => $start_of_week,
        ':start_month' => $start_of_month
    ]);
    $stats_result = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    if ($stats_result) {
        $stats = $stats_result;
        $stats['avg_transaksi'] = $stats['transaksi_harian'] > 0 ? $stats['penjualan_harian'] / $stats['transaksi_harian'] : 0;
    }

    // 2. Data untuk Riwayat Transaksi Terakhir (LIMIT 5)
    // Query ini dipastikan menggunakan LIMIT 5 sesuai permintaan
    $query_recent = "SELECT b.nama_barang, t.jumlah, (t.jumlah * t.harga) as total_harga, t.tanggal
                     FROM transaksi t JOIN barang b ON t.barang_id = b.id
                     WHERE t.tipe = 'keluar' ORDER BY t.tanggal DESC, t.id DESC LIMIT 5";
    $stmt_recent = $db->prepare($query_recent);
    $stmt_recent->execute();
    $recent_transactions = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

    // 3. Data untuk Produk Terlaris Hari Ini
    $query_top_product = "
        SELECT b.nama_barang, SUM(t.jumlah) as total_terjual
        FROM transaksi t JOIN barang b ON t.barang_id = b.id
        WHERE t.tipe = 'keluar' AND t.tanggal = :today
        GROUP BY b.nama_barang ORDER BY total_terjual DESC LIMIT 1
    ";
    $stmt_top_product = $db->prepare($query_top_product);
    $stmt_top_product->execute([':today' => $today]);
    $top_product_today = $stmt_top_product->fetch(PDO::FETCH_ASSOC);
    
    // 4. Data untuk Grafik Tren Penjualan 7 Hari Terakhir
    $query_chart = "
        SELECT DATE(tanggal) as tgl, SUM(jumlah * harga) as total
        FROM transaksi WHERE tipe = 'keluar' AND tanggal >= CURDATE() - INTERVAL 6 DAY
        GROUP BY tgl ORDER BY tgl ASC
    ";
    $stmt_chart = $db->prepare($query_chart);
    $stmt_chart->execute();
    $sales_data_raw = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

    // Proses data chart
    $sales_map = [];
    foreach($sales_data_raw as $data) { $sales_map[$data['tgl']] = $data['total']; }
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chart_labels[] = date('d M', strtotime($date));
        $chart_values[] = (float)($sales_map[$date] ?? 0);
    }

} catch (PDOException $e) {
    $db_error = "Error koneksi database: " . $e->getMessage();
}

// PERBAIKAN LOGIKA TARGET
$target_penjualan_bulanan = 15000000; // Placeholder target
$persentase_target = ($target_penjualan_bulanan > 0) ? ($stats['penjualan_bulanan'] / $target_penjualan_bulanan) * 100 : 0;
// Hitung sisa untuk mencapai target
$sisa_target = $target_penjualan_bulanan - $stats['penjualan_bulanan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-slate-100 font-sans">
    <div class="relative min-h-screen md:flex">
        <?php
        $currentPage = 'dashboard';
        include '../components/sidebar.php';
        ?>
        <div id="main-content" class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm p-4 h-16 flex justify-between items-center z-10">
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="text-gray-600 hover:text-gray-900 focus:outline-none bg-slate-200/70 hover:bg-slate-300 w-10 h-10 rounded-full items-center justify-center hidden md:flex">
                        <i id="sidebar-toggle-icon" class="fas fa-chevron-left text-xl"></i>
                    </button>
                    <button id="mobile-menu-button" class="text-gray-600 hover:text-gray-900 focus:outline-none bg-slate-200/70 hover:bg-slate-300 w-10 h-10 rounded-full flex items-center justify-center md:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
                <div class="relative">
                    <button id="profile-button" class="flex items-center space-x-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap']) ?>&background=10b981&color=fff&size=128" alt="Avatar" class="w-10 h-10 rounded-full border-2 border-slate-300">
                        <div class="hidden md:block text-right">
                            <span class="font-semibold text-slate-800 text-sm"><?= e($_SESSION['nama_lengkap']) ?></span>
                            <span class="block text-xs text-slate-500"><?= e($_SESSION['role']) ?></span>
                        </div>
                    </button>
                     <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20">
                        <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"><i class="fas fa-user-circle w-5 mr-2"></i>Profil</a>
                        <button type="button" class="logout-trigger block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-slate-100">
                            <i class="fas fa-sign-out-alt w-5 mr-2"></i>Logout
                        </button>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <div class="container mx-auto">
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                        <div>
                            <h1 class="text-3xl font-extrabold text-slate-800">Sales Dashboard</h1>
                            <p class="mt-1 text-slate-600">Performa penjualan dan aktivitas terkini.</p>
                        </div>
                        <div>
                            <a href="../model/add_nota.php" class="bg-sky-500 text-white font-bold py-3 px-5 rounded-lg shadow-md hover:bg-sky-600 transition-all transform hover:-translate-y-0.5 flex items-center">
                                <i class="fas fa-plus mr-2"></i>Buat Nota Baru
                            </a>
                        </div>
                    </div>

                    <?php if ($db_error): ?>
                        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800" role="alert"><strong>Error:</strong> <?= e($db_error) ?></div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
                        <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white p-6 rounded-2xl shadow-lg transition-transform hover:scale-105">
                            <p class="text-sm opacity-80">Penjualan Hari Ini</p>
                            <p class="text-3xl font-bold mt-1">Rp <?= number_format($stats['penjualan_harian'], 0, ',', '.') ?></p>
                            <p class="text-xs opacity-70 mt-2"><?= e($stats['transaksi_harian']) ?> Transaksi</p>
                        </div>
                        <div class="bg-gradient-to-br from-sky-500 to-blue-600 text-white p-6 rounded-2xl shadow-lg transition-transform hover:scale-105">
                            <p class="text-sm opacity-80">Rata-rata Transaksi (Hari ini)</p>
                            <p class="text-3xl font-bold mt-1">Rp <?= number_format($stats['avg_transaksi'], 0, ',', '.') ?></p>
                            <p class="text-xs opacity-70 mt-2">per penjualan</p>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-6 rounded-2xl shadow-lg transition-transform hover:scale-105">
                            <p class="text-sm opacity-80">Penjualan Minggu Ini</p>
                            <p class="text-3xl font-bold mt-1">Rp <?= number_format($stats['penjualan_mingguan'], 0, ',', '.') ?></p>
                             <p class="text-xs opacity-70 mt-2">Sejak <?= date('d M', strtotime($start_of_week)) ?></p>
                        </div>
                        <div class="bg-gradient-to-br from-slate-700 to-gray-800 text-white p-6 rounded-2xl shadow-lg transition-transform hover:scale-105">
                            <p class="text-sm opacity-80">Penjualan Bulan Ini</p>
                            <p class="text-3xl font-bold mt-1">Rp <?= number_format($stats['penjualan_bulanan'], 0, ',', '.') ?></p>
                            <p class="text-xs opacity-70 mt-2">Target: Rp <?= number_format($target_penjualan_bulanan, 0, ',', '.') ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-6">
                        <div class="col-span-12 lg:col-span-8 bg-white p-6 rounded-2xl shadow-md">
                            <h3 class="font-bold text-lg text-slate-800 mb-4">Tren Penjualan (7 Hari Terakhir)</h3>
                            <div class="relative h-80">
                                <canvas id="salesTrendChart"></canvas>
                            </div>
                        </div>

                        <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
                            <div class="bg-white p-6 rounded-2xl shadow-md">
                                <h3 class="font-bold text-lg text-slate-800 mb-2">Progres Target Bulanan</h3>
                                <div class="w-full bg-slate-200 rounded-full h-5 mb-2">
                                    <div class="bg-gradient-to-r from-sky-400 to-emerald-500 h-5 rounded-full text-center text-white text-xs font-bold flex items-center justify-center shadow-lg" style="width: <?= min($persentase_target, 100) ?>%">
                                       <span><?= round($persentase_target) ?>%</span>
                                    </div>
                                </div>
                                <?php if ($sisa_target > 0): ?>
                                    <p class="text-sm text-center text-slate-500">
                                        Butuh <span class="font-bold text-slate-700">Rp <?= number_format($sisa_target, 0, ',', '.') ?></span> lagi untuk capai target.
                                    </p>
                                <?php else: ?>
                                    <p class="text-sm text-center text-green-600 font-semibold">
                                        🎉 Target terlampaui!
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="bg-white p-6 rounded-2xl shadow-md">
                                <h3 class="font-bold text-lg text-slate-800 mb-2">Produk Terlaris Hari Ini</h3>
                                <?php if($top_product_today): ?>
                                <div class="flex items-center">
                                    <div class="bg-amber-100 text-amber-600 rounded-lg p-3">
                                        <i class="fas fa-trophy fa-2x"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-semibold text-slate-800"><?= e($top_product_today['nama_barang']) ?></p>
                                        <p class="text-slate-500"><?= e($top_product_today['total_terjual']) ?> unit terjual</p>
                                    </div>
                                </div>
                                <?php else: ?>
                                <p class="text-sm text-slate-500 italic">Belum ada penjualan hari ini.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-span-12 bg-white p-6 rounded-2xl shadow-md">
                            <h3 class="font-bold text-lg text-slate-800 mb-4">Aktivitas Penjualan Terakhir</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="text-xs text-slate-500 uppercase border-b-2 border-slate-100">
                                        <tr>
                                            <th class="py-3 px-4 text-left">Detail Barang</th>
                                            <th class="py-3 px-4 text-right">Total Penjualan</th>
                                            <th class="py-3 px-4 text-center">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($recent_transactions)): ?>
                                            <tr><td colspan="3" class="text-center py-10 text-slate-500"><i class="fas fa-receipt fa-2x mb-2"></i><p>Belum ada transaksi.</p></td></tr>
                                        <?php else: ?>
                                            <?php foreach($recent_transactions as $tx): ?>
                                            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                                                <td class="py-4 px-4 flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-sky-100 text-sky-600 rounded-lg font-bold text-lg"><?= substr(e($tx['nama_barang']), 0, 1) ?></div>
                                                    <div class="ml-3">
                                                        <p class="font-semibold text-slate-800"><?= e($tx['nama_barang']) ?></p>
                                                        <p class="text-slate-500"><?= e($tx['jumlah']) ?> unit</p>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-4 text-right font-semibold text-green-600">+Rp <?= number_format($tx['total_harga'], 0, ',', '.') ?></td>
                                                <td class="py-4 px-4 text-center text-slate-500"><?= date('d M Y', strtotime($tx['tanggal'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>
     <div id="logout-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-sm mx-auto text-center transform transition-all scale-95 opacity-0" id="logout-modal-content">
            <div class="mb-4"><i class="fas fa-exclamation-triangle text-5xl text-yellow-400"></i></div>
            <h3 class="text-2xl font-bold text-gray-800">Anda Yakin?</h3>
            <p class="text-gray-600 my-2">Apakah Anda benar-benar ingin keluar dari sesi ini?</p>
            <div class="mt-6 flex justify-center space-x-4">
                <button id="cancel-logout-btn" class="bg-slate-300 hover:bg-slate-400 text-slate-800 font-bold py-2 px-6 rounded-lg transition-colors">Batal</button>
                <a href="../../Auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg transition-colors">Yakin, Keluar</a>
            </div>
        </div>
    </div>

    <script src="../asset/lib/purchase.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesTrendChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($chart_labels) ?>,
                        datasets: [{
                            label: 'Penjualan',
                            data: <?= json_encode($chart_values) ?>,
                            borderColor: '#0ea5e9', // sky-500
                            backgroundColor: (context) => {
                                const chart = context.chart;
                                const {ctx, chartArea} = chart;
                                if (!chartArea) return null;
                                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                                gradient.addColorStop(0, 'rgba(14, 165, 233, 0)');
                                gradient.addColorStop(1, 'rgba(14, 165, 233, 0.4)');
                                return gradient;
                            },
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#0ea5e9',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: '#0ea5e9'
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: (value) => 'Rp ' + new Intl.NumberFormat('id-ID', {notation: 'compact'}).format(value) } },
                            x: { grid: { display: false } }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        tooltips: {
                            callbacks: {
                                label: (context) => 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw)
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>