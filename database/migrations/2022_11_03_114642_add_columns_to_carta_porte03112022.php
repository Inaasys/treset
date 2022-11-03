<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCartaPorte03112022 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CartaPorte', function (Blueprint $table) {
            $table->boolean('carreteraFederal')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('CartaPorte', function (Blueprint $table) {
            $table->$table->dropColumn('carreteraFederal');
        });
    }
}
