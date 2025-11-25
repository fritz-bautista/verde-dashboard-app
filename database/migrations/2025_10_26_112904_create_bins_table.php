<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->integer('capacity')->default(50); // ✅ ADD THIS
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bins');
    }
};
