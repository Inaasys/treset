<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'prestamo_herramientas',
            'campos_activados' => 'id,prestamo,fecha,recibe_herramienta,entrega_herramienta,total,observaciones,status,motivo_baja,periodo,nombre_recibe_herramienta,nombre_entrega_herramienta',
            'campos_desactivados' => 'serie,equipo,usuario,tipo_recibe_herramienta,tipo_entrega_herramienta',
            'columnas_ordenadas' => 'id,prestamo,fecha,recibe_herramienta,entrega_herramienta,total,observaciones,status,motivo_baja,periodo,nombre_recibe_herramienta,nombre_entrega_herramienta',
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
