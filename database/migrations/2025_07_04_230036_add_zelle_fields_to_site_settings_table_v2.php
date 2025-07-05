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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('zelle_email')->nullable()->after('stripe_currency');
            $table->string('zelle_name')->nullable()->after('zelle_email');
            $table->text('zelle_instructions')->nullable()->after('zelle_name');
            $table->boolean('zelle_enabled')->default(false)->after('zelle_instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['zelle_email', 'zelle_name', 'zelle_instructions', 'zelle_enabled']);
        });
    }
};
