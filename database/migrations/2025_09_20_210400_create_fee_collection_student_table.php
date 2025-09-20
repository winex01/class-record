<?php

use App\Models\Student;
use App\Models\FeeCollection;
use App\Enums\FeeCollectionStatus;
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
        Schema::create('fee_collection_student', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FeeCollection::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Student::class)->constrained()->cascadeOnDelete();
            $table->decimal('amount')->unsigned()->nullable();
            $table->string('status')->default(FeeCollectionStatus::UNPAID->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_collection_student');
    }
};
