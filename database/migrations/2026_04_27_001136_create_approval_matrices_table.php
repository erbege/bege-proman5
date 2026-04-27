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
        Schema::create('approval_matrices', function (Blueprint $table) {
            $table->id();
            $table->string('document_type'); // PR, MR, PO
            $table->integer('level');
            $table->string('role_name'); // Role name from Spatie Permissions
            $table->decimal('min_amount', 15, 2)->default(0); // For PO amount limits
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_matrices');
    }
};
