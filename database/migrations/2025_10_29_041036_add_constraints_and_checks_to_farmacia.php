<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) CHECK porcentaje 10–40 en promociones
        DB::statement("
          ALTER TABLE promociones
          ADD CONSTRAINT chk_promociones_porcentaje
          CHECK (porcentaje BETWEEN 10 AND 40)
        ");

        // 2) CHECKs en lotes: cantidad >= 0 y precio_compra > 0
        DB::statement("
          ALTER TABLE lotes
          ADD CONSTRAINT chk_lotes_cantidad_nonneg CHECK (cantidad >= 0),
          ADD CONSTRAINT chk_lotes_precio_compra_pos CHECK (precio_compra > 0)
        ");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En MariaDB usa DROP CONSTRAINT
        DB::statement("ALTER TABLE promociones DROP CONSTRAINT chk_promociones_porcentaje");
        DB::statement("ALTER TABLE lotes DROP CONSTRAINT chk_lotes_cantidad_nonneg");
        DB::statement("ALTER TABLE lotes DROP CONSTRAINT chk_lotes_precio_compra_pos");
    }
};
