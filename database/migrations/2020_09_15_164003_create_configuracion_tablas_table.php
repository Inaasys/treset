<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfiguracionTablasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracion_tablas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tabla');
            $table->text('campos_activados');
            $table->text('campos_desactivados');
            $table->text('columnas_ordenadas');
            $table->string('ordenar', 255);
            $table->string('usuario', 255);
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
        Schema::dropIfExists('configuracion_tablas');
    }
}
