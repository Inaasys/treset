<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToEmpresaTable14062021 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Empresa', function (Blueprint $table) {
            $table->string('Calle', 100)->nullable();
            $table->string('NoExterior', 10)->nullable();
            $table->string('NoInterior', 10)->nullable();
            $table->string('Colonia', 100)->nullable();
            $table->string('Localidad', 100)->nullable();
            $table->string('Referencia', 100)->nullable();
            $table->string('Municipio', 100)->nullable();
            $table->string('Estado', 100)->nullable();
            $table->string('Pais', 5)->nullable();
            $table->string('Telefonos', 100)->nullable();
            $table->string('Email', 100)->nullable();
            $table->string('LugarExpedicion', 5)->nullable();
            $table->string('RegimenFiscal', 5)->nullable();
            $table->string('Moneda', 5)->nullable();
            $table->string('MetodoPago', 5)->nullable();
            $table->string('UsoCfdi', 5)->nullable();
            //$table->string('ExpedidoEnCalle', 100)->nullable();
            //$table->string('ExpedidoEnNoExterior', 10)->nullable();
            //$table->string('ExpedidoEnNoInterior', 10)->nullable();
            //$table->string('ExpedidoEnColonia', 100)->nullable();
            //$table->string('ExpedidoEnLocalidad', 100)->nullable();
            //$table->string('ExpedidoEnReferencia', 100)->nullable();
            //$table->string('ExpedidoEnMunicipio', 100)->nullable();
            //$table->string('ExpedidoEnEstado', 100)->nullable();
            //$table->string('ExpedidoEnPais', 100)->nullable();
            //$table->string('ExpedidoEnCodigoPostal', 5)->nullable();
            //$table->string('PaginaWeb', 100)->nullable();
            //$table->string('Revision', 100)->nullable();
            //$table->string('Pagos', 100)->nullable();
            //$table->string('RealizarPago', 255)->nullable();
            //$table->string('Obsoletos', 10)->nullable();
            //$table->string('Respaldar')->nullable();
            $table->decimal('Impuesto', 30, 6)->nullable();
            $table->string('Aplicar_ImpFed_Traslados', 1)->nullable();
            $table->string('Aplicar_ImpFed_Retenciones', 1)->nullable();
            $table->string('Aplicar_ImpLoc_Traslados', 1)->nullable();
            $table->string('Aplicar_ImpLoc_Retenciones', 1)->nullable();
            //$table->string('Tallas', 1)->nullable();
            $table->string('Paquete', 255)->nullable();
            $table->string('En_Compras_Modificar_Costos_y_Precios', 1)->nullable();
            $table->string('En_Compras_Modificar_Al_Ultimo_Costo', 1)->nullable();
            $table->string('En_Compras_Modificar_Al_Costo_Mas_Alto', 1)->nullable();
            $table->string('En_Compras_Modificar_Al_Costo_Promedio', 1)->nullable();
            //$table->string('En_Compras_Modificar_El_PrecioNeto', 1)->nullable();
            //$table->string('En_Compras_Modificar_Tabla_De_Precios', 1)->nullable();
            $table->string('Aplicar_Utilidad_Financiera', 1)->nullable();
            $table->string('Aplicar_Utilidad_Aritmetica', 1)->nullable();
            $table->string('Visualizar_Logotipo_En_Documentos', 1)->nullable();
            $table->string('Utilizar_Consecutivo_De_Codigos_En_Productos', 1)->nullable();
            $table->string('Aplicar_Utilidades_De_Marcas_En_Productos', 1)->nullable();
            $table->string('Guardar_Documentos_Sin_Existencias', 1)->nullable();
            //$table->string('Mostrar_Existencias_De_Productos_En_Ayudas', 1)->nullable();
            $table->string('Aplicar_Nota_De_Credito_Por_Factura', 1)->nullable();
            //$table->string('Aplicar_Pedimentos_en_Codigos', 1)->nullable();
            $table->string('Obligar_UUID_En_Documentos', 1)->nullable();
            $table->string('Obligar_Contrarecibos_En_Pagos', 1)->nullable();
            $table->string('Guardar_Documentos_Con_Utilidad', 1)->nullable();
            $table->string('Liberar_Documentos_Vencidos', 1)->nullable();
            //$table->string('Solicitar_Agente_En_Pv', 1)->nullable();
            $table->string('Guardar_Comprobante_UUID', 1)->nullable();
            //$table->string('Partes', 1)->nullable();
            //$table->string('Ingresar_Complemento_Construccion', 1)->nullable();
            //$table->string('Rollos', 1)->nullable();
            //$table->string('PreciosNetos', 1)->nullable();
            $table->string('Utilizar_Mayusculas', 1)->nullable();
            $table->string('Unidad', 5)->nullable();
            $table->string('Conversion', 1)->nullable();
            //$table->string('Precio_Default_En_Pv')->nullable();
            //$table->string('Tipos_Precios_En_Pv', 255)->nullable();
            //$table->string('Lpa', 20)->nullable();
            //$table->string('Integrar_Otra_Base_Para_Remisiones')->nullable();
            //$table->string('dbc_Otra_Base', 255)->nullable();
            //$table->string('ServidorSMTP', 50)->nullable();
            //$table->string('PuertoSMTP', 10)->nullable();
            //$table->string('Autenticacion', 1)->nullable();
            //$table->string('De', 100)->nullable();
            //$table->string('Password', 50)->nullable();
            //$table->string('PacUsuario', 100)->nullable();
            //$table->string('PacPassword', 50)->nullable();
            //$table->string('PacProduccion', 1)->nullable();
            //$table->string('CuentaBancos', 25)->nullable();
            //$table->string('CuentaIvaTrasladado', 25)->nullable();
            //$table->string('CuentaIvaAcreditable', 25)->nullable();
            //$table->string('CuentaVentaRefacciones', 25)->nullable();
            //$table->string('CuentaVentaServicios', 25)->nullable();
            //$table->string('CuentaVentaComodin', 25)->nullable();
            //$table->string('CuentaNotaCredito', 25)->nullable();
            //$table->string('CuentaCompraRefacciones1', 25)->nullable();
            //$table->string('CuentaCompraRefacciones2', 25)->nullable();
            //$table->string('CuentaNotaCreditoProveedor', 25)->nullable();
            //$table->string('CuentaRetencionIVA', 25)->nullable();
            //$table->string('ProveedorVolvo')->nullable();
            //$table->string('ProveedorCummins')->nullable();
            //$table->string('RemitenteServicio', 255)->nullable();
            //$table->string('RemitentePassword', 255)->nullable();
            //$table->string('Digitos')->nullable();
            //$table->string('Configurar', 255)->nullable();
            //$table->string('Licencia', 255)->nullable();
            //$table->string('ClaveCiec', 50)->nullable();
            //$table->string('PreciosLibre', 1)->nullable();
            $table->string('Modificar_Registros_Cualquier_Fecha', 1)->nullable();
            //$table->string('ArchivoHerramienta', 255)->nullable();
            $table->string('Periodo_Inicial_Modulos', 4)->nullable();
            $table->string('Numero_Decimales', 2)->nullable();
            $table->string('Numero_Decimales_En_Documentos', 2)->nullable();
            $table->string('Mayusculas_Sistema', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Empresa', function (Blueprint $table) {
            //
        });
    }
}
