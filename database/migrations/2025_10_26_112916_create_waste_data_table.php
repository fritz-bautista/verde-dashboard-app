<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bin_id')->constrained('bins')->onDelete('cascade');
            $table->dateTime('predicted_overflow_time')->nullable();
            $table->float('overflow_probability')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_predictions');
    }
};
