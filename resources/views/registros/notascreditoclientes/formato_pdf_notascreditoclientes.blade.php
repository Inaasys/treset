<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Nota de Crédito Cliente</title>
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
                    <div style="width:53%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:9px; margin-left: 5px;"> Nombre: {{$d['cliente']->Nombre}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Dirección: {{$d['cliente']->Calle}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Colonia: {{$d['cliente']->Colonia}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Ciudad: {{$d['cliente']->Estado}} {{$d['cliente']->CodigoPostal}}</b></li>
                            <li style="font-size:9px; margin-left: 5px;"> EmisorRfc: {{$d['notacreditocliente']->EmisorRfc}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> ReceptorRfc: {{$d['notacreditocliente']->ReceptorRfc}}</li>
                        </ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:18px; margin-left: 5px;"><b>Nota Cliente:</b> <b style="color:red">{{$d['notacreditocliente']->Nota}}</b></li>
                            <li style="font-size:9px; margin-left: 5px;">UsoCfdi: {{$d['notacreditocliente']->UsoCfdi}} {{$d['usocfdi']->Nombre}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Forma de Pago: {{$d['notacreditocliente']->FormaPago}} {{$d['formapago']->Nombre}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Método de Pago: {{$d['notacreditocliente']->MetodoPago}} {{$d['metodopago']->Nombre}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Emitida: {{$d['notacreditocliente']->Hora}}</li>
                        </ul>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:130px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Cantidad</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Precio $</th>
                                <th>Dcto %</th>
                                <th>Descuento $</th>
                                <th>SubTotal $</th>
                            </tr>
                            @foreach($d['datadetalle'] as $nccd)
                            <tr>
                                <td>{{ number_format($nccd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{$nccd['codigodetalle']}}</td>
                                <td>{{$nccd['descripciondetalle']}}</td>
                                <td>{{ number_format($nccd['preciodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($nccd['porcentajedescuentodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($nccd['pesosdescuentodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($nccd['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                            </tr>
                            <tr style="font-size:8px; text-align: justify;">
                                <td colspan="2">Clave Producto: @if($nccd['claveproducto'] != null) {{$nccd['claveproducto']->Clave}} {{$nccd['claveproducto']->Nombre}} @endif</td>
                                <td>Clave Unidad: @if($nccd['claveunidad'] != null) {{$nccd['claveunidad']->Clave}} {{$nccd['claveunidad']->Nombre}} @endif</td>
                                <td colspan="4"><b>Traslado:</b>Tasa002 Iva{{ number_format($nccd['impuestodetalle'], $d['numerodecimalesdocumento']) }}% = {{ number_format($nccd['ivadetalle'], $d['numerodecimalesdocumento']) }} Base {{ number_format($nccd['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="4" style="font-size:10px"></td>
                                <td style="font-size:11px;text-align: right;">SubTotal $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"> <b>{{ number_format($d['subtotalnotacreditocliente'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="font-size:10px"></td>
                                <td style="font-size:11px;text-align: right;">IVA $ : </td>
                                <td colspan="2"style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['ivanotacreditocliente'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="font-size:10px"></td>
                                <td style="font-size:11px;text-align: right;">Total $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"> <b>{{ number_format($d['totalnotacreditocliente'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:20px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tr><td style="font-size:9px;">{{ number_format($d['tipocambiofactura'], $d['numerodecimalesdocumento']) }} {{$d['notacreditocliente']->Moneda}}</td></tr>
                            <tr><td style="font-size:9px;">{{$d['totalletras']}}</td></tr>
                            <tr><td style="font-size:9px;">La reproducción no autorizada de este comprobante constituye un delito en los términos de las disposiciones fiscales</td></tr>
                            <tr><td style="font-size:9px;">Tipo Relación ({{$d['notacreditocliente']->TipoRelacion}}) {{$d['notaclientedocumento']->UUID}} Factura: {{$d['notaclientedocumento']->Factura}}</td></tr>
                        </table>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tr><td style="font-size:9px;">{{$d['regimenfiscal']->Clave}} {{$d['regimenfiscal']->Nombre}}</td></tr>
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
            </section>
        </div>
    </body>
    @endforeach
</html>
