<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('utility_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utility_id')->constrained('utilities')->onDelete('cascade');
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade'); // links to floors
            $table->date('assigned_date')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_assignments');
    }
};
