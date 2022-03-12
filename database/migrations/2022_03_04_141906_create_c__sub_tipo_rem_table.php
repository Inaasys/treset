<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCSubTipoRemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_SubTipoRem', function (Blueprint $table) {
            $table->bigIncrements('Numero');
            $table->string('Clave', 20)->nullable();
            $table->string('RemolqueOSemiremolque', 255)->nullable();
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
        Schema::dropIfExists('c_SubTipoRem');
    }
}
