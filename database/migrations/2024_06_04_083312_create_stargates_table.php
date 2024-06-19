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
        Schema::create('stargates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('x', precision: 20)->nullable();
            $table->float('y', precision: 20)->nullable();
            $table->float('z', precision: 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stargates');
    }
};
