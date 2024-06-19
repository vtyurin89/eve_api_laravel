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
        Schema::create('systems', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 100);
            $table->float('x', precision: 20)->nullable();
            $table->float('y', precision: 20)->nullable();
            $table->float('z', precision: 20)->nullable();
            $table->string('security_class')->nullable();
            $table->float('security_status', precision: 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systems');
    }
};
