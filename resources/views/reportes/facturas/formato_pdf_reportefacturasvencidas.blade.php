<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="16x16">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="32x32">
        <title>Formato | Reporte Facturas Vencidas {{$data['reporte']}}</title>
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
    <body>
    @if($data['reporte'] == 'GENERAL')
        <div class="saltopagina">
            <section>
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="90px" height="70px">
                    </div>
                    <div style="float:left;width:60%;text-align: center;">
                        <b style="font-size:13px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                        <p style="font-size:11px;">Reporte Facturas Vencidas {{$data['reporte']}}</p>
                    </div>
                    <div style="float:right;width:20%;text-align: right;">
                        <p style="font-size:10px;"></p>
                    </div>
                </div><br><br>
                <div id ="contenedor">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Factura</th>
                                <th>Esquema</th>
                                <th>Status</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Depto</th>
                                <th>FormaPago</th>
                                <th>SubTotal</th>
                                <th>Iva</th>
                                <th>Total</th>
                                <th>Abonos</th>
                                <th>Descuentos</th>
                                <th>Saldos</th>
                            </tr>
                            <?php 
                                $sumasubtotal = 0; 
                                $sumaiva = 0; 
                                $sumatotal = 0; 
                                $sumaabonos = 0; 
                                $sumadescuentos = 0; 
                                $sumasaldo = 0;
                                $numfila = 1;
                            ?>
                            @foreach($data['consultarep'] as $cr)
                                @if($numfila % 2 == 0)
                                <tr style="font-size:9px;background-color:#dddddd;">
                                @else
                                <tr style="font-size:9px;">
                                @endif
                                    <td>{{$cr['Factura']}}</td>
                                    <td>{{$cr['Esquema']}}</td>
                                    <td>{{$cr['Status']}}</td>
                                    <td>{{$cr['NombreCliente']}}</td>
                                    <td>{{$cr['Fecha']}}</td>
                                    <td>{{$cr['Depto']}}</td>
                                    <td>{{$cr['FormaPago']}}</td>
                                    <td style="text-align: right;">{{ number_format($cr['SubTotal'], $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr['Iva'], $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr['Total'], $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr['Abonos'], $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr['Descuentos'], $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr['Saldo'], $data['numerodecimales']) }}</td>
                                </tr>
                                <?php 
                                    $sumasubtotal = $sumasubtotal + $cr['SubTotal']; 
                                    $sumaiva = $sumaiva + $cr['Iva']; 
                                    $sumatotal = $sumatotal + $cr['Total']; 
                                    $sumaabonos = $sumaabonos + $cr['Abonos']; 
                                    $sumadescuentos = $sumadescuentos + $cr['Descuentos']; 
                                    $sumasaldo = $sumasaldo + $cr['Saldo']; 
                                    $numfila++;
                                ?>
                            @endforeach
                            <tr>
                                <td colspan="7" style="font-size:12px;text-align: right;">Totales:</td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumasubtotal, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumaiva, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumatotal, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumaabonos, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumadescuentos, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumasaldo, $data['numerodecimales']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    @endif
    
    </body>
</html>
