<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Compra</title>
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
                            <li style="font-size:9px; margin-left: 5px;"> Factura Número: {{$d['compra']->Factura}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Remisión Número: {{$d['compra']->Remision}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> UUID: {{$d['compra']->UUID}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Emisor Rfc: {{$d['compra']->EmisorRfc}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Receptor Rfc: {{$d['compra']->ReceptorRfc}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Proveedor: {{$d['proveedor']->Nombre}} ({{$d['compra']->Proveedor}})</li>
                            <li style="font-size:9px; margin-left: 5px;"> Observaciones: {{$d['compra']->Obs}}</li>
                        </ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:18px; margin-left: 5px;"><b>Compra: </b> <b style="color:red"> {{$d['compra']->Compra}}</b></li>
                            <li style="font-size:9px; margin-left: 5px;">Fecha: {{$d['compra']->Fecha}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Status: {{$d['compra']->Status}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Tipo: {{$d['compra']->Tipo}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Movimiento: {{$d['compra']->Movimiento}}</li>
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
                                <th>Marca</th>
                                <th>Ubicación</th>
                                <th>Precio</th>
                                <th>Dcto %</th>
                                <th>SubTotal</th>
                            </tr>
                            @foreach($d['datadetalle'] as $ocd)
                            <tr>
                                <td>{{ number_format($ocd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{$ocd['codigodetalle']}}</td>
                                <td>{{$ocd['descripciondetalle']}}</td>
                                <td>{{$ocd['marcadetalle']}}</td>
                                <td>{{$ocd['ubicaciondetalle']}}</td>
                                <td>{{ number_format($ocd['preciodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($ocd['descuentodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($ocd['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="5" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">Descuento $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['descuentocompra'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">SubTotal $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['subtotalcompra'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">IVA $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['ivacompra'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">Retención $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"><b></b></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">Total $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['totalcompra'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:150px;">
                    <div style="width:45%; float:left; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Capturó Movimiento</p>
                        <p style="font-size:11px;">{{$d['compra']->Usuario}}</p>
                    </div>
                    <div style="width:5%; float:left;">
                    </div>
                    <div style="width:45%; float:right; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Recibió Mercancia</p>
                        <p style="font-size:11px;"></p>
                    </div>
                </div>
            </section>
        </div>
    </body>
    @endforeach
</html>
