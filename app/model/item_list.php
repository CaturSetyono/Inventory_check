<?php
session_start();
// Path ke Database.php saya sesuaikan agar konsisten dengan path lain di file ini.
require_once '../../config/Database.php'; 

// -- PENGATURAN PENGGUNA & PERAN --
// (Tidak ada perubahan di sini)
/*
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Purchasing', 'Admin', 'Sales'])) {
    header('Location: ../views/Auth/login.php'); 
    exit;
}
*/
if (!isset($_SESSION['nama_lengkap'])) {
    $_SESSION['nama_lengkap'] = 'Staff Gudang';
    $_SESSION['role'] = 'Purchasing';
}

function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// -- LOGIKA PAGINATION --
$limit = 30; // 1. Jumlah item per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit; // 2. Menghitung offset untuk query SQL

$items = [];
$total_items = 0;
$error_message = null;

try {
    $db = new Database();
    $conn = $db->getConnection();

    // 3. Query pertama: Hitung total semua barang untuk pagination
    $total_stmt = $conn->query("SELECT COUNT(id) FROM barang");
    $total_items = $total_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    // 4. Query kedua: Ambil data barang sesuai halaman saat ini (dengan LIMIT dan OFFSET)
    $query = "SELECT id, nama_barang, jumlah, harga_beli, tanggal 
              FROM barang 
              ORDER BY tanggal DESC, id DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Gagal mengambil data dari database: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang (Hal. <?= $page ?>) - InventoriKu</title>

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
        $currentPage = 'items';
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
                    <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100"><i class="fas fa-user-circle w-5 mr-2"></i>Profil</a>
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
                            <h1 class="text-3xl font-bold text-slate-800">Daftar Barang</h1>
                            <p class="mt-2 text-slate-600">Lihat semua data barang yang tersimpan di database.</p>
                        </div>
                        <a href="../model/add_purchasing.php" class="bg-sky-500 text-white hover:bg-sky-600 font-semibold py-2 px-4 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Tambah Barang
                        </a>
                    </div>

                    <div class="bg-white p-6 md:p-8 rounded-lg shadow-md overflow-x-auto">
                        <?php if ($error_message): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                                <p class="font-bold">Error</p>
                                <p><?= e($error_message) ?></p>
                            </div>
                        <?php else: ?>
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">No.</th>
                                        <th scope="col" class="px-6 py-3">ID</th>
                                        <th scope="col" class="px-6 py-3">Nama Barang</th>
                                        <th scope="col" class="px-6 py-3 text-center">Jumlah (Stok)</th>
                                        <th scope="col" class="px-6 py-3 text-right">Harga Beli</th>
                                        <th scope="col" class="px-6 py-3 text-center">Tanggal Masuk</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($items)): ?>
                                        <tr class="bg-white border-b">
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                Tidak ada data barang untuk ditampilkan.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php 
                                        // Nomor urut dimulai dari offset + 1
                                        $nomor = $offset + 1; 
                                        ?>
                                        <?php foreach ($items as $item): ?>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 font-medium text-gray-900"><?= $nomor++ ?></td>
                                                <td class="px-6 py-4 font-medium text-gray-900"><?= e($item['id']) ?></td>
                                                <th scope="row" class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                                    <?= e($item['nama_barang']) ?>
                                                </th>
                                                <td class="px-6 py-4 text-center"><?= e($item['jumlah']) ?></td>
                                                <td class="px-6 py-4 text-right">Rp <?= number_format($item['harga_beli'], 0, ',', '.') ?></td>
                                                <td class="px-6 py-4 text-center"><?= date_format(date_create($item['tanggal']), 'd M Y') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                        
                        <?php if ($total_pages > 1): ?>
                        <nav class="flex items-center justify-between pt-4" aria-label="Table navigation">
                            <span class="text-sm font-normal text-gray-500">
                                Menampilkan <span class="font-semibold text-gray-900"><?= $offset + 1 ?>-<?= $offset + count($items) ?></span> 
                                dari <span class="font-semibold text-gray-900"><?= $total_items ?></span>
                            </span>
                            <ul class="inline-flex items-center -space-x-px">
                                <li>
                                    <a href="?page=<?= $page > 1 ? $page - 1 : 1 ?>" 
                                       class="block px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 <?= ($page <= 1) ? 'cursor-not-allowed opacity-50' : '' ?>">
                                        <span class="sr-only">Previous</span>
                                        <i class="fas fa-chevron-left w-3 h-3"></i>
                                    </a>
                                </li>
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li>
                                    <a href="?page=<?= $i ?>" 
                                       class="px-3 py-2 leading-tight border border-gray-300 <?= ($i == $page) ? 'text-blue-600 bg-blue-50' : 'text-gray-500 bg-white hover:bg-gray-100' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                <li>
                                    <a href="?page=<?= $page < $total_pages ? $page + 1 : $total_pages ?>" 
                                       class="block px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 <?= ($page >= $total_pages) ? 'cursor-not-allowed opacity-50' : '' ?>">
                                        <span class="sr-only">Next</span>
                                        <i class="fas fa-chevron-right w-3 h-3"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
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