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
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('received_by');
            $table->integer('current_approval_level')->default(0)->after('status');
            $table->integer('max_approval_level')->default(1)->after('current_approval_level');
            $table->boolean('is_fully_approved')->default(false)->after('max_approval_level');
            $table->unsignedBigInteger('approved_by')->nullable()->after('is_fully_approved');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'current_approval_level',
                'max_approval_level',
                'is_fully_approved',
                'approved_by',
                'approved_at',
                'rejection_reason'
            ]);
        });
    }
};
