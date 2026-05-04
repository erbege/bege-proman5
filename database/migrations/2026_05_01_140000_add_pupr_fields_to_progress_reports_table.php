<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add PUPR-compliant fields to progress_reports table.
     *
     * These fields align with Kementerian PUPR standards for daily site reports:
     * - Equipment details (jenis, jumlah, kondisi peralatan)
     * - Material usage summary (bahan yang digunakan hari ini)
     * - Safety/K3 information (insiden, near-miss, APD)
     * - Weather duration (durasi cuaca berpengaruh)
     * - Next day plan (rencana kerja esok hari)
     * - Labor details JSON (klasifikasi tenaga kerja: mandor/tukang/pekerja)
     */
    public function up(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            // Peralatan: [{"name":"Excavator","qty":2,"condition":"baik","hours":8}]
            $table->json('equipment_details')->nullable()->after('labor_details');

            // Material yang dipakai hari ini: [{"material":"Semen","qty_used":50,"unit":"sak"}]
            $table->json('material_usage_summary')->nullable()->after('equipment_details');

            // K3/Safety: {"incidents":0,"near_miss":0,"apd_compliance":true,"notes":""}
            $table->json('safety_details')->nullable()->after('material_usage_summary');

            // Durasi cuaca berpengaruh: "3 jam hujan pagi"
            $table->string('weather_duration')->nullable()->after('weather');

            // Rencana kerja esok hari
            $table->text('next_day_plan')->nullable()->after('safety_details');
        });

        // Add labor_details column if it doesn't exist as a JSON column yet
        // (It was in fillable but may not exist in the original migration)
        if (!Schema::hasColumn('progress_reports', 'labor_details')) {
            Schema::table('progress_reports', function (Blueprint $table) {
                $table->json('labor_details')->nullable()->after('workers_count');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropColumn([
                'equipment_details',
                'material_usage_summary',
                'safety_details',
                'weather_duration',
                'next_day_plan',
            ]);
        });
    }
};
