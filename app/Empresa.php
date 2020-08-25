<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    public $timestamps = false;
    protected $table = 'Empresa';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Empresa',
        'Nombre', 
        'Rfc',
        'Ini',
        'Calle',
        'noExterior',
        'noInterior',
        'Colonia',
        'Localidad',
        'Referencia',
        'Municipio',
        'Estado',
        'Pais',
        'Telefonos',
        'Email',
        'LugarExpedicion',
        'RegimenFiscal',
        'Moneda',
        'MetodoPago',
        'UsoCfdi',
        'ExpedidoEnCalle',
        'ExpedidoEnNoExterior',
        'ExpedidoEnNoInterior',
        'ExpedidoEnColonia',
        'ExpedidoEnLocalidad',
        'ExpedidoEnReferencia',
        'ExpedidoEnMunicipio',
        'ExpedidoEnEstado',
        'ExpedidoEnPais',
        'ExpedidoEnCodigoPostal',
        'PaginaWeb',
        'Revision',
        'Pagos',
        'RealizarPago',
        'Obsoletos',
        'Respaldar',
        'Impuesto',
        'Aplicar_ImpFed_Traslados',
        'Aplicar_ImpFed_Retenciones',
        'Aplicar_ImpLoc_Traslados',
        'Aplicar_ImpLoc_Retenciones',
        'Tallas',
        'Paquete',
        'En_Compras_Modificar_Costos_y_Precios',
        'En_Compras_Modificar_Al_Ultimo_Costo',
        'En_Compras_Modificar_Al_Costo_Mas_Alto',
        'En_Compras_Modificar_Al_Costo_Promedio',
        'En_Compras_Modificar_El_PrecioNeto',
        'En_Compras_Modificar_Tabla_De_Precios',
        'Aplicar_Utilidad_Financiera',
        'Aplicar_Utilidad_Aritmetica',
        'Visualizar_Logotipo_En_Documentos',
        'Utilizar_Consecutivo_De_Codigos_En_Productos',
        'Aplicar_Utilidades_De_Marcas_En_Productos',
        'Guardar_Documentos_Sin_Existencias',
        'Mostrar_Existencias_De_Productos_En_Ayudas',
        'Aplicar_Nota_De_Credito_Por_Factura',
        'Aplicar_Pedimentos_en_Codigos',
        'Obligar_UUID_En_Documentos',
        'Obligar_Contrarecibos_En_Pagos',
        'Guardar_Documentos_Con_Utilidad',
        'Liberar_Documentos_Vencidos',
        'Solicitar_Agente_En_Pv',
        'Guardar_Comprobante_UUID',
        'Partes',
        'Ingresar_Complemento_Construccion',
        'Rollos',
        'PreciosNetos',
        'Utilizar_Mayusculas',
        'Unidad',
        'Conversion',
        'Precio_Default_En_Pv',
        'Tipos_Precios_En_Pv',
        'Lpa',
        'Integrar_Otra_Base_Para_Remisiones',
        'dbc_Otra_Base',
        'Exe',
        'ServidorSMTP',
        'PuertoSMTP',
        'Autenticacion',
        'De',
        'Password',
        'PacUsuario',
        'PacPassword',
        'PacProduccion',
        'Logotipo',
        'Imagen1',
        'Imagen2',
        'Imagen3',
        'Imagen4',
        'Imagen5',
        'CuentaBancos',
        'CuentaIvaTrasladado',
        'CuentaIvaAcreditable',
        'CuentaVentaRefacciones',
        'CuentaVentaServicios',
        'CuentaVentaComodin',
        'CuentaNotaCredito',
        'CuentaCompraRefacciones1',
        'CuentaCompraRefacciones2',
        'CuentaNotaCreditoProveedor',
        'CuentaRetencionIVA',
        'ProveedorVolvo',
        'ProveedorCummins',
        'RemitenteServicio',
        'RemitentePassword',
        'Digitos',
        'Configurar',
        'Licencia',
        'ClaveCiec',
        'Sistema',
        'Logo'
    ];
}
