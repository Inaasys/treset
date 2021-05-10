<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas9 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Facturas',
            'campos_activados' => 'Factura,Serie,Folio,Fecha,Status,UUID,Esquema,Depto,Cliente,NombreCliente,RfcCliente,Remision,OrdenTrabajo,EconomicoOrdenTrabajo,Pedido,Agente,NombreAgente,Tipo,Plazo,SubTotal,Iva,Total,Abonos,Descuentos,Saldo,Equipo,Usuario,MotivoBaja,Periodo',
            'campos_desactivados' => 'Orden,Unidad,Lpa,BloquearObsoleto,Incobrable,TipoPA,Refactura,Importe,Descuento,Ieps,IvaRetencion,IsrRetencion,IepsRetencion,ImpLocRetenciones,ImpLocTraslados,Costo,Comision,Utilidad,Moneda,TipoCambio,Descripcion,Obs,CondicionesDePago,LugarExpedicion,RegimenFiscal,Confirmacion,FormaPago,MetodoPago,UsoCfdi,ResidenciaFiscal,TipoRelacion,NumRegIdTrib,EmisorRfc,EmisorNombre,ReceptorRfc,ReceptorNombre,FechaTimbrado,Hora',
            'columnas_ordenadas' => 'Factura,Serie,Folio,Fecha,Status,UUID,Esquema,Depto,Cliente,NombreCliente,RfcCliente,Remision,OrdenTrabajo,EconomicoOrdenTrabajo,Pedido,Agente,NombreAgente,Tipo,Plazo,SubTotal,Iva,Total,Abonos,Descuentos,Saldo,Equipo,Usuario,MotivoBaja,Periodo',
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
