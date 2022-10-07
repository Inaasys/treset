<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartaPorteDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Carta Porte Documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('CartaPorte')->nullable();
            $table->string('Factura')->nullable();
            $table->string('UUID', 250)->nullable() ;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Carta Porte Documentos');
    }
}
