<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Cuenta por Cobrar</title>
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
            <section style="height:1080px">
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
                <div id ="contenedor">
                    <div style="width:50%; float:left; text-align: left;">
                        <p style="font-size:18px;"><b>POLIZA DE INGRESO No. {{$d['cuentaporcobrar']->Pago}}</b></p>
                        <p style="font-size:12px;"></p>
                    </div>
                    <div style="width:50%; float:right; text-align: right;">
                        <p style="font-size:12px;"><b>ALTA</b></p>
                        <p style="font-size:12px;">{{$d['fechaespanolcuentaporcobrar']}}</p>
                        <p style="width:35%; float:right; text-align:right; border: 1px solid; background-color:#ddd; font-size:12px;"><b style="margin-right:5px;">{{ number_format($d['abonocuentaporcobrar'], $d['numerodecimalesdocumento']) }}</b></p>
                    </div>
                </div>
                <div id ="contenedor">
                    <div style="width:100%; float:left; text-align: left;">
                        <p style="background-color:#ddd; border: 1px solid; font-size:12px;">{{$d['abonoletras']}}</p>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:200px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Cuenta</th>
                                <th>Concepto</th>
                                <th>Factura</th>
                                <th>Cargo</th>
                                <th>Abono $</th>
                            </tr>
                            @foreach($d['datadetalle'] as $cxcd)
                            <tr style="font-size:10px;">
                                <td></td>
                                <td>{{$cxcd['clientedetalle']->Nombre}} ({{$cxcd['clientedetalle']->Numero}})</td>
                                <td>{{$cxcd['facturadetalle']}}</td>
                                <td></td>
                                <td style="text-align:right;">{{ number_format($cxcd['imppagadodetalle'], $d['numerodecimalesdocumento']) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td style="font-size:11px;text-align: left;">{{$d['banco']->Cuenta}}</td>
                                <td colspan="2" style="font-size:11px;text-align: left;">{{$d['banco']->Nombre}}</td>
                                <td colspan="2" style="font-size:11px;text-align: left;background-color:#ddd;"><b>{{ number_format($d['abonocuentaporcobrar'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <section>
                <div id ="contenedor">
                    <div style="width:80%;  float:left; text-align: left;">
                        <table style="width:100%;border:1px solid;text-align: center;">
                            <thead>
                                <tr>
                                    <td style="width:33%; font-size:12px; text-align: center;">Hecho por:</td>
                                    <td style="width:34%; font-size:12px; text-align: center;">Revisado por:</td>
                                    <td style="width:33%; font-size:12px; text-align: center;">Autorizado por:</td>
                                </tr>
                            </thead>
                        </table>
                        <table style="width:100%;border:1px solid;text-align: center;">
                            <thead>
								<tr style="height:70px;"></tr>
                                <tr>
                                    <td style="width:33%; font-size:12px; text-align: center;">Nombre y Firma</td>
                                    <td style="width:34%; font-size:12px; text-align: center;">Nombre y Firma</td>
                                    <td style="width:33%; font-size:12px; text-align: center;">Nombre y Firma</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div style="width:19%; border:1px solid; float:right; text-align: right;">
                        <p style="font-size:12px;text-align:center;">Número de Pago</p>
                        <p style="font-size:12px;text-align:center;">{{$d['cuentaporcobrar']->Pago}}</p>
						<p style="height:26px;"></p>
                    </div>
                </div>       
            </section>
        </div>
    </body>
    @endforeach
</html>
