<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Contrarecibo</title>
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
        .marcafirma {
            opacity: 0.2;
            filter: alpha(opacity=40); /* For IE8 and earlier */
        }
        #capa1{ 
            position:absolute;
            z-index:1;
            background-color:#FFFFFF;
            text-align:center;
            background-color: transparent;
        }
        #capa2{
            /*position:absolute;*/
            z-index:0;
        }
    </style>
    @foreach($data as $d)
    <body>
        <div class="saltopagina">
            <section>
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}"  style="object-fit: contain;width:50%;height:auto;">
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
                    <div style="width:53.8%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:10px; margin-left: 5px;"> Proveedor: {{$d['proveedor']->Nombre}} ({{$d['contrarecibo']->Proveedor}})</li>
                            <li style="font-size:10px; margin-left: 5px;"> Observaciones: {{$d['contrarecibo']->Obs}}</li>
                        </ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:18px; margin-left: 5px;"><b>ContraRecibo: </b><b style="color:red">{{$d['contrarecibo']->ContraRecibo}}</b></li>
                            <li style="font-size:10px; margin-left: 5px;">Fecha: {{$d['contrarecibo']->Fecha}}</li>
                            <li style="font-size:10px; margin-left: 5px;">Status: {{$d['contrarecibo']->Status}}</li>
                            <li style="font-size:10px; margin-left: 5px;">Ampara: {{$d['contrarecibo']->Facturas}} Factura(s)</li>
                        </ul>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:130px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Fecha Factura</th>
                                <th>Factura</th>
                                <th>Remision</th>
                                <th>Total$</th>
                                <th>Fecha de Pago</th>
                                <th>Movimiento de Compra</th>
                            </tr>
                            @foreach($d['datadetalle'] as $crd)
                            <tr style="font-size:10px;">
                                <td>{{$crd['fechadetalle']}}</td>
                                <td>{{$crd['facturadetalle']}}</td>
                                <td>{{$crd['remisiondetalle']}}</td>
                                <td>{{ number_format($crd['totaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{$crd['fechaapagardetalle']}}</td>
                                <td>{{$crd['compradetalle']}}</td>
                            </tr>
                            @endforeach
                            <tr rowspan="5">
                                <td colspan="4" style="font-size:11px"><b>CERO PESOS 00/100 M.N.</b></td>
                                <td style="font-size:11px;text-align: right;">Total $:</td>
                                <td style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['totalcontrarecibo'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:30px;">
                    <div style="width:100%; float:right; text-align: center;">
                        <b style="font-size:12px;">Nota. No se entregará cheque SIN ContraRecibo Sellado y Firmado</b><br>
                    </div>
                </div>
                        @if($d['numerofirmas'] > 0)
                            <div id ="contenedor" style="margin-top:100px;">
                                @foreach($d['firmas'] as $f)
                                    <div style="width:50%; float:left; text-align: center;">
                                        <div id="capa1" style="width:50%;">
                                            <table style="width: 98%;max-width: 98%;border: 1px solid #ddd;">
                                                <tr>
                                                    <td >
                                                        {{$f->name}}
                                                    </td>
                                                    <td>
                                                        <p style="font-size:13px;color:#122b40;">Firmado electrónicamente por {{$f->name}}</p>
                                                        <p style="font-size:13px;color:#122b40;">El {{$f->Fecha}}</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div id="capa2" style="text-align: center;">
                                            <img class="marcafirma" src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="135px" height="95px">
                                        </div>
                                        <div>
                                            <b style="font-size:11px;color:blue;"><hr></hr></b>
                                            <p style="font-size:11px;">{{$f->ReferenciaPosicion}}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                        <div id ="contenedor" style="margin-top:150px;">
                            <div style="width:45%; float:left; text-align: center;">
                                <b style="font-size:11px;"><hr></hr></b>
                                <p style="font-size:11px;">{{$d['proveedor']->Nombre}} ({{$d['contrarecibo']->Proveedor}})</p>
                                <p style="font-size:11px;">Recibió ContraRecibo</p>
                            </div>
                            <div style="width:5%; float:left;">
                            </div>
                            <div style="width:45%; float:right; text-align: center;">
                                <b style="font-size:11px;"><hr></hr></b>
                                <p style="font-size:11px;">{{$d['contrarecibo']->Usuario}}</p>
                                <p style="font-size:11px;"></p>
                            </div>
                        </div>
                        @endif
            </section>
        </div>
    </body>
    @endforeach
</html>