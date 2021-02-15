<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCotizacionesDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotizaciones_t_detalles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_cotizacion')->nullable();
            $table->string('cotizacion', 50)->nullable();
            $table->dateTime('fecha')->nullable();
            $table->string('numero_parte', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('unidad', 255)->nullable();
            $table->string('status_refaccion', 255)->nullable();
            $table->string('insumo', 255)->nullable();
            $table->decimal('precio', 30, 6)->nullable();
            $table->decimal('cantidad', 30, 6)->nullable();
            $table->decimal('importe', 30, 6)->nullable();
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
        Schema::dropIfExists('cotizaciones_detalles');
    }
}
