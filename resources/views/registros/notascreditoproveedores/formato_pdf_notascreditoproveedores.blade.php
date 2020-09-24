<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Nota de Crédito Proveedor</title>
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
                        <p style="font-size:15px;">NOTA PROVEEDOR</p>
                        <b style="font-size:10px;"></b>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:50px;">
                    <div style="width:68%; height:125px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;"> Proveedor: <b>{{$d['proveedor']->Nombre}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Calle: <b>{{$d['proveedor']->Calle}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Colonia: <b>{{$d['proveedor']->Colonia}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Municipio: <b>{{$d['proveedor']->Municipio}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"></p>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:30%; height:125px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;">Nota: <b>{{$d['notacreditoproveedor']->Nota}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Fecha: <b>{{$d['notacreditoproveedor']->Fecha}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Status: <b>{{$d['notacreditoproveedor']->Status}}</b></p>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:200px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <thead style="background-color:#a6a6b3; font-size:11px;">
                            <tr>
                                <th>Cantidad</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Compra</th>
                                <th>Remision</th>
                                <th>Factura</th>
                                <th>Precio</th>
                                <th>SubTotal</th>
                            </tr>
                        </thead>
                        <tbody style="background-color:#ddd; font-size:11px; text-align: center;">
                            @foreach($d['datadetalle'] as $ncpd)
                            <tr>
                                <td>{{$ncpd['cantidaddetalle']}}</td>
                                <td>{{$ncpd['codigodetalle']}}</td>
                                <td>{{$ncpd['descripciondetalle']}}</td>
                                <td>{{$ncpd['compradetalle']}}</td>
                                <td>{{$ncpd['remisiondetalle']}}</td>
                                <td>{{$ncpd['facturadetalle']}}</td>
                                <td>{{$ncpd['preciodetalle']}}</td>
                                <td>{{$ncpd['subtotaldetalle']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Descuento $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['descuentonotacreditoproveedor']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">SubTotal $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['subtotalnotacreditoproveedor']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">IVA $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['ivanotacreditoproveedor']}}</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size:12px"></td>
                                <td style="font-size:12px;text-align: right;">Total $ : </td>
                                <td style="font-size:12px;text-align: right;"><b>{{$d['totalnotacreditoproveedor']}}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:150px;">
                    <div style="width:45%; float:left; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Realizó</p>
                        <p style="font-size:11px;"></p>
                    </div>
                    <div style="width:5%; float:left;">
                    </div>
                    <div style="width:45%; float:right; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">Recibió</p>
                        <p style="font-size:11px;"></p>
                    </div>
                </div>
            </section>
        </div>
    </body>
    @endforeach
</html>
