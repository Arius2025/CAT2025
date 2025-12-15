Markdown
# 🚀 Aplikasi Simulasi CAT CPNS 2024 (Laravel Version)

Aplikasi berbasis web untuk latihan ujian CPNS sesuai dengan standar **BKN 2024**. Proyek ini mencakup sistem penilaian otomatis, manajemen waktu (timer), ambang batas (*passing grade*), dan fitur cetak pembahasan dalam format PDF.

---

## ✨ Fitur Utama
* **110 Soal Standar BKN:** Terbagi menjadi TWK (30), TIU (35), dan TKP (45).
* **Timer Real-time:** Batas waktu 100 menit dengan fitur *auto-submit* jika waktu habis.
* **Sistem Penilaian Otomatis:**
    * **TWK & TIU:** Benar (+5), Salah (0).
    * **TKP:** Skala poin (1-5) menggunakan penyimpanan format JSON.
* **Navigasi Soal Interaktif:** Panel nomor soal dengan indikator warna:
    * 🟦 **Biru:** Sedang dikerjakan.
    * 🟩 **Hijau:** Sudah dijawab.
    * 🟥 **Merah:** Sudah dibuka tapi kosong.
    * ⬜ **Abu-abu:** Belum dibuka.
* **Analisis Hasil (Passing Grade):** Kelulusan instan berdasarkan ambang batas resmi.
* **Cetak Pembahasan:** Fitur ekspor pembahasan soal ke format PDF.

---

## 🛠️ Teknologi
* **Backend:** Laravel 10/11
* **Database:** MySQL
* **Frontend:** Bootstrap 5, Vanilla JavaScript (Timer & UI logic)
* **PDF Engine:** [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)

---

## 📦 Instalasi & Penggunaan

1. Clone Repositori
bash
git clone [https://github.com/username-kamu/nama-proyek.git](https://github.com/username-kamu/nama-proyek.git)
cd nama-proyek

2. Instalasi Dependency
Bash
composer install
npm install && npm run dev

3. Konfigurasi Database
Salin file .env.example menjadi .env dan sesuaikan koneksi database (DB_DATABASE, DB_USERNAME, DB_PASSWORD).
Bash
cp .env.example .env
php artisan key:generate

4. Migrasi & Seeding (Penting!)
Jalankan migrasi untuk membuat tabel dan masukkan 110 soal ke dalam database menggunakan seeder yang telah kita buat.
Bash
php artisan migrate
php artisan db:seed --class=PaketDuaSeeder

5. Jalankan Server
Bash
php artisan serve
Akses aplikasi di: http://127.0.0.1:8000

📋 Struktur Tabel Soal
Soal dikelola dalam tabel questions dengan skema: | Kolom | Tipe | Deskripsi | |---|---|---| | category | String | TWK, TIU, atau TKP | | question_text | Text | Isi pertanyaan | | option_a - option_e | Text | Pilihan jawaban | | correct_answer | Char | Kunci jawaban (A-E) | | tkp_points | JSON | Poin khusus soal TKP (Skala 1-5) |
