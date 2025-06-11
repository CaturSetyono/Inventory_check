<?php
session_start();
// Guard: Cek jika pengguna sudah login dan rolenya adalah Purchasing atau Admin
// Admin diberi akses untuk bisa melihat semua dashboard
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Purchasing', 'Admin'])) {
    // Jika tidak, tendang ke halaman login
    header('Location: Auth/login.php');
    exit;
}

// Dummy data untuk nama lengkap jika session tidak ada (hanya untuk development)
if (!isset($_SESSION['nama_lengkap'])) {
    $_SESSION['nama_lengkap'] = 'Staff Gudang';
    $_SESSION['role'] = 'Purchasing';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchasing Dashboard - Inventory App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom scrollbar (opsional, untuk estetika) */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }
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
                    <i class="fas fa-tachometer-alt w-6 text-center"></i>
                    <span class="ml-4">Dashboard</span>
                </a>
                <a href="#" class="flex items-center py-3 px-6 hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-plus-circle w-6 text-center"></i>
                    <span class="ml-4">Tambah Transaksi</span>
                </a>
                <a href="#" class="flex items-center py-3 px-6 hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-history w-6 text-center"></i>
                    <span class="ml-4">Riwayat Transaksi</span>
                </a>
                <a href="#" class="flex items-center py-3 px-6 hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-truck-fast w-6 text-center"></i>
                    <span class="ml-4">Manajemen Pemasok</span>
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
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                    <input type="text" placeholder="Cari transaksi, pemasok..." class="w-full pl-10 pr-4 py-2 border rounded-full bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="relative">
                    <button id="profile-button" class="flex items-center space-x-2">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_lengkap']); ?>&background=random&color=fff" alt="Avatar" class="w-10 h-10 rounded-full">
                        <span class="font-semibold text-gray-700 hidden md:block"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                        <i class="fas fa-chevron-down text-gray-500"></i>
                    </button>
                    <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user mr-2"></i>Profil</a>
                        <button type="button" class="logout-trigger block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-8">
                <div class="container mx-auto">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">Halo, <?php echo htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'])[0]); ?>!</h1>
                        <p class="mt-2 text-gray-600">Selamat datang di dasbor pembelian. Siap untuk mencatat transaksi baru?</p>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Menu Utama</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                        <div class="bg-blue-500 p-6 rounded-lg shadow-lg hover:bg-blue-600 transition-colors duration-300 text-white cursor-pointer">
                             <div class="flex items-center">
                                <i class="fas fa-plus-circle text-3xl"></i>
                                <h3 class="text-xl font-semibold ml-4">Tambah Transaksi Baru</h3>
                            </div>
                            <p class="mt-3">Catat transaksi pembelian barang baru dari pemasok. Klik di sini untuk memulai.</p>
                            <div class="text-right mt-4 font-bold text-lg">&rarr;</div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                             <div class="flex items-center">
                                <i class="fas fa-history text-3xl text-green-500"></i>
                                <h3 class="text-xl font-semibold ml-4 text-gray-800">Riwayat Transaksi</h3>
                            </div>
                            <p class="text-gray-600 mt-3">Lihat, cari, dan kelola semua riwayat transaksi pembelian yang pernah dicatat.</p>
                            <a href="#" class="inline-block mt-4 text-green-600 font-semibold hover:underline">Lihat Riwayat &rarr;</a>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                             <div class="flex items-center">
                                <i class="fas fa-truck-fast text-3xl text-purple-500"></i>
                                <h3 class="text-xl font-semibold ml-4 text-gray-800">Data Pemasok</h3>
                            </div>
                            <p class="text-gray-600 mt-3">Kelola daftar pemasok (supplier) untuk mempercepat proses transaksi.</p>
                            <a href="#" class="inline-block mt-4 text-purple-600 font-semibold hover:underline">Kelola Pemasok &rarr;</a>
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
                <a href="../Auth/logout.php" id="confirm-logout-btn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded">Yakin, Keluar</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileButton = document.getElementById('profile-button');
            const profileDropdown = document.getElementById('profile-dropdown');
            const logoutModal = document.getElementById('logout-modal');
            const logoutTriggers = document.querySelectorAll('.logout-trigger');
            const cancelLogoutBtn = document.getElementById('cancel-logout-btn');

            if(profileButton){
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
            function showLogoutModal() { logoutModal.classList.remove('hidden'); }
            function hideLogoutModal() { logoutModal.classList.add('hidden'); }
            
            logoutTriggers.forEach(trigger => {
                trigger.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
                        profileDropdown.classList.add('hidden');
                    }
                    showLogoutModal();
                });
            });

            if(cancelLogoutBtn){ cancelLogoutBtn.addEventListener('click', hideLogoutModal); }
            if(logoutModal){
                 logoutModal.addEventListener('click', function(event) {
                    if (event.target === logoutModal) { hideLogoutModal(); }
                });
            }
        });
    </script>
</body>
</html>