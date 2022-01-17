<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromCotizacionesT extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotizaciones_t', function (Blueprint $table) {
            $table->dropColumn('num_remision');
            $table->dropColumn('num_equipo');
            $table->dropColumn('ot_tecnodiesel');
            $table->dropColumn('ot_tyt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotizaciones_t', function (Blueprint $table) {
            //
        });
    }
}
