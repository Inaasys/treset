<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Códigos de Barras</title>
    </head>
    <body>
        <section>
                <div id ="contenedor">
                    @foreach($codigos as $c)
                        @if($tipo == 1)
                            @if($tamanoetiquetas == 'chica')
                                <div style="float:left;width:21.6%;height:60px;text-align: center;">
                                    <div style=" margin-top:8px;font-size:8px;">{!! DNS1D::getBarcodeSVG($c->Codigo, 'C128', 0.8,22,'black', true) !!}</div>
                                    <div style="font-size:7px;"> {{ \Illuminate\Support\Str::limit($c->Producto, 40, $end='...') }}</div>
                                    <div style="font-size:8px;">{{ $c->Ubicacion }}</div>
                                </div>
                                <div style="float:left;width:3.1%;height:60px;text-align: center;"></div>
                            @else
                                <div style="float:left;width:33%;height:118px;text-align: center;">
                                    <div style=" margin-top:33px;">{!! DNS1D::getBarcodeSVG($c->Codigo, 'C128', 0.8,35,'black', true) !!}</div>
                                    <div style="font-size:7px;">{{ \Illuminate\Support\Str::limit($c->Producto, 60, $end='...') }}</div>
                                    <div style="font-size:8px;">{{ $c->Ubicacion }}</div>
                                </div>
                            @endif
                        @elseif($tipo == 2)
                            @if($tamanoetiquetas == 'chica')
                                <div style="float:left;width:21.6%;height:60px;text-align: center;">
                                    <div style=" margin-top:8px;font-size:8px;">{!! DNS1D::getBarcodeSVG($c['codigo'], 'C128', 0.8,22,'black', true) !!}</div>
                                    <div style="font-size:7px;"> {{ \Illuminate\Support\Str::limit($c['producto'], 40, $end='...') }}</div>
                                    <div style="font-size:8px;">{{ $c['ubicacion'] }}</div>
                                </div>
                                <div style="float:left;width:3.1%;height:60px;text-align: center;"></div>
                            @else
                                <div style="float:left;width:33%;height:118px;text-align: center;">
                                    <div style=" margin-top:33px;">{!! DNS1D::getBarcodeSVG($c['codigo'], 'C128', 0.8,35,'black', true) !!}</div>
                                    <div style="font-size:7px;">{{ \Illuminate\Support\Str::limit($c['producto'], 60, $end='...') }}</div>
                                    <div style="font-size:8px;">{{ $c['ubicacion'] }}</div>
                                </div>
                            @endif
                        @else
                            @for ($i = 0; $i < $numimpresiones; $i++)
                                @if($tamanoetiquetas == 'chica')
                                    <div style="float:left;width:21.6%;height:60px;text-align: center;">
                                        <div style=" margin-top:8px;font-size:8px;">{!! DNS1D::getBarcodeSVG($c['codigo'], 'C128', 0.8,22,'black', true) !!}</div>
                                        <div style="font-size:7px;"> {{ \Illuminate\Support\Str::limit($c['producto'], 40, $end='...') }}</div>
                                        <div style="font-size:8px;">{{ $c['ubicacion'] }}</div>
                                    </div>
                                    <div style="float:left;width:3.1%;height:60px;text-align: center;"></div>
                                @else
                                    <div style="float:left;width:33%;height:118px;text-align: center;">
                                        <div style=" margin-top:33px;">{!! DNS1D::getBarcodeSVG($c['codigo'], 'C128', 0.8,35,'black', true) !!}</div>
                                        <div style="font-size:7px;">{{ \Illuminate\Support\Str::limit($c['producto'], 60, $end='...') }}</div>
                                        <div style="font-size:8px;">{{ $c['ubicacion'] }}</div>
                                    </div>
                                @endif
                            @endfor    
                        @endif
                    @endforeach   
                </div>       
        </section>
    </body>
</html>
