<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Remisiones',
            'campos_activados' => 'Remision,Serie,Folio,Fecha,Status,Cliente,NombreCliente,Pedido,Os,Eq,Rq,Agente,NombreAgente,Tipo,Almacen,NombreAlmacen,SubTotal,Iva,Total,Equipo,Usuario,MotivoBaja,Periodo',
            'campos_desactivados' => 'Plazo,Unidad,Solicita,Referencia,Destino,TeleMarketing,Importe,Descuento,Costo,Comision,Utilidad,FormaPago,Obs,TipoCambio,Hora,Facturada,Corte,SuPago,EnEfectivo,EnTarjetas,EnVales,EnCheque,Lugar,Personas',
            'columnas_ordenadas' => 'Remision,Serie,Folio,Fecha,Status,Cliente,NombreCliente,Pedido,Os,Eq,Rq,Agente,NombreAgente,Tipo,Almacen,NombreAlmacen,SubTotal,Iva,Total,Equipo,Usuario,MotivoBaja,Periodo',
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
