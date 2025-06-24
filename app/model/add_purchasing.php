<?php
session_start();

// -- PENGATURAN PENGGUNA & PERAN --
// Aktifkan blok ini di lingkungan produksi
/*
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Purchasing', 'Admin'])) {
    // Path disesuaikan
    header('Location: ../views/Auth/login.php'); 
    exit;
}
*/

// Mock session data untuk development (hapus/komentari di produksi)
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
    <title>Tambah Transaksi Pembelian - InventoriKu</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
    <style>
        /* Animasi untuk notifikasi */
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .notification {
            animation: slideIn 0.5s ease-out forwards;
        }
    </style>
</head>

<body class="bg-slate-100">

    <div class="relative min-h-screen md:flex">
        
        <?php
        // Definisikan halaman saat ini untuk menandai link sidebar yang aktif
        $currentPage = 'add_purchase';
        // Path ke sidebar disesuaikan
        require_once '../components/sidebar.php';
        ?>

        <div id="main-content" class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm p-8 h-16 flex justify-between items-center z-10">
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
                            <h1 class="text-3xl font-bold text-slate-800">Tambah Transaksi Pembelian</h1>
                            <p class="mt-2 text-slate-600">Catat setiap barang masuk untuk menjaga keakuratan stok.</p>
                        </div>
                        <a href="../views/purchasing_dashboard.php" class="bg-slate-200 text-slate-600 hover:bg-slate-300 font-semibold py-2 px-4 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                        </a>
                    </div>

                    <?php if (isset($_SESSION['message'])) : ?>
                        <?php
                        $message_type = (isset($_SESSION['message_type']) && $_SESSION['message_type'] == 'error') ? 'error' : 'success';
                        $bg_color = ($message_type == 'error') ? 'bg-red-100 border-red-500 text-red-700' : 'bg-green-100 border-green-500 text-green-700';
                        $icon = ($message_type == 'error') ? '<i class="fas fa-times-circle mr-3"></i>' : '<i class="fas fa-check-circle mr-3"></i>';
                        ?>
                        <div id="notification" class="notification border-l-4 p-4 rounded-md mb-6 <?= $bg_color ?>" role="alert">
                            <div class="flex">
                                <div class="py-1"><?= $icon ?></div>
                                <div>
                                    <p class="font-bold"><?= ($message_type == 'error') ? 'Error' : 'Sukses' ?></p>
                                    <p class="text-sm"><?= e($_SESSION['message']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php
                        // Hapus pesan setelah ditampilkan agar tidak muncul lagi saat refresh
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                        ?>
                    <?php endif; ?>


                    <div class="bg-white p-8 rounded-lg shadow-md">
                        <form action="../controllers/process_add_purchasing.php" method="POST" id="purchase-form" class="space-y-6">
                            <div>
                                <label for="nama_barang" class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                                <input type="text" id="nama_barang" name="nama_barang" required placeholder="Contoh: Paracetamol 500mg" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500 transition">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                                    <input type="number" id="jumlah" name="jumlah" required min="1" placeholder="Masukkan jumlah unit" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500 transition">
                                </div>
                                <div>
                                    <label for="harga" class="block text-sm font-medium text-gray-700 mb-1">Harga Beli (per Unit)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" id="harga" name="harga" required step="1" min="0" placeholder="Contoh: 15000" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500 transition">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pembelian</label>
                                <input type="date" id="tanggal" name="tanggal" required value="<?= date('Y-m-d') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500 transition">
                            </div>

                            <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                                <button type="reset" class="bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold py-2 px-6 rounded-lg transition-colors">Reset</button>
                                <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-save mr-2"></i> Simpan Transaksi
                                </button>
                            </div>
                        </form>
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
    <script>
        // Sembunyikan notifikasi setelah beberapa detik
        const notification = document.getElementById('notification');
        if (notification) {
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000); // 5 detik
        }
    </script>
</body>

</html>