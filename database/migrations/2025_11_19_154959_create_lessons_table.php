<?php

use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(SchoolClass::class)->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            // Virtual generated column for case-insensitive search on tags JSON column.
            // MySQL's JSON columns use utf8mb4_bin (case-sensitive) collation, so LIKE searches
            // are case-sensitive by default. This column stores LOWER(tags) so the Flowforge
            // kanban searchable plugin can search tags regardless of case.
            $table->string('tags_search')->virtualAs('LOWER(tags)')->nullable();
            $table->date('completion_date')->nullable();
            $table->json('checklists')->nullable();
            $table->string('status');
            $table->flowforgePositionColumn()->default('1');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
