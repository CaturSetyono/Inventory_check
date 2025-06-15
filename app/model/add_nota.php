<?php
session_start();
// Menggunakan path yang sudah disimpan sebelumnya jika relevan.
// Pastikan path ini benar sesuai struktur direktori Anda.
require_once '../../config/Database.php'; 

// Guard: Hanya bisa diakses oleh Sales atau Admin
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Sales', 'Admin'])) {
    header('Location: ../views/login.php'); // Arahkan ke login jika tidak sesuai
    exit;
}

// Fungsi helper untuk escaping output HTML
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$db_error = null;
$notification = null; // Untuk notifikasi sukses atau error
$barang_list = [];
$hasil_perhitungan = null;
$submitted_data = [];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Ambil daftar barang unik untuk dropdown
    $stmt_barang = $db->prepare("SELECT MIN(id) as id, nama_barang FROM barang GROUP BY nama_barang ORDER BY nama_barang ASC");
    $stmt_barang->execute();
    $barang_list = $stmt_barang->fetchAll(PDO::FETCH_ASSOC);

    // Proses form jika di-submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

        // =================================================================
        // AKSI 1: MENGHITUNG HPP (FIFO)
        // =================================================================
        if ($action === 'calculate') {
            $barang_id = filter_input(INPUT_POST, 'barang_id', FILTER_VALIDATE_INT);
            $jumlah_jual = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);
            $submitted_data = $_POST;

            if ($barang_id && $jumlah_jual > 0) {
                // Ambil nama barang berdasarkan ID yang dipilih
                $stmt_nama = $db->prepare("SELECT nama_barang FROM barang WHERE id = ?");
                $stmt_nama->execute([$barang_id]);
                $nama_barang = $stmt_nama->fetchColumn();

                if ($nama_barang) {
                    // Ambil semua batch stok yang tersedia untuk barang ini, urutkan sesuai FIFO
                    $stmt_fifo = $db->prepare("SELECT id, jumlah, harga_beli FROM barang WHERE nama_barang = ? AND jumlah > 0 ORDER BY tanggal ASC, id ASC");
                    $stmt_fifo->execute([$nama_barang]);
                    $stok_tersedia = $stmt_fifo->fetchAll(PDO::FETCH_ASSOC);

                    $total_stok_barang = array_sum(array_column($stok_tersedia, 'jumlah'));

                    // Periksa apakah stok mencukupi
                    if ($jumlah_jual <= $total_stok_barang) {
                        $sisa_jual = $jumlah_jual;
                        $harga_total = 0;
                        $rincian_fifo = [];

                        foreach ($stok_tersedia as $stok) {
                            if ($sisa_jual <= 0) break;

                            $ambil_dari_batch = min($sisa_jual, $stok['jumlah']);
                            $harga_total += $ambil_dari_batch * $stok['harga_beli'];
                            $sisa_jual -= $ambil_dari_batch;
                            
                            // Simpan rincian pengambilan (termasuk ID batch untuk update nanti)
                            $rincian_fifo[] = [
                                'batch_id' => $stok['id'], 
                                'jumlah' => $ambil_dari_batch, 
                                'harga' => $stok['harga_beli']
                            ];
                        }
                        $hasil_perhitungan = ['stok_cukup' => true, 'total_hpp' => $harga_total, 'rincian' => $rincian_fifo, 'nama_barang' => $nama_barang];
                    } else {
                        $hasil_perhitungan = ['stok_cukup' => false, 'total_stok' => $total_stok_barang];
                    }
                }
            } else {
                 $notification = ['type' => 'error', 'message' => 'Harap pilih produk dan masukkan jumlah yang valid.'];
            }
        }

        // =================================================================
        // AKSI 2: PROSES PENJUALAN DAN UPDATE DATABASE
        // =================================================================
        elseif ($action === 'sell') {
            $barang_id = filter_input(INPUT_POST, 'barang_id', FILTER_VALIDATE_INT);
            $jumlah_total = filter_input(INPUT_POST, 'jumlah_total', FILTER_VALIDATE_INT);
            $rincian_json = $_POST['rincian_fifo'];
            $rincian_fifo = json_decode($rincian_json, true);

            if ($barang_id && $jumlah_total > 0 && !empty($rincian_fifo)) {
                $db->beginTransaction();
                try {
                    $tanggal_transaksi = date('Y-m-d');

                    // Loop melalui setiap batch yang digunakan dalam perhitungan FIFO
                    foreach ($rincian_fifo as $rincian) {
                        $batch_id = $rincian['batch_id'];
                        $jumlah_diambil = $rincian['jumlah'];
                        $harga_pokok = $rincian['harga'];

                        // 1. Update (kurangi) jumlah stok di tabel 'barang'
                        $stmt_update = $db->prepare("UPDATE barang SET jumlah = jumlah - ? WHERE id = ?");
                        $stmt_update->execute([$jumlah_diambil, $batch_id]);

                        // 2. Insert ke tabel 'transaksi' sebagai 'keluar'
                        $stmt_insert = $db->prepare(
                            "INSERT INTO transaksi (barang_id, jumlah, tipe, harga, tanggal) VALUES (?, ?, 'keluar', ?, ?)"
                        );
                        // Catatan: barang_id di sini bisa merujuk ke ID batch spesifik atau ID barang utama.
                        // Menggunakan $batch_id lebih akurat untuk traceability.
                        $stmt_insert->execute([$batch_id, $jumlah_diambil, $harga_pokok, $tanggal_transaksi]);
                    }

                    $db->commit();
                    $notification = ['type' => 'success', 'message' => "Transaksi berhasil! {$jumlah_total} unit barang telah terjual dan stok telah diperbarui."];

                } catch (Exception $ex) {
                    $db->rollBack();
                    $db_error = "Transaksi Gagal: " . $ex->getMessage();
                }
            } else {
                 $notification = ['type' => 'error', 'message' => 'Data transaksi tidak lengkap. Proses dibatalkan.'];
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
    <title>Kalkulator & Transaksi Penjualan - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
</head>

<body class="bg-slate-100 font-sans">
    <div class="relative min-h-screen md:flex">
        
        <?php
        $currentPage = 'cek_harga'; 
        require_once '../components/sidebar.php';
        ?>

        <div id="main-content" class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm p-4 h-16 flex justify-between items-center z-10">
                </header>

            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="container mx-auto">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-slate-800">Kalkulator & Transaksi Penjualan</h1>
                        <p class="mt-2 text-slate-600">Hitung HPP (FIFO) dan langsung proses transaksi penjualan.</p>
                    </div>

                    <?php if ($db_error): ?>
                        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800" role="alert"><strong>Error:</strong> <?= e($db_error) ?></div>
                    <?php endif; ?>
                    <?php if ($notification): ?>
                        <div class="mb-6 p-4 rounded-lg <?= $notification['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>" role="alert">
                            <?= e($notification['message']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">
                        
                        <div class="lg:col-span-2">
                            <div class="bg-white p-8 rounded-xl shadow-lg h-full">
                                <div class="flex items-center mb-6">
                                    <i class="fas fa-cash-register text-2xl text-sky-500"></i>
                                    <h2 class="text-xl font-bold text-slate-800 ml-3">Parameter Perhitungan</h2>
                                </div>
                                <form method="POST" class="space-y-6">
                                    <input type="hidden" name="action" value="calculate">
                                    
                                    <div>
                                        <label for="barang_id" class="block text-sm font-medium text-slate-700 mb-1">Pilih Produk</label>
                                        <select name="barang_id" id="barang_id" required class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                                            <option value="">-- Pilih Barang --</option>
                                            <?php foreach ($barang_list as $b): ?>
                                                <option value="<?= $b['id'] ?>" <?= (isset($submitted_data['barang_id']) && $submitted_data['barang_id'] == $b['id']) ? 'selected' : '' ?>>
                                                    <?= e($b['nama_barang']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="jumlah" class="block text-sm font-medium text-slate-700 mb-1">Jumlah Unit Terjual</label>
                                        <input type="number" name="jumlah" id="jumlah" min="1" required value="<?= isset($submitted_data['jumlah']) ? e($submitted_data['jumlah']) : '' ?>" placeholder="Contoh: 50" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                                    </div>
                                    <div class="pt-4">
                                        <button type="submit" class="w-full bg-gradient-to-r from-sky-500 to-sky-600 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105 hover:shadow-xl flex items-center justify-center">
                                            <i class="fas fa-calculator mr-2"></i> Hitung HPP
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="lg:col-span-3">
                            <div class="bg-white rounded-xl shadow-lg h-full">
                            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasil_perhitungan !== null): ?>
                                <?php if ($hasil_perhitungan['stok_cukup']): ?>
                                    <div class="bg-gradient-to-br from-sky-500 to-blue-600 p-6 rounded-t-xl text-white">
                                        <p class="text-sm opacity-80">Total Harga Pokok Penjualan (HPP)</p>
                                        <p class="text-5xl font-bold mt-1">Rp <?= number_format($hasil_perhitungan['total_hpp'], 2, ',', '.') ?></p>
                                        <p class="mt-2 text-sm opacity-90">Untuk penjualan <strong><?= e($submitted_data['jumlah']) ?> unit</strong> <?= e($hasil_perhitungan['nama_barang']) ?></p>
                                    </div>
                                    <div class="p-6">
                                        <h3 class="font-bold text-slate-800 mb-4">Rincian Pengambilan Stok (FIFO)</h3>
                                        <ul class="space-y-3 mb-6">
                                            <?php foreach($hasil_perhitungan['rincian'] as $rincian): ?>
                                            <li class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <i class="fas fa-box-open text-slate-400 mr-3"></i>
                                                    <p class="text-slate-700"><?= e($rincian['jumlah']) ?> unit diambil dari batch @ Rp <?= number_format($rincian['harga'], 2, ',', '.') ?></p>
                                                </div>
                                                <p class="font-semibold text-slate-800">Rp <?= number_format($rincian['jumlah'] * $rincian['harga'], 2, ',', '.') ?></p>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        
                                        <form method="POST">
                                            <input type="hidden" name="action" value="sell">
                                            <input type="hidden" name="barang_id" value="<?= e($submitted_data['barang_id']) ?>">
                                            <input type="hidden" name="jumlah_total" value="<?= e($submitted_data['jumlah']) ?>">
                                            <input type="hidden" name="rincian_fifo" value='<?= e(json_encode($hasil_perhitungan['rincian'])) ?>'>
                                            
                                            <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105 hover:shadow-xl flex items-center justify-center">
                                                <i class="fas fa-check-circle mr-2"></i> Proses & Catat Penjualan
                                            </button>
                                        </form>
                                    </div>
                                <?php else: // Stok tidak cukup ?>
                                    <div class="p-8 text-center flex flex-col justify-center items-center h-full">
                                        <i class="fas fa-exclamation-triangle text-5xl text-amber-400 mb-4"></i>
                                        <h3 class="text-2xl font-bold text-slate-800">Stok Tidak Mencukupi</h3>
                                        <p class="mt-2 text-slate-500 max-w-sm">Jumlah yang diminta (<?= e($submitted_data['jumlah']) ?>) melebihi total stok yang tersedia (<?= e($hasil_perhitungan['total_stok']) ?>).</p>
                                    </div>
                                <?php endif; ?>
                            <?php else: // Tampilan Awal ?>
                                <div class="p-8 text-center flex flex-col justify-center items-center h-full">
                                    <i class="fas fa-arrow-left text-5xl text-slate-300 mb-4"></i>
                                    <h3 class="text-2xl font-bold text-slate-800">Hasil Perhitungan</h3>
                                    <p class="mt-2 text-slate-500 max-w-sm">Hasil kalkulasi HPP dan opsi untuk memproses transaksi akan muncul di sini.</p>
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../asset/lib/purchase.js"></script>
</body>
</html>