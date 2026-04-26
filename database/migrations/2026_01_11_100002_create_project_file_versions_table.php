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
        Schema::create('project_file_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_file_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('version');
            $table->string('file_path'); // Path in storage
            $table->string('disk')->default('local'); // Storage disk used
            $table->unsignedBigInteger('file_size'); // Size in bytes
            $table->string('mime_type');
            $table->string('extension', 20);
            $table->string('hash', 64)->nullable(); // SHA256 checksum
            $table->text('notes')->nullable(); // Change notes
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['project_file_id', 'version']);
            $table->index('hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_file_versions');
    }
};
