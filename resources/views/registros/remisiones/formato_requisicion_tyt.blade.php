<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="16x16">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="32x32">
        <title>Formato | Requisición TYT</title>
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
            <section style="border-style:groove;">
                <div id ="contenedor" style="text-align:center; border-style:groove;">
					REQUISICIÓN DE REFACCIONES Y MATERIALES DE ALMACEN
                </div>
                <div id ="contenedor">
					<table>
						<tr>
							<td style="width:20%"><img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="200px" height="55px"></td>
							<td style="width:30%"></td>
							<td style="float:right;width:50%;">
								<table border="1" style="width: 100%;max-width: 100%;">
									<tr>
										<td style="text-align:center;">STQ</td>
										<td><b style="text-align:center;color:red"><p>(T,L,C) FOLIO</p></b></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
                </div>
                <div id ="contenedor">
					<table style="width: 100%;max-width: 100%;">
						<tr>
							<td style="width:20%">
								<table border="1" style="width: 100%;max-width: 100%;font-size:10px;">
									<tr>
										<td style="font-size:11px;text-align:center;width:30%;">Día</td>
										<td style="font-size:11px;text-align:center;width:30%;">Mes</td>
										<td style="font-size:11px;text-align:center;width:40%;">Año</td>
									</tr>
									<tr>
										<td style="font-size:11px;text-align:center;width:30%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:30%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:40%;"><p>2021<p></td>
									</tr>
								</table>
							</td>
							<td style="width:40%">
                                <table style="width: 100%;max-width: 100%;font-size:10px;">
                                    <tr>
                                        <td style="font-size:11px;text-align:center;width:30%;"><b style="font-size:10px;"><hr></hr></b></td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:11px;text-align:center;width:30%;"><p style="font-size:10px;text-align:center;">Motivo por el cual no se entrega el cambio</p></td>
                                    </tr>
                                </table>   
							</td>
							<td  style="width:40%" style="float:right;">
								<table border="1" style="width: 100%;max-width: 100%;font-size:11px">
									<tr>
										<td style="font-size:11px;text-align:center;width:20%;">0.6</td>
										<td style="font-size:11px;text-align:center;width:20%;">O.T.</td>
										<td style="font-size:11px;text-align:center;width:20%;">Equipo</td>
										<td style="font-size:11px;text-align:center;width:20%;">T.D.R.</td>
										<td style="font-size:11px;text-align:center;width:20%;">Cambio</td>
									</tr>
									<tr>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
                </div>
                <div id ="contenedor">
                    <table border="1" style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Clave</th>
                                <th>Código ó No. de parte</th>
                                <th>Descripción</th>
                                <th>Cantidad Solicitada</th>
                                <th>Cantidad Entregada</th>
                                <th>Pendientes de Entrega</th>
                            </tr>
                            @foreach($d['datadetalle'] as $rd)
                            <tr style="font-size:10px;">
                                <td>{{$rd['insumodetalle']}}</td>
                                <td>{{$rd['codigodetalle']}}</td>
                                <td>{{$rd['descripciondetalle']}}</td>
                                <td>{{ number_format($rd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" style="font-size:11px">
									<table border="1" style="width: 100%;max-width: 100%;">
										<tr>
											<td style="font-size:11px;text-align:center;" width="25%">Elaboró</td>
											<td style="font-size:11px;text-align:center;" width="25%">Autorizó</td>
											<td style="font-size:11px;text-align:center;" width="25%">Recibió</td>
											<td style="font-size:11px;text-align:center;" width="25%">Revisa Almacén TYT</td>
										</tr>
										<tr>
											<td style="font-size:11px;text-align:center;" width="25%"><br><br><br><br>Puesto y Firma</td>
											<td style="font-size:11px;text-align:center;" width="25%"><br><br><br><br>Puesto y Firma</td>
											<td style="font-size:11px;text-align:center;" width="25%"><br><br><br><br>Puesto y Firma</td>
											<td style="font-size:11px;text-align:center;" width="25%"><br><br><br><br>Puesto y Firma</td>
										</tr>
									</table>
								</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <br>
            
            <section style="border-style:groove;">
                <div id ="contenedor" style="text-align:center; border-style:groove;">
					REQUISICIÓN DE REFACCIONES Y MATERIALES DE ALMACEN
                </div>
                <div id ="contenedor">
					<table>
						<tr>
							<td style="width:20%"><img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="200px" height="55px"></td>
							<td style="width:30%"></td>
							<td style="float:right;width:50%;">
								<table border="1" style="width: 100%;max-width: 100%;">
									<tr>
										<td style="text-align:center;">STQ</td>
										<td><b style="text-align:center;color:red"><p>(T,L,C) FOLIO</p></b></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
                </div>
                <div id ="contenedor">
					<table style="width: 100%;max-width: 100%;">
						<tr>
							<td style="width:20%">
								<table border="1" style="width: 100%;max-width: 100%;font-size:10px;">
									<tr>
										<td style="font-size:11px;text-align:center;width:30%;">Día</td>
										<td style="font-size:11px;text-align:center;width:30%;">Mes</td>
										<td style="font-size:11px;text-align:center;width:40%;">Año</td>
									</tr>
									<tr>
										<td style="font-size:11px;text-align:center;width:30%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:30%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:40%;"><p>2021<p></td>
									</tr>
								</table>
							</td>
							<td style="width:40%">
                                <table style="width: 100%;max-width: 100%;font-size:10px;">
                                    <tr>
                                        <td style="font-size:11px;text-align:center;width:30%;"><b style="font-size:10px;"><hr></hr></b></td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:11px;text-align:center;width:30%;"><p style="font-size:10px;text-align:center;">Motivo por el cual no se entrega el cambio</p></td>
                                    </tr>
                                </table>   
							</td>
							<td  style="width:40%" style="float:right;">
								<table border="1" style="width: 100%;max-width: 100%;font-size:11px">
									<tr>
										<td style="font-size:11px;text-align:center;width:20%;">0.6</td>
										<td style="font-size:11px;text-align:center;width:20%;">O.T.</td>
										<td style="font-size:11px;text-align:center;width:20%;">Equipo</td>
										<td style="font-size:11px;text-align:center;width:20%;">T.D.R.</td>
										<td style="font-size:11px;text-align:center;width:20%;">Cambio</td>
									</tr>
									<tr>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
										<td style="font-size:11px;text-align:center;width:20%;"><p>01<p></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
                </div>
                <div id ="contenedor">
                    <table border="1" style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Clave</th>
                                <th>Código ó No. de parte</th>
                                <th>Descripción</th>
                                <th>Cantidad Solicitada</th>
                                <th>Cantidad Entregada</th>
                                <th>Pendientes de Entrega</th>
                            </tr>
                            @foreach($d['datadetalle'] as $rd)
                            <tr style="font-size:10px;">
                                <td>{{$rd['insumodetalle']}}</td>
                                <td>{{$rd['codigodetalle']}}</td>
                                <td>{{$rd['descripciondetalle']}}</td>
                                <td>{{ number_format($rd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" style="font-size:11px">
									<table border="1" style="width: 100%;max-width: 100%;">
										<tr>
											<td style="font-size:11px;text-align:center;" width="25%">Elaboró</td>
											<td style="font-size:11px;text-align:center;" width="25%">Autorizó</td>
											<td style="font-size:11px;text-align:center;" width="25%">Recibió</td>
											<td style="font-size:11px;text-align:center;" width="25%">Revisa Almacén TYT</td>
										</tr>
										<tr>
											<td style="font-size:11px;text-align:center;" width="25%"><br><br><br><br>Puesto y Firma</td>
											<td style="font-size:11px;text-align:center;" width="25%"><br><br><br><br>Puesto y Firma</td>
											<td style="font-size:11px;text-align:center;" width="25%"><br><br><br><br>Puesto y Firma</td>
											<td style="font-size:11px;text-align:center;" width="25%"><br><br><br><br>Puesto y Firma</td>
										</tr>
									</table>
								</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </body>
    @endforeach
</html>
