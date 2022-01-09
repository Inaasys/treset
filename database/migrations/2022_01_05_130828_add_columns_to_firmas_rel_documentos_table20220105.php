<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToFirmasRelDocumentosTable20220105 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('firmas_rel_documentos', function (Blueprint $table) {
            $table->string('Status', 255)->nullable();
            $table->text('MotivoBaja')->nullable();
            $table->string('Equipo', 255)->nullable();
            $table->string('Usuario', 255)->nullable();
            $table->string('Periodo', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('firmas_rel_documentos', function (Blueprint $table) {
            //
        });
    }
}
