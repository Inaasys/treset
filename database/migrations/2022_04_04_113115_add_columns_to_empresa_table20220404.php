<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToEmpresaTable20220404 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Empresa', function (Blueprint $table) {
            $table->text('ModificarCreditoDeClientes')->nullable();
            $table->text('ModificarCostoyVentaDeServicios')->nullable();
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
            //
        });
    }
}
