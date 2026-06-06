<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {

            // Nama kapal
            $table->string('ship_name')
                ->nullable()
                ->after('user_id');

            // Hasil klasifikasi
            $table->enum('category', [
                'invoice',
                'loading',
                'bongkar'
            ])
            ->nullable()
            ->after('ship_name');

            // Hasil OCR
            $table->longText('ocr_result')
                ->nullable()
                ->after('category');

            // Confidence OCR / Rule Based
            $table->float('confidence')
                ->nullable()
                ->after('ocr_result');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {

            $table->dropColumn([
                'ship_name',
                'category',
                'ocr_result',
                'confidence'
            ]);

        });
    }
};