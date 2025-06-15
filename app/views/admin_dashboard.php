<?php
session_start();
// Path ke file koneksi database, dari app/views/ ke app/config/
require_once '../../config/Database.php';

// Guard: Cek jika pengguna adalah Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header('Location: Auth/login.php');
    exit;
}

// Fungsi helper untuk escaping HTML
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// -- PENGAMBILAN DATA DINAMIS DARI DATABASE --

// Inisialisasi variabel metrik dengan nilai default
$total_jenis_barang = 0;
$stok_menipis_count = 0;
$barang_masuk_hari_ini = 0;
$barang_keluar_hari_ini = 0;
$db_error = null;

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Menghitung Total Jenis Barang (unik)
    $query1 = "SELECT COUNT(DISTINCT nama_barang) as total FROM barang";
    $stmt1 = $db->prepare($query1);
    $stmt1->execute();
    $total_jenis_barang = $stmt1->fetchColumn();

    // 2. Menghitung Barang dengan Stok Menipis (misal, stok < 10)
    $stok_menipis_threshold = 10;
    // Query ini menjumlahkan stok untuk setiap nama barang, lalu menghitung berapa banyak yang totalnya di bawah ambang batas.
    $query2 = "SELECT COUNT(*) FROM (
                    SELECT SUM(jumlah) as total_stok 
                    FROM barang 
                    GROUP BY nama_barang 
                    HAVING total_stok < :threshold
               ) as barang_menipis";
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
} catch (PDOException $e) {
    // Jika terjadi error koneksi atau query
    $db_error = "Error koneksi database: " . $e->getMessage();
    // Set semua metrik ke 0 atau 'N/A' jika ada error
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
                    <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20">
                        <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"><i class="fas fa-user-circle w-5 mr-2"></i>Profil</a>
                        <button type="button" class="logout-trigger block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-slate-100">
                            <i class="fas fa-sign-out-alt w-5 mr-2"></i>Logout
                        </button>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="container mx-auto">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-slate-800">Selamat Datang Kembali, <?= e(explode(' ', $_SESSION['nama_lengkap'])[0]); ?>!</h1>
                        <p class="mt-2 text-slate-600">Berikut adalah ringkasan aktivitas inventaris Anda.</p>
                    </div>

                    <?php if ($db_error): ?>
                        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800" role="alert">
                            <strong>Error:</strong> <?= e($db_error) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Kartu Ringkasan dengan Data Dinamis -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between transition-all hover:shadow-lg hover:-translate-y-1">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Jenis Barang</p>
                                <p class="text-3xl font-bold text-slate-800"><?= number_format($total_jenis_barang) ?></p>
                            </div>
                            <div class="text-sky-500"><i class="fas fa-boxes-stacked fa-2x"></i></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between transition-all hover:shadow-lg hover:-translate-y-1">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Stok Menipis</p>
                                <p class="text-3xl font-bold text-amber-500"><?= number_format($stok_menipis_count) ?></p>
                            </div>
                            <div class="text-amber-500"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between transition-all hover:shadow-lg hover:-translate-y-1">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Barang Masuk (Hari Ini)</p>
                                <p class="text-3xl font-bold text-slate-800"><?= number_format($barang_masuk_hari_ini) ?></p>
                            </div>
                            <div class="text-green-500"><i class="fas fa-arrow-down fa-2x"></i></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between transition-all hover:shadow-lg hover:-translate-y-1">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Barang Keluar (Hari Ini)</p>
                                <p class="text-3xl font-bold text-slate-800"><?= number_format($barang_keluar_hari_ini) ?></p>
                            </div>
                            <div class="text-red-500"><i class="fas fa-arrow-up fa-2x"></i></div>
                        </div>
                    </div>

                    <!-- Akses Cepat (Path disesuaikan) -->
                    <h2 class="text-2xl font-bold text-slate-800 mb-6">Menu Akses Cepat</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                        <a href="#" class="block p-6 bg-white rounded-2xl shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-sky-500 border-2 border-transparent">
                            <div class="flex items-center mb-3 text-sky-500"><i class="fas fa-chart-line fa-2x"></i></div>
                            <h3 class="text-xl font-semibold text-slate-800">Laporan</h3>
                            <p class="text-slate-500 text-sm mt-1">Lihat laporan penjualan, pembelian, dan keuntungan.</p>
                        </a>

                        <a href="../model/item_list.php" class="block p-6 bg-white rounded-2xl shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-sky-500 border-2 border-transparent">
                            <div class="flex items-center mb-3 text-teal-500"><i class="fas fa-boxes-stacked fa-2x"></i></div>
                            <h3 class="text-xl font-semibold text-slate-800">Manajemen Barang</h3>
                            <p class="text-slate-500 text-sm mt-1">Kelola data master semua barang di inventaris.</p>
                        </a>

                        <a href="#" class="block p-6 bg-white rounded-2xl shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-sky-500 border-2 border-transparent">
                            <div class="flex items-center mb-3 text-indigo-500"><i class="fas fa-chart-bar fa-2x"></i></div>
                            <h3 class="text-xl font-semibold text-slate-800">Penjualan Terlaris</h3>
                            <p class="text-slate-500 text-sm mt-1">Analisa tren barang yang paling laku di pasaran.</p>
                        </a>

                        <a href="../model/transaction_history.php" class="block p-6 bg-white rounded-2xl shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-sky-500 border-2 border-transparent">
                            <div class="flex items-center mb-3 text-purple-500"><i class="fas fa-history fa-2x"></i></div>
                            <h3 class="text-xl font-semibold text-slate-800">Riwayat Transaksi</h3>
                            <p class="text-slate-500 text-sm mt-1">Lacak semua aktivitas pembelian dan penjualan.</p>
                        </a>

                        <a href="../model/manage_user.php" class="block p-6 bg-white rounded-2xl shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-sky-500 border-2 border-transparent">
                            <div class="flex items-center mb-3 text-slate-500"><i class="fas fa-users-cog fa-2x"></i></div>
                            <h3 class="text-xl font-semibold text-slate-800">Manajemen Pengguna</h3>
                            <p class="text-slate-500 text-sm mt-1">Atur hak akses dan akun untuk setiap peran.</p>
                        </a>
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

    <!-- Semua JS yang dibutuhkan sudah dihandle oleh sidebar dan halaman lain -->
    <script src="../asset/lib/purchase.js"></script>

</body>

</html>