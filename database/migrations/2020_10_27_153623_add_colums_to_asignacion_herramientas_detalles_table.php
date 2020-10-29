<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumsToAsignacionHerramientasDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asignacion_herramientas_detalles', function (Blueprint $table) {
            $table->string('estado_auditoria', 255)->nullable();
            $table->decimal('cantidad_auditoria', 30, 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asignacion_herramientas_detalles', function (Blueprint $table) {
            //
        });
    }
}
