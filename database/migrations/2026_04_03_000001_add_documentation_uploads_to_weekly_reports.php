<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->json('documentation_uploads')->nullable()->after('documentation_ids');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropColumn('documentation_uploads');
        });
    }
};
