<?php
session_start();
require_once '../../config/Database.php';

// Guard & Helper Functions
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Sales', 'Admin'])) {
    header('Location: ../../Auth/login.php');
    exit;
}
if (!isset($_SESSION['nama_lengkap'])) {
    $_SESSION['nama_lengkap'] = 'Sales Person';
    $_SESSION['role'] = 'Sales';
}
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// --- PENGAMBILAN DATA DINAMIS DARI DATABASE ---
$db_error = null;
try {
    $database = new Database();
    $db = $database->getConnection();
    $today = date('Y-m-d');
    $start_of_week = date('Y-m-d', strtotime('monday this week'));
    $start_of_month = date('Y-m-01');

    // 1. Metrik Kinerja Penjualan (Harian, Mingguan, Bulanan)
    $query_harian = "SELECT COALESCE(SUM(harga), 0) FROM transaksi WHERE tipe = 'keluar' AND tanggal = :today";
    $stmt_harian = $db->prepare($query_harian);
    $stmt_harian->bindParam(':today', $today);
    $stmt_harian->execute();
    $penjualan_harian = $stmt_harian->fetchColumn();

    $query_mingguan = "SELECT COALESCE(SUM(harga), 0) FROM transaksi WHERE tipe = 'keluar' AND tanggal >= :start_week";
    $stmt_mingguan = $db->prepare($query_mingguan);
    $stmt_mingguan->bindParam(':start_week', $start_of_week);
    $stmt_mingguan->execute();
    $penjualan_mingguan = $stmt_mingguan->fetchColumn();

    $query_bulanan = "SELECT COALESCE(SUM(harga), 0) FROM transaksi WHERE tipe = 'keluar' AND tanggal >= :start_month";
    $stmt_bulanan = $db->prepare($query_bulanan);
    $stmt_bulanan->bindParam(':start_month', $start_of_month);
    $stmt_bulanan->execute();
    $penjualan_bulanan = $stmt_bulanan->fetchColumn();
    
    // 2. Data untuk Riwayat Transaksi Terakhir (diperbanyak menjadi 7)
    $query_recent = "SELECT t.id, b.nama_barang, t.jumlah, t.harga, t.tanggal 
                     FROM transaksi t
                     JOIN barang b ON t.barang_id = b.id
                     WHERE t.tipe = 'keluar'
                     ORDER BY t.tanggal DESC, t.id DESC
                     LIMIT 7";
    $stmt_recent = $db->prepare($query_recent);
    $stmt_recent->execute();
    $recent_transactions = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $db_error = "Error koneksi database: " . $e->getMessage();
}

