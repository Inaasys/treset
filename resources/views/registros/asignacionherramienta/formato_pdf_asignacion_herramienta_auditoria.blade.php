<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="16x16">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="32x32">
        <title>Formato | Asignación Herramienta</title>
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
    <body>
        <div class="saltopagina">
            <section style="height:1200px">
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="125px" height="80px">
                    </div>
                    <div style="float:left;width:60%;text-align: center;">
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Calle}} No. {{$empresa->NoExterior}} </b><br>
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Colonia}} CP: {{$empresa->LugarExpedicion}}</b><br>
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Municipio}}, {{$empresa->Estado}}</b><br>
                        <b style="font-size:12px;color:#122b40;">RFC {{$empresa->Rfc}} Telefonos {{$empresa->Telefonos}}</b>
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
                        <p style="font-size:15px;">AUDITORIA HERRAMIENTA</p>
                        <b style="font-size:10px;"></b>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:50px;">
                    <div style="width:99%; height:40px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;"> Personal Herramienta: <b>{{$data[0]['Personal_Recibe_Herramienta']->nombre}} </b></p>
                        <p style="font-size:11px; margin-left: 5px;"></p>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:125px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <thead style="background-color:#a6a6b3; font-size:11px;">
                            <tr>
                                <th>Asignación</th>
                                <th>Herramienta</th>
                                <th>Descripción</th>
                                <th>Estado Auditoria</th>
                                <th>Cantidad Auditoria</th>
                                <th>Precio</th>
                                <th>Total Herramienta</th>
                            </tr>
                        </thead>
                        <tbody style="background-color:#ddd; font-size:11px; text-align: center;">
                            @foreach($data[0]['datadetalle'] as $ahd)
                            <tr>
                                <td>{{$ahd['asignaciondetalle']}}</td>    
                                <td>{{$ahd['herramientadetalle']}}</td>
                                <td>{{$ahd['descripciondetalle']}}</td>
                                <td>{{$ahd['estadoauditoriadetalle']}}</td>
                                <td>{{$ahd['cantidadauditoriadetalle']}}</td>
                                <td>{{$ahd['preciodetalle']}}</td>
                                <td>{{$ahd['totaldetalle']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Total $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$data[0]['totalasignacion']}}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:250px;">
                    <div style="width:45%; float:left; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Firma de encargado herramienta</p>
                        <p style="font-size:11px;">{{$data[0]['Personal_Recibe_Herramienta']->nombre}} </p>
                    </div>
                    <div style="width:5%; float:left;">
                    </div>
                    <div style="width:45%; float:right; text-align: center;">
                        <b style="font-size:11px;"></b>
                        <p style="font-size:11px;"></p>
                        <p style="font-size:11px;"></p>
                    </div>
                </div>
            </section>
            <section>
                <div id ="contenedor">
                    <div style="width:99%;  float:left; text-align: left;">
                        <p style="text-align: justify;color: red; font-size:11px;">
                            DE ACUERDO A LA AUDITORÍA REALIZADA EN MI PRESENCIA, YO {{$data[0]['Personal_Recibe_Herramienta']->nombre}} AUTORIZO QUE LA HERRAMIENTA FALTANTE A MI CARGO SEA DESCONTADA VÍA NÓMINA POR UN MONTO TOTAL DE $ {{$data[0]['totalasignacion']}} PARA QUE SEA REPUESTA A LA EMPRESA {{$empresa->Empresa}}  
                        </p>
                    </div>
                </div>       
            </section>
        </div>
    </body>
</html>
