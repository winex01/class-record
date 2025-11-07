<?php

use App\Models\Grade;
use App\Models\Assessment;
use App\Models\GradingComponent;
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
        Schema::create('grade_grading_component', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Grade::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(GradingComponent::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['grade_id', 'grading_component_id']);
        });

        Schema::create('grade_component_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ggc_id')
                ->constrained('grade_grading_component')
                ->cascadeOnDelete();
            $table->foreignIdFor(Assessment::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ggc_id', 'assessment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_component_assessments');
        Schema::dropIfExists('grade_grading_component');
    }
};
