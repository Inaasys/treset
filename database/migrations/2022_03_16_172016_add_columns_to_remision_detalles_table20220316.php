<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToRemisionDetallesTable20220316 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Remisiones Detalles', function (Blueprint $table) {
            $table->decimal('PorRemisionar', 30, 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Remisiones Detalles', function (Blueprint $table) {
            //
        });
    }
}
