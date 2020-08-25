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
    var fechahoy = new Date();
    var dia = ("0" + fechahoy.getDate()).slice(-2);
    var mes = ("0" + (fechahoy.getMonth() + 1)).slice(-2);
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia) ;
    $('#fecha').val(hoy).css('font-size', '12px');
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  $.get(cuentas_por_pagar_obtener_ultimo_folio, function(folio){
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
function mostrarmodalformulario(tipo){
    $("#ModalFormulario").modal('show');
    if(tipo == 'ALTA'){
        $("#btnGuardar").show();
        $("#btnGuardarModificacion").hide();
    }else if(tipo == 'MODIFICACION'){
          $("#btnGuardar").hide();
          $("#btnGuardarModificacion").hide();
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
function relistar(){
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//listar todos los registros de la tabla
function listar(){
    tabla=$('#tbllistado').DataTable({
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
            url: cuentas_por_pagar_obtener,
            data: function (d) {
                d.periodo = $("#periodo").val();
            },
        },
        "createdRow": function( row, data, dataIndex){
            if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
            else{ $(row).addClass(''); }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Pago', name: 'Pago' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Proveedor', name: 'Proveedor', orderable: false, searchable: false },
            { data: 'Nombre', name: 'Nombre', orderable: false, searchable: false },
            { data: 'Banco', name: 'Banco' },
            { data: 'Transferencia', name: 'Transferencia', orderable: false, searchable: false  },
            { data: 'Abono', name: 'Abono', orderable: false, searchable: false  },
            { data: 'Status', name: 'Status', orderable: false, searchable: false  },
            { data: 'MotivoBaja', name: 'MotivoBaja', orderable: false, searchable: false  },
            { data: 'Periodo', name: 'Periodo', orderable: false, searchable: false  }
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
//obtener registros de proveedores
function obtenerproveedores(){
  ocultarformulario();
  var tablaproveedores = '<div class="modal-header bg-red">'+
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
        "sScrollX": "110%",
        "sScrollY": "300px",
        "bScrollCollapse": true,
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: cuentas_por_pagar_obtener_proveedores,
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
        "iDisplayLength": 8,
    }); 
} 
//obtener registros de almacenes
function obtenerbancos(){
    ocultarformulario();
    var tablabancos = '<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Bancos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadobanco" class="tbllistadobanco table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Banco</th>'+
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
      $("#contenidomodaltablas").html(tablabancos);
      $('#tbllistadobanco').DataTable({
            "sScrollX": "110%",
            "sScrollY": "300px",
            "bScrollCollapse": true,
          processing: true,
          'language': {
              'loadingRecords': '&nbsp;',
              'processing': '<div class="spinner"></div>'
          },
          serverSide: true,
          ajax: {
              url: cuentas_por_pagar_obtener_bancos,
          },
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
                  $('#tbllistadoalmacen').DataTable().search( this.value ).draw();
                  }
              });
          },
          "iDisplayLength": 8,
      }); 
} 
function seleccionarproveedor(Numero, Nombre){
    $("#numeroproveedor").val(Numero);
    $("#proveedor").val(Nombre);
    $("#beneficiario").val(Nombre);
    $.get(cuentas_por_pagar_obtener_compras_proveedor, {Numero:Numero}, function(data){
        $("#tabladetallecuentasporpagar").html(data.filascompras);
    });
    mostrarformulario();
}
function seleccionarbanco(Numero, Nombre){
    $("#numerobanco").val(Numero);
    $("#banco").val(Nombre);
    mostrarformulario();
}
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Cuentas por Pagar');
  mostrarmodalformulario('ALTA');
  mostrarformulario();
  //formulario alta
  var tabs =    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#productostab" data-toggle="tab">Productos</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                        '<div class="row">'+
                            '<div class="col-md-12 table-responsive">'+
                                '<table class="table table-bordered">'+
                                    '<thead class="customercolor">'+
                                        '<tr>'+
                                          '<th>#</th>'+
                                          '<th>Compra</th>'+
                                          '<th>Factura</th>'+
                                          '<th>Fecha</th>'+
                                          '<th>Plazo</th>'+
                                          '<th>Vence</th>'+
                                          '<th>Total $</th>'+
                                          '<th>Abonos $</th>'+
                                          '<th>Notas Crédito $</th>'+
                                          '<th class="customercolortheadth">Abono</th>'+
                                          '<th>Saldo $</th>'+
                                          '<th>Contrarecibo</th>'+
                                        '</tr>'+
                                    '</thead>'+
                                    '<tbody id="tabladetallecuentasporpagar">'+           
                                    '</tbody>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+ 
                        '<div class="row">'+
                          '<div class="col-md-6">'+   
                            '<label>Contrarecibo</label>'+
                            '<input type="text" class="form-control" name="contrarecibo" id="contrarecibo" onkeyup="tipoLetra(this);" >'+
                          '</div>'+ 
                          '<div class="col-md-3 col-md-offset-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td class="tdmod">Total</td>'+
                                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+   
                    '</div>'+ 
                '</div>';
  $("#tabsform").html(tabs);
  $("#btnobtenerproveedores").show();
  obtenultimonumero();
  asignarfechaactual();
}
function calcularnuevosaldo(fila){
    var cuentaFilas = 0;
    $("tr.filascompras").each(function () {
        if(fila === cuentaFilas){   
            //descuento en pesos de la partida
            var saldocomprainicial = $('.saldocomprainicial', this).val();
            var abonocompra = $('.abonocompra', this).val();
            if(parseFloat(abonocompra) > parseFloat(saldocomprainicial)){
                $('.abonocompra', this).val(number_format(round(0, numerodecimales), numerodecimales, '.', ''))
                calcularnuevosaldo(fila);
            }else{
                var nuevosaldo  =  new Decimal(saldocomprainicial).minus(abonocompra);
                $('.saldocompra', this).val(number_format(round(nuevosaldo, numerodecimales), numerodecimales, '.', ''));
                calculartotalcuentaporpagar();
            }
        }  
        cuentaFilas++;
    });
}
function calculartotalcuentaporpagar(){
    var total = 0;
    $("tr.filascompras").each(function () {
        total = new Decimal(total).plus($('.abonocompra', this).val());
    }); 
    $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
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
            url:cuentas_por_pagar_guardar,
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
function desactivar(cxpdesactivar){
      $("#cxpdesactivar").val(cxpdesactivar);
      $("#divmotivobaja").show();
      $("#textomodaldesactivar").html('Estas seguro de dar de baja el registro?');
      $('#estatusregistro').modal('show');
}
$("#btnbaja").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formdesactivar")[0]);
  var form = $("#formdesactivar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:cuentas_por_pagar_baja,
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
function obtenerdatos(cxpmodificar){
  $("#titulomodal").html('Modificación Cuenta por Pagar');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(cuentas_por_pagar_obtener_cuenta_por_pagar,{cxpmodificar:cxpmodificar },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#productostab" data-toggle="tab">Productos</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                        '<div class="row">'+
                            '<div class="col-md-12 table-responsive">'+
                                '<table class="table table-bordered">'+
                                    '<thead class="customercolor">'+
                                        '<tr>'+
                                        '<th>#</th>'+
                                        '<th>Compra</th>'+
                                        '<th>Factura</th>'+
                                        '<th>Fecha</th>'+
                                        '<th>Plazo</th>'+
                                        '<th>Vence</th>'+
                                        '<th>Total $</th>'+
                                        '<th>Abonos $</th>'+
                                        '<th>Notas Crédito $</th>'+
                                        '<th class="customercolortheadth">Abono</th>'+
                                        '<th>Saldo $</th>'+
                                        '<th>Contrarecibo</th>'+
                                        '</tr>'+
                                    '</thead>'+
                                    '<tbody id="tabladetallecuentasporpagar">'+           
                                    '</tbody>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+ 
                        '<div class="row">'+
                        '<div class="col-md-6">'+   
                            '<label>Contrarecibo</label>'+
                            '<input type="text" class="form-control" name="contrarecibo" id="contrarecibo" onkeyup="tipoLetra(this);" >'+
                        '</div>'+ 
                        '<div class="col-md-3 col-md-offset-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td class="tdmod">Total</td>'+
                                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+   
                    '</div>'+ 
                '</div>';
                $("#tabsform").html(tabs);        
    $("#folio").val(data.CuentaXPagar.Folio);
    $("#numeroproveedor").val(data.proveedor.Numero);
    $("#proveedor").val(data.proveedor.Nombre);
    $("#btnobtenerproveedores").hide();
    $("#numerobanco").val(data.banco.Numero);
    $("#banco").val(data.banco.Nombre);
    $("#fecha").val(data.fecha);
    $("#transferencia").val(data.CuentaXPagar.Transferencia)
    $("#cheque").val(data.CuentaXPagar.Cheque);
    $("#beneficiario").val(data.CuentaXPagar.Beneficiario);
    $("#cuentadeposito").val(data.CuentaXPagar.CuentaDeposito);
    $("#anotacion").val(data.CuentaXPagar.Anotacion);
    $("#total").val(data.abonototal);
    //tabs precios productos
    $("#tabladetallecuentasporpagar").html(data.filasdetallecuentasporpagar);
    mostrarmodalformulario('MODIFICACION');
    $('.page-loader-wrapper').css('display', 'none');
  })
}

//guardar modificación
$("#btnGuardarModificacion").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:ordenes_compra_guardar_modificacion,
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

//hacer busqueda de folio para exportacion en pdf
function buscarstringlike(string){
    destruirtablafoliosexportacion();
    tabla=$('#tablafoliosencontrados').DataTable({
        "paging":   false,
        "ordering": false,
        "info":     false,
        "searching": false,
        processing: true,
        serverSide: true,
        ajax: {
            url: cuentas_por_pagar_buscar_folio_string_like,
            data: function (d) {
                d.string = string;
            },
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Pago', name: 'Pago' },
            { data: 'Proveedor', name: 'Proveedor', orderable: false, searchable: false },
            { data: 'Abono', name: 'Abono', orderable: false, searchable: false  },
            { data: 'Status', name: 'Status', orderable: false, searchable: false  },
        ],
    });
  }
init();