<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_images', function (Blueprint $table) {
            $table->foreignId('room_type_id')->nullable()->after('room_id')->constrained('room_types')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->change();
        });

        // Migrate existing images: set room_type_id from related room
        DB::statement('UPDATE room_images ri JOIN rooms r ON ri.room_id = r.id SET ri.room_type_id = r.room_type_id WHERE ri.room_type_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('room_images', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_type_id');
            $table->foreignId('room_id')->nullable(false)->change();
        });
    }
};

