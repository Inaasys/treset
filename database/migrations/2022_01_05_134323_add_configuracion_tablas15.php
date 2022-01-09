<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfiguracionTablas15 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'FirmarDocumentos',
            'campos_activados' => 'id,TipoDocumento,Documento,IdUsuario,Fecha,ReferenciaPosicion,Status,MotivoBaja,Equipo,Usuario,Periodo',
            'campos_desactivados' => 'created_at,updated_at',
            'columnas_ordenadas' => 'id,TipoDocumento,Documento,IdUsuario,Fecha,ReferenciaPosicion,Status,MotivoBaja,Equipo,Usuario,Periodo',
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
