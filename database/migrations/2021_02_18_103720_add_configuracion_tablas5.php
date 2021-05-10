<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Traspasos',
            'campos_activados' => 'Traspaso,Fecha,De,A,Orden,StatusOrden,Nombre,Tipo,Unidad,Economico,Total,Usuario,Equipo,Status,Periodo',
            'campos_desactivados' => 'Serie,Folio,Referencia,Importe,Descuento,SubTotal,Iva,Costo,Comision,Utilidad,Obs,MotivoBaja,NombreDe,NombreA',
            'columnas_ordenadas' => 'Traspaso,Fecha,De,A,Orden,StatusOrden,Nombre,Tipo,Unidad,Economico,Total,Usuario,Equipo,Status,Periodo',
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
