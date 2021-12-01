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
                            <div style="float:left;width:24%;height:10%;text-align: center;">
                                <div>{!! DNS1D::getBarcodeSVG($c->Codigo, 'C128', 1,55,'black', true) !!}</div>
                            </div>
                        @elseif($tipo == 2)
                            <div style="float:left;width:24%;height:10%;text-align: center;">
                                <div>{!! DNS1D::getBarcodeSVG($c, 'C128', 1,55,'black', true) !!}</div>
                            </div>
                        @else
                            @for ($i = 0; $i < $numimpresiones; $i++)
                                <div style="float:left;width:24%;height:10%;text-align: center;">
                                    <div>{!! DNS1D::getBarcodeSVG($c, 'C128', 1,55,'black', true) !!}</div>
                                </div>
                            @endfor    
                        @endif
                    @endforeach   
                </div>       
        </section>
    </body>
</html>
