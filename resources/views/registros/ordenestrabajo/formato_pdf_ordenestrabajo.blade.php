<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Orden Trabajo</title>
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
                <div id ="contenedor">
					<p style="font-size:11px; margin-left: 5px;"> <b>Se Factura a:</b></p>
                    <div style="width:53%; height:110px; float:left; text-align: left; border-style: groove;">
						<ul style="list-style:none;margin-left:-35px;margin-top:5px;">
							<li style="font-size:9px; margin-left: 5px;"> {{$d['cliente']->Nombre}} ({{$d['ordentrabajo']->Cliente}}), {{$d['cliente']->Calle}} {{$d['cliente']->noExterior}}, {{$d['cliente']->Colonia}}</b></li>
							<li style="font-size:9px; margin-left: 5px;"> Pedido: {{$d['ordentrabajo']->Pedido}}</li>
							<li style="font-size:9px; margin-left: 5px;"> Campaña: {{$d['ordentrabajo']->Campaña}}</li>
						</ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:110px; float:left; text-align: left; border-style: groove;">
						<ul style="list-style:none;margin-left:-35px;margin-top:5px;">
							<li style="font-size:18px; margin-left: 5px;"><b>Orden Trabajo: </b><b style="color:red">{{$d['ordentrabajo']->Orden}}</b></li>
							<li style="font-size:9px; margin-left: 5px;">Tipo: {{$d['ordentrabajo']->Tipo}}</li>
							<li style="font-size:9px; margin-left: 5px;">Entrada: {{$d['ordentrabajo']->Fecha}}</li>
							<li style="font-size:9px; margin-left: 5px;">Entrega: {{$d['ordentrabajo']->Entrega}}</li>
							<li style="font-size:9px; margin-left: 5px;">Facturada: {{$d['ordentrabajo']->Facturada}}</li>
							<li style="font-size:9px; margin-left: 5px;">Reclamo: {{$d['ordentrabajo']->Reclamo}}</li>
							<li style="font-size:9px; margin-left: 5px;">Status: {{$d['ordentrabajo']->Status}}</li>
						</ul>
                    </div>
                </div>
				<div id ="contenedor">
					<p style="font-size:11px; margin-left: 5px;"> <b>Propietario:</b></p>
                    <div style="width:98.5%; height:75px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:9px; margin-left: 5px;">{{$d['cliente']->Nombre}} ({{$d['ordentrabajo']->Cliente}})</p>
                        <p style="font-size:9px; margin-left: 5px;"> Obs: </p>
                    </div>
                </div>
				<div id ="contenedor">
					<p style="font-size:11px; margin-left: 5px;"> <b>Datos de la unidad:</b></p>
                    <div style="width:98.5%; height:60px; float:left; text-align: left; border-style: groove;">
                        <table style="width:100%;">
							<tr style="text-align:center">
								<td style="font-size:9px;width:25%">Serie/VIN: {{$d['ordentrabajo']->Vin}}</td>
								<td style="font-size:9px;width:25%">Motor: {{$d['ordentrabajo']->Motor}}</td>
								<td style="font-size:9px;width:25%">Año: {{$d['ordentrabajo']->Año}}</td>
								<td style="font-size:9px;width:25%">Kms: {{$d['ordentrabajo']->Kilometros}}</td>
							</tr>
							<tr style="text-align:center">
								<td style="font-size:9px;width:25%">Marca: {{$d['ordentrabajo']->Marca}}</td>
								<td style="font-size:9px;width:25%">Modelo: {{$d['ordentrabajo']->Modelo}}</td>
								<td style="font-size:9px;width:25%">Placas: {{$d['ordentrabajo']->Placas}}</td>
								<td style="font-size:9px;width:25%">No. Económico: {{$d['ordentrabajo']->Economico}}</td>
							</tr>
						</table>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:85px;">
                    <p style="font-size:11px; margin-left: 5px;"> <b>Reporte de Servicios:</b></p>
                    <table style="width: 99%;max-width: 100%;border: 1px solid #ddd;">
						<tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Unidad</th>
                                <th>% D</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>SubTotal</th>
                                <th>Descuento</th>
								<th>Total</th>
                            </tr>
                            @foreach($d['datadetalle'] as $otd)
                            <tr style="font-size:10px;">
                                <td>{{$otd['codigodetalle']}}</td>
                                <td>{{$otd['descripciondetalle']}}</td>
                                <td>{{$otd['unidaddetalle']}}</td>
                                <td></td>
                                <td>{{ number_format($otd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($otd['preciodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($otd['subtotaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($otd['descuentodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($otd['totaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">Importe $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;"><b>{{ number_format($d['importeordentrabajo'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">Descuento $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;"><b>{{ number_format($d['descuentoordentrabajo'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">SubTotal $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;"><b>{{ number_format($d['subtotalordentrabajo'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">IVA $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;"><b>{{ number_format($d['ivaordentrabajo'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">Total $ : </td>
                                <td colspan="2" style="font-size:11px;text-align: right;"><b>{{ number_format($d['totalordentrabajo'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
						</tbody>
                    </table>
                </div>
				<div id ="contenedor" style="margin-top:15px;">
					<p style="font-size:11px; margin-left: 5px;"> <b>Detalles de la unidad:</b></p>
                    <div style="width:49%;  float:left; text-align: left; border-style: groove;">
						<table style="width:100%;">
							<thead style="background-color:#a6a6b3; font-size:10px;">
								<tr style="text-align:center">
									<td style="width:40%"></td>
									<td style="width:20%">Bueno</td>
									<td style="width:20%">Malo</td>
									<td style="width:20%">Falta</td>
								</tr>
							</thead>
							<tbody style="font-size:9px; text-align: justify;">
								<tr style="text-align:center">
									<td style="width:40%">DEFENSA DELANTERA</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">FAROS</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">COFRE/PARRILLA</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">VIDRIOS</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">PUERTAS</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">ESPEJOS</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">ANTENAS</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">MICAS</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">FALDONES</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">LLANTAS</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">COMBUSTIBLE</td>
									<td style="width:20%">{{$d['ordentrabajo']->Combustible}} %</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
							</tbody>
						</table>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:49%; float:left; text-align: left; border-style: groove;">
                        <table style="width:100%;">
							<thead style="background-color:#a6a6b3; font-size:10px;">
								<tr style="text-align:center">
									<td style="width:40%"></td>
									<td style="width:20%">Bueno</td>
									<td style="width:20%">Malo</td>
									<td style="width:20%">Falta</td>
								</tr>
							</thead>
							<tbody style="font-size:9px; text-align: justify;">
								<tr style="text-align:center">
									<td style="width:40%">VESTIDURAS</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">STEREO</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">C.B. BANDA CORTA</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">CINTAS Y CD'S</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">HERRAMIENTAS</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">GATO</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">LLANTAS REFACCIÓN</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">POLVERAS, TAPONES</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">DEFENSA TRASERA</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
								<tr style="text-align:center">
									<td style="width:40%">T.V.</td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
									<td style="width:20%"></td>
								</tr>
							</tbody>
						</table>
                    </div>
                </div>
                <div id ="contenedor" style="width:100%;margin-top:100px;">
                    <div style="width:98.5%; float:left; text-align: left;">
					    <p style="font-size:11px; margin-left: 5px;"> <b>Observaciones de la unidad:</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> {{$d['ordentrabajo']->ObsUnidad}}</p>
                    </div>
                </div>
                <div id ="contenedor" style="width:100%;margin-top:100px;">
                    <div style="width:98.5%; float:left; text-align: left;">
					    <p style="font-size:11px; margin-left: 5px;"> <b>Observaciones de la orden:</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> {{$d['ordentrabajo']->ObsOrden}}</p>
                    </div>
                </div>
            </section>
        </div>
    </body>
    @endforeach
</html>