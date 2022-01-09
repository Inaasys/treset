<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoCambioVolvoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_cambio_volvo', function (Blueprint $table) {
            $table->bigIncrements('Numero');
            $table->string('Moneda', 50)->nullable();
            $table->dateTime('Fecha')->nullable();
            $table->decimal('Valor', 30, 6)->nullable();
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
        Schema::dropIfExists('tipo_cambio_volvo');
    }
}
