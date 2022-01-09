<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFirmasRelDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('firmas_rel_documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('TipoDocumento', 255)->nullable();
            $table->string('Documento', 255)->nullable();
            $table->integer('IdUsuario');
            $table->dateTime('Fecha')->nullable();
            $table->string('ReferenciaPosicion', 255)->nullable();
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
        Schema::dropIfExists('firmas_rel_documentos');
    }
}
