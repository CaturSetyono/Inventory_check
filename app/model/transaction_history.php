<?php
session_start();
// Memanggil file konfigurasi untuk BASE_URL
require_once '../../config/Database.php';

// === PANGGIL LOGIKA DARI CONTROLLER ===
// File ini akan menyediakan variabel $transactions dan $error_message
require_once '../controllers/get_history_controller.php';

// Mock session data
if (!isset($_SESSION['nama_lengkap'])) {
    $_SESSION['nama_lengkap'] = 'Staff Purchasing';
    $_SESSION['role'] = 'Purchasing';
}

function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
</head>

<body class="bg-slate-100">

    <div class="relative min-h-screen md:flex">

        <?php
        $currentPage = 'history';
        // Path ke sidebar sudah benar
        require_once '../components/sidebar.php';
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
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-800">Riwayat Transaksi Pembelian</h1>
                            <p class="mt-2 text-slate-600">Semua catatan pembelian barang yang masuk ke inventaris.</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Tanggal</th>
                                        <th scope="col" class="px-6 py-3">Nama Barang</th>
                                        <th scope="col" class="px-6 py-3 text-center">Jumlah</th>
                                        <th scope="col" class="px-6 py-3 text-right">Harga Satuan</th>
                                        <th scope="col" class="px-6 py-3 text-right">Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($error_message): ?>
                                        <tr class="bg-white border-b">
                                            <td colspan="5" class="px-6 py-4 text-center text-red-500">
                                                <?= e($error_message) ?>
                                            </td>
                                        </tr>
                                    <?php elseif (empty($transactions)): ?>
                                        <tr class="bg-white border-b">
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                                Belum ada riwayat transaksi pembelian.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $trans): ?>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 font-medium text-gray-900">
                                                    <?= e(date('d M Y', strtotime($trans['tanggal']))) ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?= e($trans['nama_barang']) ?>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <?= e(number_format($trans['jumlah'])) ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    Rp <?= e(number_format($trans['harga'], 0, ',', '.')) ?>
                                                </td>
                                                <td class="px-6 py-4 text-right font-semibold">
                                                    Rp <?= e(number_format($trans['total_harga'], 0, ',', '.')) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>