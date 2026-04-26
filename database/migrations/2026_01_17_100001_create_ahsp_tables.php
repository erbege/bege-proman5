<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Creates tables for AHSP (Analisa Harga Satuan Pekerjaan):
     * - ahsp_categories: Kategori pekerjaan (hierarkis)
     * - ahsp_work_types: Jenis pekerjaan AHSP
     * - ahsp_components: Komponen (tenaga kerja/bahan/peralatan)
     * - ahsp_base_prices: Harga satuan dasar per wilayah
     * - ahsp_price_histories: Histori perubahan harga (audit trail)
     * - ahsp_price_snapshots: Snapshot harga saat RAB dibuat
     */
    public function up(): void
    {
        // 1. Kategori Pekerjaan (hierarchical: 1. -> 1.1 -> 1.1.1)
        Schema::create('ahsp_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20); // 1, 1.1, 1.1.1, etc.
            $table->string('name'); // PERSIAPAN LAPANGAN / SITE WORK
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('level')->default(0); // 0=root, 1, 2, 3...
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('ahsp_categories')->nullOnDelete();
            $table->index(['parent_id', 'sort_order']);
            $table->index('code');
        });

        // 2. Jenis Pekerjaan AHSP (level paling detail, misal: 1.1.1.1)
        Schema::create('ahsp_work_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ahsp_category_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30); // 1.1.1.1
            $table->string('name'); // Pembuatan 1 m' pagar sementara dari kayu tinggi 2 meter
            $table->string('unit', 20); // m', m2, m3, kg, ls
            $table->text('description')->nullable();
            $table->string('source', 50)->default('PUPR'); // SNI, PUPR, BOW, Custom
            $table->string('reference')->nullable(); // SE Dirjen Binkon No 128/SE/Dk/2025
            $table->decimal('overhead_percentage', 5, 2)->default(10.00); // Biaya umum & keuntungan %
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['ahsp_category_id', 'code']);
            $table->index('code');
            $table->index('name');
        });

        // 3. Komponen AHSP (Tenaga Kerja / Bahan / Peralatan)
        Schema::create('ahsp_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ahsp_work_type_id')->constrained()->cascadeOnDelete();
            $table->enum('component_type', ['labor', 'material', 'equipment']); // A, B, C
            $table->string('code', 20)->nullable(); // L.01, L.02
            $table->string('name'); // Pekerja, Tukang kayu, Semen Portland
            $table->string('unit', 20); // OH, m3, kg, liter
            $table->decimal('coefficient', 15, 6); // 0.600, 0.0387
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['ahsp_work_type_id', 'component_type']);
        });

        // 4. Harga Satuan Dasar (per wilayah)
        Schema::create('ahsp_base_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Pekerja, Tukang kayu, Semen Portland (PC)
            $table->string('code', 20)->nullable(); // L.01, M.01
            $table->enum('component_type', ['labor', 'material', 'equipment']);
            $table->string('unit', 20); // OH, kg, m3
            $table->string('region_code', 20); // ID-JK, ID-JB, ID-JT
            $table->string('region_name')->nullable(); // Jakarta, Jawa Barat
            $table->decimal('price', 18, 2); // 100000.00
            $table->date('effective_date');
            $table->string('source')->nullable(); // Permen PUPR, Harga Pasar
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'region_code']);
            $table->index(['component_type', 'region_code']);
            $table->index('effective_date');
        });

        // 5. Histori Perubahan Harga (Audit Trail)
        Schema::create('ahsp_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ahsp_base_price_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_price', 18, 2);
            $table->decimal('new_price', 18, 2);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        // 6. Snapshot Harga saat RAB dibuat (untuk tracking)
        Schema::create('ahsp_price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ahsp_work_type_id')->constrained()->cascadeOnDelete();
            $table->string('region_code', 20);
            $table->decimal('labor_cost', 18, 2)->default(0);
            $table->decimal('material_cost', 18, 2)->default(0);
            $table->decimal('equipment_cost', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0); // A+B+C
            $table->decimal('overhead_percentage', 5, 2)->default(10.00);
            $table->decimal('overhead_cost', 18, 2)->default(0);
            $table->decimal('unit_price', 18, 2)->default(0); // Final price
            $table->json('components_data')->nullable(); // Full breakdown as JSON
            $table->timestamps();

            $table->index(['rab_item_id', 'ahsp_work_type_id']);
        });

        // Add ahsp_work_type_id and source to rab_items
        Schema::table('rab_items', function (Blueprint $table) {
            $table->foreignId('ahsp_work_type_id')->nullable()->after('rab_section_id')
                ->constrained()->nullOnDelete();
            $table->string('source', 20)->default('manual')->after('is_analyzed');
            // source: 'manual', 'import', 'ahsp'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rab_items', function (Blueprint $table) {
            $table->dropForeign(['ahsp_work_type_id']);
            $table->dropColumn(['ahsp_work_type_id', 'source']);
        });

        Schema::dropIfExists('ahsp_price_snapshots');
        Schema::dropIfExists('ahsp_price_histories');
        Schema::dropIfExists('ahsp_base_prices');
        Schema::dropIfExists('ahsp_components');
        Schema::dropIfExists('ahsp_work_types');
        Schema::dropIfExists('ahsp_categories');
    }
};
