<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rab_sections', function (Blueprint $table) {
            $table->foreignId('ahsp_category_id')
                ->nullable()
                ->after('level')
                ->constrained('ahsp_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rab_sections', function (Blueprint $table) {
            $table->dropForeign(['ahsp_category_id']);
            $table->dropColumn('ahsp_category_id');
        });
    }
};
