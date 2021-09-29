<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Cuenta Por Cobrar</title>
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
                            <li style="font-size:9px; margin-left: 5px;"> Colonia: {{$d['cliente']->Colonia}}  &nbsp;&nbsp;  Estado: @if($d['estadocliente'] != null) {{$d['estadocliente']->Nombre}} @endif</li>
                            <li style="font-size:9px; margin-left: 5px;"> Municipio: {{$d['cliente']->Municipio}} &nbsp;&nbsp; C.P. {{$d['cliente']->CodigoPostal}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> EmisorRfc:{{$d['cuentaporcobrar']->EmisorRfc}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> ReceptorRfc: {{$d['cuentaporcobrar']->ReceptorRfc}}</li>
                        </ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:18px; margin-left: 5px;"><b>Pago:</b> <b style="color:red">{{$d['cuentaporcobrar']->Pago}}</b></li>
                            <li style="font-size:9px; margin-left: 5px;">Emitida: {{$d['cuentaporcobrar']->Hora}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Fecha Depósito: {{$d['cuentaporcobrar']->FechaPago}}</li>
                        </ul>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:130px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>UUID del Documento</th>
                                <th>Factura</th>
                                <th>Fecha</th>
                                <th>Plazo</th>
                                <th>Vence</th>
                                <th>Total $</th>
                                <th>Parc</th>
                                <th>ImpSaldoAnt $</th>
                                <th>ImpPagado $</th>
                                <th>ImpSaldoInsoluto $</th>
                            </tr>
                            @foreach($d['datadetalle'] as $cxcd)
                                <tr style="font-size:10px;">
                                    <td>{{$cxcd['iddocumentodetalle']}}</td>
                                    <td>{{$cxcd['facturadetalle']}}</td>
                                    <td>{{$cxcd['fechadetalle']}}</td>
                                    <td>{{$cxcd['plazodetalle']}}</td>
                                    <td>{{$cxcd['vencedetalle']}}</td>
                                    <td>{{ number_format($cxcd['totalfactura'], $d['numerodecimalesdocumento']) }}</td>
                                    <td>{{$cxcd['numparcialidaddetalle']}}</td>
                                    <td>{{ number_format($cxcd['impsaldoantdetalle'], $d['numerodecimalesdocumento']) }}</td>
                                    <td>{{ number_format($cxcd['imppagadodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                    <td>{{ number_format($cxcd['impsaldoinsolutodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                </tr>
                                <tr style="font-size:10px;">
                                    <td></td> 
                                    <td colspan="2"><b>{{ number_format($cxcd['tipocambiofacturadetalle'], $d['numerodecimalesdocumento']) }}</b></td> 
                                    <td colspan="7"><b>Método de Pago: {{$cxcd['nombremetodopagodetalle']}} ({{$cxcd['clavemetodopagodetalle']}})</b></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="7" style="font-size:10px"></td>
                                <td style="font-size:10px;text-align: right;">Total $ : </td>
                                <td style="font-size:10px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['abonocuentaporcobrar'], $d['numerodecimalesdocumento']) }}</b></td>
                                <td style="font-size:10px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['importesaldoinsoluto'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id ="contenedor">
                    <div style="width:100%; float:right; text-align: left;">
                        <p style="font-size:12px;">{{$d['cuentaporcobrar']->Anotacion}}</p>
                        <b style="font-size:10px;"></b>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:20px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tr><td style="font-size:9px;">{{ number_format($d['tipocambiocxc'], $d['numerodecimalesdocumento']) }} {{$d['cuentaporcobrar']->Moneda}}</td></tr>
                            <tr><td style="font-size:9px;">{{$d['totalletras']}}</td></tr>
                            <tr><td style="font-size:9px;">La reproducción no autorizada de este comprobante constituye un delito en los términos de las disposiciones fiscales</td></tr>
                            <tr><td style="font-size:9px;">Forma Pago: {{$d['formapago']->Nombre}} ({{$d['formapago']->Clave}})</td></tr>
                        </table>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:100%;">
                        <table style="width: 100%;max-width: 100%;">
                            <tr><td style="font-size:9px;">Regimen Fiscal : {{$d['regimenfiscal']->Nombre}} ({{$d['regimenfiscal']->Clave}})</td></tr>
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
