<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToEmpresaTable20220218 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Empresa', function (Blueprint $table) {
            $table->text('PedirObligatoriamenteReferenciaEnRemisiones')->nullable();
            $table->text('PedirObligatoriamenteOrdenServicioEnRemisiones')->nullable();
            $table->text('PedirObligatoriamenteEquipoEnRemisiones')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Empresa', function (Blueprint $table) {
            //
        });
    }
}
