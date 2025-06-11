<?php
require_once '../../config/Database.php';
session_start();

// Guard: Hanya Admin yang bisa mengakses file ini
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    // Redirect dengan pesan error jika diakses tanpa hak
    header('Location: ../views/manage_user.php?status=error&message=Akses ditolak.');
    exit;
}

// Periksa apakah metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/manage_user.php');
    exit;
}

// Buat koneksi database
$database = new Database();
$db = $database->getConnection();

// Ambil aksi dari form (create, update, delete)
$action = $_POST['action'] ?? '';

// LOGIKA CREATE (TAMBAH PENGGUNA)
if ($action == 'create') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi input
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($role)) {
        header('Location: ../views/manage_user.php?status=error&message=Semua field wajib diisi.');
        exit;
    }

    // Cek apakah username sudah ada
    $check_query = "SELECT id FROM users WHERE username = :username";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':username', $username);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        header('Location: ../views/manage_user.php?status=error&message=Username sudah digunakan.');
        exit;
    }

    // Hash password sebelum disimpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Query untuk insert data
    $query = "INSERT INTO users (nama_lengkap, username, password, role) VALUES (:nama_lengkap, :username, :password, :role)";
    $stmt = $db->prepare($query);

    // Bind parameters
    $stmt->bindParam(':nama_lengkap', $nama_lengkap);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':role', $role);

    if ($stmt->execute()) {
        header('Location: ../views/manage_user.php?status=success&message=Pengguna berhasil ditambahkan.');
    } else {
        header('Location: ../views/manage_user.php?status=error&message=Gagal menambahkan pengguna.');
    }
}

// LOGIKA UPDATE (EDIT PENGGUNA)
elseif ($action == 'update') {
    $id = $_POST['id'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi input
    if (empty($id) || empty($nama_lengkap) || empty($username) || empty($role)) {
        header('Location: ../views/manage_user.php?status=error&message=Data tidak lengkap.');
        exit;
    }

    // Cek apakah username sudah ada (dan bukan milik user ini sendiri)
    $check_query = "SELECT id FROM users WHERE username = :username AND id != :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':username', $username);
    $check_stmt->bindParam(':id', $id);
    $check_stmt->execute();
    if ($check_stmt->rowCount() > 0) {
        header('Location: ../views/manage_user.php?status=error&message=Username sudah digunakan oleh pengguna lain.');
        exit;
    }

    // Bangun query update
    $query_parts = [];
    $params = [
        ':id' => $id,
        ':nama_lengkap' => $nama_lengkap,
        ':username' => $username,
        ':role' => $role
    ];
    
    $query_parts[] = "nama_lengkap = :nama_lengkap";
    $query_parts[] = "username = :username";
    $query_parts[] = "role = :role";

    // Jika password diisi, update juga passwordnya
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query_parts[] = "password = :password";
        $params[':password'] = $hashed_password;
    }

    $query = "UPDATE users SET " . implode(', ', $query_parts) . " WHERE id = :id";
    $stmt = $db->prepare($query);

    if ($stmt->execute($params)) {
        header('Location: ../views/manage_user.php?status=success&message=Data pengguna berhasil diperbarui.');
    } else {
        header('Location: ../views/manage_user.php?status=error&message=Gagal memperbarui data.');
    }
}

// LOGIKA DELETE (HAPUS PENGGUNA)
elseif ($action == 'delete') {
    $id = $_POST['id'];

    if (empty($id)) {
        header('Location: ../views/manage_user.php?status=error&message=ID pengguna tidak valid.');
        exit;
    }

    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        header('Location: ../views/manage_user.php?status=success&message=Pengguna berhasil dihapus.');
    } else {
        header('Location: ../views/manage_user.php?status=error&message=Gagal menghapus pengguna.');
    }
}

// Jika aksi tidak dikenali
else {
    header('Location: ../views/manage_user.php');
}
?>