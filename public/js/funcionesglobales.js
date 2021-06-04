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
        "timeOut": "9500",
        "progressBar": true,
        "extendedTImeout": "5000"
      });
    }
}
//FIN VALIDAR EN TODOS LOS MODULOS QUE LA FECHA DE ALTA SOLO SE DE EN EL MES ACTUAL
//FUNCIONES EXPORTACION DE PDF EN MODULOS
//muestra la exportacion por folio
function mostrartiposeleccionfolios(){
    $("#tiposeleccionfolios").show();
}
//muestra la exportacion por filtrado de fechas
function mostrartipofiltracionfechas(){
    $("#tipofiltracionfechas").show();
}
//oculta la exportacion por folio
function ocultartiposeleccionfolios(){
    $("#tiposeleccionfolios").hide();
}
//oculta la exportacion por filtrado de fechas
function ocultartipofiltracionfechas(){
    $("#tipofiltracionfechas").hide();
}
//destruye la tabla de folio encontrados
function destruirtablafoliosexportacion(){
    $('#tablafoliosencontrados').DataTable().clear().destroy();
}
//mostrar modal de generacion PDF
function mostrarmodalgenerarpdf(){
    ocultartipofiltracionfechas();
    ocultartiposeleccionfolios();
    $("#arraypdf").empty();
    $("#formgenerarpdf")[0].reset();
    $("#formgenerarpdf").parsley().reset();
    $("#modalgenerarpdf").modal('show');
}
//mostrar o ocultar div dependiendo el tipo de filtrado que  ocuparan para realizar los pdf
function mostrartipogeneracionpdf(){
    if($('input:radio[name=tipogeneracionpdf]:checked').val() == 0){
        buscarstringlike();
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
        $('#tablafoliosencontrados').DataTable().clear().destroy();
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
//validar que el rango de las fecha de inicio y fin para la creacion de documentos sea maximos 2 meses y validar que la fecha final sea mayor a la fecha inicial en la creacion de documentos
function validarrangofechascreaciondocumentos(){
    var fechainiciopdf = $("#fechainiciopdf").val();
    var fechaterminacionpdf = $("#fechaterminacionpdf").val();
    if(Date.parse(fechaterminacionpdf) < Date.parse(fechainiciopdf)) {
        toastr.error( "Error, la fecha de terminación debe ser mayor a la fecha de inicio", "Mensaje", {
            "timeOut": "5000",
            "progressBar": true,
            "extendedTImeout": "5000" 
        });
        $("#btngenerardocumentospdf").hide();
    }else{
        var fechadesde = new Date(fechainiciopdf);
        var fechahasta = new Date(fechaterminacionpdf);    
        var milisegundostranscurridos = fechahasta - fechadesde;// diferencia en milisegundos
        var diastranscurridos = milisegundostranscurridos / (1000 * 60 * 60 * 24) // diferencia en dias
        if(parseInt(diastranscurridos) > 60){
            toastr.error( "Error, el rango entre ambas fechas no debe ser mayor a 60 días", "Mensaje", {
                "timeOut": "5000",
                "progressBar": true,
                "extendedTImeout": "5000" 
            });
            $("#btngenerardocumentospdf").hide();
        }else{
            $("#btngenerardocumentospdf").show();
        }
    }
}
//FIN FUNCIONES EXPORTACION DE PDF EN MODULOS
////////////////////////////////////////MENSAJES TOASTR.JS INAASYS//////////////////////////////////////////
//error en permisos del usuario
function msj_errorenpermisos(){
    toastr.error( "No tiene permisos para realizar esta acción, contacta al administrador del sistema", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000" 
    });
}
//error en peticion ajax
function msj_mantenimientoajax(){
    toastr.info( "Aviso, estamos trabajando en esta función, pronto podras hacer uso de ella", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//error en peticion ajax
function msj_errorajax(){
    toastr.error( "Aviso, estamos experimentando problemas, contacta al administrador del sistema", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje correcto alta
function msj_datosguardadoscorrectamente(){
    toastr.success( "Datos guardados correctamente", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//mensaje correcto bajas
function msj_statuscambiado(){
    toastr.success( "Estatus Cambiado", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//mensaje error el vin ya existe
function msj_errorcorreoexistente(){
    toastr.error( "Aviso, el Correo Electrónico ya existe", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error el vin ya existe
function msj_errorvinexistente(){
    toastr.error( "Aviso, el Vin ya existe", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje verifique que todos los datos sean correctos
function msj_verificartodoslosdatos(){
	toastr.error( "Aviso, verifique que todos los datos sean correctos", "Mensaje", {
            "timeOut": "6000",
            "progressBar": true,
            "extendedTImeout": "6000"
    });
}
//mensaje error el rfc ya existe
function msj_errorrfcexistente(){
    toastr.error( "Aviso, el RFC ya existe", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error el codigo ya existe
function msj_errorcodigoexistente(){
    toastr.error( "Aviso, el código ya existe", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error la orden ya existe
function msj_errorordenexistente(){
    toastr.error( "Aviso, el número de orden ya existe", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error el UUID ya existe
function msj_erroruuidexistente(){
            toastr.error( "Aviso, el uuid de la factura ya fue ingresado en el sistema", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error comprobar el total de orden compra
function msj_errortotalordencompra(){
    toastr.error( "Aviso, debes revisar que el total de la orden de compra coincida con la suma del/los total/totales de la(s) factura(s) de/los proveedor/proveedores", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error el producto ya fue agregado
function msj_errorproductoyaagregado(){
    toastr.error( "Aviso, este producto ya esta agregado", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error el codigo no existe en la orden
function msj_errorcodigonoexisteenorden(){
    toastr.error( "Aviso, el código no existe en la orden de compra", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error el servicio ya fue agregado
function msj_errorservicioyaagregado(){
    toastr.error( "Aviso, el servicio ya fue agregado", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}

//mensaje error la compra ya fue agregada
function msj_errorcomprayaagregada(){
    toastr.error( "Aviso, el código de la compra ya fue agregado", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    }); 
}
//mensaje error la fecha debe ser del año y mes en curso
function msj_errorfechaanoymesactual(){
    toastr.error( "Aviso, la fecha debe ser del mes y año en curso", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error la fecha debe ser igual a la fecha de factura del proveedor
function msj_errorfechaigualafechafactura(){
    toastr.error( "Aviso, la fecha debe ser igual a la fecha de la factura del proveedor", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error el total de las pertidas no coincide con el total de factura del proveedor
function msj_errortotalpartidasnocoincide(){
    toastr.error( "Aviso, el total de las partidas no coincide con el total de la factura del proveedor", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error el RFC del proveedor no es igual al RFC del xml
function msj_errorrfcdistinto(){
    toastr.error( "Aviso, el RFC del proveedor no es igual al RFC del xml", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error esta factura no corresponde al RFC de la empresa
function msj_errorrfcreceptordistinto(){
    toastr.error( "Aviso, esta factura no corresponde al RFC de la empresa", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error se require al menos una entrada de un contrarecibo
function msj_errorentradacontrarecibo(){
	toastr.error( "Aviso, se requiere la entrada de al menos un contrarecibo ", "Mensaje", {
            "timeOut": "6000",
            "progressBar": true,
            "extendedTImeout": "6000"
    });
}
//mensaje error el personal no cuenta con herramienta asignada para auditar
function msj_errorpersonalsinherramientaasignada(){
	toastr.error( "Aviso, el personal seleccionado no tiene herramienta asignada que auditar", "Mensaje", {
            "timeOut": "6000",
            "progressBar": true,
            "extendedTImeout": "6000"
    });
}
//mensaje termino prestamo menor a inicio prestamo
function msjterminoprestamomenor(){
    toastr.error( "Aviso, la fecha de termino no puede ser menor a la fecha de inicio", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje herramienta agregada correctamente
function msj_herramientagregadocorrectamente(){
    toastr.success( "Aviso, la herramienta se agrego correctamente", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje herramient ya agregada
function msj_errorherramientayaagregado(){
    toastr.error( "Aviso, la herramienta solo se puede agregar una vez", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error herramienta sin existencias para prestamo
function msj_errorherramientasinexistenciasparaprestamo(){
    toastr.error( "Aviso, todas las existencias de la herramienta seleccionada ya estan prestadas", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje info el persona tiene asignaciones sin autorizar
function msj_infopersonalconasignacionesporautorizar(){
    toastr.info( "Recomendación, el personal seleccionado tiene asignaciones sin autorizar, antes de auditar al personal se recomienda tener autorizadas todas sus asignaciones de herramienta", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//la fecha final es mayor al dia de hoy
function msjfechafinalmayorahoy(){
    toastr.error( "Aviso, la fecha final del reporte no puede ser mayor a la fecha actual", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//la fecha incial es mayor a la fecha final
function msjfechainicialmayorafechafinal(){
    toastr.error( "Aviso, la fecha inicial no puede ser mayor a la fecha final del reporte", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//msj debe seleccionar al menos una compra para generar el formato en excel
function msjseleccionaunacompra(){
    toastr.info( "Aviso, debe seleccionar al menos una compra para poder realizar el formato de la caja chica en excel", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//msj faltan datos por capturar OT
function msjfaltandatosporcapturar(){
    toastr.error( "Aviso, faltan datos por capturar, revisa todas las pestañas del formulario", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });   
}
//msj tecnico ya agregado OT
function msj_errortecnicoyaagregado(){
    toastr.error( "Aviso, el técnico ya esta agregado", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    }); 
}
//msj solo se puedne agregar 4 tecnicos OT
function msjsolo4tecnicospermitidos(){
    toastr.error( "Aviso, solo se permiten agregar 4 técnicos", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    }); 
}
//msj El total de horas trabajadas por los técnicos es distinto al total de horas facturadas OT
function msjtotalhorasnocorresponden(){
    toastr.error( "Aviso, el total de horas trabajadas por los técnicos es distinto al total de horas facturadas", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });     
}
//msj para quitar refaccion cancela su traspaso
function msjerrorcancelartraspaso(){
    toastr.error( "Aviso, para quitar la refacción cancela el traspaso al que corresponde", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });   
}
//msj orden terminada correctamente
function msj_ordenterminada(){
    toastr.success( "Aviso, la orden de trabajo se termino correctamente", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//msj la remision ya ha sido utilizada en una cotizacion
function msjremisionyautilizada(){
    toastr.error( "Aviso, la remisión ya fue utilizada en una cotización", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });       
}
//mensaje error se require al menos una entrada de una partida
function msj_erroralmenosunaentrada(){
	toastr.error( "Aviso, se requiere la entrada de al menos una partida", "Mensaje", {
            "timeOut": "6000",
            "progressBar": true,
            "extendedTImeout": "6000"
    });
}
//mensaje solo se admite 1 compra para devolución de producto
function msj_errorsolo1compraparadevoluciones(){
	toastr.error( "Aviso, solo se admite 1 compra para devolución de producto", "Mensaje", {
            "timeOut": "6000",
            "progressBar": true,
            "extendedTImeout": "6000"
    });
}
//mensaje error el total de la nota debe ser igual al total de los descuentos
function msj_errorendiferenciatotalnotatotaldescuentos(){
    toastr.error( "Aviso, el total de la nota debe ser igual al total de los descuentos", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    }); 
}
//mensaje solo se admite 1 factura
function msj_errorsolo1factura(){
	toastr.error( "Aviso, solo se admite 1 factura para selección de códigos, para 2 o mas facturas se aplican DPPP", "Mensaje", {
            "timeOut": "6000",
            "progressBar": true,
            "extendedTImeout": "6000"
    });
}
//mensaje error al menos debe haber una partida agregada por tabla
function msj_erroralmenosunapartidaagregada(){
    toastr.error( "Aviso, al menos debe haber una partida agregada por tabla", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje error elige al menos un almacén
function msj_erroreligeunalmacen(){
    toastr.error( "Aviso, elige al menos un almacén", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
}
//mensaje remision agregada correctamente
function remisionagregadacorrectamente(){
    toastr.success( "Aviso, la remisión se agrego correctamente a las partidas", "Mensaje", {
        "timeOut": "6000",
        "positionClass": "toast-top-left",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
} 
//mensaje remision eliminada correctamente
function remisioneliminadacorrectamente(){
    toastr.success( "Aviso, la remisión se elimino correctamente de las partidas", "Mensaje", {
        "timeOut": "6000",
        "positionClass": "toast-top-left",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
} 
//mensaje orden agregada correctamente
function ordenagregadacorrectamente(){
    toastr.success( "Aviso, la orden se agrego correctamente a las partidas", "Mensaje", {
        "timeOut": "6000",
        "positionClass": "toast-top-left",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
} 
//mensaje orden eliminada correctamente
function ordeneliminadacorrectamente(){
    toastr.success( "Correcto, la orden se elimino correctamente de las partidas", "Mensaje", {
        "timeOut": "6000",
        "positionClass": "toast-top-left",
        "progressBar": true,
        "extendedTImeout": "6000"
    });
} 
//mensaje documento enviado correctamente
function msj_documentoenviadoporemailcorrectamente(){
    toastr.success( "Correcto, el documento se envió", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });  
}
//mensaje este usuario ya cuenta con una serie igual para el documento seleccionado, cambia la serie
function msj_errorserieexistenteendocumento(){
    toastr.error( "Aviso, este usuario ya cuenta con una serie igual para el documento seleccionado, cambia la serie o documento", "Mensaje", {
        "timeOut": "6000",
        "progressBar": true,
        "extendedTImeout": "6000"
    });  
}
///////////////////////////////////FIN MENSAJES TOASTR.JS INAASYS///////////////////////////////////////
//////////////////////////////FUNCIONES PARA CONFIGURACION DE COLUMNAS DE TABLAS/////////////////////////////////////////
//ordenar las columnas para vista de tabla
function ordenarcolumnas(){
    var string_datos_ordenamiento_columnas = [];
    var lista = document.getElementsByClassName("inputnestable");
    for (var i = 0; i < lista.length; i++) {
        string_datos_ordenamiento_columnas.push(lista[i].value);
    }
    $("#string_datos_ordenamiento_columnas").val(string_datos_ordenamiento_columnas);
}
//permitir o reestringir acceso al menu
function construirarraydatostabla(e){
    //agregar columna seleccionada en la nestable ordenamiento de columnas
    var columna =   '<li class="dd-item nestable'+e.value+'">'+
                        '<div class="dd-handle">'+e.value+'</div>'+
                        '<input type="hidden" class="inputnestable" value="'+e.value+'">'+
                    '</li>';
    $("#columnasnestable").append(columna);
    $("#string_datos_ordenamiento_columnas").val($("#string_datos_ordenamiento_columnas").val()+","+e.value);
    var string_datos_tabla_true = [];
    var string_datos_table_false = [];
    var lista = document.getElementsByClassName("datotabla");
    for (var i = 0; i < lista.length; i++) {
        if(lista[i].checked){
            string_datos_tabla_true.push(lista[i].name);
        }else{
            $(".nestable"+lista[i].name).remove();
            string_datos_table_false.push(lista[i].name);
        }
    }
    $("#string_datos_tabla_true").val(string_datos_tabla_true);
    $("#string_datos_tabla_false").val(string_datos_table_false);
    ordenarcolumnas();
}
//////////////////////////////FIN FUNCIONES PARA CONFIGURACION DE COLUMNAS DE TABLAS/////////////////////////////////////////
