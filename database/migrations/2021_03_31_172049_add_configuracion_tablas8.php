<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas8 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'CuentasPorCobrar',
            'campos_activados' => 'Pago,Fecha,UUID,FechaPago,Abono,Cliente,NombreCliente,RfcCliente,FormaPago,NombreFormaPago,Esquema,Status,MotivoBaja,Usuario,Equipo,Periodo',
            'campos_desactivados' => 'Serie,Folio,Corte,Banco,Anotacion,Moneda,TipoCambio,EmisorRfc,EmisorNombre,LugarExpedicion,RegimenFiscal,ReceptorRfc,ReceptorNombre,NumOperacion,RfcEmisorCtaOrd,NomBancoOrdExt,CtaOrdenante,RfcEmisorCtaBen,CtaBeneficiario,TipoCadPago,CertPago,CadPago,SelloPago,Hora,TipoRelacion,NumeroCliente,NumeroFormaPago,ClaveFormaPago',
            'columnas_ordenadas' => 'Pago,Fecha,UUID,FechaPago,Abono,Cliente,NombreCliente,RfcCliente,FormaPago,NombreFormaPago,Esquema,Status,MotivoBaja,Usuario,Equipo,Periodo',
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
