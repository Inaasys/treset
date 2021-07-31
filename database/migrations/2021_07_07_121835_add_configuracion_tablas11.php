<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas11 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'CotizacionesProductos',
            'campos_activados' => 'Cotizacion,Cliente,NombreCliente,Agente,Plazo,Fecha,Tipo,SubTotal,Iva,Total,Status,Equipo,Usuario,Periodo',
            'campos_desactivados' => 'Serie,Folio',
            'columnas_ordenadas' => 'Cotizacion,Cliente,NombreCliente,Agente,Plazo,Fecha,Tipo,SubTotal,Iva,Total,Status,Equipo,Usuario,Periodo',
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
