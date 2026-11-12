<?php

namespace App\Services;

use App\Models\QuestionAudit;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuestionQualityService
{
    public function __construct(
        private readonly AISoalService $aiService
    ) {
    }

    public function generateWithQc(array $params, ?User $user = null): array
    {
        $startedAt = now();

        $result = $this->aiService->generateSoal(
            $params['kategori'],
            (int) $params['total'],
            (string) ($params['provider'] ?? 'groq'),
            $params['model'] ?? null,
            $params['pdf_path'] ?? null,
            $params['fokus_topik'] ?? null,
            null,
            $params['exam_mode_id'] ?? null,
            $params['sub_category_id'] ?? null,
            (string) ($params['difficulty'] ?? 'medium'),
            $user?->id
        );

        if ($result !== true) {
            return [
                'ok' => false,
                'message' => (string) $result,
                'summary' => null,
            ];
        }

        $rows = Soal::query()
            ->when(Schema::hasColumn('soals', 'created_by') && $user?->id, fn ($q) => $q->where('created_by', $user->id))
            ->where('created_at', '>=', $startedAt->copy()->subSeconds(5))
            ->when(Schema::hasColumn('soals', 'exam_mode_id') && !empty($params['exam_mode_id']), fn ($q) => $q->where('exam_mode_id', (int) $params['exam_mode_id']))
            ->when(Schema::hasColumn('soals', 'sub_category_id') && !empty($params['sub_category_id']), fn ($q) => $q->where('sub_category_id', (int) $params['sub_category_id']))
            ->when(Schema::hasColumn('soals', 'difficulty') && !empty($params['difficulty']), fn ($q) => $q->where('difficulty', (string) $params['difficulty']))
            ->where('kategori', (string) $params['kategori'])
            ->orderByDesc('id')
            ->take(max(((int) $params['total']) + 8, (int) $params['total']))
            ->get()
            ->sortBy('id')
            ->values();

        $summary = [
            'approved' => 0,
            'revised' => 0,
            'flagged' => 0,
            'rejected' => 0,
            'audited' => 0,
            'question_ids' => [],
        ];

        foreach ($rows as $soal) {
            $audit = $this->runAudit($soal, 'generated', (string) ($params['provider'] ?? 'groq'), $params['model'] ?? null);
            $summary['audited']++;
            $summary['question_ids'][] = (int) $soal->id;
            $qcStatus = (string) ($audit->question?->qc_status ?? ($audit->status ?? 'flagged'));
            if (!isset($summary[$qcStatus])) {
                $summary[$qcStatus] = 0;
            }
            $summary[$qcStatus]++;
        }

        return [
            'ok' => true,
            'message' => 'Generate dengan QC selesai.',
            'summary' => $summary,
        ];
    }

    public function runAudit(Soal $soal, string $source = 'existing_tiu', string $provider = 'groq', ?string $model = null): QuestionAudit
    {
        $aiResult = $this->aiService->auditExistingSoal($soal, $provider, $model);
        $normalized = $this->normalizeAudit($soal, $aiResult, $source);

        return DB::transaction(function () use ($soal, $normalized, $source) {
            $audit = QuestionAudit::create([
                'question_id' => $soal->id,
                'source' => $normalized['source'],
                'audit_type' => 'ai_qc',
                'status' => $normalized['status'],
                'confidence' => $normalized['confidence'],
                'issue_type' => $normalized['issue_type'],
                'old_answer' => $normalized['old_answer'],
                'suggested_answer' => $normalized['suggested_answer'],
                'old_explanation' => $normalized['old_explanation'],
                'suggested_explanation' => $normalized['suggested_explanation'],
                'notes' => $normalized['notes'],
                'payload' => $normalized['payload'],
            ]);

            $qcStatus = $normalized['qc_status'];
            if (!empty($normalized['should_delete_question'])) {
                $audit->status = 'rejected';
                $audit->reviewed_at = now();
                $audit->applied_at = now();
                $audit->save();

                $soal->delete();

                return $audit->fresh('question');
            }

            if ($source === 'generated' && $qcStatus === 'flagged' && $this->canAutoRevise($normalized)) {
                $this->applyAutoRevision($soal, $normalized);
                $qcStatus = 'revised';
                $audit->status = 'revised';
                $audit->reviewed_at = now();
                $audit->applied_at = now();
                $audit->save();
            }

            if (Schema::hasColumn('soals', 'qc_status')) {
                $soal->forceFill([
                    'qc_status' => $qcStatus,
                    'qc_checked_at' => now(),
                    'qc_confidence' => $normalized['confidence'],
                ])->save();
            }

            return $audit->fresh('question');
        });
    }

    public function applySuggestedFix(QuestionAudit $audit, bool $applyAnswer = true, bool $applyExplanation = true): Soal
    {
        $soal = $audit->question;
        abort_unless($soal, 404, 'Soal tidak ditemukan.');

        if ($applyAnswer && !empty($audit->suggested_answer)) {
            $soal->jawaban_benar = strtoupper((string) $audit->suggested_answer);
        }
        if ($applyExplanation && !empty($audit->suggested_explanation)) {
            $soal->pembahasan = (string) $audit->suggested_explanation;
        }

        if (Schema::hasColumn('soals', 'qc_status')) {
            $soal->qc_status = 'reviewed';
            $soal->qc_checked_at = now();
            $soal->qc_confidence = $audit->confidence;
            $soal->save();
        }

        $audit->status = 'reviewed';
        $audit->reviewed_at = now();
        $audit->applied_at = now();
        $audit->save();

        return $soal;
    }

    public function markReviewed(QuestionAudit $audit): void
    {
        $audit->status = 'reviewed';
        $audit->reviewed_at = now();
        $audit->save();

        if ($audit->question) {
            if (Schema::hasColumn('soals', 'qc_status')) {
                $audit->question->forceFill([
                    'qc_status' => 'reviewed',
                    'qc_checked_at' => now(),
                    'qc_confidence' => $audit->confidence,
                ])->save();
            }
        }
    }

    private function normalizeAudit(Soal $soal, array $result, string $source): array
    {
        $dbStatus = (string) ($result['status'] ?? 'invalid');
        $needsRevision = (bool) ($result['needs_revision'] ?? false);
        $pembahasanOk = (bool) ($result['pembahasan_ok'] ?? false);
        $aiKey = $this->normalizeAnswer($result['ai_key'] ?? null);
        $dbKey = $this->normalizeAnswer($result['db_key'] ?? $soal->jawaban_benar);
        $aiPembahasan = trim((string) ($result['ai_pembahasan'] ?? ''));
        $notes = trim((string) ($result['notes'] ?? ''));
        $proposed = is_array($result['proposed'] ?? null) ? $result['proposed'] : [];
        $deleteBecauseNearest = $this->containsNearestOptionLanguage([
            (string) ($soal->pembahasan ?? ''),
            $aiPembahasan,
            trim((string) ($result['revision_reason'] ?? '')),
            trim((string) ($proposed['pembahasan'] ?? '')),
            $notes,
        ]);

        $status = 'suspicious';
        $issueType = 'manual_review';
        $confidence = 72.00;
        $qcStatus = 'flagged';

        if ($deleteBecauseNearest) {
            $status = 'rejected';
            $issueType = 'nearest_option_invalid';
            $confidence = 99.00;
            $qcStatus = 'rejected';
            $notes = 'Soal dihapus otomatis karena pembahasan memakai pola "jawaban yang paling dekat / opsi terdekat", sehingga tidak layak disimpan.';
        } elseif ($dbStatus === 'match' && $pembahasanOk) {
            $status = 'valid';
            $issueType = 'no_issue';
            $confidence = $source === 'generated' ? 95.00 : 91.00;
            $qcStatus = $source === 'generated' ? 'approved' : 'valid';
        } elseif ($needsRevision) {
            $status = 'wrong';
            $issueType = !empty($proposed) ? 'requires_full_revision' : 'ambiguous_or_invalid_options';
            $confidence = 93.00;
            $qcStatus = !empty($proposed) ? 'flagged' : 'rejected';
        } elseif ($dbStatus === 'mismatch' && $aiKey !== '') {
            $status = 'wrong';
            $issueType = 'answer_mismatch';
            $confidence = 90.00;
            $qcStatus = 'flagged';
        } elseif (!$pembahasanOk) {
            $status = 'suspicious';
            $issueType = 'weak_explanation';
            $confidence = 78.00;
            $qcStatus = 'flagged';
        } elseif ($dbStatus === 'invalid') {
            $status = 'suspicious';
            $issueType = 'audit_error';
            $confidence = 44.00;
            $qcStatus = 'flagged';
        }

        return [
            'source' => $source,
            'status' => $status,
            'confidence' => $confidence,
            'issue_type' => $issueType,
            'old_answer' => $dbKey ?: null,
            'suggested_answer' => $aiKey ?: $this->normalizeAnswer($proposed['jawaban_benar'] ?? null),
            'old_explanation' => (string) ($soal->pembahasan ?? ''),
            'suggested_explanation' => $aiPembahasan !== '' ? $aiPembahasan : trim((string) ($proposed['pembahasan'] ?? '')),
            'notes' => $notes !== '' ? $notes : trim((string) ($result['revision_reason'] ?? 'Perlu review manual.')),
            'payload' => [
                'ai_result' => $result,
                'delete_reason' => $deleteBecauseNearest ? 'nearest_option_invalid' : null,
                'question_snapshot' => [
                    'id' => $soal->id,
                    'kategori' => $soal->kategori,
                    'sub_topik' => $soal->sub_topik,
                    'teks_soal' => $soal->teks_soal,
                    'opsi_a' => $soal->opsi_a,
                    'opsi_b' => $soal->opsi_b,
                    'opsi_c' => $soal->opsi_c,
                    'opsi_d' => $soal->opsi_d,
                    'opsi_e' => $soal->opsi_e,
                    'jawaban_benar' => $soal->jawaban_benar,
                ],
            ],
            'qc_status' => $qcStatus,
            'should_delete_question' => $deleteBecauseNearest,
        ];
    }

    private function canAutoRevise(array $normalized): bool
    {
        return in_array((string) $normalized['issue_type'], ['answer_mismatch', 'weak_explanation'], true)
            && (
                !empty($normalized['suggested_answer'])
                || !empty($normalized['suggested_explanation'])
            );
    }

    private function applyAutoRevision(Soal $soal, array $normalized): void
    {
        if (!empty($normalized['suggested_answer'])) {
            $soal->jawaban_benar = (string) $normalized['suggested_answer'];
        }
        if (!empty($normalized['suggested_explanation'])) {
            $soal->pembahasan = (string) $normalized['suggested_explanation'];
        }
        $soal->save();
    }

    private function normalizeAnswer(mixed $value): string
    {
        $key = strtoupper(trim((string) $value));
        return preg_match('/^[A-E]$/', $key) ? $key : '';
    }

    private function containsNearestOptionLanguage(array $texts): bool
    {
        $haystack = implode("\n", array_filter(array_map(
            fn ($text) => trim((string) $text),
            $texts
        )));

        if ($haystack === '') {
            return false;
        }

        return (bool) preg_match('/paling\s+dekat|opsi\s+terdekat|pilihan\s+terdekat|yang\s+paling\s+dekat\s+dengan\s+opsi/i', $haystack);
    }
}
