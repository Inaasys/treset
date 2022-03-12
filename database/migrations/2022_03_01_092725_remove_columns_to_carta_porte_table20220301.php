<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsToCartaPorteTable20220301 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CartaPorte', function (Blueprint $table) {
            $table->dropColumn('Atencion');
            $table->dropColumn('Remitente');
            $table->dropColumn('Destinatario');
            $table->dropColumn('SeRecogeEn');
            $table->dropColumn('SeEntregaEn');
            $table->dropColumn('MiPedido');
            $table->dropColumn('SuPedido');
            $table->dropColumn('Referencia');
            $table->dropColumn('Operador');
            $table->dropColumn('Placas');
            $table->dropColumn('Economico');
            $table->dropColumn('Tanque');
            $table->dropColumn('Tipo');
            $table->dropColumn('Factura');
            $table->dropColumn('Total');
            $table->dropColumn('Obs');
            $table->dropColumn('MotivoBaja');
            $table->dropColumn('Equipo');
            $table->dropColumn('Usuario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('CartaPorte', function (Blueprint $table) {
            //
        });
    }
}
