<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCMaterialPeligrosoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_MaterialPeligroso', function (Blueprint $table) {
            $table->bigIncrements('Numero');
            $table->string('Clave', 20)->nullable();
            $table->text('Descripcion')->nullable();
            $table->string('ClaseODiv', 50)->nullable();
            $table->string('PeligroSecundaria', 50)->nullable();
            $table->text('NombreTecnico')->nullable();
            $table->string('FechaDeInicioDeVigencia', 50)->nullable();
            $table->string('FechaDeFinDeVigencia', 50)->nullable();
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
        Schema::dropIfExists('c_MaterialPeligroso');
    }
}
