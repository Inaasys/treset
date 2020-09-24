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
			letter-spacing: 2px;
	        font-family: 'Roboto', Arial, Tahoma, sans-serif;
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
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Calle}} {{$empresa->NoExterior}} </b>
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Municipio}} {{$empresa->Estado}}, {{$empresa->Pais}} CP: {{$empresa->LugarExpedicion}}</b>
                    </div>
                    <div style="float:right;width:20%;text-align: right;">
                        <p style="font-size:10px;"></p>
                    </div>
                </div><br><br><br><br><br>
                <div>
                    <hr></hr>
                </div>
                <div id ="contenedor">
                    <div style="width:100%; float:right; text-align: right;">
                        <p style="font-size:15px;">ORDEN TRABAJO</p>
                        <b style="font-size:10px;"></b>
                    </div>
                </div>
                <div id ="contenedor">
					<p style="font-size:13px; margin-left: 5px;"> <b>Se Factura a:</b></p>
                    <div style="width:68%; height:175px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;"> <b>{{$d['cliente']->Nombre}} ({{$d['ordentrabajo']->Cliente}}), {{$d['cliente']->Calle}} {{$d['cliente']->noExterior}}, {{$d['cliente']->Colonia}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Pedido: <b>{{$d['ordentrabajo']->Pedido}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Campaña: <b>{{$d['ordentrabajo']->Campaña}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Tels: <b></b></p>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:30%; height:175px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;">Orden: <b>{{$d['ordentrabajo']->Orden}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Tipo: <b>{{$d['ordentrabajo']->Tipo}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Entrada: <b>{{$d['ordentrabajo']->Fecha}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Entrega: <b>{{$d['ordentrabajo']->Entrega}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Facturada: <b>{{$d['ordentrabajo']->Facturada}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Reclamo: <b>{{$d['ordentrabajo']->Reclamo}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Status: <b>{{$d['ordentrabajo']->Status}}</b></p>
                    </div>
                </div>
				<div id ="contenedor" style="margin-top:200px;">
					<p style="font-size:13px; margin-left: 5px;"> <b>Propietario:</b></p>
                    <div style="width:98.5%; height:75px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;"><b>{{$d['cliente']->Nombre}} ({{$d['ordentrabajo']->Cliente}})</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Obs: </p>
                    </div>
                </div>
				<div id ="contenedor" style="margin-top:95px;">
					<p style="font-size:13px; margin-left: 5px;"> <b>Datos de la unidad:</b></p>
                    <div style="width:98.5%; height:60px; float:left; text-align: left; border-style: groove;">
                        <table style="width:100%;">
							<tr style="text-align:center">
								<td style="font-size:11px;width:25%">Serie/VIN: <b>{{$d['ordentrabajo']->Vin}}</b></td>
								<td style="font-size:11px;width:25%">Motor: <b>{{$d['ordentrabajo']->Motor}}</b></td>
								<td style="font-size:11px;width:25%">Año: <b>{{$d['ordentrabajo']->Año}}</b></td>
								<td style="font-size:11px;width:25%">Kms: <b>{{$d['ordentrabajo']->Kilometros}}</b></td>
							</tr>
							<tr style="text-align:center">
								<td style="font-size:11px;width:25%">Marca: <b>{{$d['ordentrabajo']->Marca}}</b></td>
								<td style="font-size:11px;width:25%">Modelo: <b>{{$d['ordentrabajo']->Modelo}}</b></td>
								<td style="font-size:11px;width:25%">Placas: <b>{{$d['ordentrabajo']->Placas}}</b></td>
								<td style="font-size:11px;width:25%">No. Económico: <b>{{$d['ordentrabajo']->Economico}}</b></td>
							</tr>
						</table>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:85px;">
                    <p style="font-size:13px; margin-left: 5px;"> <b>Reporte de Servicios:</b></p>
                    <table style="width: 99%;max-width: 100%;border: 1px solid #ddd;">
                        
                            <tr style="background-color:#a6a6b3; font-size:11px;">
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
                            <tr style="background-color:#ddd; font-size:11px; text-align: center;">
                                <td>{{$otd['codigodetalle']}}</td>
                                <td>{{$otd['descripciondetalle']}}</td>
                                <td>{{$otd['unidaddetalle']}}</td>
                                <td></td>
                                <td>{{$otd['cantidaddetalle']}}</td>
                                <td>{{$otd['preciodetalle']}}</td>
                                <td>{{$otd['subtotaldetalle']}}</td>
                                <td>{{$otd['descuentodetalle']}}</td>
                                <td>{{$otd['totaldetalle']}}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Importe $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['importeordentrabajo']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Descuento $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['descuentoordentrabajo']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">SubTotal $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['subtotalordentrabajo']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">IVA $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['ivaordentrabajo']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Total $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['totalordentrabajo']}}</b></td>
                            </tr>
                    </table>
                </div>
				<div id ="contenedor" style="margin-top:15px;">
					<p style="font-size:13px; margin-left: 5px;"> <b>Detalles de la unidad:</b></p>
                    <div style="width:49%;  float:left; text-align: left; border-style: groove;">
						<table style="width:100%;">
							<thead style="background-color:#a6a6b3; font-size:11px;">
								<tr style="text-align:center">
									<td style="width:40%"></td>
									<td style="width:20%">Bueno</td>
									<td style="width:20%">Malo</td>
									<td style="width:20%">Falta</td>
								</tr>
							</thead>
							<tbody style="background-color:#ddd; font-size:11px; text-align: center;">
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
							<thead style="background-color:#a6a6b3; font-size:11px;">
								<tr style="text-align:center">
									<td style="width:40%"></td>
									<td style="width:20%">Bueno</td>
									<td style="width:20%">Malo</td>
									<td style="width:20%">Falta</td>
								</tr>
							</thead>
							<tbody style="background-color:#ddd; font-size:11px; text-align: center;">
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
					    <p style="font-size:13px; margin-left: 5px;"> <b>Observaciones de la unidad:</b></p>
                        <p style="font-size:13px; margin-left: 5px;"> {{$d['ordentrabajo']->ObsUnidad}}</p>
                    </div>
                </div>
                <div id ="contenedor" style="width:100%;margin-top:100px;">
                    <div style="width:98.5%; float:left; text-align: left;">
					    <p style="font-size:13px; margin-left: 5px;"> <b>Observaciones de la orden:</b></p>
                        <p style="font-size:13px; margin-left: 5px;"> {{$d['ordentrabajo']->ObsOrden}}</p>
                    </div>
                </div>
            </section>
        </div>
    </body>
    @endforeach
</html>