<?php
session_start();
require_once '../config/Database.php';


if (isset($_SESSION['loggedin'], $_SESSION['role']) && $_SESSION['loggedin'] === true) {
    $role = $_SESSION['role'];
    if ($role === 'Admin') header('Location: ../app/Views/admin_dashboard.php');
    if ($role === 'Sales') header('Location: ../app/Views/sales_dashboard.php');
    if ($role === 'Purchasing') header('Location: ../app/Views/purchasing_dashboard.php');
    exit;
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inisialisasi koneksi database
    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password tidak boleh kosong.';
    } else {
        // Gunakan prepared statements untuk mencegah SQL Injection
        $query = "SELECT id, username, password, role, nama_lengkap FROM users WHERE username = :username LIMIT 1";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifikasi password yang di-hash
            if (password_verify($password, $user['password'])) {
                // Password benar, buat session
                session_regenerate_id();
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];

                // Arahkan berdasarkan role
                switch ($user['role']) {
                    case 'Admin':
                        header('Location: ../app/Views/admin_dashboard.php');
                        break;
                    case 'Sales':
                        header('Location: ../app/Views/sales_dashboard.php');
                        break;
                    case 'Purchasing':
                        header('Location: ../app/Views/purchasing_dashboard.php');
                        break;
                    default:
                        // Jika role tidak dikenal, arahkan ke login
                        header('Location: login.php');
                        break;
                }
                exit;
            } else {
                $error_message = 'Username atau password salah.';
            }
        } else {
            $error_message = 'Username atau password salah.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary">Inventro</h1>
            <p class="text-gray-500 mt-2">Silakan login untuk melanjutkan</p>
        </div>

        <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg relative mb-6 flex items-center" role="alert">
            <svg class="h-6 w-6 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium"><?php echo htmlspecialchars($error_message); ?></span>
        </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-5">
                <label for="username" class="block mb-2 text-sm font-semibold text-gray-700">Username</label>
                <input type="text" id="username" name="username" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>

            <div class="mb-8">
                <label for="password" class="block mb-2 text-sm font-semibold text-gray-700">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                    <div id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 cursor-pointer">
                        <svg id="eye-slash" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L6.228 6.228" />
                        </svg>
                        <svg id="eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.432 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-opacity-90 transition duration-300">Login</button>
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePasswordButton = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eye');
        const eyeSlashIcon = document.getElementById('eye-slash');

        togglePasswordButton.addEventListener('click', function() {
            // Cek tipe input saat ini
            const isPassword = passwordInput.type === 'password';

            // Ubah tipe input
            passwordInput.type = isPassword ? 'text' : 'password';

            // Ganti ikon yang ditampilkan
            eyeIcon.classList.toggle('hidden', !isPassword);
            eyeSlashIcon.classList.toggle('hidden', isPassword);
        });
    </script>

</body>

</html>