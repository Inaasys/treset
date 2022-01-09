<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="16x16">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="32x32">
        <title>Formato | Requisción</title>
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
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="125px" height="80px">
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
                            <li style="font-size:10px; margin-left: 5px;"> Cliente: {{$d['cliente']->Nombre}} ({{$d['cliente']->Numero}})</li>
                            <li style="font-size:10px; margin-left: 5px;"> Observaciones: {{$d['requisicion']->Obs}}</li>
                        </ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:110px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:12px; margin-left: 5px;"><b>Requisicion: </b><b style="color:red">{{$d['requisicion']->Requisicion}}</b></li>
                            <li style="font-size:10px; margin-left: 5px;">Fecha: {{$d['requisicion']->Fecha}}</li>
                            <li style="font-size:10px; margin-left: 5px;">Status: {{$d['requisicion']->Status}}</li>
                        </ul>
                    </div>
                </div>
				<div id ="contenedor">
                    <div style="width:98.5%; height:60px; float:left; text-align: left; border-style: groove;">
                        <table style="width:100%;">
                            <tr style="text-align:center">
								<td style="font-size:10px;width:25%">Orden: <b>{{$d['ordentrabajo']->Orden}}</b></td>
								<td style="font-size:10px;width:25%">Pedido: {{$d['ordentrabajo']->Pedido}}</td>
								<td style="font-size:10px;width:25%">Serie/VIN: {{$d['ordentrabajo']->Vin}}</td>
								<td style="font-size:10px;width:25%">No. Económico: {{$d['ordentrabajo']->Economico}}</td>
							</tr>
                            <tr style="text-align:center">
								<td style="font-size:10px;width:25%">Motor: {{$d['ordentrabajo']->Motor}}</td>
								<td style="font-size:10px;width:25%">Marca: {{$d['ordentrabajo']->Marca}}</td>
								<td style="font-size:10px;width:25%">Modelo: {{$d['ordentrabajo']->Modelo}}</td>
								<td style="font-size:10px;width:25%">Año: {{$d['ordentrabajo']->Año}}</td>
							</tr>
							<tr style="text-align:center">
								<td style="font-size:10px;width:25%">Kilometros: {{$d['ordentrabajo']->Kilometros}}</td>
							</tr>
						</table>
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
                                <th>Línea</th>
                                <th>Ubicación</th>
                                <th>Precio</th>
                                <th>Importe</th>
                            </tr>
                            @foreach($d['datadetalle'] as $rd)
                            <tr style="font-size:10px;">
                                <td>{{ number_format($rd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{$rd['codigodetalle']}}</td>
                                <td>{{$rd['descripciondetalle']}}</td>
                                <td>{{$rd['marcadetalle']}}</td>
                                <td>{{$rd['lineadetalle']}}</td>
                                <td>{{$rd['ubicaciondetalle']}}</td>
                                <td>{{ number_format($rd['preciodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($rd['importedetalle'], $d['numerodecimalesdocumento']) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="5" style="font-size:11px"></td>
                                <td colspan="2" style="font-size:11px;text-align: right;">Total $ : </td>
                                <td style="font-size:11px;text-align: right;"><b>{{ number_format($d['totalrequisicion'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @if($d['numerofirmas'] > 0)
                            <div id ="contenedor" style="margin-top:50px;">
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
                            <p style="font-size:11px;">Elaboró</p>
                            <p style="font-size:11px;">{{$d['requisicion']->Usuario}}</p>
                        </div>
                        <div style="width:5%; float:left;">
                        </div>
                        <div style="width:45%; float:right; text-align: center;">
                            <b style="font-size:11px;"><hr></hr></b>
                            <p style="font-size:11px;">Entregó Material</p>
                            <p style="font-size:11px;"></p>
                        </div>
                    </div>
                    <div id ="contenedor" style="margin-top:300px;">
                        <div style="width:45%; float:left; text-align: center;">
                            <b style="font-size:11px;"><hr></hr></b>
                            <p style="font-size:11px;">Recibió Material</p>
                            <p style="font-size:11px;"></p>
                        </div>
                        <div style="width:5%; float:left;">
                        </div>
                        <div style="width:45%; float:right; text-align: center;">
                            <b style="font-size:11px;"><hr></hr></b>
                            <p style="font-size:11px;">Técnico Recibe Material</p>
                            <p style="font-size:11px;"></p>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </body>
    @endforeach
</html>
