<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToEmpresaTable12062021 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Empresa', function (Blueprint $table) {
            $table->string('background_navbar', 255)->default('bg-grey')->nullable();
            $table->string('background_forms_and_modals', 255)->default('bg-red')->nullable();
            $table->string('background_tables', 255)->default('bg-blue-grey')->nullable();
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
