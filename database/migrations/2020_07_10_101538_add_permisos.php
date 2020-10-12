<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermisos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.clientes.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.clientes.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.clientes.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.agentes.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.agentes.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.agentes.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.proveedores.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.proveedores.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.proveedores.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.ordenescompra.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.ordenescompra.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.ordenescompra.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.compras.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.compras.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.compras.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.contrarecibos.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.contrarecibos.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.contrarecibos.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.almacenes.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.almacenes.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.almacenes.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.marcas.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.marcas.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.marcas.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.lineas.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.lineas.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.lineas.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.productos.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.productos.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.productos.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.bancos.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.bancos.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.bancos.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.tecnicos.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.tecnicos.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.tecnicos.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.servicios.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.servicios.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.servicios.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.vines.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.vines.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.vines.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.encuestas.crear.encuentas.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.encuestas.crear.encuentas.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.encuestas.crear.encuentas.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'catalogos.folios.fiscales.folios.facturas.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.folios.fiscales.folios.facturas.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'catalogos.folios.fiscales.folios.facturas.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));


        DB::table('permisos')->insert(array(
            'name' => 'registros.cotizaciones.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.cotizaciones.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.cotizaciones.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.pedidos.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.pedidos.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.pedidos.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.remisiones.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.remisiones.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.remisiones.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.facturas.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.facturas.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.facturas.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.produccion.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.produccion.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.produccion.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.destinar.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.destinar.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.destinar.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.traspasos.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.traspasos.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.traspasos.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.requisiciones.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.requisiciones.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.requisiciones.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.ordenes.trabajo.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.ordenes.trabajo.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.ordenes.trabajo.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.planeacion.taller.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.planeacion.taller.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.planeacion.taller.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.cuentas.x.cobrar.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.cuentas.x.cobrar.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.cuentas.x.cobrar.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.cuentas.x.pagar.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.cuentas.x.pagar.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.cuentas.x.pagar.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.notas.credito.clientes.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.notas.credito.clientes.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.notas.credito.clientes.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.notas.credito.proveedores.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.notas.credito.proveedores.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.notas.credito.proveedores.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.ciclicos.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.ciclicos.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.ciclicos.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.ajustes.inventario.altas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.ajustes.inventario.bajas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('permisos')->insert(array(
            'name' => 'registros.ajustes.inventario.cambios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('permisos')->insert(array(
            'name' => 'registros.ordenescompra.autorizar',
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
