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
        @case('AGRUPARxCLIENTES')
            <div class="saltopagina">
                <section>
                    <div id ="contenedor">
                        <div style="float:left;width:20%;text-align: left;">
                        <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="90px" height="70px">
                        </div>
                        <div style="float:left;width:60%;text-align: center;">
                            <b style="font-size:13px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                            <p style="font-size:11px;">Reporte Relación CuentasPorCobrar {{$data['reporte']}} de {{$data['fechainicio']}} a {{$data['fechaterminacion']}}</p>
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
                                    $numcliente = 0;
                                    $sumatotalabono = 0;
                                    $sumatotalsaldo = 0;
                                    $sumaabono = 0;
                                ?>
                                @foreach($data['consultarep'] as $cr)
                                    @if($numcliente != $cr->NumeroCliente)
                                        <?php 
                                            $sumaabono = $sumaabono + $cr->Abono; 
                                            $numcliente = $cr->NumeroCliente;
                                            $sumatotalsaldo = $sumatotalsaldo + $cr->SaldoCliente;
                                        ?>
                                        <tr style="font-size:11px;">
                                            <td colspan="7"><b>{{$cr->Cliente}}  ({{$cr->NumeroCliente}})</b></td>
                                            <td><b>{{$cr->SaldoCliente}}</b></td>
                                        </tr>
                                        <tr style="background-color:#a6a6b3;font-size:10px;">
                                            <th>Factura</th>
                                            <th>Fecha</th>
                                            <th>Agente</th>
                                            <th>Abono $</th>
                                            <th>Forma de Pago</th>
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
                                        <td>{{$cr->Factura}}</td>
                                        <td>{{$cr->Fecha}}</td>
                                        <td>{{$cr->Agente}}</td>
                                        <td>{{$cr->Abono}}</td>
                                        <td>{{$cr->FormaPago}}</td>
                                        <td>{{$cr->Banco}}</td>
                                        <td>{{$cr->Anotacion}}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <?php 
                                        $sumatotalabono = $sumatotalabono + $cr->Abono;
                                        $numfila++;
                                    ?>
                                    @if($numcliente != $cr->NumeroCliente)
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
                                    <td colspan="3" style="font-size:12px;text-align: right;">Totales:</td>
                                    <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumatotalabono, $data['numerodecimales']) }}</b></td>
                                    <td colspan="3" style="font-size:12px;text-align: right;"></td>
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
        @case('RELACIONDEPAGOS')
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
                                    <th>Cliente</th>
                                    <th>Forma de Pago</th>
                                    <th>Banco</th>
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
                                        <td>{{ \Illuminate\Support\Str::limit($cr->Cliente, $limit = 30, $end = '...') }}</td>
                                        <td>{{$cr->FormaPago}}</td>
                                        <td>{{$cr->Banco}}</td>
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
                                    <td colspan="5" style="font-size:12px;text-align: right;">Totales:</td>
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