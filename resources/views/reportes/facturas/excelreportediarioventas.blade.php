    @if($numerocliente > 0)
        <table>
            <tr>
                <td style="font-weight: bold;font-size:12px;">Cliente : {{$cliente}}</td>
            </tr>
        </table>
    @endif
    <table style="border-collapse: collapse;">
        <thead >
            <tr>
                <th style="width:10px;border: 1px solid black;font-weight: bold;font-size:9px;">Fecha</th>
                <th style="width:10px;border: 1px solid black;font-weight: bold;font-size:9px;">Dia</th>
                <th style="width:20px;border: 1px solid black;font-weight: bold;font-size:9px;">Importe Día Sin Iva $</th>
                <th style="width:20px;border: 1px solid black;font-weight: bold;font-size:9px;">Importe Esperado Día $</th>
                <th style="width:15px;border: 1px solid black;font-weight: bold;font-size:9px;">Faltante/Sobrante $</th>
                <th style="width:20px;border: 1px solid black;font-weight: bold;font-size:9px;">Acumulado Mes Sin Iva $</th>
                <th style="width:20px;border: 1px solid black;font-weight: bold;font-size:9px;">Acumulado Esperado Mes $</th>
                <th style="width:25px;border: 1px solid black;font-weight: bold;font-size:9px;">Faltante/Sobrante Acumulado $</th>
                <th style="width:15px;border: 1px solid black;font-weight: bold;font-size:9px;">Objetivo Final %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($arrayfacturado as $faturadodia)
                <tr>
                    <td style="border: 1px solid black;font-size:9px;">{{$faturadodia['fechafacturas']}}</td>
                    <td style="border: 1px solid black;font-size:9px;">{{$faturadodia['dia']}}</td>
                    <td style="border: 1px solid black;font-size:9px;">{{$faturadodia['importediatotalsinivaconformato']}}</td>
                    <td style="border: 1px solid black;font-size:9px;">{{$faturadodia['importeesperadofacturadopordiaconformato']}}</td>
                    @if($faturadodia['importediatotalsiniva'] < $faturadodia['importeesperadofacturadopordia'])
                        <td style="background-color : #c32d23;border: 1px solid black;font-size:9px;">{{$faturadodia['faltantesobranteimporteobjetivoconformato']}}</td>
                    @else
                        <td style="background-color : #4CAF50;border: 1px solid black;font-size:9px;">{{$faturadodia['faltantesobranteimporteobjetivoconformato']}}</td>
                    @endif
                    <td style="border: 1px solid black;font-size:9px;">{{$faturadodia['acumuladomessinivaconformato']}}</td>
                    <td style="border: 1px solid black;font-size:9px;">{{$faturadodia['acumuladoesperadomesconformato']}}</td>
                    @if($faturadodia['acumuladomessiniva'] < $faturadodia['acumuladoesperadomes'])
                        <td style="background-color : #c32d23;border: 1px solid black;font-size:9px;">{{$faturadodia['faltantesobranteacumuladoobjetivoconformato']}}</td>
                    @else
                        <td style="background-color : #4CAF50;border: 1px solid black;font-size:9px;">{{$faturadodia['faltantesobranteacumuladoobjetivoconformato']}}</td>
                    @endif
                    @if($faturadodia['porcentajeobjetivofinal'] >= 100)
                        <td style="background-color : #4CAF50;border: 1px solid black;font-size:9px;">{{$faturadodia['porcentajeobjetivofinal']}}</td>
                    @else
                        <td style="border: 1px solid black;font-size:9px;">{{$faturadodia['porcentajeobjetivofinal']}}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>