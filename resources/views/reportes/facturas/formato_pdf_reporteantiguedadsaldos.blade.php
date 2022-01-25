<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="16x16">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="32x32">
        <title>Formato | Reporte Antiguedad de Saldos {{$data['reporte']}}</title>
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
                        <p style="font-size:11px;">Reporte Antiguedad de Saldos {{$data['reporte']}}</p>
                    </div>
                    <div style="float:right;width:20%;text-align: right;">
                        <p style="font-size:10px;"></p>
                    </div>
                </div><br><br>
                <div id ="contenedor">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Cliente</th>
                                <th>Total Facturas</th>
                                <th>Pagos CXC</th>
                                <th>Pagos NC</th>
                                <th>Total Pagos</th>
                                <th>Saldo</th>
                            </tr>
                            <?php 
                                $totalfacturas = 0; 
                                $totalcxc = 0; 
                                $totalnc = 0; 
                                $totalpagos = 0; 
                                $totalsaldos = 0; 
                            ?>
                            @foreach($data['consultarep'] as $cr)
                                <tr style="font-size:9px;">
                                    <td>{{$cr->NombreCliente}}</td>
                                    <td style="text-align: right;">{{ number_format($cr->Facturado, $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr->AbonosCXC, $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr->DescuentosNotasCredito, $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr->TotalPagos, $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr->SaldoFacturado, $data['numerodecimales']) }}</td>
                                </tr>
                                <?php 
                                    $totalfacturas = $totalfacturas + $cr->Facturado; 
                                    $totalcxc = $totalcxc + $cr->AbonosCXC; 
                                    $totalnc = $totalnc + $cr->DescuentosNotasCredito; 
                                    $totalpagos = $totalpagos + $cr->TotalPagos; 
                                    $totalsaldos = $totalsaldos + $cr->SaldoFacturado; 
                                ?>
                            @endforeach
                            <tr>
                                <td style="font-size:12px;text-align: right;">Totales:</td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalfacturas, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalcxc, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalnc, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalpagos, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalsaldos, $data['numerodecimales']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

    @else
        <div class="saltopagina">
            <section>
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="90px" height="70px">
                    </div>
                    <div style="float:left;width:60%;text-align: center;">
                        <b style="font-size:13px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                        <p style="font-size:11px;">Reporte Antiguedad de Saldos {{$data['reporte']}}</p>
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
                                <th>Fecha</th>
                                <th>Días</th>
                                <th>Cliente</th>
                                <th>Total Facturas</th>
                                <th>Pagos CXC</th>
                                <th>Pagos NC</th>
                                <th>Total Pagos</th>
                                <th>Saldo</th>
                            </tr>
                            <?php 
                                $totalfacturas = 0; 
                                $totalcxc = 0; 
                                $totalnc = 0; 
                                $totalpagos = 0; 
                                $totalsaldos = 0; 
                            ?>
                            @foreach($data['consultarep'] as $cr)
                                <tr style="font-size:9px;">
                                    <td>{{$cr->Factura}}</td>
                                    <td>{{$cr->Fecha}}</td>
                                    <td>{{$cr->Plazo}}</td>
                                    <td>{{$cr->NombreCliente}}</td>
                                    <td style="text-align: right;">{{ number_format($cr->TotalFactura, $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr->AbonosCXC, $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr->DescuentosNotasCredito, $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr->TotalPagos, $data['numerodecimales']) }}</td>
                                    <td style="text-align: right;">{{ number_format($cr->SaldoFacturado, $data['numerodecimales']) }}</td>
                                </tr>
                                <?php 
                                    $totalfacturas = $totalfacturas + $cr->TotalFactura; 
                                    $totalcxc = $totalcxc + $cr->AbonosCXC; 
                                    $totalnc = $totalnc + $cr->DescuentosNotasCredito; 
                                    $totalpagos = $totalpagos + $cr->TotalPagos; 
                                    $totalsaldos = $totalsaldos + $cr->SaldoFacturado; 
                                ?>
                            @endforeach
                            <tr>
                                <td colspan="4" style="font-size:12px;text-align: right;">Totales:</td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalfacturas, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalcxc, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalnc, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalpagos, $data['numerodecimales']) }}</b></td>
                                <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($totalsaldos, $data['numerodecimales']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    @endif
    </body>
</html>
