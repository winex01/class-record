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
        Schema::create('fee_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(SchoolClass::class)->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->decimal('amount')->unsigned()->nullable();
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_collected')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_collections');
    }
};
