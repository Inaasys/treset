<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCClaveUnidadPesoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_ClaveUnidadPeso', function (Blueprint $table) {
            $table->bigIncrements('Numero');
            $table->string('Clave', 20)->nullable();
            $table->text('Descripcion')->nullable();
            $table->string('Nota', 255)->nullable();
            $table->string('FechaDeInicioDeVigencia', 50)->nullable();
            $table->string('FechaDeFinDeVigencia', 50)->nullable();
            $table->string('Simbolo', 50)->nullable();
            $table->string('Bandera', 50)->nullable();
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
        Schema::dropIfExists('c_ClaveUnidadPeso');
    }
}
