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
                            <li style="font-size:18px; margin-left: 5px;"><b>Carta Porte:</b> <b style="color:red">{{$cartaporte->CartaPorte}}</b></li>
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
                        </tbody>
                    </table>
                </div>
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

            </section>
        </div>
    </body>
</html>
