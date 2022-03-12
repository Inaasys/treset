<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfiguracionTablas20 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'CartaPorte',
            'campos_activados' => 'CartaPorte,Fecha,Esquema,Unidad,Importe,Descuento,SubTotal,Iva,Total,Moneda,TipoCambio,Descripcion,Obs,Status,MotivoBaja,Equipo,Usuario,CondicionesDePago,LugarExpedicion,RegimenFiscal,FormaPago,MetodoPago,UsoCfdi,ResidenciaFiscal,TipoRelacion,FechaTimbrado,UUID,Hora,Periodo,TransporteInternacional,TotalDistanciaRecorrida,TotalMercancias,RfcRemitente,NombreRemitente,FechaSalida,CodigoPostalRemitente,RfcDestinatario,NombreDestinatario,FechaLlegada,DistanciaRecorrida,CodigoPostalDestinatario,ClaveTransporte,RfcOperador,NombreOperador,CodigoPostalOperador,PermisoSCT,NumeroPermisoSCT,NombreAsegurado,NumeroPolizaSeguro,ConfiguracionVehicular,PlacaVehiculoMotor,AnoModeloVehiculoMotor',
            'campos_desactivados' => 'Serie,Folio,Agente,Tipo,Ieps,IvaRetencion,IsrRetencion,IepsRetencion,ImpLocRetenciones,ImpLocTraslados,Costo,Comision,Utilidad,Abonos,Descuentos,Saldo,NumRegIdTrib,CalleRemitente,NoExteriorRemitente,NoInteriorRemitente,ColoniaRemitente,LocalidadRemitente,ReferenciaRemitente,MunicipioRemitente,EstadoRemitente,PaisRemitente,CalleDestinatario,NoExteriorDestinatario,NoInteriorDestinatario,ColoniaDestinatario,LocalidadDestinatario,ReferenciaDestinatario,MunicipioDestinatario,EstadoDestinatario,NumeroLicencia,CalleOperador,NoExteriorOperador,NoInteriorOperador,ColoniaOperador,LocalidadOperador,ReferenciaOperador,MunicipioOperador,EstadoOperador,PaisOperador,PaisDestinatario,SubTipoRemolque,PlacaRemolque',
            'columnas_ordenadas' => 'CartaPorte,Fecha,Esquema,Unidad,Importe,Descuento,SubTotal,Iva,Total,Moneda,TipoCambio,Descripcion,Obs,Status,MotivoBaja,Equipo,Usuario,CondicionesDePago,LugarExpedicion,RegimenFiscal,FormaPago,MetodoPago,UsoCfdi,ResidenciaFiscal,TipoRelacion,FechaTimbrado,UUID,Hora,Periodo,TransporteInternacional,TotalDistanciaRecorrida,TotalMercancias,RfcRemitente,NombreRemitente,FechaSalida,CodigoPostalRemitente,RfcDestinatario,NombreDestinatario,FechaLlegada,DistanciaRecorrida,CodigoPostalDestinatario,ClaveTransporte,RfcOperador,NombreOperador,CodigoPostalOperador,PermisoSCT,NumeroPermisoSCT,NombreAsegurado,NumeroPolizaSeguro,ConfiguracionVehicular,PlacaVehiculoMotor,AnoModeloVehiculoMotor',
            'ordenar' => '',
            'usuario' => 'admin',
            'campos_busquedas' => 'CartaPorte,Status,UUID,NombreDestinatario,RfcDestinatario',
            'primerordenamiento' => 'Fecha',
            'formaprimerordenamiento' => 'DESC',
            'segundoordenamiento' => 'Serie',
            'formasegundoordenamiento' => 'ASC',
            'tercerordenamiento' => 'Folio',
            'formatercerordenamiento' => 'DESC',
            'IdUsuario' => 0,
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
