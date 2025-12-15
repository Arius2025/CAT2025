<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class PaketDuaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $type = 'paket_2'; // Pastikan ini 'paket_2' agar tidak tercampur

        // --- SOAL 1 s/d 5 (Manual) ---
        Question::create([
            'type_id' => $type, 'category' => 'TWK',
            'question_text' => 'Di tengah arus globalisasi, ancaman terhadap jati diri bangsa dapat berupa westernisasi. Sikap nasionalisme yang paling tepat dalam menghadapi hal tersebut adalah...',
            'option_a' => 'Menolak seluruh kebudayaan asing yang masuk ke Indonesia',
            'option_b' => 'Menerima kebudayaan asing yang modern agar tidak tertinggal',
            'option_c' => 'Menyaring budaya asing dengan nilai-nilai Pancasila sebagai filter utama',
            'option_d' => 'Mempelajari bahasa asing seluas-luasnya untuk persaingan global',
            'option_e' => 'Membatasi penggunaan internet di kalangan remaja',
            'correct_answer' => 'C'
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TWK',
            'question_text' => 'Seorang ASN ditawarkan hadiah oleh seorang warga sebagai bentuk terima kasih karena telah dibantu mengurus perizinan tanpa biaya. Sikap ASN yang berintegritas adalah...',
            'option_a' => 'Menerima karena pemberian tersebut bersifat sukarela',
            'option_b' => 'Menerima hadiah tersebut namun tetap melaporkannya ke atasan',
            'option_c' => 'Menolak hadiah tersebut dengan sopan karena sudah menjadi kewajiban dalam tugas',
            'option_d' => 'Menolak hadiah tersebut dan memarahi warga agar tidak mengulangi',
            'option_e' => 'Minta warga tersebut menyumbangkannya ke kotak amal kantor',
            'correct_answer' => 'C'
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TWK',
            'question_text' => 'Menurut UU No. 3 Tahun 2002, keikutsertaan warga negara dalam upaya bela negara diselenggarakan melalui beberapa hal di bawah ini, KECUALI...',
            'option_a' => 'Pendidikan kewarganegaraan',
            'option_b' => 'Pelatihan dasar kemiliteran secara wajib',
            'option_c' => 'Pengabdian sebagai prajurit TNI',
            'option_d' => 'Pengabdian sesuai dengan profesi',
            'option_e' => 'Partisipasi dalam demonstrasi kebijakan negara',
            'correct_answer' => 'E'
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TWK',
            'question_text' => 'Mengembangkan perbuatan yang luhur, yang mencerminkan sikap dan suasana kekeluargaan dan kegotongroyongan merupakan butir pengamalan Pancasila pada...',
            'option_a' => 'Sila ke-1', 'option_b' => 'Sila ke-2', 'option_c' => 'Sila ke-3', 'option_d' => 'Sila ke-4', 'option_e' => 'Sila ke-5',
            'correct_answer' => 'E'
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TWK',
            'question_text' => 'Pasal 1 ayat (3) UUD 1945 yang menyatakan bahwa "Negara Indonesia adalah negara hukum" mengandung konsekuensi bahwa...',
            'option_a' => 'Segala tindakan pemerintah harus berdasarkan aspirasi partai politik',
            'option_b' => 'Hukum hanya berlaku bagi rakyat biasa',
            'option_c' => 'Terdapat supremasi hukum dalam penyelenggaraan negara',
            'option_d' => 'Presiden memiliki kekuasaan mutlak untuk mengubah hukum',
            'option_e' => 'Hukum ditentukan oleh suara terbanyak di parlemen',
            'correct_answer' => 'C'
        ]);

        // --- SOAL 6 s/d 20 (Looping agar kode rapi) ---
        $twk_data = [
            ['Integritas merupakan salah satu pilar dalam pemberantasan korupsi. Nilai inti integritas yang berkaitan dengan etos kerja adalah...', 'Jujur, Disiplin, Tanggung Jawab', 'Mandiri, Kerja Keras, Sederhana', 'Berani, Peduli, Adil', 'Sabar, Sopan, Santun', 'Ramah, Cepat, Tepat', 'B'],
            ['Bahasa Indonesia berfungsi sebagai lambang kebanggaan nasional. Hal ini berarti...', 'Bahasa Indonesia digunakan dalam sidang resmi', 'Bahasa Indonesia merupakan alat komunikasi antarbudaya', 'Bahasa Indonesia mencerminkan nilai-nilai luhur budaya bangsa', 'Bahasa Indonesia wajib diajarkan di luar negeri', 'Bahasa Indonesia adalah bahasa pengantar pendidikan', 'C'],
            ['Sistem pertahanan keamanan rakyat semesta (Sishankamrata) menempatkan rakyat sebagai...', 'Kekuatan utama', 'Kekuatan pendukung', 'Kekuatan cadangan', 'Kekuatan operasional', 'Kekuatan administratif', 'B'],
            ['Panitia Sembilan menghasilkan dokumen "Piagam Jakarta" pada tanggal...', '1 Juni 1945', '22 Juni 1945', '17 Agustus 1945', '18 Agustus 1945', '10 November 1945', 'B'],
            ['Pengakuan persamaan derajat, hak, dan kewajiban antara sesama manusia adalah perwujudan sila...', 'Ke-1', 'Ke-2', 'Ke-3', 'Ke-4', 'Ke-5', 'B'],
            ['Tugas Komisi Yudisial menurut UUD 1945 adalah...', 'Mengadili perkara kasasi', 'Menguji UU terhadap UUD', 'Mengusulkan pengangkatan Hakim Agung', 'Memutus sengketa hasil pemilu', 'Menyelesaikan sengketa antarlembaga negara', 'C'],
            ['Paham yang menempatkan kesetiaan tertinggi individu kepada bangsa dan tanah air disebut...', 'Chauvinisme', 'Nasionalisme', 'Patriotisme', 'Liberalisme', 'Sosialisme', 'B'],
            ['Bhinneka Tunggal Ika pertama kali muncul dalam Kitab Sutasoma karya...', 'Mpu Prapanca', 'Mpu Gandring', 'Mpu Tantular', 'Mpu Panuluh', 'Mpu Sedah', 'C'],
            ['Salah satu fungsi konstitusi sebagai pembatas kekuasaan bertujuan agar...', 'Pemerintah dapat bekerja tanpa gangguan', 'Tidak terjadi kesewenang-wenangan penguasa', 'Rakyat tidak perlu ikut campur politik', 'Lembaga negara memiliki kekuasaan mutlak', 'Presiden menjabat seumur hidup', 'B'],
            ['Cinta tanah air dan bangsa dapat diwujudkan dengan cara...', 'Menggunakan produk luar negeri', 'Mempelajari budaya asing saja', 'Bangga menggunakan produk dalam negeri', 'Menghafal seluruh pasal UUD', 'Bekerja di luar negeri selamanya', 'C'],
            ['Lembaga yang berwenang mengubah dan menetapkan UUD adalah...', 'Presiden', 'DPR', 'MPR', 'MA', 'MK', 'C'],
            ['Nilai patriotisme dalam kehidupan berbangsa adalah...', 'Rela berkorban untuk kepentingan pribadi', 'Pantang menyerah dalam mencari keuntungan', 'Rela berkorban untuk kepentingan bangsa dan negara', 'Bekerja keras demi jabatan', 'Menghargai pendapat orang lain', 'C'],
            ['Pancasila sebagai ideologi terbuka berarti...', 'Nilai-nilainya dapat diubah sesuai keinginan', 'Dapat menerima pengaruh asing tanpa filter', 'Nilai-nilainya dapat berinteraksi dengan perkembangan zaman', 'Tidak memiliki prinsip yang tetap', 'Hanya berlaku untuk kelompok tertentu', 'C'],
            ['Hak prerogatif presiden diatur dalam UUD 1945, salah satunya adalah...', 'Menetapkan UU sendiri', 'Mengangkat menteri-menteri', 'Membubarkan DPR', 'Memilih Ketua MK', 'Menghapus jabatan gubernur', 'B'],
            ['Identitas nasional Indonesia yang membedakan dengan bangsa lain adalah...', 'Lagu Barat', 'Bendera Merah Putih', 'Mata uang Dollar', 'Sistem kerajaan', 'Bahasa Inggris', 'B'],
        ];

        foreach ($twk_data as $q) {
            Question::create([
                'type_id' => $type,
                'category' => 'TWK',
                'question_text' => $q[0],
                'option_a' => $q[1], 'option_b' => $q[2], 'option_c' => $q[3], 'option_d' => $q[4], 'option_e' => $q[5],
                'correct_answer' => $q[6]
            ]);
        }
        // --- BAGIAN 2: TWK & TIU (NO 21 - 40) ---
        $twk_tiu_data = [
            // TWK - Pilar Negara & Bahasa Indonesia (21-30)
            ['Pasal 1 ayat (1) UUD 1945 menyatakan bahwa Negara Indonesia ialah Negara Kesatuan, yang berbentuk...', 'Republik', 'Monarki', 'Federasi', 'Serikat', 'Parlementer', 'A'],
            ['Lembaga yang berwenang melantik Presiden dan/atau Wakil Presiden hasil pemilihan umum sesuai UUD 1945 adalah...', 'DPR', 'MK', 'MA', 'MPR', 'KPU', 'D'],
            ['Sila keempat Pancasila memberikan tuntunan dalam kehidupan bernegara untuk mengutamakan...', 'Kepentingan pribadi', 'Kepentingan golongan', 'Musyawarah untuk mufakat', 'Kekuasaan absolut', 'Kebebasan berpendapat tanpa batas', 'C'],
            ['Kelebihan Indonesia sebagai negara kepulauan dalam aspek bela negara adalah...', 'Mudah disusupi asing', 'Memiliki garis pertahanan laut yang luas', 'Sulit koordinasi antar pulau', 'Bergantung pada negara tetangga', 'Hanya fokus pada angkatan darat', 'B'],
            ['Penulisan kata baku yang tepat menurut Pedoman Umum Ejaan Bahasa Indonesia (PUEBI) adalah...', 'Analisa', 'Apotik', 'Izin', 'Kwitansi', 'Nasehat', 'C'],
            ['Fungsi Pancasila sebagai pandangan hidup bangsa Indonesia adalah...', 'Alat pemecah belah', 'Pedoman perilaku sehari-hari', 'Hanya simbol negara', 'Dasar untuk menjajah', 'Satu-satunya sumber kekayaan', 'B'],
            ['Wewenang Mahkamah Konstitusi adalah memutus sengketa kewenangan lembaga negara yang kewenangannya diberikan oleh...', 'Presiden', 'DPR', 'UUD 1945', 'Menteri Hukum', 'MPR', 'C'],
            ['Bentuk pengamalan sila kelima Pancasila dalam kehidupan masyarakat adalah...', 'Menghargai hasil karya orang lain', 'Berdoa sebelum makan', 'Mengikuti upacara bendera', 'Membantu teman berkelahi', 'Memaksakan pendapat', 'A'],
            ['Kalimat yang menggunakan tanda baca yang benar adalah...', 'Ibu membeli: nasi, sayur dan ikan.', 'Ibu membeli nasi, sayur, dan ikan.', 'Ibu membeli nasi sayur dan ikan', 'Ibu, membeli nasi, sayur, ikan.', 'Ibu membeli nasi; sayur; ikan.', 'B'],
            ['Sikap bela negara bagi pelajar dapat diwujudkan dengan cara...', 'Mengikuti tawuran', 'Mencontek saat ujian', 'Mengikuti pendidikan kepramukaan dan upacara', 'Membolos sekolah', 'Mengabaikan kebersihan kelas', 'C'],

            // TIU - Kemampuan Verbal & Logika (31-40)
            ['MOBIL : BENSIN = PELAYAR : ...', 'Laut', 'Kapal', 'Angin', 'Makanan', 'Kompas', 'D'], // Hubungan energi/kebutuhan utama
            ['HAUS : AIR = ... : ...', 'Lapar : Makan', 'Ngantuk : Tidur', 'Panas : Api', 'Gelap : Lampu', 'Dingin : Selimut', 'A'],
            ['KENDARAAN : RODA = RUMAH : ...', 'Jendela', 'Pintu', 'Atap', 'Fondasi', 'Lantai', 'D'], // Unsur penyangga
            ['ABAI >< ...', 'Lalai', 'Peduli', 'Diam', 'Ingkar', 'Lupa', 'B'],
            ['EPILOG >< ...', 'Dialog', 'Monolog', 'Prolog', 'Katalog', 'Analog', 'C'],
            ['Semua mamalia menyusui anaknya. Kucing adalah mamalia. Kesimpulannya adalah...', 'Kucing tidak menyusui', 'Semua mamalia adalah kucing', 'Kucing menyusui anaknya', 'Ada kucing yang tidak menyusui', 'Kucing hewan yang lucu', 'C'],
            ['HUTAN : POHON = ... : ...', 'Laut : Ikan', 'Sawah : Padi', 'Pasar : Pedagang', 'Perpustakaan : Buku', 'Sekolah : Siswa', 'D'],
            ['Beberapa orang yang makan pedas merasa sakit perut. Budi makan pedas. Kesimpulannya adalah...', 'Budi sakit perut', 'Budi tidak sakit perut', 'Mungkin Budi sakit perut', 'Semua orang makan pedas sakit perut', 'Sakit perut disebabkan cabai', 'C'],
            ['Semua pelajar memiliki kartu pelajar. Sebagian orang yang memiliki kartu pelajar mendapat diskon buku. Kesimpulannya...', 'Semua pelajar mendapat diskon buku', 'Sebagian pelajar mendapat diskon buku', 'Tidak ada pelajar yang mendapat diskon buku', 'Kartu pelajar hanya untuk diskon buku', 'Diskon buku hanya untuk pelajar', 'B'],
            ['Jika hari hujan, maka ibu membawa payung. Ibu tidak membawa payung. Kesimpulannya...', 'Hari hujan', 'Hari tidak hujan', 'Ibu lupa payung', 'Hari mendung', 'Ibu membeli payung', 'B'],
        ];

        foreach ($twk_tiu_data as $q) {
            Question::create([
                'type_id' => $type,
                'category' => (Question::where('type_id', $type)->count() < 30) ? 'TWK' : 'TIU',
                'question_text' => $q[0],
                'option_a' => $q[1], 'option_b' => $q[2], 'option_c' => $q[3], 'option_d' => $q[4], 'option_e' => $q[5],
                'correct_answer' => $q[6]
            ]);
        }
        // --- BAGIAN 3: TIU NUMERIK (NO 41 - 60) ---
        $tiu_numerik = [
            ['Hasil dari 0,75 + 1/4 - 25% adalah...', '0,25', '0,50', '0,75', '1,00', '1,25', 'C'],
            ['Jika x = 16 dan y = 4, maka nilai dari x^2 / y^3 adalah...', '2', '4', '8', '16', '32', 'B'],
            ['Sebuah proyek dapat diselesaikan dalam 15 hari oleh 10 orang. Jika proyek ingin diselesaikan dalam 5 hari, berapa tambahan pekerja yang dibutuhkan?', '10 orang', '15 orang', '20 orang', '25 orang', '30 orang', 'C'],
            ['3, 6, 12, 24, 48, ... Angka selanjutnya adalah...', '72', '80', '96', '100', '112', 'C'],
            ['1, 4, 9, 16, 25, ... Angka selanjutnya adalah...', '30', '32', '35', '36', '40', 'D'],
            ['Umur Andi 3 kali umur Budi. Jika selisih umur mereka adalah 10 tahun, berapa umur Andi?', '5 tahun', '10 tahun', '15 tahun', '20 tahun', '25 tahun', 'C'],
            ['Sebuah mobil menempuh jarak 120 km dalam waktu 2 jam. Berapa kecepatan rata-rata mobil tersebut?', '40 km/jam', '50 km/jam', '60 km/jam', '70 km/jam', '80 km/jam', 'C'],
            ['Jika 5 kg jeruk harganya Rp 75.000, maka harga 8 kg jeruk adalah...', 'Rp 100.000', 'Rp 110.000', 'Rp 120.000', 'Rp 130.000', 'Rp 140.000', 'C'],
            ['Berapakah 33,33% dari 900?', '200', '250', '300', '350', '400', 'C'],
            ['12, 15, 20, 27, 36, ... Angka selanjutnya adalah...', '45', '47', '48', '50', '52', 'B'],
            ['Seorang pedagang membeli barang seharga Rp 200.000 dan menjualnya dengan rugi 5%. Berapa harga jualnya?', 'Rp 180.000', 'Rp 185.000', 'Rp 190.000', 'Rp 195.000', 'Rp 205.000', 'C'],
            ['Jika p = 3, q = 2, dan r = p^2 + 2pq + q^2, maka nilai r adalah...', '16', '20', '25', '30', '35', 'C'],
            ['Suku berikutnya dari deret A, C, E, G, ... adalah...', 'H', 'I', 'J', 'K', 'L', 'B'],
            ['Jika 7 orang dapat menjahit 14 baju dalam 2 hari, berapa baju yang dapat dijahit 2 orang dalam 4 hari?', '4 baju', '8 baju', '10 baju', '12 baju', '16 baju', 'B'],
            ['Berapa hasil dari (1/2 + 1/3) : 5/6?', '1/2', '1', '1 1/2', '2', '2 1/2', 'B'],
            ['Sebuah persegi panjang memiliki panjang 10 cm dan lebar 5 cm. Berapa kelilingnya?', '15 cm', '30 cm', '45 cm', '50 cm', '60 cm', 'B'],
            ['A, D, G, J, ... Huruf selanjutnya adalah...', 'L', 'M', 'N', 'O', 'P', 'B'],
            ['Jika x adalah bilangan genap dan y adalah bilangan ganjil, maka x + y adalah...', 'Bilangan genap', 'Bilangan ganjil', 'Bilangan prima', 'Habis dibagi 2', 'Selalu negatif', 'B'],
            ['Akar dari 625 adalah...', '15', '25', '35', '45', '55', 'B'],
            ['Hasil dari 15^2 - 10^2 adalah...', '25', '50', '75', '125', '150', 'D'],
        ];

        foreach ($tiu_numerik as $q) {
            Question::create([
                'type_id' => $type,
                'category' => 'TIU',
                'question_text' => $q[0],
                'option_a' => $q[1], 'option_b' => $q[2], 'option_c' => $q[3], 'option_d' => $q[4], 'option_e' => $q[5],
                'correct_answer' => $q[6],
                'tkp_points' => null
            ]);
        }
        // --- BAGIAN 4: TIU FIGURAL & AWAL TKP (NO 61 - 80) ---

        // 61. TIU - Figural (Analogi Gambar)
        Question::create([
            'type_id' => $type, 'category' => 'TIU',
            'question_text' => 'Pilihlah gambar yang merupakan kelanjutan logis dari pola sebelumnya: (Asumsi pola: Penambahan satu sisi pada bangun datar)',
            'option_a' => 'Segitiga', 'option_b' => 'Segiempat', 'option_c' => 'Segilima', 'option_d' => 'Segienam', 'option_e' => 'Lingkaran',
            'correct_answer' => 'D'
        ]);

        // 62. TIU - Figural (Ketidaksamaan)
        Question::create([
            'type_id' => $type, 'category' => 'TIU',
            'question_text' => 'Manakah di antara bangun berikut yang secara logis berbeda dari kelompoknya?',
            'option_a' => 'Kubus', 'option_b' => 'Balok', 'option_c' => 'Bola', 'option_d' => 'Prisma', 'option_e' => 'Persegi',
            'correct_answer' => 'E' // Karena persegi adalah bangun datar, sisanya bangun ruang
        ]);

        // 63. TIU - Logika Analitis
        Question::create([
            'type_id' => $type, 'category' => 'TIU',
            'question_text' => 'Andi lebih tua dari Budi. Cici lebih muda dari Budi. Dedi lebih tua dari Andi. Siapakah yang paling muda?',
            'option_a' => 'Andi', 'option_b' => 'Budi', 'option_c' => 'Cici', 'option_d' => 'Dedi', 'option_e' => 'Tidak dapat ditentukan',
            'correct_answer' => 'C'
        ]);

        // 64. TIU - Logika Analitis (Posisi)
        Question::create([
            'type_id' => $type, 'category' => 'TIU',
            'question_text' => 'Dalam sebuah antrean, Rani berada tepat di belakang Susi. Tono berada di depan Susi. Jika Susi berada di urutan ke-3, di urutan berapakah Rani?',
            'option_a' => '1', 'option_b' => '2', 'option_c' => '3', 'option_d' => '4', 'option_e' => '5',
            'correct_answer' => 'D'
        ]);

        // 65. TIU - Figural (Serial)
        Question::create([
            'type_id' => $type, 'category' => 'TIU',
            'question_text' => 'Pola: 2 titik, 4 titik, 6 titik. Gambar selanjutnya adalah...',
            'option_a' => '7 titik', 'option_b' => '8 titik', 'option_c' => '9 titik', 'option_d' => '10 titik', 'option_e' => '12 titik',
            'correct_answer' => 'B'
        ]);

        // --- MULAI TKP (SOAL NO 66 - 80) ---
        // 66. TKP - Pelayanan Publik
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda sedang melayani pelanggan yang sangat cerewet dan menanyakan hal yang sama berulang kali. Sikap Anda adalah...',
            'option_a' => 'Menjawab dengan ketus karena merasa waktu terbuang',
            'option_b' => 'Meminta rekan lain untuk melayaninya',
            'option_c' => 'Tetap sabar, tersenyum, dan menjelaskan kembali dengan bahasa yang lebih sederhana sampai ia paham',
            'option_d' => 'Mendiamkannya sampai ia berhenti bicara sendiri',
            'option_e' => 'Melaporkannya kepada atasan karena mengganggu pelayanan',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 3, 'C' => 5, 'D' => 2, 'E' => 1])
        ]);

        // 67. TKP - Pelayanan Publik
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Saat jam istirahat dimulai, seorang lansia datang membutuhkan bantuan darurat untuk administrasi kesehatan. Sikap Anda...',
            'option_a' => 'Menyuruhnya menunggu sampai jam istirahat selesai',
            'option_b' => 'Meninggalkan tempat begitu saja karena sudah hak saya untuk istirahat',
            'option_c' => 'Menunda istirahat sejenak untuk membantunya menyelesaikan urusan tersebut hingga tuntas',
            'option_d' => 'Memarahinya karena tidak datang lebih pagi',
            'option_e' => 'Memberinya nomor antrean untuk nanti sore',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 1, 'C' => 5, 'D' => 1, 'E' => 3])
        ]);

        // 68. TKP - Jejaring Kerja
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda ditugaskan dalam tim lintas divisi yang anggotanya belum Anda kenal sama sekali. Hal yang pertama Anda lakukan adalah...',
            'option_a' => 'Menunggu orang lain menyapa saya terlebih dahulu',
            'option_b' => 'Hanya bekerja jika diperintah oleh ketua tim',
            'option_c' => 'Memperkenalkan diri secara aktif dan berusaha menjalin komunikasi yang baik dengan seluruh anggota tim',
            'option_d' => 'Mencari tahu kelemahan masing-masing anggota tim',
            'option_e' => 'Meminta pindah ke tim yang sudah saya kenal anggotanya',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 3, 'C' => 5, 'D' => 1, 'E' => 1])
        ]);

        // 69. TKP - Sosial Budaya
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Rekan kerja Anda sering membawa makanan khas daerahnya yang baunya sangat menyengat dan Anda tidak menyukainya. Sikap Anda...',
            'option_a' => 'Membuang makanan tersebut saat dia tidak melihat',
            'option_b' => 'Menghargai keberagaman tersebut dan tetap bersikap biasa saja tanpa menyinggung perasaannya',
            'option_c' => 'Menyindirnya agar dia tahu diri',
            'option_d' => 'Melarangnya membawa makanan ke kantor',
            'option_e' => 'Melaporkannya ke bagian kebersihan',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 5, 'C' => 2, 'D' => 1, 'E' => 3])
        ]);

        // 70. TKP - TIK
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Pimpinan meminta Anda membuat laporan menggunakan software baru yang belum pernah Anda gunakan sebelumnya. Sikap Anda...',
            'option_a' => 'Menolak karena tidak ada dalam deskripsi pekerjaan saya',
            'option_b' => 'Mengeluh kepada rekan kerja tentang sulitnya software tersebut',
            'option_c' => 'Berusaha mempelajari software tersebut secara mandiri melalui tutorial dan segera menyelesaikan laporan',
            'option_d' => 'Meminta orang lain mengerjakan laporan saya',
            'option_e' => 'Mengerjakan laporan dengan software lama saja',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 2, 'C' => 5, 'D' => 1, 'E' => 3])
        ]);

        // 71. TKP - Integritas
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda menemukan amplop berisi uang di meja kerja Anda setelah membantu seorang vendor. Tidak ada catatan siapa pengirimnya. Sikap Anda...',
            'option_a' => 'Mengambilnya karena dianggap sebagai rezeki',
            'option_b' => 'Menyumbangkannya ke panti asuhan atas nama saya',
            'option_c' => 'Melaporkan temuan tersebut kepada unit kepatuhan internal atau atasan untuk ditindaklanjuti',
            'option_d' => 'Membagikan uang tersebut kepada rekan seruangan',
            'option_e' => 'Mendiamkannya saja di meja sampai ada yang mencari',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 2, 'C' => 5, 'D' => 1, 'E' => 3])
        ]);

        // 72. TKP - Profesionalisme
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Tugas yang diberikan atasan kepada Anda sangat menumpuk dan memiliki tenggat waktu yang sama. Anda akan...',
            'option_a' => 'Mengerjakan yang paling mudah saja',
            'option_b' => 'Marah-marah karena merasa tertekan',
            'option_c' => 'Membuat skala prioritas dan menyelesaikannya satu per satu dengan fokus dan disiplin',
            'option_d' => 'Membawa pulang semua pekerjaan dan tidak tidur semalaman',
            'option_e' => 'Meminta rekan kerja mengerjakan sebagian tanpa izin atasan',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 3, 'B' => 1, 'C' => 5, 'D' => 4, 'E' => 2])
        ]);

        // 73. TKP - Anti Radikalisme
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Seorang teman kerja sering mengirimkan pesan provokatif yang menjelek-jelekkan pemerintah di grup WhatsApp kantor. Sikap Anda...',
            'option_a' => 'Ikut mengomentari dengan nada yang sama',
            'option_b' => 'Diam saja agar tidak terlibat masalah',
            'option_c' => 'Menegurnya secara pribadi untuk berhenti mengirim hal sensitif dan tetap profesional sebagai ASN',
            'option_d' => 'Langsung keluar dari grup tanpa pamit',
            'option_e' => 'Melaporkannya ke pihak berwajib tanpa bukti',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 3, 'C' => 5, 'D' => 2, 'E' => 4])
        ]);

        // 74. TKP - Pelayanan Publik
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Instansi Anda mendapat komplain massal di media sosial terkait lambatnya sistem antrean digital. Tindakan Anda...',
            'option_a' => 'Menghapus komentar-komentar negatif tersebut',
            'option_b' => 'Membiarkannya karena bukan tugas saya mengurusi media sosial',
            'option_c' => 'Memberikan saran kepada atasan dan tim IT untuk segera memperbaiki bug sistem dan memberikan permohonan maaf resmi',
            'option_d' => 'Menyalahkan vendor pembuat aplikasi di media sosial',
            'option_e' => 'Berhenti menggunakan sistem digital dan kembali ke manual',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 2, 'C' => 5, 'D' => 1, 'E' => 3])
        ]);

        // 75. TKP - Jejaring Kerja
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda terpilih menjadi ketua panitia HUT kantor. Namun, anggaran yang tersedia sangat terbatas. Anda akan...',
            'option_a' => 'Membatalkan acara karena anggarannya kecil',
            'option_b' => 'Meminta iuran wajib yang besar kepada seluruh karyawan',
            'option_c' => 'Mengajak tim berdiskusi kreatif untuk membuat acara yang bermakna namun tetap hemat biaya',
            'option_d' => 'Menggunakan uang pribadi agar acara mewah',
            'option_e' => 'Mengerjakan persiapan seadanya tanpa semangat',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 2, 'C' => 5, 'D' => 3, 'E' => 1])
        ]);

        // 76. TKP - TIK
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Saat Anda sedang mempresentasikan data penting, laptop Anda tiba-tiba mengalami "Blue Screen". Sikap Anda...',
            'option_a' => 'Panik dan mengakhiri presentasi begitu saja',
            'option_b' => 'Menangis karena merasa malu di depan atasan',
            'option_c' => 'Meminta maaf dengan tenang, lalu berusaha mencari solusi alternatif seperti menggunakan laptop rekan atau melanjutkan penjelasan secara lisan',
            'option_d' => 'Menyalahkan bagian inventaris barang',
            'option_e' => 'Diam seribu bahasa sampai suasana canggung',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 1, 'C' => 5, 'D' => 2, 'E' => 1])
        ]);

        // 77. TKP - Profesionalisme
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda ditugaskan ke luar kota selama seminggu, namun di saat yang sama anak Anda sedang sakit ringan di rumah. Sikap Anda...',
            'option_a' => 'Membatalkan tugas secara mendadak',
            'option_b' => 'Membawa anak ikut pergi bertugas',
            'option_c' => 'Memastikan anak sudah dirawat dengan baik oleh keluarga/tenaga medis, lalu tetap menjalankan tugas negara secara profesional',
            'option_d' => 'Sering menelepon rumah setiap jam sehingga pekerjaan terbengkalai',
            'option_e' => 'Mengeluh sepanjang perjalanan dinas',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 2, 'C' => 5, 'D' => 3, 'E' => 1])
        ]);

        // 78. TKP - Sosial Budaya
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Di kantor baru Anda, terdapat tradisi makan siang bersama yang mengharuskan setiap orang membawa makanan sendiri. Anda lebih suka makan sendiri. Sikap Anda...',
            'option_a' => 'Tetap makan sendiri di meja kerja',
            'option_b' => 'Menolak ajakan mereka dengan kasar',
            'option_c' => 'Mencoba sesekali ikut bergabung untuk menjalin keakraban dengan rekan kerja baru',
            'option_d' => 'Makan di luar kantor agar tidak terlihat',
            'option_e' => 'Menyuruh mereka berhenti melakukan tradisi tersebut',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 3, 'B' => 1, 'C' => 5, 'D' => 3, 'E' => 1])
        ]);

        // 79. TKP - Anti Radikalisme
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Bagaimana sikap Anda terhadap rekan kerja yang memiliki pandangan politik yang sangat ekstrem dan berbeda dengan Anda?',
            'option_a' => 'Memusuhinya dan tidak mau bekerja sama',
            'option_b' => 'Berdebat dengannya setiap hari agar dia berubah',
            'option_c' => 'Tetap bersikap profesional dalam bekerja dan menghormati perbedaan tanpa harus terpengaruh oleh pahamnya',
            'option_d' => 'Melaporkannya ke polisi tanpa alasan yang kuat',
            'option_e' => 'Mengikuti pandangannya agar tidak dikucilkan',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 2, 'C' => 5, 'D' => 3, 'E' => 1])
        ]);

        // 80. TKP - Pelayanan Publik
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Seorang pengguna layanan datang dengan marah karena ada kesalahan data pada dokumennya yang disebabkan oleh kelalaian Anda. Sikap Anda...',
            'option_a' => 'Menyalahkan sistem komputer',
            'option_b' => 'Pura-pura tidak tahu dan menyuruhnya mengurus ulang dari awal',
            'option_c' => 'Mengakui kesalahan, meminta maaf dengan tulus, dan segera memperbaiki dokumen tersebut secepat mungkin',
            'option_d' => 'Membayar orang lain untuk memperbaiki kesalahan tersebut',
            'option_e' => 'Menyuruhnya kembali besok karena Anda lelah',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 1, 'C' => 5, 'D' => 2, 'E' => 1])
        ]);

        // --- SOAL NO 81 s/d 90: TKP (Sosial Budaya & TIK) ---
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda baru saja dipindahkan ke kantor cabang yang berada di daerah pelosok dengan kebudayaan yang sangat berbeda dari daerah asal Anda. Sikap Anda adalah...',
            'option_a' => 'Meminta pindah kembali ke kantor pusat karena tidak betah',
            'option_b' => 'Bersikap tertutup dan hanya bergaul dengan sesama pendatang saja',
            'option_c' => 'Mempelajari kebudayaan dan adat istiadat setempat agar dapat menyesuaikan diri dan bekerja lebih efektif',
            'option_d' => 'Mengharuskan rekan kerja lokal mengikuti budaya kerja daerah asal saya',
            'option_e' => 'Bekerja seadanya dan sering mengambil cuti untuk pulang kampung',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 2, 'C' => 5, 'D' => 2, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Kantor Anda sedang menerapkan sistem pengarsipan digital baru untuk menggantikan sistem manual. Banyak rekan senior yang kesulitan dan mengeluh. Tindakan Anda...',
            'option_a' => 'Ikut mengeluh karena sistem baru memang merepotkan',
            'option_b' => 'Mempelajari sistem tersebut dengan sungguh-sungguh dan menawarkan bantuan untuk mengajari rekan yang kesulitan',
            'option_c' => 'Tetap menggunakan sistem manual selama pimpinan tidak melihat',
            'option_d' => 'Menunggu instruksi pelatihan resmi dan tidak mau mencoba sendiri',
            'option_e' => 'Menyuruh bagian IT untuk mengerjakan semua pengarsipan saya',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 5, 'C' => 1, 'D' => 3, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Saat Anda sedang sibuk melayani masyarakat, seorang warga dari kelompok minoritas datang dan tampak bingung dengan prosedur pelayanan. Sikap Anda...',
            'option_a' => 'Mengabaikannya dan melayani warga lain yang lebih dulu antre',
            'option_b' => 'Menyuruhnya bertanya kepada satpam di depan saja',
            'option_c' => 'Menghampirinya dengan ramah dan membantu menjelaskan prosedur pelayanan tanpa membeda-bedakan latar belakangnya',
            'option_d' => 'Melayaninya dengan setengah hati karena merasa terganggu',
            'option_e' => 'Meminta rekan kerja lain yang lebih sabar untuk membantunya',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 2, 'C' => 5, 'D' => 1, 'E' => 3])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Dalam sebuah rapat, Anda menyarankan penggunaan aplikasi manajemen tugas online untuk tim, namun ide tersebut ditolak mentah-mentah oleh ketua tim. Anda akan...',
            'option_a' => 'Marah dan tidak mau ikut rapat lagi di masa mendatang',
            'option_b' => 'Menerima keputusan tersebut dengan lapang dada dan tetap bekerja maksimal dengan sistem yang ada saat ini',
            'option_c' => 'Membicarakan keburukan ketua tim di belakang rekan kerja lain',
            'option_d' => 'Sengaja membuat kesalahan dalam tugas agar ketua tim menyesal menolak ide saya',
            'option_e' => 'Mencoba menjelaskan kembali manfaatnya di waktu yang lebih tepat secara persuasif',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 4, 'C' => 1, 'D' => 1, 'E' => 5])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda menemukan konten di grup WhatsApp kantor yang berisi berita bohong (hoax) tentang kebijakan pemerintah yang sedang berjalan. Sikap Anda...',
            'option_a' => 'Ikut menyebarkannya ke grup lain agar lebih viral',
            'option_b' => 'Diam saja karena tidak ingin berdebat dengan rekan kerja',
            'option_c' => 'Mencari sumber berita resmi, lalu memberikan klarifikasi yang benar di grup tersebut secara sopan',
            'option_d' => 'Menghapus pesan tersebut di HP saya saja tanpa peduli orang lain',
            'option_e' => 'Melaporkan rekan yang mengirim pesan tersebut kepada polisi',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 3, 'C' => 5, 'D' => 2, 'E' => 4])
        ]);

        // --- SOAL NO 86 s/d 90: TKP (Profesionalisme & Anti Radikalisme) ---
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Atasan Anda memberikan target kerja yang sangat tinggi dan terasa kurang realistis bagi Anda. Sikap Anda adalah...',
            'option_a' => 'Langsung menyerah dan mengerjakan seadanya',
            'option_b' => 'Menerimanya sebagai tantangan, menyusun strategi kerja yang efektif, dan berusaha mencapai target tersebut semaksimal mungkin',
            'option_c' => 'Mengajukan protes keras di depan rekan kerja yang lain',
            'option_d' => 'Meminta atasan untuk mengganti target tersebut dengan yang lebih mudah',
            'option_e' => 'Mengabaikan instruksi tersebut dan bekerja seperti biasa',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 5, 'C' => 2, 'D' => 3, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Seorang rekan kerja mengajak Anda berdiskusi dan mulai menyinggung hal-hal yang bersifat meragukan keabsahan Pancasila sebagai ideologi negara. Sikap Anda...',
            'option_a' => 'Mendengarkannya karena ingin tahu lebih dalam',
            'option_b' => 'Mengalihkan pembicaraan ke topik pekerjaan dan menegaskan bahwa Pancasila adalah harga mati bagi setiap ASN',
            'option_c' => 'Setuju dengannya karena merasa ada benarnya juga',
            'option_d' => 'Diam saja agar hubungan pertemanan tidak rusak',
            'option_e' => 'Ikut memprovokasi rekan lain agar ikut berdiskusi',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 5, 'C' => 1, 'D' => 3, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Bagaimana Anda menyikapi adanya perbedaan keyakinan dalam menjalankan tugas-tugas kelompok di kantor?',
            'option_a' => 'Memilih hanya bekerja dengan rekan yang memiliki keyakinan sama',
            'option_b' => 'Mengabaikan perbedaan tersebut dan fokus pada profesionalitas serta hasil kerja tim yang maksimal',
            'option_c' => 'Berusaha mempengaruhi rekan lain agar mengikuti keyakinan saya',
            'option_d' => 'Mempermasalahkan perbedaan tersebut saat terjadi kegagalan tim',
            'option_e' => 'Menghindari diskusi yang berkaitan dengan agama sama sekali',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 5, 'C' => 1, 'D' => 2, 'E' => 4])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda ditawarkan gratifikasi berupa paket liburan oleh seorang klien setelah Anda menyelesaikan urusannya. Sikap Anda adalah...',
            'option_a' => 'Menerimanya karena itu diberikan secara ikhlas sebagai tanda terima kasih',
            'option_b' => 'Menolaknya dengan sopan dan menjelaskan bahwa ASN dilarang menerima gratifikasi dalam bentuk apapun',
            'option_c' => 'Menerimanya namun tidak melaporkannya kepada siapapun',
            'option_d' => 'Meminta klien mengganti paket liburan dengan uang tunai saja',
            'option_e' => 'Menolak hadiah tersebut dan memarahi klien di depan umum',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 5, 'C' => 1, 'D' => 1, 'E' => 3])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Bagi Anda, melakukan inovasi dalam metode kerja digital di kantor bertujuan untuk...',
            'option_a' => 'Agar terlihat lebih hebat dari rekan kerja lainnya',
            'option_b' => 'Mendapatkan pujian dan kenaikan pangkat dari atasan',
            'option_c' => 'Meningkatkan efisiensi dan kualitas pelayanan kepada masyarakat luas',
            'option_d' => 'Menghindari pekerjaan manual yang melelahkan',
            'option_e' => 'Menghabiskan anggaran teknologi di kantor',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 3, 'C' => 5, 'D' => 2, 'E' => 1])
        ]);

        // --- SOAL NO 81 s/d 90: TKP (Sosial Budaya & TIK) ---
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda baru saja dipindahkan ke kantor cabang yang berada di daerah pelosok dengan kebudayaan yang sangat berbeda dari daerah asal Anda. Sikap Anda adalah...',
            'option_a' => 'Meminta pindah kembali ke kantor pusat karena tidak betah',
            'option_b' => 'Bersikap tertutup dan hanya bergaul dengan sesama pendatang saja',
            'option_c' => 'Mempelajari kebudayaan dan adat istiadat setempat agar dapat menyesuaikan diri dan bekerja lebih efektif',
            'option_d' => 'Mengharuskan rekan kerja lokal mengikuti budaya kerja daerah asal saya',
            'option_e' => 'Bekerja seadanya dan sering mengambil cuti untuk pulang kampung',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 2, 'C' => 5, 'D' => 2, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Kantor Anda sedang menerapkan sistem pengarsipan digital baru untuk menggantikan sistem manual. Banyak rekan senior yang kesulitan dan mengeluh. Tindakan Anda...',
            'option_a' => 'Ikut mengeluh karena sistem baru memang merepotkan',
            'option_b' => 'Mempelajari sistem tersebut dengan sungguh-sungguh dan menawarkan bantuan untuk mengajari rekan yang kesulitan',
            'option_c' => 'Tetap menggunakan sistem manual selama pimpinan tidak melihat',
            'option_d' => 'Menunggu instruksi pelatihan resmi dan tidak mau mencoba sendiri',
            'option_e' => 'Menyuruh bagian IT untuk mengerjakan semua pengarsipan saya',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 5, 'C' => 1, 'D' => 3, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Saat Anda sedang sibuk melayani masyarakat, seorang warga dari kelompok minoritas datang dan tampak bingung dengan prosedur pelayanan. Sikap Anda...',
            'option_a' => 'Mengabaikannya dan melayani warga lain yang lebih dulu antre',
            'option_b' => 'Menyuruhnya bertanya kepada satpam di depan saja',
            'option_c' => 'Menghampirinya dengan ramah dan membantu menjelaskan prosedur pelayanan tanpa membeda-bedakan latar belakangnya',
            'option_d' => 'Melayaninya dengan setengah hati karena merasa terganggu',
            'option_e' => 'Meminta rekan kerja lain yang lebih sabar untuk membantunya',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 2, 'C' => 5, 'D' => 1, 'E' => 3])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Dalam sebuah rapat, Anda menyarankan penggunaan aplikasi manajemen tugas online untuk tim, namun ide tersebut ditolak mentah-mentah oleh ketua tim. Anda akan...',
            'option_a' => 'Marah dan tidak mau ikut rapat lagi di masa mendatang',
            'option_b' => 'Menerima keputusan tersebut dengan lapang dada dan tetap bekerja maksimal dengan sistem yang ada saat ini',
            'option_c' => 'Membicarakan keburukan ketua tim di belakang rekan kerja lain',
            'option_d' => 'Sengaja membuat kesalahan dalam tugas agar ketua tim menyesal menolak ide saya',
            'option_e' => 'Mencoba menjelaskan kembali manfaatnya di waktu yang lebih tepat secara persuasif',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 4, 'C' => 1, 'D' => 1, 'E' => 5])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda menemukan konten di grup WhatsApp kantor yang berisi berita bohong (hoax) tentang kebijakan pemerintah yang sedang berjalan. Sikap Anda...',
            'option_a' => 'Ikut menyebarkannya ke grup lain agar lebih viral',
            'option_b' => 'Diam saja karena tidak ingin berdebat dengan rekan kerja',
            'option_c' => 'Mencari sumber berita resmi, lalu memberikan klarifikasi yang benar di grup tersebut secara sopan',
            'option_d' => 'Menghapus pesan tersebut di HP saya saja tanpa peduli orang lain',
            'option_e' => 'Melaporkan rekan yang mengirim pesan tersebut kepada polisi',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 3, 'C' => 5, 'D' => 2, 'E' => 4])
        ]);

        // --- SOAL NO 86 s/d 90: TKP (Profesionalisme & Anti Radikalisme) ---
        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Atasan Anda memberikan target kerja yang sangat tinggi dan terasa kurang realistis bagi Anda. Sikap Anda adalah...',
            'option_a' => 'Langsung menyerah dan mengerjakan seadanya',
            'option_b' => 'Menerimanya sebagai tantangan, menyusun strategi kerja yang efektif, dan berusaha mencapai target tersebut semaksimal mungkin',
            'option_c' => 'Mengajukan protes keras di depan rekan kerja yang lain',
            'option_d' => 'Meminta atasan untuk mengganti target tersebut dengan yang lebih mudah',
            'option_e' => 'Mengabaikan instruksi tersebut dan bekerja seperti biasa',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 5, 'C' => 2, 'D' => 3, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Seorang rekan kerja mengajak Anda berdiskusi dan mulai menyinggung hal-hal yang bersifat meragukan keabsahan Pancasila sebagai ideologi negara. Sikap Anda...',
            'option_a' => 'Mendengarkannya karena ingin tahu lebih dalam',
            'option_b' => 'Mengalihkan pembicaraan ke topik pekerjaan dan menegaskan bahwa Pancasila adalah harga mati bagi setiap ASN',
            'option_c' => 'Setuju dengannya karena merasa ada benarnya juga',
            'option_d' => 'Diam saja agar hubungan pertemanan tidak rusak',
            'option_e' => 'Ikut memprovokasi rekan lain agar ikut berdiskusi',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 5, 'C' => 1, 'D' => 3, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Bagaimana Anda menyikapi adanya perbedaan keyakinan dalam menjalankan tugas-tugas kelompok di kantor?',
            'option_a' => 'Memilih hanya bekerja dengan rekan yang memiliki keyakinan sama',
            'option_b' => 'Mengabaikan perbedaan tersebut dan fokus pada profesionalitas serta hasil kerja tim yang maksimal',
            'option_c' => 'Berusaha mempengaruhi rekan lain agar mengikuti keyakinan saya',
            'option_d' => 'Mempermasalahkan perbedaan tersebut saat terjadi kegagalan tim',
            'option_e' => 'Menghindari diskusi yang berkaitan dengan agama sama sekali',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 5, 'C' => 1, 'D' => 2, 'E' => 4])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda ditawarkan gratifikasi berupa paket liburan oleh seorang klien setelah Anda menyelesaikan urusannya. Sikap Anda adalah...',
            'option_a' => 'Menerimanya karena itu diberikan secara ikhlas sebagai tanda terima kasih',
            'option_b' => 'Menolaknya dengan sopan dan menjelaskan bahwa ASN dilarang menerima gratifikasi dalam bentuk apapun',
            'option_c' => 'Menerimanya namun tidak melaporkannya kepada siapapun',
            'option_d' => 'Meminta klien mengganti paket liburan dengan uang tunai saja',
            'option_e' => 'Menolak hadiah tersebut dan memarahi klien di depan umum',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 5, 'C' => 1, 'D' => 1, 'E' => 3])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Bagi Anda, melakukan inovasi dalam metode kerja digital di kantor bertujuan untuk...',
            'option_a' => 'Agar terlihat lebih hebat dari rekan kerja lainnya',
            'option_b' => 'Mendapatkan pujian dan kenaikan pangkat dari atasan',
            'option_c' => 'Meningkatkan efisiensi dan kualitas pelayanan kepada masyarakat luas',
            'option_d' => 'Menghindari pekerjaan manual yang melelahkan',
            'option_e' => 'Menghabiskan anggaran teknologi di kantor',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 3, 'C' => 5, 'D' => 2, 'E' => 1])
        ]);
        // --- SOAL NO 101 s/d 110: TKP (INTEGRITAS, PROFESIONALISME & ANTI RADIKALISME FINAL) ---

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda sedang menghadiri sebuah diskusi komunitas dan mendengar seseorang mengajak peserta untuk memboikot pajak negara sebagai bentuk protes politik. Sikap Anda...',
            'option_a' => 'Diam saja karena itu hak berpendapat setiap orang',
            'option_b' => 'Ikut setuju karena saya juga merasa pajak terlalu tinggi',
            'option_c' => 'Memberikan penjelasan bahwa membayar pajak adalah kewajiban warga negara untuk pembangunan dan melaporkan jika ada ajakan kekerasan',
            'option_d' => 'Langsung keluar dari ruangan karena takut terlibat',
            'option_e' => 'Mengajak orang lain untuk berdebat kusir dengan orang tersebut',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 1, 'C' => 5, 'D' => 3, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Seorang rekan kerja meminta bantuan Anda untuk memalsukan tanda tangan kehadiran rapat karena dia ada urusan keluarga. Sikap Anda...',
            'option_a' => 'Membantunya karena kasihan dia sedang ada masalah keluarga',
            'option_b' => 'Menolak dengan tegas dan menjelaskan bahwa hal itu melanggar integritas serta menyarankannya meminta izin resmi ke atasan',
            'option_c' => 'Membantunya sekali ini saja asal dia berjanji tidak mengulanginya',
            'option_d' => 'Melaporkannya langsung ke bagian kedisiplinan tanpa bicara dulu',
            'option_e' => 'Pura-pura tidak mendengar permintaannya',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 5, 'C' => 2, 'D' => 4, 'E' => 2])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda ditugaskan di bagian pelayanan dan mendapati sistem komputer sangat lambat sehingga antrean menumpuk. Masyarakat mulai protes. Tindakan Anda...',
            'option_a' => 'Memarahi bagian IT karena sistem yang buruk',
            'option_b' => 'Menghentikan pelayanan sementara sampai sistem benar-benar lancar',
            'option_c' => 'Tetap melayani dengan tenang, memohon maaf atas kendala yang terjadi, dan berusaha mencari solusi manual jika memungkinkan',
            'option_d' => 'Menyuruh masyarakat pulang dan datang besok pagi',
            'option_e' => 'Ikut mengeluh bersama masyarakat agar mereka tidak menyalahkan saya',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 2, 'C' => 5, 'D' => 1, 'E' => 2])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Bagaimana sikap Anda terhadap rekan kerja yang memiliki gaya hidup sangat berbeda dan sering memamerkan kemewahan di kantor?',
            'option_a' => 'Merasa iri dan berusaha meniru gaya hidupnya',
            'option_b' => 'Menjauhinya karena merasa tidak selevel',
            'option_c' => 'Tetap bersikap biasa, fokus pada profesionalisme kerja, dan tidak mencampuri urusan pribadinya selama tidak mengganggu kantor',
            'option_d' => 'Menegurnya agar tidak pamer di lingkungan kantor',
            'option_e' => 'Membicarakan sumber kekayaannya dengan rekan kerja lain',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 3, 'C' => 5, 'D' => 2, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Dalam melaksanakan tugas pelayanan publik, prinsip utama yang Anda pegang sebagai ASN adalah...',
            'option_a' => 'Melayani siapa saja yang memberikan keuntungan pribadi',
            'option_b' => 'Mendahulukan kepentingan golongan atau kelompok saya',
            'option_c' => 'Memberikan pelayanan yang adil, transparan, dan akuntabel kepada seluruh lapisan masyarakat tanpa diskriminasi',
            'option_d' => 'Bekerja seadanya sesuai dengan besarnya tunjangan yang diterima',
            'option_e' => 'Menghindari pekerjaan yang sulit dan berisiko tinggi',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 1, 'C' => 5, 'D' => 2, 'E' => 2])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda menemukan adanya celah dalam sistem aplikasi kantor yang bisa digunakan untuk memanipulasi data tanpa ketahuan. Sikap Anda...',
            'option_a' => 'Menggunakan celah tersebut untuk memudahkan pekerjaan saya',
            'option_b' => 'Menjual informasi celah tersebut kepada pihak lain',
            'option_c' => 'Segera melaporkan temuan celah tersebut kepada bagian IT agar segera diperbaiki demi keamanan data instansi',
            'option_d' => 'Mendiamkannya saja selama tidak ada yang tahu',
            'option_e' => 'Memberitahu teman dekat saya agar dia juga bisa memanfaatkannya',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 1, 'B' => 1, 'C' => 5, 'D' => 2, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Anda diminta untuk bergabung dalam sebuah proyek besar yang membutuhkan koordinasi antar lembaga. Namun, ada ego sektoral yang kuat antar lembaga tersebut. Sikap Anda...',
            'option_a' => 'Ikut membela lembaga saya mati-matian meskipun salah',
            'option_b' => 'Menarik diri dari proyek karena malas menghadapi konflik',
            'option_c' => 'Menjadi jembatan komunikasi yang baik dan fokus pada tujuan bersama demi kepentingan bangsa dan negara',
            'option_d' => 'Hanya bekerja jika lembaga lain juga bekerja keras',
            'option_e' => 'Menyalahkan pimpinan lembaga lain atas kemacetan koordinasi',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 1, 'C' => 5, 'D' => 3, 'E' => 2])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Apa yang Anda lakukan jika melihat konten di media sosial yang mengatasnamakan agama untuk menghasut orang melakukan tindakan radikal?',
            'option_a' => 'Mengabaikannya karena itu bukan urusan saya',
            'option_b' => 'Melakukan laporan (report) pada konten tersebut dan mengedukasi lingkaran terdekat tentang bahaya konten tersebut',
            'option_c' => 'Ikut menyebarkannya untuk menanyakan kebenarannya',
            'option_d' => 'Mendebat pengunggahnya dengan kata-kata kasar',
            'option_e' => 'Menutup akun media sosial saya selamanya',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 5, 'C' => 1, 'D' => 2, 'E' => 1])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Seorang ASN yang profesional menurut Anda adalah mereka yang...',
            'option_a' => 'Datang paling pagi tapi pulang paling awal',
            'option_b' => 'Bekerja hanya jika diawasi oleh atasan',
            'option_c' => 'Memiliki kompetensi yang sesuai, berintegritas tinggi, dan selalu berorientasi pada pelayanan masyarakat',
            'option_d' => 'Pandai mengambil hati atasan agar cepat naik pangkat',
            'option_e' => 'Mampu mengerjakan semua hal tanpa bantuan orang lain',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 2, 'B' => 1, 'C' => 5, 'D' => 2, 'E' => 3])
        ]);

        Question::create([
            'type_id' => $type, 'category' => 'TKP',
            'question_text' => 'Visi hidup Anda sebagai seorang calon Aparatur Sipil Negara di masa depan adalah...',
            'option_a' => 'Mendapatkan kemapanan ekonomi yang terjamin',
            'option_b' => 'Menjadi pejabat yang dihormati di lingkungan saya',
            'option_c' => 'Menjadi bagian dari agen perubahan yang berkontribusi nyata bagi kemajuan dan kesejahteraan Indonesia',
            'option_d' => 'Menghabiskan waktu sampai masa pensiun tiba',
            'option_e' => 'Memperluas jaringan bisnis pribadi melalui jabatan',
            'correct_answer' => '-', 
            'tkp_points' => json_encode(['A' => 3, 'B' => 2, 'C' => 5, 'D' => 1, 'E' => 1])
        ]);
    }
}