<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ❌ Remove this if it already exists
            // $table->enum('role', ['admin', 'college', 'student'])->default('student');

            // ✅ Only add this if it's missing
            if (!Schema::hasColumn('users', 'college_id')) {
                $table->foreignId('college_id')->nullable()->constrained()->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'college_id')) {
                $table->dropConstrainedForeignId('college_id');
            }
            // $table->dropColumn('role'); // optional if you created it before
        });
    }

};
