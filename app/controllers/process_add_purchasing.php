<?php
session_start();
require '../../config/Database.php';

// -- PENGATURAN PENGGUNA & PERAN (diaktifkan di produksi) --
/*
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Purchasing', 'Admin'])) {
    $_SESSION['message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    $_SESSION['message_type'] = "error";
    header('Location: ../views/Auth/login.php');
    exit;
}
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../model/add_purchasing.php');
    exit;
}

// 1. Validasi Input
$nama_barang = trim($_POST['nama_barang'] ?? '');
$jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);
$harga = filter_input(INPUT_POST, 'harga', FILTER_VALIDATE_FLOAT);
$tanggal = $_POST['tanggal'] ?? '';

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

if (empty($nama_barang) || $jumlah === false || $jumlah <= 0 || $harga === false || $harga < 0 || !validateDate($tanggal)) {
    $_SESSION['message'] = "Data tidak valid. Pastikan semua kolom terisi dengan benar.";
    $_SESSION['message_type'] = "error";
    header('Location: ../model/add_purchasing.php');
    exit;
}

// 2. Inisialisasi Database
$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    $_SESSION['message'] = "Gagal terhubung ke database.";
    $_SESSION['message_type'] = "error";
    header('Location: ../model/add_purchasing.php');
    exit;
}

// 3. Proses Transaksi Database menggunakan sintaks PDO
try {
    // Mulai transaksi
    $conn->beginTransaction();

    // Query 1: Masukkan ke tabel 'barang'
    // Menggunakan named parameters (contoh: :nama_barang) untuk keamanan
    $sql_barang = "INSERT INTO barang (nama_barang, jumlah, harga_beli, tanggal) VALUES (:nama_barang, :jumlah, :harga, :tanggal)";
    $stmt_barang = $conn->prepare($sql_barang);

    // Eksekusi statement dengan mengikat nilai dalam array
    $stmt_barang->execute([
        ':nama_barang' => $nama_barang,
        ':jumlah'      => $jumlah,
        ':harga'       => $harga,
        ':tanggal'     => $tanggal
    ]);

    // Ambil ID barang yang baru saja dimasukkan
    $barang_id = $conn->lastInsertId();

    // Jika ID tidak valid, hentikan proses
    if (!$barang_id) {
        throw new PDOException("Gagal mendapatkan ID barang baru setelah insert.");
    }

    // Query 2: Masukkan ke tabel 'transaksi'
    $sql_trans = "INSERT INTO transaksi (barang_id, jumlah, tipe, harga, tanggal) VALUES (:barang_id, :jumlah, 'masuk', :harga, :tanggal)";
    $stmt_trans = $conn->prepare($sql_trans);
    
    $stmt_trans->execute([
        ':barang_id' => $barang_id,
        ':jumlah'    => $jumlah,
        ':harga'     => $harga,
        ':tanggal'   => $tanggal
    ]);

    // Jika semua query berhasil, commit transaksi
    $conn->commit();

    $_SESSION['message'] = "Transaksi pembelian berhasil direkam. Stok baru telah ditambahkan.";
    $_SESSION['message_type'] = "success";

} catch (PDOException $e) {
    // Jika terjadi error, batalkan semua perubahan (rollback)
    // Cek dulu apakah transaksi sedang berjalan sebelum rollback
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    $_SESSION['message'] = "Terjadi kesalahan database. Transaksi dibatalkan. Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";

} finally {
    // Tutup koneksi dengan mengaturnya menjadi null
    $conn = null;
}

// Redirect kembali ke halaman form
header('Location: ../model/add_purchasing.php');
exit;