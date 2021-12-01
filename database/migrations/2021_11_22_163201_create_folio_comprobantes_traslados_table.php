<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFolioComprobantesTrasladosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Folios Comprobantes Traslados', function (Blueprint $table) {
            $table->bigIncrements('Numero');
            $table->string('Serie', 10)->nullable();
            $table->string('Esquema', 10)->nullable();
            $table->integer('FolioInicial');
            $table->string('Titulo', 20)->nullable();
            $table->string('ArchivoCertificado', 100)->nullable();
            $table->string('ArchivoLlave', 100)->nullable();
            $table->string('Contraseña', 100)->nullable();
            $table->string('NoCertificado', 50)->nullable();
            $table->string('ValidoDesde', 20)->nullable();
            $table->string('ValidoHasta', 20)->nullable();
            $table->string('Empresa', 255)->nullable();
            $table->text('Domicilio')->nullable();
            $table->string('Leyenda1', 255)->nullable();
            $table->string('Leyenda2', 255)->nullable();
            $table->string('Leyenda3', 255)->nullable();
            $table->text('Leyenda')->nullable();
            $table->text('CertificadoBase64')->nullable();
            $table->text('LlaveBase64')->nullable();
            $table->string('Version', 10)->nullable();
            $table->string('Predeterminar', 1)->nullable();
            $table->string('Status', 20)->nullable();
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
        Schema::dropIfExists('Folios Comprobantes Traslados');
    }
}
