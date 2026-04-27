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
        Schema::create('document_sequences', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('type')->unique(); // MR, PR, PO, etc.
            $blueprint->string('prefix');
            $blueprint->unsignedBigInteger('next_number')->default(1);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
