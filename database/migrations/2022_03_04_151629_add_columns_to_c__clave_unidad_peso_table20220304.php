<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCClaveUnidadPesoTable20220304 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('c_ClaveUnidadPeso', function (Blueprint $table) {
            
            $table->string('Nombre', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('c_ClaveUnidadPeso', function (Blueprint $table) {
            //
        });
    }
}
