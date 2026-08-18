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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('site');
            $table->string('domain')->nullable();
            $table->string('client_name');
            $table->string('client_contact')->nullable();
            $table->decimal('monthly_value', 10, 2)->nullable();
            $table->unsignedTinyInteger('due_day')->nullable();
            $table->string('payment_status')->default('pendente');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
