<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            if (!Schema::hasColumn('soals', 'audit_status')) {
                $table->string('audit_status', 24)->nullable()->after('qc_confidence');
                $table->timestamp('audited_at')->nullable()->after('audit_status');
                $table->foreignId('audited_by')->nullable()->after('audited_at')->constrained('users')->nullOnDelete();
                $table->string('last_audit_source', 32)->nullable()->after('audited_by');
                $table->decimal('last_audit_score', 5, 2)->nullable()->after('last_audit_source');
                $table->index('audit_status', 'soals_audit_status_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            if (Schema::hasColumn('soals', 'audit_status')) {
                $table->dropIndex('soals_audit_status_index');
                $table->dropConstrainedForeignId('audited_by');
                $table->dropColumn(['audit_status', 'audited_at', 'last_audit_source', 'last_audit_score']);
            }
        });
    }
};
