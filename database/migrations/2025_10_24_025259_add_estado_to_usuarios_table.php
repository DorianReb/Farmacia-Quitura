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
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'estado')) {
                $table->enum('estado', ['Pendiente', 'Activo', 'Rechazado'])
                    ->default('Pendiente')
                    ->after('rol');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            //
            if (Schema::hasColumn('usuarios', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};
