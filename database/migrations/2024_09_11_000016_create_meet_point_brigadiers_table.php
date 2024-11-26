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
        Schema::create('meet_point_brigadiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meet_point_id')->constrained()->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('brigadier_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('incident_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meet_point_brigadiers');
    }
};
