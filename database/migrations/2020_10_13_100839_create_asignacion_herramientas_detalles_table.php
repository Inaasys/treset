<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAsignacionHerramientasDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asignacion_herramientas_detalles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_asignacion_herramienta');
            $table->string('asignacion', 50)->nullable();
            $table->dateTime('fecha')->nullable();
            $table->string('herramienta', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('unidad', 255)->nullable();
            $table->decimal('cantidad', 30, 6)->nullable();
            $table->decimal('precio', 30, 6)->nullable();
            $table->decimal('total', 30, 6)->nullable();
            $table->string('estado_herramienta', 255)->nullable();
            $table->integer('item')->nullable();
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
        Schema::dropIfExists('asignacion_herramientas_detalles');
    }
}
