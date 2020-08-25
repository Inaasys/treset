<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="16x16">
        <link rel="icon" type="image/png" href="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" sizes="32x32">
        <title>Formato | Orden Compra</title>
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
	        font-family: 'Roboto', Arial, Tahoma, sans-serif;
        }
    </style>
    @foreach($data as $d)
    <body>
        <div class="saltopagina">
            <section>
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="125px" height="80px">
                    </div>
                    <div style="float:left;width:60%;text-align: center;">
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Calle}} {{$empresa->NoExterior}} </b>
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Municipio}} {{$empresa->Estado}}, {{$empresa->Pais}} CP: {{$empresa->LugarExpedicion}}</b>
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
                        <p style="font-size:15px;">ORDEN  COMPRA</p>
                        <b style="font-size:10px;"></b>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:50px;">
                    <div style="width:68%; height:120px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;"> Proveedor: <b>{{$d['proveedor']->Nombre}} ({{$d['ordencompra']->Proveedor}})</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Referencia: <b>{{$d['ordencompra']->Referencia}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Observaciones: <b>{{$d['ordencompra']->Obs}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"></p>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:30%; height:120px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;">Orden: <b>{{$d['ordencompra']->Orden}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Fecha: <b>{{$d['ordencompra']->Fecha}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Status: <b>{{$d['ordencompra']->Status}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Tipo: <b>{{$d['ordencompra']->Tipo}}</b></p>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:185px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <thead style="background-color:#a6a6b3; font-size:11px;">
                            <tr>
                                <th>Cantidad</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Marca</th>
                                <th>Ubicación</th>
                                <th>Precio</th>
                                <th>Dcto %</th>
                                <th>SubTotal</th>
                            </tr>
                        </thead>
                        <tbody style="background-color:#ddd; font-size:11px; text-align: center;">
                            @foreach($d['datadetalle'] as $ocd)
                            <tr>
                                <td>{{$ocd['cantidaddetalle']}}</td>
                                <td>{{$ocd['codigodetalle']}}</td>
                                <td>{{$ocd['descripciondetalle']}}</td>
                                <td>{{$ocd['marcadetalle']}}</td>
                                <td>{{$ocd['ubicaciondetalle']}}</td>
                                <td>{{$ocd['preciodetalle']}}</td>
                                <td>{{$ocd['descuentodetalle']}}</td>
                                <td>{{$ocd['subtotaldetalle']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Descuento $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['descuentoordencompra']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">SubTotal $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['subtotalordencompra']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">IVA $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['ivaordencompra']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Total $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['totalordencompra']}}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:150px;">
                    <div style="width:45%; float:left; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Solicitó Mercancia</p>
                        <p style="font-size:11px;">{{\Auth::user()->user}}</p>
                    </div>
                    <div style="width:5%; float:left;">
                    </div>
                    <div style="width:45%; float:right; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Recibió Mercancia</p>
                        <p style="font-size:11px;"></p>
                    </div>
                </div>
            </section>
        </div>
    </body>
    @endforeach
</html>
