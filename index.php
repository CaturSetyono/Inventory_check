<?php
/**
 * index.php
 *
 * Ini adalah halaman utama (landing page) dari aplikasi IKU Inc.
 * Halaman ini bersifat statis dan bertujuan untuk memberikan informasi umum,
 * menampilkan fitur-fitur utama, serta menyediakan tautan untuk masuk (login)
 * ke dalam sistem atau menghubungi pihak pengembang.
 *
 * Halaman ini menggunakan:
 * - Tailwind CSS untuk styling.
 * - Alpine.js untuk interaktivitas UI sederhana (efek header saat scroll).
 * - Font Awesome untuk ikon.
 * - Google Fonts (Inter) untuk tipografi.
 * - JavaScript native dengan IntersectionObserver untuk animasi saat scroll.
 */
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKU - Solusi Inventaris Profesional</title>
    <meta name="description" content="Platform manajemen inventaris yang intuitif dan modern untuk optimasi bisnis Anda.">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        // Konfigurasi ini memperluas tema default Tailwind CSS dengan palet warna,
        // jenis font, dan efek animasi yang spesifik untuk branding IKU Inc.
        tailwind.config = {
            theme: {
                extend: {
                    // Palet warna kustom untuk konsistensi desain
                    colors: {
                        'brand-blue': '#3B82F6', // Biru sebagai warna aksen utama
                        'base-100': '#FFFFFF', // Putih untuk latar belakang utama
                        'base-200': '#F8FAFC', // Abu-abu sangat terang untuk latar sekunder
                        'base-300': '#F1F5F9', // Abu-abu muda untuk border dan pemisah
                        'text-primary': '#1E293B', // Warna teks utama (abu-abu gelap)
                        'text-secondary': '#64748B', // Warna teks sekunder atau tambahan
                    },
                    // Mengatur font default menjadi 'Inter'
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                    // Efek bayangan kustom
                    boxShadow: {
                        'subtle': '0 4px 12px rgba(0, 0, 0, 0.05)',
                        'subtle-hover': '0 6px 16px rgba(0, 0, 0, 0.07)',
                    },
                    // Ukuran radius sudut kustom
                    borderRadius: {
                        'lg': '0.75rem',
                        'xl': '1rem',
                    },
                    // Definisi animasi kustom
                    animation: {
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                    },
                    // Keyframes untuk animasi 'fadeInUp'
                    keyframes: {
                        fadeInUp: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(20px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            },
                        },
                    },
                }
            }
        }
    </script>

    <style>
        /* Pengaturan z-index untuk memastikan tumpukan elemen yang benar.
           Header harus selalu di atas, diikuti konten, dan video di paling belakang.
           Class ini tidak digunakan secara langsung, namun menjadi referensi struktur z-index.
        */
        .header-wrapper {
            position: relative;
            z-index: 50;
        }

        .content-wrapper {
            position: relative;
            z-index: 10;
        }

        .video-background {
            position: absolute;
            z-index: 1;
        }
    </style>
</head>

