<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // user_uploaded_proof, manual_submit, online_paid, admin_approved, admin_rejected
            $table->text('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};

