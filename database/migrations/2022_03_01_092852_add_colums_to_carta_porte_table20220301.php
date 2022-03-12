<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumsToCartaPorteTable20220301 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CartaPorte', function (Blueprint $table) {
            $table->string('Esquema', 10)->nullable();
            $table->integer('Agente')->nullable();
            $table->string('Tipo', 20)->nullable();
            $table->string('Unidad', 20)->nullable();
            $table->decimal('Importe', 30, 6)->nullable();
            $table->decimal('Descuento', 30, 6)->nullable();
            $table->decimal('Ieps', 30, 6)->nullable();
            $table->decimal('SubTotal', 30, 6)->nullable();
            $table->decimal('Iva', 30, 6)->nullable();
            $table->decimal('IvaRetencion', 30, 6)->nullable();
            $table->decimal('IsrRetencion', 30, 6)->nullable();
            $table->decimal('IepsRetencion', 30, 6)->nullable();
            $table->decimal('ImpLocRetenciones', 30, 6)->nullable();
            $table->decimal('ImpLocTraslados', 30, 6)->nullable();
            $table->decimal('Total', 30, 6)->nullable();
            $table->decimal('Costo', 30, 6)->nullable();
            $table->decimal('Comision', 30, 6)->nullable();
            $table->decimal('Utilidad', 30, 6)->nullable();
            $table->decimal('Abonos', 30, 6)->nullable();
            $table->decimal('Descuentos', 30, 6)->nullable();
            $table->decimal('Saldo', 30, 6)->nullable();
            $table->string('Moneda', 5)->nullable();
            $table->decimal('TipoCambio', 30, 6)->nullable();
            $table->string('Descripcion', 255)->nullable();
            $table->string('Obs', 255)->nullable();
            $table->string('MotivoBaja', 255)->nullable();
            $table->string('Equipo', 20)->nullable();
            $table->string('Usuario', 20)->nullable();
            $table->string('CondicionesDePago', 50)->nullable();
            $table->string('LugarExpedicion', 5)->nullable();
            $table->string('RegimenFiscal', 5)->nullable();
            $table->string('FormaPago', 5)->nullable();
            $table->string('MetodoPago', 5)->nullable();
            $table->string('UsoCfdi', 5)->nullable();
            $table->string('ResidenciaFiscal', 5)->nullable();
            $table->string('TipoRelacion', 5)->nullable();
            $table->string('NumRegIdTrib', 40)->nullable();
            $table->string('FechaTimbrado', 50)->nullable();
            $table->string('UUID', 50)->nullable();
            $table->dateTime('Hora')->nullable();
            $table->string('TransporteInternacional', 2)->nullable();
            $table->decimal('TotalDistanciaRecorrida', 30, 6)->nullable();
            $table->integer('TotalMercancias')->nullable();
            $table->string('RfcRemitente', 20)->nullable();
            $table->string('NombreRemitente', 150)->nullable();
            $table->dateTime('FechaSalida')->nullable();
            $table->string('CalleRemitente', 100)->nullable();
            $table->string('NoExteriorRemitente', 10)->nullable();
            $table->string('NoInteriorRemitente', 10)->nullable();
            $table->string('ColoniaRemitente', 100)->nullable();
            $table->string('LocalidadRemitente', 100)->nullable();
            $table->string('ReferenciaRemitente', 100)->nullable();
            $table->string('MunicipioRemitente', 100)->nullable();
            $table->string('EstadoRemitente', 5)->nullable();
            $table->string('PaisRemitente', 5)->nullable();
            $table->string('CodigoPostalRemitente', 5)->nullable();
            $table->string('RfcDestinatario', 20)->nullable();
            $table->string('NombreDestinatario', 150)->nullable();
            $table->dateTime('FechaLlegada')->nullable();
            $table->decimal('DistanciaRecorrida', 30, 6)->nullable();
            $table->string('CalleDestinatario', 100)->nullable();
            $table->string('NoExteriorDestinatario', 10)->nullable();
            $table->string('NoInteriorDestinatario', 10)->nullable();
            $table->string('ColoniaDestinatario', 100)->nullable();
            $table->string('LocalidadDestinatario', 100)->nullable();
            $table->string('ReferenciaDestinatario', 100)->nullable();
            $table->string('MunicipioDestinatario', 100)->nullable();
            $table->string('EstadoDestinatario', 5)->nullable();
            $table->string('PaisDestinatario', 5)->nullable();
            $table->string('CodigoPostalDestinatario', 5)->nullable();
            $table->string('ClaveTransporte', 5)->nullable();     
            $table->string('RfcOperador', 20)->nullable();  
            $table->string('NombreOperador', 150)->nullable();  
            $table->string('NumeroLicencia', 20)->nullable();  
            $table->string('CalleOperador', 100)->nullable();  
            $table->string('NoExteriorOperador', 10)->nullable();  
            $table->string('NoInteriorOperador', 10)->nullable();  
            $table->string('ColoniaOperador', 100)->nullable();  
            $table->string('LocalidadOperador', 100)->nullable();  
            $table->string('ReferenciaOperador', 100)->nullable();  
            $table->string('MunicipioOperador', 100)->nullable();  
            $table->string('EstadoOperador', 5)->nullable();  
            $table->string('PaisOperador', 5)->nullable();  
            $table->string('CodigoPostalOperador', 5)->nullable();  
            $table->string('PermisoSCT', 5)->nullable();  
            $table->string('NumeroPermisoSCT', 50)->nullable();  
            $table->string('NombreAsegurado', 150)->nullable();   
            $table->string('NumeroPolizaSeguro', 30)->nullable();   
            $table->string('ConfiguracionVehicular', 10)->nullable();   
            $table->string('PlacaVehiculoMotor', 7)->nullable();   
            $table->string('AnoModeloVehiculoMotor', 4)->nullable();   
            $table->string('SubTipoRemolque', 10)->nullable();   
            $table->string('PlacaRemolque', 7)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('CartaPorte', function (Blueprint $table) {
            //
        });
    }
}
