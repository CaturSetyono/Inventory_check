<?php
// app/controllers/generate_nota_pdf.php

session_start();

// 1. Load Composer Autoloader
require_once '../../vendor/autoload.php';

// 2. Import Namespace Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Guard: Hanya Sales atau Admin yang bisa akses
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Sales', 'Admin'])) {
    die("Akses ditolak. Anda harus login sebagai Sales atau Admin.");
}

// Fungsi helper
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

// 3. Pastikan data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Metode tidak diizinkan.");
}

// 4. Ambil data nota dari form
$nama_barang = $_POST['nama_barang'] ?? 'Tidak Diketahui';
$jumlah_jual = $_POST['jumlah_jual'] ?? 0;
$total_hpp = $_POST['total_hpp'] ?? 0;
$rincian_json = $_POST['rincian_fifo'] ?? '[]';
$rincian_fifo = json_decode($rincian_json, true);

// 5. Buat struktur HTML untuk Nota PDF
ob_start(); // Mulai output buffering untuk menangkap HTML
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Penjualan</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; color: #333; }
        .header p { margin: 5px 0; }
        .details { margin-bottom: 20px; }
        .details table { width: 100%; }
        .details td { padding: 5px; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total-section { margin-top: 20px; float: right; width: 50%; }
        .total-section table { width: 100%; }
        .total-section td { padding: 5px; }
        .total-section .total { font-weight: bold; font-size: 16px; border-top: 2px solid #333; }
        .footer { text-align: center; margin-top: 50px; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NOTA PENJUALAN</h1>
            <p><strong>PT Inventory Karya Usaha.</strong> | Jl. Cendrawasih No. 45, Yogyakarta</p>
        </div>
        <div class="details">
            <table>
                <tr>
                    <td><strong>No. Nota:</strong> INV-<?= time() ?></td>
                    <td class="text-right"><strong>Tanggal:</strong> <?= date('d M Y') ?></td>
                </tr>
                <tr>
                    <td><strong>Sales:</strong> <?= e($_SESSION['nama_lengkap']) ?></td>
                    <td class="text-right"></td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi Barang</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= e($nama_barang) ?></td>
                    <td class="text-right"><?= e($jumlah_jual) ?></td>
                    <td class="text-right">
                        <?php
                        // Menghitung HPP rata-rata per unit untuk ditampilkan di nota
                        $hpp_per_unit = ($jumlah_jual > 0) ? $total_hpp / $jumlah_jual : 0;
                        echo 'Rp ' . number_format($hpp_per_unit, 2, ',', '.');
                        ?>
                    </td>
                    <td class="text-right">Rp <?= number_format($total_hpp, 2, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            <table>
                <tr>
                    <td>Total</td>
                    <td class="text-right total">Rp <?= number_format($total_hpp, 2, ',', '.') ?></td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <div class="footer">
            <p>Terima kasih telah bertransaksi.</p>
            <p>Ini adalah bukti pembelian yang sah, barang yang sudah dibeli tidak dapat dikembalikan atau ditukar!</p>
        </div>
    </div>
</body>
</html>
<?php
$html = ob_get_clean(); // Ambil konten HTML dan hentikan output buffering

// 6. Inisialisasi dan Konfigurasi Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Penting untuk gambar/CSS eksternal jika ada
$dompdf = new Dompdf($options);

// 7. Load HTML ke Dompdf
$dompdf->loadHtml($html);

// 8. Atur Ukuran Kertas dan Orientasi
$dompdf->setPaper('A5', 'portrait');

// 9. Render HTML menjadi PDF
$dompdf->render();

// 10. Kirim PDF ke Browser untuk di-download
// Nama file: NOTA-timestamp.pdf
$dompdf->stream("NOTA-" . time() . ".pdf", ["Attachment" => true]);