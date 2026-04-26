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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general'); // general, storage, mail, etc.
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'key' => 'storage_driver',
                'value' => 'local',
                'type' => 'string',
                'group' => 'storage',
                'description' => 'File storage driver (local or s3)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_file_size',
                'value' => '52428800', // 50MB in bytes
                'type' => 'integer',
                'group' => 'storage',
                'description' => 'Maximum file upload size in bytes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 's3_bucket',
                'value' => '',
                'type' => 'string',
                'group' => 'storage',
                'description' => 'S3 bucket name',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 's3_region',
                'value' => 'ap-southeast-1',
                'type' => 'string',
                'group' => 'storage',
                'description' => 'S3 region',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
