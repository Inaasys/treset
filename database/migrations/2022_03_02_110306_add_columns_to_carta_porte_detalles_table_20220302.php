<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCartaPorteDetallesTable20220302 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CartaPorte Detalles', function (Blueprint $table) {
            $table->string('ClaveUnidad', 5)->nullable();
            $table->string('ClaveProducto', 20)->nullable();
            $table->string('MaterialPeligroso', 5)->nullable();
            $table->decimal('PesoEnKilogramos', 30, 6)->nullable();
            $table->string('Moneda', 5)->nullable();
            $table->decimal('PrecioNeto', 30, 6)->nullable();
            $table->decimal('Importe', 30, 6)->nullable();
            $table->decimal('Dcto', 30, 6)->nullable();
            $table->decimal('Descuento', 30, 6)->nullable();
            $table->decimal('ImporteDescuento', 30, 6)->nullable();
            $table->decimal('Ieps', 30, 6)->nullable();
            $table->decimal('SubTotal', 30, 6)->nullable();
            $table->decimal('Impuesto', 30, 6)->nullable();
            $table->decimal('Iva', 30, 6)->nullable();
            $table->decimal('IvaRetencion', 30, 6)->nullable();
            $table->decimal('IsrRetencion', 30, 6)->nullable();
            $table->decimal('IepsRetencion', 30, 6)->nullable();
            $table->decimal('Costo', 30, 6)->nullable();
            $table->decimal('CostoTotal', 30, 6)->nullable();
            $table->decimal('Com', 30, 6)->nullable();
            $table->decimal('Comision', 30, 6)->nullable();
            $table->decimal('Utilidad', 30, 6)->nullable();
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
            //
        });
    }
}
