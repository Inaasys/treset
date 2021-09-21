<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas14 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Requisiciones',
            'campos_activados' => 'Requisicion,Serie,Folio,Cliente,Nombre,Total,Orden,Vin,Economico,Status,MotivoBaja,Usuario,Periodo',
            'campos_desactivados' => 'Equipo,Fecha,Importe,Descuento,SubTotal,Iva,Costo,Comision,Utilidad,Obs,Tipo,Unidad',
            'columnas_ordenadas' => 'Requisicion,Serie,Folio,Cliente,Nombre,Total,Orden,Vin,Economico,Status,MotivoBaja,Usuario,Periodo',
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
