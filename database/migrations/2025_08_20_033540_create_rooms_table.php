<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->string('room_number')->unique();
            $table->foreignId('room_type_id')->nullable(); // relasi ke room_types (FK opsional)
            $table->string('status')->default('Available');
            $table->integer('floor');
            $table->text('description')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
