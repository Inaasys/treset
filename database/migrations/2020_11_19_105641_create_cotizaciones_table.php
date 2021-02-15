<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCotizacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotizaciones_t', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cotizacion', 50)->nullable();
            $table->string('serie', 255)->nullable();
            $table->dateTime('fecha')->nullable();
            $table->string('num_remision', 255)->nullable();
            $table->integer('num_equipo')->nullable();
            $table->decimal('subtotal', 30, 6)->nullable();
            $table->decimal('iva', 30, 6)->nullable();
            $table->decimal('total', 30, 6)->nullable();
            $table->string('ot_tecnodiesel', 255)->nullable();
            $table->string('ot_tyt', 255)->nullable();
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
        Schema::dropIfExists('cotizaciones');
    }
}
