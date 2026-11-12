<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exam_answers')) {
            return;
        }

        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('question_id')->index();
            $table->unsignedBigInteger('exam_mode_id')->nullable()->index();
            $table->unsignedBigInteger('sub_category_id')->nullable()->index();
            $table->string('kategori', 80)->nullable()->index();
            $table->string('sub_topik', 160)->nullable()->index();
            $table->string('selected_answer', 8)->nullable();
            $table->string('correct_answer', 8)->nullable();
            $table->boolean('is_answered')->default(true)->index();
            $table->boolean('is_correct')->default(false)->index();
            $table->integer('score')->default(0);
            $table->timestamp('answered_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['exam_id', 'question_id'], 'exam_answers_exam_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
