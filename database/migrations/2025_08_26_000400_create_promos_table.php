<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // uppercase
            $table->string('name');
            $table->decimal('discount_rate', 5, 2); // e.g., 0.10 for 10%
            $table->foreignId('apply_room_type_id')->nullable(); // optional filter by room type
            $table->boolean('active')->default(true);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
