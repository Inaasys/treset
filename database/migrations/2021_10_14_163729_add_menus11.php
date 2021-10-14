<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMenus11 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('menus')->insert(array(
            'name' => 'menureporterelacioncontrarecibos',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacioncotizaciones',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacionremisiones',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacionfacturasventasclientes',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacionfacturasventasagentes',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacionfacturasventasmarcas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacionproduccion',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacioncomprobantes',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacionrequisiciones',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacioncxc',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacioncxp',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacionnotasclientes',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporterelacionnotasproveedores',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureportecostoinventario',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureportecostoinventarioparametros',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporteproductossincomprasyventas',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureportemovimientosalinventario',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporteultimafechaycostosproductoscomprados',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporteultimafechaypreciosproductosfacturados',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureporteinventariomaximosyminimos',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureportebitacorasdocumentosyclientes',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('menus')->insert(array(
            'name' => 'menureportepermisosusuarios',
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
