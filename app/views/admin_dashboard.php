<?php
session_start();
// Guard: Cek jika pengguna sudah login dan rolenya adalah Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    // Jika tidak, tendang ke halaman login
    header('Location: ../../Auth/login.php');
    exit;
}

// Dummy data untuk nama lengkap jika session tidak ada (hanya untuk development)
if (!isset($_SESSION['nama_lengkap'])) {
    $_SESSION['nama_lengkap'] = 'Admin Ganteng';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Inventory App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom scrollbar (opsional, untuk estetika) */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">

    <div class="flex h-screen">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-6 text-center">
                <h2 class="text-2xl font-bold text-white"><i class="fas fa-boxes-stacked mr-2"></i>InventoriKu</h2>
            </div>
            <nav class="mt-6">
                <a href="#" class="flex items-center py-3 px-6 bg-gray-700 text-gray-100">
                    <i class="fas fa-tachometer-alt w-6 text-center"></i><span class="ml-4">Dashboard</span>
                </a>
                <a href="#" class="flex items-center py-3 px-6 hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-box-open w-6 text-center"></i><span class="ml-4">Manajemen Barang</span>
                </a>
                <a href="#" class="flex items-center py-3 px-6 hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-warehouse w-6 text-center"></i><span class="ml-4">Manajemen Stok</span>
                </a>
                <a href="#" class="flex items-center py-3 px-6 hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-truck-fast w-6 text-center"></i><span class="ml-4">Manajemen Pemasok</span>
                </a>
                <a href="#" class="flex items-center py-3 px-6 hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-chart-line w-6 text-center"></i><span class="ml-4">Laporan</span>
                </a>
                <a href="../model/manage_user.php" class="flex items-center py-3 px-6 hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-users w-6 text-center"></i><span class="ml-4">Manajemen Pengguna</span>
                </a>
                <a href="#" class="flex items-center py-3 px-6 hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-cog w-6 text-center"></i><span class="ml-4">Pengaturan</span>
                </a>
            </nav>
            <div class="absolute bottom-0 w-64 p-6">
                <button type="button" class="logout-trigger flex items-center justify-center bg-red-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-600 w-full transition duration-200">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-md p-4 flex justify-between items-center">
                <div class="relative w-1/3">
                   
                </div>
                <div class="relative">
                    <button id="profile-button" class="flex items-center space-x-2">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_lengkap']); ?>&background=random&color=fff" alt="Avatar" class="w-10 h-10 rounded-full">
                        <span class="font-semibold text-gray-700 hidden md:block"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                        <i class="fas fa-chevron-down text-gray-500"></i>
                    </button>
                    <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user mr-2"></i>Profil</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-2"></i>Pengaturan</a>
                        <div class="border-t border-gray-100"></div>
                        <button type="button" class="logout-trigger block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-8">
                <div class="container mx-auto">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">Selamat Datang Kembali, <?php echo htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'])[0]); ?>!</h1>
                        <p class="mt-2 text-gray-600">Berikut adalah ringkasan aktivitas inventaris Anda hari ini.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Barang</p>
                                <p class="text-2xl font-bold text-gray-800">1,250</p>
                            </div>
                            <div class="bg-blue-100 text-blue-600 rounded-full p-3"><i class="fas fa-box-open fa-lg"></i></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Stok Hampir Habis</p>
                                <p class="text-2xl font-bold text-orange-500">15</p>
                            </div>
                            <div class="bg-orange-100 text-orange-600 rounded-full p-3"><i class="fas fa-exclamation-triangle fa-lg"></i></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Barang Masuk Hari Ini</p>
                                <p class="text-2xl font-bold text-gray-800">75</p>
                            </div>
                            <div class="bg-green-100 text-green-600 rounded-full p-3"><i class="fas fa-arrow-down fa-lg"></i></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Barang Keluar Hari Ini</p>
                                <p class="text-2xl font-bold text-gray-800">120</p>
                            </div>
                            <div class="bg-red-100 text-red-600 rounded-full p-3"><i class="fas fa-arrow-up fa-lg"></i></div>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Akses Cepat Manajemen</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="flex items-center"><i class="fas fa-box-open text-3xl text-blue-500"></i>
                                <h3 class="text-xl font-semibold ml-4 text-gray-800">Manajemen Barang</h3>
                            </div>
                            <p class="text-gray-600 mt-3">Tambah, edit, hapus, dan lihat detail semua barang yang ada di dalam inventaris.</p><a href="#" class="inline-block mt-4 text-blue-600 font-semibold hover:underline">Kelola Sekarang &rarr;</a>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="flex items-center"><i class="fas fa-warehouse text-3xl text-green-500"></i>
                                <h3 class="text-xl font-semibold ml-4 text-gray-800">Manajemen Stok</h3>
                            </div>
                            <p class="text-gray-600 mt-3">Lakukan penyesuaian stok (stock opname), catat barang masuk dan barang keluar.</p><a href="#" class="inline-block mt-4 text-green-600 font-semibold hover:underline">Kelola Sekarang &rarr;</a>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="flex items-center"><i class="fas fa-chart-pie text-3xl text-yellow-500"></i>
                                <h3 class="text-xl font-semibold ml-4 text-gray-800">Laporan Inventaris</h3>
                            </div>
                            <p class="text-gray-600 mt-3">Hasilkan laporan penjualan, laporan stok, dan analisis data untuk pengambilan keputusan.</p><a href="#" class="inline-block mt-4 text-yellow-600 font-semibold hover:underline">Lihat Laporan &rarr;</a>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="flex items-center">
                                <i class="fas fa-truck-fast text-3xl text-purple-500"></i>
                                <h3 class="text-xl font-semibold ml-4 text-gray-800">Manajemen Pemasok</h3>
                            </div>
                            <p class="text-gray-600 mt-3">Kelola data pemasok (supplier), lacak riwayat pembelian dan performa mereka.</p>
                            <a href="#" class="inline-block mt-4 text-purple-600 font-semibold hover:underline">Kelola Sekarang &rarr;</a>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="flex items-center">
                                <i class="fas fa-users-cog text-3xl text-indigo-500"></i>
                                <h3 class="text-xl font-semibold ml-4 text-gray-800">Manajemen Pengguna</h3>
                            </div>
                            <p class="text-gray-600 mt-3">Tambah atau nonaktifkan akun pengguna lain (misal: Staf Gudang) dan atur hak aksesnya.</p>
                            <a href="../model/manage_user.php" class="inline-block mt-4 text-indigo-600 font-semibold hover:underline">Kelola Sekarang &rarr;</a>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="flex items-center">
                                <i class="fas fa-cogs text-3xl text-gray-500"></i>
                                <h3 class="text-xl font-semibold ml-4 text-gray-800">Pengaturan Sistem</h3>
                            </div>
                            <p class="text-gray-600 mt-3">Atur informasi umum perusahaan, kategori barang, satuan, dan konfigurasi lainnya.</p>
                            <a href="#" class="inline-block mt-4 text-gray-600 font-semibold hover:underline">Buka Pengaturan &rarr;</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="logout-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-sm mx-auto text-center">
            <div class="mb-4"><i class="fas fa-exclamation-triangle text-5xl text-yellow-400"></i></div>
            <h3 class="text-2xl font-bold text-gray-800">Anda Yakin?</h3>
            <p class="text-gray-600 my-2">Apakah Anda benar-benar ingin keluar dari sesi ini?</p>
            <div class="mt-6 flex justify-center space-x-4">
                <button id="cancel-logout-btn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">Batal</button>
                <a href="../../Auth/logout.php" id="confirm-logout-btn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded">Yakin, Keluar</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logika Dropdown Profil
            const profileButton = document.getElementById('profile-button');
            const profileDropdown = document.getElementById('profile-dropdown');

            if (profileButton) {
                profileButton.addEventListener('click', function(event) {
                    event.stopPropagation();
                    profileDropdown.classList.toggle('hidden');
                });
            }

            window.addEventListener('click', function() {
                if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
                    profileDropdown.classList.add('hidden');
                }
            });

            // BARU: Logika untuk Modal Konfirmasi Logout
            const logoutModal = document.getElementById('logout-modal');
            const logoutTriggers = document.querySelectorAll('.logout-trigger');
            const cancelLogoutBtn = document.getElementById('cancel-logout-btn');

            function showLogoutModal() {
                logoutModal.classList.remove('hidden');
            }

            function hideLogoutModal() {
                logoutModal.classList.add('hidden');
            }

            logoutTriggers.forEach(trigger => {
                trigger.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
                        profileDropdown.classList.add('hidden');
                    }
                    showLogoutModal();
                });
            });

            if (cancelLogoutBtn) {
                cancelLogoutBtn.addEventListener('click', hideLogoutModal);
            }

            if (logoutModal) {
                logoutModal.addEventListener('click', function(event) {
                    if (event.target === logoutModal) {
                        hideLogoutModal();
                    }
                });
            }
        });
    </script>
</body>

</html>