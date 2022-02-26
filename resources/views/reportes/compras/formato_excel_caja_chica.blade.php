    <table>
        <tr >
            <td colspan="2"></td>
            <td style="background-color:#98cfe4;font-weight: bold;font-size:8px;" colspan="5">DETALLE DE CAJAS CHICAS</td>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td  colspan="2"></td>
            <td style="background-color:#98cfe4;font-weight: bold;font-size:8px;" colspan="5">CAJA CHICA No. _____</td>
            <td colspan="5"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="2"></td>
            <td style="font-size:8px;" colspan="5">NOMBRE DE LA EMPRESA: <label style="font-weight: bold;font-size:8px;">{{$empresa->Nombre}}</label></td>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td style="font-size:8px;" colspan="5">REEMBOLSO FISCAL CAJA CHICA</td>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td style="font-size:8px;" colspan="5">REVISA: _______________________________________________________</td>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td style="font-size:8px;" colspan="3">COMPRUEBA: _______________________________________________________</td>
            <td style="font-size:8px;" colspan="2">FECHA {{$fechahoy}}</td>
            <td colspan="5"></td>
        </tr>
    </table>
    <table style="border-collapse: collapse;">
        <thead >
            <tr>
                <th style="background-color:#98cfe4;width:9px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">FECHA</th>
                <th style="background-color:#98cfe4;width:8px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">MOV <br> COMPRA</th>
                <th style="background-color:#98cfe4;width:35px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">PROVEEDOR</th>
                <th style="background-color:#98cfe4;width:31px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">UUID</th>
                <th style="background-color:#98cfe4;width:31px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">FACTURA</th>
                <th style="background-color:#98cfe4;width:15px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">CONCEPTO DE PAGO</th>
                <th style="background-color:#98cfe4;width:45px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">OBSERVACIONES</th>
                <th style="background-color:#98cfe4;width:10px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">SUBTOTAL</th>
                <th style="background-color:#98cfe4;width:10px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">IVA</th>
                <th style="background-color:#98cfe4;width:11px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">IVA RETENCIÓN</th>
                <th style="background-color:#98cfe4;width:10px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">IMP <br> HOSPEDAJE</th>
                <th style="background-color:#98cfe4;width:10px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">TOTAL</th>
                <th style="background-color:#98cfe4;width:10px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">DEPTO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $d)
                <tr>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['fechacompra']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['movimientocompra']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['proveedor']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['UUID']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Factura']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;"></td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['observacionescompra']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">$ {{$d['subtotal']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">$ {{$d['iva']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">$ {{$d['ivaretencion']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">$ </td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">$ {{$d['total']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;"></td>

                </tr>
            @endforeach
            <tr>
                <td colspan="6"></td>
                <td style="border: 1px solid black;font-weight: bold;font-size:9px;">TOTAL A COMPROBAR</td>
                <td style="border: 1px solid black;font-weight: bold;font-size:9px;">$ {{$d['sumasubtotal']}}</td>
                <td style="border: 1px solid black;font-weight: bold;font-size:9px;">$ {{$d['sumaiva']}}</td>
                <td style="border: 1px solid black;font-weight: bold;font-size:9px;">$ {{$d['sumaivaretencion']}}</td>
                <td style="border: 1px solid black;font-weight: bold;font-size:9px;">$ </td>
                <td style="border: 1px solid black;font-weight: bold;font-size:9px;">$ {{$d['sumatotal']}}</td>
            </tr>
        </tbody>
    </table>