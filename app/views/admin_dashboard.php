<?php
session_start();
// Path ke file koneksi database, dari app/views/ ke config/
require_once '../../config/Database.php';

// Guard: Cek jika pengguna adalah Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../../Auth/login.php'); // Arahkan ke root login
    exit;
}

// Fungsi helper untuk escaping HTML
if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// -- PENGAMBILAN DATA DINAMIS DARI DATABASE --

// Inisialisasi variabel metrik dengan nilai default
$total_jenis_barang = 0;
$stok_menipis_count = 0;
$barang_masuk_hari_ini = 0;
$barang_keluar_hari_ini = 0;
$db_error = null;

// Variabel untuk chart baru
$chart_labels = [];
$chart_data_masuk = [];
$chart_data_keluar = [];

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Menghitung Total Jenis Barang (unik)
    $query1 = "SELECT COUNT(DISTINCT nama_barang) as total FROM barang";
    $stmt1 = $db->prepare($query1);
    $stmt1->execute();
    $total_jenis_barang = $stmt1->fetchColumn();

    // 2. Menghitung Barang dengan Stok Menipis (misal, stok <= 10)
    $stok_menipis_threshold = 10;
    $query2 = "SELECT COUNT(*) FROM (SELECT SUM(jumlah) as total_stok FROM barang GROUP BY nama_barang HAVING total_stok <= :threshold) as barang_menipis";
    $stmt2 = $db->prepare($query2);
    $stmt2->bindParam(':threshold', $stok_menipis_threshold, PDO::PARAM_INT);
    $stmt2->execute();
    $stok_menipis_count = $stmt2->fetchColumn();

    // 3. Menghitung total barang MASUK hari ini dari tabel transaksi
    $today = date('Y-m-d');
    $query3 = "SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE tipe = 'masuk' AND tanggal = :today";
    $stmt3 = $db->prepare($query3);
    $stmt3->bindParam(':today', $today);
    $stmt3->execute();
    $barang_masuk_hari_ini = $stmt3->fetchColumn();

    // 4. Menghitung total barang KELUAR hari ini dari tabel transaksi
    $query4 = "SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE tipe = 'keluar' AND tanggal = :today";
    $stmt4 = $db->prepare($query4);
    $stmt4->bindParam(':today', $today);
    $stmt4->execute();
    $barang_keluar_hari_ini = $stmt4->fetchColumn();

    // 5. MENGAMBIL DATA UNTUK GRAFIK BARU (7 Hari Terakhir)
    $query_chart = "
        SELECT
            tanggal,
            SUM(CASE WHEN tipe = 'masuk' THEN jumlah ELSE 0 END) as total_masuk,
            SUM(CASE WHEN tipe = 'keluar' THEN jumlah ELSE 0 END) as total_keluar
        FROM transaksi
        WHERE tanggal >= CURDATE() - INTERVAL 6 DAY
        GROUP BY tanggal
        ORDER BY tanggal ASC
    ";
    $stmt_chart = $db->prepare($query_chart);
    $stmt_chart->execute();
    $daily_traffic = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

    // Proses data untuk format Chart.js
    $period = new DatePeriod(
        new DateTime('-6 days'),
        new DateInterval('P1D'),
        new DateTime('+1 day')
    );

    $traffic_map = [];
    foreach($daily_traffic as $traffic) {
        $traffic_map[$traffic['tanggal']] = $traffic;
    }

    foreach ($period as $date) {
        $day = $date->format('Y-m-d');
        $chart_labels[] = $date->format('d M'); // Format '17 Jun'
        $chart_data_masuk[] = $traffic_map[$day]['total_masuk'] ?? 0;
        $chart_data_keluar[] = $traffic_map[$day]['total_keluar'] ?? 0;
    }

} catch (PDOException $e) {
    // Jika terjadi error koneksi atau query
    $db_error = "Error koneksi database: " . e($e->getMessage());
    // Set semua metrik ke 'N/A' jika ada error
    $total_jenis_barang = $stok_menipis_count = $barang_masuk_hari_ini = $barang_keluar_hari_ini = 'N/A';
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
                <button id="sidebar-toggle" class="text-gray-600 hover:text-gray-900 focus:outline-none bg-slate-200/70 hover:bg-slate-300 w-10 h-10 rounded-full flex items-center justify-center">
                    <i id="sidebar-toggle-icon" class="fas fa-chevron-left text-xl"></i>
                </button>
                <div class="relative">
                    <button id="profile-button" class="flex items-center space-x-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap']) ?>&background=4f46e5&color=fff&size=128" alt="Avatar" class="w-10 h-10 rounded-full border-2 border-slate-300">
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
                    <div class="mb-6">
                        <h1 class="text-3xl font-extrabold text-slate-800">Dashboard Admin</h1>
                        <p class="mt-1 text-slate-600">Ringkasan aktivitas inventaris terkini.</p>
                    </div>

                    <?php if ($db_error): ?>
                        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800" role="alert">
                            <strong>Error:</strong> <?= e($db_error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-gradient-to-br from-sky-500 to-sky-600 text-white p-6 rounded-2xl shadow-lg transition-transform hover:scale-105">
                            <div class="flex justify-between items-start">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium opacity-80">Total Jenis Barang</span>
                                    <span class="text-4xl font-extrabold mt-1"><?= e(number_format($total_jenis_barang)) ?></span>
                                </div>
                                <i class="fas fa-boxes-stacked fa-3x opacity-20"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white p-6 rounded-2xl shadow-lg transition-transform hover:scale-105">
                             <div class="flex justify-between items-start">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium opacity-80">Stok Menipis</span>
                                    <span class="text-4xl font-extrabold mt-1"><?= e(number_format($stok_menipis_count)) ?></span>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-3x opacity-20"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-2xl shadow-lg transition-transform hover:scale-105">
                             <div class="flex justify-between items-start">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium opacity-80">Masuk (Hari Ini)</span>
                                    <span class="text-4xl font-extrabold mt-1"><?= e(number_format($barang_masuk_hari_ini)) ?></span>
                                </div>
                                <i class="fas fa-arrow-down fa-3x opacity-20"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-6 rounded-2xl shadow-lg transition-transform hover:scale-105">
                             <div class="flex justify-between items-start">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium opacity-80">Keluar (Hari Ini)</span>
                                    <span class="text-4xl font-extrabold mt-1"><?= e(number_format($barang_keluar_hari_ini)) ?></span>
                                </div>
                                <i class="fas fa-arrow-up fa-3x opacity-20"></i>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        <div class="lg:col-span-8 bg-white p-6 rounded-2xl shadow-md">
                            <h3 class="text-lg font-bold text-slate-800 mb-4">Ringkasan (7 Hari Terakhir)</h3>
                            <div class="relative h-80">
                                <canvas id="trafficChart"></canvas>
                            </div>
                        </div>

                        <div class="lg:col-span-4 bg-white p-6 rounded-2xl shadow-md flex flex-col">
                            <h3 class="text-lg font-bold text-slate-800 mb-4">Akses Cepat</h3>
                            <nav class="flex-1 space-y-3">
                                <a href="../model/report_sales.php" class="flex items-center p-4 rounded-lg hover:bg-slate-100 transition-colors">
                                    <i class="fas fa-chart-line text-xl text-sky-500 w-8 text-center"></i>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-slate-700">Laporan</h4>
                                        <p class="text-xs text-slate-500">Lihat ringkasan penjualan & pembelian.</p>
                                    </div>
                                </a>
                                <a href="../model/item_list.php" class="flex items-center p-4 rounded-lg hover:bg-slate-100 transition-colors">
                                    <i class="fas fa-boxes-stacked text-xl text-teal-500 w-8 text-center"></i>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-slate-700">Daftar Barang</h4>
                                        <p class="text-xs text-slate-500">Analisa data master semua barang.</p>
                                    </div>
                                </a>
                                <a href="../model/trending_sales.php" class="flex items-center p-4 rounded-lg hover:bg-slate-100 transition-colors">
                                    <i class="fas fa-chart-bar text-xl text-indigo-500 w-8 text-center"></i>
                                     <div class="ml-4">
                                        <h4 class="font-semibold text-slate-700">Penjualan Terlaris</h4>
                                        <p class="text-xs text-slate-500">Analisa tren barang paling laku.</p>
                                    </div>
                                </a>
                                <a href="../model/transaction_history.php" class="flex items-center p-4 rounded-lg hover:bg-slate-100 transition-colors">
                                    <i class="fas fa-history text-xl text-purple-500 w-8 text-center"></i>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-slate-700">Riwayat Transaksi</h4>
                                        <p class="text-xs text-slate-500">Lacak semua aktivitas inventaris.</p>
                                    </div>
                                </a>
                                <a href="../model/manage_user.php" class="flex items-center p-4 rounded-lg hover:bg-slate-100 transition-colors">
                                    <i class="fas fa-users-cog text-xl text-slate-500 w-8 text-center"></i>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-slate-700">Manajemen Pengguna</h4>
                                        <p class="text-xs text-slate-500">Atur hak akses untuk setiap peran.</p>
                                    </div>
                                </a>
                            </nav>
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
            const ctx = document.getElementById('trafficChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar', // Tipe grafik bar
                    data: {
                        labels: <?= json_encode($chart_labels) ?>,
                        datasets: [{
                            label: 'Barang Masuk',
                            data: <?= json_encode($chart_data_masuk) ?>,
                            backgroundColor: 'rgba(34, 197, 94, 0.6)', // Warna hijau
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Barang Keluar',
                            data: <?= json_encode($chart_data_keluar) ?>,
                            backgroundColor: 'rgba(239, 68, 68, 0.6)', // Warna merah
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#e2e8f0' // Warna grid lebih soft
                                },
                                ticks: {
                                    color: '#64748b' // Warna teks sumbu Y
                                }
                            },
                            x: {
                                grid: {
                                    display: false // Hilangkan grid vertikal
                                },
                                ticks: {
                                    color: '#64748b' // Warna teks sumbu X
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>