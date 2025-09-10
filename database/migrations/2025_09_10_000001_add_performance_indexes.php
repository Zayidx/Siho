<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->index('status');
            $table->index('check_in_date');
            $table->index('check_out_date');
            $table->index('guest_id');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->index('payment_review_status');
            $table->index('paid_at');
            $table->index('payment_proof_uploaded_at');
        });

        Schema::table('fnb_orders', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('is_popular');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['check_in_date']);
            $table->dropIndex(['check_out_date']);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex(['payment_review_status']);
            $table->dropIndex(['paid_at']);
            $table->dropIndex(['payment_proof_uploaded_at']);
        });

        Schema::table('fnb_orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['is_popular']);
        });
    }
};
