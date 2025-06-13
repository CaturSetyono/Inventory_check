<?php
// File ini tidak butuh session_start() karena akan dipanggil oleh file view.
// File ini hanya berisi logika untuk mengambil data.

require_once '../../config/Database.php';

$transactions = [];
$error_message = null;

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn === null) {
        throw new Exception("Gagal terhubung ke database.");
    }

    $sql = "SELECT 
                t.tanggal, 
                b.nama_barang, 
                t.jumlah, 
                t.harga,
                (t.jumlah * t.harga) AS total_harga
            FROM 
                transaksi t
            JOIN 
                barang b ON t.barang_id = b.id
            WHERE 
                t.tipe = 'masuk'
            ORDER BY 
                t.tanggal DESC, t.id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Tangkap semua jenis error (PDOException atau Exception biasa)
    $error_message = "Error: " . $e->getMessage();
} finally {
    // Tutup koneksi
    $conn = null;
}

// Variabel $transactions dan $error_message sekarang siap digunakan oleh file view yang memanggil file ini.