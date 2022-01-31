<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="16x16">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="32x32">
        <title>Formato | Reporte Relación Cuentas Por Pagar {{$data['reporte']}}</title>
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
    @switch($data['reporte'])
        @case('AGRUPARxPROVEEDOR')
            <div class="saltopagina">
                <section>
                    <div id ="contenedor">
                        <div style="float:left;width:20%;text-align: left;">
                        <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="90px" height="70px">
                        </div>
                        <div style="float:left;width:60%;text-align: center;">
                            <b style="font-size:13px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                            <p style="font-size:11px;">Reporte Relación CuentasPorPagar {{$data['reporte']}} de {{$data['fechainicio']}} a {{$data['fechaterminacion']}}</p>
                        </div>
                        <div style="float:right;width:20%;text-align: right;">
                            <p style="font-size:10px;"></p>
                        </div>
                    </div><br><br>
                    <div id ="contenedor">
                        <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                            <tbody style="font-size:9px; text-align: justify;">
                                <?php 
                                    $numfila = 1;
                                    $numproveedor = 0;
                                    $sumatotalabono = 0;
                                    $sumatotalsaldo = 0;
                                    $sumaabono = 0;
                                ?>
                                @foreach($data['consultarep'] as $cr)
                                    @if($numproveedor != $cr->Numero)
                                        <?php 
                                            $sumaabono = $sumaabono + $cr->Abono; 
                                            $numproveedor = $cr->Numero;
                                            $sumatotalsaldo = $sumatotalsaldo + $cr->SaldoProveedor;
                                        ?>
                                        <tr style="font-size:11px;">
                                            <td colspan="8"><b>{{$cr->Proveedor}}  ({{$cr->Numero}})</b></td>
                                            <td><b>{{$cr->SaldoProveedor}}</b></td>
                                        </tr>
                                        <tr style="background-color:#a6a6b3;font-size:10px;">
                                            <th>Fecha</th>
                                            <th>Remision</th>
                                            <th>Factura</th>
                                            <th>Transferencia</th>
                                            <th>Cheque</th>
                                            <th>Abono $</th>
                                            <th>Banco</th>
                                            <th>Anotación</th>
                                            <th>Saldo $</th>
                                        </tr>
                                    @endif
                                    @if($numfila % 2 == 0)
                                    <tr style="font-size:9px;background-color:#dddddd;">
                                    @else
                                    <tr style="font-size:9px;">
                                    @endif
                                        <td>{{$cr->Fecha}}</td>
                                        <td>{{$cr->Remision}}</td>
                                        <td>{{$cr->Factura}}</td>
                                        <td>{{$cr->Transferencia}}</td>
                                        <td>{{$cr->Cheque}}</td>
                                        <td style="text-align: right;">{{ number_format($cr->Abono, $data['numerodecimales']) }}</td>
                                        <td>{{$cr->Banco}}</td>
                                        <td>{{$cr->Anotacion}}</td>
                                        <td></td>
                                    </tr>
                                    <?php 
                                        $sumatotalabono = $sumatotalabono + $cr->Abono;
                                        $numfila++;
                                    ?>
                                    @if($numproveedor != $cr->Numero)
                                        <tr style="font-size:10px;">
                                            <td colspan="5"></td>
                                            <td ><b>{{$sumaabono}}</b></td>
                                            <td></td>
                                            <td></td>
                                            <td><b></b></td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td colspan="5" style="font-size:12px;text-align: right;">Totales:</td>
                                    <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumatotalabono, $data['numerodecimales']) }}</b></td>
                                    <td colspan="2" style="font-size:12px;text-align: right;"></td>
                                    <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumatotalsaldo, $data['numerodecimales']) }}</b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        @break
        @case('AGRUPARxBANCO')

        @break
        @case('RELACIONPAGOS')
            <div class="saltopagina">
                <section>
                    <div id ="contenedor">
                        <div style="float:left;width:20%;text-align: left;">
                        <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="90px" height="70px">
                        </div>
                        <div style="float:left;width:60%;text-align: center;">
                            <b style="font-size:13px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                            <p style="font-size:11px;">Reporte Relación CuentasPorPagar {{$data['reporte']}} de {{$data['fechainicio']}} a {{$data['fechaterminacion']}}</p>
                        </div>
                        <div style="float:right;width:20%;text-align: right;">
                            <p style="font-size:10px;"></p>
                        </div>
                    </div><br><br>
                    <div id ="contenedor">
                        <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                            <tbody style="font-size:9px; text-align: justify;">
                                <tr style="background-color:#a6a6b3;font-size:10px;">
                                    <th>Pago</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Transferencia</th>
                                    <th>Cheque</th>
                                    <th>Beneficiario</th>
                                    <th>Abono $</th>
                                    <th>Anotación</th>
                                    <th>Status</th>
                                </tr>
                                <?php 
                                    $sumaabono = 0; 
                                    $numfila = 1;
                                ?>
                                @foreach($data['consultarep'] as $cr)
                                    @if($numfila % 2 == 0)
                                    <tr style="font-size:8px;background-color:#dddddd;">
                                    @else
                                    <tr style="font-size:8px;">
                                    @endif
                                        <td>{{$cr->Pago}}</td>
                                        <td>{{$cr->Fecha}}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($cr->Proveedor, $limit = 30, $end = '...') }}</td>
                                        <td>{{$cr->Transferencia}}</td>
                                        <td>{{$cr->Cheque}}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($cr->Beneficiario, $limit = 30, $end = '...') }}</td>
                                        <td style="text-align: right;">{{ number_format($cr->Abono, $data['numerodecimales']) }}</td>
                                        <td>{{$cr->Anotacion}}</td>
                                        <td>{{$cr->Status}}</td>
                                    </tr>
                                    <?php 
                                        $sumaabono = $sumaabono + $cr->Abono; 
                                        $numfila++;
                                    ?>
                                @endforeach
                                <tr>
                                    <td colspan="6" style="font-size:12px;text-align: right;">Totales:</td>
                                    <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumaabono, $data['numerodecimales']) }}</b></td>
                                    <td colspan="2" style="font-size:12px;text-align: right;"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        @break
    @endswitch
    </body>
</html>