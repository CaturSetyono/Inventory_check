<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../../Auth/login.php');
    exit;
}
// Memanggil controller yang sudah kita buat
require_once '../controllers/trending_sales_controller.php';

// Pastikan variabel dari controller tersedia:
// $db_error, $trending_items, $period_label, $filter_period
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan Terlaris - InventoriKu</title>
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
        $currentPage = 'trending'; // Tandai halaman aktif di sidebar
        include '../components/sidebar.php'; // Memanggil sidebar
        ?>

        <div id="main-content" class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm p-4 h-16 flex justify-between items-center z-10">
                <button id="sidebar-toggle" class="text-gray-600 hover:text-gray-900 focus:outline-none bg-slate-200/70 hover:bg-slate-300 w-10 h-10 rounded-full flex items-center justify-center">
                    <i id="sidebar-toggle-icon" class="fas fa-chevron-left text-xl"></i>
                </button>
                <div class="relative">
                    <button id="profile-button" class="flex items-center space-x-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap'] ?? 'User') ?>&background=0ea5e9&color=fff&size=128" alt="Avatar" class="w-10 h-10 rounded-full border-2 border-slate-300">
                        <div class="hidden md:block text-right">
                            <span class="font-semibold text-slate-800 text-sm"><?= e($_SESSION['nama_lengkap'] ?? 'Pengguna') ?></span>
                            <span class="block text-xs text-slate-500"><?= e($_SESSION['role'] ?? 'Guest') ?></span>
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
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-800">Penjualan Terlaris</h1>
                            <p class="mt-2 text-slate-600">Daftar barang dengan penjualan terbanyak selama periode <span class="font-semibold"><?= e($period_label) ?></span>.</p>
                        </div>
                        <div class="flex gap-2 bg-white p-2 rounded-lg shadow-sm border border-slate-200">
                            <a href="?period=all_time" class="px-4 py-2 text-sm font-semibold rounded-md <?= ($filter_period === 'all_time' ? 'bg-sky-500 text-white shadow' : 'text-slate-600 hover:bg-slate-100') ?> transition">Seluruh Waktu</a>
                            <a href="?period=this_month" class="px-4 py-2 text-sm font-semibold rounded-md <?= ($filter_period === 'this_month' ? 'bg-sky-500 text-white shadow' : 'text-slate-600 hover:bg-slate-100') ?> transition">Bulan Ini</a>
                            <a href="?period=last_7_days" class="px-4 py-2 text-sm font-semibold rounded-md <?= ($filter_period === 'last_7_days' ? 'bg-sky-500 text-white shadow' : 'text-slate-600 hover:bg-slate-100') ?> transition">7 Hari Terakhir</a>
                            <a href="?period=this_year" class="px-4 py-2 text-sm font-semibold rounded-md <?= ($filter_period === 'this_year' ? 'bg-sky-500 text-white shadow' : 'text-slate-600 hover:bg-slate-100') ?> transition">Tahun Ini</a>
                        </div>
                    </div>

                    <?php if ($db_error): ?>
                        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800" role="alert">
                            <strong>Error:</strong> <?= e($db_error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white p-6 md:p-8 rounded-lg shadow-md overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Peringkat</th>
                                    <th scope="col" class="px-6 py-3">Nama Barang</th>
                                    <th scope="col" class="px-6 py-3 text-center">Total Terjual (Unit)</th>
                                    <th scope="col" class="px-6 py-3 text-right">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($trending_items)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-10 text-slate-500">
                                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                                            <p>Belum ada data penjualan terlaris untuk periode ini.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $rank = 1; ?>
                                    <?php foreach ($trending_items as $item): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-4 font-bold text-lg text-slate-800"><?= $rank++ ?>.</td>
                                            <th scope="row" class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                                <?= e($item['nama_barang']) ?>
                                            </th>
                                            <td class="px-6 py-4 text-center text-slate-700 font-medium"><?= e(number_format($item['total_terjual'])) ?></td>
                                            <td class="px-6 py-4 text-right text-green-600 font-bold">Rp <?= number_format($item['total_revenue_from_transaction_price'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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

</body>
</html>