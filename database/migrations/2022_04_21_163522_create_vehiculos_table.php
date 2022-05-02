<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('PermisoSCT', 255)->nullable();
            $table->string('NumeroPermisoSCT', 255)->nullable();
            $table->string('NombreAseguradora', 255)->nullable();
            $table->string('NumeroPolizaSeguro', 50)->nullable();
            $table->string('Placa', 50)->nullable();
            $table->string('Año', 5)->nullable();
            $table->string('SubTipoRemolque', 50)->nullable();
            $table->string('PlacaSubTipoRemolque', 50)->nullable();
            $table->string('Marca', 50)->nullable();
            $table->string('Modelo', 50)->nullable();
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
        Schema::dropIfExists('vehiculos');
    }
}
