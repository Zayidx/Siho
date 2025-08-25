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
            $table->decimal('total_amount', 12, 2); 
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('paid_at')->nullable(); 
            $table->string('payment_method')->nullable();
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
