//CONVERTIR EN MAYUSCULAS O MINUCULAS DEPENDIENDO DE LA CONFIGURACION DEL SISTEMA
function tipoLetra(e) {
    if(mayusculas_sistema == 'S'){
        var start = e.selectionStart;
        var end = e.selectionEnd;
        e.value = e.value.toUpperCase();
        e.setSelectionRange(start, end);
        //e.value = e.value.toUpperCase();
    }
}
//FIN CONVERTIR EN MAYUSCULAS O MINUCULAS DEPENDIENDO DE LA CONFIGURACION DEL SISTEMA
//MAYUSCULAS AL INPUT
function mayusculas(e) {
    var start = e.selectionStart;
    var end = e.selectionEnd;
    e.value = e.value.toUpperCase();
    e.setSelectionRange(start, end);
    //e.value = e.value.toUpperCase();
}
//FIN MAYUSCULAS AL INPUT
//TRUNCAR UN VALOR
function truncar(num,n) {
    //var num = (arguments[0] != null) ? arguments[0] : 0;
    //var n = (arguments[1] != null) ? arguments[1] : 2;
    num = (arguments[0] !== null) ? arguments[0] : 0;
    n = (arguments[1] !== null) ? arguments[1] : 2;  
    if(num > 0){
        num = String(num);
        if(num.indexOf('.') !== -1) {
            var numarr = num.split(".");
            if (numarr.length > 1) {
                if(n > 0){
                    var temp = numarr[0] + ".";
                    for(var i = 0; i < n; i++){
                        if(i < numarr[1].length){
                            temp += numarr[1].charAt(i);
                        }
                    }
                    num = Number(temp);
                }
            }
        }
    }
    return Number(num);
}
//FIN TRUNCAR UN VALOR
//FUNCION EQUIVALENTE A number_format DE PHP RECURSO DE: https://locutus.io/php/strings/number_format/
function number_format(number, decimals, dec_point, thousands_sep) {
    // elimina cualquier caracter que no sea numerico
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Arreglo para IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}
//FIN FUNCION EQUIVALENTE A number_format DE PHP RECURSO DE: https://locutus.io/php/strings/number_format/
//REDONDEAR CORRECTAMENTE CANTIDADES COMO PHP RECURSO DE: https://wiki.developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round$revision/1383484
function round(number, precision) {
    var shift = function (number, exponent) {
      var numArray = ("" + number).split("e");
      return +(numArray[0] + "e" + (numArray[1] ? (+numArray[1] + exponent) : exponent));
    };
    return shift(Math.round(shift(number, +precision)), -precision);
}
//FIN REDONDEAR CORRECTAMENTE CANTIDADES COMO PHP RECURSO DE: https://wiki.developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round$revision/1383484
//DAR FORMATO CORRECTO A LOS INPUTS EN LOS QUE SE MANEJAN CANTIDADES EJEMPLO SI EL SISTEMA ESTA CONFIGURADO PARA 4 DECIMALES AL ESCRIBIR EL VALOR 4 SE FORMATEARA CON LOS DECIMALES ESTABLECIDOS 4.0000 SI ESCRIBIERA 4.56789 SE FORMATEARIA A 4.5678
function formatocorrectoinputcantidades(e){
    //e.value = parseFloat(e.value).toFixed(parseInt(numerodecimales));
    //e.value = truncar(e.value, numerodecimales).toFixed(parseInt(numerodecimales));
    e.value = number_format(round(e.value, numerodecimales), numerodecimales, '.', '')
}
//FIN DAR FORMATO CORRECTO A LOS INPUTS EN LOS QUE SE MANEJAN CANTIDADES EJEMPLO SI EL SISTEMA ESTA CONFIGURADO PARA 4 DECIMALES AL ESCRIBIR EL VALOR 4 SE FORMATEARA CON LOS DECIMALES ESTABLECIDOS 4.0000 SI ESCRIBIERA 4.56789 SE FORMATEARIA A 4.5678
//VOLVER A APLICAR CONFIGURACION A DATATABLE PRINCIPAL PARA QUE SE REALIZE LA BUSQUEDA CON LA TECLA ENTER
function regresarbusquedadatatableprincipal(){
    var $buscar = $('div.dataTables_filter input');
    $buscar.unbind();
    $buscar.bind('keyup change', function(e) {
        if(e.keyCode == 13 || this.value == "") {
            $('#tbllistado').DataTable().search( this.value ).draw();
        }
    });
}
//FIN VOLVER A APLICAR CONFIGURACION A DATATABLE PRINCIPAL PARA QUE SE REALIZE LA BUSQUEDA CON LA TECLA ENTER
//VALIDAR EN TODOS LOS MODULOS QUE LA FECHA DE ALTA SOLO SE DE EN EL MES ACTUAL
function validasolomesactual(){
    var fechaseleccionada = $("#fecha").val().split("-");
    var messeleccionado = fechaseleccionada[1];
    var anoseleccionado = fechaseleccionada[0];
    if(messeleccionado != meshoy || anoseleccionado != periodohoy){
      $("#fecha").val("");
      toastr.error( "Error la fecha debe ser del mes y año en curso", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
      });
    }
}
//FIN VALIDAR EN TODOS LOS MODULOS QUE LA FECHA DE ALTA SOLO SE DE EN EL MES ACTUAL
//FUNCIONES EXPORTACION DE PDF EN MODULOS
function mostrartiposeleccionfolios(){
    $("#tiposeleccionfolios").show();
}
function mostrartipofiltracionfechas(){
    $("#tipofiltracionfechas").show();
}
function ocultartiposeleccionfolios(){
    $("#tiposeleccionfolios").hide();
}
function ocultartipofiltracionfechas(){
    $("#tipofiltracionfechas").hide();
}
function destruirtablafoliosexportacion(){
    $('#tablafoliosencontrados').DataTable().clear().destroy();
}
//mostrar modal de generacion PDF
function mostrarmodalgenerarpdf(){
    ocultartipofiltracionfechas();
    ocultartiposeleccionfolios();
    $("#arraypdf").empty();
    destruirtablafoliosexportacion();
    $("#formgenerarpdf")[0].reset();
    $("#formgenerarpdf").parsley().reset();
    $("#modalgenerarpdf").modal('show');
}
//mostrar o ocultar div dependiendo el tipo de filtrado que  ocuparan para realizar los pdf
function mostrartipogeneracionpdf(){
    if($('input:radio[name=tipogeneracionpdf]:checked').val() == 0){
        mostrartiposeleccionfolios();
        ocultartipofiltracionfechas();
        $("#fechainiciopdf").removeAttr('required');
        $("#fechaterminacionpdf").removeAttr('required');
        $("#arraypdf").attr('required', 'required');
    }else{
        ocultartiposeleccionfolios();
        mostrartipofiltracionfechas();
        $("#fechainiciopdf").attr('required', 'required');
        $("#fechaterminacionpdf").attr('required', 'required');
        $("#arraypdf").removeAttr('required');
    }
}
//agregar al multiple select el folio seleccionado
function agregararraypdf(foliomodulo){
    var arraypdf = $("#arraypdf").val();
    var coincidencias = 0;
    if(arraypdf != null){
        for(var i = 0; i < arraypdf.length; i++){
            if(foliomodulo == arraypdf[i]){
                coincidencias++;
            }
        }
    }
    if(coincidencias == 0){
        $("#arraypdf").append('<option value="'+foliomodulo+'" selected>'+foliomodulo+'</option>');
    }
}
//FIN FUNCIONES EXPORTACION DE PDF EN MODULOS

