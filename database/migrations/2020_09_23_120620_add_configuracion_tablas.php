<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfiguracionTablas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Productos',
            'campos_activados' => 'Codigo,ClaveProducto,ClaveUnidad,Producto,Unidad,Ubicacion,Costo,CostoDeLista,Moneda,CostoDeVenta,Utilidad,SubTotal,Iva,Total,Marca,Linea,Status,NombreMarca,NombreLinea,Existencias',
            'campos_desactivados' => 'Supercedido,Grupo,Precio,Impuesto,TasaIeps,Venta,Insumo,FechaUltimaCompra,FechaUltimaVenta,UltimoCosto,UltimaVenta,NumeroMarca,Utilidad1Marca,Utilidad2Marca,Utilidad3Marca,Utilidad4Marca,Utilidad5Marca,NumeroLinea',
            'columnas_ordenadas' => 'Codigo,ClaveProducto,ClaveUnidad,Producto,Unidad,Ubicacion,Costo,CostoDeLista,Moneda,CostoDeVenta,Utilidad,SubTotal,Iva,Total,Marca,Linea,Status,NombreMarca,NombreLinea,Existencias',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Clientes',
            'campos_activados' => 'Numero,Status,Nombre,NumeroAgente,NombreAgente',
            'campos_desactivados' => 'Rfc,Municipio,Bloquear,FacturarAlCosto,Plazo,Credito,Saldo,FormaPago,Email1,Telefonos,Agente,Calle,noExterior,noInterior,Colonia,Localidad,Referencia,Estado,Pais,CodigoPostal,MetodoPago,UsoCfdi,DireccionAgente,ColoniaAgente,CiudadAgente,CpAgente,RfcAgente,ContactoAgente,TelefonosAgente,EmailAgente,CuentaAgente,ComisionAgente,AnotiacionesAgente',
            'columnas_ordenadas' => 'Numero,Status,Nombre,NumeroAgente,NombreAgente',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Proveedores',
            'campos_activados' => 'Numero,Status,Rfc,Nombre,CodigoPostal,Email1,Plazo',
            'campos_desactivados' => 'Telefonos',
            'columnas_ordenadas' => 'Numero,Status,Rfc,Nombre,CodigoPostal,Email1,Plazo',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'OrdenesDeCompra',
            'campos_activados' => 'Orden,Status,Proveedor,Fecha,AutorizadoPor,AutorizadoFecha,Tipo,Almacen,SubTotal,Iva,Total,Equipo,Usuario,Periodo,NombreProveedor',
            'campos_desactivados' => 'Folio,Serie,Plazo,Referencia,Importe,Descuento,Obs,MotivoBaja,NumeroProveedor,RfcProveedor,CodigoPostalProveedor,PlazoProveedor,TelefonosProveedor,Email1Proveedor,NumeroAlmacen,NombreAlmacen',
            'columnas_ordenadas' => 'Orden,Status,Proveedor,Fecha,AutorizadoPor,AutorizadoFecha,Tipo,Almacen,SubTotal,Iva,Total,Equipo,Usuario,Periodo,NombreProveedor',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'Compras',
            'campos_activados' => 'Compra,Status,Proveedor,Plazo,Fecha,FechaEmitida,Remision,Factura,Tipo,Almacen,Movimiento,UUID,Orden,SubTotal,Iva,Total,Abonos,Descuentos,Saldo,TipoCambio,Obs,Equipo,Usuario,Periodo,NombreProveedor,NombreAlmacen',
            'campos_desactivados' => 'Folio,Serie,MotivoBaja,ReceptorNombre,ReceptorRfc,EmisorNombre,EmisorRfc,FechaTimbrado,Moneda,ImpLocTraslados,ImpLocRetenciones,IepsRetencion,IsrRetencion,IvaRetencion,Ieps,Descuento,Importe,BloquearObsoleto,Departamento,NumeroProveedor,RfcProveedor,CodigoPostalProveedor,PlazoProveedor,TelefonosProveedor,Email1Proveedor,NumeroAlmacen',
            'columnas_ordenadas' => 'Compra,Status,Proveedor,Plazo,Fecha,FechaEmitida,Remision,Factura,Tipo,Almacen,Movimiento,UUID,Orden,SubTotal,Iva,Total,Abonos,Descuentos,Saldo,TipoCambio,Obs,Equipo,Usuario,Periodo,NombreProveedor,NombreAlmacen',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'ContraRecibos',
            'campos_activados' => 'ContraRecibo,Status,Fecha,Proveedor,Total,Obs,Periodo,NombreProveedor',
            'campos_desactivados' => 'Serie,Folio,Facturas,MotivoBaja,Equipo,Usuario,NumeroProveedor,RfcProveedor,CodigoPostalProveedor,PlazoProveedor,TelefonosProveedor,Email1Proveedor',
            'columnas_ordenadas' => 'ContraRecibo,Status,Fecha,Proveedor,Total,Obs,Periodo,NombreProveedor',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'OrdenesDeTrabajo',
            'campos_activados' => 'Orden,Status,Caso,Fecha,Tipo,Unidad,Cliente,Total,Vin,Pedido,Marca,Economico,Placas,Año,Kilometros,Reclamo,Motor,MotivoBaja,Usuario,Equipo,Periodo,NombreCliente',
            'campos_desactivados' => 'Folio,Serie,DelCliente,Agente,Plazo,Entrega,Laminado,ServicioEnAgencia,RetrabajoOrden,Impuesto,Importe,Descuento,SubTotal,Iva,Facturado,Costo,Comision,Utilidad,Operador,OperadorCelular,Modelo,Color,Combustible,Bahia,Forma,ObsOrden,ObsUnidad,Campaña,Falla,Causa,Correccion,Rodar,Terminada,Facturada,HoraEntrada,HoraEntrega,HorasReales,Promocion,TipoServicio,KmProximoServicio,FechaRecordatorio,FechaIngresoUnidad,FechaAsignacionUnidad,FechaTerminoUnidad,EstadoServicio,Refactura,NumeroCliente,RfcCliente,CalleCliente,noExteriorCliente,ColoniaCliente,LocalidadCliente,MunicipioCliente,EstadoCliente,PaisCliente,CodigoPostalCliente,ReferenciaCliente,TelefonosCliente,Email1Cliente,AnotacionesCliente,FormaPagoCliente,MetodoPagoCliente,UsoCfdiCliente',
            'columnas_ordenadas' => 'Orden,Status,Caso,Fecha,Tipo,Unidad,Cliente,Total,Vin,Pedido,Marca,Economico,Placas,Año,Kilometros,Reclamo,Motor,MotivoBaja,Usuario,Equipo,Periodo,NombreCliente',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'CuentasPorPagar',
            'campos_activados' => 'Pago,Status,Fecha,Proveedor,Transferencia,Abono,Periodo,Banco,NombreProveedor',
            'campos_desactivados' => 'MotivoBaja,Folio,Serie,Cheque,Beneficiario,CuentaDeposito,Anotacion,Equipo,Usuario,NumeroBanco,NombreBanco,CuentaBanco,NumeroProveedor,RfcProveedor,CodigoPostalProveedor,PlazoProveedor,TelefonosProveedor,Email1Proveedor',
            'columnas_ordenadas' => 'Pago,Status,Fecha,Proveedor,Transferencia,Abono,Periodo,Banco,NombreProveedor',
            'ordenar' => '',
            'usuario' => 'admin',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ));

        DB::table('configuracion_tablas')->insert(array(
            'tabla' => 'NotasCreditoProveedor',
            'campos_activados' => 'Nota,Status,Proveedor,Fecha,NotaProveedor,Almacen,UUID,SubTotal,Iva,Total,Obs,MotivoBaja,Equipo,Usuario,Periodo,NombreProveedor',
            'campos_desactivados' => 'Folio,Serie,Importe,Descuento,Ieps,IvaRetencion,IsrRetencion,IepsRetencion,ImpLocRetenciones,ImpLocTraslados,Moneda,TipoCambio,FechaEmitida,EmisorRfc,EmisorNombre,ReceptorRfc,ReceptorNombre,NumeroProveedor,RfcProveedor,CodigoPostalProveedor,PlazoProveedor,TelefonosProveedor,Email1Proveedor',
            'columnas_ordenadas' => 'Nota,Status,Proveedor,Fecha,NotaProveedor,Almacen,UUID,SubTotal,Iva,Total,Obs,MotivoBaja,Equipo,Usuario,Periodo,NombreProveedor',
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
