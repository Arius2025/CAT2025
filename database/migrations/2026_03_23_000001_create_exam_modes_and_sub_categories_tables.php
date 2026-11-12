<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_modes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('color')->nullable();
            $table->boolean('has_figural')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('exam_mode_id')->constrained('exam_modes')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('soals', function (Blueprint $table) {
            $table->foreignId('exam_mode_id')->nullable()->after('sub_topik')->constrained('exam_modes')->nullOnDelete();
            $table->foreignId('sub_category_id')->nullable()->after('exam_mode_id')->constrained('sub_categories')->nullOnDelete();
        });

        $now = now();

        DB::table('exam_modes')->insert([
            [
                'name' => 'SKD',
                'code' => 'skd',
                'color' => '#0ea5e9',
                'has_figural' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'SKB',
                'code' => 'skb',
                'color' => '#22c55e',
                'has_figural' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'PPPK',
                'code' => 'pppk',
                'color' => '#f59e0b',
                'has_figural' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $modes = DB::table('exam_modes')->pluck('id', 'code');
        $subCategories = [
            ['name' => 'TWK', 'exam_mode_id' => $modes['skd'] ?? null],
            ['name' => 'TIU', 'exam_mode_id' => $modes['skd'] ?? null],
            ['name' => 'TKP', 'exam_mode_id' => $modes['skd'] ?? null],
            ['name' => 'D3 Farmasi', 'exam_mode_id' => $modes['skb'] ?? null],
            ['name' => 'D3 Manajemen Informatika', 'exam_mode_id' => $modes['skb'] ?? null],
            ['name' => 'S1 Teknik Informatika', 'exam_mode_id' => $modes['skb'] ?? null],
            ['name' => 'PPPK Umum', 'exam_mode_id' => $modes['pppk'] ?? null],
        ];

        DB::table('sub_categories')->insert(array_values(array_filter(array_map(function ($row) use ($now) {
            if (empty($row['exam_mode_id'])) {
                return null;
            }

            return [
                'name' => $row['name'],
                'exam_mode_id' => $row['exam_mode_id'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $subCategories))));
    }

    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            if (Schema::hasColumn('soals', 'sub_category_id')) {
                $table->dropConstrainedForeignId('sub_category_id');
            }
            if (Schema::hasColumn('soals', 'exam_mode_id')) {
                $table->dropConstrainedForeignId('exam_mode_id');
            }
        });

        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('exam_modes');
    }
};
