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
        hr {
            border: 1px solid black;
            border-radius: 1px;
            margin-top:-3px;
        }
    </style>
    @foreach($data as $d)
    <body>
        <div class="saltopagina">
            <section style="width:93%;margin-left:30px;">
                <div id ="contenedor" style="text-align:center;">
					<table  style="width: 100%;max-width: 100%;height:103px;">
						<tr>
							<td style="width:10%" ></td>
							<td style="width:90%;text-align:center;"></td>
						</tr>
					</table>
                </div>
                <div id ="contenedor">
					<table>
						<tr>
							<td style="width:20%"></td>
							<td style="width:30%"></td>
							<td style="float:right;width:50%;">
								<tabl style="width: 100%;max-width: 100%;height:70px;">
									<tr>
										<td style="text-align:center;"></td>
										<td><b style="text-align:center;color:red"><p></p></b></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
                </div>
                <div id ="contenedor">
					<table style="width: 100%;max-width: 100%;">
						<tr>
							<td style="width:18%">
								<table style="width: 100%;max-width: 100%;font-size:10px;">
									<tr>
										<td style="font-size:8px;text-align:center;width:33%;"><p>{{$d['dia']}}<p></td>
										<td style="font-size:8px;text-align:center;width:33%;"><p>{{$d['mes']}}<p></td>
										<td style="font-size:8px;text-align:center;width:33%;"><p>{{$d['anio']}}<p></td>
									</tr>
								</table>
							</td>
							<td style="width:35%">
                                <table style="width: 100%;max-width: 100%;font-size:10px;">
                                    <tr>
                                        <td style="font-size:11px;text-align:center;width:30%;"><b style="font-size:10px;"></b></td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:11px;text-align:center;width:30%;"><p style="font-size:10px;text-align:center;"></p></td>
                                    </tr>
                                </table>   
							</td>
							<td  style="width:47%" style="float:right;">
								<table style="width: 100%;max-width: 100%;font-size:11px">
									<tr>
										<td style="font-size:8px;text-align:center;width:20%;"><p>{{$d['referencia']}}<p></td>
										<td style="font-size:8px;text-align:center;width:20%;"><p>{{$d['ordenservicio']}}<p></td>
										<td style="font-size:8px;text-align:center;width:20%;"><p>{{$d['equipo']}}<p></td>
										<td style="font-size:8px;text-align:center;width:20%;"><p><p></td>
										<td style="font-size:8px;text-align:center;width:20%;"><p><p></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
                </div>
                <div id ="contenedor" style="height:280px;">
                    <table style="width: 100%;max-width: 100%;margin-top:27px;table-layout: fixed;">
                        <tbody style="font-size:7.5px;">
                            @foreach($d['datadetalle'] as $rd)
                            <tr style="font-size:7.5px;height:17px !important;text-align:center;">
                                <td style="width:12% !important;">{{$rd['insumodetalle']}}</td>
                                <td style="width:10% !important;">{{$rd['codigodetalle']}}</td>
                                <td style="width:39% !important;text-align:left;">&nbsp;&nbsp;&nbsp;{{$rd['descripciondetalle']}}</td>
                                <td style="width:13% !important;">{{ number_format($rd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td style="width:13% !important;">{{ number_format($rd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td style="width:13% !important;"></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="height:100%;width: 100%;">
                        <img src="{!! public_path('images/lineascancelacion.png') !!}">

                    </div>
                    
                </div>
            </section>
        </div>
    </body>
    @endforeach
</html>
