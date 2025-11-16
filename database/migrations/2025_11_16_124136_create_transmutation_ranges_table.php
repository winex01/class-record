<?php

use App\Models\TransmuteTemplate;
use App\Models\User;
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
        Schema::create('transmutation_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TransmuteTemplate::class)->constrained()->cascadeOnDelete();

            // Initial grade range
            $table->decimal('initial_min', 5, 2);
            $table->decimal('initial_max', 5, 2);

            // Transmuted grade
            $table->unsignedTinyInteger('transmuted_grade');

            $table->timestamps();

            // Prevent duplicate ranges for the SAME class
            $table->unique(
                ['transmute_template_id', 'initial_min', 'initial_max'],
                'tt_min_max_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transmutation_ranges');
    }
};
