<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom ini sudah dibuat oleh migrasi create_documents_table
        if (Schema::hasColumn('documents', 'ocr_text')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table) {
            $table->longText('ocr_text')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('ocr_text');
        });
    }
};
