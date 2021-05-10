<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas10 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Existencias',
            'campos_activados' => 'Codigo,Producto,Unidad,Ubicacion,Almacen,Existencias,Costo,CostoDeLista,Moneda,CostoDeVenta,Utilidad,SubTotal,Iva,Total,Marca,Linea,NombreMarca,NombreLinea,FechaUltimaCompra,FechaUltimaVenta,ClaveProducto,ClaveUnidad,Status',
            'campos_desactivados' => 'Precio',
            'columnas_ordenadas' => 'Codigo,Producto,Unidad,Ubicacion,Almacen,Existencias,Costo,CostoDeLista,Moneda,CostoDeVenta,Utilidad,SubTotal,Iva,Total,Marca,Linea,NombreMarca,NombreLinea,FechaUltimaCompra,FechaUltimaVenta,ClaveProducto,ClaveUnidad,Status',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
