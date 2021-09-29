<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Nota de Crédito Proveedor</title>
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
                            <li style="font-size:9px; margin-left: 5px;"> Proveedor: {{$d['proveedor']->Nombre}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Calle: {{$d['proveedor']->Calle}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Colonia: {{$d['proveedor']->Colonia}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Municipio: {{$d['proveedor']->Municipio}}</li>
                        </ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:18px; margin-left: 5px;"><b>Nota Proveedor: </b><b style="color:red">{{$d['notacreditoproveedor']->Nota}}</b></li>
                            <li style="font-size:9px; margin-left: 5px;">Fecha: {{$d['notacreditoproveedor']->Fecha}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Status: {{$d['notacreditoproveedor']->Status}}</li>
                        </ul>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:13px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Cantidad</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Compra</th>
                                <th>Remision</th>
                                <th>Factura</th>
                                <th>Precio $</th>
                                <th>SubTotal $</th>
                            </tr>
                            @foreach($d['datadetalle'] as $ncpd)
                            <tr style="font-size:10px;">
                                <td>{{ number_format($ncpd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{$ncpd['codigodetalle']}}</td>
                                <td>{{$ncpd['descripciondetalle']}}</td>
                                <td>{{$ncpd['compradetalle']}}</td>
                                <td>{{$ncpd['remisiondetalle']}}</td>
                                <td>{{$ncpd['facturadetalle']}}</td>
                                <td>{{ number_format($ncpd['preciodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($ncpd['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">Descuento $ : </td>
                                <td style="font-size:11px;text-align: right;"><b>{{ number_format($d['descuentonotacreditoproveedor'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">SubTotal $ : </td>
                                <td style="font-size:11px;text-align: right;"><b>{{ number_format($d['subtotalnotacreditoproveedor'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">IVA $ : </td>
                                <td style="font-size:11px;text-align: right;"><b>{{ number_format($d['ivanotacreditoproveedor'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">Total $ : </td>
                                <td style="font-size:11px;text-align: right;"><b>{{ number_format($d['totalnotacreditoproveedor'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:150px;">
                    <div style="width:45%; float:left; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Realizó</p>
                        <p style="font-size:11px;"></p>
                    </div>
                    <div style="width:5%; float:left;">
                    </div>
                    <div style="width:45%; float:right; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Recibió</p>
                        <p style="font-size:11px;"></p>
                    </div>
                </div>
            </section>
        </div>
    </body>
    @endforeach
</html>
