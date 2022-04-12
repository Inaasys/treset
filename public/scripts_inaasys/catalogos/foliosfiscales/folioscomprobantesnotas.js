'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
    $.get(folios_comprobantes_notas_obtener_ultimo_numero, function(numero){
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
    //agregar inputs de busqueda por columna
    $('#tbllistado tfoot th').each( function () {
      var title = $(this).text();
      if(title != 'Operaciones'){
        $(this).html( '<input type="text" placeholder="Buscar en columna '+title+'" />' );
      }
    });
    tabla=$('#tbllistado').DataTable({
        keys: true,
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
        ajax: folios_comprobantes_notas_obtener,
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Numero', name: 'Numero', orderable: false, searchable: true },
            { data: 'Serie', name: 'Serie', orderable: false, searchable: true },
            { data: 'Esquema', name: 'Esquema', orderable: false, searchable: true },
            { data: 'FolioInicial', name: 'FolioInicial', orderable: false, searchable: true },
            { data: 'Titulo', name: 'Titulo', orderable: false, searchable: true },
            { data: 'ValidoDesde', name: 'ValidoDesde', orderable: false, searchable: true },
            { data: 'ValidoHasta', name: 'ValidoHasta', orderable: false, searchable: true },
            { data: 'Empresa', name: 'Empresa', orderable: false, searchable: true },
            { data: 'Predeterminar', name: 'Predeterminar', orderable: false, searchable: true },
            { data: 'Status', name: 'Status', orderable: false, searchable: true }
        ],
        initComplete: function () {
          // Aplicar busquedas por columna
          this.api().columns().every( function () {
            var that = this;
            $('input',this.footer()).on( 'change', function(){
              if(that.search() !== this.value){
                that.search(this.value).draw();
              }
            });
          });
          //Aplicar busqueda general
          var $buscar = $('div.dataTables_filter input');
          $buscar.unbind();
          $buscar.bind('keyup change', function(e) {
              if(e.keyCode == 13 || this.value == "") {
                $('#tbllistado').DataTable().search( this.value ).draw();
                $(".inputbusquedageneral").val(""); 
              }
          });
        }
    });
    //modificacion al dar doble click
    $('#tbllistado tbody').on('dblclick', 'tr', function () {
      var data = tabla.row( this ).data();
      obtenerdatos(data.Numero);
    }); 
}
//colocar pagare default 1
function pagaredefault1(){
    $("#pagare").val(pagaredefaultuno);
}
//tipo de esquema
function tipoesquema(){
    var tipoesquema = $("#esquema").val();
    if(tipoesquema != "CFDI"){
        $("#divcertificadosempresa").hide();
        $("#archivocertificado").removeAttr('required');
        $("#contrasenallaveprivada").removeAttr('required');
        $("#archivollaveprivada").removeAttr('required');
        $("#certificadovalidodesde").removeAttr('required');
        $("#certificadovalidohasta").removeAttr('required');
    }else{
        $("#divcertificadosempresa").show();
        $("#archivocertificado").attr('required', 'required');
        $("#contrasenallaveprivada").attr('required', 'required');
        $("#archivollaveprivada").attr('required', 'required');
        $("#certificadovalidodesde").attr('required', 'required');
        $("#certificadovalidohasta").attr('required', 'required');
    }
}
//alta
function alta(){
    $("#titulomodal").html('Alta Folio Fiscal Nota');
    mostrarmodalformulario('ALTA');
    mostrarformulario();
    //formulario alta
    var tabs =  '<div class="row">'+
                    '<div class="col-md-3">'+
                        '<label>Número</label>'+
                        '<input type="text" class="form-control inputnext" name="numero" id="numero" required readonly>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<label>Serie</label>'+
                        '<input type="text" class="form-control inputnext" name="serie" id="serie" value="N"  onkeyup="tipoLetra(this)" required data-parsley-length="[1, 10]">'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<label>Esquema</label>'+
                        '<select name="esquema" id="esquema" class="form-control select2" style="width:100%" onchange="tipoesquema();" required>'+
                            '<option selected disabled hidden>Selecciona...</option>'+
                            '<option value="CFDI" selected>CFDI</option>'+
                            '<option value="INTERNA">INTERNA</option>'+
                            '<option value="NOTA">NOTA</option>'+
                        '</select>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<label>Titulo</label>'+
                        '<input type="text" class="form-control inputnext" name="titulo" id="titulo" value="NOTA DE CREDITO" onkeyup="tipoLetra(this)" required data-parsley-length="[1, 20]">'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#foliostab" data-toggle="tab">Folios</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#domiciliotab" data-toggle="tab">Domicilio</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#pagaretab" data-toggle="tab">Leyenda</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="foliostab">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Folio Inicial (Las Notas empiezan con este folio)</label>'+
                                        '<input type="text" class="form-control inputnext" name="folioinicial" id="folioinicial" value="1" required >'+
                                    '</div>'+
                                '</div>'+  
                                '<div class="row" id="divcertificadosempresa">'+ 
                                    '<div class="col-md-6">'+
                                        '<label>Archivo de Certificado (*.cer)</label>'+
                                        '<input type="file" name="archivocertificado" id="archivocertificado"  class="dropify" required data-parsley-length="[1, 100]" data-allowed-file-extensions="cer" data-height="100" onchange="validararchivostimbrado();" />'+
                                    '</div>'+ 
                                    '<div class="col-md-6">'+
                                        '<label>Archivo Llave Privada (*.key)</label>'+
                                        '<input type="file" name="archivollaveprivada" id="archivollaveprivada"  class="dropify" required data-parsley-length="[1, 100]" data-allowed-file-extensions="key" data-height="100" onchange="validararchivostimbrado();" />'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Contraseña Llave Privada</label>'+
                                        '<input type="password" class="form-control inputnext" name="contrasenallaveprivada" id="contrasenallaveprivada" required data-parsley-length="[1, 100]" onchange="validararchivostimbrado();">'+
                                    '</div>'+ 
                                    '<div class="col-md-4">'+
                                        '<label>Certificado Válido Desde</label>'+
                                        '<input type="text" class="form-control inputnext" name="certificadovalidodesde" id="certificadovalidodesde" required readonly>'+
                                    '</div>'+ 
                                    '<div class="col-md-4">'+
                                        '<label>Certificado Válido Hasta</label>'+
                                        '<input type="text" class="form-control inputnext" name="certificadovalidohasta" id="certificadovalidohasta" required readonly>'+
                                    '</div>'+ 
                                '</div>'+ 
                            '</div>'+
                            '<div role="tabpanel" class="tab-pane fade" id="domiciliotab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<label>Empresa</label>'+
                                        '<input type="text" class="form-control inputnext" name="empresa" id="empresa" value="'+nombreempresa+'" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]">'+
                                    '</div>'+
                                    '<div class="col-md-12">'+
                                        '<label>Domicilio</label>'+
                                        '<textarea class="form-control inputnext" name="domicilio" id="domicilio" onkeyup="tipoLetra(this);" rows="10" required>'+textareadomicilio+'</textarea>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<label>Leyenda 1</label>'+
                                        '<input type="text" class="form-control inputnext" name="leyenda1" id="leyenda1" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Leyenda 2</label>'+
                                        '<input type="text" class="form-control inputnext" name="leyenda2" id="leyenda2" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Leyenda 3</label>'+
                                        '<input type="text" class="form-control inputnext" name="leyenda3" id="leyenda3" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]">'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div role="tabpanel" class="tab-pane fade" id="pagaretab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="btn bg-blue btn-xs waves-effect" onclick="pagaredefault1()">'+
                                            'Asignar Pagaré Default 1'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-12">'+
                                        '<label>Pagare</label>'+
                                        '<textarea class="form-control inputnext" name="pagare" id="pagare" onkeyup="tipoLetra(this);" rows="15"></textarea>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                        '<br>'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<b>Versión 3.3</b>'+
                                '<input type="hidden" class="form-control" name="versioncfdi" id="versioncfdi" value="3.3">'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    $('.dropify').dropify();
    obtenultimonumero();
    setTimeout(function(){$("#numero").focus();},500);
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnext");          
      switch(code){
        case 13:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnext").eq(index - 1).focus().select(); 
          break;
      }
    });
}
//validar archivos para timbrado electronico
function validararchivostimbrado(){
    var formData = new FormData($("#formparsley")[0]);
    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:folios_comprobantes_notas_enviar_archivos_timbrado,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){ 
            var results = JSON.parse(data);
            if(results.msj != "OK"){
                msj_faltanarchivosocontrasena(results.msj);
            }
            $("#certificadovalidodesde").val(results.updated_at).change();
            $("#certificadovalidohasta").val(results.expires_at);
        },
        error:function(data){
            if(data.status == 403){
                msj_errorenpermisos();
            }else{
                msj_errorajax();
            }
        }
    })
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
            url:folios_comprobantes_notas_guardar,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                if(data == 1){
                    msj_errorserieexistente();
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
            url:folios_comprobantes_notas_alta_o_baja,
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
//mostrar div certificados
function mostrardivcertificados(){
    if( $('#idactualizarcertificado').prop('checked') ) {
        $("#divcertificadosempresa").show();
        $("#archivocertificado").attr('required', 'required');
        $("#contrasenallaveprivada").attr('required', 'required');
        $("#archivollaveprivada").attr('required', 'required');
        $("#certificadovalidodesde").attr('required', 'required');
        $("#certificadovalidohasta").attr('required', 'required');
    }else{
        $("#divcertificadosempresa").hide();
        $("#archivocertificado").removeAttr('required');
        $("#contrasenallaveprivada").removeAttr('required');
        $("#archivollaveprivada").removeAttr('required');
        $("#certificadovalidodesde").removeAttr('required');
        $("#certificadovalidohasta").removeAttr('required');
    }
}
function obtenerdatos(numerofolio){
    $("#titulomodal").html('Modificación Folio Fiscal Nota');
    $('.page-loader-wrapper').css('display', 'block');
    $.get(folios_comprobantes_notas_obtener_folio,{numerofolio:numerofolio },function(data){
    //formulario modificacion
    var tabs =  '<div class="row">'+
                    '<div class="col-md-3">'+
                        '<label>Número</label>'+
                        '<input type="text" class="form-control inputnext" name="numero" id="numero" required readonly>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<label>Serie</label>'+
                        '<input type="text" class="form-control inputnext" name="serie" id="serie" value="N"  onkeyup="tipoLetra(this)" required readonly data-parsley-length="[1, 10]">'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<label>Esquema</label>'+
                        '<select name="esquema" id="esquema" class="form-control select2" style="width:100%"  required>'+
                            '<option value="'+data.FolioComprobanteNota.Esquema+'" selected>'+data.FolioComprobanteNota.Esquema+'</option>'+
                        '</select>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<label>Titulo</label>'+
                        '<input type="text" class="form-control inputnext" name="titulo" id="titulo" value="NOTA DE CREDITO" onkeyup="tipoLetra(this)" required data-parsley-length="[1, 20]">'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#foliostab" data-toggle="tab">Folios</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#domiciliotab" data-toggle="tab">Domicilio</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#pagaretab" data-toggle="tab">Leyenda</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="foliostab">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Folio Inicial (Las Notas empiezan con este folio)</label>'+
                                        '<input type="text" class="form-control inputnext" name="folioinicial" id="folioinicial" value="1" required >'+
                                    '</div>'+
                                '</div>'+   
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<input type="checkbox" name="actualizarcertificado" id="idactualizarcertificado" value="1" onchange="mostrardivcertificados();">'+
                                        '<label for="idactualizarcertificado">Actualizar Certificados</label>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row" id="divcertificadosempresa" hidden>'+ 
                                    '<div class="col-md-6">'+
                                        '<label>Archivo de Certificado (*.cer)</label>'+
                                        '<input type="file" name="archivocertificado" id="archivocertificado"  class="dropify"  data-parsley-length="[1, 100]" data-allowed-file-extensions="cer" data-height="100" onchange="validararchivostimbrado();" />'+
                                    '</div>'+ 
                                    '<div class="col-md-6">'+
                                        '<label>Archivo Llave Privada (*.key)</label>'+
                                        '<input type="file" name="archivollaveprivada" id="archivollaveprivada"  class="dropify"  data-parsley-length="[1, 100]" data-allowed-file-extensions="key" data-height="100" onchange="validararchivostimbrado();" />'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Contraseña Llave Privada</label>'+
                                        '<input type="password" class="form-control inputnext" name="contrasenallaveprivada" id="contrasenallaveprivada"  data-parsley-length="[1, 100]" onchange="validararchivostimbrado();">'+
                                    '</div>'+ 
                                    '<div class="col-md-4">'+
                                        '<label>Certificado Válido Desde</label>'+
                                        '<input type="text" class="form-control inputnext" name="certificadovalidodesde" id="certificadovalidodesde"  readonly>'+
                                    '</div>'+ 
                                    '<div class="col-md-4">'+
                                        '<label>Certificado Válido Hasta</label>'+
                                        '<input type="text" class="form-control inputnext" name="certificadovalidohasta" id="certificadovalidohasta"  readonly>'+
                                    '</div>'+ 
                                '</div>'+ 
                            '</div>'+
                            '<div role="tabpanel" class="tab-pane fade" id="domiciliotab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<label>Empresa</label>'+
                                        '<input type="text" class="form-control inputnext" name="empresa" id="empresa" value="'+nombreempresa+'" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]">'+
                                    '</div>'+
                                    '<div class="col-md-12">'+
                                        '<label>Domicilio</label>'+
                                        '<textarea class="form-control inputnext" name="domicilio" id="domicilio" onkeyup="tipoLetra(this);" rows="10" required>'+textareadomicilio+'</textarea>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<label>Leyenda 1</label>'+
                                        '<input type="text" class="form-control inputnext" name="leyenda1" id="leyenda1" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Leyenda 2</label>'+
                                        '<input type="text" class="form-control inputnext" name="leyenda2" id="leyenda2" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]">'+
                                    '</div>'+
                                    '<div class="col-md-4">'+
                                        '<label>Leyenda 3</label>'+
                                        '<input type="text" class="form-control inputnext" name="leyenda3" id="leyenda3" onkeyup="tipoLetra(this);" data-parsley-length="[1, 255]">'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div role="tabpanel" class="tab-pane fade" id="pagaretab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="btn bg-blue btn-xs waves-effect" onclick="pagaredefault1()">'+
                                            'Asignar Pagaré Default 1'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-12">'+
                                        '<label>Pagare</label>'+
                                        '<textarea class="form-control inputnext" name="pagare" id="pagare" onkeyup="tipoLetra(this);" rows="15"></textarea>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                        '<br>'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<b>Versión 3.3</b>'+
                                '<input type="hidden" class="form-control" name="versioncfdi" id="versioncfdi" value="3.3">'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //colocar autocomplette off  todo el formulario
    $(".form-control").attr('autocomplete','off');
    $("#numero").val(data.FolioComprobanteNota.Numero);
    $("#serie").val(data.FolioComprobanteNota.Serie);
    $("#titulo").val(data.FolioComprobanteNota.Titulo);
    $("#folioinicial").val(data.FolioComprobanteNota.FolioInicial);
    if(data.FolioComprobanteNota.Esquema != 'CFDI'){
        $("#idactualizarcertificado").attr('onclick','javascript: return false');
    }
    $("#empresa").val(data.FolioComprobanteNota.Empresa);
    $("#domicilio").val(data.FolioComprobanteNota.Domicilio);
    $("#leyenda1").val(data.FolioComprobanteNota.Leyenda1);
    $("#leyenda2").val(data.FolioComprobanteNota.Leyenda2);
    $("#leyenda3").val(data.FolioComprobanteNota.Leyenda3);
    $("#pagare").val(data.FolioComprobanteNota.Leyenda);
    $('.dropify').dropify();
    setTimeout(function(){$("#numero").focus();},500);
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keyup(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      var index = $(this).index(".inputnext");          
      switch(code){
        case 13:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 39:
          $(".inputnext").eq(index + 1).focus().select(); 
          break;
        case 37:
          $(".inputnext").eq(index - 1).focus().select(); 
          break;
      }
    });
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
            url:folios_comprobantes_notas_guardar_modificacion,
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
    $("#numerofoliopred").val(numerofolio);
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
            url:folios_comprobantes_notas_predeterminar,
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