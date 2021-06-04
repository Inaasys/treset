'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1000));
}
function asignarfechaactual(){
    /*
    var fechahoy = new Date();
    var dia = ("0" + fechahoy.getDate()).slice(-2);
    var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia) ;
    $('#fecha').val(hoy);
    $('#fechaapagar').val(hoy);
    */
    $.get(ordenes_compra_obtener_fecha_actual_datetimelocal, function(fechadatetimelocal){
        $("#fecha").val(fechadatetimelocal);
        $('#fechaapagar').val(fechadatetimelocal);
    }) 
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
    var serie = $("#serie").val();
    $.get(contrarecibos_obtener_ultimo_folio,{serie:serie}, function(folio){
        $("#folio").val(folio);
    })  
}
//cerrar modales
function limpiarmodales(){
  $("#tabsform").empty();
}
//limpiar todos los inputs del formulario alta
function limpiar(){
  $("#formparsley")[0].reset();
  //Resetear las validaciones del formulario alta
  form = $("#formparsley");
  form.parsley().reset();
  //volver a aplicar configuracion a datatable principal para que realize la busqueda con la tecla enter
  regresarbusquedadatatableprincipal();
}
//mostrar modal formulario
function mostrarmodalformulario(tipo, modificacionpermitida){
    $("#ModalFormulario").modal('show');
    if(tipo == 'ALTA'){
        $("#btnGuardar").show();
        $("#btnGuardarModificacion").hide();
    }else if(tipo == 'MODIFICACION'){
        if(modificacionpermitida == 0){
            $("#btnGuardar").hide();
            $("#btnGuardarModificacion").hide();
        }else{
            $("#btnGuardar").hide();
            $("#btnGuardarModificacion").show();
        }
    }   
}
//ocultar modal formulario
function ocultarmodalformulario(){
  $("#ModalFormulario").modal('hide');
}
//mostrar formulario en modal y ocultar tabla de seleccion
function mostrarformulario(){
  $("#formulario").show();
  $("#contenidomodaltablas").hide();
}
//mostrar tabla de seleccion y ocultar formulario en modal
function ocultarformulario(){
  $("#formulario").hide();
  $("#contenidomodaltablas").show();
}
//cambiar url para exportar excel
function cambiarurlexportarexcel(){
    //colocar el periodo seleccionado como parametro para exportar a excel
    var periodo = $("#periodo").val();
    $("#btnGenerarFormatoExcel").attr("href", urlgenerarformatoexcel+'?periodo='+periodo);
}
//relistar tabla al cambiar el periodo
function relistar(){
    cambiarurlexportarexcel();
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//listar todos los registros de la tabla
function listar(){
    cambiarurlexportarexcel();
    //Campos ordenados a mostras
    var campos = columnas_ordenadas.split(",");
    var campos_tabla  = [];
    campos_tabla.push({ 'data':'operaciones', 'name':'operaciones', 'orderable':false, 'searchable':false});
    for (var i = 0; i < campos.length; i++) {
        campos_tabla.push({ 
            'data'    : campos[i],
            'name'  : campos[i],
            'orderable': true,
            'searchable': true
        });
    }
    tabla=$('#tbllistado').DataTable({
        "lengthMenu": [ 10, 50, 100, 250, 500 ],
        "pageLength": 250,
        "sScrollX": "110%",
        "sScrollY": "350px",
        "bScrollCollapse": true,
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: contrarecibos_obtener,
            data: function (d) {
                d.periodo = $("#periodo").val();
            },
        },
        "createdRow": function( row, data, dataIndex){
            if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
            else{ $(row).addClass(''); }
        },
        columns: campos_tabla,
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistado').DataTable().search( this.value ).draw();
                }
            });
        }
    });
}
//obtener series documento
function obtenerseriesdocumento(){
    ocultarformulario();
    var seriedefault = 'A';
    var tablaseriesdocumento=   '<div class="modal-header bg-red">'+
                                    '<h4 class="modal-title">Series Documento &nbsp;&nbsp; <div class="btn bg-green btn-xs waves-effect" onclick="seleccionarseriedocumento(\''+seriedefault+'\')">Asignar Serie Default (A)</div></h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                        '<table id="tbllistadoseriedocumento" class="tbllistadoseriedocumento table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="customercolor">'+
                                            '<tr>'+
                                                '<th>Operaciones</th>'+
                                                '<th>Serie</th>'+
                                                '<th>Documento</th>'+
                                                '<th>Nombre</th>'+
                                            '</tr>'+
                                            '</thead>'+
                                            '<tbody></tbody>'+
                                        '</table>'+
                                        '</div>'+
                                    '</div>'+   
                                    '</div>'+
                                '</div>'+
                                '<div class="modal-footer">'+
                                    '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                                '</div>';  
    $("#contenidomodaltablas").html(tablaseriesdocumento);
    $('#tbllistadoseriedocumento').DataTable({
        "lengthMenu": [ 10, 50, 100, 250, 500 ],
        "pageLength": 250,
        "sScrollX": "110%",
        "sScrollY": "370px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
          'loadingRecords': '&nbsp;',
          'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
          url: contrarecibos_obtener_series_documento
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Serie', name: 'Serie' },
            { data: 'Documento', name: 'Documento' },
            { data: 'Nombre', name: 'Nombre' }
        ],
        "initComplete": function() {
          var $buscar = $('div.dataTables_filter input');
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoseriedocumento').DataTable().search( this.value ).draw();
              }
          });
        },
        
    });  
}
function seleccionarseriedocumento(Serie){
    $.get(contrarecibos_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
        $("#folio").val(folio);
        $("#serie").val(Serie);
        $("#serietexto").html("Serie: "+Serie);
        mostrarformulario();
    }) 
}
//obtener registros de proveedores
function obtenerproveedores(){
    ocultarformulario();
    var tablaproveedores =  '<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Proveedores</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadoproveedor" class="tbllistadoproveedor table table-bordered table-striped table-hover" style="width:100% !important">'+
                                                '<thead class="customercolor">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
                                                        '<th>Nombre</th>'+
                                                        '<th>R.F.C.</th>'+
                                                        '<th>Código Postal</th>'+
                                                        '<th>Teléfonos</th>'+
                                                        '<th>Email</th>'+
                                                    '</tr>'+
                                                '</thead>'+
                                                '<tbody></tbody>'+
                                            '</table>'+
                                        '</div>'+
                                    '</div>'+   
                                '</div>'+
                            '</div>'+
                            '<div class="modal-footer">'+
                                '<button type="button" class="btn btn-danger btn-sm" onclick="mostrarformulario();">Regresar</button>'+
                            '</div>';
    $("#contenidomodaltablas").html(tablaproveedores);
    $('#tbllistadoproveedor').DataTable({
        "lengthMenu": [ 10, 50, 100, 250, 500 ],
        "pageLength": 250,
        "sScrollX": "110%",
        "sScrollY": "370px",
        "bScrollCollapse": true,
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: contrarecibos_obtener_proveedores,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false },
            { data: 'CodigoPostal', name: 'CodigoPostal', orderable: false, searchable: false },
            { data: 'Telefonos', name: 'Telefonos', orderable: false, searchable: false },
            { data: 'Email1', name: 'Email1', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoproveedor').DataTable().search( this.value ).draw();
                }
            });
        },
        
    }); 
} 
function seleccionarproveedor(Numero, Nombre, Plazo, fechahoy, fechahoyespanol){
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    var numeroproveedor = Numero;
    if(numeroproveedoranterior != numeroproveedor){
        $('.page-loader-wrapper').css('display', 'block');
        $("#numeroproveedor").val(Numero);
        $("#numeroproveedoranterior").val(Numero);
        $("#proveedor").val(Nombre);
        if(Nombre != null){
            $("#textonombreproveedor").html(Nombre.substring(0, 60));
        }
        $.get(contrarecibos_obtener_compras_proveedor, {Numero:Numero}, function(data){
            $("#tabladetallecontrarecibos").html(data.filascompras);
            $("#totalcontrarecibos").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
            //funcion asincrona para colocar la fecha actual si el usuario lo desea
            cambiarfechaapagar(fechahoy, fechahoyespanol).then(resultado=>{})
            $('.page-loader-wrapper').css('display', 'none');
        });
        mostrarformulario();
    }
}
//funcion asincrona para colocar la fecha actual si el usuario lo desea
function cambiarfechaapagar(fechahoy, fechahoyespanol){
    return new Promise((ejecuta)=>{
      setTimeout(function(){ 
        var confirmacion = confirm("Aplicar esta fecha de pago a facturas? " +fechahoyespanol); 
        if (confirmacion == true) { 
                $(".fechapagarproveedor").val(fechahoy);
                $(".fechaespanoltexto").html(fechahoyespanol);
        }
        return ejecuta(confirmacion);
      },500);
    })
}
//obtener por numero
function obtenerproveedorpornumero(){
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    var numeroproveedor = $("#numeroproveedor").val();
    if(numeroproveedoranterior != numeroproveedor){
        if($("#numeroproveedor").parsley().isValid()){
            $('.page-loader-wrapper').css('display', 'block');
            var numeroproveedor = $("#numeroproveedor").val();
            $.get(contrarecibos_obtener_compras_proveedor_por_numero, {numeroproveedor:numeroproveedor}, function(data){
                $("#numeroproveedor").val(data.numero);
                $("#numeroproveedoranterior").val(data.numero);
                $("#proveedor").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombreproveedor").html(data.nombre.substring(0, 60));
                }
                $("#tabladetallecontrarecibos").html(data.filascompras);
                $("#totalcontrarecibos").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
                //funcion asincrona para colocar la fecha actual si el usuario lo desea
                cambiarfechaapagar(data.fechahoy, data.fechahoyespanol).then(resultado=>{})
                mostrarformulario();
                $('.page-loader-wrapper').css('display', 'none');
            }) 
        }
    }
}
//regresar numero
function regresarnumeroproveedor(){
    var numeroproveedoranterior = $("#numeroproveedoranterior").val();
    $("#numeroproveedor").val(numeroproveedoranterior);
}
//alta
function alta(){
    $("#titulomodal").html('Alta ContraRecibos');
    mostrarmodalformulario('ALTA');
    mostrarformulario();
    //formulario alta
    var tabs =  '<div class="col-md-12">'+
                    '<div class="row">'+
                        '<div class="col-md-3">'+
                            '<label>Contrarecibo <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                            '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                            '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                            '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                            '<input type="hidden" class="form-control" name="numerofacturas" id="numerofacturas" value="0" required readonly>'+
                        '</div>'+   
                        '<div class="col-md-3">'+
                            '<label>Proveedor <span class="label label-danger" id="textonombreproveedor"></span></label>'+
                            '<table class="col-md-12">'+
                                '<tr>'+
                                    '<td>'+
                                        '<div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer">'+
                                            '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior" required data-parsley-type="integer">'+
                                            '<input type="hidden" class="form-control" name="proveedor" id="proveedor" required readonly>'+
                                        '</div>'+
                                    '</td>'+
                                '</tr>'+   
                            '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                            '<label>Fecha Contrarecibo</label>'+
                            '<input type="datetime-local" class="form-control" name="fecha" id="fecha" onchange="validasolomesactual();asignafechapagoproveedor();" required>'+
                            '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="{{$periodohoy}}">'+
                        '</div>'+
                        '<div class="col-md-3">'+
                            '<label>Fecha del Pago al Proveedor</label>'+
                            '<input type="datetime-local" class="form-control" name="fechaapagar" id="fechaapagar" required readonly>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="col-md-12">'+
                    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                            '<a href="#productostab" data-toggle="tab">Productos</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                            '<div class="row">'+
                                '<div class="col-md-12 table-responsive cabecerafija" style="height: 350px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<table class="table table-bordered">'+
                                        '<thead class="customercolor">'+
                                            '<tr>'+
                                            '<th class="customercolor">#</th>'+
                                            '<th class="customercolor">Compra</th>'+
                                            '<th class="customercolor">Factura</th>'+
                                            '<th class="customercolor">Remisión</th>'+
                                            '<th class="customercolor">Fecha Factura</th>'+
                                            '<th class="customercolor">Plazo</th>'+
                                            '<th class="customercolor">Fecha a Pagar</th>'+
                                            '<th class="customercolor">Total $</th>'+
                                            '<th class="customercolortheadth">ContraRecibo </th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody id="tabladetallecontrarecibos">'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+ 
                            '<div class="row">'+
                            '<div class="col-md-6">'+   
                                '<label>Observaciones</label>'+
                                '<textarea class="form-control" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" rows="2" required data-parsley-length="[1, 255]"></textarea>'+
                            '</div>'+ 
                            '<div class="col-md-3 col-md-offset-3">'+
                                    '<table class="table table-striped table-hover">'+
                                        '<tr>'+
                                            '<td class="tdmod">Total ContraRecibos</td>'+
                                            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalcontrarecibos" id="totalcontrarecibos" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                        '</tr>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+   
                        '</div>'+ 
                    '</div>'+ 
                '</div>';
    $("#tabsform").html(tabs);
    obtenultimonumero();
    asignarfechaactual();
    //activar busqueda para proveedores
    $('#numeroproveedor').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        obtenerproveedorpornumero();
        }
    });
    //regresar numero proveedor
    $('#numeroproveedor').on('change', function(e) {
        regresarnumeroproveedor();
    });
}
function calculartotalcontrarecibo(tipo){
    var total = 0;
    var numerofacturas = 0;
    var lista = document.getElementsByClassName("contrarecibocompra");
    for (var i = 0; i < lista.length; i++) {
        if(lista[i].checked){
            $("#idcontrarecibocompra" + i).val(1);
            numerofacturas++;
        }else{
            $("#idcontrarecibocompra" + i).val(0);
        }
    }
    $("tr.filascompras").each(function () {
        if($(".contrarecibocompra", this).val() == 1){
            total = new Decimal(total).plus($('.totalcompra', this).val());
            if(tipo == "modificacion"){
                $(".agregadoen", this).val("NA");
            }
        }else if($(".contrarecibocompra", this).val() == 0){
            if(tipo == "modificacion"){
                $(".agregadoen", this).val("ELIMINADO");
            }
        }
    }); 
    $("#totalcontrarecibos").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
    $("#numerofacturas").val(numerofacturas);
}
//asignar la misma fecha de pago a proveedor y fecha de alta contrarecibo
function asignafechapagoproveedor(){
    var fecha = $("#fecha").val();
    $("#fechaapagar").val(fecha);
}
//colocar checked a todos los checboc antes de enviar el formulario para evitar error
function allchecked(){
    var lista = document.getElementsByClassName("contrarecibocompra");
    for (var i = 0; i < lista.length; i++) {
        lista[i].checked = true;
    }
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        if($("#numerofacturas").val() == 0){
            msj_errorentradacontrarecibo();
        }else{
            $('.page-loader-wrapper').css('display', 'block');
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url:contrarecibos_guardar,
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success:function(data){
                    msj_datosguardadoscorrectamente();
                    limpiar();
                    ocultarmodalformulario();
                    limpiarmodales();
                    $('.page-loader-wrapper').css('display', 'none');
                },
                error:function(data){
                    if(data.status == 403){
                        msj_errorenpermisos();
                    }else{
                        msj_errorajax();
                    }
                    $('.page-loader-wrapper').css('display', 'none');
                }
            })
        }
    }else{
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(contrarecibodesactivar){
    $.get(contrarecibos_verificar_si_continua_baja,{contrarecibodesactivar:contrarecibodesactivar}, function(data){
        if(data.Status == 'BAJA'){
            $("#contrarecibodesactivar").val(0);
            $("#textomodaldesactivar").html('Error, este contrarecibo ya fue dado de baja');
            $("#divmotivobaja").hide();
            $("#btnbaja").hide();
            $('#estatusregistro').modal('show');
        }else{    
            if(data.resultadofechas != ''){
                $("#contrarecibodesactivar").val(0);
                $("#textomodaldesactivar").html('Error solo se pueden dar de baja los contrarecibos del mes actual, fecha del contrarecibo: ' + data.resultadofechas);
                $("#divmotivobaja").hide();
                $("#btnbaja").hide();
                $('#estatusregistro').modal('show');
            }else{
                $("#contrarecibodesactivar").val(contrarecibodesactivar);
                $("#textomodaldesactivar").html('Estas seguro de dar de baja el contrarecibo? No'+ contrarecibodesactivar);
                $("#divmotivobaja").show();
                $("#btnbaja").show();
                $('#estatusregistro').modal('show');
            }
        }
    }) 
}
$("#btnbaja").on('click', function(e){
    e.preventDefault();
    var formData = new FormData($("#formdesactivar")[0]);
    var form = $("#formdesactivar");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:contrarecibos_baja,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
            $('#estatusregistro').modal('hide');
            msj_datosguardadoscorrectamente();
            $('.page-loader-wrapper').css('display', 'none');
        },
        error:function(data){
            if(data.status == 403){
                msj_errorenpermisos();
            }else{
                msj_errorajax();
            }
            $('#estatusregistro').modal('hide');
            $('.page-loader-wrapper').css('display', 'none');
        }
        })
    }else{
        form.parsley().validate();
    }
});
function obtenerdatos(contrarecibomodificar){
    $("#titulomodal").html('Modificación ContraRecibos');
    $('.page-loader-wrapper').css('display', 'block');
    $.get(contrarecibos_obtener_contrarecibo,{contrarecibomodificar:contrarecibomodificar },function(data){
    //formulario modificacion
    var tabs =  '<div class="col-md-12">'+
                    '<div class="row">'+
                        '<div class="col-md-3">'+
                            '<label>Contrarecibo <b style="color:#F44336 !important;" id="serietexto"> Serie:</b></label>'+
                            '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                            '<input type="hidden" class="form-control" name="serie" id="serie" required readonly data-parsley-length="[1, 10]">'+
                            '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" readonly>'+
                            '<input type="hidden" class="form-control" name="numerofacturas" id="numerofacturas" required readonly>'+
                        '</div>'+   
                        '<div class="col-md-3">'+
                            '<label>Proveedor <span class="label label-danger" id="textonombreproveedor"></span></label>'+
                            '<table class="col-md-12">'+
                                '<tr>'+
                                    '<td hidden>'+
                                        '<div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()">Seleccionar</div>'+
                                    '</td>'+
                                    '<td>'+
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control" name="numeroproveedor" id="numeroproveedor" required data-parsley-type="integer">'+
                                            '<input type="hidden" class="form-control" name="numeroproveedoranterior" id="numeroproveedoranterior"  required data-parsley-type="integer">'+
                                            '<input type="hidden" class="form-control" name="proveedor" id="proveedor" required readonly>'+
                                        '</div>'+
                                    '</td>'+
                                '</tr>'+   
                            '</table>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                            '<label>Fecha Contrarecibo</label>'+
                            '<input type="datetime-local" class="form-control" name="fecha" id="fecha" onchange="validasolomesactual();asignafechapagoproveedor();" required>'+
                            '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy">'+
                        '</div>'+
                        '<div class="col-md-3">'+
                            '<label>Fecha del Pago al Proveedor</label>'+
                            '<input type="datetime-local" class="form-control" name="fechaapagar" id="fechaapagar" required readonly>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="col-md-12">'+
                    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                            '<a href="#productostab" data-toggle="tab">Productos</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                            '<div class="row">'+
                                '<div class="col-md-12 table-responsive cabecerafija" style="height: 350px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<table class="table table-bordered">'+
                                        '<thead class="customercolor">'+
                                            '<tr>'+
                                            '<th class="customercolor">#</th>'+
                                            '<th class="customercolor">Compra</th>'+
                                            '<th class="customercolor">Factura</th>'+
                                            '<th class="customercolor">Remisión</th>'+
                                            '<th class="customercolor">Fecha Factura</th>'+
                                            '<th class="customercolor">Plazo</th>'+
                                            '<th class="customercolor">Fecha a Pagar</th>'+
                                            '<th class="customercolor">Total $</th>'+
                                            '<th class="customercolortheadth">ContraRecibo </th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody id="tabladetallecontrarecibos">'+         
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+ 
                            '<div class="row">'+
                            '<div class="col-md-6">'+   
                                '<label>Observaciones</label>'+
                                '<textarea class="form-control" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" rows="2" required data-parsley-length="[1, 255]"></textarea>'+
                            '</div>'+ 
                            '<div class="col-md-3 col-md-offset-3">'+
                                    '<table class="table table-striped table-hover">'+
                                        '<tr>'+
                                            '<td class="tdmod">Total ContraRecibos</td>'+
                                            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalcontrarecibos" id="totalcontrarecibos" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                        '</tr>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+   
                        '</div>'+ 
                    '</div>'+ 
                '</div>';
    $("#tabsform").html(tabs); 
    $("#periodohoy").val(data.contrarecibo.Periodo);
    $("#folio").val(data.contrarecibo.Folio);
    $("#serie").val(data.contrarecibo.Serie);
    $("#serietexto").html("Serie: "+data.contrarecibo.Serie);
    $("#numeroproveedor").val(data.proveedor.Numero);
    $("#numeroproveedoranterior").val(data.proveedor.Numero);
    $("#proveedor").val(data.proveedor.Nombre);
    if(data.proveedor.Nombre != null){
        $("#textonombreproveedor").html(data.proveedor.Nombre.substring(0, 60));
    }
    $("#btnobtenerproveedores").hide();
    $("#fecha").val(data.fecha);
    $("#fechaapagar").val(data.fecha);
    $("#observaciones").val(data.contrarecibo.Obs);
    $("#totalcontrarecibos").val(data.total);
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //tabs detalles
    $("#tabladetallecontrarecibos").html(data.filasdetallescontrarecibo);
    $("#numerofacturas").val(data.contrarecibo.Facturas);
    //busquedas seleccion
    //regresar numero proveedor
    $('#numeroproveedor').on('change', function(e) {
        regresarnumeroproveedor();
    });
    mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
    mostrarformulario();
    $('.page-loader-wrapper').css('display', 'none');
  }).fail( function() {
    msj_errorajax();    
    $('.page-loader-wrapper').css('display', 'none');
  })
}
//cambios
$("#btnGuardarModificacion").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        if($("#numerofacturas").val() == 0){
            msj_errorentradacontrarecibo();
        }else{
            $('.page-loader-wrapper').css('display', 'block');
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url:contrarecibos_guardar_modificacion,
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success:function(data){
                    msj_datosguardadoscorrectamente();
                    limpiar();
                    ocultarmodalformulario();
                    limpiarmodales();
                    $('.page-loader-wrapper').css('display', 'none');
                },
                error:function(data){
                    if(data.status == 403){
                        msj_errorenpermisos();
                    }else{
                        msj_errorajax();
                    }
                    $('.page-loader-wrapper').css('display', 'none');
                }
            })
        }
    }else{
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
    $.get(contrarecibos_obtener_datos_envio_email,{documento:documento}, function(data){
      $("#textomodalenviarpdfemail").html("Enviar email ContraRecibo No." + documento);
      $("#emaildocumento").val(documento);
      $("#emailde").val(data.emailde);
      $("#emailpara").val(data.emailpara);
      $("#emailasunto").val("CONTRARECIBO NO. " + documento +" DE USADOS TRACTOCAMIONES Y PARTES REFACCIONARIAS SA DE CV");
      $("#modalenviarpdfemail").modal('show');
    })   
}
//enviar documento pdf por email
$("#btnenviarpdfemail").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formenviarpdfemail")[0]);
    var form = $("#formenviarpdfemail");
    if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:contrarecibos_enviar_pdfs_email,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          msj_documentoenviadoporemailcorrectamente();
          $("#modalenviarpdfemail").modal('hide');
          $('.page-loader-wrapper').css('display', 'none');
        },
        error:function(data){
          if(data.status == 403){
            msj_errorenpermisos();
          }else{
            msj_errorajax();
          }
          $("#modalenviarpdfemail").modal('hide');
          $('.page-loader-wrapper').css('display', 'none');
        }
      })
    }else{
      form.parsley().validate();
    }
});
//hacer busqueda de folio para exportacion en pdf
function relistarbuscarstringlike(){
    var tabla = $('#tablafoliosencontrados').DataTable();
    tabla.ajax.reload();
}
function buscarstringlike(){
    var columnastablafoliosencontrados =  '<tr>'+
                                          '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                          '<th>ContraRecibo</th>'+
                                          '<th>Proveedor</th>'+
                                          '<th>Total</th>'+
                                          '<th>Status</th>'+
                                        '</tr>';
    $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
    tabla=$('#tablafoliosencontrados').DataTable({
        "paging":   false,
        "ordering": false,
        "info":     false,
        "searching": false,
        processing: true,
        serverSide: true,
        ajax: {
            url: contrarecibos_buscar_folio_string_like,
            data: function (d) {
                d.string = $("#buscarfolio").val();
            },
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'ContraRecibo', name: 'ContraRecibo' },
            { data: 'Proveedor', name: 'Proveedor', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false  },
            { data: 'Status', name: 'Status', orderable: false, searchable: false  },
        ],
    });
}
//configurar tabla
function configurar_tabla(){
    //formulario configuracion tabla
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#tabcamposamostrar" data-toggle="tab">Campos a mostrar</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#tabordenarcolumnas" data-toggle="tab">Ordenar Columnas</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="tabcamposamostrar">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>DATOS CONTRARECIBO</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="ContraRecibo" id="idContraRecibo" class="filled-in datotabla" value="ContraRecibo" readonly onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idContraRecibo">ContraRecibo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" readonly onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idStatus">Status</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Serie" id="idSerie" class="filled-in datotabla" value="Serie" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idSerie">Serie</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Folio" id="idFolio" class="filled-in datotabla" value="Folio" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFolio">Folio</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Fecha" id="idFecha" class="filled-in datotabla" value="Fecha" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFecha">Fecha</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Proveedor" id="idProveedor" class="filled-in datotabla" value="Proveedor" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idProveedor">Proveedor</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Total" id="idTotal" class="filled-in datotabla" value="Total" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTotal">Total</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Obs" id="idObs" class="filled-in datotabla" value="Obs" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idObs">Obs</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+    
                                    '<input type="checkbox" name="Periodo" id="idPeriodo" class="filled-in datotabla" value="Periodo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idPeriodo">Periodo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Facturas" id="idFacturas" class="filled-in datotabla" value="Facturas" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idFacturas">Facturas</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="MotivoBaja" id="idMotivoBaja" class="filled-in datotabla" value="MotivoBaja" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idMotivoBaja">MotivoBaja</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Equipo" id="idEquipo" class="filled-in datotabla" value="Equipo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idEquipo">Equipo</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Usuario" id="idUsuario" class="filled-in datotabla" value="Usuario" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idUsuario">Usuario</label>'+
                                '</div>'+
                                '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                                '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>DATOS PROVEEDOR</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="NumeroProveedor" id="idNumeroProveedor" class="filled-in datotabla" value="NumeroProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNumeroProveedor">NumeroProveedor</label>'+  
                                '</div>'+
                                '<div class="col-md-4 form-check">'+  
                                    '<input type="checkbox" name="NombreProveedor" id="idNombreProveedor" class="filled-in datotabla" value="NombreProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idNombreProveedor">NombreProveedor</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="RfcProveedor" id="idRfcProveedor" class="filled-in datotabla" value="RfcProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idRfcProveedor">RfcProveedor</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="CodigoPostalProveedor" id="idCodigoPostalProveedor" class="filled-in datotabla" value="CodigoPostalProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idCodigoPostalProveedor">CodigoPostalProveedor</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="PlazoProveedor" id="idPlazoProveedor" class="filled-in datotabla" value="PlazoProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idPlazoProveedor">PlazoProveedor</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="TelefonosProveedor" id="idTelefonosProveedor" class="filled-in datotabla" value="TelefonosProveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idTelefonosProveedor">TelefonosProveedor</label>'+ 
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Email1Proveedor" id="idEmail1Proveedor" class="filled-in datotabla" value="Email1Proveedor"  onchange="construirarraydatostabla(this);"/>'+
                                    '<label for="idEmail1Proveedor">Email1Proveedor</label>'+                                     
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+ 
                    '<div role="tabpanel" class="tab-pane fade" id="tabordenarcolumnas">'+
                        '<div class="row">'+
                            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">'+
                                '<div class="card">'+
                                    '<div class="header">'+
                                        '<h2>'+
                                            'Ordenar Columnas'+
                                            '<small>Ordena las columnas arrastrándolas hacia arriba o hacia abajo. </small>'+
                                        '</h2>'+
                                    '</div>'+
                                    '<div class="body">'+
                                        '<div class="clearfix m-b-20">'+
                                            '<div class="dd" onchange="ordenarcolumnas()">'+
                                                '<ol class="dd-list" id="columnasnestable">'+
                                                '</ol>'+
                                            '</div>'+
                                        '</div>'+
                                        '<input type="hidden" id="string_datos_ordenamiento_columnas" name="string_datos_ordenamiento_columnas" class="form-control" required>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+      
                    '</div>'+
                '</div>';
    $("#tabsconfigurartabla").html(tabs);
    $("#string_datos_ordenamiento_columnas").val(columnas_ordenadas);
    $("#string_datos_tabla_true").val(campos_activados);
    $("#string_datos_tabla_false").val(campos_desactivados);
    $("#modalconfigurartabla").modal('show');
    $("#titulomodalconfiguraciontabla").html("Configuración de la tabla");
    $('.dd').nestable();
    //campos activados
    var campos = campos_activados.split(",");
    for (var i = 0; i < campos.length; i++) {
        $("input[name='"+campos[i]+"']").prop('checked', true);
    }
    //crear lista para ordenar columnas
    var campos_ordenados = columnas_ordenadas.split(",");
    $("#columnasnestable").empty();
    for (var i = 0; i < campos_ordenados.length; i++) {
        var columna =   '<li class="dd-item nestable'+campos_ordenados[i]+'">'+
                            '<div class="dd-handle">'+campos_ordenados[i]+'</div>'+
                            '<input type="hidden" class="inputnestable" value="'+campos_ordenados[i]+'">'+
                        '</li>';
        $("#columnasnestable").append(columna);
    }
  }
init();