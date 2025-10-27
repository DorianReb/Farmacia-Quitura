<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Schema::table('asigna_promociones', function (Blueprint $table) {
        //     $table->dropColumn('estado');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asigna_promociones', function (Blueprint $table) {
            // Por si necesitas revertir:
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo')->after('promocion_id');
        });
    }
};
