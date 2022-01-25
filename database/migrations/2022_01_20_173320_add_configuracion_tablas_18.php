<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfiguracionTablas18 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'ListaPreciosFleetguard',
            'campos_activados' => 'Numero,NumeroParte,Descripcion,PrecioPublico',
            'campos_desactivados' => 'created_at,updated_at',
            'columnas_ordenadas' => 'Numero,NumeroParte,Descripcion,PrecioPublico',
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
