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
        Schema::table('levels', function (Blueprint $table) {
            $table->unsignedBigInteger('college_id')->nullable()->after('id');
            $table->foreign('college_id')->references('id')->on('colleges')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropForeign(['college_id']);
            $table->dropColumn('college_id');
        });
    }

};
