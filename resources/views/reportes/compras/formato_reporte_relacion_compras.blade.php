    <table style="border-collapse: collapse;">
        <thead >
            <tr>
                @foreach($campos_consulta as $campo)
                    <th style="background-color:#98cfe4;width:15px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">{{$campo}}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if($reporte == 'RELACION')
                @foreach($data as $d)
                    <tr>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Compra']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Proveedor']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Nombre']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Fecha']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Plazo']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Vence']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Remision']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Factura']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Movimiento']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Almacen']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Tipo']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Importe']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Descuento']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['SubTotal']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Iva']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Total']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Abonos']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Descuentos']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Saldo']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Obs']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Status']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['MotivoBaja']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Usuario']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Rfc']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Calle']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['NoExterior']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Colonia']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Municipio']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Estado']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['CodigoPostal']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Contacto']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Telefonos']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Email1']}}</td>
                    </tr>
                @endforeach
            @elseif($reporte == 'DETALLES')
                @foreach($data as $d)
                    <tr>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Compra']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Proveedor']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Nombre']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Fecha']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Plazo']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Vence']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Remision']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Factura']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Movimiento']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Almacen']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Tipo']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Codigo']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Descripcion']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Unidad']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Cantidad']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Precio']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Importe']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Descuento']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['SubTotal']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Iva']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Total']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['ObsCompra']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['ObsDetalle']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Status']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['MotivoBaja']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Usuario']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Rfc']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Calle']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['NoExterior']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Colonia']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Municipio']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Estado']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['CodigoPostal']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Contacto']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Telefonos']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Email1']}}</td>
                    </tr>
                @endforeach
            @else
                @foreach($data as $d)
                    <tr>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Numero']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Nombre']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Totalc']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Rfc']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Calle']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['NoExterior']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Colonia']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Municipio']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Estado']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['CodigoPostal']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Contacto']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Telefonos']}}</td>
                        <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['Email1']}}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>