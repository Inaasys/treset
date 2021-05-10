'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
    $.get(folios_comprobantes_facturas_obtener_ultimo_numero, function(numero){
      $("#numero").val(numero);
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
        ajax: folios_comprobantes_facturas_obtener,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero', orderable: false, searchable: false },
            { data: 'Serie', name: 'Serie' },
            { data: 'Esquema', name: 'Esquema' },
            { data: 'FolioInicial', name: 'FolioInicial', orderable: false, searchable: false },
            { data: 'Titulo', name: 'Titulo', orderable: false, searchable: false },
            { data: 'Depto', name: 'Depto', orderable: false, searchable: false },
            { data: 'ValidoDesde', name: 'ValidoDesde', orderable: false, searchable: false },
            { data: 'ValidoHasta', name: 'ValidoHasta', orderable: false, searchable: false },
            { data: 'Empresa', name: 'Empresa', orderable: false, searchable: false },
            { data: 'Predeterminar', name: 'Predeterminar', orderable: false, searchable: false },
            { data: 'Status', name: 'Status', orderable: false, searchable: false }
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
}
//colocar pagare default 1
function pagaredefault1(){
    $("#pagare").val(pagaredefaultuno);
}
//colocar pagare default 2
function pagaredefault2(){
    $("#pagare").val(pagaredefaultdos);
}
//alta
function alta(){
    $("#titulomodal").html('Alta Folio Fiscal Factura');
    mostrarmodalformulario('ALTA');
    mostrarformulario();
    //formulario alta
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#foliostab" data-toggle="tab">Folios</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#domiciliotab" data-toggle="tab">Domicilio</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#pagaretab" data-toggle="tab">Pagaré</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#alineartab" data-toggle="tab">Alinear</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="foliostab">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<label>Departamento</label>'+
                                '<select name="departamento" id="departamento" class="form-control select2" style="width:100%" required>'+
                                    '<option selected disabled hidden>Selecciona...</option>'+
                                    '<option value="PRODUCTOS" selected>PRODUCTOS</option>'+
                                    '<option value="LIBRE">LIBRE</option>'+
                                    '<option value="SERVICIO">SERVICIO</option>'+
                                '</select>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Folio Inicial (Las Facturas empiezan con este folio)</label>'+
                                '<input type="text" class="form-control" name="folioinicial" id="folioinicial" value="1" required >'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+ 
                            '<div class="col-md-4">'+
                                '<label>Archivo de Certificado (*.cer)</label>'+
                                '<input type="file" class="form-control" name="archivocertificado" id="archivocertificado" required data-parsley-length="[1, 100]" ></input>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Contraseña Llave Privada</label>'+
                                '<input type="text" class="form-control" name="contrasenallaveprivada" id="contrasenallaveprivada" required data-parsley-length="[1, 100]" >'+
                            '</div>'+  
                            '<div class="col-md-4">'+
                                '<label>Archivo Llave Privada (*.key)</label>'+
                                '<input type="file" class="form-control" name="archivollaveprivada" id="archivollaveprivada" required data-parsley-length="[1, 100]" ></input>'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+   
                            '<div class="col-md-4">'+
                                '<label>Número de Certificado</label>'+
                                '<input type="text" class="form-control" name="numerocertificado" id="numerocertificado" onkeyup="tipoLetra(this);" required readonly data-parsley-length="[1, 50]" >'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Certificado Válido Desde</label>'+
                                '<input type="text" class="form-control" name="certificadovalidodesde" id="certificadovalidodesde" onkeyup="tipoLetra(this);" required readonly data-parsley-length="[1, 20]" >'+
                            '</div>'+  
                            '<div class="col-md-4">'+
                                '<label>Certificado Válido Hasta</label>'+
                                '<input type="text" class="form-control" name="certificadovalidohasta" id="certificadovalidohasta" onkeyup="tipoLetra(this);" required readonly data-parsley-length="[1, 20]" >'+
                            '</div>'+
                        '</div>'+  
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="domiciliotab">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<label>Empresa</label>'+
                                '<input type="text" class="form-control" name="empresa" id="empresa" value="'+nombreempresa+'" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" >'+
                            '</div>'+
                            '<div class="col-md-12">'+
                                '<label>Domicilio</label>'+
                                '<textarea class="form-control" name="domicilio" id="domicilio" onkeyup="tipoLetra(this);" rows="10" required>'+textareadomicilio+'</textarea>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Leyenda 1</label>'+
                                '<input type="text" class="form-control" name="leyenda1" id="leyenda1" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" >'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Leyenda 2</label>'+
                                '<input type="text" class="form-control" name="leyenda2" id="leyenda2" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" >'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Leyenda 3</label>'+
                                '<input type="text" class="form-control" name="leyenda3" id="leyenda3" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" >'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="pagaretab">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="btn bg-blue btn-xs waves-effect" onclick="pagaredefault1()">'+
                                    'Asignar Pagaré Default 1'+
                                '</div>'+
                                '&nbsp;&nbsp;'+
                                '<div class="btn bg-blue btn-xs waves-effect" onclick="pagaredefault2()" style="display:none;">'+
                                    'Asignar Pagaré Default 2'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-12">'+
                                '<label>Pagare</label>'+
                                '<textarea class="form-control" name="pagare" id="pagare" onkeyup="tipoLetra(this);" rows="15" required></textarea>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="alineartab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Ubicar Logotipo</label>'+
                                '<select name="ubicarlogotipo" id="ubicarlogotipo" class="form-control select2" style="width:100%" required>'+
                                    '<option selected disabled hidden>Selecciona...</option>'+
                                    '<option value="0" selected>Izquierda</option>'+
                                    '<option value="1">Derecha</option>'+
                                '</select>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Alinear Logotipo</label>'+
                                '<select name="alinearlogotipo" id="alinearlogotipo" class="form-control select2" style="width:100%" required>'+
                                    '<option selected disabled hidden>Selecciona...</option>'+
                                    '<option value="0">Arriba Izquierda</option>'+
                                    '<option value="1">Arriba Centrado</option>'+
                                    '<option value="2">Arriba Derecha</option>'+
                                    '<option value="3">Abajo Izquierda</option>'+
                                    '<option value="4">Abajo Centrado</option>'+
                                    '<option value="5">Abajo Derecha</option>'+
                                    '<option value="6">Medio Izquierda</option>'+
                                    '<option value="7">Medio Centrado</option>'+
                                    '<option value="8">Medio Derecha</option>'+
                                    '<option value="9">Recortar</option>'+
                                    '<option value="10" selected>Ajustar Zoom</option>'+
                                    '<option value="11">Estrechar</option>'+
                                    '<option value="12">Imagen de Fondo</option>'+
                                '</select>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Alinear Empresa</label>'+
                                '<select name="alinearempresa" id="alinearempresa" class="form-control select2" style="width:100%" required>'+
                                    '<option selected disabled hidden>Selecciona...</option>'+
                                    '<option value="0">Arriba Izquierda</option>'+
                                    '<option value="1">Arriba Centrado</option>'+
                                    '<option value="2">Arriba Derecha</option>'+
                                    '<option value="3">Abajo Izquierda</option>'+
                                    '<option value="4">Abajo Centrado</option>'+
                                    '<option value="5">Abajo Derecha</option>'+
                                    '<option value="6">Medio Izquierda</option>'+
                                    '<option value="7" selected>Medio Centrado</option>'+
                                    '<option value="8">Medio Derecha</option>'+
                                    '<option value="9">Arriba</option>'+
                                    '<option value="10">Abajo</option>'+
                                    '<option value="11">Medio</option>'+
                                '</select>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-md-6 form-check">'+
                        '<input type="checkbox" name="imprimirlogotipo" id="idimprimirlogotipo" class="filled-in datotabla" value="S" checked readonly />'+
                        '<label for="idimprimirlogotipo">imprimirlogotipo</label>'+
                    '</div>'+
                    '<div class="col-md-6">'+
                        '<b>Versión 3.3</b>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    obtenultimonumero();
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
            url:folios_comprobantes_facturas_guardar,
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
function desactivar(numerofolio){
  $("#numerofolio").val(numerofolio);
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
            url:folios_comprobantes_facturas_alta_o_baja,
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
function obtenerdatos(numerofolio){
    $("#titulomodal").html('Modificación Folio Fiscal Factura');
    $('.page-loader-wrapper').css('display', 'block');
    $.get(folios_comprobantes_facturas_obtener_folio,{numerofolio:numerofolio },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#foliostab" data-toggle="tab">Folios</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#domiciliotab" data-toggle="tab">Domicilio</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#pagaretab" data-toggle="tab">Pagaré</a>'+
                    '</li>'+
                    '<li role="presentation">'+
                        '<a href="#alineartab" data-toggle="tab">Alinear</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="foliostab">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<label>Departamento</label>'+
                                '<select name="departamento" id="departamento" class="form-control select2" style="width:100%" required>'+
                                    '<option selected disabled hidden>Selecciona...</option>'+
                                    '<option value="PRODUCTOS" selected>PRODUCTOS</option>'+
                                    '<option value="LIBRE">LIBRE</option>'+
                                    '<option value="SERVICIO">SERVICIO</option>'+
                                '</select>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<label>Folio Inicial (Las Facturas empiezan con este folio)</label>'+
                                '<input type="text" class="form-control" name="folioinicial" id="folioinicial" value="1" required >'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+ 
                            '<div class="col-md-4">'+
                                '<label>Archivo de Certificado (*.cer)</label>'+
                                '<input type="file" class="form-control" name="archivocertificado" id="archivocertificado" required data-parsley-length="[1, 100]" ></input>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Contraseña Llave Privada</label>'+
                                '<input type="text" class="form-control" name="contrasenallaveprivada" id="contrasenallaveprivada" required data-parsley-length="[1, 100]" >'+
                            '</div>'+  
                            '<div class="col-md-4">'+
                                '<label>Archivo Llave Privada (*.key)</label>'+
                                '<input type="file" class="form-control" name="archivollaveprivada" id="archivollaveprivada" required data-parsley-length="[1, 100]" ></input>'+
                            '</div>'+
                        '</div>'+  
                        '<div class="row">'+   
                            '<div class="col-md-4">'+
                                '<label>Número de Certificado</label>'+
                                '<input type="text" class="form-control" name="numerocertificado" id="numerocertificado" onkeyup="tipoLetra(this);" required readonly data-parsley-length="[1, 50]" >'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Certificado Válido Desde</label>'+
                                '<input type="text" class="form-control" name="certificadovalidodesde" id="certificadovalidodesde" onkeyup="tipoLetra(this);" required readonly data-parsley-length="[1, 20]" >'+
                            '</div>'+  
                            '<div class="col-md-4">'+
                                '<label>Certificado Válido Hasta</label>'+
                                '<input type="text" class="form-control" name="certificadovalidohasta" id="certificadovalidohasta" onkeyup="tipoLetra(this);" required readonly data-parsley-length="[1, 20]" >'+
                            '</div>'+
                        '</div>'+  
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="domiciliotab">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<label>Empresa</label>'+
                                '<input type="text" class="form-control" name="empresa" id="empresa" value="'+nombreempresa+'" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" >'+
                            '</div>'+
                            '<div class="col-md-12">'+
                                '<label>Domicilio</label>'+
                                '<textarea class="form-control" name="domicilio" id="domicilio" onkeyup="tipoLetra(this);" rows="10" required>'+textareadomicilio+'</textarea>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Leyenda 1</label>'+
                                '<input type="text" class="form-control" name="leyenda1" id="leyenda1" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" >'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Leyenda 2</label>'+
                                '<input type="text" class="form-control" name="leyenda2" id="leyenda2" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" >'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Leyenda 3</label>'+
                                '<input type="text" class="form-control" name="leyenda3" id="leyenda3" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" >'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="pagaretab">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="btn bg-blue btn-xs waves-effect" onclick="pagaredefault1()">'+
                                    'Asignar Pagaré Default 1'+
                                '</div>'+
                                '&nbsp;&nbsp;'+
                                '<div class="btn bg-blue btn-xs waves-effect" onclick="pagaredefault2()" style="display:none;">'+
                                    'Asignar Pagaré Default 2'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-12">'+
                                '<label>Pagare</label>'+
                                '<textarea class="form-control" name="pagare" id="pagare" onkeyup="tipoLetra(this);" rows="15" required></textarea>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="alineartab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Ubicar Logotipo</label>'+
                                '<select name="ubicarlogotipo" id="ubicarlogotipo" class="form-control select2" style="width:100%" required>'+
                                    '<option selected disabled hidden>Selecciona...</option>'+
                                    '<option value="0" selected>Izquierda</option>'+
                                    '<option value="1">Derecha</option>'+
                                '</select>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Alinear Logotipo</label>'+
                                '<select name="alinearlogotipo" id="alinearlogotipo" class="form-control select2" style="width:100%" required>'+
                                    '<option selected disabled hidden>Selecciona...</option>'+
                                    '<option value="0">Arriba Izquierda</option>'+
                                    '<option value="1">Arriba Centrado</option>'+
                                    '<option value="2">Arriba Derecha</option>'+
                                    '<option value="3">Abajo Izquierda</option>'+
                                    '<option value="4">Abajo Centrado</option>'+
                                    '<option value="5">Abajo Derecha</option>'+
                                    '<option value="6">Medio Izquierda</option>'+
                                    '<option value="7">Medio Centrado</option>'+
                                    '<option value="8">Medio Derecha</option>'+
                                    '<option value="9">Recortar</option>'+
                                    '<option value="10" selected>Ajustar Zoom</option>'+
                                    '<option value="11">Estrechar</option>'+
                                    '<option value="12">Imagen de Fondo</option>'+
                                '</select>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Alinear Empresa</label>'+
                                '<select name="alinearempresa" id="alinearempresa" class="form-control select2" style="width:100%" required>'+
                                    '<option selected disabled hidden>Selecciona...</option>'+
                                    '<option value="0">Arriba Izquierda</option>'+
                                    '<option value="1">Arriba Centrado</option>'+
                                    '<option value="2">Arriba Derecha</option>'+
                                    '<option value="3">Abajo Izquierda</option>'+
                                    '<option value="4">Abajo Centrado</option>'+
                                    '<option value="5">Abajo Derecha</option>'+
                                    '<option value="6">Medio Izquierda</option>'+
                                    '<option value="7" selected>Medio Centrado</option>'+
                                    '<option value="8">Medio Derecha</option>'+
                                    '<option value="9">Arriba</option>'+
                                    '<option value="10">Abajo</option>'+
                                    '<option value="11">Medio</option>'+
                                '</select>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-md-6 form-check">'+
                        '<input type="checkbox" name="imprimirlogotipo" id="idimprimirlogotipo" class="filled-in datotabla" value="S" checked readonly />'+
                        '<label for="idimprimirlogotipo">imprimirlogotipo</label>'+
                    '</div>'+
                    '<div class="col-md-6">'+
                        '<b>Versión 3.3</b>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    $("#numero").val(data.FolioComprobanteFactura.Numero);
    $("#serie").val(data.FolioComprobanteFactura.Serie);
    $("#esquema").val(data.FolioComprobanteFactura.Esquema).change();
    $("#titulo").val(data.FolioComprobanteFactura.Titulo);
    $("#departamento").val(data.FolioComprobanteFactura.Depto).change();
    $("#contrasenallaveprivada").val(data.FolioComprobanteFactura.Contraseña);
    $("#numerocertificado").val(data.FolioComprobanteFactura.NoCertificado);
    $("#certificadovalidodesde").val(data.FolioComprobanteFactura.ValidoDesde);
    $("#certificadovalidohasta").val(data.FolioComprobanteFactura.ValidoHasta);
    $("#empresa").val(data.FolioComprobanteFactura.Empresa);
    $("#domicilio").val(data.FolioComprobanteFactura.Domicilio);
    $("#leyenda1").val(data.FolioComprobanteFactura.Leyenda1);
    $("#leyenda2").val(data.FolioComprobanteFactura.Leyenda2);
    $("#leyenda3").val(data.FolioComprobanteFactura.Leyenda3);
    $("#pagare").val(data.FolioComprobanteFactura.Pagare);
    $("#ubicarlogotipo").val(data.FolioComprobanteFactura.UbicarLogotipo).change();
    $("#alinearlogotipo").val(data.FolioComprobanteFactura.AlinearLogotipo).change();
    $("#alinearempresa").val(data.FolioComprobanteFactura.AlinearEmpresa).change();
    if(data.FolioComprobanteFactura.ImprimirLogotipo == 'S'){
        $("#imprimirlogotipo").attr('checked', 'checked');
    }else{
        $("#imprimirlogotipo").removeAttr('checked');
    }
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
            url:folios_comprobantes_facturas_guardar_modificacion,
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
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function predeterminarfolio(numerofolio){
    $("#numerofolio").val(numerofolio);
    $("#btnpredeterminar").show();
    $('#modalpredeterminarfolio').modal('show');
}
$("#btnpredeterminar").on('click', function(e){
    e.preventDefault();
    var formData = new FormData($("#formpredeterminar")[0]);
    var form = $("#formpredeterminar");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:folios_comprobantes_facturas_predeterminar,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                $('#modalpredeterminarfolio').modal('hide');
                msj_datosguardadoscorrectamente();
                $('.page-loader-wrapper').css('display', 'none');
            },
            error:function(data){
                if(data.status == 403){
                    msj_errorenpermisos();
                }else{
                    msj_errorajax();
                }
                $('#modalpredeterminarfolio').modal('hide');
                $('.page-loader-wrapper').css('display', 'none');
            }
        })
    }else{
        form.parsley().validate();
    }
});
init();