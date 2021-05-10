<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas7 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'NotasCreditoCliente',
            'campos_activados' => 'Nota,Fecha,Almacen,Status,UUID,Esquema,Cliente,NombreCliente,RfcCliente,SubTotal,Iva,Total,Equipo,Usuario,MotivoBaja,Periodo',
            'campos_desactivados' => 'Serie,Folio,Importe,Descuento,Ieps,IvaRetencion,IsrRetencion,IepsRetencion,ImpLocRetenciones,ImpLocTraslados,Moneda,TipoCambio,Descripcion,Obs,CondicionesDePago,LugarExpedicion,RegimenFiscal,TipoRelacion,Confirmacion,FormaPago,MetodoPago,UsoCfdi,ResidenciaFiscal,NumRegIdTrib,EmisorRfc,EmisorNombre,ReceptorRfc,ReceptorNombre,FechaTimbrado,Hora,NumeroCliente',
            'columnas_ordenadas' => 'Nota,Fecha,Almacen,Status,UUID,Esquema,Cliente,NombreCliente,RfcCliente,SubTotal,Iva,Total,Equipo,Usuario,MotivoBaja,Periodo',
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
