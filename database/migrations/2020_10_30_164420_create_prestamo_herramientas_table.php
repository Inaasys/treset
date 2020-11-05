<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrestamoHerramientasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prestamo_herramientas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('prestamo', 50)->nullable();
            $table->string('serie')->nullable();
            $table->dateTime('fecha')->nullable();
            $table->integer('recibe_herramienta')->nullable();
            $table->integer('entrega_herramienta')->nullable();
            $table->decimal('total', 30, 6)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('correo', 255)->nullable();
            $table->string('correo_enviado', 255)->nullable();
            $table->string('inicio_prestamo', 255)->nullable();
            $table->string('termino_prestamo', 255)->nullable();
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
        Schema::dropIfExists('prestamo_herramientas');
    }
}
