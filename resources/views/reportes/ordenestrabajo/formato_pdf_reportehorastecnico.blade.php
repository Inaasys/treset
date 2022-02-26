<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="16x16">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="32x32">
        <title>Formato | Reporte Horas Técnico {{$data['statusorden']}}</title>
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
    @if($data['reporte'] == 'FACTURADAS')
        <div class="saltopagina">
            <section>
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="90px" height="70px">
                    </div>
                    <div style="float:left;width:60%;text-align: center;">
                        <b style="font-size:13px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                        <p style="font-size:11px;">Reporte Horas Técnico {{$data['statusorden']}}</p>
                    </div>
                    <div style="float:right;width:20%;text-align: right;">
                        <p style="font-size:10px;"></p>
                    </div>
                </div><br><br>
                <div id ="contenedor">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            @if($data['todoslostecnicos'] == 1)
                                    <tr style="background-color:#a6a6b3;font-size:10px;">
                                        <th>Técnico</th>
                                        <th>Nombre</th>
                                        <th>Orden</th>
                                        <th>Tipo</th>
                                        <th>Factura</th>
                                        <th>Fecha</th>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Horas</th>
                                        <th>Precio</th>
                                        <th>Total</th>
                                    </tr>
                                    <?php 
                                        $sumahoras = 0; 
                                        $sumatotal = 0; 
                                        $numfila = 1;
                                    ?>
                                    @foreach($data['consultarep'] as $cr)
                                            @if($numfila % 2 == 0)
                                            <tr style="font-size:9px;background-color:#dddddd;">
                                            @else
                                            <tr style="font-size:9px;">
                                            @endif
                                                <td>{{$cr['Tecnico']}}</td>
                                                <td>{{$cr['Nombre']}}</td>
                                                <td>{{$cr['Orden']}}</td>
                                                <td>{{$cr['Tipo']}}</td>
                                                <td>{{$cr['Factura']}}</td>
                                                <td>{{$cr['Fecha']}}</td>
                                                <td>{{$cr['Codigo']}}</td>
                                                <td>{{$cr['Descripcion']}}</td>
                                                <td>{{$cr['Horas']}}</td>
                                                <td>{{$cr['Precio']}}</td>
                                                <td>{{$cr['Total']}}</td>
                                            </tr>
                                            <?php 
                                                $sumahoras = $sumahoras + $cr['Horas']; 
                                                $sumatotal = $sumatotal + $cr['Total']; 
                                                $numfila++;
                                            ?>
                                    @endforeach
                                    <tr>
                                        <td colspan="8" style="font-size:12px;text-align: right;">Totales:</td>
                                        <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumahoras, $data['numerodecimales']) }}</b></td>
                                        <td style="font-size:12px;text-align: right;background-color:#ddd;"><b></b></td>
                                        <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumatotal, $data['numerodecimales']) }}</b></td>
                                    </tr>
                            @else
                                    <?php 
                                        $sumatotalhoras = 0; 
                                        $sumatotaltotal = 0; 
                                    ?>
                                @foreach($data['arraytecnicosseleccionados'] as $ats)
                                    <?php 
                                        $sumahoras = 0; 
                                        $sumatotal = 0; 
                                        $numfila = 1;
                                    ?>
                                    @foreach($data['consultarep'] as $cr)
                                        @if($ats == $cr['Tecnico'])
                                            @if($numfila == 1)
                                            <tr>
                                                <th colspan ="9">{{$cr['Nombre']}} ( {{$cr['Tecnico']}} )</th>
                                            </tr>
                                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                                <th>Orden</th>
                                                <th>Tipo</th>
                                                <th>Factura</th>
                                                <th>Fecha</th>
                                                <th>Código</th>
                                                <th>Descripción</th>
                                                <th>Horas</th>
                                                <th>Precio</th>
                                                <th>Total</th>
                                            </tr>
                                            @endif
                                            @if($numfila % 2 == 0)
                                            <tr style="font-size:9px;background-color:#dddddd;">
                                            @else
                                            <tr style="font-size:9px;">
                                            @endif
                                                <td>{{$cr['Orden']}}</td>
                                                <td>{{$cr['Tipo']}}</td>
                                                <td>{{$cr['Factura']}}</td>
                                                <td>{{$cr['Fecha']}}</td>
                                                <td>{{$cr['Codigo']}}</td>
                                                <td>{{$cr['Descripcion']}}</td>
                                                <td>{{$cr['Horas']}}</td>
                                                <td>{{$cr['Precio']}}</td>
                                                <td>{{$cr['Total']}}</td>
                                            </tr>
                                            <?php 
                                                $sumahoras = $sumahoras + $cr['Horas']; 
                                                $sumatotal = $sumatotal + $cr['Total']; 
                                                $sumatotalhoras = $sumatotalhoras + $cr['Horas']; 
                                                $sumatotaltotal = $sumatotaltotal + $cr['Total']; 
                                                $numfila++;
                                            ?>
                                        @endif
                                    @endforeach
                                    <tr>
                                        <td colspan="6" style="font-size:12px;text-align: right;"></td>
                                        <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumahoras, $data['numerodecimales']) }}</b></td>
                                        <td style="font-size:12px;text-align: right;background-color:#ddd;"><b></b></td>
                                        <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumatotal, $data['numerodecimales']) }}</b></td>
                                    </tr>
                                @endforeach
                                
                                    <tr>
                                        <td colspan="6" style="font-size:12px;text-align: right;">Totales:</td>
                                        <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumatotalhoras, $data['numerodecimales']) }}</b></td>
                                        <td style="font-size:12px;text-align: right;background-color:#ddd;"><b></b></td>
                                        <td style="font-size:12px;text-align: right;background-color:#ddd;"><b>{{ number_format($sumatotaltotal, $data['numerodecimales']) }}</b></td>
                                    </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    @endif
    
    </body>
</html>
