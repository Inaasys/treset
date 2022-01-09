<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitulosFirmas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Capturó Movimiento',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Recibió Mercancia',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        
        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Solicitó Mercancia',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Recibió Mercancia',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Recibió ContraRecibo',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Entregó Mercancia',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Cliente',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Realizó',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Hecho por',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Revisado por',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Autorizado por',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Recibió',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Realizó Ajuste',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Revisó Ajuste',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('titulos_firmas')->insert(array(
            'Titulo' => 'Autorizo Ajuste',
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
