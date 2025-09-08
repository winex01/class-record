<?php

use App\Models\User;
use App\Models\SchoolClass;
use App\Enums\AssessmentStatus;
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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(SchoolClass::class)->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->date('date')->nullable();
            $table->string('type')->nullable(); // enum: AssessmentType
            $table->string('max_score')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default(AssessmentStatus::PENDING->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
