<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('menus')->insert(array(
            'name' => 'menucatalogoclientes',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogoagentes',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogoproveedores',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogoalmacenes',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogomarcas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogolineas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogoproductos',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogobancos',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogotecnicos',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogoservicios',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogovines',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogoencuentascrearencuesta',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menucatalogofoliosfiscalesfoliosfacturas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menuregistrosordenescompra',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistroscompras',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistroscontrarecibos',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistroscotizaciones',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrospedidos',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosremisiones',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosfacturas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosproduccion',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosdestinar',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrostraspasos',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosrequisiciones',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosordenestrabajo',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosplaneaciontaller',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistroscuentasxcobrar',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistroscuentasxpagar',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosnotascreditoclientes',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosnotascreditoproveedores',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosciclicos',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));
        DB::table('menus')->insert(array(
            'name' => 'menuregistrosajusteinventario',
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
