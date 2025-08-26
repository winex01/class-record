<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // class name
            $table->text('description')->nullable(); // optional description

            // TODO:: WIP, TBD
            $table->string('type')->default('academic'); // academic, vocational, training, seperate model

            $table->json('levels')->nullable();
            $table->json('tags')->nullable(); // store tags as JSON array
            $table->boolean('active')->default(true); // status

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_classes');
    }
};
