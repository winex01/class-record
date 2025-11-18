<?php

use App\Models\User;
use App\Models\TransmuteTemplate;
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
        Schema::create('transmute_template_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TransmuteTemplate::class)->constrained()->cascadeOnDelete();

            // Initial grade range
            $table->decimal('initial_min', 5, 2);
            $table->decimal('initial_max', 5, 2);

            // Transmuted grade
            $table->string('transmuted_grade', 10)->nullable();

            $table->timestamps();

            // Prevent duplicate ranges for the SAME class
            $table->unique(
                ['transmute_template_id', 'initial_min', 'initial_max'],
                'ttr_min_max_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transmute_template_ranges');
    }
};
