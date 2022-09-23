<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsIntoFacturasTable21092022 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Facturas', function (Blueprint $table) {
            $table->string('Afectado')->nullable();
            $table->string('EmisorSiniestro')->nullable();
            $table->string('NumeroSiniestro')->nullable();
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
            $table->dropColumn('Afectado');
            $table->dropColumn('EmisorSiniestro');
            $table->dropColumn('NumeroSiniestro');
        });
    }
}
