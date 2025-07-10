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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // parent
            $table->foreignId('schedule_id')->constrained();
            $table->string('child_name');
            $table->integer('child_age');
            $table->integer('sessions_remaining')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, completed
            $table->string('check_in_code')->unique(); // QR code for check-in
            $table->boolean('is_paid')->default(false);
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
}; 