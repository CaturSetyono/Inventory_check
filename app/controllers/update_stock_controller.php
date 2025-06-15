<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/Database.php';

// Cek 'action' dari input JSON (untuk fetch) atau dari GET (opsional)
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? null;

// Ambil role dari session
$userRole = $_SESSION['role'] ?? 'Guest';

// Buat koneksi database sekali saja
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    // Jika koneksi gagal, hentikan semuanya
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal: ' . $e->getMessage()]);
    exit;
}


// --- SELEKSI AKSI BERDASARKAN PARAMETER 'action' ---
switch ($action) {
    case 'update':
        // LOGIKA UNTUK UPDATE STOK
        header('Content-Type: application/json'); // Pastikan header JSON di sini
        if ($userRole !== 'Admin') {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
            exit;
        }

        $id = $input['id'] ?? null;
        $field = $input['field'] ?? null;
        $value = $input['value'] ?? '';
        
        $allowed_fields = ['nama_barang', 'jumlah', 'harga_beli'];
        if (!$id || !$field || !in_array($field, $allowed_fields)) {
            echo json_encode(['status' => 'error', 'message' => 'Input tidak valid.']);
            exit;
        }

        try {
            $sql = "UPDATE barang SET `{$field}` = :value, tanggal = CURDATE() WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit; // Hentikan skrip setelah aksi selesai

    case 'delete':
        // LOGIKA UNTUK DELETE STOK
        header('Content-Type: application/json'); // Pastikan header JSON di sini
        if ($userRole !== 'Admin') {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
            exit;
        }
        $id = $input['id'] ?? null;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID barang tidak ada.']);
            exit;
        }

        try {
            $db->beginTransaction();
            $stmt1 = $db->prepare("DELETE FROM transaksi WHERE barang_id = :id");
            $stmt1->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt1->execute();

            $stmt2 = $db->prepare("DELETE FROM barang WHERE id = :id");
            $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();
            $db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Barang berhasil dihapus.']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit; // Hentikan skrip setelah aksi selesai

    default:
        // LOGIKA DEFAULT: AMBIL DATA UNTUK DITAMPILKAN (PAGINATION)
        // Ini akan berjalan jika tidak ada 'action' spesifik (saat halaman dimuat)
        $limit = 20;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;

        $items = [];
        $total_items = 0;
        $db_error = null;

        try {
            $total_items = $db->query("SELECT COUNT(id) FROM barang")->fetchColumn();
            $total_pages = ceil($total_items / $limit);

            $query = "SELECT id, nama_barang, jumlah, harga_beli, tanggal FROM barang ORDER BY id DESC LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $db_error = "Gagal mengambil data: " . $e->getMessage();
        }
        break;
}
// Variabel $items, $total_pages, dll. akan tersedia untuk file view