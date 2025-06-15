<?php
session_start();
// Menggunakan path yang sudah kita standarkan dan Anda instruksikan.
// Jika file ini di app/model/, path ke config adalah ../config/Database.php
// Jika Anda memindahkannya, sesuaikan path ini.
require_once '../../config/Database.php';

// Guard: Hanya bisa diakses oleh Sales atau Admin
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Sales', 'Admin'])) {
    header('Location: ../views/sales_dashboard.php');
    exit;
}

if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

$db_error = null;
$barang_list = [];
$harga_jual = null; // Gunakan null untuk pengecekan yang lebih baik
$submitted_data = []; // Untuk menyimpan input user

try {
    $database = new Database();
    $db = $database->getConnection();

    // Logic 1: Ambil semua barang unik untuk dropdown
    $stmt_barang = $db->prepare("SELECT MIN(id) as id, nama_barang FROM barang GROUP BY nama_barang ORDER BY nama_barang ASC");
    $stmt_barang->execute();
    $barang_list = $stmt_barang->fetchAll(PDO::FETCH_ASSOC);

    // Logic 2: Proses form jika di-submit (method POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $barang_id = filter_input(INPUT_POST, 'barang_id', FILTER_VALIDATE_INT);
        $jumlah_jual = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);
        $submitted_data = $_POST; // Simpan input

        if ($barang_id && $jumlah_jual) {
            // Cari nama_barang dari id yang dipilih
            $stmt_nama = $db->prepare("SELECT nama_barang FROM barang WHERE id = ?");
            $stmt_nama->execute([$barang_id]);
            $nama_barang = $stmt_nama->fetchColumn();

            if ($nama_barang) {
                // Logic 3: Ambil semua stok masuk untuk barang tersebut, urutkan FIFO
                $stmt_fifo = $db->prepare("SELECT id, jumlah, harga_beli FROM barang WHERE nama_barang = ? AND jumlah > 0 ORDER BY tanggal ASC, id ASC");
                $stmt_fifo->execute([$nama_barang]);
                $stok_tersedia = $stmt_fifo->fetchAll(PDO::FETCH_ASSOC);

                $sisa_jual = $jumlah_jual;
                $harga_total = 0;
                $stok_cukup = false;
                
                $total_stok_barang = array_sum(array_column($stok_tersedia, 'jumlah'));
                if ($jumlah_jual <= $total_stok_barang) {
                    $stok_cukup = true;
                    foreach ($stok_tersedia as $stok) {
                        if ($sisa_jual <= 0) break;
                        
                        $ambil_dari_batch = min($sisa_jual, $stok['jumlah']);
                        $harga_total += $ambil_dari_batch * $stok['harga_beli'];
                        $sisa_jual -= $ambil_dari_batch;
                    }
                }
                 $harga_jual = ['total' => $harga_total, 'stok_cukup' => $stok_cukup];
            }
        }
    }

} catch (PDOException $e) {
    $db_error = "Error Database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Harga Jual (FIFO) - InventoriKu</title>
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
        $currentPage = 'cek_harga'; // Sesuaikan dengan key di sidebar jika ada
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
                            <span class="font-semibold text-slate-800 text-sm"><?= e($_SESSION['nama_lengkap']) ?></span>
                            <span class="block text-xs text-slate-500"><?= e($_SESSION['role']) ?></span>
                        </div>
                    </button>
                    </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="container mx-auto max-w-2xl">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-800">Cek Harga Pokok Penjualan</h1>
                            <p class="mt-2 text-slate-600">Hitung HPP berdasarkan stok yang masuk lebih dulu (FIFO).</p>
                        </div>
                    </div>

                    <?php if ($db_error): ?>
                        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800" role="alert"><strong>Error:</strong> <?= e($db_error) ?></div>
                    <?php endif; ?>

                    <div class="bg-white p-8 rounded-lg shadow-md">
                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="barang_id" class="block text-sm font-medium text-slate-700 mb-1">Nama Barang</label>
                                <select name="barang_id" id="barang_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500 transition">
                                    <option value="">-- Pilih Barang --</option>
                                    <?php foreach ($barang_list as $b): ?>
                                        <option value="<?= $b['id'] ?>" <?= (isset($submitted_data['barang_id']) && $submitted_data['barang_id'] == $b['id']) ? 'selected' : '' ?>>
                                            <?= e($b['nama_barang']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="jumlah" class="block text-sm font-medium text-slate-700 mb-1">Jumlah Jual</label>
                                <input type="number" name="jumlah" id="jumlah" min="1" required value="<?= isset($submitted_data['jumlah']) ? e($submitted_data['jumlah']) : '' ?>" placeholder="Contoh: 50" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500 transition">
                            </div>
                            <div class="pt-4 border-t border-gray-200">
                                <button type="submit" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center">
                                    <i class="fas fa-calculator mr-2"></i> Hitung Harga Pokok
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php if ($harga_jual !== null): ?>
                        <div class="mt-8">
                            <?php if ($harga_jual['stok_cukup']): ?>
                                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-6 rounded-lg shadow-md">
                                    <h3 class="text-lg font-bold">Hasil Perhitungan HPP (FIFO)</h3>
                                    <p class="mt-2">Total Harga Pokok Penjualan untuk <strong class="font-semibold"><?= e($submitted_data['jumlah']) ?> unit</strong> adalah:</p>
                                    <p class="text-4xl font-bold mt-2">Rp <?= number_format($harga_jual['total'], 2, ',', '.') ?></p>
                                </div>
                            <?php else: ?>
                                 <div class="bg-amber-100 border-l-4 border-amber-500 text-amber-800 p-6 rounded-lg shadow-md">
                                    <h3 class="text-lg font-bold">Stok Tidak Mencukupi</h3>
                                    <p class="mt-2">Jumlah yang diminta (<?= e($submitted_data['jumlah']) ?>) melebihi total stok yang tersedia.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </main>
        </div>
    </div>
    <script src="../asset/lib/purchase.js"></script>
</body>
</html>