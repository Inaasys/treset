<!DOCTYPE html>
<html>
  <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="icon" type="image/png" href="logotipo_empresa/{{$empresa->Logo}}" sizes="16x16">
        <link rel="icon" type="image/png" href="logotipo_empresa/{{$empresa->Logo}}" sizes="32x32">
        <title>INAASYS - @yield('titulo')</title>
            @include('secciones.head')
            @yield('additionals_css')
    </head>
    <body class="theme-red" >
        <!-- Icono cargando al cargar cualquier vista en el sistema -->
        <div id="cargando" class="page-loader-wrapper">
            <div class="loader">
                <div class="preloader">
                    <div class="spinner-layer pl-black">
                        <div class="circle-clipper left">
                            <div class="circle"></div>
                        </div>
                        <div class="circle-clipper right">
                            <div class="circle"></div>
                        </div>
                    </div>
                </div>
                <p>Cargando...</p>
            </div>
        </div>
        @include('secciones.header')
        @include('secciones.aside')
        @yield('content')
        @include('utilerias.empresa')
        @include('secciones.footer')
        @yield('additionals_js')
    </body>
</html>