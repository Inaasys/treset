<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas13 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Produccion',
            'campos_activados' => 'Produccion,Serie,Folio,Fecha,Codigo,Almacen,Cantidad,Costo,Obs,Status,Motivo de Baja,Equipo,Usuario,Periodo',
            'campos_desactivados' => '',
            'columnas_ordenadas' => 'Produccion,Serie,Folio,Fecha,Codigo,Almacen,Cantidad,Costo,Obs,Status,Motivo de Baja,Equipo,Usuario,Periodo',
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
