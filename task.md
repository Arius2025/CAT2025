# Task: Peningkatan Sistem Ujian (Auto-Save, Analitik, Leaderboard, Gambar)

## 1. Auto-Save & Soft Anti-Cheat
- [ ] Modifikasi `isian.blade.php`: Tambahkan listener Javascript untuk menyimpan setiap perubahan jawaban ke `localStorage`.
- [ ] Modifikasi `isian.blade.php`: Tambahkan interval penyimpan sisa detik (`timer`) ke `localStorage`.
- [ ] Integrasikan "Soft Warning" (Toast notification) jika user `blur` dari window browser.

## 2. Analitik Kelemahan (Radar Chart) & Manajemen Waktu
- [ ] Buat file migration: Tambahkan kolom `analitik_topik` (JSON format) pada tabel `ujian_histories`.
- [ ] Jalankan `php artisan migrate`.
- [ ] Update `UjianController@submit`: Kalkulasi persentase benar untuk tiap `sub_topik` yang dijawab dan simpan rekap data tersebut.
- [ ] Modifikasi `hasil.blade.php`: Tambahkan library `Chart.js` via CDN.
- [ ] Gambar Radar Chart kelemahan berdasar `analitik_topik` di halaman Hasil Ujian.

## 3. Papan Peringkat (Global Leaderboard)
- [ ] Tambahkan method `leaderboard` pada Controller (ex: [UjianController](file:///c:/laragon9/www/cat-cpns/app/Http/Controllers/UjianController.php#10-475)).
- [ ] Buat view baru `resources/views/ujian/leaderboard.blade.php` dengan list/table peserta.
- [ ] Daftarkan route `/ujian/leaderboard`.
- [ ] Tambahkan tombol/icon navigasi "Papan Peringkat" di dashboard user.

## 4. Dukungan Gambar pada Soal (Figural TIU)
- [ ] Buat file migration: Tambahkan kolom `gambar_path` (nullable string) pada tabel `soals`.
- [ ] Jalankan `php artisan migrate`.
- [ ] Sesuaikan view `isian.blade.php` (tampilan soal) untuk me-render `<img src="gambar_path">` tepat di bawah `teks_soal` jika isinya tidak null.
- [ ] Tambahkan Endpoint/UI sederhana bagi admin di Bank Soal (jika ada) atau sekadar fitur backend, agar soal figural bisa diupdate manual gambarnya.
