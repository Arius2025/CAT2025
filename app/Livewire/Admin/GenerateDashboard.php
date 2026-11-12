<?php

namespace App\Livewire\Admin;

use App\Models\ExamMode;
use App\Models\Soal;
use App\Support\ExamStructureSupport;
use App\Support\QuestionStatsSupport;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class GenerateDashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $preferredExamModeIds = $user && method_exists($user, 'preferredExamModeIds') ? $user->preferredExamModeIds() : [];
        $preferredFormasiNames = $user && method_exists($user, 'preferredFormasiNames') ? $user->preferredFormasiNames() : [];
        [
            'stats' => $stats,
            'modeQuestionStats' => $modeQuestionStats,
            'auditStatusStats' => $auditStatusStats,
            'uncategorizedCount' => $uncategorizedCount,
        ] = QuestionStatsSupport::dashboardSnapshot();
        $examModes = ExamStructureSupport::modes();
        if (!empty($preferredExamModeIds)) {
            $examModes = collect($examModes)
                ->filter(fn ($mode) => in_array((int) ($mode->id ?? 0), $preferredExamModeIds, true))
                ->values();
        }
        $defaultExamMode = !empty($preferredExamModeIds)
            ? (collect($examModes)->firstWhere('id', $preferredExamModeIds[0]) ?: collect($examModes)->first() ?: ExamStructureSupport::defaultMode())
            : ExamStructureSupport::defaultMode();
        $subCategories = $defaultExamMode
            ? ExamStructureSupport::subCategories((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''), 'formasi')
            : collect();
        if (($defaultExamMode->code ?? '') !== 'skd' && !empty($preferredFormasiNames)) {
            $subCategories = collect($subCategories)
                ->filter(fn ($sub) => in_array(trim((string) ($sub->name ?? '')), $preferredFormasiNames, true))
                ->values();
        }
        $modePackages = $defaultExamMode
            ? ExamStructureSupport::packages((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''))
            : collect();
        $categoryOptions = $defaultExamMode
            ? ExamStructureSupport::categoryOptions((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''))
            : collect();
        $focusTopicPresets = ExamStructureSupport::focusTopicPresets();
        $dynamicExamReady = ExamStructureSupport::schemaReady();

        return view('livewire.admin.generate-dashboard', compact('stats', 'modeQuestionStats', 'auditStatusStats', 'uncategorizedCount', 'examModes', 'defaultExamMode', 'subCategories', 'modePackages', 'categoryOptions', 'focusTopicPresets', 'dynamicExamReady'));
    }
}
