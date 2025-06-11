<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventro - Solusi Manajemen Inventaris Anda</title>
    
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5', // Indigo-600
                        secondary: '#10B981', // Emerald-500
                    }
                }
            }
        }
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Menggunakan font Inter jika tersedia */
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-primary">
                Inventro
            </a>
            <div>
                <a href="#fitur" class="text-gray-600 hover:text-primary px-4">Fitur</a>
                <a href="Auth/login.php" class="bg-primary text-white font-semibold py-2 px-4 rounded-lg hover:bg-opacity-90 transition duration-300 ml-4">
                    Login
                </a>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-24 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight">
            Manajemen Inventaris Menjadi <span class="text-primary">Lebih Mudah</span> dan <span class="text-primary">Efisien</span>
        </h1>
        <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto">
            Lacak stok barang, kelola pesanan, dan dapatkan laporan akurat dalam satu platform yang intuitif. Fokus pada bisnis Anda, biarkan kami yang mengurus inventaris.
        </p>
        <div class="mt-10">
            <a href="Auth/login.php" class="bg-primary text-white font-bold py-4 px-10 rounded-lg shadow-lg hover:bg-opacity-90 transform hover:-translate-y-1 transition-all duration-300">
                Get Started for Free
            </a>
        </div>
    </main>

    <section id="fitur" class="bg-white py-20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Fitur Unggulan Kami</h2>
                <p class="text-gray-600 mt-2">Semua yang Anda butuhkan untuk mengoptimalkan manajemen stok.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                
                <div class="p-8 border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="bg-primary/10 text-primary w-12 h-12 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M5.5 9.5L14.5 18.5M14.5 4h5v5" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-gray-900">Real-time Stock Tracking</h3>
                    <p class="mt-2 text-gray-600">Pantau jumlah stok secara langsung saat terjadi penjualan atau penerimaan barang untuk menghindari kehabisan atau kelebihan stok.</p>
                </div>

                <div class="p-8 border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300">
                     <div class="bg-primary/10 text-primary w-12 h-12 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-gray-900">Manajemen Produk & Supplier</h3>
                    <p class="mt-2 text-gray-600">Kelola semua informasi produk, kategori, dan data supplier dalam satu tempat yang terorganisir dengan rapi.</p>
                </div>

                <div class="p-8 border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300">
                     <div class="bg-primary/10 text-primary w-12 h-12 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-gray-900">Laporan Analitis</h3>
                    <p class="mt-2 text-gray-600">Dapatkan wawasan mendalam tentang pergerakan stok, produk terlaris, dan valuasi inventaris untuk pengambilan keputusan yang lebih baik.</p>
                </div>

            </div>
        </div>
    </section>

    <footer class="bg-gray-800 text-white">
        <div class="container mx-auto px-6 py-8 text-center">
            <p class="text-gray-400">© 2025 Inventro. All Rights Reserved.</p>
            <p class="text-gray-500 mt-2">Dibuat untuk menyederhanakan hidup Anda.</p>
        </div>
    </footer>

</body>
</html>