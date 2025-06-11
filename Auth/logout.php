<?php
// Selalu mulai sesi di awal untuk mengakses data sesi.
session_start();

// 1. Hapus semua variabel sesi.
// Ini akan mengosongkan array $_SESSION.
session_unset();

// 2. Hancurkan sesi.
// Ini akan menghapus semua data yang terkait dengan sesi pengguna di server.
session_destroy();

// 3. Alihkan (redirect) pengguna ke halaman login.
// Setelah sesi dihancurkan, pengguna tidak lagi dianggap login,
// jadi kita arahkan kembali ke halaman login.
header('Location: ../');

// 4. Pastikan tidak ada kode lain yang dieksekusi setelah redirect.
exit;
?>