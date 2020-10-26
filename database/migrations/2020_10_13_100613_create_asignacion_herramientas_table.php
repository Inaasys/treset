<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAsignacionHerramientasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asignacion_herramientas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('asignacion', 50)->nullable();
            $table->string('serie')->nullable();
            $table->dateTime('fecha');
            $table->integer('recibe_herramienta')->nullable();
            $table->integer('entrega_herramienta')->nullable();
            $table->decimal('total', 30, 6)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('autorizado_por', 255)->nullable();
            $table->dateTime('fecha_autorizacion')->nullable();
            $table->string('status', 255)->nullable();
            $table->text('motivo_baja')->nullable();
            $table->string('equipo', 255)->nullable();
            $table->string('usuario', 255)->nullable();
            $table->string('periodo', 10)->nullable();
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
        Schema::dropIfExists('asignacion_herramientas');
    }
}
