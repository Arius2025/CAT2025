<?php

namespace App\Support;

use App\Models\ExamMode;
use App\Models\ExamModePackage;
use App\Models\SubCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ExamStructureSupport
{
    public static function packageSchemaReady(): bool
    {
        return Schema::hasTable('exam_mode_packages');
    }

    public static function schemaReady(): bool
    {
        return Schema::hasTable('exam_modes') && Schema::hasTable('sub_categories');
    }

    public static function modes(): Collection
    {
        if (self::schemaReady()) {{
            return ExamMode::query()->orderBy('id')->get();
        }}

        return collect([
            (object) ['id' => 1, 'code' => 'skd', 'name' => 'SKD'],
            (object) ['id' => 2, 'code' => 'skb', 'name' => 'SKB'],
            (object) ['id' => 3, 'code' => 'pppk', 'name' => 'PPPK'],
        ]);
    }

    public static function defaultMode(): ?object
    {
        if (self::schemaReady()) {{
            return ExamMode::query()->where('code', 'skd')->first() ?: ExamMode::query()->first();
        }}

        return (object) ['id' => 1, 'code' => 'skd', 'name' => 'SKD'];
    }

    public static function subCategories(?int $examModeId = null, ?string $examModeCode = null, ?string $kind = 'formasi'): Collection
    {
        if (!self::schemaReady()) {{
            return collect();
        }}

        $query = SubCategory::query()->with('examMode');

        if ($examModeId) {{
            $query->where('exam_mode_id', $examModeId);
        }} elseif ($examModeCode) {{
            $query->whereHas('examMode', fn ($q) => $q->where('code', strtolower(trim($examModeCode))));
        }}

        if ($kind !== null && Schema::hasColumn('sub_categories', 'kind')) {{
            $query->where('kind', $kind);
        }}

        return $query->orderBy('name')->get();
    }

    public static function packages(?int $examModeId = null, ?string $examModeCode = null): Collection
    {
        if (self::packageSchemaReady()) {{
            $query = ExamModePackage::query()->where('is_active', true);

            if ($examModeId) {{
                $query->where('exam_mode_id', $examModeId);
            }} elseif ($examModeCode) {{
                $query->whereHas('examMode', fn ($q) => $q->where('code', strtolower(trim($examModeCode))));
            }}

            $items = $query->orderBy('sort_order')->orderBy('code')->get();
            if ($items->isNotEmpty()) {{
                return $items;
            }}
        }}

        $code = strtolower(trim((string) ($examModeCode ?? 'skd')));
        if (empty($code) && $examModeId) {{
            $mode = self::modes()->firstWhere('id', $examModeId);
            $code = strtolower(trim((string) ($mode->code ?? 'skd')));
        }}

        return collect(self::defaultPackages($code))->map(function ($pkg) use ($examModeId) {{
            return (object) array_merge($pkg, [
                'id' => null,
                'exam_mode_id' => $examModeId,
            ]);
        }});
    }

    public static function package(?int $examModeId = null, ?string $examModeCode = null, ?string $packageCode = null): ?object
    {
        $packages = self::packages($examModeId, $examModeCode);
        $code = strtoupper(trim((string) ($packageCode ?? '')));

        if ($code !== '') {{
            $matched = $packages->firstWhere('code', $code);
            if ($matched) {{
                return $matched;
            }}
        }}

        return $packages->first();
    }

    public static function packageConfig(?int $examModeId = null, ?string $examModeCode = null, ?string $packageCode = null): ?object
    {
        return self::package($examModeId, $examModeCode, $packageCode);
    }

    public static function defaultPackages(string $modeCode): array
    {
        $code = strtolower(trim($modeCode));
        $map = [
            'skd' => [
                [
                    'code' => 'A',
                    'name' => 'TWK',
                    'description' => 'Wawasan Kebangsaan',
                    'duration_minutes' => 30,
                    'total_questions' => 30,
                    'sections' => [['key' => 'TWK', 'count' => 30, 'label' => 'TWK', 'kategori' => 'TWK', 'pass_min' => 65, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5]],
                    'passing_rules' => ['logic' => 'all_sections', 'section_min' => ['TWK' => 65]],
                    'score_rules' => ['summary' => 'SKD TWK: benar 5, salah 0, kosong 0.'],
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'code' => 'B',
                    'name' => 'TIU',
                    'description' => 'Intelegensia Umum',
                    'duration_minutes' => 30,
                    'total_questions' => 35,
                    'sections' => [['key' => 'TIU', 'count' => 35, 'label' => 'TIU', 'kategori' => 'TIU', 'pass_min' => 80, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5]],
                    'passing_rules' => ['logic' => 'all_sections', 'section_min' => ['TIU' => 80]],
                    'score_rules' => ['summary' => 'SKD TIU: benar 5, salah 0, kosong 0.'],
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                [
                    'code' => 'C',
                    'name' => 'TKP',
                    'description' => 'Karakteristik Pribadi',
                    'duration_minutes' => 30,
                    'total_questions' => 45,
                    'sections' => [['key' => 'TKP', 'count' => 45, 'label' => 'TKP', 'kategori' => 'TKP', 'pass_min' => 166, 'score_type' => 'weighted_option', 'blank_score' => 0, 'max_option_score' => 5, 'min_option_score' => 1]],
                    'passing_rules' => ['logic' => 'all_sections', 'section_min' => ['TKP' => 166]],
                    'score_rules' => ['summary' => 'SKD TKP: bobot 1-5 per opsi, kosong 0.'],
                    'is_active' => true,
                    'sort_order' => 3,
                ],
                [
                    'code' => 'D',
                    'name' => 'Full SKD',
                    'description' => 'Tryout lengkap TWK, TIU, dan TKP.',
                    'duration_minutes' => 100,
                    'total_questions' => 110,
                    'sections' => [['key' => 'TWK', 'count' => 30, 'label' => 'TWK', 'kategori' => 'TWK', 'pass_min' => 65, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5], ['key' => 'TIU', 'count' => 35, 'label' => 'TIU', 'kategori' => 'TIU', 'pass_min' => 80, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5], ['key' => 'TKP', 'count' => 45, 'label' => 'TKP', 'kategori' => 'TKP', 'pass_min' => 166, 'score_type' => 'weighted_option', 'blank_score' => 0, 'max_option_score' => 5, 'min_option_score' => 1]],
                    'passing_rules' => ['logic' => 'all_sections', 'section_min' => ['TIU' => 80, 'TKP' => 166, 'TWK' => 65]],
                    'score_rules' => ['summary' => 'Full SKD mengikuti bobot resmi TWK/TIU/TKP.'],
                    'is_active' => true,
                    'sort_order' => 4,
                ],
            ],
            'skb' => [
                [
                    'code' => 'A',
                    'name' => 'Materi Inti Bidang',
                    'description' => 'Latihan materi inti bidang sesuai jabatan. Cocok untuk latihan kompetensi keahlian dan keprofesian.',
                    'duration_minutes' => 45,
                    'total_questions' => 40,
                    'sections' => [['key' => 'SKB_TEKNIS', 'count' => 40, 'label' => 'Materi Inti Bidang Jabatan', 'kategori' => 'SKB_TEKNIS', 'pass_min' => null, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5, 'recommended_topics' => ['Pemahaman Dasar Jabatan', 'Kompetensi Teknis Lapangan', 'Penerapan Prosedur Kerja', 'Pengetahuan Spesifik Bidang']]],
                    'passing_rules' => ['logic' => 'rank_only'],
                    'score_rules' => ['summary' => 'Template SKB: benar 5, salah 0, kosong 0.', 'source_hint' => 'Latihan SKB berfokus pada skill teknis keprofesian.'],
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'code' => 'B',
                    'name' => 'Regulasi dan Analisis',
                    'description' => 'Latihan regulasi, standar profesi, dan analisis bidang.',
                    'duration_minutes' => 35,
                    'total_questions' => 30,
                    'sections' => [['key' => 'SKB_REGULASI', 'count' => 30, 'label' => 'Regulasi & Undang-Undang', 'kategori' => 'SKB_REGULASI', 'pass_min' => null, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5, 'recommended_topics' => ['Regulasi Sektoral', 'SOP Jabatan', 'Standar Profesi', 'Keselamatan Kerja', 'Aturan Perundangan']]],
                    'passing_rules' => ['logic' => 'rank_only'],
                    'score_rules' => ['summary' => 'Template SKB: benar 5, salah 0, kosong 0.', 'source_hint' => 'Latihan SKB berfokus pada skill teknis keprofesian.'],
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                [
                    'code' => 'C',
                    'name' => 'Studi Kasus Bidang',
                    'description' => 'Latihan kasus atau analisis situasi bidang.',
                    'duration_minutes' => 30,
                    'total_questions' => 20,
                    'sections' => [['key' => 'SKB_STUDI_KASUS', 'count' => 20, 'label' => 'Studi Kasus Jabatan', 'kategori' => 'SKB_STUDI_KASUS', 'pass_min' => null, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5, 'recommended_topics' => ['Kasus Interaksi Obat', 'Kasus Pelayanan Pasien', 'Kasus Medication Error', 'Kasus Monitoring Terapi', 'Kasus Pengelolaan Stok Obat']]],
                    'passing_rules' => ['logic' => 'rank_only'],
                    'score_rules' => ['summary' => 'Template SKB dapat diedit sesuai materi pokok jabatan resmi.', 'source_hint' => 'Preset SKB default difokuskan untuk bidang IT dan tetap editable sesuai kebutuhan instansi.'],
                    'is_active' => true,
                    'sort_order' => 3,
                ],
                [
                    'code' => 'D',
                    'name' => 'Full SKB',
                    'description' => 'Template simulasi SKB lengkap. Silakan sesuaikan dengan materi pokok jabatan pada setting admin.',
                    'duration_minutes' => 90,
                    'total_questions' => 90,
                    'sections' => [['key' => 'SKB_TEKNIS', 'count' => 40, 'label' => 'Materi Inti Bidang Jabatan', 'kategori' => 'SKB_TEKNIS', 'pass_min' => null, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5, 'recommended_topics' => ['Pemahaman Dasar Jabatan', 'Kompetensi Teknis Lapangan', 'Penerapan Prosedur Kerja', 'Pengetahuan Spesifik Bidang']], ['key' => 'SKB_REGULASI', 'count' => 30, 'label' => 'Regulasi & Undang-Undang', 'kategori' => 'SKB_REGULASI', 'pass_min' => null, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5, 'recommended_topics' => ['Regulasi Sektoral', 'SOP Jabatan', 'Standar Profesi', 'Keselamatan Kerja', 'Aturan Perundangan']], ['key' => 'SKB_STUDI_KASUS', 'count' => 20, 'label' => 'Studi Kasus Jabatan', 'kategori' => 'SKB_STUDI_KASUS', 'pass_min' => null, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5, 'recommended_topics' => ['Kasus Interaksi Obat', 'Kasus Pelayanan Pasien', 'Kasus Medication Error', 'Kasus Monitoring Terapi', 'Kasus Pengelolaan Stok Obat']]],
                    'passing_rules' => ['logic' => 'rank_only', 'notes' => 'Template default. Edit sesuai kebutuhan instansi dan jabatan.'],
                    'score_rules' => ['summary' => 'Template SKB default.', 'source_hint' => 'Default ini bisa dijadikan dasar SKB bidang IT lalu disesuaikan per instansi.'],
                    'is_active' => true,
                    'sort_order' => 4,
                ],
            ],
            'pppk' => [
                [
                    'code' => 'A',
                    'name' => 'Kompetensi Teknis',
                    'description' => 'Kompetensi teknis sesuai jabatan atau rumpun formasi.',
                    'duration_minutes' => 120,
                    'total_questions' => 90,
                    'sections' => [['key' => 'TEKNIS', 'count' => 90, 'label' => 'Kompetensi Teknis', 'kategori' => 'TEKNIS', 'pass_min' => null, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5, 'recommended_topics' => ['Kompetensi Dasar Bidang', 'Pengetahuan Teknis Jabatan', 'Studi Kasus Lapangan', 'Regulasi Sektoral']]],
                    'passing_rules' => ['logic' => 'rank_only', 'notes' => 'Nilai ambang teknis PPPK mengikuti jabatan pada lampiran resmi dan dapat berbeda per formasi.'],
                    'score_rules' => ['summary' => 'PPPK Teknis: benar 5, salah 0, kosong 0.', 'source_hint' => 'Soal teknis dibuat objektif sesuai formasi jabatan masing-masing.'],
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'code' => 'B',
                    'name' => 'Manajerial',
                    'description' => 'Latihan kompetensi manajerial.',
                    'duration_minutes' => 30,
                    'total_questions' => 25,
                    'sections' => [['key' => 'MANAJERIAL', 'count' => 25, 'label' => 'Manajerial', 'kategori' => 'MANAJERIAL', 'pass_min' => null, 'score_type' => 'weighted_scale', 'blank_score' => 0, 'max_option_score' => 4, 'min_option_score' => 1]],
                    'passing_rules' => ['logic' => 'rank_only'],
                    'score_rules' => ['summary' => 'PPPK Manajerial: bobot 1-4 per opsi, kosong 0.'],
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                [
                    'code' => 'C',
                    'name' => 'Sosio-Kultural',
                    'description' => 'Latihan kompetensi sosial kultural.',
                    'duration_minutes' => 25,
                    'total_questions' => 20,
                    'sections' => [['key' => 'SOSIAL_KULTURAL', 'count' => 20, 'label' => 'Sosio-Kultural', 'kategori' => 'SOSIAL_KULTURAL', 'pass_min' => null, 'score_type' => 'weighted_scale', 'blank_score' => 0, 'max_option_score' => 4, 'min_option_score' => 1]],
                    'passing_rules' => ['logic' => 'rank_only'],
                    'score_rules' => ['summary' => 'PPPK Sosio-Kultural: bobot 1-4 per opsi, kosong 0.'],
                    'is_active' => true,
                    'sort_order' => 3,
                ],
                [
                    'code' => 'D',
                    'name' => 'Full PPPK',
                    'description' => 'Simulasi lengkap Teknis, Manajerial, Sosio-Kultural, dan Wawancara.',
                    'duration_minutes' => 130,
                    'total_questions' => 145,
                    'sections' => [['key' => 'TEKNIS', 'count' => 90, 'label' => 'Kompetensi Teknis', 'kategori' => 'TEKNIS', 'pass_min' => null, 'score_type' => 'objective', 'blank_score' => 0, 'wrong_score' => 0, 'correct_score' => 5, 'recommended_topics' => ['Kompetensi Dasar Bidang', 'Pengetahuan Teknis Jabatan', 'Studi Kasus Lapangan', 'Regulasi Sektoral']], ['key' => 'MANAJERIAL', 'count' => 25, 'label' => 'Manajerial', 'kategori' => 'MANAJERIAL', 'pass_min' => null, 'score_type' => 'weighted_scale', 'blank_score' => 0, 'max_option_score' => 4, 'min_option_score' => 1], ['key' => 'SOSIAL_KULTURAL', 'count' => 20, 'label' => 'Sosio-Kultural', 'kategori' => 'SOSIAL_KULTURAL', 'pass_min' => null, 'score_type' => 'weighted_scale', 'blank_score' => 0, 'max_option_score' => 4, 'min_option_score' => 1], ['key' => 'WAWANCARA', 'count' => 10, 'label' => 'Wawancara', 'kategori' => 'WAWANCARA', 'pass_min' => 24, 'score_type' => 'weighted_scale', 'blank_score' => 0, 'max_option_score' => 4, 'min_option_score' => 1]],
                    'passing_rules' => ['logic' => 'groups_and_sections', 'notes' => 'Nilai ambang teknis PPPK mengikuti jabatan pada lampiran resmi. Atur manual bila ingin dikunci per sub kategori.', 'group_min' => [['min' => 117, 'label' => 'Manajerial + Sosio-Kultural', 'sections' => ['MANAJERIAL', 'SOSIAL_KULTURAL']]], 'section_min' => ['WAWANCARA' => 24]],
                    'score_rules' => ['summary' => 'PPPK Full: Teknis benar 5; Manajerial, Sosio-Kultural, Wawancara bobot 1-4.', 'source_hint' => 'Mode full PPPK mencakup kompetensi Teknis, Manajerial, Sosio-Kultural, dan Wawancara.'],
                    'is_active' => true,
                    'sort_order' => 4,
                ],
            ],
        ];

        return $map[$code] ?? ($map['skd'] ?? []);
    }

    public static function focusTopicPresets(): array
    {
        return [
            'skd' => [
                'TWK' => [
                    'Nasionalisme & Integritas Birokrasi',
                    'Bela Negara & Ketahanan Siber/Disinformasi',
                    'Pilar Negara (Pancasila & UUD NRI 1945 HOTS)',
                    'Harmonisasi Bhinneka Tunggal Ika & NKRI',
                    'Bahasa Indonesia Kedinasan (EYD V & Naskah Dinas)',
                ],
                'TIU' => [
                    'Kemampuan Verbal (Analogi Tingkat Lanjut)',
                    'Kemampuan Verbal (Silogisme Multi-Kuantor)',
                    'Kemampuan Verbal (Penalaran Analitis Posisi/Jadwal)',
                    'Kemampuan Numerik (Berhitung Cepat & Persentase)',
                    'Kemampuan Numerik (Deret Angka Bertingkat/Kombinasi)',
                    'Kemampuan Numerik (Perbandingan Kuantitatif X vs Y)',
                    'Kemampuan Numerik (Soal Cerita Aljabar & Kecepatan)',
                    'Kemampuan Figural (Analogi, Serial & Pola Sudut)',
                ],
                'TKP' => [
                    'Pelayanan Publik (Kepuasan Masyarakat & Empati Prima)',
                    'Jejaring Kerja (Kolaborasi & Resolusi Konflik Lintas Unit)',
                    'Sosial Budaya (Toleransi & Perekat Bangsa)',
                    'Teknologi Informasi & Komunikasi (Digital Mindset & SPBE)',
                    'Profesionalisme (Akuntabilitas Kinerja & Manajemen Waktu)',
                    'Anti-Radikalisme (Deteksi Intoleransi & Loyalitas Pancasila)',
                ],
                'FIGURAL' => [
                    'Analogi Gambar',
                    'Serial Perubahan Elemen & Pola',
                    'Ketidaksamaan Pola Visual',
                    'Rotasi Sudut & Konsistensi Spasial',
                    'Jaring-Jaring Kubus & Bentuk 3D',
                ],
            ],
            'pppk_apoteker' => [
                'TEKNIS' => ['Pengertian Apoteker', 'Farmasi Praktis', 'Farmasi Industri', 'Farmakologi', 'Farmakoterapi', 'Farmasi Klinis', 'Pelayanan Kefarmasian', 'Resep dan Dosis', 'Monitoring Efek Samping Obat', 'Regulasi Obat'],
                'MANAJERIAL' => ['Integritas', 'Kerja Sama', 'Komunikasi', 'Orientasi pada Hasil', 'Pelayanan Publik', 'Pengembangan Diri', 'Mengelola Perubahan', 'Pengambilan Keputusan'],
                'SOSIAL_KULTURAL' => ['Keberagaman Budaya', 'Pelayanan Inklusif', 'Etika Sosial', 'Empati Layanan', 'Konflik Sosial'],
                'WAWANCARA' => ['Motivasi Menjadi ASN', 'Etika Profesi', 'Kesiapan Penempatan', 'Komitmen Pelayanan', 'Pengalaman Bidang Farmasi'],
            ],
            'skb_apoteker' => [
                'SKB_TEKNIS' => ['Farmakologi Klinik', 'Farmakoterapi', 'Pelayanan Kefarmasian', 'Farmasi Klinik', 'Manajemen Obat', 'Analisis Resep'],
                'SKB_REGULASI' => ['Regulasi Kefarmasian', 'Standar Pelayanan Kefarmasian', 'Etika Profesi Apoteker', 'Mutu dan Keselamatan Obat', 'Perizinan dan Distribusi'],
                'SKB_STUDI_KASUS' => ['Kasus Interaksi Obat', 'Kasus Pelayanan Pasien', 'Kasus Medication Error', 'Kasus Monitoring Terapi', 'Kasus Pengelolaan Stok Obat'],
            ],
            'pppk_farmakologi' => [
                'TEKNIS' => ['Farmakokinetik', 'Farmakodinamik', 'Interaksi Obat', 'Efek Samping Obat', 'Toksikologi Dasar', 'Golongan Obat'],
            ],
            'pppk_farmakoterapi' => [
                'TEKNIS' => ['Terapi Hipertensi', 'Terapi Diabetes', 'Terapi Antibiotik', 'Pemilihan Obat Rasional', 'Evaluasi Regimen Terapi', 'Drug Related Problems'],
            ],
            'pppk_farmasi_klinis' => [
                'TEKNIS' => ['Patient Counseling', 'Medication Review', 'Monitoring Terapi Obat', 'Rekonsiliasi Obat', 'Pelayanan Informasi Obat', 'Identifikasi DRP'],
            ],
            'pppk_pelayanan_kefarmasian' => [
                'TEKNIS' => ['Standar Pelayanan Kefarmasian', 'Pelayanan Resep', 'Dispensing', 'Penyimpanan Obat', 'Pelayanan Informasi Obat', 'Pengelolaan Obat'],
            ],
            'pppk_resep_dosis' => [
                'TEKNIS' => ['Skrining Resep', 'Perhitungan Dosis', 'Aturan Pakai Obat', 'Konversi Sediaan', 'Stabilitas Racikan', 'Kesalahan Penulisan Resep'],
            ],
            'pppk_regulasi_obat' => [
                'TEKNIS' => ['Regulasi Obat', 'BPOM dan Perizinan', 'Distribusi dan Penyimpanan', 'Obat Narkotika dan Psikotropika', 'Pelaporan MESO', 'CDOB'],
            ],
            'skb_farmakologi_klinik' => [
                'SKB_TEKNIS' => ['Farmakologi Sistem Kardiovaskular', 'Farmakologi Antiinfeksi', 'Interaksi Obat Klinis', 'Efek Samping Klinis', 'Farmakokinetik Klinik', 'Pemantauan Obat'],
            ],
            'skb_farmasi_klinik' => [
                'SKB_TEKNIS' => ['Medication Review', 'Clinical Pathway', 'Drug Related Problems', 'Patient Safety', 'Monitoring Terapi', 'Konseling Pasien'],
            ],
            'skb_pelayanan_kefarmasian' => [
                'SKB_TEKNIS' => ['Standar Pelayanan', 'Dispensing', 'Pelayanan Informasi Obat', 'Pengelolaan Sediaan', 'Pelayanan Resep', 'Keselamatan Pasien'],
            ],
            'skb_analisis_resep' => [
                'SKB_TEKNIS' => ['Skrining Administratif', 'Skrining Farmasetik', 'Skrining Klinis', 'Perhitungan Dosis', 'Interaksi Resep', 'Koreksi Resep'],
                'SKB_STUDI_KASUS' => ['Kasus Resep Anak', 'Kasus Resep Geriatri', 'Kasus Polifarmasi', 'Kasus Interaksi Obat'],
            ],
            'skb_regulasi_kefarmasian' => [
                'SKB_REGULASI' => ['Standar Pelayanan Kefarmasian', 'Etika Profesi Apoteker', 'CDOB', 'Pelaporan MESO', 'Perizinan Apotek', 'Regulasi Obat Khusus'],
            ],
            'skb_studi_kasus_pasien' => [
                'SKB_STUDI_KASUS' => ['Kasus Medication Error', 'Kasus Monitoring Terapi', 'Kasus Konseling Pasien', 'Kasus Kepatuhan Minum Obat', 'Kasus Pengelolaan Stok'],
            ],
            'pppk_asisten_apoteker' => [
                'TEKNIS' => ['Pelayanan Kefarmasian Dasar', 'Penyiapan & Peracikan Obat', 'Pemberian Informasi Obat Dasar', 'Pengelolaan Sediaan Farmasi (Gudang)', 'Swamedikasi Dasar', 'Aturan Pakai & Dosis Dasar', 'Administrasi Farmasi', 'Etika Profesi Asisten Apoteker'],
                'MANAJERIAL' => ['Integritas', 'Kerja Sama', 'Komunikasi', 'Orientasi pada Hasil', 'Pelayanan Publik', 'Pengembangan Diri', 'Mengelola Perubahan', 'Pengambilan Keputusan'],
                'SOSIAL_KULTURAL' => ['Keberagaman Budaya', 'Pelayanan Inklusif', 'Etika Sosial', 'Empati Layanan', 'Konflik Sosial'],
                'WAWANCARA' => ['Motivasi Menjadi ASN', 'Komitmen Pelayanan Kesehatan', 'Kesiapan Penempatan', 'Pengalaman Pelayanan Farmasi'],
            ],
            'skb_asisten_apoteker' => [
                'SKB_TEKNIS' => ['Standar Pelayanan Kefarmasian', 'Peracikan Sediaan Dasar', 'Penyimpanan & Distribusi', 'Identifikasi Resep', 'Komunikasi Efektif Pasien'],
                'SKB_REGULASI' => ['UU Kesehatan Dasar', 'Standar Profesi Asisten Apoteker', 'Regulasi Obat Bebas & Terbatas'],
            ],
            'pppk_it_terampil' => [
                'TEKNIS' => ['Dasar Hardware & OS', 'Jaringan Komputer Dasar (LAN)', 'Software Troubleshooting', 'Maintenance Perangkat IT', 'Dasar Database SQL', 'Entri & Validasi Data', 'Teknik Pengolahan Data', 'Keamanan Informasi Dasar'],
                'MANAJERIAL' => ['Integritas', 'Kerja Sama', 'Komunikasi', 'Orientasi pada Hasil', 'Pelayanan Publik', 'Pengembangan Diri', 'Mengelola Perubahan', 'Pengambilan Keputusan'],
                'SOSIAL_KULTURAL' => ['Keberagaman Budaya', 'Pelayanan Inklusif', 'Etika Sosial', 'Empati Layanan', 'Konflik Sosial'],
                'WAWANCARA' => ['Motivasi Menjadi ASN', 'Etika Profesi IT', 'Kesiapan Penempatan', 'Komitmen Pelayanan', 'Pengalaman Proyek Praktis'],
            ],
            'pppk_it_ahli' => [
                'TEKNIS' => ['Analisis & Desain Sistem (SDLC)', 'Manajemen Proyek IT', 'Audit IT & Tata Kelola', 'Enterprise Data Architecture', 'Siber Security Lanjutan', 'Cloud Computing & Virtualization', 'Big Data & Data Science', 'Framework Development (MVC)'],
                'MANAJERIAL' => ['Integritas', 'Kerja Sama', 'Komunikasi', 'Orientasi pada Hasil', 'Pelayanan Publik', 'Pengembangan Diri', 'Mengelola Perubahan', 'Pengambilan Keputusan'],
                'SOSIAL_KULTURAL' => ['Keberagaman Budaya', 'Pelayanan Inklusif', 'Etika Sosial', 'Empati Layanan', 'Konflik Sosial'],
                'WAWANCARA' => ['Motivasi Menjadi ASN', 'Etika Profesi IT Ahli', 'Kesiapan Penempatan', 'Komitmen Pelayanan', 'Pengalaman Manajerial IT'],
            ],
            'pppk_keperawatan_s1' => [
                'TEKNIS' => ['Asuhan Keperawatan Medikal Bedah', 'Keperawatan Gawat Darurat', 'Keperawatan Komunitas', 'Patient Safety', 'Komunikasi Terapeutik', 'Etika Keperawatan'],
                'MANAJERIAL' => ['Integritas', 'Kerja Sama Tim Klinis', 'Prioritas Tindakan', 'Pelayanan Pasien', 'Adaptasi Shift Layanan'],
                'SOSIAL_KULTURAL' => ['Empati Pasien', 'Komunikasi dengan Keluarga', 'Pelayanan Inklusif', 'Sensitivitas Budaya', 'Etika Sosial Kesehatan'],
                'WAWANCARA' => ['Motivasi Menjadi ASN Kesehatan', 'Komitmen Pelayanan', 'Kesiapan Penempatan', 'Etika Profesi Perawat'],
            ],
            'pppk_anastesi_d4' => [
                'TEKNIS' => ['Persiapan Tindakan Anestesi', 'Monitoring Pasien Perioperatif', 'Keselamatan Pasien', 'Peralatan Anestesi', 'Farmakologi Anestesi Dasar', 'Penanganan Komplikasi Dasar'],
                'MANAJERIAL' => ['Kerja Sama Tim Bedah', 'Komunikasi Klinis', 'Prioritas Tindakan', 'Disiplin Prosedur', 'Pelayanan Pasien'],
                'SOSIAL_KULTURAL' => ['Empati Pasien', 'Komunikasi dengan Keluarga', 'Pelayanan Inklusif', 'Etika Sosial Kesehatan', 'Sensitivitas Budaya'],
                'WAWANCARA' => ['Motivasi Menjadi ASN Kesehatan', 'Kesiapan Shift dan Penempatan', 'Etika Profesi Anastesi', 'Komitmen Keselamatan Pasien'],
            ],
            'skb_it_terampil' => [
                'SKB_TEKNIS' => ['Troubleshooting Aplikasi', 'Administrasi Basis Data Dasar', 'Jaringan LAN', 'Maintenance Perangkat TI', 'Analisis Kebutuhan Sistem Sederhana', 'Dokumentasi Teknis Operasional'],
                'SKB_REGULASI' => ['SOP Operasional TI', 'Keamanan Data Dasar', 'Tata Kelola Helpdesk', 'Kebijakan Backup dan Recovery Dasar'],
                'SKB_STUDI_KASUS' => ['Kasus Gangguan Jaringan Kantor', 'Kasus Error Aplikasi Internal', 'Kasus Validasi Data Operasional', 'Kasus Pemeliharaan Perangkat'],
            ],
            'skb_it_ahli' => [
                'SKB_TEKNIS' => ['Arsitektur Sistem Terdistribusi', 'Cloud Computing', 'Data Enterprise Management', 'Siber Security Lanjutan', 'Analisis dan Desain Sistem', 'Framework Development Enterprise'],
                'SKB_REGULASI' => ['Tata Kelola TI', 'Audit Sistem Informasi', 'Keamanan Siber dan Kepatuhan', 'Manajemen Risiko Teknologi'],
                'SKB_STUDI_KASUS' => ['Kasus Integrasi Sistem', 'Kasus Desain Arsitektur Aplikasi', 'Kasus Incident Response', 'Kasus Optimasi Kinerja Sistem'],
            ],
            'skb_keperawatan_s1' => [
                'SKB_TEKNIS' => ['Asuhan Keperawatan Komprehensif', 'Keperawatan Gawat Darurat', 'Patient Safety', 'Triase Dasar', 'Komunikasi Terapeutik', 'Dokumentasi Keperawatan'],
                'SKB_REGULASI' => ['Etika Profesi Perawat', 'SOP Keperawatan', 'Keselamatan Pasien', 'Regulasi Pelayanan Keperawatan'],
                'SKB_STUDI_KASUS' => ['Kasus Prioritas Keperawatan', 'Kasus Komunikasi Pasien', 'Kasus Respon Darurat', 'Kasus Edukasi Pasien'],
            ],
            'skb_anastesi_d4' => [
                'SKB_TEKNIS' => ['Persiapan Alat Anestesi', 'Monitoring Pasien Anestesi', 'Keselamatan Pasien Perioperatif', 'Farmakologi Anestesi Dasar', 'Airway Management Dasar', 'Tindakan Pra-Anestesi'],
                'SKB_REGULASI' => ['SOP Anastesi', 'Keselamatan Kamar Operasi', 'Etika Profesi Anastesi', 'Regulasi Pelayanan Perioperatif'],
                'SKB_STUDI_KASUS' => ['Kasus Monitoring Intraoperatif', 'Kasus Komplikasi Ringan', 'Kasus Persiapan Operasi', 'Kasus Recovery Room'],
            ],
        
        ];
    }

    public static function resolveFocusTopicPresetKey(?string $examModeCode = null, ?string $subCategoryName = null): string
    {
        $mode = strtolower(trim((string) ($examModeCode ?? '')));
        $sub = strtolower(trim((string) ($subCategoryName ?? '')));

        $subMap = [
            'pppk' => [
                '/d3 farmasi|farmasi d3/' => 'pppk_asisten_apoteker',
                '/d3 manajemen informatika|manajemen informatika d3/' => 'pppk_it_terampil',
                '/s1 teknik informatika|teknik informatika s1/' => 'pppk_it_ahli',
                '/s1 keperawatan|keperawatan s1/' => 'pppk_keperawatan_s1',
                '/d4 anastesi|anastesi d4/' => 'pppk_anastesi_d4',
                '/farmakologi/' => 'pppk_farmakologi',
                '/farmakoterapi/' => 'pppk_farmakoterapi',
                '/farmasi klinis/' => 'pppk_farmasi_klinis',
                '/pelayanan kefarmasian/' => 'pppk_pelayanan_kefarmasian',
                '/resep|dosis/' => 'pppk_resep_dosis',
                '/regulasi obat/' => 'pppk_regulasi_obat',
            ],
            'skb' => [
                '/d3 manajemen informatika|manajemen informatika d3/' => 'skb_it_terampil',
                '/s1 teknik informatika|teknik informatika s1/' => 'skb_it_ahli',
                '/s1 keperawatan|keperawatan s1/' => 'skb_keperawatan_s1',
                '/d4 anastesi|anastesi d4/' => 'skb_anastesi_d4',
                '/farmakologi klinik/' => 'skb_farmakologi_klinik',
                '/farmasi klinik/' => 'skb_farmasi_klinik',
                '/pelayanan kefarmasian/' => 'skb_pelayanan_kefarmasian',
                '/analisis resep/' => 'skb_analisis_resep',
                '/regulasi kefarmasian/' => 'skb_regulasi_kefarmasian',
                '/studi kasus pasien/' => 'skb_studi_kasus_pasien',
            ],
        ];

        foreach (($subMap[$mode] ?? []) as $pattern => $key) {{
            if (preg_match($pattern, $sub)) {{
                return $key;
            }}
        }}

        return $mode !== '' ? $mode : 'skd';
    }

    public static function recommendedTopics(?string $examModeCode = null, ?string $subCategoryName = null, ?string $category = null): array
    {
        $presets = self::focusTopicPresets();
        $key = self::resolveFocusTopicPresetKey($examModeCode, $subCategoryName);
        $rows = $presets[$key] ?? ($presets[$examModeCode ?? ''] ?? []);
        $cat = strtoupper(trim((string) ($category ?? '')));

        if ($cat === '') {{
            return $rows;
        }}

        $specific = array_values($rows[$cat] ?? []);
        $baseKey = in_array($examModeCode, ['pppk', 'skb', 'skd'], true) ? $examModeCode : 'skd';
        $baseRows = $presets[$baseKey] ?? [];
        $base = array_values($baseRows[$cat] ?? []);

        return array_values(array_unique(array_merge($specific, $base)));
    }

    public static function categoryOptions(?int $examModeId = null, ?string $examModeCode = null): Collection
    {
        $rows = [];
        foreach (self::packages($examModeId, $examModeCode) as $package) {{
            foreach ((array) ($package->sections ?? []) as $section) {{
                $kategori = strtoupper(trim((string) ($section['kategori'] ?? '')));
                if ($kategori === '') {{
                    continue;
                }}

                if (!isset($rows[$kategori])) {{
                    $rows[$kategori] = (object) [
                        'value' => $kategori,
                        'label' => (string) ($section['label'] ?? str_replace('_', ' ', $kategori)),
                        'score_type' => (string) ($section['score_type'] ?? 'objective'),
                    ];
                }}
            }}
        }}

        if ($examModeCode === 'skd' || empty($rows)) {{
            $rows['FIGURAL'] = (object) ['value' => 'FIGURAL', 'label' => 'Figural', 'score_type' => 'objective'];
        }}

        return collect(array_values($rows));
    }
}
