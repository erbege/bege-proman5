<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rab_items', function (Blueprint $table) {
            $table->boolean('can_parallel')->default(false)->after('sort_order')
                ->comment('If true, this item can be executed in parallel with the previous item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rab_items', function (Blueprint $table) {
            $table->dropColumn('can_parallel');
        });
    }
};
