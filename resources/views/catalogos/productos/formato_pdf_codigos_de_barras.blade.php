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
                <div id ="contenedor" >
                    @foreach($codigos as $c)
                        @if($tipo == 1)
                            @for ($i = 0; $i < $c['existencia']; $i++)
                                <div style="float:left;width:33%;height:119px;text-align:center;border:1px solid #AAAAAA;">
                                    <div style=" margin-top:20px;">{!! DNS1D::getBarcodeSVG($c['codigo'], 'C128', 0.7,40,'black', true) !!}</div>
                                    <div style="font-size:9px;margin-right: 20px;margin-left: 20px;"><b>{{ \Illuminate\Support\Str::limit($c['producto'], 80, $end='...') }}</b></div>
                                    <div style="font-size:10px;margin-right: 20px;margin-left: 20px;"><b>{{ $c['ubicacion'] }}</b></div>
                                    <div id="capa2" style="margin-right:200px;margin-top:-60px;">
                                        <img class="marcafirma" src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="50px" height="50px">
                                    </div>
                                </div>
                            @endfor
                        @elseif($tipo == 2)
                                <div style="float:left;width:33%;height:120px;text-align:center;border:1px solid #AAAAAA;">
                                    <div style=" margin-top:20px;">{!! DNS1D::getBarcodeSVG($c['codigo'], 'C128', 0.7,40,'black', true) !!}</div>
                                    <div style="font-size:9px;margin-right: 20px;margin-left: 20px;"><b>{{ \Illuminate\Support\Str::limit($c['producto'], 80, $end='...') }}</b></div>
                                    <div style="font-size:10px;margin-right: 20px;margin-left: 20px;"><b>{{ $c['ubicacion'] }}</b></div>
                                    <div id="capa2" style="margin-right:200px;margin-top:-60px;">
                                        <img class="marcafirma" src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="50px" height="50px">
                                    </div>
                                </div>
                        @else
                            @for ($i = 0; $i < $numimpresiones; $i++)
                                <div style="float:left;width:33%;height:120px;text-align:center;border:1px solid #AAAAAA;">
                                    <div style=" margin-top:20px;">{!! DNS1D::getBarcodeSVG($c['codigo'], 'C128', 0.7,40,'black', true) !!}</div>
                                    <div style="font-size:9px;margin-right: 20px;margin-left: 20px;"><b>{{ \Illuminate\Support\Str::limit($c['producto'], 80, $end='...') }}</b></div>
                                    <div style="font-size:10px;margin-right: 20px;margin-left: 20px;"><b>{{ $c['ubicacion'] }}</b></div>
                                    <div id="capa2" style="margin-right:200px;margin-top:-60px;">
                                        <img class="marcafirma" src="{!! public_path('logotipo_empresa/') !!}{{$empresa->Logo}}" width="50px" height="50px">
                                    </div>
                                </div>
                            @endfor    
                        @endif
                    @endforeach   
                </div>       
        </section>
    </body>
</html>
