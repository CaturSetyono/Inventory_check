<?php
require_once '../../config/Database.php';
session_start();

// Guard: Cek jika pengguna adalah Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    // Ganti 'admin_dashboard.php' dengan path file dasbor admin Anda yang sebenarnya
    header('Location: ../../admin_dashboard.php');
    exit;
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
    <title>Manajemen Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Style untuk transisi modal */
        .modal-enter {
            opacity: 0;
            transform: scale(0.95);
        }

        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: opacity 300ms, transform 300ms;
        }

        .modal-leave {
            opacity: 1;
            transform: scale(1);
        }

        .modal-leave-active {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 300ms, transform 300ms;
        }
    </style>
</head>

<body class="bg-slate-100">

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">

        <nav class="mb-6 text-sm" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="./admin_dashboard.php" class="text-slate-500 hover:text-blue-600">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard Admin
                    </a>
                </li>
                <li class="flex items-center">
                    <span class="mx-2 text-slate-400">/</span>
                </li>
                <li class="text-slate-700 font-semibold" aria-current="page">
                    Manajemen Pengguna
                </li>
            </ol>
        </nav>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Manajemen Pengguna</h1>
                <p class="mt-1 text-slate-500">Tambah, edit, atau hapus pengguna sistem.</p>
            </div>
            <button id="addUserBtn" class="mt-4 sm:mt-0 flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                <span>Tambah Pengguna</span>
            </button>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $_GET['status'] == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-md overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="p-4 text-left font-semibold text-slate-600">Nama Lengkap</th>
                        <th scope="col" class="p-4 text-left font-semibold text-slate-600">Username</th>
                        <th scope="col" class="p-4 text-left font-semibold text-slate-600">Role</th>
                        <th scope="col" class="p-4 text-left font-semibold text-slate-600">Tanggal Bergabung</th>
                        <th scope="col" class="p-4 text-center font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 whitespace-nowrap font-medium text-slate-800"><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                            <td class="p-4 whitespace-nowrap text-slate-500">@<?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-semibold leading-tight rounded-full
                                <?php
                                if ($user['role'] == 'Admin') echo 'bg-rose-100 text-rose-800';
                                elseif ($user['role'] == 'Sales') echo 'bg-emerald-100 text-emerald-800';
                                else echo 'bg-sky-100 text-sky-800';
                                ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td class="p-4 whitespace-nowrap text-slate-500"><?php echo date('d F Y', strtotime($user['created_at'])); ?></td>
                            <td class="p-4 whitespace-nowrap text-center space-x-2">
                                <button class="edit-btn font-semibold py-1 px-3 rounded-md text-xs transition-colors bg-sky-100 text-sky-700 hover:bg-sky-200"
                                    data-id="<?php echo $user['id']; ?>"
                                    data-nama_lengkap="<?php echo htmlspecialchars($user['nama_lengkap']); ?>"
                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                    data-role="<?php echo $user['role']; ?>">
                                    Edit
                                </button>
                                <button class="delete-btn font-semibold py-1 px-3 rounded-md text-xs transition-colors bg-rose-100 text-rose-700 hover:bg-rose-200"
                                    data-id="<?php echo $user['id']; ?>"
                                    data-nama_lengkap="<?php echo htmlspecialchars($user['nama_lengkap']); ?>">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 z-50 modal-enter">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 id="modalTitle" class="text-xl font-bold text-slate-800">Tambah Pengguna Baru</h2>
            </div>
            <form id="userForm" action="../controllers/process_user.php" method="POST" class="p-6">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="userId">

                <div class="space-y-4">
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                        <input type="text" id="username" name="username" class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p id="passwordHint" class="text-xs text-slate-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <select id="role" name="role" class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="Admin">Admin</option>
                            <option value="Sales">Sales</option>
                            <option value="Purchasing">Purchasing</option>
                        </select>
                    </div>
                </div>
                <div class="pt-6 mt-6 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" id="cancelBtn" class="bg-white hover:bg-slate-100 text-slate-700 font-bold py-2 px-4 rounded-lg border border-slate-300 transition-colors">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 z-50 modal-enter">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm mx-auto text-center transform transition-all">
            <div class="mb-4 text-red-500"><i class="fas fa-exclamation-circle fa-4x"></i></div>
            <h3 class="text-xl font-bold text-slate-800">Konfirmasi Hapus</h3>
            <p class="text-slate-500 my-3">Anda akan menghapus pengguna <strong id="deleteUserName" class="text-slate-900"></strong>. Tindakan ini tidak dapat dibatalkan.</p>
            <form action="../controllers/process_user.php" method="POST" class="mt-6 flex justify-center space-x-3">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteUserId">
                <button type="button" id="cancelDeleteBtn" class="bg-white hover:bg-slate-100 text-slate-700 font-bold py-2 px-6 rounded-lg border border-slate-300">Batal</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg">Ya, Hapus</button>
            </form>
        </div>
    </div>

    <script>
        // DIUBAH: Logika JS disesuaikan untuk animasi modal
        document.addEventListener('DOMContentLoaded', function() {
            // --- Elemen-elemen Modal Utama ---
            const userModal = document.getElementById('userModal');
            const addUserBtn = document.getElementById('addUserBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const modalTitle = document.getElementById('modalTitle');
            const userForm = document.getElementById('userForm');
            const formAction = document.getElementById('formAction');
            const userId = document.getElementById('userId');
            const passwordInput = document.getElementById('password');
            const passwordHint = document.getElementById('passwordHint');

            // --- Elemen-elemen Modal Hapus ---
            const deleteModal = document.getElementById('deleteModal');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            const deleteUserIdInput = document.getElementById('deleteUserId');
            const deleteUserNameSpan = document.getElementById('deleteUserName');

            // --- Fungsi untuk mengelola transisi modal ---
            const openModalWithTransition = (modal) => {
                modal.classList.remove('hidden');
                modal.classList.remove('modal-leave-active', 'modal-leave');
                modal.classList.add('modal-enter');
                requestAnimationFrame(() => {
                    modal.classList.add('modal-enter-active');
                });
            };

            const closeModalWithTransition = (modal) => {
                modal.classList.remove('modal-enter-active', 'modal-enter');
                modal.classList.add('modal-leave');
                requestAnimationFrame(() => {
                    modal.classList.add('modal-leave-active');
                });
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300); // Durasi transisi
            };

            // --- Event Listeners ---
            addUserBtn.addEventListener('click', () => {
                userForm.reset();
                modalTitle.textContent = 'Tambah Pengguna Baru';
                formAction.value = 'create';
                passwordInput.setAttribute('required', 'true');
                passwordHint.classList.add('hidden');
                openModalWithTransition(userModal);
            });

            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    userForm.reset();
                    modalTitle.textContent = 'Edit Pengguna';
                    formAction.value = 'update';
                    passwordInput.removeAttribute('required');
                    passwordHint.classList.remove('hidden');

                    userId.value = this.dataset.id;
                    document.getElementById('nama_lengkap').value = this.dataset.nama_lengkap;
                    document.getElementById('username').value = this.dataset.username;
                    document.getElementById('role').value = this.dataset.role;

                    openModalWithTransition(userModal);
                });
            });

            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    deleteUserIdInput.value = this.dataset.id;
                    deleteUserNameSpan.textContent = this.dataset.nama_lengkap;
                    openModalWithTransition(deleteModal);
                });
            });

            cancelBtn.addEventListener('click', () => closeModalWithTransition(userModal));
            cancelDeleteBtn.addEventListener('click', () => closeModalWithTransition(deleteModal));

            userModal.addEventListener('click', (e) => {
                if (e.target === userModal) closeModalWithTransition(userModal);
            });
            deleteModal.addEventListener('click', (e) => {
                if (e.target === deleteModal) closeModalWithTransition(deleteModal);
            });
        });
    </script>

</body>

</html>