<?php

namespace App\Support;

class QuestionDisplaySupport
{
    /**
     * Format question text into rich, legible, structured HTML (Deret Angka, Soal Cerita, etc.)
     */
    public static function format(?string $rawText, ?string $kategori = null, ?string $subTopik = null): string
    {
        if (empty($rawText)) {
            return '';
        }

        $text = trim($rawText);

        // 1. Strip accidental embedded option rows (e.g., "\nA. 16\nB. 17\nC. 18...")
        $text = preg_replace('/\n\s*[A-E]\.\s*.*(?:\n\s*[A-E]\.\s*.*)*$/s', '', $text);
        $text = trim($text);

        $kategori = strtoupper(trim((string) $kategori));
        $subTopik = trim((string) $subTopik);
        $isDeretTopic = (bool) preg_match('/deret|barisan|sequence|pola\s*angka/i', $subTopik);

        // 2. Try TABEL DATA Visualizer (Markdown / Pipe-separated)
        $tableResult = self::tryParseTable($text, $kategori, $subTopik);
        if ($tableResult !== null) {
            return $tableResult;
        }

        // 3. Try DERET ANGKA / HURUF Visualizer
        $deretResult = self::tryParseDeret($text, $isDeretTopic, $kategori);
        if ($deretResult !== null) {
            return $deretResult;
        }

        // 4. Try PERBANDINGAN KUANTITATIF (X vs Y)
        $compResult = self::tryParseComparison($text, $kategori, $subTopik);
        if ($compResult !== null) {
            return $compResult;
        }

        // 5. Try ANALOGI KATA (A : B = C : ?)
        $analogiResult = self::tryParseAnalogi($text, $kategori, $subTopik);
        if ($analogiResult !== null) {
            return $analogiResult;
        }

        // 6. Try SILOGISME (Premis 1 / Premis 2 / Kesimpulan)
        $silogismeResult = self::tryParseSilogisme($text, $kategori, $subTopik);
        if ($silogismeResult !== null) {
            return $silogismeResult;
        }

        // 7. Try SOAL CERITA / STUDI KASUS (Narrative + Core Question Stem)
        $ceritaResult = self::tryParseCerita($text, $kategori, $subTopik);
        if ($ceritaResult !== null) {
            return $ceritaResult;
        }

        // 8. Default Plain Text with typography enhancements
        return self::renderStandardText($text);
    }

