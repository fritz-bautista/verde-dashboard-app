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
        // Ensure it’s dropped first to avoid duplicate table errors
        Schema::dropIfExists('waste_predictions');

        Schema::create('waste_predictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bin_id')->nullable(); // link to bins
            $table->float('predicted_waste_level')->nullable(); // prediction result
            $table->timestamp('predicted_at')->nullable(); // when it was predicted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_predictions');
    }
};
