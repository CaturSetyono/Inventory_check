<?php
// Di asumsikan file ini berada di app/model/
session_start();
require_once '../../config/Database.php'; // Path dari app/model/ ke app/config/

// Guard: Cek jika pengguna adalah Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    // Redirect ke halaman dashboard admin jika bukan admin
    header('Location: ../views/admin_dashboard.php');
    exit;
}

if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// Buat koneksi database
$database = new Database();
$db = $database->getConnection();

// Ambil semua data user untuk ditampilkan di tabel
$query = "SELECT id, nama_lengkap, username, role, created_at FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
    <style>
        /* CSS untuk transisi modal fade & scale */
        .modal-enter {
            opacity: 0;
            transform: scale(0.95);
        }

        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: opacity 200ms ease-out, transform 200ms ease-out;
        }

        .modal-leave {
            opacity: 1;
            transform: scale(1);
        }

        .modal-leave-active {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 200ms ease-in, transform 200ms ease-in;
        }
    </style>
</head>

<body class="bg-slate-100 font-sans">

    <div class="relative min-h-screen md:flex">

        <?php
        // Integrasi Sidebar
        $currentPage = 'manage_user'; // Set halaman aktif untuk sidebar
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

                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-800">Manajemen Pengguna</h1>
                            <p class="mt-2 text-slate-600">Tambah, edit, atau nonaktifkan akun pengguna sistem.</p>
                        </div>
                        <button id="addUserBtn" class="mt-4 sm:mt-0 flex items-center bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i> Tambah Pengguna
                        </button>
                    </div>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $_SESSION['message_type'] == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" role="alert">
                            <?= e($_SESSION['message']) ?>
                        </div>
                        <?php
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                        ?>
                    <?php endif; ?>

                    <div class="bg-white p-6 md:p-8 rounded-lg shadow-md overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Pengguna</th>
                                    <th scope="col" class="px-6 py-3">Role</th>
                                    <th scope="col" class="px-6 py-3">Tanggal Bergabung</th>
                                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-10 text-slate-500">
                                            <i class="fas fa-box-open fa-3x mb-3"></i>
                                            <p>Belum ada pengguna yang terdaftar.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama_lengkap']) ?>&background=random&color=fff&size=128" alt="">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-semibold text-slate-900"><?= e($user['nama_lengkap']) ?></div>
                                                        <div class="text-sm text-slate-500">@<?= e($user['username']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2.5 py-1 text-xs font-semibold leading-tight rounded-full
                                                <?php
                                                if ($user['role'] == 'Admin') echo 'bg-rose-100 text-rose-800';
                                                elseif ($user['role'] == 'Sales') echo 'bg-emerald-100 text-emerald-800';
                                                else echo 'bg-sky-100 text-sky-800'; // Purchasing
                                                ?>">
                                                    <?= e($user['role']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-slate-500"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                                <button class="edit-btn text-sm font-medium py-2 px-3 rounded-lg transition-colors text-sky-600 bg-sky-100 hover:bg-sky-200"
                                                    data-id="<?= $user['id'] ?>"
                                                    data-nama_lengkap="<?= e($user['nama_lengkap']) ?>"
                                                    data-username="<?= e($user['username']) ?>"
                                                    data-role="<?= e($user['role']) ?>">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                <button class="delete-btn text-sm font-medium py-2 px-3 rounded-lg transition-colors text-red-600 bg-red-100 hover:bg-red-200"
                                                    data-id="<?= $user['id'] ?>"
                                                    data-nama_lengkap="<?= e($user['nama_lengkap']) ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 z-50 modal-enter">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200">
                <h2 id="modalTitle" class="text-xl font-bold text-slate-800">Tambah Pengguna Baru</h2>
                <button type="button" class="cancel-modal-btn text-slate-400 hover:text-slate-600"><i class="fas fa-times fa-lg"></i></button>
            </div>
            <form id="userForm" action="../controllers/process_user.php" method="POST" class="p-6">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="userId">
                <div class="space-y-4">
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500" required>
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                        <input type="text" id="username" name="username" class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500" required>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <p id="passwordHint" class="text-xs text-slate-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <select id="role" name="role" class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500" required>
                            <option value="Admin">Admin</option>
                            <option value="Sales">Sales</option>
                            <option value="Purchasing">Purchasing</option>
                        </select>
                    </div>
                </div>
                <div class="pt-6 mt-6 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" class="cancel-modal-btn bg-white hover:bg-slate-100 text-slate-700 font-bold py-2 px-4 rounded-lg border border-slate-300 transition-colors">Batal</button>
                    <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">Simpan Pengguna</button>
                </div>
            </form>
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

      </form>
        </div>
    </div> <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 z-50 modal-enter">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
            <form id="deleteUserForm" action="../controllers/process_user.php" method="POST">
                <div class="p-6 text-center">
                    <i class="fas fa-exclamation-triangle text-5xl text-yellow-400 mb-4"></i>
                    <h2 class="text-2xl font-bold text-slate-800">Konfirmasi Hapus</h2>
                    <p class="mt-2 text-slate-600">
                        Apakah Anda yakin ingin menghapus pengguna <strong id="deleteUserName" class="font-semibold"></strong>? 
                        <br>
                        Tindakan ini tidak dapat diurungkan.
                    </p>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteUserId">
                </div>
                <div class="px-6 py-4 bg-slate-50 rounded-b-lg flex justify-center space-x-4">
                    <button type="button" class="cancel-modal-btn bg-white hover:bg-slate-100 text-slate-700 font-bold py-2 px-6 rounded-lg border border-slate-300 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div id="logout-modal" class="hidden ...">
    ```

    <script src="../asset/lib/purchase.js"></script>
    <script src="../asset/lib/add_user.js"></script>

</body>

</html>