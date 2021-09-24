<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Factura</title>
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
    @foreach($data as $d)
    <body>
        <div class="saltopagina">
            <section>
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="125px" height="80px">
                    </div>
                    <div style="float:left;width:60%;text-align: center;">
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                        <b style="font-size:12px;color:#122b40;">{{$calleempresa}} No. {{$noexteriorempresa}} </b><br>
                        <b style="font-size:12px;color:#122b40;">{{$coloniaempresa}} CP: {{$cpempresa}}</b><br>
                        <b style="font-size:12px;color:#122b40;">{{$municipioempresa}}, {{$estadoempresa}}</b><br>
                        <b style="font-size:12px;color:#122b40;">RFC {{$empresa->Rfc}} Telefonos {{$telefonosempresa}}</b>
                    </div>
                    <div style="float:right;width:20%;text-align: right;">
                        <p style="font-size:10px;"></p>
                    </div>
                </div><br><br><br><br><br>
                <div>
                    <hr></hr>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:53%; height:110px;float:left;text-align:left;border-style:groove;">    
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:9px; margin-left: 5px;"> Nombre: {{$d['cliente']->Nombre}} ({{$d['cliente']->Numero}})</li>
                            <li style="font-size:9px; margin-left: 5px;"> Dirección: {{$d['cliente']->Calle}} {{$d['cliente']->noExterior}} {{$d['cliente']->noInterior}}</b></li>
                            <li style="font-size:9px; margin-left: 5px;"> Colonia: {{$d['cliente']->Colonia}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Estado: {{$d['cliente']->Localidad}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Ciudad: {{$d['cliente']->Municipio}} C.P. {{$d['cliente']->CodigoPostal}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Agente: {{$d['agente']->Nombre}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> EmisorRfc: {{$d['factura']->EmisorRfc}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> ReceptorRfc: {{$d['factura']->ReceptorRfc}}</li>
                        </ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:18px; margin-left: 5px;"><b>Factura:</b> <b style="color:red">{{$d['factura']->Factura}}</b></li>
                            <li style="font-size:9px; margin-left: 5px;">Plazo: {{$d['factura']->Plazo}} Días</li>
                            <li style="font-size:9px; margin-left: 5px;">Emitida: {{$d['factura']->Hora}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Vence: {{$d['fechavence']}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Pedido: {{$d['factura']->Pedido}}</li>
                            <li style="font-size:9px; margin-left: 5px;">UsoCfdi: @if($d['usocfdi'] != null) {{$d['usocfdi']->Clave}} {{$d['usocfdi']->Nombre}} @endif</li>
                            <li style="font-size:9px; margin-left: 5px;">Forma de Pago: @if($d['formapago'] != null) {{$d['formapago']->Clave}} {{$d['formapago']->Nombre}} @endif</li>
                            <li style="font-size:9px; margin-left: 5px;">Método de Pago: @if($d['metodopago'] != null) {{$d['metodopago']->Clave}} {{$d['metodopago']->Nombre}} @endif</li>
                        </ul>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:130px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Cantidad</th>
                                <th>Descripción</th>
                                <th>Insumo</th>
                                <th>Precio $</th>
                                <th colspan="2">SubTotal $</th>
                            </tr>
                            @if($d['tipodetalles'] == 'remisiones')
                                @foreach($d['datageneral'] as $dataremisionoorden)
                                    @if($dataremisionoorden['datosgenerales'] != null)
                                        <tr>
                                            <td style="font-size:12px;" colspan="6"><b>Remisión:</b> {{$dataremisionoorden['datosgenerales']->Remision}} &nbsp;&nbsp;&nbsp; <b>Pedido:</b> {{$dataremisionoorden['datosgenerales']->Pedido}} &nbsp;&nbsp;&nbsp; <b>Orden Servicio:</b> {{$dataremisionoorden['datosgenerales']->Os}} &nbsp;&nbsp;&nbsp; <b>Equipo:</b> {{$dataremisionoorden['datosgenerales']->Eq}} &nbsp;&nbsp;&nbsp; <b>Requisición:</b> {{$dataremisionoorden['datosgenerales']->Rq}}</td>
                                        </tr>
                                    @endif
                                    @foreach($dataremisionoorden['datadetalle'] as $detalle)
                                        <tr>
                                        <td>{{ number_format($detalle['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                            <td>{{$detalle['descripciondetalle']}}</td>
                                            <td>{{$detalle['insumodetalle']}}</td>
                                            <td>{{ number_format($detalle['preciodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                            <td colspan="2">{{ number_format($detalle['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                                        </tr>
                                        <tr style="font-size:8px; text-align: justify;">
                                            <td colspan="2">Clave Producto: @if($detalle['claveproducto'] != null) {{$detalle['claveproducto']->Clave}} {{$detalle['claveproducto']->Nombre}} @endif</td>
                                            <td>Clave Unidad: @if($detalle['claveunidad'] != null) {{$detalle['claveunidad']->Clave}} {{$detalle['claveunidad']->Nombre}} @endif</td>
                                            <td colspan="3"><b>Traslado:</b>Tasa002 Iva{{ number_format($detalle['impuestodetalle'], $d['numerodecimalesdocumento']) }}% = {{ number_format($detalle['ivadetalle'], $d['numerodecimalesdocumento']) }} Base {{ number_format($detalle['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="5" style="font-size:10px"></td>
                                        <td style="font-size:11px;text-align: right;"><b>{{ number_format($dataremisionoorden['sumatotaldetalles'], $d['numerodecimalesdocumento']) }}</b></td>
                                    </tr>
                                @endforeach
                            @endif
                            @if($d['tipodetalles'] == 'ordenes')
                                @foreach($d['datageneral'] as $dataremisionoorden)
                                    @if($dataremisionoorden['datosgenerales'] != null)
                                        <tr>
                                            <td style="font-size:12px;" colspan="6"><b>Orden:</b> {{$dataremisionoorden['datosgenerales']->Orden}} &nbsp;&nbsp; <b>Pedido:</b> {{$dataremisionoorden['datosgenerales']->Pedido}} &nbsp;&nbsp; <b>No. de Vin:</b> {{$dataremisionoorden['datosgenerales']->Vin}} &nbsp;&nbsp; <b>Economico:</b> {{$dataremisionoorden['datosgenerales']->Economico}} &nbsp;&nbsp; <b>Motor:</b> {{$dataremisionoorden['datosgenerales']->Motor}} &nbsp;&nbsp; <b>Marca:</b> {{$dataremisionoorden['datosgenerales']->Marca}} &nbsp;&nbsp; <b>Modelo:</b> {{$dataremisionoorden['datosgenerales']->Modelo}} &nbsp;&nbsp; <b>Año:</b> {{$dataremisionoorden['datosgenerales']->Año}} &nbsp;&nbsp; <b>Kilometros:</b> {{$dataremisionoorden['datosgenerales']->Kilometros}}</td>
                                        </tr>
                                    @endif
                                    @foreach($dataremisionoorden['datadetalle'] as $detalle)
                                        <tr>
                                            <td>{{ number_format($detalle['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                            <td>{{$detalle['descripciondetalle']}}</td>
                                            <td></td>
                                            <td style="text-align: right;">{{ number_format($detalle['preciodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                            <td style="text-align: right;" colspan="2">{{ number_format($detalle['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                                        </tr>
                                        <tr style="font-size:8px; text-align: justify;">
                                            <td colspan="2">Clave Producto: @if($detalle['claveproducto'] != null) {{$detalle['claveproducto']->Clave}} {{$detalle['claveproducto']->Nombre}} @endif</td>
                                            <td>Clave Unidad: @if($detalle['claveunidad'] != null) {{$detalle['claveunidad']->Clave}} {{$detalle['claveunidad']->Nombre}} @endif</td>
                                            <td colspan="3"><b>Traslado:</b>Tasa002 Iva{{ number_format($detalle['impuestodetalle'], $d['numerodecimalesdocumento']) }}% = {{ number_format($detalle['ivadetalle'], $d['numerodecimalesdocumento']) }} Base {{ number_format($detalle['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="5" style="font-size:10px"></td>
                                        <td style="font-size:11px;text-align: right;"><b>{{ number_format($dataremisionoorden['sumatotaldetalles'], $d['numerodecimalesdocumento']) }}</b></td>
                                    </tr>
                                @endforeach
                            @endif
                            @if($d['tipodetalles'] == 'libre')
                                @foreach($d['datageneral'] as $dataremisionoorden)
                                    @foreach($dataremisionoorden['datadetalle'] as $detalle)
                                        <tr>
                                            <td>{{ number_format($detalle['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                            <td>{{$detalle['descripciondetalle']}}</td>
                                            <td></td>
                                            <td>{{ number_format($detalle['preciodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                            <td colspan="2">{{ number_format($detalle['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                                        </tr>
                                        <tr style="font-size:8px; text-align: justify;">
                                            <td colspan="2">Clave Producto: @if($detalle['claveproducto'] != null) {{$detalle['claveproducto']->Clave}} {{$detalle['claveproducto']->Nombre}} @endif</td>
                                            <td>Clave Unidad: @if($detalle['claveunidad'] != null) {{$detalle['claveunidad']->Clave}} {{$detalle['claveunidad']->Nombre}} @endif</td>
                                            <td colspan="3"><b>Traslado:</b>Tasa002 Iva{{ number_format($detalle['impuestodetalle'], $d['numerodecimalesdocumento']) }}% = {{ number_format($detalle['ivadetalle'], $d['numerodecimalesdocumento']) }} Base {{ number_format($detalle['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="5" style="font-size:10px"></td>
                                        <td style="font-size:11px;text-align: right;"><b>{{ number_format($dataremisionoorden['sumatotaldetalles'], $d['numerodecimalesdocumento']) }}</b></td>
                                    </tr>
                                @endforeach
                            @endif
                            <tr>
                                <td colspan="3" style="font-size:10px"></td>
                                <td style="font-size:11px;text-align: right;">SubTotal $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['subtotalfactura'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="font-size:10px"></td>
                                <td style="font-size:11px;text-align: right;">IVA $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['ivafactura'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="font-size:10px"></td>
                                <td style="font-size:11px;text-align: right;">Total $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['totalfactura'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:20px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tr><td style="font-size:9px;">{{ number_format($d['tipocambiofactura'], $d['numerodecimalesdocumento']) }} {{$d['factura']->Moneda}}</td></tr>
                            <tr><td style="font-size:9px;">{{$d['totalletras']}}</td></tr>
                            <tr><td style="font-size:9px;">La reproducción no autorizada de este comprobante constituye un delito en los términos de las disposiciones fiscales</td></tr>
                        </table>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tr><td style="font-size:9px;">Regimen Fiscal : @if($d['regimenfiscal'] != null) {{$d['regimenfiscal']->Nombre}} ({{$d['regimenfiscal']->Clave}}) @endif</td></tr>
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
                            @if($d['comprobantetimbrado'] > 0)
                                <tr>
                                    <td style="font-size:9px;">{{$d['comprobante']->UUID}}</td>
                                    <td style="font-size:9px;">{{$d['comprobante']->Fecha}}</td>
                                    <td style="font-size:9px;">{{$d['comprobante']->CertificadoSAT}}</td>
                                    <td style="font-size:9px;">{{$d['comprobante']->CertificadoCFD}}</td>
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
                                    @if($d['comprobantetimbrado'] > 0)
                                        <td rowspan="4">
                                            @if($d['comprobante']->UrlVerificarCfdi != "")
                                                {!!QrCode::size(150)->margin(0)->generate($d['comprobante']->UrlVerificarCfdi) !!}
                                            @else
                                                {!!QrCode::size(150)->margin(0)->generate("https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx") !!}
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                                @if($d['comprobantetimbrado'] > 0)
                                    <tr>
                                        <td style="font-size:9px;"><div style="width:700px;white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;">{{$d['comprobante']->selloCFD}}</div></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="font-size:9px;">Sello Digital SAT:</td>
                                </tr>
                                @if($d['comprobantetimbrado'] > 0)
                                    <tr>
                                        <td style="font-size:9px;"><div style="width:700px;white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;">{{$d['comprobante']->selloSAT}}</div></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tbody style="font-size:9px; text-align: justify;">
                                <tr>
                                    <td style="font-size:9px;">Cadena Original del Complemento de Certificación Digital del SAT:</td>
                                </tr>
                                @if($d['comprobantetimbrado'] > 0)
                                    <tr>
                                        <td style="font-size:9px;"><div style="width:915px;white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;">{{$d['comprobante']->CadenaOriginal}}</div></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($d['pagare'] != "")
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                            <tr style="background-color:#a6a6b3;font-size:9px;">
                                <td style="font-size:9px;">PAGARÉ No. {{$d['factura']->Factura}}</td>
                            </tr>
                            <tr style="text-align:justify;font-size:9px;">
                                <td style="font-size:9px;">{!! nl2br(e($d['pagare'])) !!} </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @endif
            </section>
        </div>
    </body>
    @endforeach
</html>