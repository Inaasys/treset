<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas12 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'CotizacionesServicio',
            'campos_activados' => 'Cotizacion,Cliente,NombreCliente,Agente,Plazo,Fecha,Unidad,Vin,Economico,Marca,Modelo,Año,Kilometros,Placas,Color,SubTotal,Iva,Total,Status,Equipo,Usuario,Periodo',
            'campos_desactivados' => 'Serie,Folio',
            'columnas_ordenadas' => 'Cotizacion,Cliente,NombreCliente,Agente,Plazo,Fecha,Unidad,Vin,Economico,Marca,Modelo,Año,Kilometros,Placas,Color,SubTotal,Iva,Total,Status,Equipo,Usuario,Periodo',
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
