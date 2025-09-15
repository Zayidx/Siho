<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fnb_orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->string('payment_proof_path')->nullable()->after('notes');
            $table->string('payment_review_status')->nullable()->after('payment_proof_path');
            $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_review_status');
        });
    }

    public function down(): void
    {
        Schema::table('fnb_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_proof_path',
                'payment_review_status',
                'payment_proof_uploaded_at',
            ]);
        });
    }
};

