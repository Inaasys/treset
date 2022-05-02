<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCxcDetallesTable20220429 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CxC Detalles', function (Blueprint $table) {
            $table->decimal('Equivalencia', 30, 6)->nullable();
            $table->string('ObjetoImp', 2)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('CxC Detalles', function (Blueprint $table) {
            //
        });
    }
}
