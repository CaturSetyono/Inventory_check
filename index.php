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
    <link rel="stylesheet" href="./app/asset/css/style.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        // Konfigurasi Tailwind CSS untuk desain yang elegan dan minimalis
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-blue': '#3B82F6', // Biru yang tenang sebagai aksen utama
                        'base-100': '#FFFFFF', // Putih bersih untuk latar utama
                        'base-200': '#F8FAFC', // Abu-abu sangat muda untuk latar sekunder
                        'base-300': '#F1F5F9', // Abu-abu muda untuk border
                        'text-primary': '#1E293B', // Teks utama (abu-abu gelap)
                        'text-secondary': '#64748B', // Teks sekunder
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'], // Menggunakan Inter untuk semua teks
                    },
                    boxShadow: {
                        'subtle': '0 4px 12px rgba(0, 0, 0, 0.05)',
                        'subtle-hover': '0 6px 16px rgba(0, 0, 0, 0.07)',
                    },
                    borderRadius: {
                        'lg': '0.75rem',
                        'xl': '1rem',
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                    },
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
</head>

<body class="bg-base-100">

    <header class="bg-base-100/80 backdrop-blur-lg sticky top-0 z-50 border-b border-base-300">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="./" class="text-xl font-bold text-text-primary">
                IKU Inc.
            </a>
            <a href="Auth/login.php" class="bg-brand-blue text-white font-medium text-sm px-4 py-2 rounded-lg hover:bg-opacity-90 transition-colors duration-300">
                Masuk
            </a>
        </nav>
    </header>

    <main class="relative flex items-center justify-center min-h-[75vh] md:min-h-[80vh] pt-20 pb-24 text-center text-white overflow-hidden">
        <div class="hero-video-container">
            <video class="hero-video" autoplay loop muted playsinline>
                <source src="./app/asset/video/hero_animation1.mp4" type="video/mp4">
                Browser Anda tidak mendukung tag video.
            </video>
            <div class="hero-overlay"></div>
        </div>
        <div class="container relative mx-auto px-6 z-10 animate-fade-in-up">
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
                <h2 class="text-3xl md:text-4xl">Fitur Intuitif untuk Bisnis Modern</h2>
                <p class="mt-4 text-lg">Semua yang Anda butuhkan untuk kendali penuh atas inventaris Anda, dalam satu dashboard yang rapi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-base-100 p-8 rounded-xl border border-base-300 shadow-subtle hover:shadow-subtle-hover hover:-translate-y-1 transition-all duration-300 scroll-reveal">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 text-brand-blue rounded-lg mb-5">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="text-xl mb-2">Otomatisasi FIFO Cerdas</h3>
                    <p>Kurangi pemborosan dengan sistem yang secara otomatis memprioritaskan stok terlama.</p>
                </div>
                <div class="bg-base-100 p-8 rounded-xl border border-base-300 shadow-subtle hover:shadow-subtle-hover hover:-translate-y-1 transition-all duration-300 scroll-reveal" style="transition-delay: 100ms;">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 text-brand-blue rounded-lg mb-5">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="text-xl mb-2">Prediksi Permintaan</h3>
                    <p>Dapatkan rekomendasi restock berbasis data untuk menghindari kehabisan atau kelebihan stok.</p>
                </div>
                <div class="bg-base-100 p-8 rounded-xl border border-base-300 shadow-subtle hover:shadow-subtle-hover hover:-translate-y-1 transition-all duration-300 scroll-reveal" style="transition-delay: 200ms;">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 text-brand-blue rounded-lg mb-5">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl mb-2">Laporan Analitik</h3>
                    <p>Visualisasikan data performa stok Anda untuk pengambilan keputusan yang lebih baik.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 md:py-32 bg-base-100">
        <div class="container mx-auto px-6 text-center scroll-reveal">
            <h2 class="text-3xl md:text-4xl max-w-xl mx-auto">Siap bertumbuh bersama kami?</h2>
            <p class="mt-4 text-lg max-w-2xl mx-auto">
                Tinggalkan cara manual, Mulai optimalkan profit bisnis Anda hari ini dengan IKU Inc.
            </p>
            <div class="mt-10">
                <a href="https://wa.me/6281393667609?text=Saya%20Tertarik%20dengan%20IKU%20Inc%2C%20bisa%20bantu%20jelaskan%20lebih%20lanjut%3F" target="_blank" class="bg-brand-blue text-white font-semibold text-lg py-3 px-10 rounded-lg shadow-lg hover:bg-opacity-90 transition-colors transform hover:-translate-y-1 duration-300 inline-flex items-center">
                    Dapatkan Penawaran
                </a>
            </div>
        </div>
    </section>

    <footer class="bg-base-200 border-t border-base-300 text-text-secondary">
        <div class="container mx-auto px-6 py-12 text-center">
            <p class="text-sm">&copy; <?= date("Y ") ?>IKU Inc. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            }); // Muncul saat 10% elemen terlihat

            document.querySelectorAll('.scroll-reveal').forEach(el => {
                observer.observe(el);
            });
        });
    </script>

</body>

</html>