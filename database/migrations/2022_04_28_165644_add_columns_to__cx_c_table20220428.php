<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCxCTable20220428 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CxC', function (Blueprint $table) {
            $table->string('UsoCfdi', 5)->nullable();
            $table->string('Exportacion', 5)->nullable();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('CxC', function (Blueprint $table) {
            //
        });
    }
}
