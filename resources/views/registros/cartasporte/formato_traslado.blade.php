<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Traslado</title>
    </head>
    <style>
        .saltopagina {
            overflow: hidden;
            page-break-after: always;
        }
        .saltopagina:last-of-type {
            page-break-after: auto
        }
        body {
            letter-spacing: 1px;
	        font-family: 'Roboto', Arial, Tahoma, sans-serif;
        }
        .wrap,
        .wrap2{
            width:750px;
            white-space: pre-wrap;      /* CSS3 */
            white-space: -moz-pre-wrap; /* Firefox */
            white-space: -pre-wrap;     /* Opera <7 */
            white-space: -o-pre-wrap;   /* Opera 7 */
            word-wrap: break-word;      /* IE */
        }
    </style>
    <body>
        <div class="saltopagina">
            <section>
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" style="object-fit: contain;width:50%;height:auto;">
                    </div>
                    <div style="float:left;width:60%;text-align: center;">
                        <b style="font-size:13px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                        <b style="font-size:13px;color:#122b40;">{{$calleempresa}} No. {{$noexteriorempresa}} </b><br>
                        <b style="font-size:13px;color:#122b40;">{{$coloniaempresa}} CP: {{$cpempresa}}</b><br>
                        <b style="font-size:13px;color:#122b40;">{{$municipioempresa}}, {{$estadoempresa}}</b><br>
                        <b style="font-size:13px;color:#122b40;">RFC {{$empresa->Rfc}} Telefonos {{$telefonosempresa}}</b>
                    </div>
                    <div style="float:right;width:20%;text-align: right;">
                        <p style="font-size:10px;"></p>
                    </div>
                </div><br><br><br><br><br>
                <div>
                    <hr></hr>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:53%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:10px; margin-left: 5px;"> Nombre: {{$cliente->Nombre}}</li>
                            <li style="font-size:10px; margin-left: 5px;"> Dirección: {{$cliente->Calle}}</li>
                            <li style="font-size:10px; margin-left: 5px;"> Colonia: {{$cliente->Colonia}}</li>
                            <li style="font-size:10px; margin-left: 5px;"> Ciudad: {{$cliente->Estado}} {{$cliente->CodigoPostal}}</b></li>
                            <li style="font-size:10px; margin-left: 5px;"> EmisorRfc: {{$cartaporte->RfcRemitente}}</li>
                            <li style="font-size:10px; margin-left: 5px;"> ReceptorRfc: {{$cartaporte->RfcDestinatario}}</li>
                            <li style="font-size:10px; margin-left: 5px;"> ReceptorRegimenFiscal: {{$regimenfiscal->Nombre.' ('.$regimenfiscal->Clave.')'}}</li>
                        </ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:18px; margin-left: 5px;"><b>FOLIO:</b> <b style="color:red">{{$cartaporte->CartaPorte}}</b></li>
                            <li style="font-size:10px; margin-left: 5px;">Efecto de comprobante: Traslado</li>
                            <li style="font-size:10px; margin-left: 5px;">UsoCfdi: {{$usocfdi->Clave .' '.$usocfdi->Nombre}}</li>
                            <li style="font-size:10px; margin-left: 5px;">Emitida: {{$cartaporte->FechaTimbrado}}</li>
                        </ul>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:130px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                    <th>Cantidad</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Precio $</th>
                                    <th>Dcto %</th>
                                    <th>Descuento $</th>
                                    <th>SubTotal $</th>
                                </tr>
                            </tr>
                            @foreach ($datadetalle as $detalle)
                                <tr>
                                    <td>{{$detalle['cantidaddetalle']}}</td>
                                    <td>{{$detalle['codigodetalle']}}</td>
                                    <td>{{$detalle['descripciondetalle']}}</td>
                                    <td>{{$detalle['precio']}}</td>
                                    <td>{{$detalle['descuento']}}</td>
                                    <td>{{$detalle['descuentopesos']}}</td>
                                    <td>{{$detalle['importe']}}</td>
                                </tr>
                                <tr style="font-size:8px; text-align: justify;">
                                    <td colspan="2">Clave Producto: @if($detalle['claveproducto'] != null) {{$detalle['claveproducto']->Clave}} {{$detalle['claveproducto']->Nombre}} @endif</td>
                                    <td>Clave Unidad: @if($detalle['claveunidad'] != null) {{$detalle['claveunidad']->Clave}} {{$detalle['claveunidad']->Nombre}} @endif</td>
                                    <td colspan="4"><b>Peso Bruto Total: </b>{{ $detalle['pesoBruto']}} KG Peso Unitario: {{$detalle['pesounitario']}} KG</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="4" style="font-size:10px"></td>
                                <td style="font-size:11px;text-align: right;">SubTotal $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"> <b>{{ number_format(0, $numerodecimalesdocumento) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="font-size:10px"></td>
                                <td style="font-size:11px;text-align: right;">Total $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"> <b>{{ number_format(0, $numerodecimalesdocumento) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @if ($cartaporte->carreteraFederal)
                    <div id ="contenedor" style="margin-top:10px;">
                        <div style="width:100%;">
                            <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                                <tr style="background-color:#a6a6b3;font-size:9px;">
                                    <td style="font-size:9px;">Complemento Carta Porte</td>
                                </tr>
                                <tr style="text-align:justify;font-size:9px;">
                                    <td style="font-size:9px;">
                                        <li>Carta Porte</li>
                                        <br>
                                        <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                                            <tbody style="font-size:9px; text-align: justify;">
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th>Transporte Internacional</th>
                                                        <th>Distancia Recorrida</th>
                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td>{{$cartaporte->TransporteInternacional}}</td>
                                                    <td>{{number_format($cartaporte->TotalDistanciaRecorrida,$numerodecimalesdocumento,'.',',')}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr style="text-align:justify;font-size:9px;">
                                    <td style="font-size:9px;">
                                        <li>Ubicaciones</li>
                                        <br>
                                        <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                                            <tbody style="font-size:9px; text-align: justify;">
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th>Tipo de Ubicación</th>
                                                        <th>Identificación de la ubicación</th>
                                                        <th>RFC del Remitente o Destinatario</th>
                                                        <th>Nombre del Remitente o Destinatario</th>

                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td>Origen</td>
                                                    <td>OR000123</td>
                                                    <td>{{$cartaporte->RfcRemitente}}</td>
                                                    <td>{{$cartaporte->NombreRemitente}}</td>
                                                </tr>
                                                <tr>
                                                    <td><ul><li>Domicilio</li></ul></td>
                                                </tr>
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th></th>
                                                        <th>País</th>
                                                        <th>C.P</th>
                                                        <th>Estado</th>
                                                        <th>Municipio</th>
                                                        <th>Localidad</th>
                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td>México</td>
                                                    <td>{{$dataDomicilioEmisor['cp']}}</td>
                                                    <td>{{$dataDomicilioEmisor['estado']}}</td>
                                                    <td>{{$dataDomicilioEmisor['municipio']}}</td>
                                                    <td></td>
                                                </tr>
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th></th>
                                                        <th>Colonia</th>
                                                        <th>Calle</th>
                                                        <th>Número Exterior</th>
                                                        <th>Numero Interior</th>
                                                        <th>Referencia</th>
                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td>{{$dataDomicilioEmisor['colonia']}}</td>
                                                    <td>{{$dataDomicilioEmisor['calle']}}</td>
                                                    <td>{{$dataDomicilioEmisor['exterior']}}</td>
                                                    <td>{{$dataDomicilioEmisor['interior']}}</td>
                                                    <td>{{$dataDomicilioEmisor['referencia']}}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table><br>
                                        <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                                            <tbody style="font-size:9px; text-align: justify;">
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th>Tipo de Ubicación</th>
                                                        <th>Identificación de la ubicación</th>
                                                        <th>RFC del Remitente o Destinatario</th>
                                                        <th>Nombre del Remitente o Destinatario</th>

                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td>Destino</td>
                                                    <td>DE000456</td>
                                                    <td>{{$cartaporte->RfcDestinatario}}</td>
                                                    <td>{{$dataDestino['nombre']}}</td>
                                                </tr>
                                                <tr>
                                                    <td><ul><li>Domicilio</li></ul></td>
                                                </tr>
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th></th>
                                                        <th>País</th>
                                                        <th>C.P</th>
                                                        <th>Estado</th>
                                                        <th>Municipio</th>
                                                        <th>Localidad</th>
                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td>México</td>
                                                    <td>{{$dataDestino['cp']}}</td>
                                                    <td>{{$dataDestino['estado']}}</td>
                                                    <td>{{$dataDestino['municipio']}}</td>
                                                    <td>{{$dataDestino['localidad']}}</td>
                                                    <td></td>
                                                </tr>
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th></th>
                                                        <th>Colonia</th>
                                                        <th>Calle</th>
                                                        <th>Número Exterior</th>
                                                        <th>Numero Interior</th>
                                                        <th>Referencia</th>
                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td>{{$dataDestino['colonia']}}</td>
                                                    <td>{{$dataDestino['calle']}}</td>
                                                    <td>{{$dataDestino['exterior']}}</td>
                                                    <td>{{$dataDestino['interior']}}</td>
                                                    <td>{{$dataDestino['referencia']}}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr style="text-align:justify;font-size:9px;">
                                    <td style="font-size:9px;">
                                        <li>Mercancias</li>
                                        <br>
                                        <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                                            <tbody style="font-size:9px; text-align: justify;">
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th>Peso Bruto Total</th>
                                                        <th>Unidad Peso</th>
                                                        <th>Peso Neto Total</th>
                                                        <th>Total Mercancias</th>
                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td>{{number_format($cartaporte->PesoBrutoTotal,$numerodecimalesdocumento,'.',',')}}</td>
                                                    <td>Una unidad de masa igual a mil gramos (KGM).</td>
                                                    <td>{{number_format($cartaporte->PesoBrutoTotal,$numerodecimalesdocumento,'.',',')}}</td>
                                                    <td>{{$cartaporte->TotalMercancias}}</td>
                                                </tr>
                                                @foreach ($datadetalle as $detalle)
                                                    <tr>
                                                        <td><ul><li>Mercancia</li></ul></td>
                                                    </tr>
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <tr style="background-color:#a6a6b3;font-size:10px;">
                                                            <th></th>
                                                            <th>Bienes Transportados</th>
                                                            <th>Descripcion</th>
                                                            <th>Cantiad</th>
                                                            <th>Clave Unidad</th>
                                                        </tr>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>{{$detalle['claveproducto']->Nombre}}</td>
                                                        <td>{{$detalle['descripciondetalle']}}</td>
                                                        <td>{{$detalle['cantidaddetalle']}}</td>
                                                        <td>{{$detalle['claveunidad']->Nombre}}</td>
                                                    </tr>
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <tr style="background-color:#a6a6b3;font-size:10px;">
                                                            <th></th>
                                                            <th>Unidad</th>
                                                            <th>Material Peligroso</th>
                                                            <th>Clave Material Peligroso</th>
                                                            <th>Embalaje</th>
                                                        </tr>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>{{$detalle['claveunidad']->Clave}}</td>
                                                        <td>{{$detalle['materialPeligroso']}}</td>
                                                        <td>{{$detalle['claveMaterial']}}</td>
                                                        <td>{{$detalle['embalaje']}}</td>
                                                    </tr>
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <tr style="background-color:#a6a6b3;font-size:10px;">
                                                            <th></th>
                                                            <th>Peso En Kg</th>
                                                            <th>Fracción Arancelaria</th>
                                                        </tr>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>{{$detalle['pesoBruto']}}</td>
                                                        <td></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <br>
                                <tr style="text-align:justify;font-size:9px;">
                                    <td style="font-size:9px;">
                                        <li>Autotransporte</li>
                                        <br>
                                        <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                                            <tbody style="font-size:9px; text-align: justify;">
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th>Tipo PermisoSCT</th>
                                                        <th>Número de permiso SCT</th>
                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td>{{$datosAutoTransporte['tipoPermiso']}}</td>
                                                    <td>{{$datosAutoTransporte['numeroPermiso']}}</td>
                                                </tr>
                                                <tr>
                                                    <td><ul><li>Identificación Vehicular</li></ul></td>
                                                </tr>
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th></th>
                                                        <th>Configuración vehicular</th>
                                                        <th>Placa VM</th>
                                                        <th>Año modelo VM</th>
                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td>{{$datosAutoTransporte['confVehiculo']}}</td>
                                                    <td>{{$cartaporte->PlacaVehiculoMotor}}</td>
                                                    <td>{{$cartaporte->AnoModeloVehiculoMotor}}</td>
                                                </tr>
                                                <tr>
                                                    <td><ul><li>Seguros</li></ul></td>
                                                </tr>
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th></th>
                                                        <th>Aseguradora de Responsabilidad Civil</th>
                                                        <th>Poliza de responsabilidad Civil</th>
                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td>{{$cartaporte->NombreAsegurado}}</td>
                                                    <td>{{$cartaporte->NumeroPolizaSeguro}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                    </td>
                                </tr>
                                <tr style="text-align:justify;font-size:9px;">
                                    <td style="font-size:9px;">
                                        <li>Figura Transporte</li>
                                        <br>
                                        <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                                            <tbody style="font-size:9px; text-align: justify;">
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th>Tipo de Figura</th>
                                                        <th>RFC Figura</th>
                                                        <th>Número de Licencia</th>
                                                        <th>Número de Registro de Identidad Tributaria de la Figura</th>
                                                        <th>Residencia Fiscal de la Figura</th>

                                                    </tr>
                                                </tr>
                                                <tr>
                                                    <td>Operador</td>
                                                    <td>{{$cartaporte->RfcOperador}}</td>
                                                    <td>{{$cartaporte->NumeroLicencia}}</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td><ul><li>Domicilio</li></ul></td>
                                                </tr>
                                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                                        <th></th>
                                                        <th>País</th>
                                                        <th>C.P</th>
                                                        <th>Estado</th>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td>México</td>
                                                    <td>{{$cartaporte->CodigoPostalOperador}}</td>
                                                    <td>{{$cartaporte->EstadoOperador}}</td>
                                                </tr>
                                            </tbody>
                                        </table><br>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endif
                <div id ="contenedor" style="margin-top:20px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tr><td style="font-size:9px;">La reproducción no autorizada de este comprobante constituye un delito en los términos de las disposiciones fiscales</td></tr>
                            @foreach ($cartaporte->documentos as $documento)
                                <tr>
                                    <td style="font-size:9px;">Tipo Relación ({{$cartaporte->TipoRelacion}}) {{$documento->UUID}} Factura: {{$documento->Factura}}</td>
                                </tr>
                            @endforeach
                            <tr><td style="font-size:9px;color:red;"> @if($comprobante != null) Este documento es una representación impresa de un CFDI Versión {{$comprobante->Version}} @endif</td></tr>
                        </table>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tr><td style="font-size:9px;">{{$regimenEmisor->Clave}} {{$regimenEmisor->Nombre}}</td></tr>
                        </table>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tr>
                                <td style="font-size:9px;">Folio Fiscal / UUID:</td>
                                <td style="font-size:9px;">Fecha Timbrado:</td>
                                <td style="font-size:9px;">Certificado SAT:</td>
                                <td style="font-size:9px;">Certificado del Emisor:</td>
                            </tr>
                            @if($comprobantetimbrado > 0)
                                <tr>
                                    <td style="font-size:9px;">{{$comprobante->UUID}}</td>
                                    <td style="font-size:9px;">{{$comprobante->Fecha}}</td>
                                    <td style="font-size:9px;">{{$comprobante->CertificadoSAT}}</td>
                                    <td style="font-size:9px;">{{$comprobante->CertificadoCFD}}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tbody style="font-size:9px; text-align: justify;">
                                <tr>
                                    <td style="font-size:9px;">Sello Digital CFD:</td>
                                    @if($comprobantetimbrado > 0)
                                        <td rowspan="4">
                                            @if($comprobante->UrlVerificarCfdi != "")
                                                {!!QrCode::size(150)->margin(0)->generate($comprobante->UrlVerificarCfdi) !!}
                                            @else
                                                {!!QrCode::size(150)->margin(0)->generate("https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx") !!}
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                                @if($comprobantetimbrado > 0)
                                    <tr>
                                        <td style="font-size:9px;"><div style="width:700px;white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;">{{$comprobante->selloCFD}}</div></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="font-size:9px;">Sello Digital SAT:</td>
                                </tr>
                                @if($comprobantetimbrado > 0)
                                    <tr>
                                        <td style="font-size:9px;"><div style="width:700px;white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;">{{$comprobante->selloSAT}}</div></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div id ="contenedor" style="margin-top:10px;">
                        <div style="width:100%;">
                            <table style="width: 100%;max-width: 100%;">
                                <tbody style="font-size:9px; text-align: justify;">
                                    <tr>
                                        <td style="font-size:9px;">Cadena Original del Complemento de Certificación Digital del SAT:</td>
                                    </tr>
                                    @if($comprobantetimbrado > 0)
                                        <tr>
                                            <td style="font-size:9px;"><div style="width:915px;white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;">{{$comprobante->CadenaOriginal}}</div></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>
