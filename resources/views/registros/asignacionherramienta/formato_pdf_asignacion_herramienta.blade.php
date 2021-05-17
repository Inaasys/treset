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
    @foreach($data as $d)
    <body>
        <div class="saltopagina">
            <section style="height:1050px">
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="125px" height="80px">
                    </div>
                    <div style="float:left;width:60%;text-align: center;">
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                        <b style="font-size:12px;color:#122b40;">{{$calleempresa}} No. {{$noexteriorempresa}} </b><br>
                        <b style="font-size:12px;color:#122b40;">{{$coloniaempresa}} CP: {{$cpempresa}}</b><br>
                        <b style="font-size:12px;color:#122b40;">{{$municipioempresa}}, {{$estadoempresa}}</b><br>
                        <b style="font-size:12px;color:#122b40;">RFC {{$empresa->Rfc}} Telefonos {{$telefonosempresa}}</b>
                    </div>
                    <div style="float:right;width:20%;text-align: right;">
                        <p style="font-size:10px;"></p>
                    </div>
                </div><br><br><br><br><br>
                <div>
                    <hr></hr>
                </div>
                <div id ="contenedor" style="margin-top:10px;">
                    <div style="width:53%; height:120px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:9px; margin-left: 5px;"> Recibe Herramienta: {{$d['asignacion']->nombre_recibe_herramienta}} </li>
                            <li style="font-size:9px; margin-left: 5px;"> Entrega Herramienta: {{$d['asignacion']->nombre_entrega_herramienta}}</li>
                            <li style="font-size:9px; margin-left: 5px;"> Observaciones: {{$d['asignacion']->observaciones}}</li>
                        </ul>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:45%; height:120px; float:left; text-align: left; border-style: groove;">
                        <ul style="list-style:none;margin-left:-35px;margin-top:5px;">
                            <li style="font-size:12px; margin-left: 5px;"><b>Asignación:</b> <b style="color:red">{{$d['asignacion']->asignacion}}</b></li>
                            <li style="font-size:9px; margin-left: 5px;">Fecha: {{$d['asignacion']->fecha}}</li>
                            <li style="font-size:9px; margin-left: 5px;">Status: {{$d['asignacion']->status}}</li>
                        </ul>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:185px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <tbody style="font-size:9px; text-align: justify;">
                            <tr style="background-color:#a6a6b3;font-size:10px;">
                                <th>Herramienta</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Total Herramienta</th>
                            </tr>
                            @foreach($d['datadetalle'] as $ahd)
                            <tr>
                                <td>{{$ahd['herramientadetalle']}}</td>
                                <td>{{$ahd['descripciondetalle']}}</td>
                                <td>{{$ahd['estadodetalle']}}</td>
                                <td>{{ number_format($ahd['cantidaddetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($ahd['preciodetalle'], $d['numerodecimalesdocumento']) }}</td>
                                <td>{{ number_format($ahd['totaldetalle'], $d['numerodecimalesdocumento']) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="4" style="font-size:11px"></td>
                                <td style="font-size:11px;text-align: right;">Total $ : </td>
                                <td style="font-size:11px;text-align: right;background-color:#ddd;"><b>{{ number_format($d['totalasignacion'], $d['numerodecimalesdocumento']) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:150px;">
                    <div style="width:45%; float:left; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Firma de quien recibe</p>
                        <p style="font-size:11px;">{{$d['asignacion']->nombre_recibe_herramienta}}</p>
                    </div>
                    <div style="width:5%; float:left;">
                    </div>
                    <div style="width:45%; float:right; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Firma de quien entrega</p>
                        <p style="font-size:11px;">{{$d['asignacion']->nombre_entrega_herramienta}}</p>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:350px;">
                    <div style="width:45%; float:left; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Nombre y Firma del jefe inmediato</p>
                        <p style="font-size:11px;"></p>
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
                        <p style="text-align: justify;color: red; font-size:10px;">
                            OBLIGACIONES DEL TRABAJADOR :  
                        </p>
                        <p style="text-align: justify;color: red; font-size:9px;">
                            RECONOZCO QUE "{{$empresa->Empresa}}" ES PROPIETARIO DE LA HERRAMIENTA DESCRITA EN EL PRESENTE RESGUARDO, Y QUE PARA LA OPTIMA REALIZACIÓN DE MIS ACTIVIDADES, SE ME HA ASIGNADO BAJO MI RESPONSABILIDAD; POR LO QUE SOY EL USUARIO RESPONSABLE DE LA HERRAMIENTA, ASI MISMO, SOY RESPONSABLE DE LA CUSTODIA, Y USO ADECUADO DE LA MISMA; DEBO RESGUARDAR LA HERRAMIENTA EN UN LUGAR SEGURO Y ASIGNADO POR LA EMPRESA, LA CUAL SERÁ SUJETA A REVISIÓN EN CUALQUIER MOMENTO Y LAS VECES QUE LA EMPRESA ASI LO CONSIDERE.                            
                        </p>
                        <p style="text-align: justify;color: red; font-size:9px;">
                            ME COMPROMETO A INFORMAR OPORTUNAMENTE DE CUALQUIER DESPERFECTO QUE SUFRA EL EQUIPO, QUEDO ENTERADO DE QUE CUALQUIER DESPERFECTO QUE RESULTE DE UN MAL USO, MALA FE, NEGLIGENCIA, IMPERICIA, POR ESTADO DE EMBRIAGUEZ O POR CUALQUIER OTRA CAUSA ANALOGA SERÁ DEDUCIDA Y/O DESCONTADA DE MI SALARIO, HASTA CUBRIR EL SALDO TOTAL; ASÍ MISMO ME HAGO RESPONSABLE DE CUALQUIER RESPONSABILIDAD CIVIL QUE SURJA A LA EMPRESA POR MOTIVO DE ACCIDENTE, HABIENDO RESPONSABILIDAD DEL TRABAJADOR, Y DE LO QUE RESULTE SERÁ DEDUCIDA DE MI SALARIO HASTA EL PAGO TOTAL.                        </p>
                    </div>
                </div>       
            </section>
        </div>
    </body>
    @endforeach
</html>
