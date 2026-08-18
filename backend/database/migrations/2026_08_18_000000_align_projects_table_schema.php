<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bring an already-migrated `projects` table (from an earlier iteration of this
     * migration, before the infra columns were dropped) up to the current schema.
     */
    public function up(): void
    {
        $hasType = Schema::hasColumn('projects', 'type');
        $hasNotes = Schema::hasColumn('projects', 'notes');
        $hasDescription = Schema::hasColumn('projects', 'description');

        Schema::table('projects', function (Blueprint $table) use ($hasType, $hasNotes, $hasDescription) {
            if (!$hasType) {
                $table->string('type')->default('site')->after('name');
            }

            if ($hasNotes && !$hasDescription) {
                $table->renameColumn('notes', 'description');
            } elseif (!$hasDescription) {
                $table->text('description')->nullable();
            }
        });

        $dropColumns = array_filter([
            'slug',
            'path',
            'compose_project',
            'nginx_config_path',
            'redis_host',
            'redis_port',
            'redis_password',
            'redis_db',
            'last_payment_at',
        ], fn (string $column) => Schema::hasColumn('projects', $column));

        if ($dropColumns) {
            Schema::table('projects', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way alignment migration — restoring the dropped infra columns isn't meaningful.
    }
};
