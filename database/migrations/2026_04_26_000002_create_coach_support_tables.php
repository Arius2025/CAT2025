<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recommendation_logs')) {
            Schema::create('recommendation_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('generated_at')->index();
                $table->string('type', 30)->index();
                $table->text('recommendation_text');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('expert_rules')) {
            Schema::create('expert_rules', function (Blueprint $table) {
                $table->id();
                $table->string('code', 80)->unique();
                $table->json('condition_json');
                $table->text('result_text');
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('priority')->default(100)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('study_plans')) {
            Schema::create('study_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('date')->index();
                $table->text('task_text');
                $table->string('status', 30)->default('pending')->index();
                $table->timestamps();

                $table->index(['user_id', 'date']);
            });
        }

        if (Schema::hasTable('expert_rules')) {
            $rules = [
                [
                    'code' => 'tiu_numerik_low_accuracy',
                    'condition_json' => json_encode([
                        'scope' => 'topic',
                        'match' => ['keywords' => ['numerik', 'aritmetika', 'persen', 'rasio', 'deret']],
                        'conditions' => [
                            ['field' => 'accuracy', 'operator' => '<', 'value' => 60],
                            ['field' => 'attempts', 'operator' => '>=', 'value' => 20],
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                    'result_text' => 'Fokus operasi cepat, persen, rasio, dan deret selama 3 hari. Akurasi {topic_label} Anda masih {accuracy}% dari {attempts} soal.',
                    'priority' => 10,
                ],
                [
                    'code' => 'tkp_service_pattern',
                    'condition_json' => json_encode([
                        'scope' => 'topic',
                        'match' => ['keywords' => ['tkp', 'pelayanan', 'integritas', 'profesionalisme']],
                        'conditions' => [
                            ['field' => 'accuracy', 'operator' => '<', 'value' => 70],
                            ['field' => 'attempts', 'operator' => '>=', 'value' => 10],
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                    'result_text' => 'Pelajari pola jawaban pelayanan publik, integritas, dan profesionalisme. Topik {topic_label} masih di {accuracy}%.',
                    'priority' => 20,
                ],
                [
                    'code' => 'skb_farmasi_foundation',
                    'condition_json' => json_encode([
                        'scope' => 'topic',
                        'match' => ['keywords' => ['farmasi', 'farmakologi', 'obat']],
                        'conditions' => [
                            ['field' => 'accuracy', 'operator' => '<', 'value' => 55],
                            ['field' => 'attempts', 'operator' => '>=', 'value' => 10],
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                    'result_text' => 'Ulangi farmakologi dasar dan klasifikasi obat. {topic_label} masih menjadi titik lemah dengan akurasi {accuracy}%.',
                    'priority' => 30,
                ],
                [
                    'code' => 'overall_declining_trend',
                    'condition_json' => json_encode([
                        'scope' => 'global',
                        'conditions' => [
                            ['field' => 'trend_delta', 'operator' => '<=', 'value' => -5],
                            ['field' => 'recent_sessions', 'operator' => '>=', 'value' => 6],
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                    'result_text' => 'Tren skor sedang menurun. Kurangi tryout penuh selama beberapa sesi dan fokus review kesalahan yang paling sering muncul.',
                    'priority' => 40,
                ],
                [
                    'code' => 'inactive_user_recovery',
                    'condition_json' => json_encode([
                        'scope' => 'global',
                        'conditions' => [
                            ['field' => 'inactive_days', 'operator' => '>', 'value' => 7],
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                    'result_text' => 'Anda sempat jeda {inactive_days} hari. Mulai lagi dengan latihan ringan 10 soal per hari untuk mengembalikan ritme.',
                    'priority' => 50,
                ],
            ];

            foreach ($rules as $rule) {
                DB::table('expert_rules')->updateOrInsert(
                    ['code' => $rule['code']],
                    [
                        'condition_json' => $rule['condition_json'],
                        'result_text' => $rule['result_text'],
                        'is_active' => true,
                        'priority' => $rule['priority'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('study_plans');
        Schema::dropIfExists('expert_rules');
        Schema::dropIfExists('recommendation_logs');
    }
};
