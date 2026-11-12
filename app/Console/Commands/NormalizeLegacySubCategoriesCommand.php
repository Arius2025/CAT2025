<?php

namespace App\Console\Commands;

use App\Models\ExamMode;
use App\Models\SubCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class NormalizeLegacySubCategoriesCommand extends Command
{
    protected $signature = 'subcategories:normalize-legacy {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Normalisasi nama sub kategori lama ke 3 formasi final tanpa menghapus data';

    public function handle(): int
    {
        if (!Schema::hasTable('sub_categories') || !Schema::hasTable('exam_modes')) {
            $this->error('Tabel exam_modes atau sub_categories belum tersedia.');
            return self::FAILURE;
        }

        $modeMap = ExamMode::query()->pluck('code', 'id')->map(fn ($v) => strtolower((string) $v))->all();
        $renameMap = [
            'skb' => [
                'SKB CPNS (Asisten Apoteker - D3)' => 'D3 Farmasi',
                'SKB CPNS (Apoteker - S1)' => 'D3 Farmasi',
                'SKB CPNS (IT - Terampil D3)' => 'D3 Manajemen Informatika',
                'SKB CPNS (IT - Ahli S1)' => 'S1 Teknik Informatika',
            ],
            'pppk' => [
                'PPPK Teknis (Asisten Apoteker - D3)' => 'D3 Farmasi',
                'PPPK Teknis (Apoteker - S1)' => 'D3 Farmasi',
                'PPPK Teknis (IT - Terampil D3)' => 'D3 Manajemen Informatika',
                'PPPK Teknis (IT - Ahli S1)' => 'S1 Teknik Informatika',
            ],
        ];

        $rows = SubCategory::query()->orderBy('exam_mode_id')->orderBy('name')->get();
        $changes = [];

        foreach ($rows as $row) {
            $modeCode = $modeMap[$row->exam_mode_id] ?? null;
            if (!$modeCode) {
                continue;
            }
            $newName = $renameMap[$modeCode][$row->name] ?? null;
            if (!$newName || $newName === $row->name) {
                continue;
            }
            $changes[] = [$row, $newName, $modeCode];
        }

        if (empty($changes)) {
            $this->info('Tidak ada sub kategori lama yang perlu dinormalisasi.');
            return self::SUCCESS;
        }

        $this->table(['ID', 'Mode', 'Lama', 'Baru'], array_map(function ($item) {
            [$row, $newName, $modeCode] = $item;
            return [$row->id, strtoupper($modeCode), $row->name, $newName];
        }, $changes));

        if ($this->option('dry-run')) {
            $this->warn('Dry run: tidak ada data yang diubah.');
            return self::SUCCESS;
        }

        foreach ($changes as [$row, $newName]) {
            $row->name = $newName;
            $row->save();
        }

        $this->info('Normalisasi sub kategori lama selesai tanpa menghapus data.');
        return self::SUCCESS;
    }
}


