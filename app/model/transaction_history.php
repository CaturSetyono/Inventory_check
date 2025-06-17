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
    <title><?= e($pageTitle) ?></title>
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
            <header class="bg-white p-4 h-16 flex justify-between items-center z-10">
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
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-800"><?= e($pageTitle) ?></h1>
                            <p class="mt-2 text-slate-600"><?= e($pageSubtitle) ?></p>
                        </div>

                    </div>

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 w-16 text-center">No.</th>
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
                                            <td colspan="7" class="px-6 py-4 text-center text-red-500"><?= e($error_message) ?></td>
                                        </tr>
                                    <?php elseif (empty($transactions)): ?>
                                        <tr class="bg-white border-b">
                                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada data transaksi untuk ditampilkan.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                        // AWAL PERUBAHAN: Inisialisasi nomor urut
                                        $nomor = (($page - 1) * $limit) + 1;
                                        ?>
                                        <?php foreach ($transactions as $trans): ?>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 font-medium text-gray-900 text-center"><?= $nomor++ ?></td>
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

                    <?php if ($total_pages > 1 && !$error_message && !empty($transactions)): ?>
                        <div class="mt-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0" aria-label="Pagination">
                            <div>
                                <p class="text-sm text-slate-600">
                                    Halaman <span class="font-semibold text-slate-800"><?= $page ?></span> dari <span class="font-semibold text-slate-800"><?= $total_pages ?></span>
                                </p>
                            </div>

                            <nav class="flex items-center space-x-2">
                                <a href="?page=<?= $page > 1 ? $page - 1 : 1 ?>"
                                    class="flex items-center justify-center h-9 w-9 rounded-md bg-white text-slate-500 transition-colors border border-slate-300 hover:bg-slate-100 hover:text-slate-700 <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                                    <span class="sr-only">Previous</span>
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </a>

                                <?php
                                $window = 1; // Jumlah halaman di sekitar halaman aktif
                                for ($i = 1; $i <= $total_pages; $i++):
                                    // Kondisi untuk menampilkan nomor halaman atau elipsis
                                    if ($i == 1 || $i == $total_pages || ($i >= $page - $window && $i <= $page + $window)) {
                                        // Tampilkan link halaman
                                        echo '<a href="?page=' . $i . '" aria-current="' . ($i == $page ? 'page' : 'false') . '" 
                    class="flex items-center justify-center h-9 min-w-[2.25rem] px-3 rounded-md font-semibold text-sm transition-all ' .
                                            ($i == $page
                                                ? 'bg-sky-500 text-white shadow-md'
                                                : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-100 hover:border-slate-400') . '">
                    ' . $i . '
                </a>';
                                    } elseif ($i == $page - $window - 1 || $i == $page + $window + 1) {
                                        // Tampilkan elipsis
                                        echo '<span class="flex items-center justify-center h-9 w-9 text-slate-500">...</span>';
                                    }
                                endfor;
                                ?>

                                <a href="?page=<?= $page < $total_pages ? $page + 1 : $total_pages ?>"
                                    class="flex items-center justify-center h-9 w-9 rounded-md bg-white text-slate-500 transition-colors border border-slate-300 hover:bg-slate-100 hover:text-slate-700 <?= $page >= $total_pages ? 'opacity-50 pointer-events-none' : '' ?>">
                                    <span class="sr-only">Next</span>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            </nav>
                        </div>
                    <?php endif; ?>
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