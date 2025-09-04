<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_item_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('name');
            $table->unsignedInteger('quantity')->default(0);
            $table->string('unit', 20)->nullable();
            $table->timestamps();

            $table->index(['room_id','name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_item_inventories');
    }
};

