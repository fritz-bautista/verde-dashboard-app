<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks temporarily
        Schema::disableForeignKeyConstraints();

        // Drop tables in the correct order (children first, then parents)
        Schema::dropIfExists('waste_predictions');
        Schema::dropIfExists('waste_pickups');
        Schema::dropIfExists('waste_levels');
        Schema::dropIfExists('bins');
        Schema::dropIfExists('clusters');

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // No rollback necessary
    }
};
