<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('vessel_name')->nullable()->after('confidence_score');
            $table->date('loading_date')->nullable()->after('vessel_name');
            $table->date('discharge_date')->nullable()->after('loading_date');
            $table->decimal('bl_liters_obs', 15, 3)->nullable()->after('discharge_date');
            $table->decimal('liters_15c', 15, 3)->nullable()->after('bl_liters_obs');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'vessel_name',
                'loading_date',
                'discharge_date',
                'bl_liters_obs',
                'liters_15c',
            ]);
        });
    }
};