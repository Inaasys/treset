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
            letter-spacing: 2px;
	        font-family: 'Roboto', Arial, Tahoma, sans-serif;
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
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Calle}} {{$empresa->NoExterior}} </b><br>
                        <b style="font-size:12px;color:#122b40;">  {{$empresa->Municipio}} {{$empresa->Estado}}, {{$empresa->Pais}} CP: {{$empresa->LugarExpedicion}}</b>
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
                        <p style="font-size:15px;">ASIGNACIÓN HERRAMIENTA</p>
                        <b style="font-size:10px;"></b>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:50px;">
                    <div style="width:68%; height:120px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;"> Recibe Herramienta: <b>{{$d['asignacion']->nombre_recibe_herramienta}} </b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Entrega Herramienta: <b>{{$d['asignacion']->nombre_entrega_herramienta}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Observaciones: <b>{{$d['asignacion']->observaciones}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"></p>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:30%; height:120px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;">Asignación: <b>{{$d['asignacion']->asignacion}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Fecha: <b>{{$d['asignacion']->fecha}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Status: <b>{{$d['asignacion']->status}}</b></p>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:185px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <thead style="background-color:#a6a6b3; font-size:11px;">
                            <tr>
                                <th>Herramienta</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Total Herramienta</th>
                            </tr>
                        </thead>
                        <tbody style="background-color:#ddd; font-size:11px; text-align: center;">
                            @foreach($d['datadetalle'] as $ahd)
                            <tr>
                                <td>{{$ahd['herramientadetalle']}}</td>
                                <td>{{$ahd['descripciondetalle']}}</td>
                                <td>{{$ahd['estadodetalle']}}</td>
                                <td>{{$ahd['cantidaddetalle']}}</td>
                                <td>{{$ahd['preciodetalle']}}</td>
                                <td>{{$ahd['totaldetalle']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Total $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['totalasignacion']}}</b></td>
                            </tr>
                        </tfoot>
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
                        <p style="text-align: justify;color: red; font-size:11px;">
                            OBLIGACIONES DEL TRABAJADOR :  
                        </p>
                        <p style="text-align: justify;color: red; font-size:11px;">
                            RECONOZCO QUE "{{$empresa->Empresa}}" ES PROPIETARIO DE LA HERRAMIENTA DESCRITA EN EL PRESENTE RESGUARDO, Y QUE PARA LA OPTIMA REALIZACIÓN DE MIS ACTIVIDADES, SE ME HA ASIGNADO BAJO MI RESPONSABILIDAD; POR LO QUE SOY EL USUARIO RESPONSABLE DE LA HERRAMIENTA, ASI MISMO, SOY RESPONSABLE DE LA CUSTODIA, Y USO ADECUADO DE LA MISMA; DEBO RESGUARDAR LA HERRAMIENTA EN UN LUGAR SEGURO Y ASIGNADO POR LA EMPRESA, LA CUAL SERÁ SUJETA A REVISIÓN EN CUALQUIER MOMENTO Y LAS VECES QUE LA EMPRESA ASI LO CONSIDERE.                            
                        </p>
                        <p style="text-align: justify;color: red; font-size:11px;">
                            ME COMPROMETO A INFORMAR OPORTUNAMENTE DE CUALQUIER DESPERFECTO QUE SUFRA EL EQUIPO, QUEDO ENTERADO DE QUE CUALQUIER DESPERFECTO QUE RESULTE DE UN MAL USO, MALA FE, NEGLIGENCIA, IMPERICIA, POR ESTADO DE EMBRIAGUEZ O POR CUALQUIER OTRA CAUSA ANALOGA SERÁ DEDUCIDA Y/O DESCONTADA DE MI SALARIO, HASTA CUBRIR EL SALDO TOTAL; ASÍ MISMO ME HAGO RESPONSABLE DE CUALQUIER RESPONSABILIDAD CIVIL QUE SURJA A LA EMPRESA POR MOTIVO DE ACCIDENTE, HABIENDO RESPONSABILIDAD DEL TRABAJADOR, Y DE LO QUE RESULTE SERÁ DEDUCIDA DE MI SALARIO HASTA EL PAGO TOTAL.                        </p>
                    </div>
                </div>       
            </section>
        </div>
    </body>
    @endforeach
</html>
