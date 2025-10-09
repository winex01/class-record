<?php

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
        Schema::create('recurrings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->json('monday')->nullable();
            $table->json('tuesday')->nullable();
            $table->json('wednesday')->nullable();
            $table->json('thursday')->nullable();
            $table->json('friday')->nullable();
            $table->json('saturday')->nullable();
            $table->json('sunday')->nullable();
            $table->date('date_start');
            $table->date('date_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurrings');
    }
};
