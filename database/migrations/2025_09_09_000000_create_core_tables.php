<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Users + auth helpers
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('pending_email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Profile
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('id_number')->nullable()->unique();
            $table->string('foto')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('stay_purpose', 120)->nullable();

            $table->foreignId('role_id')->constrained('roles')->onDelete('restrict');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Core hotel structures
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->unsignedInteger('capacity')->default(2);
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->string('status')->default('Available');
            $table->integer('floor');
            $table->text('description')->nullable();
            $table->json('personalized_facilities')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('users')->cascadeOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('status')->default('Confirmed');
            $table->text('special_requests')->nullable();
            $table->timestamps();
        });

        Schema::create('reservation_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            $table->unique(['reservation_id', 'room_id']);
        });

        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->decimal('subtotal_amount', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->decimal('service_fee_amount', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->string('payment_review_status')->nullable();
            $table->timestamp('payment_proof_uploaded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('facility_room_type', function (Blueprint $table) {
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->primary(['facility_id', 'room_type_id']);
        });

        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->string('category', 30)->nullable();
            $table->timestamps();
            $table->index('category');
        });

        Schema::create('hotel_galleries', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('category', 30)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();
            $table->index('category');
        });

        // F&B
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->constrained('menu_categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('fnb_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('service_type', 20)->default('in_room');
            $table->integer('total_amount')->default(0);
            $table->string('room_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('fnb_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('fnb_orders')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->integer('qty');
            $table->integer('unit_price');
            $table->integer('line_total');
            $table->timestamps();
        });

        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('discount_rate', 5, 2);
            $table->foreignId('apply_room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamps();
        });

        Schema::create('room_type_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(['room_type_id', 'date']);
        });

        Schema::create('room_item_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity')->default(0);
            $table->string('unit', 20)->nullable();
            $table->timestamps();
            $table->index(['room_id', 'name']);
        });

        Schema::create('room_type_item_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity')->default(0);
            $table->string('unit', 20)->nullable();
            $table->timestamps();
            $table->index(['room_type_id', 'name']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->string('phone')->nullable();
            $table->text('message');
            $table->string('ip')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('room_type_item_templates');
        Schema::dropIfExists('room_item_inventories');
        Schema::dropIfExists('room_type_inventories');
        Schema::dropIfExists('promos');
        Schema::dropIfExists('fnb_order_items');
        Schema::dropIfExists('fnb_orders');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_categories');
        Schema::dropIfExists('hotel_galleries');
        Schema::dropIfExists('room_images');
        Schema::dropIfExists('facility_room_type');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('reservation_room');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
