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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('document')->nullable()->after('email');
            $table->string('zip_code', 9)->nullable()->after('address');
            $table->string('street_name')->nullable()->after('zip_code');
            $table->string('street_number')->nullable()->after('street_name');
            $table->string('neighborhood')->nullable()->after('street_number');
            $table->string('city')->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['document', 'zip_code', 'street_name', 'street_number', 'neighborhood', 'city', 'state']);
        });
    }
};