    /**
     * Parse and render Deret Angka/Huruf
     */
    protected static function tryParseDeret(string $text, bool $isDeretTopic, string $kategori): ?string
    {
        // Token pattern: numbers (with optional decimals/fractions/signs), single uppercase letters, ?, ..., _, X, Y
        $tokenPat = '(?:[-+]?\d+(?:[.,\/]\d+)?|\b[A-Z]\b|\?{1,3}|\.{2,4}|_{1,4})';
        $seqPat = '/(' . $tokenPat . '(?:\s*,\s*' . $tokenPat . '){2,}(?:\s*,\s*' . $tokenPat . ')*)/i';

        if (!preg_match($seqPat, $text, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $rawSeq = $matches[1][0];
        $matchPos = $matches[1][1];
        $tokens = array_values(array_filter(array_map('trim', explode(',', $rawSeq))));

        // We require at least 3 distinct tokens
        if (count($tokens) < 3) {
            return null;
        }

        // Text before & after
        $before = trim(substr($text, 0, $matchPos));
        $before = preg_replace('/[:,\s]+$/', '', $before);

        $after = trim(substr($text, $matchPos + strlen($rawSeq)));
        $after = preg_replace('/^[.,\s]+/', '', $after);

        $html = '<div class="soal-deret-container my-3">';
        
        $html .= '<div class="soal-deret-header">';
        $html .= '<span class="soal-deret-badge"><i class="fas fa-arrow-trend-up me-1"></i> Pola Barisan &amp; Deret</span>';
        $html .= '</div>';

        if (!empty($before)) {
            $html .= '<div class="soal-deret-intro mb-2">' . htmlspecialchars($before) . '</div>';
        }

        // Sequence Track
        $html .= '<div class="soal-deret-track-wrapper">';
        $html .= '<div class="soal-deret-track">';

        foreach ($tokens as $idx => $t) {
            $isTarget = in_array($t, ['?', '??', '???', '...', '....', '_', '__', '___', 'X', 'Y'], true) || ($idx === count($tokens) - 1 && $t === '?');
            $idxLabel = 'U<sub>' . ($idx + 1) . '</sub>';
            if ($isTarget) {
                $idxLabel = '<span class="text-warning fw-bold">Target</span>';
            }

            $html .= '<div class="soal-deret-token ' . ($isTarget ? 'is-target' : '') . '">';
            $html .= '<div class="soal-deret-val">' . htmlspecialchars($t) . '</div>';
            $html .= '<div class="soal-deret-idx">' . $idxLabel . '</div>';
            $html .= '</div>';

            if ($idx < count($tokens) - 1) {
                $html .= '<div class="soal-deret-connector"><i class="fas fa-arrow-right"></i></div>';
            }
        }

        $html .= '</div>';
        $html .= '</div>';

        if (!empty($after)) {
            $html .= '<div class="soal-pertanyaan-box mt-3">';
            $html .= '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Inti Pertanyaan:</div>';
            $html .= '<div class="soal-pertanyaan-text">' . htmlspecialchars($after) . '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Parse Perbandingan Kuantitatif (X vs Y)
     */
    protected static function tryParseComparison(string $text, string $kategori, string $subTopik): ?string
    {
        // Detect patterns like "x = ... dan y = ..." or "P = ... dan Q = ..."
        if (!preg_match('/\b([xXpP])\s*=\s*([^,\n;]+?)\s*(?:,|dan|serta|sedangkan)\s*([yYqQ])\s*=\s*([^.\n;]+?)(?:\.|$|\n)/i', $text, $m)) {
            return null;
        }

        $var1 = strtoupper($m[1]);
        $val1 = trim($m[2]);
        $var2 = strtoupper($m[3]);
        $val2 = trim($m[4]);

        $before = trim(substr($text, 0, strpos($text, $m[0])));
        $after = trim(substr($text, strpos($text, $m[0]) + strlen($m[0])));

        $html = '<div class="soal-comparison-container my-3">';
        $html .= '<div class="soal-comparison-badge mb-2"><i class="fas fa-scale-balanced me-1"></i> Perbandingan Kuantitatif</div>';

        if (!empty($before)) {
            $html .= '<div class="mb-2 text-secondary" style="font-size: 0.95rem;">' . htmlspecialchars($before) . '</div>';
        }

        $html .= '<div class="soal-comparison-grid">';
        $html .= '<div class="soal-comp-card">';
        $html .= '<div class="soal-comp-label">Nilai ' . htmlspecialchars($var1) . '</div>';
        $html .= '<div class="soal-comp-val">' . htmlspecialchars($val1) . '</div>';
        $html .= '</div>';

        $html .= '<div class="soal-comp-vs">VS</div>';

        $html .= '<div class="soal-comp-card">';
        $html .= '<div class="soal-comp-label">Nilai ' . htmlspecialchars($var2) . '</div>';
        $html .= '<div class="soal-comp-val">' . htmlspecialchars($val2) . '</div>';
        $html .= '</div>';
        $html .= '</div>';

        if (!empty($after)) {
            $html .= '<div class="soal-pertanyaan-box mt-3">';
            $html .= '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Hubungan yang Ditanyakan:</div>';
            $html .= '<div class="soal-pertanyaan-text">' . htmlspecialchars($after) . '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Parse Soal Cerita / Studi Kasus
     */
    protected static function tryParseCerita(string $text, string $kategori, string $subTopik): ?string
    {
        // Must be a case study or narrative (length >= 80)
        if (mb_strlen($text) < 80 && !in_array($kategori, ['TKP', 'TWK'], true)) {
            return null;
        }

        $stemPatterns = [
            '/\b(?:Berdasarkan|Menurut|Dari|Sesuai)\s+(?:kasus|situasi|uraian|peristiwa|kondisi|wacana|bacaan|teks|pasal|ketentuan|deskripsi|simulasi|skenario)\s+[^.?!]+[,.]\s*(?:tindakan|sikap|langkah|keputusan|peran|upaya|manakah|apa|bagaimana|hal\s+apa)[^.?!]*[?:]?/i',
            '/\b(?:Sikap|Tindakan|Langkah|Keputusan|Upaya|Respon|Perilaku)\s+(?:yang\s+)?(?:paling\s+)?(?:tepat|bijak|efektif|seharusnya|dapat|mencerminkan|harus|diambil|Anda\s+lakukan)[^.?!]*[?:]?/i',
            '/\b(?:Berikut\s+ini|Di\s+bawah\s+ini)\s+.*?[,:]\s*(?:KECUALI)?.*$/i',
            '/\b(?:Berapakah|Berapa|Manakah|Apakah|Bagaimana|Mengapa|Siapakah|Apa\s+yang)\s+[^.?!]*\?$/i',
            '/([^.?!;\n]+\?\s*$)/',
        ];

        $splitPos = -1;
        foreach ($stemPatterns as $pat) {
            if (preg_match_all($pat, $text, $matches, PREG_OFFSET_CAPTURE)) {
                $lastMatch = end($matches[0]);
                if ($lastMatch[1] >= 45) {
                    $splitPos = $lastMatch[1];
                    break;
                }
            }
        }

        if ($splitPos === -1 && mb_strlen($text) < 130) {
            return null;
        }

        if ($splitPos !== -1) {
            $scenario = trim(substr($text, 0, $splitPos));
            $stem = trim(substr($text, $splitPos));
        } else {
            $scenario = $text;
            $stem = '';
        }

        $badgeLabel = 'Studi Kasus &amp; Skenario';
        $badgeIcon = 'fa-book-open';
        if ($kategori === 'TKP') {
            $badgeLabel = 'Situasi Kerja &amp; Dilema Etika';
            $badgeIcon = 'fa-user-tie';
        } elseif ($kategori === 'TWK') {
            $badgeLabel = 'Studi Kasus Kebangsaan';
            $badgeIcon = 'fa-landmark';
        } elseif ($kategori === 'TIU') {
            $badgeLabel = 'Konteks Soal Cerita';
            $badgeIcon = 'fa-file-lines';
        }

        $html = '<div class="soal-cerita-container my-2">';
        
        // Scenario Box
        $html .= '<div class="soal-cerita-card">';
        $html .= '<div class="soal-cerita-header">';
        $html .= '<span class="soal-cerita-badge"><i class="fas ' . $badgeIcon . ' me-1"></i> ' . $badgeLabel . '</span>';
        $html .= '</div>';
        $html .= '<div class="soal-cerita-body">' . nl2br(htmlspecialchars($scenario)) . '</div>';
        $html .= '</div>';

        // Question Callout
        if (!empty($stem)) {
            $html .= '<div class="soal-pertanyaan-box mt-3">';
            $html .= '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Inti Pertanyaan:</div>';
            $html .= '<div class="soal-pertanyaan-text">' . nl2br(htmlspecialchars($stem)) . '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Parse and render Data Table (Markdown or pipe-separated)
     */
    protected static function tryParseTable(string $text, string $kategori, string $subTopik): ?string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        if (count($lines) < 2) {
            return null;
        }

        $tableStart = -1;
        $tableEnd = -1;
        $inTable = false;

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            // A line is considered a table row if it contains at least 2 pipes, or starts and ends with pipe
            $isTableRow = (substr_count($line, '|') >= 2) || (str_starts_with($line, '|') && str_ends_with($line, '|'));

            if ($isTableRow) {
                if (!$inTable) {
                    $inTable = true;
                    $tableStart = $i;
                }
                $tableEnd = $i;
            } else {
                if ($inTable) {
                    // Table ended
                    break;
                }
            }
        }

        if ($tableStart === -1 || ($tableEnd - $tableStart + 1) < 2) {
            return null;
        }

        $tableRawLines = array_slice($lines, $tableStart, $tableEnd - $tableStart + 1);
        $beforeLines = array_slice($lines, 0, $tableStart);
        $afterLines = array_slice($lines, $tableEnd + 1);

        $before = trim(implode("\n", $beforeLines));
        $after = trim(implode("\n", $afterLines));

        // Process table rows
        $parsedRows = [];
        foreach ($tableRawLines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') continue;

            // Check if it's a markdown separator line like |---|:---:|---|
            if (preg_match('/^\|?[\s\-:|]+\|?$/', $line) && str_contains($line, '-')) {
                continue;
            }

            // Split by pipe
            $trimmed = trim($line, " \t|");
            $cells = array_map('trim', explode('|', $trimmed));
            if (!empty($cells)) {
                $parsedRows[] = $cells;
            }
        }

        if (count($parsedRows) < 2) {
            return null;
        }

        $headers = array_shift($parsedRows);
        $dataRows = $parsedRows;

        $html = '<div class="soal-tabel-container my-3">';
        $html .= '<div class="soal-tabel-badge"><i class="fas fa-table-list me-1"></i> Data Tabel &amp; Analisis Informasi</div>';

        if (!empty($before)) {
            $html .= '<div class="mb-2 text-secondary" style="font-size: 0.95rem;">' . nl2br(htmlspecialchars($before)) . '</div>';
        }

        $html .= '<div class="soal-tabel-wrapper">';
        $html .= '<table class="soal-data-table">';
        $html .= '<thead><tr>';
        foreach ($headers as $th) {
            $html .= '<th>' . htmlspecialchars($th) . '</th>';
        }
        $html .= '</tr></thead>';

        $html .= '<tbody>';
        foreach ($dataRows as $row) {
            $html .= '<tr>';
            for ($c = 0; $c < count($headers); $c++) {
                $val = $row[$c] ?? '';
                $html .= '<td>' . htmlspecialchars($val) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        if (!empty($after)) {
            $html .= '<div class="soal-pertanyaan-box mt-3">';
            $html .= '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Inti Pertanyaan:</div>';
            $html .= '<div class="soal-pertanyaan-text">' . nl2br(htmlspecialchars($after)) . '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Parse Analogi Kata (A : B = C : ?)
     */
    protected static function tryParseAnalogi(string $text, string $kategori, string $subTopik): ?string
    {
        $pat = '/(?:^|\n|\b)([^:\n\r=]{1,40})\s*:\s*([^:\n\r=]{1,40})\s*(?:=|==|::|\bsepadan dengan\b)\s*([^:\n\r=]{1,40})\s*:\s*([^:\n\r]{1,40})(?:\.|\n|$)/iu';

        if (!preg_match($pat, $text, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $fullMatch = $m[0][0];
        $matchPos = $m[0][1];

        $term1 = trim($m[1][0]);
        $term2 = trim($m[2][0]);
        $term3 = trim($m[3][0]);
        $term4 = trim($m[4][0]);

        if (mb_strlen($term1) > 35 || mb_strlen($term2) > 35 || mb_strlen($term3) > 35 || mb_strlen($term4) > 35) {
            return null;
        }

        $before = trim(substr($text, 0, $matchPos));
        $after = trim(substr($text, $matchPos + strlen($fullMatch)));

        $isUnknown = function ($term) {
            return in_array(trim($term), ['?', '??', '???', '...', '....', '…', '_', '__', '___'], true)
                || preg_match('/^(\?+|\.{2,}|_{2,})$/', trim($term));
        };

        $html = '<div class="soal-analogi-container my-3">';
        $html .= '<div class="soal-analogi-badge"><i class="fas fa-arrows-split-up-and-left me-1"></i> Analogi Pasangan Kata</div>';

        if (!empty($before)) {
            $html .= '<div class="mb-2 text-secondary" style="font-size: 0.95rem;">' . nl2br(htmlspecialchars($before)) . '</div>';
        }

        $html .= '<div class="soal-analogi-track">';
        
        $html .= '<div class="soal-analogi-pair">';
        $html .= '<div class="soal-analogi-term ' . ($isUnknown($term1) ? 'is-unknown' : '') . '">' . htmlspecialchars($term1) . '</div>';
        $html .= '<span class="soal-analogi-sep">:</span>';
        $html .= '<div class="soal-analogi-term ' . ($isUnknown($term2) ? 'is-unknown' : '') . '">' . htmlspecialchars($term2) . '</div>';
        $html .= '</div>';

        $html .= '<span class="soal-analogi-eq">=</span>';

        $html .= '<div class="soal-analogi-pair">';
        $html .= '<div class="soal-analogi-term ' . ($isUnknown($term3) ? 'is-unknown' : '') . '">' . htmlspecialchars($term3) . '</div>';
        $html .= '<span class="soal-analogi-sep">:</span>';
        $html .= '<div class="soal-analogi-term ' . ($isUnknown($term4) ? 'is-unknown' : '') . '">' . htmlspecialchars($term4) . '</div>';
        $html .= '</div>';

        $html .= '</div>';

        if (!empty($after)) {
            $html .= '<div class="soal-pertanyaan-box mt-3">';
            $html .= '<div class="soal-pertanyaan-kicker"><i class="fas fa-circle-question me-1"></i> Hubungan Analogi:</div>';
            $html .= '<div class="soal-pertanyaan-text">' . nl2br(htmlspecialchars($after)) . '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Parse Silogisme (Premis 1 / Premis 2 / Kesimpulan)
     */
    protected static function tryParseSilogisme(string $text, string $kategori, string $subTopik): ?string
    {
        if (!preg_match('/\bPremis\s*(?:1|Mayor|pertama)\b/i', $text) && !preg_match('/\bPremis\s*2\b/i', $text)) {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $premises = [];
        $introLines = [];
        $conclusionLines = [];
        $collectingPremises = false;
        $donePremises = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            if (preg_match('/^(?:[-*•]\s*)?Premis\s*([0-9]+|[A-Za-z]+)\s*[:.-]\s*(.+)$/i', $trimmed, $m)) {
                $collectingPremises = true;
                $label = 'P' . (is_numeric($m[1]) ? $m[1] : substr(strtoupper($m[1]), 0, 1));
                $premises[] = [
                    'label' => $label,
                    'text' => trim($m[2])
                ];
            } elseif ($collectingPremises) {
                if (preg_match('/^(?:Kesimpulan|Maka|Pertanyaan|Jadi)[^:]*[:.-]?\s*(.*)$/i', $trimmed, $cm)) {
                    $donePremises = true;
                    $conclusionLines[] = $trimmed;
                } elseif (!$donePremises) {
                    $premises[] = [
                        'label' => 'P' . (count($premises) + 1),
                        'text' => $trimmed
                    ];
                } else {
                    $conclusionLines[] = $trimmed;
                }
            } else {
                $introLines[] = $trimmed;
            }
        }

        if (count($premises) < 2) {
            return null;
        }

        $intro = trim(implode("\n", $introLines));
        $conclusion = trim(implode("\n", $conclusionLines));

        $html = '<div class="soal-silogisme-container my-3">';
        $html .= '<div class="soal-silogisme-badge"><i class="fas fa-brain me-1"></i> Silogisme &amp; Logika Formal</div>';

        if (!empty($intro)) {
            $html .= '<div class="mb-2 text-secondary" style="font-size: 0.95rem;">' . nl2br(htmlspecialchars($intro)) . '</div>';
        }

        $html .= '<div class="soal-premis-list">';
        foreach ($premises as $p) {
            $html .= '<div class="soal-premis-card">';
            $html .= '<div class="soal-premis-num">' . htmlspecialchars($p['label']) . '</div>';
            $html .= '<div class="soal-premis-text">' . htmlspecialchars($p['text']) . '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';

        if (!empty($conclusion)) {
            $html .= '<div class="soal-silogisme-conclusion">';
            $html .= '<div class="soal-silogisme-conclusion-icon"><i class="fas fa-circle-question"></i></div>';
            $html .= '<div>';
            $html .= '<div class="soal-comp-label" style="margin-bottom: 2px;">Tarik Kesimpulan Logis:</div>';
            $html .= '<div class="soal-premis-text fw-bold">' . nl2br(htmlspecialchars($conclusion)) . '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Standard text rendering with clean paragraphing
     */
    protected static function renderStandardText(string $text): string
    {
        return '<div class="soal-plain-content">' . nl2br(htmlspecialchars($text)) . '</div>';
    }
}
