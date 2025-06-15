<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Panggil controller yang sudah dinamis
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
    <title><?= e($pageTitle) ?> - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
</head>
<body class="bg-slate-100">

    <div class="relative min-h-screen md:flex">
        <?php
        $currentPage = 'history';
        require_once '../components/sidebar.php';
        ?>

        <div id="main-content" class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm p-4 h-16 flex justify-between items-center z-10">
                </header>

            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="container mx-auto">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-800"><?= e($pageTitle) ?></h1>
                            <p class="mt-2 text-slate-600"><?= e($pageSubtitle) ?></p>
                        </div>
                        <a href="../views/purchasing_dashboard.php" class="bg-slate-200 text-slate-600 hover:bg-slate-300 font-semibold py-2 px-4 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Tanggal</th>
                                        <th scope="col" class="px-6 py-3">Nama Barang</th>
                                        <th scope="col" class="px-6 py-3 text-center">Tipe</th>
                                        <th scope="col" class="px-6 py-3 text-center">Jumlah</th>
                                        <th scope="col" class="px-6 py-3 text-right">Harga Satuan</th>
                                        <th scope="col" class="px-6 py-3 text-right">Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($error_message): ?>
                                        <tr class="bg-white border-b">
                                            <td colspan="6" class="px-6 py-4 text-center text-red-500"><?= e($error_message) ?></td>
                                        </tr>
                                    <?php elseif (empty($transactions)): ?>
                                        <tr class="bg-white border-b">
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data transaksi untuk ditampilkan.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $trans): ?>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 font-medium text-gray-900"><?= e(date('d M Y', strtotime($trans['tanggal']))) ?></td>
                                                <td class="px-6 py-4"><?= e($trans['nama_barang']) ?></td>
                                                <td class="px-6 py-4 text-center">
                                                    <?php if ($trans['tipe'] == 'masuk'): ?>
                                                        <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full text-xs">Masuk</span>
                                                    <?php else: ?>
                                                        <span class="px-2 py-1 font-semibold leading-tight text-orange-700 bg-orange-100 rounded-full text-xs">Keluar</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 text-center"><?= e(number_format($trans['jumlah'])) ?></td>
                                                <td class="px-6 py-4 text-right">Rp <?= e(number_format($trans['harga'], 0, ',', '.')) ?></td>
                                                <td class="px-6 py-4 text-right font-semibold">Rp <?= e(number_format($trans['total_harga'], 0, ',', '.')) ?></td>
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