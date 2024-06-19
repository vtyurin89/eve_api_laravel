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
        Schema::table('systems', function (Blueprint $table) {
            $table->foreign('constellation_id')->references('id')->on('constellations')->onDelete('cascade');
        });
        Schema::table('constellations', function (Blueprint $table) {
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
        });
        Schema::table('stations', function (Blueprint $table) {
            $table->foreign('system_id')->references('id')->on('systems')->onDelete('cascade');
        });
        Schema::table('stargates', function (Blueprint $table) {
            $table->foreign('system_id')->references('id')->on('systems')->onDelete('cascade');
        });
        Schema::table('danger_ratings', function (Blueprint $table) {
            $table->foreign('system_id')->references('id')->on('systems')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->dropColumn('constellation_id');
        });
        Schema::table('constellations', function (Blueprint $table) {
            $table->dropColumn('region_id');
        });
        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn('system_id');
        });
        Schema::table('stargates', function (Blueprint $table) {
            $table->dropColumn('system_id');
        });
        Schema::table('danger_ratings', function (Blueprint $table) {
            $table->dropColumn('system_id');
        });
    }
};
