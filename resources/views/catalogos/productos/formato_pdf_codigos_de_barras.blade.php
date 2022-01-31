<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Códigos de Barras</title>
    </head>
    
    <style>
        .marcafirma {
            opacity: 0.5;
            filter: alpha(opacity=40); /* For IE8 and earlier */
        }
        #capa1{ 
            position:absolute;
            z-index:1;
            background-color:#FFFFFF;
            text-align:center;
            background-color: transparent;
        }
        #capa2{
            /*position:absolute;*/
            z-index:0;
        }
    </style>
    <body>
        <section>
                <div id ="contenedor">
                    @foreach($codigos as $c)
                        @if($tipo == 1)
                            @if($tamanoetiquetas == 'chica')
                                <div style="float:left;width:21.6%;height:60px;text-align: center;">
                                    <div style=" margin-top:8px;font-size:8px;">{!! DNS1D::getBarcodeSVG($c->Codigo, 'C128', 0.9,22,'black', true) !!}</div>
                                    <div style="font-size:9px;"> {{ \Illuminate\Support\Str::limit($c->Producto, 40, $end='...') }}</div>
                                    <div style="font-size:10px;">{{ $c->Ubicacion }}</div>
                                </div>
                                <div style="float:left;width:3.1%;height:60px;text-align: center;"></div>
                            @else
                                <div style="float:left;width:33%;height:85px;text-align: center;">
                                    <div style=" margin-top:33px;">{!! DNS1D::getBarcodeSVG($c->Codigo, 'C128', 0.9,45,'black', true) !!}</div>
                                    <div style="font-size:9px;margin-right: 10px;margin-left: 10px;"><b>{{ \Illuminate\Support\Str::limit($c->Producto, 60, $end='...') }}</b></div>
                                    <div style="font-size:10px;margin-right: 10px;margin-left: 10px;"><b>{{ $c->Ubicacion }}</b></div>
                                    <div id="capa2" style="margin-top:-70px;margin-right:150px;">
                                        <img class="marcafirma" src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="40px" height="40px">
                                    </div>
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
                                <div style="float:left;width:33%;height:85px;text-align: center;">
                                    <div style=" margin-top:33px;">{!! DNS1D::getBarcodeSVG($c['codigo'], 'C128', 0.9,45,'black', true) !!}</div>
                                    <div style="font-size:9px;margin-right: 10px;margin-left: 10px;"><b>{{ \Illuminate\Support\Str::limit($c['producto'], 60, $end='...') }}</b></div>
                                    <div style="font-size:10px;margin-right: 10px;margin-left: 10px;"><b>{{ $c['ubicacion'] }}</b></div>
                                    <div id="capa2" style="margin-top:-70px;margin-right:150px;">
                                        <img class="marcafirma" src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="40px" height="40px">
                                    </div>
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
                                    <div style="float:left;width:33%;height:85px;text-align: center;">
                                        <div style=" margin-top:33px;">{!! DNS1D::getBarcodeSVG($c['codigo'], 'C128', 0.9,45,'black', true) !!}</div>
                                        <div style="font-size:9px;margin-right: 10px;margin-left: 10px;"><b>{{ \Illuminate\Support\Str::limit($c['producto'], 60, $end='...') }}</b></div>
                                        <div style="font-size:10px;margin-right: 10px;margin-left: 10px;"><b>{{ $c['ubicacion'] }}</b></div>
                                        <div id="capa2" style="margin-top:-70px;margin-right:150px;">
                                            <img class="marcafirma" src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="40px" height="40px">
                                        </div>
                                    </div>
                                @endif
                            @endfor    
                        @endif
                    @endforeach   
                </div>       
        </section>
    </body>
</html>
