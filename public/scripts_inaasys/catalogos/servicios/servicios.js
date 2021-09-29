'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
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
function mostrarmodalformulario(tipo){
    $("#ModalFormulario").modal('show');
    if(tipo == 'ALTA'){
        $("#btnGuardar").show();
        $("#btnGuardarModificacion").hide();
    }else if(tipo == 'MODIFICACION'){
        $("#btnGuardar").hide();
        $("#btnGuardarModificacion").show();
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
//listar todos los registros de la tabla
function listar(){
    tabla=$('#tbllistado').DataTable({
        "lengthMenu": [ 100, 250, 500, 1000 ],
        "pageLength": 1000,
        "sScrollX": "110%",
        "sScrollY": "350px",
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: servicios_obtener,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Codigo', name: 'Codigo', orderable: false, searchable: true },
            { data: 'Servicio', name: 'Servicio', orderable: false, searchable: true },
            { data: 'Unidad', name: 'Unidad', orderable: false, searchable: false },
            { data: 'NumeroFamilia', name: 'NumeroFamilia', orderable: false, searchable: false },
            { data: 'Familia', name: 'Familia', orderable: false, searchable: false },
            { data: 'Costo', name: 'Costo', orderable: false, searchable: false },
            { data: 'Venta', name: 'Venta', orderable: false, searchable: false },
            { data: 'Cantidad', name: 'Cantidad', orderable: false, searchable: false },
            { data: 'ClaveProducto', name: 'ClaveProducto', orderable: false, searchable: false },
            { data: 'ClaveUnidad', name: 'ClaveUnidad', orderable: false, searchable: false },
            { data: 'Status', name: 'Status', orderable: false, searchable: true }
        ],
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
    //modificacion al dar doble click
    $('#tbllistado tbody').on('dblclick', 'tr', function () {
      var data = tabla.row( this ).data();
      obtenerdatos(data.Codigo);
    }); 
}
function listarfamilias(){
    ocultarformulario();
    var tablafamilias = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Familias</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadofamilia" class="tbllistadofamilia table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Número</th>'+
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
    $("#contenidomodaltablas").html(tablafamilias);
    $('#tbllistadofamilia').DataTable({
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: servicios_obtener_familias,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadofamilia').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
}
//listar claves productos
function listarclavesproductos(){
    ocultarformulario();
    var tablaclavesproductos =  '<div class="modal-header '+background_forms_and_modals+'">'+
                                    '<h4 class="modal-title">Claves Productos</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row">'+
                                        '<div class="col-md-12">'+
                                            '<div class="table-responsive">'+
                                                '<table id="tbllistadoclaveproducto" class="tbllistadoclaveproducto table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                    '<thead class="'+background_tables+'">'+
                                                        '<tr>'+
                                                            '<th>Operaciones</th>'+
                                                            '<th>Clave</th>'+
                                                            '<th>Nombre</th>'+
                                                            '<th>Usual</th>'+
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
    $("#contenidomodaltablas").html(tablaclavesproductos);
    $('#tbllistadoclaveproducto').DataTable({
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: servicios_obtener_claves_productos,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Usual', name: 'Usual', orderable: false, searchable: false  }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoclaveproducto').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
}
//listar claves unidades
function listarclavesunidades(){
    ocultarformulario();
    var tablaclavesunidades =   '<div class="modal-header '+background_forms_and_modals+'">'+
                                    '<h4 class="modal-title">Claves Unidades</h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row">'+
                                        '<div class="col-md-12">'+
                                            '<div class="table-responsive">'+
                                                '<table id="tbllistadoclaveunidad" class="tbllistadoclaveunidad table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                    '<thead class="'+background_tables+'">'+
                                                        '<tr>'+
                                                            '<th>Operaciones</th>'+
                                                            '<th>Clave</th>'+
                                                            '<th>Nombre</th>'+
                                                            '<th>Descripción</th>'+
                                                            '<th>Usual</th>'+
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
    $("#contenidomodaltablas").html(tablaclavesunidades);
    $('#tbllistadoclaveunidad').DataTable({
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: servicios_obtener_claves_unidades,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Clave', name: 'Clave' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false  },
            { data: 'Usual', name: 'Usual', orderable: false, searchable: false  }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadoclaveunidad').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
}
function seleccionarfamilia(Numero, Nombre){
    $("#familia").val(Numero);
    $("#nombrefamilia").val(Nombre);
    $("#nombrefamilia").keyup();
    mostrarformulario();
}
function seleccionarclaveproducto(Clave, Nombre){
    $("#claveproducto").val(Clave);
    $("#nombreclaveproducto").val(Nombre);
    $("#nombreclaveproducto").keyup();
    mostrarformulario();
}
function seleccionarclaveunidad(Clave, Nombre){
    $("#claveunidad").val(Clave);
    $("#nombreclaveunidad").val(Nombre);
    $("#nombreclaveunidad").keyup();
    mostrarformulario();
}
//alta
function alta(){
    $("#titulomodal").html('Alta Servicio');
    mostrarmodalformulario('ALTA');
    mostrarformulario();
    //formulario alta
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#datosgenerales" data-toggle="tab">Datos Generales</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="datosgenerales">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<label>Familia<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarfamilia" class="btn bg-blue waves-effect" onclick="listarfamilias()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+ 
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control" name="nombrefamilia" id="nombrefamilia" value="0" required readonly onkeyup="tipoLetra(this);">'+
                                            '<input type="hidden" class="form-control" name="familia" id="familia" value="0" required readonly onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+    
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Clave Producto<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclavesproductos" class="btn bg-blue waves-effect" onclick="listarclavesproductos()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="claveproducto" id="claveproducto" required data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                                            '<input type="text" class="form-control" name="nombreclaveproducto" id="nombreclaveproducto" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+ 
                            '<div class="col-md-6">'+
                                '<label>Clave Unidad<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclavesunidad" class="btn bg-blue waves-effect" onclick="listarclavesunidades()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="claveunidad" id="claveunidad" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">'+
                                            '<input type="text" class="form-control" name="nombreclaveunidad" id="nombreclaveunidad" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Servicio<b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="servicio" id="servicio" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Unidad<b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="unidad" id="unidad" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+   
                            '<div class="col-md-4">'+
                                '<label>Cantidad</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="cantidad" id="cantidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Costo</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+  
                            '<div class="col-md-4">'+
                                '<label>Venta</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="venta" id="venta" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                        '</div>'+  
                    '</div>'+
                '</div>';
  $("#tabsform").html(tabs);
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:servicios_guardar,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                if(data == 1){
                    msj_errorcodigoexistente();
                }else{
                    msj_datosguardadoscorrectamente();
                    limpiar();
                    ocultarmodalformulario();
                    limpiarmodales();
                }
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
    }else{
        form.parsley().validate();
    }
});
//dar de baja o alta registro
function desactivar(codigoservicio){
  $("#codigoservicio").val(codigoservicio);
  $('#estatusregistro').modal('show');
}
$("#aceptar").on('click', function(e){
    e.preventDefault();
    var formData = new FormData($("#formdesactivar")[0]);
    var form = $("#formdesactivar");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:servicios_alta_o_baja,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                $('#estatusregistro').modal('hide');
                msj_statuscambiado();
                $('.page-loader-wrapper').css('display', 'none');
            },
            error:function(data){
                $('#estatusregistro').modal('hide');
                if(data.status == 403){
                    msj_errorenpermisos();
                }else{
                    msj_errorajax();
                }
                $('.page-loader-wrapper').css('display', 'none');
            }
        })
    }else{
        form.parsley().validate();
    }
});
function obtenerdatos(codigoservicio){
    $("#titulomodal").html('Modificación Servicio');
    $('.page-loader-wrapper').css('display', 'block');
    $.get(servicios_obtener_servicio,{codigoservicio:codigoservicio },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#datosgenerales" data-toggle="tab">Datos Generales</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="datosgenerales">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<label>Familia<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarfamilia" class="btn bg-blue waves-effect" onclick="listarfamilias()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+ 
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control" name="nombrefamilia" id="nombrefamilia" value="0" required readonly onkeyup="tipoLetra(this);">'+
                                            '<input type="hidden" class="form-control" name="familia" id="familia" value="0" required readonly onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+    
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Clave Producto<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclavesproductos" class="btn bg-blue waves-effect" onclick="listarclavesproductos()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="claveproducto" id="claveproducto" required data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                                            '<input type="text" class="form-control" name="nombreclaveproducto" id="nombreclaveproducto" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+ 
                            '<div class="col-md-6">'+
                                '<label>Clave Unidad<b style="color:#F44336 !important;">*</b></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclavesunidad" class="btn bg-blue waves-effect" onclick="listarclavesunidades()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="claveunidad" id="claveunidad" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">'+
                                            '<input type="text" class="form-control" name="nombreclaveunidad" id="nombreclaveunidad" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Servicio<b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="servicio" id="servicio" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Unidad<b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="text" class="form-control" name="unidad" id="unidad" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+   
                            '<div class="col-md-4">'+
                                '<label>Cantidad</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="cantidad" id="cantidad" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Costo</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+  
                            '<div class="col-md-4">'+
                                '<label>Venta</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="venta" id="venta" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                        '</div>'+  
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    console.log(data);
    //boton formulario 
    $("#codigo").val(codigoservicio);
    $("#servicio").val(data.servicio.Servicio);
    $("#unidad").val(data.servicio.Unidad);
    $("#cantidad").val(data.cantidad);
    $("#costo").val(data.costo);
    $("#venta").val(data.venta);
    if(data.familia != null){
        $("#familia").val(data.familia.Numero);
        $("#nombrefamilia").val(data.familia.Nombre);
        $("#nombrefamilia").keyup();
    }
    $("#claveproducto").val(data.claveproducto.Clave);
    $("#nombreclaveproducto").val(data.claveproducto.Nombre);
    $("#nombreclaveproducto").keyup();
    $("#claveunidad").val(data.claveunidad.Clave);
    $("#nombreclaveunidad").val(data.claveunidad.Nombre);
    $("#nombreclaveunidad").keyup();
    mostrarmodalformulario('MODIFICACION');
    $('.page-loader-wrapper').css('display', 'none');
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
//guardar el registro
$("#btnGuardarModificacion").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:servicios_guardar_modificacion,
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
    }else{
        form.parsley().validate();
    }
});
init();