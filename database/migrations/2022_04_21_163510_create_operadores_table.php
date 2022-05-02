<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperadoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operadores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Rfc', 20)->nullable();
            $table->string('Nombre', 255)->nullable();
            $table->string('NumeroLicencia', 50)->nullable();
            $table->string('Calle', 255)->nullable();
            $table->string('NoExterior',10)->nullable();
            $table->string('NoInterior',10)->nullable();
            $table->string('Colonia', 255)->nullable();
            $table->string('Localidad', 255)->nullable();
            $table->string('Referencia', 255)->nullable();
            $table->string('Municipio', 255)->nullable();
            $table->string('Estado', 255)->nullable();
            $table->string('Pais', 255)->nullable();
            $table->string('CodigoPostal', 5)->nullable();
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
        Schema::dropIfExists('operadores');
    }
}
