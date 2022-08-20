<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToEmpresaTable20082022 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Empresa', function (Blueprint $table) {
            $table->string('ligarOTaCompra', 2)->default('NO')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Empresa', function (Blueprint $table) {
            $table->dropColumn('ligarOTaCompra');
        });
    }
}
