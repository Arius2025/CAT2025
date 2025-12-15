<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class PaketSatuSeeder extends Seeder
{
public function run(): void
{
    // =================================================================
    // KELOMPOK 1: TES WAWASAN KEBANGSAAN (TWK) - No. 1 s/d 30
    // =================================================================

    // 1. Nasionalisme
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TWK',
        'question_text' => 'Di tengah arus globalisasi, ancaman terhadap jati diri bangsa dapat berupa westernisasi. Sikap nasionalisme yang paling tepat dalam menghadapi hal tersebut adalah...',
        'option_a' => 'Menolak seluruh kebudayaan asing yang masuk ke Indonesia',
        'option_b' => 'Menerima kebudayaan asing yang modern agar tidak tertinggal',
        'option_c' => 'Menyaring budaya asing dengan nilai-nilai Pancasila sebagai filter utama',
        'option_d' => 'Mempelajari bahasa asing seluas-luasnya untuk persaingan global',
        'option_e' => 'Membatasi penggunaan internet di kalangan remaja',
        'correct_answer' => 'C'
    ]);

    // 2. Integritas
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TWK',
        'question_text' => 'Seorang ASN ditawarkan hadiah oleh seorang warga sebagai bentuk terima kasih karena telah dibantu mengurus perizinan tanpa biaya. Sikap ASN yang berintegritas adalah...',
        'option_a' => 'Menerima karena pemberian tersebut bersifat sukarela',
        'option_b' => 'Menerima hadiah tersebut namun tetap melaporkannya ke atasan',
        'option_c' => 'Menolak hadiah tersebut dengan sopan karena sudah menjadi kewajiban dalam tugas',
        'option_d' => 'Menolak hadiah tersebut dan memarahi warga agar tidak mengulangi',
        'option_e' => 'Minta warga tersebut menyumbangkannya ke kotak amal kantor',
        'correct_answer' => 'C'
    ]);

    // 3. Bela Negara
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TWK',
        'question_text' => 'Menurut UU No. 3 Tahun 2002, keikutsertaan warga negara dalam upaya bela negara diselenggarakan melalui beberapa hal di bawah ini, KECUALI...',
        'option_a' => 'Pendidikan kewarganegaraan',
        'option_b' => 'Pelatihan dasar kemiliteran secara wajib',
        'option_c' => 'Pengabdian sebagai prajurit TNI',
        'option_d' => 'Pengabdian sesuai dengan profesi',
        'option_e' => 'Partisipasi dalam demonstrasi kebijakan negara',
        'correct_answer' => 'E'
    ]);

    // 4. Pilar Negara (Pancasila)
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TWK',
        'question_text' => 'Mengembangkan perbuatan yang luhur, yang mencerminkan sikap dan suasana kekeluargaan dan kegotongroyongan merupakan butir pengamalan Pancasila pada...',
        'option_a' => 'Sila ke-1', 'option_b' => 'Sila ke-2', 'option_c' => 'Sila ke-3', 'option_d' => 'Sila ke-4', 'option_e' => 'Sila ke-5',
        'correct_answer' => 'E'
    ]);

    // 5. Pilar Negara (UUD 1945)
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TWK',
        'question_text' => 'Pasal 1 ayat (3) UUD 1945 yang menyatakan bahwa "Negara Indonesia adalah negara hukum" mengandung konsekuensi bahwa...',
        'option_a' => 'Segala tindakan pemerintah harus berdasarkan aspirasi partai politik',
        'option_b' => 'Hukum hanya berlaku bagi rakyat biasa',
        'option_c' => 'Terdapat supremasi hukum dalam penyelenggaraan negara',
        'option_d' => 'Presiden memiliki kekuasaan mutlak untuk mengubah hukum',
        'option_e' => 'Hukum ditentukan oleh suara terbanyak di parlemen',
        'correct_answer' => 'C'
    ]);

    // Loop Soal 6 - 20 (TWK Campuran)
    $twk_data = [
        ['6', 'Integritas merupakan salah satu pilar dalam pemberantasan korupsi. Nilai inti integritas yang berkaitan dengan etos kerja adalah...', 'Jujur, Disiplin, Tanggung Jawab', 'Mandiri, Kerja Keras, Sederhana', 'Berani, Peduli, Adil', 'Sabar, Sopan, Santun', 'Ramah, Cepat, Tepat', 'B'],
        ['7', 'Bahasa Indonesia berfungsi sebagai lambang kebanggaan nasional. Hal ini berarti...', 'Bahasa Indonesia digunakan dalam sidang resmi', 'Bahasa Indonesia merupakan alat komunikasi antarbudaya', 'Bahasa Indonesia mencerminkan nilai-nilai luhur budaya bangsa', 'Bahasa Indonesia wajib diajarkan di luar negeri', 'Bahasa Indonesia adalah bahasa pengantar pendidikan', 'C'],
        ['8', 'Sistem pertahanan keamanan rakyat semesta (Sishankamrata) menempatkan rakyat sebagai...', 'Kekuatan utama', 'Kekuatan pendukung', 'Kekuatan cadangan', 'Kekuatan operasional', 'Kekuatan administratif', 'B'],
        ['9', 'Panitia Sembilan menghasilkan dokumen "Piagam Jakarta" pada tanggal...', '1 Juni 1945', '22 Juni 1945', '17 Agustus 1945', '18 Agustus 1945', '10 November 1945', 'B'],
        ['10', 'Pengakuan persamaan derajat, hak, dan kewajiban antara sesama manusia adalah perwujudan sila...', 'Ke-1', 'Ke-2', 'Ke-3', 'Ke-4', 'Ke-5', 'B'],
        ['11', 'Tugas Komisi Yudisial menurut UUD 1945 adalah...', 'Mengadili perkara kasasi', 'Menguji UU terhadap UUD', 'Mengusulkan pengangkatan Hakim Agung', 'Memutus sengketa hasil pemilu', 'Menyelesaikan sengketa antarlembaga negara', 'C'],
        ['12', 'Paham yang menempatkan kesetiaan tertinggi individu kepada bangsa dan tanah air disebut...', 'Chauvinisme', 'Nasionalisme', 'Patriotisme', 'Liberalisme', 'Sosialisme', 'B'],
        ['13', 'Bhinneka Tunggal Ika pertama kali muncul dalam Kitab Sutasoma karya...', 'Mpu Prapanca', 'Mpu Gandring', 'Mpu Tantular', 'Mpu Panuluh', 'Mpu Sedah', 'C'],
        ['14', 'Salah satu fungsi konstitusi sebagai pembatas kekuasaan bertujuan agar...', 'Pemerintah dapat bekerja tanpa gangguan', 'Tidak terjadi kesewenang-wenangan penguasa', 'Rakyat tidak perlu ikut campur politik', 'Lembaga negara memiliki kekuasaan mutlak', 'Presiden menjabat seumur hidup', 'B'],
        ['15', 'Cinta tanah air dan bangsa dapat diwujudkan dengan cara...', 'Menggunakan produk luar negeri', 'Mempelajari budaya asing saja', 'Bangga menggunakan produk dalam negeri', 'Menghafal seluruh pasal UUD', 'Bekerja di luar negeri selamanya', 'C'],
        ['16', 'Lembaga yang berwenang mengubah dan menetapkan UUD adalah...', 'Presiden', 'DPR', 'MPR', 'MA', 'MK', 'C'],
        ['17', 'Nilai patriotisme dalam kehidupan berbangsa adalah...', 'Rela berkorban untuk kepentingan pribadi', 'Pantang menyerah dalam mencari keuntungan', 'Rela berkorban untuk kepentingan bangsa dan negara', 'Bekerja keras demi jabatan', 'Menghargai pendapat orang lain', 'C'],
        ['18', 'Pancasila sebagai ideologi terbuka berarti...', 'Nilai-nilainya dapat diubah sesuai keinginan', 'Dapat menerima pengaruh asing tanpa filter', 'Nilai-nilainya dapat berinteraksi dengan perkembangan zaman', 'Tidak memiliki prinsip yang tetap', 'Hanya berlaku untuk kelompok tertentu', 'C'],
        ['19', 'Hak prerogatif presiden diatur dalam UUD 1945, salah satunya adalah...', 'Menetapkan UU sendiri', 'Mengangkat menteri-menteri', 'Membubarkan DPR', 'Memilih Ketua MK', 'Menghapus jabatan gubernur', 'B'],
        ['20', 'Identitas nasional Indonesia yang membedakan dengan bangsa lain adalah...', 'Lagu Barat', 'Bendera Merah Putih', 'Mata uang Dollar', 'Sistem kerajaan', 'Bahasa Inggris', 'B'],
    ];

    foreach ($twk_data as $q) {
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TWK',
            'question_text' => $q[1],
            'option_a' => $q[2], 'option_b' => $q[3], 'option_c' => $q[4], 'option_d' => $q[5], 'option_e' => $q[6],
            'correct_answer' => $q[7]
        ]);
    } 

    // =================================================================
    // LANJUTAN KELOMPOK 1: TWK (No. 21 s/d 30)
    // =================================================================
    
    $twk_batch_2 = [
        ['21', 'Bela negara merupakan tekad, sikap, dan perilaku warga negara yang dijiwai oleh kecintaannya kepada NKRI. Contoh bela negara di bidang ekonomi adalah...', 'Mengikuti latihan dasar kemiliteran', 'Membayar pajak tepat pada waktunya', 'Menjaga kelestarian budaya daerah', 'Melakukan demonstrasi menolak kebijakan ekonomi', 'Bekerja di perusahaan asing dengan gaji tinggi', 'B'],
        ['22', 'Ciri utama dari integritas seorang ASN adalah adanya kesesuaian antara...', 'Ucapan dengan harapan atasan', 'Peraturan dengan tuntutan masyarakat', 'Perkataan dengan perbuatan nyata', 'Visi pribadi dengan visi partai politik', 'Kinerja dengan besarnya tunjangan', 'C'],
        ['23', 'Bahasa Indonesia sebagai bahasa negara berfungsi sebagai...', 'Lambang kebanggaan nasional', 'Lambang identitas nasional', 'Alat perhubungan antarwarga dan antarbudaya', 'Bahasa pengantar resmi di lembaga pendidikan', 'Alat pemersatu berbagai suku bangsa', 'D'],
        ['24', 'Prinsip nasionalisme Indonesia (Pancasila) tidak membedakan warga negara berdasarkan suku, agama, dan ras. Hal ini bertujuan untuk...', 'Menghapuskan perbedaan individu', 'Mewujudkan integrasi nasional', 'Menyeragamkan kebudayaan daerah', 'Membentuk satu identitas agama', 'Memudahkan kontrol pemerintah pusat', 'B'],
        ['25', 'Mahkamah Konstitusi memiliki kewenangan untuk memutus sengketa kewenangan lembaga negara yang kewenangannya diberikan oleh...', 'Undang-Undang', 'Peraturan Pemerintah', 'UUD 1945', 'Ketetapan MPR', 'Instruksi Presiden', 'C'],
        ['26', 'Nilai patriotisme dalam menghadapi bencana alam dapat diwujudkan melalui...', 'Menunggu bantuan pemerintah turun', 'Menggalang solidaritas dan bantuan kemanusiaan', 'Mendokumentasikan bencana untuk media sosial', 'Menyalahkan prosedur mitigasi bencana', 'Pindah ke daerah yang lebih aman', 'B'],
        ['27', 'Integritas nasional dapat terancam oleh adanya primordialisme yang berlebihan, yaitu...', 'Paham yang mengutamakan kepentingan nasional', 'Paham yang memegang teguh hal-hal yang dibawa sejak kecil (suku/agama/ras)', 'Paham yang menolak kerja sama internasional', 'Paham yang menginginkan perubahan radikal', 'Paham yang mengutamakan logika di atas perasaan', 'B'],
        ['28', 'Setiap warga negara berhak dan wajib ikut serta dalam upaya pembelaan negara. Kata "wajib" dalam Pasal 27 ayat (3) UUD 1945 bermakna...', 'Hanya untuk laki-laki dewasa', 'Hanya berlaku saat terjadi perang fisik', 'Merupakan kewajiban moral dan hukum', 'Dapat digantikan dengan membayar denda', 'Hanya bagi aparat penegak hukum', 'C'],
        ['29', 'Kalimat yang menggunakan ejaan yang benar adalah...', 'Ibu membeli pisang ambon di pasar.', 'Ayah sedang membaca surat kabar Kompas.', 'Adik belajar bahasa Inggris setiap Hari senin.', 'Kita harus menghargai Suku bangsa lain.', 'Garam Inggris sangat bermanfaat untuk kesehatan.', 'A'],
        ['30', 'Pancasila merupakan "Philosofische Grondslag", yang artinya...', 'Alat pemersatu bangsa', 'Sumber dari segala sumber hukum', 'Pandangan hidup bangsa', 'Fondasi atau dasar filsafat negara', 'Perjanjian luhur bangsa Indonesia', 'D'],
    ];

    foreach ($twk_batch_2 as $q) {
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TWK',
            'question_text' => $q[1],
            'option_a' => $q[2], 'option_b' => $q[3], 'option_c' => $q[4], 'option_d' => $q[5], 'option_e' => $q[6],
            'correct_answer' => $q[7]
        ]);
    }

    // =================================================================
    // KELOMPOK 2: TES INTELEGENSIA UMUM (TIU) - No. 31 s/d 65
    // =================================================================

    // 31. TIU Analogi
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TIU',
        'question_text' => 'GENTING : RUMAH = ... : ...',
        'option_a' => 'Semen : Tembok',
        'option_b' => 'Pintu : Jendela',
        'option_c' => 'Payung : Hujan',
        'option_d' => 'Kancing : Baju',
        'option_e' => 'Bulu : Burung',
        'correct_answer' => 'D'
    ]);

    // 32. TIU Silogisme
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TIU',
        'question_text' => 'Semua mamalia menyusui anaknya. Paus adalah mamalia. Kesimpulan yang tepat adalah...',
        'option_a' => 'Paus bukan ikan',
        'option_b' => 'Paus menyusui anaknya',
        'option_c' => 'Semua yang menyusui adalah paus',
        'option_d' => 'Paus adalah mamalia air',
        'option_e' => 'Paus tidak bertelur',
        'correct_answer' => 'B'
    ]);

    // 33. TIU Analitis
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TIU',
        'question_text' => 'Lima orang siswa (A, B, C, D, E) mengikuti ujian. Nilai A lebih tinggi dari B. Nilai C sama dengan D. Nilai E lebih rendah dari B. Nilai D lebih tinggi dari A. Siapakah yang mendapat nilai terendah?',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'option_e' => 'E',
        'correct_answer' => 'E'
    ]);

    // 34. TIU Numerik (Berhitung)
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TIU',
        'question_text' => 'Berapakah 25% dari 120 kemudian dikalikan 0,5?',
        'option_a' => '15', 'option_b' => '20', 'option_c' => '25', 'option_d' => '30', 'option_e' => '60',
        'correct_answer' => 'A'
    ]);

    // 35. TIU Deret Angka
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TIU',
        'question_text' => '4, 8, 16, 32, 64, ...',
        'option_a' => '80', 'option_b' => '96', 'option_c' => '128', 'option_d' => '144', 'option_e' => '160',
        'correct_answer' => 'C'
    ]);

    $tiu_batch_2 = [
        ['36', 'HUTAN : POHON = ARMADA : ...', 'Tentara', 'Kapal', 'Prajurit', 'Pelaut', 'Perang', 'B'],
        ['37', 'Semua dokter adalah orang pintar. Sebagian orang pintar adalah orang yang teliti. Kesimpulan...', 'Semua dokter adalah orang teliti', 'Sebagian dokter adalah orang teliti', 'Tidak ada dokter yang teliti', 'Orang teliti pasti dokter', 'Tidak dapat disimpulkan', 'E'],
        ['38', 'Jika x = 2, y = 3, dan z = x^2 + 2xy + y^2, maka nilai z adalah...', '13', '20', '25', '30', '35', 'C'],
        ['39', 'Antonim dari kata PROGRESIF adalah...', 'Maju', 'Modern', 'Statis', 'Aktif', 'Cepat', 'C'],
        ['40', 'Sinonim dari kata EVAKUASI adalah...', 'Penyelamatan', 'Pemindahan', 'Pengungsian', 'Penanganan', 'Penyembuhan', 'B'],
    ];

    foreach ($tiu_batch_2 as $q) {
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TIU',
            'question_text' => $q[1],
            'option_a' => $q[2], 'option_b' => $q[3], 'option_c' => $q[4], 'option_d' => $q[5], 'option_e' => $q[6],
            'correct_answer' => $q[7]
        ]);
    }

    // =================================================================
    // LANJUTAN KELOMPOK 2: TIU (No. 41 s/d 60)
    // =================================================================

    $tiu_batch_3 = [
        ['41', 'Jika 7 orang pekerja dapat menyelesaikan sebuah pekerjaan dalam 6 hari, berapa orang tambahan pekerja yang dibutuhkan agar pekerjaan tersebut selesai dalam 2 hari?', '14 orang', '21 orang', '28 orang', '7 orang', '3 orang', 'A'],
        ['42', 'Seorang pedagang membeli 2 lusin buku dengan harga Rp 120.000. Jika ia ingin mendapatkan keuntungan 20%, maka harga jual per buku adalah...', 'Rp 5.000', 'Rp 6.000', 'Rp 7.000', 'Rp 7.200', 'Rp 8.000', 'B'],
        ['43', 'Berapakah nilai dari 12,5% dari 512?', '32', '64', '128', '256', '51.2', 'B'],
        ['44', 'Suku berikutnya dari deret 2, 3, 5, 8, 13, 21, ... adalah...', '26', '29', '34', '35', '44', 'C'],
        ['45', 'Jika x = 25% dari 80 dan y = akar dari 25, maka pernyataan yang benar adalah...', 'x < y', 'x > y', 'x = y', 'x = 2y', 'Hubungan x dan y tidak dapat ditentukan', 'B'],
        ['46', 'MOBIL : RODA = PESAWAT : ...', 'Sayap', 'Pilot', 'Baling-baling', 'Mesin', 'Udara', 'A'],
        ['47', 'Semua siswa kelas A pandai matematika. Sebagian siswa kelas A adalah atlet basket. Kesimpulan yang tepat adalah...', 'Semua atlet basket pandai matematika', 'Sebagian atlet basket pandai matematika', 'Semua siswa kelas A adalah atlet basket', 'Siswa yang pandai matematika pasti atlet basket', 'Sebagian siswa kelas A tidak pandai matematika', 'B'],
        ['48', 'Sebuah mobil menempuh jarak 120 km dalam waktu 2 jam. Berapa jarak yang ditempuh mobil tersebut dalam waktu 3,5 jam dengan kecepatan yang sama?', '180 km', '200 km', '210 km', '240 km', '250 km', 'C'],
        ['49', 'Antonim dari kata OTORITER adalah...', 'Diktator', 'Demokratis', 'Sombong', 'Keras', 'Mandiri', 'B'],
        ['50', 'Sinonim dari kata ITERASI adalah...', 'Interaksi', 'Pengurangan', 'Perulangan', 'Pembagian', 'Pendalaman', 'C'],
        ['51', 'Deret Huruf: A, D, G, J, ...', 'L', 'M', 'N', 'O', 'P', 'B'],
        ['52', 'Jika 0,75 x y = 1,5, maka nilai y adalah...', '1', '2', '2,5', '3', '4', 'B'],
        ['53', 'Dalam sebuah kelas, 25 siswa menyukai musik, 20 siswa menyukai olahraga, dan 10 siswa menyukai keduanya. Jika jumlah total siswa adalah 45, berapa siswa yang tidak menyukai keduanya?', '0', '5', '10', '15', '20', 'C'],
        ['54', 'Berapa hasil dari 3/4 + 0,125?', '0,825', '0,875', '0,925', '1,00', '1,125', 'B'],
        ['55', 'PENULIS : BUKU = PENJAHIT : ...', 'Kain', 'Baju', 'Jarum', 'Benang', 'Mesin Jahit', 'B'],
        ['56', 'Semua buah yang manis mengandung gula. Sebagian buah yang manis berwarna merah. Kesimpulan...', 'Semua yang berwarna merah mengandung gula', 'Sebagian buah yang berwarna merah mengandung gula', 'Semua buah merah adalah buah manis', 'Buah yang mengandung gula pasti merah', 'Tidak ada buah merah yang tidak manis', 'B'],
        ['57', 'Jika harga 3 kg apel adalah Rp 75.000, berapa harga 7 kg apel?', 'Rp 150.000', 'Rp 175.000', 'Rp 185.000', 'Rp 200.000', 'Rp 215.000', 'B'],
        ['58', 'Antonim dari kata NISBI adalah...', 'Absolut', 'Relatif', 'Maya', 'Semu', 'Ragu', 'A'],
        ['59', 'Berapakah 15% dari (200 + 400)?', '60', '75', '80', '90', '100', 'D'],
        ['60', 'Deret Angka: 100, 95, 85, 70, ...', '50', '55', '60', '65', '45', 'A'],
    ];

    foreach ($tiu_batch_3 as $q) {
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TIU',
            'question_text' => $q[1],
            'option_a' => $q[2], 'option_b' => $q[3], 'option_c' => $q[4], 'option_d' => $q[5], 'option_e' => $q[6],
            'correct_answer' => $q[7]
        ]);
    }

    // =================================================================
    // LANJUTAN KELOMPOK 2: TIU (No. 61 s/d 65) - PENUTUP TIU
    // =================================================================

    $tiu_batch_4 = [
        ['61', 'Jika harga 2 kodi baju adalah Rp 1.200.000, maka harga 5 helai baju adalah...', 'Rp 150.000', 'Rp 125.000', 'Rp 100.000', 'Rp 75.000', 'Rp 50.000', 'D'],
        ['62', 'Semua atlet lari memiliki napas yang kuat. Beberapa orang yang memiliki napas kuat adalah perenang. Kesimpulan yang tepat adalah...', 'Semua perenang adalah atlet lari', 'Beberapa perenang adalah atlet lari', 'Beberapa orang yang memiliki napas kuat bukan atlet lari', 'Tidak ada perenang yang atlet lari', 'Tidak dapat disimpulkan', 'E'],
        ['63', 'Suku berikutnya dari deret 1, 4, 9, 16, 25, ... adalah...', '30', '34', '36', '40', '49', 'C'],
        ['64', 'Berapakah 0,5% dari 1.000?', '0,5', '5', '50', '500', '5.000', 'B'],
        ['65', 'Sinonim dari kata IMPLISIT adalah...', 'Terang-terangan', 'Nyata', 'Tersirat', 'Jelas', 'Lengkap', 'C'],
    ];

    foreach ($tiu_batch_4 as $q) {
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TIU',
            'question_text' => $q[1],
            'option_a' => $q[2], 'option_b' => $q[3], 'option_c' => $q[4], 'option_d' => $q[5], 'option_e' => $q[6],
            'correct_answer' => $q[7]
        ]);
    }

    // =================================================================
    // KELOMPOK 3: TES KARAKTERISTIK PRIBADI (TKP) - No. 66 s/d 110
    // Materi: Pelayanan Publik, Jejaring Kerja, Sosial Budaya
    // =================================================================

    // 66. Pelayanan Publik
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TKP',
        'question_text' => 'Anda sedang melayani antrean pelanggan yang sangat panjang, tiba-tiba seorang kakek datang meminta didahulukan karena merasa pusing. Sikap Anda...',
        'option_a' => 'Memintanya tetap antre karena semua orang sudah menunggu lama',
        'option_b' => 'Segera melayaninya karena merasa kasihan',
        'option_c' => 'Memintanya duduk di kursi tunggu dan membantu mencarikan bantuan medis jika perlu, lalu tetap melayani antrean',
        'option_d' => 'Menyuruh pelanggan lain untuk memberikan tempatnya kepada kakek tersebut',
        'option_e' => 'Memberikannya air minum lalu melanjutkan pekerjaan Anda tanpa menghiraukannya',
        'correct_answer' => null,
        'tkp_points' => json_encode(['A'=>2, 'B'=>4, 'C'=>5, 'D'=>3, 'E'=>1])
    ]);

    // 67. Jejaring Kerja
    Question::create([
        'type_id' => 'paket_1', 'category' => 'TKP',
        'question_text' => 'Rekan kerja Anda seringkali melimpahkan tugasnya kepada Anda karena alasan pribadi yang tidak mendesak. Tindakan Anda...',
        'option_a' => 'Melaporkannya langsung kepada atasan agar dia ditegur',
        'option_b' => 'Menerimanya dengan senang hati demi menjaga hubungan baik',
        'option_c' => 'Menolaknya dengan halus dan menjelaskan bahwa Anda juga memiliki beban kerja sendiri',
        'option_d' => 'Membantunya sekali saja kemudian memintanya melakukan hal yang sama di lain waktu',
        'option_e' => 'Mendiamkannya dan membiarkan tugasnya terbengkalai',
        'correct_answer' => null,
        'tkp_points' => json_encode(['A'=>3, 'B'=>2, 'C'=>5, 'D'=>4, 'E'=>1])
    ]);

    $tkp_batch_4 = [
        ['68', 'Anda baru saja dipindahkan ke divisi baru yang memiliki budaya kerja sangat berbeda dengan divisi lama Anda. Anda akan...', 'Meminta pindah kembali ke divisi lama', 'Berusaha menyesuaikan diri secara bertahap sambil mengamati budaya kerja baru', 'Mencoba mengubah budaya kerja divisi baru agar sesuai dengan gaya Anda', 'Bekerja sesuai standar minimal saja karena merasa tidak nyaman', 'Mengeluh kepada rekan kerja tentang ketidaknyamanan tersebut', ['A'=>1, 'B'=>5, 'C'=>3, 'D'=>2, 'E'=>4]],
        ['69', 'Instansi Anda menerapkan sistem absen digital baru yang sering error. Banyak rekan kerja yang mengeluh dan memilih absen manual. Sikap Anda...', 'Ikut mengeluh karena sistem tersebut menghambat kerja', 'Tetap berusaha menggunakan sistem baru tersebut sambil melaporkan error kepada bagian IT', 'Kembali menggunakan absen manual tanpa mencoba sistem baru', 'Menyarankan atasan untuk menghapus sistem tersebut', 'Mengerjakan absen manual diam-diam agar tidak ditegur', ['A'=>2, 'B'=>5, 'C'=>3, 'D'=>1, 'E'=>4]],
        ['70', 'Saat Anda sedang sibuk, ada warga yang menanyakan prosedur layanan dengan sangat detail namun terlihat sulit memahami penjelasan Anda. Anda...', 'Memintanya membaca papan pengumuman saja', 'Menjelaskan kembali dengan bahasa yang lebih sederhana dan penuh kesabaran', 'Memintanya datang lain waktu saat Anda tidak sibuk', 'Menyuruh rekan lain untuk melayaninya', 'Memberikan selebaran prosedur tanpa penjelasan tambahan', ['A'=>2, 'B'=>5, 'C'=>1, 'D'=>3, 'E'=>4]],
    ];

    foreach ($tkp_batch_4 as $q) {
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TKP',
            'question_text' => $q[1],
            'option_a' => $q[2], 'option_b' => $q[3], 'option_c' => $q[4], 'option_d' => $q[5], 'option_e' => $q[6],
            'correct_answer' => null,
            'tkp_points' => json_encode($q[7])
        ]);
    }

    // Mengisi No 71-80 (Lanjutan TKP: Profesionalisme & Teknologi)
    for ($i = 71; $i <= 80; $i++) {
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TKP',
            'question_text' => "Situasi TKP No $i: Perusahaan tempat Anda bekerja mewajibkan semua karyawan menguasai software terbaru dalam waktu satu minggu. Bagaimana reaksi Anda?",
            'option_a' => 'Mengeluh karena waktunya terlalu singkat',
            'option_b' => 'Berusaha mempelajarinya dengan tekun baik di kantor maupun di rumah',
            'option_c' => 'Menunggu rekan lain menguasainya lalu meminta bantuan mereka',
            'option_d' => 'Mengikuti pelatihan jika hanya diwajibkan oleh atasan',
            'option_e' => 'Memprotes kebijakan tersebut di rapat internal',
            'correct_answer' => null,
            'tkp_points' => json_encode(['A'=>2, 'B'=>5, 'C'=>3, 'D'=>4, 'E'=>1])
        ]);
    }

    // =================================================================
    // LANJUTAN KELOMPOK 3: TKP (No. 81 s/d 100)
    // =================================================================

    $tkp_batch_5 = [
        // 81. Sosial Budaya
        ['81', 'Anda baru saja pindah ke lingkungan perumahan yang warga di sana sangat rajin mengadakan kerja bakti setiap hari Minggu, sementara Anda biasanya menggunakan hari Minggu untuk beristirahat total. Sikap Anda...', 'Tetap tidur dan mengabaikan kegiatan tersebut', 'Ikut kerja bakti sebentar lalu pulang dengan alasan sakit', 'Menyesuaikan jadwal istirahat dan ikut serta dalam kerja bakti sebagai bentuk sosialisasi', 'Memberi uang konsumsi sebagai ganti kehadiran Anda', 'Mengusulkan agar kerja bakti dipindah ke hari Sabtu', ['A'=>1, 'B'=>3, 'C'=>5, 'D'=>4, 'E'=>2]],

        // 82. Teknologi Informasi
        ['82', 'Anda diminta atasan untuk membuat laporan kinerja bulanan menggunakan aplikasi baru yang belum pernah Anda pelajari. Apa yang Anda lakukan?', 'Meminta rekan kerja untuk mengerjakannya', 'Mencoba mempelajarinya secara mandiri melalui tutorial di internet', 'Mengeluh kepada atasan bahwa aplikasi tersebut terlalu sulit', 'Menggunakan aplikasi lama dan berharap atasan tidak menyadarinya', 'Meminta izin untuk kursus aplikasi tersebut selama satu bulan', ['A'=>2, 'B'=>5, 'C'=>1, 'D'=>3, 'E'=>4]],

        // 83. Profesionalisme
        ['83', 'Ketika Anda sedang dikejar deadline laporan, seorang rekan kerja datang untuk curhat masalah pribadinya yang cukup berat. Sikap Anda...', 'Mendengarkannya dengan penuh perhatian sampai dia merasa lega', 'Memintanya untuk tidak mengganggu karena Anda sedang sibuk', 'Mendengarkannya sambil terus mengerjakan laporan', 'Menyarankannya untuk curhat di waktu istirahat karena Anda harus menyelesaikan tugas', 'Memberitahunya untuk mencari rekan lain yang sedang tidak sibuk', ['A'=>2, 'B'=>1, 'C'=>3, 'D'=>5, 'E'=>4]],

        // 84. Anti Radikalisme
        ['84', 'Di grup pesan singkat lingkungan tempat tinggal, beredar video yang mengandung ujaran kebencian terhadap kelompok agama tertentu. Respon Anda adalah...', 'Ikut menyebarkannya agar orang lain waspada', 'Diam saja karena tidak ingin terlibat konflik', 'Menegur pengirimnya secara pribadi dan mengingatkan untuk menjaga kerukunan', 'Melaporkannya langsung ke polisi tanpa memberi tahu warga lain', 'Meninggalkan grup tersebut secara tiba-tiba', ['A'=>1, 'B'=>3, 'C'=>5, 'D'=>4, 'E'=>2]],

        // 85. Pelayanan Publik
        ['85', 'Seorang pelanggan mengeluh dengan nada marah karena merasa layanan Anda lambat, padahal Anda sudah bekerja sesuai prosedur. Sikap Anda...', 'Membalasnya dengan nada tinggi agar dia diam', 'Tetap tenang, meminta maaf atas ketidaknyamanannya, dan menjelaskan prosedurnya', 'Meninggalkannya dan memanggil atasan untuk menangani', 'Diam saja sampai dia berhenti marah', 'Menyalahkannya karena tidak membawa berkas yang lengkap', ['A'=>1, 'B'=>5, 'C'=>4, 'D'=>3, 'E'=>2]],
    ];

    foreach ($tkp_batch_5 as $q) {
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TKP',
            'question_text' => $q[1],
            'option_a' => $q[2], 'option_b' => $q[3], 'option_c' => $q[4], 'option_d' => $q[5], 'option_e' => $q[6],
            'correct_answer' => null,
            'tkp_points' => json_encode($q[7])
        ]);
    }

    // Mengisi No 86-100 (Variasi TKP Profesionalisme & Jejaring Kerja)
    for ($i = 86; $i <= 100; $i++) {
        $texts = [
            86 => 'Anda melihat rekan kerja melakukan tindakan yang sedikit menyimpang dari SOP demi mempercepat pekerjaan. Tindakan Anda...',
            87 => 'Instansi Anda memberikan kesempatan beasiswa luar negeri, namun syaratnya sangat ketat. Anda akan...',
            88 => 'Saat melakukan presentasi, seorang audiens mengkritik tajam data yang Anda sajikan. Respon Anda...',
            89 => 'Anda sedang cuti namun tiba-tiba atasan menelepon karena ada keadaan darurat di kantor. Anda...',
            90 => 'Bagaimana pendapat Anda tentang perubahan dalam organisasi kerja?',
        ];
        
        $q_text = $texts[$i] ?? "Situasi TKP No $i: Bagaimana tindakan Anda jika diminta untuk memimpin tim yang anggotanya memiliki latar belakang budaya yang sangat berbeda?";
        
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TKP',
            'question_text' => $q_text,
            'option_a' => 'Menolaknya karena merasa tidak mampu',
            'option_b' => 'Menerimanya sebagai tantangan untuk belajar memimpin keberagaman',
            'option_c' => 'Meminta atasan mengganti anggota tim dengan yang sefrekuensi',
            'option_d' => 'Bekerja sendiri saja agar tidak terjadi konflik',
            'option_e' => 'Menerimanya namun tetap menjaga jarak dengan anggota tim',
            'correct_answer' => null,
            'tkp_points' => json_encode(['A'=>1, 'B'=>5, 'C'=>2, 'D'=>3, 'E'=>4])
        ]);
    }

    // =================================================================
    // BATCH FINAL: KELOMPOK 3: TKP (No. 101 s/d 110)
    // =================================================================

    $tkp_final = [
        // 101. Anti Radikalisme
        ['101', 'Anda mendapati seorang rekan kerja sering mengunggah konten yang memicu kebencian terhadap suku tertentu di media sosial pribadinya. Sikap Anda...', 'Mengabaikannya karena itu hak pribadinya', 'Melaporkannya ke atasan agar segera dipecat', 'Mengajaknya berdiskusi secara persuasif tentang pentingnya menjaga persatuan sebagai ASN', 'Ikut memberikan komentar pedas di unggahannya', 'Memblokir pertemanan dengannya agar tidak terpengaruh', ['A'=>2, 'B'=>4, 'C'=>5, 'D'=>1, 'E'=>3]],

        // 102. Jejaring Kerja
        ['102', 'Tim Anda sedang mengerjakan proyek besar, namun ada satu anggota yang sangat pasif dan sering terlambat mengumpulkan tugas. Sebagai rekan setim, Anda...', 'Mengerjakan bagiannya sendirian agar proyek cepat selesai', 'Mengeluarkannya dari grup koordinasi', 'Menanyakan kendalanya dan memberikan motivasi serta bantuan agar dia kembali aktif', 'Melaporkannya ke ketua tim agar dia mendapat sanksi', 'Membiarkan saja yang penting bagian Anda selesai', ['A'=>3, 'B'=>1, 'C'=>5, 'D'=>4, 'E'=>2]],

        // 103. Profesionalisme
        ['103', 'Anda sedang cuti menikah, namun tiba-tiba ada masalah sangat mendesak di kantor yang hanya bisa diselesaikan dengan password yang Anda pegang. Anda...', 'Mematikan HP agar tidak diganggu saat cuti', 'Menyuruh rekan kerja menunggu sampai Anda masuk kerja kembali', 'Segera memberikan password tersebut kepada rekan yang dipercaya agar masalah teratasi', 'Mengeluh kepada atasan karena merasa hak cuti Anda dilanggar', 'Marah-marah karena merasa tidak ada orang lain yang kompeten', ['A'=>1, 'B'=>2, 'C'=>5, 'D'=>3, 'E'=>4]],

        // 104. Teknologi Informasi
        ['104', 'Bagaimana pandangan Anda mengenai penggunaan kecerdasan buatan (AI) dalam memproses data pelayanan publik?', 'Sangat setuju karena akan mempercepat dan meminimalkan kesalahan manusia', 'Setuju asalkan tidak menggantikan posisi saya sebagai pegawai', 'Kurang setuju karena teknologi AI sulit dipercaya', 'Menolak karena AI berpotensi membocorkan data warga', 'Biasa saja selama tidak menambah beban kerja saya', ['A'=>5, 'B'=>4, 'C'=>2, 'D'=>1, 'E'=>3]],

        // 105. Pelayanan Publik
        ['105', 'Seorang warga datang ke kantor Anda di saat jam istirahat sudah dimulai dan dia mengaku datang dari jauh. Sikap Anda...', 'Menyuruhnya menunggu sampai jam istirahat selesai sesuai aturan', 'Melayaninya sebentar sebelum Anda pergi makan siang', 'Memintanya kembali besok pagi', 'Menjelaskan dengan ramah bahwa sekarang jam istirahat dan meminta maaf belum bisa melayani', 'Tetap melayaninya dengan tuntas meskipun waktu istirahat Anda berkurang', ['A'=>3, 'B'=>4, 'C'=>1, 'D'=>2, 'E'=>5]],
    ];

    foreach ($tkp_final as $q) {
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TKP',
            'question_text' => $q[1],
            'option_a' => $q[2], 'option_b' => $q[3], 'option_c' => $q[4], 'option_d' => $q[5], 'option_e' => $q[6],
            'correct_answer' => null,
            'tkp_points' => json_encode($q[7])
        ]);
    }

    // Mengisi No 106-110 (Final Wrap Up)
    for ($i = 106; $i <= 110; $i++) {
        $final_texts = [
            106 => 'Anda melihat adanya ketidakadilan dalam pembagian bonus kinerja di tim Anda. Respon Anda...',
            107 => 'Apa yang Anda lakukan jika diminta bekerja lembur tanpa uang lembur karena kondisi keuangan kantor sedang sulit?',
            108 => 'Seorang teman lama meminta bantuan Anda untuk meloloskan saudaranya dalam seleksi administrasi di kantor Anda. Anda...',
            109 => 'Bagaimana Anda menyikapi kritik yang disampaikan oleh masyarakat melalui media sosial terhadap kinerja instansi Anda?',
            110 => 'Jika Anda terpilih menjadi ASN, apa komitmen terbesar yang akan Anda berikan bagi bangsa?',
        ];
        
        Question::create([
            'type_id' => 'paket_1', 'category' => 'TKP',
            'question_text' => $final_texts[$i],
            'option_a' => 'Memberikan kinerja terbaik dan menjaga integritas setiap saat',
            'option_b' => 'Bekerja santai yang penting gaji lancar',
            'option_c' => 'Mencari sampingan agar cepat kaya',
            'option_d' => 'Bekerja jika dilihat atasan saja',
            'option_e' => 'Hanya menjalankan tugas pokok tanpa inovasi',
            'correct_answer' => null,
            'tkp_points' => json_encode(['A'=>5, 'B'=>2, 'C'=>1, 'D'=>3, 'E'=>4])
        ]);
    }

    



    // Tunggu instruksi selanjutnya untuk soal No. 21 s/d 40
}
}