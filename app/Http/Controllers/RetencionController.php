<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PlantillasFacturasExport;
use App\Imports\FacturasImport;
use App\Exports\FacturasExport;
use App\Remision;
use App\RemisionDetalle;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Factura;
use App\FacturaDetalle;
use App\FacturaDocumento;
use App\Cliente;
use App\Almacen;
use App\Agente;
use App\Producto;
use App\Servicio;
use App\Pais;
use App\Estado;
use App\Municipio;
use App\CodigoPostal;
use App\FormaPago;
use App\MetodoPago;
use App\UsoCFDI;
use App\c_TipoRelacion;
use App\c_RegimenFiscal;
use App\BitacoraDocumento;
Use App\Existencia;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Comprobante;
use App\Configuracion_Tabla;
use App\VistaFactura;
use App\VistaObtenerExistenciaProducto;
use App\FolioComprobanteFactura;
use App\TipoOrdenCompra;
use App\TipoUnidad;
use App\CuentaXCobrar;
use App\CuentaXCobrarDetalle;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\NotaClienteDocumento;
use App\User_Rel_Serie;
use Config;
use Mail;
use Facturapi\Facturapi;
use Storage;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use ZipArchive;
use File;

class RetencionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //API FACTURAPI 
        $this->facturapi = new Facturapi( config('app.keyfacturapi') ); //

    }
    
    public function retenciones_crearretencion(){
        //crear retencion
        $invoice = array(
            "customer" => array(
                "legal_name" => "DULCE MARIA MANJARREZ VILCHIS",
                "tax_id" => "MAVD810511PP7",
                "curp" => "MAVD810511MMCNLL04",
                /*
                //se debe agregar para version 2.0 de facturapi que integrado el timbrado de cfdi 4.0
                "tax_system" => "601",
                "address" => 
                    array(
                        "zip" => "52100",
                    )
                //fin cfdi 4.0
                */
            ),
            "cve_retenc" => "14",
            "folio_int" => "2",
            "periodo" => array(
                "mes_ini" => 8,
                "mes_fin" => 8,
                "ejerc" => 2021
            ),
            "totales" => array(
                "monto_tot_operacion" => 37320.00,
                "monto_tot_exent" => 3732.00,
                "monto_tot_grav" => 33588.00,
                "monto_tot_ret" => 3732.00,
                "imp_retenidos" => array(
                    array(
                        "impuesto" => "ISR",
                        "monto_ret" => 3732.00,
                        "base_ret" => 37320.00,
                        //"tipo_pago_ret" => "03"
                    )
                ),
            ),
            "complements" => array(
                //"custom",
                '<dividendos:Dividendos Version="1.0"><dividendos:DividOUtil CveTipDivOUtil="06" MontISRAcredRetMexico="0" MontISRAcredRetExtranjero="0" TipoSocDistrDiv="Sociedad Nacional"/></dividendos:Dividendos>',
                //"<dividendos:DividOUtil CveTipDivOUtil='06' MontISRAcredRetMexico='0' MontISRAcredRetExtranjero='0' TipoSocDistrDiv='Sociedad Nacional'/>"
            ),
            "namespaces" => array(
                array(
                    "prefix" => "dividendos",
                    "uri" => "http://www.sat.gob.mx/esquemas/retencionpago/1/dividendos",
                    "schema_location" => "http://www.sat.gob.mx/esquemas/retencionpago/1/dividendos http://www.sat.gob.mx/esquemas/retencionpago/1/dividendos/dividendos.xsd"

                )
            ),
            "pdf_custom_section" => "<table><tr><td><b>Complemento de Dividendos</b></td></tr><tr><td>REPARTO Y DISTRIBUCION DE DIVIDENDOS 2021 PROVENIENTES DE CUFIN</td></tr><tr><td><b>Domicilio Receptor</b></td></tr><tr><td><b>Dirección</b> AV. INDEPENDENCIA No.307  <b>C.P.</b> 52100</td></tr><tr><td><b>Colonia</b> BARRIO DE SAN MIGUEL  <b>Municipio</b> SAN MATEO ATENCO</td></tr><tr><td><b>Estado</b> MÉXICO </td></tr></table>"
        ); 
        //dd($invoice);   
        $new_invoice = $this->facturapi->Retentions->create( $invoice );
        $result = json_encode($new_invoice);
        $result2 = json_decode($result, true);
        if(array_key_exists('ok', $result2) == true){
            $mensaje = $new_invoice->message;
            $tipomensaje = "error";
            $data = array(
                        'mensaje' => "Error, ".$mensaje,
                        'tipomensaje' => $tipomensaje 
                    );
            //return response()->json($data);
            dd($data);
        }else{
            /*
            //obtener datos del xml del documento timbrado para guardarlo en la tabla comprobantes
            $descargar_xml = $this->facturapi->Invoices->download_xml($new_invoice->id); // stream containing the XML file or
            $xml = simplexml_load_string($descargar_xml);  
            $comprobante = $xml->attributes(); 
            $CertificadoCFD = $comprobante['NoCertificado'];
            //obtener datos generales del xml nodo Emisor
            $activar_namespaces = $xml->getNameSpaces(true);
            $namespaces = $xml->children($activar_namespaces['cfdi']);
            //obtener UUID del xml timbrado digital
            $activar_namespaces = $namespaces->Complemento->getNameSpaces(true);
            $namespaces_uuid = $namespaces->Complemento->children($activar_namespaces['tfd']);
            $atributos_complemento = $namespaces_uuid->TimbreFiscalDigital->attributes();
            $NoCertificadoSAT = $atributos_complemento['NoCertificadoSAT'];
            $SelloCFD = $atributos_complemento['SelloCFD'];
            $SelloSAT = $atributos_complemento['SelloSAT'];
            $fechatimbrado = $atributos_complemento['FechaTimbrado'];
            $cadenaoriginal = "||".$atributos_complemento['Version']."|".$new_invoice->uuid."|".$atributos_complemento['FechaTimbrado']."|".$atributos_complemento['SelloCFD']."|".$atributos_complemento['NoCertificadoSAT']."||";
            //guardar en tabla comprobante
            $Comprobante = new Comprobante;
            $Comprobante->Comprobante = 'Factura';
            $Comprobante->Tipo = $new_invoice->type;
            //version 4.0
            //$Comprobante->Version = '4.0';
            //version 3.3
            $Comprobante->Version = '3.3';
            $Comprobante->Serie = $new_invoice->series;
            $Comprobante->Folio = $new_invoice->folio_number;
            $Comprobante->UUID = $new_invoice->uuid;
            $Comprobante->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $Comprobante->SubTotal = $factura->SubTotal;
            $Comprobante->Descuento = $factura->Descuento;
            $Comprobante->Total = $factura->Total;
            $Comprobante->EmisorRfc = $factura->EmisorRfc;
            $Comprobante->ReceptorRfc = $factura->ReceptorRfc;
            $Comprobante->FormaPago = $new_invoice->payment_form;
            $Comprobante->MetodoPago = $new_invoice->payment_method;
            $Comprobante->UsoCfdi = $new_invoice->use;
            $Comprobante->Moneda = $new_invoice->currency;
            $Comprobante->TipoCambio = Helpers::convertirvalorcorrecto($new_invoice->exchange);
            $Comprobante->CertificadoSAT = $NoCertificadoSAT;
            $Comprobante->CertificadoCFD = $CertificadoCFD;
            $Comprobante->FechaTimbrado = $fechatimbrado;
            $Comprobante->CadenaOriginal = $cadenaoriginal;
            $Comprobante->selloSAT = $SelloSAT;
            $Comprobante->selloCFD = $SelloCFD;
            //$Comprobante->CfdiTimbrado = $new_invoice->type;
            $Comprobante->Periodo = $this->periodohoy;
            $Comprobante->IdFacturapi = $new_invoice->id;
            $Comprobante->UrlVerificarCfdi = $new_invoice->verification_url;
            $Comprobante->save();
            //Colocar UUID en factura
            Factura::where('Factura', $request->facturatimbrado)
                            ->update([
                                'FechaTimbrado' => $fechatimbrado,
                                'UUID' => $new_invoice->uuid
                            ]);  
            */
            // Enviar a más de un correo (máx 10)
            $this->facturapi->Retentions->send_by_email(
                $new_invoice->id,
                array(
                    "osbaldo.anzaldo@utpcamiones.com.mx",
                    //"marco.baltazar@utpcamiones.com.mx",
                )
            );
            /*
            $mensaje = "Correcto, el documento se timbro correctamente";
            $tipomensaje = "success";
            $data = array(
                        'mensaje' => $mensaje,
                        'tipomensaje' => $tipomensaje 
                    );
            return response()->json($data);
            */
            dd($new_invoice);
        }        

    }

}
