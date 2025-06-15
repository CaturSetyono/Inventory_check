<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../config/Database.php';

// Guard: Hanya untuk Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../views/Auth/login.php');
    exit;
}
function e($string) { return htmlspecialchars($string, ENT_QUOTES, 'UTF-8'); }

// -- LOGIKA PAGINATION --
$limit = 20; // Jumlah item per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

$items = [];
$total_items = 0;
$db_error = null;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Hitung total barang
    $total_items = $conn->query("SELECT COUNT(id) FROM barang")->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    // Ambil data barang dengan limit & offset
    $query = "SELECT id, nama_barang, jumlah, harga_beli, tanggal FROM barang ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $db_error = "Gagal mengambil data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Stok (Hal. <?= $page ?>) - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
    <style>.editable:hover { background-color: #eff6ff; cursor: pointer; }</style>
</head>
<body class="bg-slate-100">
<div class="relative min-h-screen">
    <?php
    $currentPage = 'manage_stock'; 
    include '../components/sidebar.php';
    ?>
    <div id="main-content" class="flex-1 flex flex-col min-h-screen md:ml-64">
        <header class="bg-white shadow-sm p-4 h-16 flex justify-between items-center z-10">
            </header>
        <main class="flex-1 overflow-y-auto p-6 md:p-8">
            <div class="container mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-slate-800">Manajemen Stok Barang</h1>
                    <p class="mt-2 text-slate-600">Klik pada kolom untuk edit langsung. Tekan Enter atau klik di luar untuk menyimpan.</p>
                </div>
                <div class="bg-white rounded-lg shadow-md">
                    <div class="overflow-x-auto">
                        <table id="stock-table" class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama Barang</th>
                                    <th scope="col" class="px-6 py-3 text-center">Jumlah Stok</th>
                                    <th scope="col" class="px-6 py-3 text-right">Harga Beli</th>
                                    <th scope="col" class="px-6 py-3 text-center">Update Terakhir</th>
                                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr class="bg-white border-b hover:bg-gray-50" data-id="<?= e($item['id']) ?>">
                                    <td class="editable px-6 py-4 font-semibold text-gray-900" data-field="nama_barang"><?= e($item['nama_barang']) ?></td>
                                    <td class="editable px-6 py-4 text-center" data-field="jumlah"><?= e($item['jumlah']) ?></td>
                                    <td class="editable px-6 py-4 text-right" data-field="harga_beli"><?= number_format($item['harga_beli'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4 text-center"><?= date('d M Y', strtotime($item['tanggal'])) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="delete-btn text-red-500 hover:text-red-700" data-id="<?= e($item['id']) ?>" data-name="<?= e($item['nama_barang']) ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($total_pages > 1): ?>
                    <nav class="flex items-center justify-between p-4" aria-label="Table navigation">
                        <span class="text-sm font-normal text-gray-500">Menampilkan <span class="font-semibold text-gray-900"><?= $offset + 1 ?>-<?= $offset + count($items) ?></span> dari <span class="font-semibold text-gray-900"><?= $total_items ?></span></span>
                        <ul class="inline-flex items-center -space-x-px">
                            </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="../asset/lib/purchase.js"></script>
</body>
</html>