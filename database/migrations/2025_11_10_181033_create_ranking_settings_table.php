<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ranking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('semester_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->string('status')->default('Not Started'); // Active / Completed / Stopped
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('ranking_settings');
    }
};