<body class="bg-base-100 font-sans" x-data="{ scrolledFromTop: false }" @scroll.window="scrolledFromTop = (window.scrollY > 50)">

    <div class="fixed top-0 left-0 w-full h-screen -z-10">
        <video class="w-full h-full object-cover" autoplay loop muted playsinline>
            <source src="./app/asset/video/hero_animation.mp4" type="video/mp4">
            Browser Anda tidak mendukung tag video.
        </video>
        <div class="absolute inset-0 bg-black/50"></div>
    </div>

    <header class="sticky top-0 z-50 transition-colors duration-300" :class="{ 'bg-base-100/80 backdrop-blur-lg border-b border-base-300': scrolledFromTop, 'bg-transparent border-b border-transparent': !scrolledFromTop }">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="./" class="text-xl font-bold transition-colors duration-300" :class="{ 'text-white': !scrolledFromTop, 'text-text-primary': scrolledFromTop }">
                IKU Inc.
            </a>
            <a href="Auth/login.php" class="font-semibold text-sm text-white px-5 py-2.5 rounded-lg bg-white/20 backdrop-blur-md border border-white/30 shadow-lg hover:bg-white/30 hover:-translate-y-px transform transition-all duration-300 ease-in-out" :class="{ 'text-white': !scrolledFromTop, 'text-text-primary': scrolledFromTop }">
                Masuk
            </a>
        </nav>
    </header>

    <div class="relative z-10">
        <main class="flex flex-col items-center justify-center h-screen text-center text-white">
            <div class="container relative mx-auto px-6 animate-fade-in-up">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-semibold leading-tight md:leading-snug">
                    Manajemen Inventaris, <br class="hidden md:block"> mudah dengan akses yang <span class="text-blue-300">Real Time</span>.
                </h1>
                <p class="mt-4 max-w-xl mx-auto text-base md:text-lg text-slate-200">
                    Ubah cara Anda mengelola stok dengan platform cerdas yang memprediksi, mengotomatisasi, dan mengoptimalkan.
                </p>
                <div class="mt-8">
                    <a href="#fitur" class="bg-white text-brand-blue font-semibold px-8 py-3 rounded-lg shadow-lg hover:bg-slate-100 transition-all duration-300 transform hover:-translate-y-1">
                        Pelajari Fitur
                    </a>
                </div>
            </div>
        </main>

        <section id="fitur" class="py-24 md:py-32 bg-base-200">
            <div class="container mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-16 scroll-reveal">
                    <h2 class="text-3xl md:text-4xl text-text-primary">Fitur Intuitif untuk Bisnis Modern</h2>
                    <p class="mt-4 text-lg text-text-secondary">Semua yang Anda butuhkan untuk kendali penuh atas inventaris Anda, dalam satu dashboard yang rapi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-base-100 p-8 rounded-xl border border-base-300 shadow-subtle hover:shadow-subtle-hover hover:-translate-y-1 transition-all duration-300 scroll-reveal">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 text-brand-blue rounded-lg mb-5">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3 class="text-xl mb-2 text-text-primary">Otomatisasi FIFO Cerdas</h3>
                        <p class="text-text-secondary">Kurangi pemborosan dengan sistem yang secara otomatis memprioritaskan stok terlama.</p>
                    </div>
                    <div class="bg-base-100 p-8 rounded-xl border border-base-300 shadow-subtle hover:shadow-subtle-hover hover:-translate-y-1 transition-all duration-300 scroll-reveal" style="transition-delay: 100ms;">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 text-brand-blue rounded-lg mb-5">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3 class="text-xl mb-2 text-text-primary">Prediksi Permintaan</h3>
                        <p class="text-text-secondary">Dapatkan rekomendasi restock berbasis data untuk menghindari kehabisan atau kelebihan stok.</p>
                    </div>
                    <div class="bg-base-100 p-8 rounded-xl border border-base-300 shadow-subtle hover:shadow-subtle-hover hover:-translate-y-1 transition-all duration-300 scroll-reveal" style="transition-delay: 200ms;">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 text-brand-blue rounded-lg mb-5">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="text-xl mb-2 text-text-primary">Laporan Analitik</h3>
                        <p class="text-text-secondary">Visualisasikan data performa stok Anda untuk pengambilan keputusan yang lebih baik.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-24 md:py-32 bg-base-100">
            <div class="container mx-auto px-6 text-center scroll-reveal">
                <h2 class="text-3xl md:text-4xl max-w-xl mx-auto text-text-primary">Siap bertumbuh bersama kami?</h2>
                <p class="mt-4 text-lg max-w-2xl mx-auto text-text-secondary">
                    Tinggalkan cara manual, Mulai optimalkan profit bisnis Anda hari ini dengan IKU Inc.
                </p>
                <div class="mt-10">
                    <a href="https://wa.me/6281393667609?text=Saya%20Tertarik%20dengan%20IKU%20Inc%2C%20bisa%20bantu%20jelaskan%20lebih%20lanjut%3F" target="_blank" class="bg-brand-blue text-white font-semibold text-lg py-3 px-10 rounded-lg shadow-lg hover:bg-opacity-90 transition-colors transform hover:-translate-y-1 duration-300 inline-flex items-center">
                        Dapatkan Penawaran
                    </a>
                </div>
            </div>
        </section>
    </div>

    <footer class="bg-base-200 border-t border-base-300 text-text-secondary">
        <div class="container mx-auto px-6 py-12 text-center">
            <p class="text-sm">&copy; <?= date("Y") ?> IKU Inc. All rights reserved.</p>
        </div>
    </footer>
    

</body>

</html>