// Placeholder untuk target
$target_penjualan_bulanan = 50000000;
$persentase_target = ($target_penjualan_bulanan > 0) ? ($penjualan_bulanan / $target_penjualan_bulanan) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
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
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap']) ?>&background=0ea5e9&color=fff&size=128" alt="Avatar" class="w-10 h-10 rounded-full border-2 border-slate-300">
                        <div class="hidden md:block text-right">
                            <span class="font-semibold text-slate-800 text-sm"><?= e($_SESSION['nama_lengkap']) ?></span>
                            <span class="block text-xs text-slate-500"><?= e($_SESSION['role']) ?></span>
                        </div>
                    </button>
                    </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="container mx-auto">
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-800">Sales Dashboard</h1>
                            <p class="mt-2 text-slate-600">Ringkasan performa penjualan dan aktivitas terkini.</p>
                        </div>
                        <div>
                            <a href="<?= BASE_URL ?>app/model/add_invoice.php" class="bg-sky-500 text-white font-bold py-3 px-5 rounded-lg shadow-md hover:bg-sky-600 transition-all transform hover:-translate-y-0.5 flex items-center">
                                <i class="fas fa-plus mr-2"></i>Buat Invoice Baru
                            </a>
                        </div>
                    </div>

                    <?php if ($db_error): ?>
                        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800" role="alert"><strong>Error:</strong> <?= e($db_error) ?></div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-md transition-all hover:shadow-lg hover:-translate-y-1">
                            <div class="flex items-start justify-between">
                                <p class="text-sm font-medium text-slate-500">Penjualan Hari Ini</p>
                                <i class="fas fa-calendar-day text-xl text-slate-400"></i>
                            </div>
                            <p class="text-3xl font-bold text-green-600 mt-2">Rp <?= number_format($penjualan_harian, 0, ',', '.') ?></p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md transition-all hover:shadow-lg hover:-translate-y-1">
                             <div class="flex items-start justify-between">
                                <p class="text-sm font-medium text-slate-500">Penjualan Minggu Ini</p>
                                <i class="fas fa-calendar-week text-xl text-slate-400"></i>
                            </div>
                            <p class="text-3xl font-bold text-slate-800 mt-2">Rp <?= number_format($penjualan_mingguan, 0, ',', '.') ?></p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md transition-all hover:shadow-lg hover:-translate-y-1">
                             <div class="flex items-start justify-between">
                                <p class="text-sm font-medium text-slate-500">Penjualan Bulan Ini</p>
                                <i class="fas fa-calendar-alt text-xl text-slate-400"></i>
                            </div>
                            <p class="text-3xl font-bold text-slate-800 mt-2">Rp <?= number_format($penjualan_bulanan, 0, ',', '.') ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-6">

                        <div class="col-span-12 lg:col-span-8">
                            <div class="bg-white p-6 rounded-lg shadow-md">
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
                                                <tr>
                                                    <td colspan="3" class="text-center py-10 text-slate-500">
                                                        <i class="fas fa-receipt fa-3x mb-3"></i>
                                                        <p>Belum ada transaksi penjualan.</p>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach($recent_transactions as $tx): ?>
                                                <tr class="border-t border-slate-100">
                                                    <td class="py-4 px-4 flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-sky-100 text-sky-600 rounded-lg font-bold">
                                                            <?= substr($tx['nama_barang'], 0, 1) ?>
                                                        </div>
                                                        <div class="ml-3">
                                                            <p class="font-semibold text-slate-800"><?= e($tx['nama_barang']) ?></p>
                                                            <p class="text-slate-500"><?= e($tx['jumlah']) ?> unit</p>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-4 text-right font-semibold text-green-600">Rp <?= number_format($tx['harga'], 0, ',', '.') ?></td>
                                                    <td class="py-4 px-4 text-center text-slate-500"><?= date('d M Y', strtotime($tx['tanggal'])) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="font-bold text-lg text-slate-800 mb-2">Target Penjualan Bulan Ini</h3>
                                <p class="text-sm text-slate-500 mb-3">Tercapai: <span class="font-bold">Rp <?= number_format($penjualan_bulanan) ?></span> dari <span class="font-bold">Rp <?= number_format($target_penjualan_bulanan) ?></span></p>
                                <div class="w-full bg-slate-200 rounded-full h-4">
                                    <div class="bg-gradient-to-r from-sky-400 to-sky-600 h-4 rounded-full text-center text-white text-xs font-bold flex items-center justify-center" style="width: <?= min($persentase_target, 100) ?>%">
                                       <span><?= round($persentase_target) ?>%</span>
                                    </div>
                                </div>
                            </div>

                            <a href="<?= BASE_URL ?>app/model/cek_harga.php" class="bg-white p-6 rounded-lg shadow-md transition-all hover:shadow-lg hover:border-sky-500 border-2 border-transparent flex items-start gap-4">
                                <i class="fas fa-tags text-3xl text-sky-500 mt-1"></i>
                                <div>
                                    <h3 class="font-bold text-lg text-slate-800">Cek Harga Jual</h3>
                                    <p class="text-sm text-slate-500 mt-1">Gunakan metode FIFO untuk menghitung harga pokok penjualan (HPP) barang.</p>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../asset/lib/purchase.js"></script>
</body>
</html>