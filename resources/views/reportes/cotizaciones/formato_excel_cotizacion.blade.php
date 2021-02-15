    <table>
        <tr >
            <td colspan="6"></td>
            <td style="font-weight: bold;font-size:25px;text-align:center;" colspan="3">COTIZACIÓN</td>
            <td colspan="4"></td>
        </tr>
    </table>
    <table>
        <tr >
            <td colspan="11"></td>
            <td style="font-weight: bold;font-size:12px;">Fecha</td>
            <td style="font-weight: bold;font-size:10px;text-align:center;">{{$data['fechacotizacion']}}</td>
        </tr>
        <tr >
            <td colspan="8"></td>
            <td style="font-weight: bold;font-size:12px;">Cummins</td>
            <td style="font-weight: bold;font-size:10px;text-align:center;"></td>
            <td></td>
            <td style="font-weight: bold;font-size:12px;">Folio</td>
            <td style="font-weight: bold;font-size:10px;text-align:center;">{{$data['info_cotizacion']->id}}</td>
        </tr>
        <tr >
            <td colspan="7"></td>
            <td style="font-weight: bold;font-size:12px;">Tipo de cambio: </td>
            <td style="font-weight: bold;font-size:12px;">Volvo</td>
            <td style="font-weight: bold;font-size:10px;text-align:center;"></td>
            <td></td>
            <td style="font-weight: bold;font-size:12px;"></td>
            <td style="font-weight: bold;font-size:10px;"></td>
        </tr>
        <tr >
            <td colspan="11"></td>
            <td style="font-weight: bold;font-size:12px;"></td>
            <td style="font-weight: bold;font-size:10px;"></td>
        </tr>
    </table>
    <table style="border-collapse: collapse;">
        <thead >
            <tr>
                <th style="background-color:#FFFFFF;width:5px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">NO. </th>
                <th style="background-color:#FFFFFF;width:10px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">NO. RQ</th>
                <th style="background-color:#FFFFFF;width:10px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">NO. EQUIPO </th>
                <th style="background-color:#FFFFFF;width:12px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">OT. TECNO</th>
                <th style="background-color:#FFFFFF;width:12px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">OS. TYT</th>
                <th style="background-color:#FFFFFF;width:10px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">FECHA</th>
                <th style="background-color:#FFFFFF;width:15px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">NÚMERO PARTE</th>
                <th style="background-color:#FFFF00;width:45px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">DESCRIPCIÓN</th>
                <th style="background-color:#FFFF00;width:13px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">STATUS <br> REFACCIONES</th>
                <th style="background-color:#FFFF00;width:15px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">INSUMO</th>
                <th style="background-color:#FFFF00;width:20px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">PRECIO</th>
                <th style="background-color:#FFFFFF;width:20px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">CANTIDAD</th>
                <th style="background-color:#FFFF00;width:20px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;?>
            @foreach($data['detalles'] as $d)
                <tr>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$no}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$data['info_cotizacion']->num_remision}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$data['info_cotizacion']->num_equipo}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$data['info_cotizacion']->ot_tecnodiesel}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$data['info_cotizacion']->ot_tyt}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$data['fechacotizacion']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['numero_parte']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['descripcion']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['status_refaccion']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['insumo']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">$ {{$d['precio']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">$ {{$d['cantidad']}}</td>
                    <td style="border: 1px solid black;font-size:8px;text-align:center;">$ {{$d['importe']}}</td>
                </tr>
                <?php $no++;?>
            @endforeach
            <tr>
                <td colspan="11"></td>
                <td style="border: 1px solid black;font-weight: bold;font-size:12px;">SUBTOTAL</td>
                <td style="border: 1px solid black;font-weight: bold;font-size:10px;">$ {{$data['subtotal']}}</td>
            </tr>
            <tr>
                <td colspan="11"></td>
                <td style="border: 1px solid black;font-weight: bold;font-size:12px;">IVA</td>
                <td style="border: 1px solid black;font-weight: bold;font-size:10px;">$ {{$data['iva']}}</td>
            </tr>
            <tr>
                <td colspan="11"></td>
                <td style="border: 1px solid black;font-weight: bold;font-size:12px;">TOTAL</td>
                <td style="border: 1px solid black;font-weight: bold;font-size:10px;">$ {{$data['total']}}</td>
            </tr>
        </tbody>
    </table>