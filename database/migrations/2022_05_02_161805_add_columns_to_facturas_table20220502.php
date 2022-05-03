<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToFacturasTable20220502 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Facturas', function (Blueprint $table) {
            $table->string('Periodicidad', 5)->nullable(); 
            $table->string('Meses', 5)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Facturas', function (Blueprint $table) {
            //
        });
    }
}
