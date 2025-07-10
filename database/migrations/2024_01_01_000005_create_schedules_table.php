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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('group'); // group, private, special
            $table->string('special_type')->nullable();
            $table->string('location')->nullable();
            $table->string('photo')->nullable();
            $table->string('slug')->unique();
            $table->foreignId('trainer_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_participants')->default(10);
            $table->integer('current_participants')->default(0);
            $table->decimal('price', 8, 2)->default(0);
            $table->decimal('unlimited_price', 8, 2)->nullable();
            $table->decimal('discount_price', 8, 2)->nullable();
            $table->boolean('is_discounted')->default(false);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->timestamp('discount_expiry_date')->nullable();
            $table->string('status')->default('active'); // active, inactive, cancelled, completed
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_unlimited_bookings')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
}; 