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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            // [PERBAIKAN] Hapus typo 'reserervation_id' menjadi 'reservation_id'
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            // Rincian biaya
            $table->decimal('subtotal_amount', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->decimal('service_fee_amount', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('paid_at')->nullable(); 
            $table->string('payment_method')->nullable();
            // Bukti/Review pembayaran
            $table->string('payment_proof_path')->nullable();
            $table->string('payment_review_status')->nullable(); // pending|approved|rejected
            $table->timestamp('payment_proof_uploaded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
