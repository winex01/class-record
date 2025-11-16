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
        Schema::create('grade_transmutations', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(SchoolClass::class)->constrained()->cascadeOnDelete();

            // Initial grade range
            $table->decimal('initial_min', 5, 2);
            $table->decimal('initial_max', 5, 2);

            // Transmuted grade
            $table->decimal('transmuted_grade', 5, 2);

            $table->timestamps();

            // Prevent duplicate ranges for the SAME class
            $table->unique(
                ['school_class_id', 'initial_min', 'initial_max'],
                'gt_class_min_max_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_transmutations');
    }
};
