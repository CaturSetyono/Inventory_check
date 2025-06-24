<?php
// File ini berasumsi BASE_URL sudah didefinisikan dari file konfigurasi
// dan session sudah dimulai di halaman pemanggil.

if (!defined('BASE_URL')) {
    // Definisikan BASE_URL jika belum ada. Sesuaikan jika perlu.
    // Asumsi file sidebar.php ada di 'app/components/'.
    // BASE_URL harus menunjuk ke root folder 'inventory_app/'.
    define('BASE_URL', '/inventory_app/');
}
if (!isset($currentPage)) {
    $currentPage = '';
}
if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
// Ambil role dari session untuk mempermudah
$userRole = $_SESSION['role'] ?? 'Guest'; // Default ke 'Guest' kalo session gak ada

?>
<aside id="sidebar" class="bg-slate-900 text-white w-64 flex-shrink-0 flex flex-col fixed inset-y-0 left-0 z-40 md:relative md:flex">
    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden"></div>

    <!-- Header Sidebar untuk Mobile -->
    <div class="flex justify-between items-center p-4 md:hidden border-b border-slate-700">
        <div class="flex items-center space-x-3">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap']) ?>&background=0ea5e9&color=fff&size=128" alt="Avatar" class="w-10 h-10 rounded-full">
            <div>
                <span class="font-semibold text-white text-sm"><?= e($_SESSION['nama_lengkap']) ?></span>
                <span class="block text-xs text-slate-400"><?= e($userRole) ?></span>
            </div>
        </div>
        <button id="close-sidebar-btn" class="text-slate-400 hover:text-white"><i class="fas fa-times text-2xl"></i></button>
    </div>

    <!-- Logo untuk Desktop -->
    <div id="logo-container" class="hidden md:flex items-center justify-center p-6 h-16 border-b border-slate-700"> 
        <span class="sidebar-text text-2xl font-bold ml-3">IKU Inc.</span>
    </div>

    <!-- Navigasi Utama -->
    <div class="flex-1 overflow-y-auto">
        <nav class="mt-6 space-y-2 px-2">
            <?php
            // Class untuk menandai menu aktif/tidak aktif
            $activeClass = "bg-slate-700/50 text-white";
            $inactiveClass = "text-slate-300 hover:bg-slate-700 hover:text-white";

            // --- KHUSUS MENU ADMIN ---
            if ($userRole === 'Admin'):
            ?>
                <a href="<?= BASE_URL ?>app/views/admin_dashboard.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'dashboard') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-tachometer-alt w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Dashboard</span>
                </a>
                <a href="<?= BASE_URL ?>app/controllers/item_list_controller.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'items') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-boxes-stacked w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Daftar Barang</span>
                </a>
                <a href="<?= BASE_URL ?>app/model/report_sales.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'reports') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-chart-line w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Laporan</span>
                </a>
                <a href="<?= BASE_URL ?>app/model/trending_sales.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'trending') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-chart-bar w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Penjualan Terlaris</span>
                </a>
                <a href="<?= BASE_URL ?>app/model/transaction_history.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'history') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-history w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Riwayat Transaksi</span>
                </a>
                <a href="<?= BASE_URL ?>app/model/manage_user.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'manage_user') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-users-cog w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Manajemen Pengguna</span>
                </a>
            <?php
            endif;
            ?>

            <?php
            // --- KHUSUS MENU PURCHASING ---
            if ($userRole === 'Purchasing'):
            ?>
                <a href="<?= BASE_URL ?>app/views/purchasing_dashboard.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'dashboard') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-tachometer-alt w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Dashboard</span>
                </a>
                <a href="<?= BASE_URL ?>app/model/add_purchasing.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'add_purchase') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-plus-circle w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Tambah Transaksi</span>
                </a>
                <a href="<?= BASE_URL ?>app/controllers/item_list_controller.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'items') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-boxes-stacked w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Daftar Barang</span>
                </a>
                <a href="<?= BASE_URL ?>app/model/transaction_history.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'history') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-history w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Riwayat Pembelian</span>
                </a>
            <?php
            endif;
            ?>

            <?php
            // --- KHUSUS MENU SALES (JIKA NANTI ADA) ---
            if ($userRole === 'Sales'):
            ?>
                <a href="<?= BASE_URL ?>app/views/sales_dashboard.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'dashboard') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-tachometer-alt w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Dashboard</span>
                </a>
                <a href="<?= BASE_URL ?>app/model/add_nota.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'invoice') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-file-invoice-dollar w-6 text-center text-lg"></i>
                    <span class="sidebar-text ml-4">Buat Nota</span>
                </a>
                <a href="<?= BASE_URL ?>app/model/transaction_history.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'history') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-history w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Riwayat Penjualan</span>
                </a>
                <a href="<?= BASE_URL ?>app/controllers/item_list_controller.php" class="nav-link flex items-center py-3 px-4 transition duration-200 rounded-lg <?= ($currentPage === 'items') ? $activeClass : $inactiveClass ?>">
                    <i class="fas fa-boxes-stacked w-6 text-center text-lg"></i><span class="sidebar-text ml-4">Daftar Barang</span>
                </a>
            <?php
            endif;
            ?>

        </nav>
    </div>

    <!-- Tombol Logout -->
    <div class="p-4 border-t border-slate-700">
        <button id="logout-button" type="button" class="logout-trigger w-full flex items-center justify-center bg-red-600/80 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-600 transition duration-200">
            <i class="fas fa-sign-out-alt w-6 text-center text-lg"></i><span class="sidebar-text ml-2">Logout</span>
        </button>
    </div>
</aside>