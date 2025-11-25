<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bin_id')->constrained()->onDelete('cascade');
            $table->float('weight'); // weight of waste in kg
            $table->float('level')->comment('Fill level percentage, 0 to 100');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_levels');
    }
};
