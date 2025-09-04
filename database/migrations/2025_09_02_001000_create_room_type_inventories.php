<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_type_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('quantity'); // maximum allocatable rooms for that date
            $table->timestamps();

            $table->unique(['room_type_id','date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_type_inventories');
    }
};