////////////////////////////////////////MENSAJES TOASTR.JS INAASYS//////////////////////////////////////////
//error en permisos del usuario
function msj_errorenpermisos(){
    toastr.error( "No tiene permisos para realizar esta acción, contacta al administrador del sistema", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000" 
    });
}
//error en peticion ajax
function msj_errorajax(){
    toastr.error( "Ocurrio un error", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje correcto alta
function msj_datosguardadoscorrectamente(){
    toastr.success( "Datos guardados correctamente", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//mensaje correcto bajas
function msj_statuscambiado(){
    toastr.success( "Estatus Cambiado", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//mensaje error el vin ya existe
function msj_errorcorreoexistente(){
    toastr.error( "Error el Correo Electrónico ya existe", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje error el vin ya existe
function msj_errorvinexistente(){
    toastr.error( "Error el Vin ya existe", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje verifique que todos los datos sean correctos
function msj_verificartodoslosdatos(){
	toastr.error( "Verifique que todos los datos sean correctos", "Mensaje", {
            "timeOut": "5000",
            "progressBar": true,
            "extendedTImeout": "5000"
    });
}
//mensaje error el rfc ya existe
function msj_errorrfcexistente(){
    toastr.error( "Error el RFC ya existe", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje error el codigo ya existe
function msj_errorcodigoexistente(){
    toastr.error( "Error el código ya existe", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje error el UUID ya existe
function msj_erroruuidexistente(){
            toastr.error( "Error el uuid de la factura ya fue ingresado en el sistema", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje error comprobar el total de orden compra
function msj_errortotalordencompra(){
    toastr.error( "Debes revisar que el total de la orden de compra coincida con la suma del/los total/totales de la(s) factura(s) de/los proveedor/proveedores", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje error el producto ya fue agregado
function msj_errorproductoyaagregado(){
    toastr.error( "El producto ya fue agregado", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje error la fecha debe ser del año y mes en curso
function msj_errorfechaanoymesactual(){
    toastr.error( "Error la fecha debe ser del mes y año en curso", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje error la fecha debe ser igual a la fecha de factura del proveedor
function msj_errorfechaigualafechafactura(){
    toastr.error( "Error la fecha debe ser igual a la fecha de la factura del proveedor", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje error el total de las pertidas no coincide con el total de factura del proveedor
function msj_errortotalpartidasnocoincide(){
    toastr.error( "El total de las partidas no coincide con el total de la factura del proveedor", "Mensaje", {
        "timeOut": "5000",
        "progressBar": true,
        "extendedTImeout": "5000"
    });
}
//mensaje error se require al menos una entrada de un contrarecibo
function msj_errorentradacontrarecibo(){
	toastr.error( "Se requiere la entrada de al menos un contrarecibo ", "Mensaje", {
            "timeOut": "5000",
            "progressBar": true,
            "extendedTImeout": "5000"
    });
}
///////////////////////////////////FIN MENSAJES TOASTR.JS INAASYS///////////////////////////////////////
