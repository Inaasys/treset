<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Formato | Contrarecibo</title>
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
                        <p style="font-size:15px;">CONTRA RECIBO</p>
                        <b style="font-size:10px;"></b>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:50px;">
                    <div style="width:68%; height:120px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;"> Proveedor: <b>{{$d['proveedor']->Nombre}} ({{$d['contrarecibo']->Proveedor}})</b></p>
                        <p style="font-size:11px; margin-left: 5px;"> Observaciones: <b>{{$d['contrarecibo']->Obs}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;"></p>
                        <p style="font-size:11px; margin-left: 5px;"></p>
                    </div>
                    <div style="width:1%; float:left;">
                    </div>
                    <div style="width:30%; height:120px; float:left; text-align: left; border-style: groove;">
                        <p style="font-size:11px; margin-left: 5px;">ContraRecibo: <b>{{$d['contrarecibo']->ContraRecibo}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Fecha: <b>{{$d['contrarecibo']->Fecha}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Status: <b>{{$d['contrarecibo']->Status}}</b></p>
                        <p style="font-size:11px; margin-left: 5px;">Ampara: <b>{{$d['contrarecibo']->Facturas}} Factura(s)</b></p>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:185px;">
                    <table style="width: 100%;max-width: 100%;border: 1px solid #ddd;">
                        <thead style="background-color:#a6a6b3; font-size:11px;">
                            <tr>
                                <th>Fecha Factura</th>
                                <th>Factura</th>
                                <th>Remision</th>
                                <th>Total$</th>
                                <th>Fecha de Pago</th>
                                <th>Movimiento de Compra</th>
                            </tr>
                        </thead>
                        <tbody style="background-color:#ddd; font-size:11px; text-align: center;">
                            @foreach($d['datadetalle'] as $crd)
                            <tr>
                                <td>{{$crd['fechadetalle']}}</td>
                                <td>{{$crd['facturadetalle']}}</td>
                                <td>{{$crd['remisiondetalle']}}</td>
                                <td>{{$crd['totaldetalle']}}</td>
                                <td>{{$crd['fechaapagardetalle']}}</td>
                                <td>{{$crd['compradetalle']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr rowspan="5">
                                <td colspan="5" style="font-size:12px"><b>CERO PESOS 00/100 M.N.</b></td>
                                <td colspan="2" style="font-size:12px"><b>Total $ : {{$d['totalcontrarecibo']}}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id ="contenedor" style="margin-top:30px;">
                    <div style="width:100%; float:right; text-align: center;">
                        <b style="font-size:12px;">Nota. No se entregará cheque SIN ContraRecibo Sellado y Firmado</b><br>
                    </div>
                </div>
                <div id ="contenedor" style="margin-top:150px;">
                    <div style="width:45%; float:left; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">{{$d['proveedor']->Nombre}} ({{$d['contrarecibo']->Proveedor}})</p>
                        <p style="font-size:11px;">Recibió ContraRecibo</p>
                    </div>
                    <div style="width:5%; float:left;">
                    </div>
                    <div style="width:45%; float:right; text-align: center;">
                        <b style="font-size:11px;"><hr></hr></b>
                        <p style="font-size:11px;">{{\Auth::user()->user}}</p>
                        <p style="font-size:11px;"></p>
                    </div>
                </div>
            </section>
        </div>
    </body>
    @endforeach
</html>
