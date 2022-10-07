<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCartaPorteDetallesTable15072022 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CartaPorte Detalles', function (Blueprint $table) {
            $table->string('CveMaterialPeligroso')->nullable();
            $table->string('Embalaje')->nullable();
            $table->longText('DescripEmbalaje')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('CartaPorte Detalles', function (Blueprint $table) {
            $table->dropColumn('CveMaterialPeligroso');
            $table->dropColumn('Embalaje');
            $table->dropColumn('DescripEmbalaje');
        });
    }
}
