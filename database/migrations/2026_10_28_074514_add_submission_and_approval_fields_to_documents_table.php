<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'ocr_text_edited')) {
                $table->longText('ocr_text_edited')->nullable();
            }
            if (! Schema::hasColumn('documents', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (! Schema::hasColumn('documents', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (! Schema::hasColumn('documents', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('documents', 'admin_note')) {
                $table->text('admin_note')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            $kolom = array_filter(
                ['ocr_text_edited', 'submitted_at', 'approved_at', 'admin_note'],
                fn ($c) => Schema::hasColumn('documents', $c)
            );

            if ($kolom) {
                $table->dropColumn($kolom);
            }
        });
    }
};