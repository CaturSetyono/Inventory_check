<?php
session_start();
// Memanggil controller yang sudah kita buat
require_once '../controllers/report_controller.php';

// Pastikan variabel dari controller tersedia
// $db_error, $total_sales_value, $total_purchases_value, $sales_per_month, $purchases_per_month
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - InventoriKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/purchase.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-slate-100 font-sans">
    <div class="relative min-h-screen md:flex">
        <?php
        $currentPage = 'reports'; // Tandai halaman aktif di sidebar
        include '../components/sidebar.php'; // Memanggil sidebar
        ?>

        <div id="main-content" class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm p-4 h-16 flex justify-between items-center z-10">
                <button id="sidebar-toggle" class="text-gray-600 hover:text-gray-900 focus:outline-none bg-slate-200/70 hover:bg-slate-300 w-10 h-10 rounded-full flex items-center justify-center">
                    <i id="sidebar-toggle-icon" class="fas fa-chevron-left text-xl"></i>
                </button>
                <div class="relative">
                    <button id="profile-button" class="flex items-center space-x-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap'] ?? 'User') ?>&background=0ea5e9&color=fff&size=128" alt="Avatar" class="w-10 h-10 rounded-full border-2 border-slate-300">
                        <div class="hidden md:block text-right">
                            <span class="font-semibold text-slate-800 text-sm"><?= e($_SESSION['nama_lengkap'] ?? 'Pengguna') ?></span>
                            <span class="block text-xs text-slate-500"><?= e($_SESSION['role'] ?? 'Guest') ?></span>
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
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-slate-800">Laporan Keuangan</h1>
                        <p class="mt-2 text-slate-600">Ringkasan penjualan, pembelian, dan tren bulanan.</p>
                    </div>

                    <?php if ($db_error): ?>
                        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800" role="alert">
                            <strong>Error:</strong> <?= e($db_error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between transition-all hover:shadow-lg hover:-translate-y-1">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Penjualan</p>
                                <p class="text-3xl font-bold text-green-600 mt-2">Rp <?= number_format($total_sales_value, 0, ',', '.') ?></p>
                            </div>
                            <div class="text-green-500"><i class="fas fa-money-bill-wave fa-2x"></i></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between transition-all hover:shadow-lg hover:-translate-y-1">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Pembelian</p>
                                <p class="text-3xl font-bold text-blue-600 mt-2">Rp <?= number_format($total_purchases_value, 0, ',', '.') ?></p>
                            </div>
                            <div class="text-blue-500"><i class="fas fa-shopping-cart fa-2x"></i></div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4">Tren Penjualan & Pembelian (12 Bulan Terakhir)</h3>
                        <div class="relative h-80 md:h-96">
                            <canvas id="salesPurchasesChart"
                                data-sales-labels='<?= $sales_per_month['labels'] ?? '[]' ?>'
                                data-sales-values='<?= $sales_per_month['values'] ?? '[]' ?>'
                                data-purchases-values='<?= $purchases_per_month['values'] ?? '[]' ?>'>
                            </canvas>
                        </div>
                    </div>
                </div>
            </main>
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

    <script src="../asset/lib/purchase.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesPurchasesChart');
            if (ctx) {
                const salesLabels = JSON.parse(ctx.dataset.salesLabels);
                const salesValues = JSON.parse(ctx.dataset.salesValues);
                const purchasesValues = JSON.parse(ctx.dataset.purchasesValues);

                new Chart(ctx, {
                    type: 'line', // Menggunakan line chart untuk tren
                    data: {
                        labels: salesLabels,
                        datasets: [
                            {
                                label: 'Penjualan',
                                data: salesValues,
                                borderColor: '#22c55e', // green-500
                                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                                fill: true,
                                tension: 0.3
                            },
                            {
                                label: 'Pembelian',
                                data: purchasesValues,
                                borderColor: '#3b82f6', // blue-500
                                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                fill: true,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: false,
                                text: 'Tren Penjualan & Pembelian Bulanan'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value, index, values) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>