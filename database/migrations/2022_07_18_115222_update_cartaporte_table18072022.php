<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCartaporteTable18072022 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CartaPorte', function (Blueprint $table) {
            $table->string('EstadoRemitente',60)->change();
            $table->string('PaisDestinatario',20)->change();
            $table->string('EstadoOperador',60)->change();
            $table->string('PaisOperador',20)->change();
            $table->string('EstadoDestinatario',60)->change();
            $table->string('PermisoSCT',25)->change();
            $table->decimal('PesoBrutoTotal', 8, 4)->nullable();
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
            $table->dropColumn('PesoBrutoTotal');
        });
    }
}
