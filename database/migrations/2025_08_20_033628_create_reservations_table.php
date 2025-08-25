<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('status')->default('Confirmed');
            $table->text('special_requests')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};