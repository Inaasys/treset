<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListaPreciosFleetguardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lista_precios_fleetguard', function (Blueprint $table) {
            $table->bigIncrements('Numero');
            $table->string('NumeroParte', 50)->nullable();
            $table->text('Descripcion')->nullable();
            $table->decimal('PrecioPublico', 30, 6)->nullable();
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
        Schema::dropIfExists('lista_precios_fleetguard');
    }
}
