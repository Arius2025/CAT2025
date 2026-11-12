<?php

namespace App\Services;

use App\Models\ExamMode;
use App\Models\Soal;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SoalBackupService
{
    /**
     * Normalisasi teks umum (strip html, decode entity, lowercase, collapse whitespace).
     */
    public static function normalizeText(?string $value): string
    {
        $s = (string) ($value ?? '');
        $s = strip_tags($s);
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (function_exists('mb_strtolower')) {
            $s = mb_strtolower($s, 'UTF-8');
        } else {
            $s = strtolower($s);
        }
        $s = preg_replace('/\s+/u', ' ', trim($s));
        return $s ?? '';
    }

    /**
     * Normalisasi stem naskah soal untuk perbandingan duplicate detection.
     */
    public static function normalizeQuestionStem(?string $value): string
    {
        $s = self::normalizeText($value);
        // Hapus label opsi trailing jika ada (misal "a. ...")
        $s = preg_replace('/\b[a-e]\s*[\.\:\)]\s*.*$/is', '', $s) ?? $s;
        // Hapus tanda baca non-alfanumerik agar sedikit perbedaan titik/koma tetap terdeteksi sebagai soal sama
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s) ?? $s;
        $s = preg_replace('/\s+/u', ' ', trim($s)) ?? '';
        return $s;
    }

    /**
     * Hitung SHA1 hash konten soal berbasis teks + kelima opsi + kategori.
     */
    public static function computeContentHash(array $row, string $kategori): string
    {
        $parts = [
            self::normalizeText($row['teks_soal'] ?? ''),
            self::normalizeText($row['opsi_a'] ?? ''),
            self::normalizeText($row['opsi_b'] ?? ''),
            self::normalizeText($row['opsi_c'] ?? ''),
            self::normalizeText($row['opsi_d'] ?? ''),
            self::normalizeText($row['opsi_e'] ?? ''),
            strtoupper(trim($kategori)),
        ];
        return sha1(implode('|', $parts));
    }

    /**
     * Membangun query builder untuk ekspor berdasarkan filter.
     */
    public function buildExportQuery(array $filters = []): Builder
    {
        $query = Soal::query()->with(['examMode', 'subCategory'])->orderBy('id');

        $ids = $filters['ids'] ?? null;
        if (!empty($ids)) {
            if (is_string($ids)) {
                $idList = array_filter(array_map('intval', explode(',', $ids)));
            } else {
                $idList = array_filter(array_map('intval', (array) $ids));
            }
            if (!empty($idList)) {
                return $query->whereIn('id', $idList);
            }
        }

        $search = trim((string) ($filters['q'] ?? $filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
                $q->orWhere('teks_soal', 'like', '%' . $search . '%')
                    ->orWhere('pembahasan', 'like', '%' . $search . '%')
                    ->orWhere('sub_topik', 'like', '%' . $search . '%');
            });
        }

        $kategori = strtoupper(trim((string) ($filters['kategori'] ?? '')));
        if ($kategori !== '') {
            if ($kategori === 'FIGURAL') {
                $query->where('kategori', 'TIU')
                    ->where(function ($q) {
                        if (Schema::hasColumn('soals', 'prompt_gambar_soal')) {
                            $q->whereNotNull('prompt_gambar_soal');
                        }
                        $q->orWhere('sub_topik', 'LIKE', '%Figural%')
                            ->orWhere('sub_topik', 'LIKE', '%Spasial%')
                            ->orWhere('sub_topik', 'LIKE', '%Visual%')
                            ->orWhere('sub_topik', 'LIKE', '%Gambar%')
                            ->orWhere('sub_topik', 'LIKE', '%Pola%');
                    });
            } else {
                $query->where('kategori', $kategori);
            }
        }

        $subTopik = trim((string) ($filters['sub_topik'] ?? ''));
        if ($subTopik !== '') {
            $query->where('sub_topik', 'like', '%' . $subTopik . '%');
        }

        $examModeId = (int) ($filters['exam_mode_id'] ?? 0);
        if ($examModeId > 0 && Schema::hasColumn('soals', 'exam_mode_id')) {
            $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
            $query->forExamMode($examModeId, $defaultSkdId);
        }

        $subCategoryId = (int) ($filters['sub_category_id'] ?? 0);
        if ($subCategoryId > 0 && Schema::hasColumn('soals', 'sub_category_id')) {
            $query->forSubCategory($subCategoryId);
        }

        $difficulty = trim((string) ($filters['difficulty'] ?? ''));
        if ($difficulty !== '' && Schema::hasColumn('soals', 'difficulty')) {
            $query->where('difficulty', $difficulty);
        }

        $standarTahun = trim((string) ($filters['standar_tahun'] ?? ''));
        if ($standarTahun !== '' && Schema::hasColumn('soals', 'standar_tahun')) {
            if ($standarTahun === '2027') {
                $query->where('standar_tahun', '2027');
            } elseif ($standarTahun === 'pra-2027') {
                $query->where(function ($q) {
                    $q->whereNull('standar_tahun')->orWhere('standar_tahun', '!=', '2027');
                });
            }
        }

        return $query;
    }

    /**
     * Format model Soal menjadi array ekspor standar.
     */
    public static function formatSoalForExport(Soal $soal): array
    {
        $bobot = $soal->bobot_tkp;
        if (is_string($bobot)) {
            $decoded = json_decode($bobot, true);
            $bobot = is_array($decoded) ? $decoded : null;
        }

        return [
            'id' => (int) $soal->id,
            'kategori' => (string) $soal->kategori,
            'standar_tahun' => (string) ($soal->standar_tahun ?? '2027'),
            'sub_topik' => $soal->sub_topik ?: null,
            'difficulty' => $soal->difficulty ?: 'medium',
            'exam_mode' => $soal->examMode?->code ?? 'skd',
            'exam_mode_name' => $soal->examMode?->name ?? 'SKD',
            'sub_category' => $soal->subCategory?->name ?: null,
            'teks_soal' => (string) $soal->teks_soal,
            'opsi_a' => (string) $soal->opsi_a,
            'opsi_b' => (string) $soal->opsi_b,
            'opsi_c' => (string) $soal->opsi_c,
            'opsi_d' => (string) $soal->opsi_d,
            'opsi_e' => (string) $soal->opsi_e,
            'jawaban_benar' => (string) ($soal->jawaban_benar ?? 'A'),
            'bobot_tkp' => $bobot,
            'pembahasan' => (string) ($soal->pembahasan ?? ''),
            'hash_soal' => $soal->hash_soal ?: null,
            'created_at' => $soal->created_at ? $soal->created_at->toIso8601String() : null,
        ];
    }

    /**
     * Ekspor bank soal dalam format JSON streamed download.
     */
    public function exportJson(array $filters = []): StreamedResponse
    {
        $filename = 'backup-bank-soal-cat-' . now()->format('Y-m-d-His') . '.json';
        $query = $this->buildExportQuery($filters);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "[\n");
            $first = true;

            $query->chunk(200, function ($batch) use ($out, &$first) {
                foreach ($batch as $soal) {
                    $item = self::formatSoalForExport($soal);
                    $json = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                    $json = preg_replace('/^/m', '  ', (string) $json);

                    if (!$first) {
                        fwrite($out, ",\n");
                    }
                    fwrite($out, $json);
                    $first = false;
                }
            });

            fwrite($out, "\n]\n");
            fclose($out);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Ekspor bank soal dalam format CSV streamed download (Excel friendly BOM).
     */
    public function exportCsv(array $filters = []): StreamedResponse
    {
        $filename = 'backup-bank-soal-cat-' . now()->format('Y-m-d-His') . '.csv';
        $query = $this->buildExportQuery($filters);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM agar Excel menampilkan huruf latin/simbol dengan benar
            fwrite($out, "\xEF\xBB\xBF");

            // Header baris CSV
            fputcsv($out, [
                'ID',
                'Kategori',
                'Standar Tahun',
                'Sub Topik',
                'Difficulty',
                'Mode Ujian',
                'Sub Kategori',
                'Teks Soal',
                'Opsi A',
                'Opsi B',
                'Opsi C',
                'Opsi D',
                'Opsi E',
                'Kunci Jawaban',
                'Bobot TKP',
                'Pembahasan',
                'Hash Soal',
            ]);

            $query->chunk(250, function ($batch) use ($out) {
                foreach ($batch as $soal) {
                    $data = self::formatSoalForExport($soal);
                    $bobotStr = !empty($data['bobot_tkp']) ? json_encode($data['bobot_tkp'], JSON_UNESCAPED_UNICODE) : '';

                    fputcsv($out, [
                        $data['id'],
                        $data['kategori'],
                        $data['standar_tahun'],
                        $data['sub_topik'] ?? '',
                        $data['difficulty'],
                        $data['exam_mode'],
                        $data['sub_category'] ?? '',
                        $data['teks_soal'],
                        $data['opsi_a'],
                        $data['opsi_b'],
                        $data['opsi_c'],
                        $data['opsi_d'],
                        $data['opsi_e'],
                        $data['jawaban_benar'],
                        $bobotStr,
                        $data['pembahasan'],
                        $data['hash_soal'] ?? '',
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Membaca dan mengimpor file backup (JSON atau CSV) dengan proteksi duplikat ketat.
     *
     * @param UploadedFile $file
     * @param array $options ['standar_tahun' => '2027'|'pra-2027'|'keep', 'default_exam_mode_id' => int]
     * @return array
     */
    public function import(UploadedFile $file, array $options = []): array
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $realPath = $file->getRealPath();

        // 1. Parse rows berdasarkan format file
        $rawRows = [];
        if ($extension === 'json' || $file->getClientMimeType() === 'application/json') {
            $content = file_get_contents($realPath);
            $decoded = json_decode($content, true);

            if (is_array($decoded)) {
                if (isset($decoded['soals']) && is_array($decoded['soals'])) {
                    $rawRows = $decoded['soals'];
                } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
                    $rawRows = $decoded['data'];
                } else {
                    $rawRows = $decoded;
                }
            }
        } else {
            // CSV parsing
            $rawRows = $this->parseCsvFile($realPath);
        }

        if (empty($rawRows) || !is_array($rawRows)) {
            return [
                'total' => 0,
                'imported' => 0,
                'skipped_duplicates' => 0,
                'errors' => 1,
                'error_messages' => ['Format berkas tidak valid atau tidak berisi data butir soal.'],
            ];
        }

        // 2. Siapkan cache kamus mode ujian dan sub kategori
        $examModes = Schema::hasTable('exam_modes')
            ? ExamMode::all()
            : collect();
        $defaultSkd = $examModes->firstWhere('code', 'skd') ?? $examModes->first();
        $defaultModeId = !empty($options['default_exam_mode_id'])
            ? (int) $options['default_exam_mode_id']
            : ($defaultSkd?->id ?? null);

        $subCategories = Schema::hasTable('sub_categories')
            ? SubCategory::all()
            : collect();

        // 3. Bangun peta Memory Anti-Duplikasi dari seluruh database yang sudah ada
        // Menggunakan hash_soal dan normalized stem soal per kategori
        $existingHashes = [];
        $existingStems = [];

        Soal::query()->select(['id', 'kategori', 'hash_soal', 'teks_soal'])->chunk(500, function ($batch) use (&$existingHashes, &$existingStems) {
            foreach ($batch as $soal) {
                $kat = strtoupper(trim((string) $soal->kategori));
                if (!empty($soal->hash_soal)) {
                    $existingHashes[$soal->hash_soal] = true;
                }
                $stem = self::normalizeQuestionStem($soal->teks_soal);
                if ($stem !== '') {
                    $stemHash = sha1($kat . '::' . $stem);
                    $existingStems[$stemHash] = true;
                }
            }
        });

        $targetStandar = $options['standar_tahun'] ?? '2027';
        $userId = auth()->id();

        $importedCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;
        $errorMessages = [];

        DB::beginTransaction();
        try {
            foreach ($rawRows as $idx => $row) {
                if (!is_array($row)) {
                    $errorCount++;
                    continue;
                }

                // Normalisasi kunci array (case-insensitive & snake_case)
                $normalizedRow = $this->normalizeRowKeys($row);

                $teksSoal = trim((string) ($normalizedRow['teks_soal'] ?? $normalizedRow['pertanyaan'] ?? $normalizedRow['soal'] ?? ''));
                $opsiA = trim((string) ($normalizedRow['opsi_a'] ?? ''));
                $opsiB = trim((string) ($normalizedRow['opsi_b'] ?? ''));
                $opsiC = trim((string) ($normalizedRow['opsi_c'] ?? ''));
                $opsiD = trim((string) ($normalizedRow['opsi_d'] ?? ''));
                $opsiE = trim((string) ($normalizedRow['opsi_e'] ?? ''));

                // Validasi kelengkapan teks dan opsi
                if ($teksSoal === '' || $opsiA === '' || $opsiB === '' || $opsiC === '') {
                    $errorCount++;
                    $errorMessages[] = "Baris #" . ($idx + 1) . ": Teks soal atau opsi jawaban tidak lengkap.";
                    continue;
                }

                // Kategori
                $kategori = strtoupper(trim((string) ($normalizedRow['kategori'] ?? 'TWK')));
                if (!in_array($kategori, ['TWK', 'TIU', 'TKP', 'SKB', 'PPPK', 'FIGURAL'])) {
                    $kategori = 'TWK';
                }

                // Hitung content hash & stem hash
                $contentHash = self::computeContentHash([
                    'teks_soal' => $teksSoal,
                    'opsi_a' => $opsiA,
                    'opsi_b' => $opsiB,
                    'opsi_c' => $opsiC,
                    'opsi_d' => $opsiD,
                    'opsi_e' => $opsiE,
                ], $kategori);

                $stem = self::normalizeQuestionStem($teksSoal);
                $stemHash = $stem !== '' ? sha1($kategori . '::' . $stem) : null;

                // Cek apakah terdeteksi duplikat
                $isDuplicate = false;
                if (!empty($normalizedRow['hash_soal']) && isset($existingHashes[$normalizedRow['hash_soal']])) {
                    $isDuplicate = true;
                } elseif (isset($existingHashes[$contentHash])) {
                    $isDuplicate = true;
                } elseif ($stemHash !== null && isset($existingStems[$stemHash])) {
                    $isDuplicate = true;
                }

                if ($isDuplicate) {
                    $duplicateCount++;
                    continue;
                }

                // Tandai di memori agar jika dalam file yang sama ada soal duplikat berulang, tetap dicegat
                $existingHashes[$contentHash] = true;
                if (!empty($normalizedRow['hash_soal'])) {
                    $existingHashes[$normalizedRow['hash_soal']] = true;
                }
                if ($stemHash !== null) {
                    $existingStems[$stemHash] = true;
                }

                // Resolusi Kunci Jawaban
                $rawJawaban = strtoupper(trim((string) ($normalizedRow['jawaban_benar'] ?? $normalizedRow['kunci_jawaban'] ?? $normalizedRow['kunci'] ?? 'A')));
                if (!in_array($rawJawaban, ['A', 'B', 'C', 'D', 'E'])) {
                    $rawJawaban = 'A';
                }

                // Resolusi Bobot TKP
                $bobotTkp = null;
                $rawBobot = $normalizedRow['bobot_tkp'] ?? null;
                if (is_string($rawBobot) && trim($rawBobot) !== '') {
                    $decoded = json_decode(trim($rawBobot), true);
                    if (is_array($decoded)) {
                        $rawBobot = $decoded;
                    }
                }
                if (is_array($rawBobot)) {
                    $bobotTkp = [
                        'A' => (int) ($rawBobot['A'] ?? $rawBobot['a'] ?? 0),
                        'B' => (int) ($rawBobot['B'] ?? $rawBobot['b'] ?? 0),
                        'C' => (int) ($rawBobot['C'] ?? $rawBobot['c'] ?? 0),
                        'D' => (int) ($rawBobot['D'] ?? $rawBobot['d'] ?? 0),
                        'E' => (int) ($rawBobot['E'] ?? $rawBobot['e'] ?? 0),
                    ];
                } elseif ($kategori === 'TKP') {
                    // Fallback sebaran bobot 1-5 dengan nilai 5 untuk kunci utama
                    $scores = [4, 3, 2, 1];
                    shuffle($scores);
                    $bobotTkp = [];
                    foreach (['A', 'B', 'C', 'D', 'E'] as $opt) {
                        $bobotTkp[$opt] = ($opt === $rawJawaban) ? 5 : (array_pop($scores) ?? 1);
                    }
                }

                // Resolusi Standar Tahun
                $rowStandar = trim((string) ($normalizedRow['standar_tahun'] ?? ''));
                if ($targetStandar === 'keep') {
                    $finalStandar = in_array($rowStandar, ['2027', 'pra-2027']) ? $rowStandar : '2027';
                } else {
                    $finalStandar = in_array($targetStandar, ['2027', 'pra-2027']) ? $targetStandar : '2027';
                }

                // Resolusi Mode Ujian
                $modeId = $defaultModeId;
                $modeCode = strtolower(trim((string) ($normalizedRow['exam_mode'] ?? $normalizedRow['exam_mode_code'] ?? '')));
                if ($modeCode !== '') {
                    $matchedMode = $examModes->firstWhere('code', $modeCode)
                        ?? $examModes->first(fn ($m) => strtolower($m->name) === $modeCode);
                    if ($matchedMode) {
                        $modeId = $matchedMode->id;
                    }
                }

                // Resolusi Sub Kategori
                $subCatId = null;
                $subCatName = trim((string) ($normalizedRow['sub_category'] ?? $normalizedRow['sub_category_name'] ?? ''));
                if ($subCatName !== '') {
                    $matchedSub = $subCategories->first(function ($s) use ($subCatName, $modeId) {
                        $nameMatch = strcasecmp($s->name, $subCatName) === 0;
                        return $modeId ? ($nameMatch && $s->exam_mode_id == $modeId) : $nameMatch;
                    });
                    if ($matchedSub) {
                        $subCatId = $matchedSub->id;
                    }
                }

                $difficulty = strtolower(trim((string) ($normalizedRow['difficulty'] ?? 'medium')));
                if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
                    $difficulty = 'medium';
                }

                // Simpan ke database
                $soal = new Soal();
                $soal->kategori = $kategori;
                $soal->teks_soal = $teksSoal;
                $soal->opsi_a = $opsiA;
                $soal->opsi_b = $opsiB;
                $soal->opsi_c = $opsiC;
                $soal->opsi_d = $opsiD;
                $soal->opsi_e = $opsiE;
                $soal->jawaban_benar = $rawJawaban;
                $soal->pembahasan = $normalizedRow['pembahasan'] ?? null;
                $soal->sub_topik = $normalizedRow['sub_topik'] ?? null;
                $soal->difficulty = $difficulty;
                $soal->hash_soal = $contentHash;

                if (Schema::hasColumn('soals', 'standar_tahun')) {
                    $soal->standar_tahun = $finalStandar;
                }
                if (Schema::hasColumn('soals', 'exam_mode_id')) {
                    $soal->exam_mode_id = $modeId;
                }
                if (Schema::hasColumn('soals', 'sub_category_id')) {
                    $soal->sub_category_id = $subCatId;
                }
                if (Schema::hasColumn('soals', 'created_by') && $userId) {
                    $soal->created_by = $userId;
                }
                if ($bobotTkp !== null) {
                    $soal->bobot_tkp = $bobotTkp;
                }

                $soal->save();
                $importedCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal mengimpor backup bank soal: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'total' => count($rawRows),
                'imported' => 0,
                'skipped_duplicates' => 0,
                'errors' => 1,
                'error_messages' => ['Terjadi kesalahan internal saat menyimpan ke database: ' . $e->getMessage()],
            ];
        }

        return [
            'total' => count($rawRows),
            'imported' => $importedCount,
            'skipped_duplicates' => $duplicateCount,
            'errors' => $errorCount,
            'error_messages' => array_slice($errorMessages, 0, 5),
        ];
    }

    /**
     * Parsing file CSV menjadi array of associative arrays.
     */
    protected function parseCsvFile(string $filePath): array
    {
        $rows = [];
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return $rows;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return $rows;
        }

        // Hapus UTF-8 BOM jika ada di byte pertama
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = null;
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if ($headers === null) {
                $headers = array_map(function ($h) {
                    $h = trim((string) $h);
                    $h = strtolower($h);
                    $h = str_replace([' ', '-'], '_', $h);
                    return $h;
                }, $data);
                continue;
            }

            if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = $data[$i] ?? '';
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Normalisasi nama field kunci associative array agar fleksibel.
     */
    protected function normalizeRowKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $k => $v) {
            $cleanKey = strtolower(trim((string) $k));
            $cleanKey = str_replace([' ', '-'], '_', $cleanKey);
            $normalized[$cleanKey] = $v;
        }
        return $normalized;
    }
}
