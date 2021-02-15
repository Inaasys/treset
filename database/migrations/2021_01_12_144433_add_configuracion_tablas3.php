<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'cotizaciones_t',
            'campos_activados' => 'id,cotizacion,fecha,subtotal,iva,total,ot_tecnodiesel,ot_tyt,status,motivo_baja,equipo,usuario,periodo',
            'campos_desactivados' => 'serie,num_remision,requisicion,num_equipo',
            'columnas_ordenadas' => 'id,cotizacion,fecha,subtotal,iva,total,ot_tecnodiesel,ot_tyt,status,motivo_baja,equipo,usuario,periodo',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
