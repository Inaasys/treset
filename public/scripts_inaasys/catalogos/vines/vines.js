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
        ajax: vines_obtener,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Cliente', name: 'Cliente', orderable: false, searchable: true },
            { data: 'Economico', name: 'Economico', orderable: false, searchable: true },
            { data: 'Vin', name: 'Vin', orderable: false, searchable: true },
            { data: 'Placas', name: 'Placas', orderable: false, searchable: false },
            { data: 'Motor', name: 'Motor', orderable: false, searchable: false },
            { data: 'Marca', name: 'Marca', orderable: false, searchable: false },
            { data: 'Modelo', name: 'Modelo', orderable: false, searchable: false },
            { data: 'Año', name: 'Año', orderable: false, searchable: false },
            { data: 'Color', name: 'Color', orderable: false, searchable: false },
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
      obtenerdatos(data.Vin);
    }); 
}
function listarclientes(){
    ocultarformulario();
    var tablaclientes = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Clientes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadocliente" class="tbllistadocliente table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Número</th>'+
                                                    '<th>Nombre</th>'+
                                                    '<th>R.F.C.</th>'+
                                                    '<th>Municipio</th>'+
                                                    '<th>Saldo $</th>'+
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
    $("#contenidomodaltablas").html(tablaclientes);
    $('#tbllistadocliente').DataTable({
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,  
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: vines_obtener_clientes,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero' },
            { data: 'Nombre', name: 'Nombre' },
            { data: 'Rfc', name: 'Rfc', orderable: false, searchable: false },
            { data: 'Municipio', name: 'Municipio', orderable: false, searchable: false },
            { data: 'Saldo', name: 'Saldo', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadocliente').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });
}
function seleccionarcliente(Numero, Nombre){
    $("#cliente").val(Numero);
    $("#nombrecliente").val(Nombre);
    $("#nombrecliente").keyup();
    mostrarformulario();
}
//alta
function alta(){
    $("#titulomodal").html('Alta Vin');
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
                                '<label>Cliente</label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclientes" class="btn bg-blue waves-effect" onclick="listarclientes()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="cliente" id="cliente" required onkeyup="tipoLetra(this);">'+
                                            '<input type="text" class="form-control" name="nombrecliente" id="nombrecliente" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Económico</label>'+
                                '<input type="text" class="form-control" name="economico" id="economico" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Vin</label>'+
                                '<input type="text" class="form-control" name="vin" id="vin" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Placas</label>'+
                                '<input type="text" class="form-control" name="placas" id="placas" required data-parsley-length="[1, 10]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Motor</label>'+
                                '<input type="text" class="form-control" name="motor" id="motor" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Marca</label>'+
                                '<input type="text" class="form-control" name="marca" id="marca" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Modelo</label>'+
                                '<input type="text" class="form-control" name="modelo" id="modelo" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Año</label>'+
                                '<input type="text" class="form-control" name="ano" id="ano" data-parsley-type="integer" required data-parsley-length="[4, 4]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Color</label>'+
                                '<input type="text" class="form-control" name="color" id="color" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>' 
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
            url:vines_guardar,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                if(data == 1){
                    msj_errorvinexistente();
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
function desactivar(numerovin){
  $("#numerovin").val(numerovin);
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
            url:vines_alta_o_baja,
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
function obtenerdatos(numerovin){
    $("#titulomodal").html('Modificación Vin');
    $('.page-loader-wrapper').css('display', 'block');
    $.get(vines_obtener_vine,{numerovin:numerovin },function(data){
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
                                '<label>Cliente</label>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarclientes" class="btn bg-blue waves-effect" onclick="listarclientes()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-9">'+  
                                        '<div class="form-line">'+
                                            '<input type="hidden" class="form-control" name="cliente" id="cliente" required onkeyup="tipoLetra(this);">'+
                                            '<input type="text" class="form-control" name="nombrecliente" id="nombrecliente" required onkeyup="tipoLetra(this);">'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Económico</label>'+
                                '<input type="text" class="form-control" name="economico" id="economico" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);" readonly>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Vin</label>'+
                                '<input type="text" class="form-control" name="vin" id="vin" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);" readonly>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Placas</label>'+
                                '<input type="text" class="form-control" name="placas" id="placas" required data-parsley-length="[1, 10]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Motor</label>'+
                                '<input type="text" class="form-control" name="motor" id="motor" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Marca</label>'+
                                '<input type="text" class="form-control" name="marca" id="marca" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Modelo</label>'+
                                '<input type="text" class="form-control" name="modelo" id="modelo" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Año</label>'+
                                '<input type="text" class="form-control" name="ano" id="ano" data-parsley-type="integer"  data-parsley-length="[4, 4]" required onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Color</label>'+
                                '<input type="text" class="form-control" name="color" id="color" required data-parsley-length="[1, 30]"  onkeyup="tipoLetra(this);">'+
                            '</div>'+
                        '</div>' 
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //boton formulario 
    $("#cliente").val(data.cliente.Numero);
    $("#nombrecliente").val(data.cliente.Nombre);
    $("#nombrecliente").keyup();
    $("#economico").val(data.vin.Economico);
    $("#vin").val(data.vin.Vin);
    $("#placas").val(data.vin.Placas);
    $("#motor").val(data.vin.Motor);
    $("#marca").val(data.vin.Marca);
    $("#modelo").val(data.vin.Modelo);
    $("#ano").val(data.vin.Año);
    $("#color").val(data.vin.Color);
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
            url:vines_guardar_modificacion,
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