<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bin_id')->constrained('bins')->onDelete('cascade');
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->integer('week')->nullable();
            $table->float('weight')->default(0); // in kg
            $table->string('status')->default('Half'); // Half, Full, Collected, etc.
            $table->integer('points')->default(0); // reward/point system
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_records');
    }
};
