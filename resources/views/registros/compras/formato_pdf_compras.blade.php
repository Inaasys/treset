<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Compra</title>
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
            <section>
                <div id ="contenedor">
                    <div style="float:left;width:20%;text-align: left;">
                    <img src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="125px" height="80px">
                    </div>
                    <div style="float:left;width:60%;text-align: center;">
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Empresa}}</b><br>
                        <b style="font-size:12px;color:#122b40;">{{$empresa->Calle}} {{$empresa->NoExterior}} </b><br>
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
                        <p style="font-size:15px;">COMPRA</p>
                        <b style="font-size:10px;"></b>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:50px;">
                    <div style="width:68%; height:175px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;"> Factura Número: <b>{{$d['compra']->Factura}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Remisión Número: <b>{{$d['compra']->Remision}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> UUID: <b>{{$d['compra']->UUID}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Emisor Rfc: <b>{{$d['compra']->EmisorRfc}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Receptor Rfc: <b>{{$d['compra']->ReceptorRfc}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Proveedor: <b>{{$d['proveedor']->Nombre}} ({{$d['compra']->Proveedor}})</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Observaciones: <b>{{$d['compra']->Obs}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"></p>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:30%; height:175px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;">Compra: <b>{{$d['compra']->Compra}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Fecha: <b>{{$d['compra']->Fecha}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Status: <b>{{$d['compra']->Status}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Tipo: <b>{{$d['compra']->Tipo}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Movimiento: <b>{{$d['compra']->Movimiento}}</b></p>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:240px;">
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
                                <td style="font-size:12px;text-align: right;"><b>{{$d['descuentocompra']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">SubTotal $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['subtotalcompra']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">IVA $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['ivacompra']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Retención $ : </td>
                                <td style="font-size:12px;text-align: right;"><b></b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Total $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['totalcompra']}}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:150px;">
                    <div style="width:45%; float:left; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Capturó Movimiento</p>
                        <p style="font-size:11px;">{{$d['compra']->Usuario}}</p>
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
