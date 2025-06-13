<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventro - Manajemen Stok Profesional untuk UMKM</title>
    <meta name="description" content="Solusi manajemen persediaan modern dengan metode FIFO. Ideal untuk UMKM produk makanan, obat-obatan, dan barang dengan masa kadaluwarsa.">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        // Konfigurasi Tailwind CSS untuk tema cerah
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Warna Biru Profesional sebagai Aksen Utama
                        primary: '#4f46e5', // Indigo-600
                        'primary-hover': '#6366f1', // Indigo-500
                        
                        // Warna Teks
                        'heading': '#1e293b', // Slate-800
                        'paragraph': '#475569', // Slate-600
                        'subtle-text': '#64748b', // Slate-500
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Styling dasar dan latar belakang pola titik halus */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc; /* Slate-50, hampir putih */
            color: #475569; /* paragraph */
        }

        .dot-background {
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="antialiased">

    <header class="bg-white/80 backdrop-blur-sm sticky top-0 z-50 transition-shadow duration-300 border-b border-slate-200">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-primary">
                Inventro
            </a>
            <div>
                <a href="Auth/login.php" class="bg-primary text-white font-semibold py-2 px-5 rounded-md hover:bg-primary-hover transition duration-300">
                    Login
                </a>
            </div>
        </nav>
    </header>

    <main class="relative overflow-hidden">
        <div class="dot-background absolute inset-0 z-0"></div>
        <div class="container relative mx-auto px-6 pt-20 pb-24 text-center animate-fade-in-up z-10">
            <h1 class="text-4xl md:text-6xl font-extrabold text-heading leading-tight tracking-tight">
                Manajemen Stok <span class="text-primary">FIFO</span> untuk Bisnis Modern
            </h1>
            <p class="mt-6 text-lg md:text-xl text-paragraph max-w-3xl mx-auto">
                Solusi inventaris cerdas untuk UMKM dengan produk berbatas waktu. Hindari kerugian, tingkatkan efisiensi, dan maksimalkan profit.
            </p>
            <div class="mt-10 flex justify-center gap-4">
                <a href="Auth/login.php" class="bg-primary text-white font-bold py-3 px-8 rounded-full shadow-lg shadow-primary/20 hover:bg-primary-hover transform hover:-translate-y-0.5 transition-all duration-300">
                    Coba Gratis
                </a>
            </div>

            <div class="relative mt-20 max-w-4xl mx-auto">
                <div class="bg-white rounded-xl shadow-2xl p-4 border border-slate-200">
                    <div class="h-64 bg-slate-100 rounded-lg flex items-center justify-center">
                        <p class="text-slate-400 font-medium">Visualisasi Dasbor Aplikasi Anda</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <section id="fitur" class="bg-white py-24 border-t border-slate-200">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16 animate-fade-in-up">
                <h2 class="text-3xl font-bold text-heading">Dirancang untuk Pertumbuhan Bisnis Anda</h2>
                <p class="text-paragraph mt-2">Fitur-fitur unggulan yang memberikan solusi nyata.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-md hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                    <div class="bg-primary/10 text-primary w-14 h-14 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0011.664 0l3.181-3.183m-11.664 0l3.181-3.183A8.25 8.25 0 006.82 6.82l-3.182 3.182" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-heading">Stok Perpetual Real-time</h3>
                    <p class="mt-2 text-paragraph">Data stok diperbarui secara instan setiap ada transaksi, memberi Anda kontrol penuh dan akurat atas persediaan.</p>
                </div>

                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-md hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                     <div class="bg-primary/10 text-primary w-14 h-14 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0h18M8.625 12h.008v.008H8.625V12zm3.75 0h.008v.008h-.008V12zm3.75 0h.008v.008h-.008V12z" />
                        </svg>
                     </div>
                     <h3 class="mt-6 text-xl font-bold text-heading">Otomatisasi Metode FIFO</h3>
                     <p class="mt-2 text-paragraph">Sistem cerdas kami memprioritaskan penjualan produk berdasarkan tanggal masuk untuk meminimalkan risiko kadaluwarsa.</p>
                </div>

                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-md hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                     <div class="bg-primary/10 text-primary w-14 h-14 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                             <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                         </svg>
                     </div>
                     <h3 class="mt-6 text-xl font-bold text-heading">Laporan Valuasi Akurat</h3>
                     <p class="mt-2 text-paragraph">Dapatkan laporan nilai persediaan yang presisi untuk analisis keuangan dan pengambilan keputusan strategis yang lebih baik.</p>
                </div>

            </div>
        </div>
    </section>

    <footer class="bg-slate-100">
        <div class="container mx-auto px-6 py-8 text-center text-subtle-text">
            <p>© 2025 Inventro. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>