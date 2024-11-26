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
        Schema::create('meet_point_zones', function (Blueprint $table) {
            $table->foreignId('zone_id')->constrained()->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('meet_point_id')->constrained()->onUpdate('cascade')->onDelete('restrict');
            $table->primary(['zone_id', 'meet_point_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meet_point_zones');
    }
};
