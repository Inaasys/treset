<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfiguracionTablas19 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'ExistenciasSucursales',
            'campos_activados' => 'Codigo,Status,Producto,Ubicacion,Almacen,Existencias,Costo,Utilidad,SubTotal,Iva,Total,totalCostoInventario,Insumo',
            'campos_desactivados' => 'Unidad,CostoDeLista,Moneda,CostoDeVenta,Marca,Linea,FechaUltimaCompra,FechaUltimaVenta,ClaveProducto,ClaveUnidad,Precio,NombreMarca,NombreLinea',
            'columnas_ordenadas' => 'Codigo,Insumo,Status,Producto,Ubicacion,Almacen,Existencias,Costo,Utilidad,SubTotal,Iva,Total,totalCostoInventario',
            'ordenar' => '',
            'usuario' => 'admin',
            'campos_busquedas' => 'Codigo,Producto',
            'primerordenamiento' => 'omitir',
            'formaprimerordenamiento' => 'DESC',
            'segundoordenamiento' => 'omitir',
            'formasegundoordenamiento' => 'DESC',
            'tercerordenamiento' => 'omitir',
            'formatercerordenamiento' => 'DESC',
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
