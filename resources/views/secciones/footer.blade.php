    <!--RUTAS ATAJOS-->
    <script>
        var ordenes_compra = '{!!URL::to('ordenes_compra')!!}';
        var compras = '{!!URL::to('compras')!!}';
        var remisiones = '{!!URL::to('remisiones')!!}';
        var facturas = '{!!URL::to('facturas')!!}';
        var productos = '{!!URL::to('productos')!!}';
        var existencias = '{!!URL::to('existencias')!!}';
        var requisiciones = '{!!URL::to('requisiciones')!!}';
        var produccion = '{!!URL::to('produccion')!!}';
        var traspasos = '{!!URL::to('traspasos')!!}';
        var ajustesinventario = '{!!URL::to('ajustesinventario')!!}';
        var notas_credito_clientes = '{!!URL::to('notas_credito_clientes')!!}';
        var notas_credito_proveedores = '{!!URL::to('notas_credito_proveedores')!!}';
        var ordenes_trabajo = '{!!URL::to('ordenes_trabajo')!!}';
        var clientes = '{!!URL::to('clientes')!!}';
        var proveedores = '{!!URL::to('proveedores')!!}';
        var puntodeventa = '{!!URL::to('punto_de_venta')!!}';
        var generar_documentos_obtener_series_disponibles_documentos = '{!!URL::to('generar_documentos_obtener_series_disponibles_documentos')!!}';
        
        /*TIPO USUARIO LOGUEADO EN EL SISTEMA*/
        var rol_de_usuario_logueado_en_sistema = '{{Auth::user()->role_id}}';
    </script>
    <!-- Jquery Core Js -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap Core Js -->
    <script src="plugins/bootstrap/js/bootstrap.js"></script>
    <!-- Select Plugin Js -->
    <script src="plugins/bootstrap-select/js/bootstrap-select.js"></script>
    <!-- Slimscroll Plugin Js -->
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <!-- Waves Effect Plugin Js -->
    <script src="plugins/node-waves/waves.js"></script>
    <!-- Custom Js -->
    <script src="js/admin.js"></script>
    <!-- Demo Js 
    <script src="js/demo.js"></script>-->
    <!-- Atajos teclas -->
    <!--https://rawgit.com/jeresig/jquery.hotkeys/master/test-static-01.html-->
    <!--https://github.com/jeresig/jquery.hotkeys-->

    <script src="js/atajosteclas/jquery.hotkeys.js"></script>
    <!-- cargador de imagenes input file-->
    <script src="js/dropify/dropify.min.js"></script>
    <script src="js/dropify/forms_file_input.min.js"></script>   
    <script src="js/funcionesglobales.js"></script>
    <script src="scripts_inaasys/utilerias/empresa.js"></script>
    <script>
        /*SOLUCION :  warning Added non-passive event listener to a scroll-blocking <some> event. Consider marking event handler as 'passive' to make the page more responsive.*/
        jQuery.event.special.touchstart = {
            setup: function( _, ns, handle ){
                if ( ns.includes("noPreventDefault") ) {
                    this.addEventListener("touchstart", handle, { passive: false });
                } else {
                    this.addEventListener("touchstart", handle, { passive: true });
                }
            }
        };
        //desabilitar los submenus segun la configuracion del usuario logueado
        $(function(){
            $.get('{!!URL::to('usuarios_obtener_submenus_activos')!!}',function(data){
                $.each(data.array_submenus,function(key, registro) {
                    if(registro[1] == "true"){
                        //$('#' + registro[0]).click(function () {return true;});
                        $('#' + registro[0]).show();
                    }else{
                        //$('#' + registro[0]).click(function () {return false;});
                        $('#' + registro[0]).hide();
                    }  
                });
            });
        });
    </script>
    @if(\Session::has('error'))
        <link href="css/toastr/toastr.min.css" rel="stylesheet">
        <script src="js/toastr/toastr.min.js"></script>
        <script src="js/toastr/toastr.init.js"></script>
        <script>
            var msj = {'msj': '{{Session::get("error")}}'}
            toastr.error(msj.msj, "Aviso!", {
                "timeOut": "5000",
                "progressBar": true,
                "extendedTImeout": "5000"
            });
        </script>
    @endif  