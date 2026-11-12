<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soal_audits', function (Blueprint $table) {
            $table->boolean('needs_revision')->default(false)->after('pembahasan_ok');
            $table->text('revision_reason')->nullable()->after('needs_revision');

            $table->text('proposed_teks_soal')->nullable()->after('revision_reason');
            $table->text('proposed_opsi_a')->nullable()->after('proposed_teks_soal');
            $table->text('proposed_opsi_b')->nullable()->after('proposed_opsi_a');
            $table->text('proposed_opsi_c')->nullable()->after('proposed_opsi_b');
            $table->text('proposed_opsi_d')->nullable()->after('proposed_opsi_c');
            $table->text('proposed_opsi_e')->nullable()->after('proposed_opsi_d');
            $table->char('proposed_jawaban_benar', 1)->nullable()->after('proposed_opsi_e');
            $table->text('proposed_pembahasan')->nullable()->after('proposed_jawaban_benar');
        });
    }

    public function down(): void
    {
        Schema::table('soal_audits', function (Blueprint $table) {
            $table->dropColumn([
                'needs_revision',
                'revision_reason',
                'proposed_teks_soal',
                'proposed_opsi_a',
                'proposed_opsi_b',
                'proposed_opsi_c',
                'proposed_opsi_d',
                'proposed_opsi_e',
                'proposed_jawaban_benar',
                'proposed_pembahasan',
            ]);
        });
    }
};

