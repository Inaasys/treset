<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumsToConfiguracionTablasTable05072021 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configuracion_tablas', function (Blueprint $table) {            
            $table->text('campos_busquedas')->nullable();
            $table->string('primerordenamiento', 100)->nullable();
            $table->string('formaprimerordenamiento', 5)->nullable();
            $table->string('segundoordenamiento', 100)->nullable();
            $table->string('formasegundoordenamiento', 5)->nullable();
            $table->string('tercerordenamiento', 100)->nullable();
            $table->string('formatercerordenamiento', 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configuracion_tablas', function (Blueprint $table) {
            //
        });
    }
}
