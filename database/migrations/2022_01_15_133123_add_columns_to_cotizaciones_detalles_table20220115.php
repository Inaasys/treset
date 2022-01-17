<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCotizacionesDetallesTable20220115 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotizaciones_t_detalles', function (Blueprint $table) {
            
            $table->string('num_remision', 255)->nullable();
            $table->decimal('fecha_remision', 30, 6)->nullable();
            $table->string('num_equipo')->nullable();
            $table->string('ot_tecnodiesel', 255)->nullable();
            $table->string('ot_tyt', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotizaciones_t_detalles', function (Blueprint $table) {
            //
        });
    }
}
