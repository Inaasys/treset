<table>
    <tr >
        <td style="background-color:#98cfe4;font-weight: bold;font-size:11px;text-align:center;" colspan="4">REPORTE HORAS TÉCNICO</td>
    </tr>
</table>
<table style="border-collapse: collapse;">
    <thead >
        <tr>
            <th style="background-color:#98cfe4;width:10px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">TÉCNICO</th>
            <th style="background-color:#98cfe4;width:50px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">NOMBRE</th>
            <th style="background-color:#98cfe4;width:25px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">HORAS</th>
            <th style="background-color:#98cfe4;width:25px;border: 1px solid black;font-weight: bold;font-size:8px;text-align:center;">TOTAL $</th>
        </tr>
    </thead>
    <tbody>
        <?php $totalhoras = 0; ?>
        <?php $totalpesoshoras = 0; ?>
        @foreach($data as $d)
            <tr>
                <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['tecnico']}}</td>
                <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['nombre']}}</td>
                <td style="border: 1px solid black;font-size:8px;text-align:center;">{{$d['horas']}}</td>
                <td style="border: 1px solid black;font-size:8px;text-align:center;">$ {{$d['total']}}</td>
            </tr>
            <?php $totalhoras = $totalhoras + $d['horas']; ?>
            <?php $totalpesoshoras = $totalpesoshoras + $d['total']; ?>
        @endforeach
        <tr>
            <td colspan="2"></td>
            <td style="border: 1px solid black;font-weight: bold;font-size:9px;">TOTAL $: {{$totalhoras}}</td>
            <td style="border: 1px solid black;font-weight: bold;font-size:9px;">TOTAL $: {{$totalpesoshoras}}</td>
        </tr>
    </tbody>
</table>