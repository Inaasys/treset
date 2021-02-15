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
    $('#fecha').val(hoy);
    $('input[type=datetime-local]').val(new Date().toJSON().slice(0,19));
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  $.get(compras_obtener_ultimo_folio, function(folio){
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
  $("#formxml")[0].reset();
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
function relistar(){
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
}
//listar todos los registros de la tabla
function listar(){
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
        url: compras_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.Status ==  `BAJA`){ $(row).addClass('bg-red');}
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
//obtener tipos ordenes de compra
function obtenertiposordenescompra(){
    $.get(compras_obtener_tipos_ordenes_compra, function(select_tipos_ordenes_compra){
      $("#tipo").html(select_tipos_ordenes_compra);
    })  
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
                                  '<table id="tbllistadoproveedor" class="tbllistadoproveedor table table-bordered table-striped table-hover" style="width:100% !important;">'+
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
            url: compras_obtener_proveedores,
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
function obteneralmacenes(){
    ocultarformulario();
    var tablaalmacenes = '<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Almacenes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Almacén</th>'+
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
      $("#contenidomodaltablas").html(tablaalmacenes);
      $('#tbllistadoalmacen').DataTable({
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
              url: compras_obtener_almacenes,
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
//obtener datos del proveedor
function seleccionarproveedor(Numero, Nombre){
    $("#numeroproveedor").val(Numero);
    $("#proveedor").val(Nombre);
    //mostrar boton de buscar ordenes de compra
    $("#btnlistarordenesdecompra").show();
    mostrarformulario();
}
//obtener datos del almacen
function seleccionaralmacen(Numero, Nombre){
    $("#numeroalmacen").val(Numero);
    $("#almacen").val(Nombre);
    mostrarformulario();
}
//listar departamentos
function listardepartamentos(fila){
  ocultarformulario();
  var tabladepartamentos =  '<div class="modal-header bg-red">'+
                                '<h4 class="modal-title">Departamentos</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                            '<table id="tbllistadodepartamento" class="tbllistadodepartamento table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                                '<thead class="customercolor">'+
                                                    '<tr>'+
                                                        '<th>Operaciones</th>'+
                                                        '<th>Numero</th>'+
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
  $("#contenidomodaltablas").html(tabladepartamentos);
  $('#tbllistadodepartamento').DataTable({
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
          url: compras_obtener_departamentos,
          data: function (d) {
            d.fila = fila;
          }
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
                $('#tbllistadodepartamento').DataTable().search( this.value ).draw();
              }
          });
      },
      "iDisplayLength": 8,
  });
}
//seleccion de departamento
function seleccionardepartamento(numerodepartamento, departamento, fila){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){       
      $(".numerodepartamentopartida", this).val(numerodepartamento);
      $(".departamentopartida", this).val(departamento);
    }  
    cuentaFilas++;
  });   
  mostrarformulario();
}

//alta clientes
function alta(){
  $("#titulomodal").html('Alta Compra Prod');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs =    '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#compratab" data-toggle="tab">Compra</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#emisorreceptortab" data-toggle="tab">Emisor, Receptor</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="compratab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Compra <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+
                                        '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly>'+
                                        '<input type="hidden" class="form-control" name="uuid" id="uuid" readonly required>'+
                                        '<input type="hidden" class="form-control" name="diferenciatotales" id="diferenciatotales" readonly required>'+
                                    '</div>'+   
                                    '<div class="col-md-3">'+
                                        '<label>Plazo Días (proveedor)</label>'+
                                        '<input type="text" class="form-control" name="plazo" id="plazo"  required readonly onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Fecha</label>'+
                                        '<input type="date" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();validarmescompra();">'+
                                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                        '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                                    '</div>'+   
                                    '<div class="col-md-3">'+
                                        '<label>Emitida</label>'+
                                        '<input type="datetime-local" class="form-control" name="fechaemitida" id="fechaemitida"  required readonly>'+
                                        '<input type="hidden" class="form-control" name="fechatimbrado" id="fechatimbrado" >'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Proveedor</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()" style="display:none">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="hidden" class="form-control" name="numeroproveedor" id="numeroproveedor" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="text" class="form-control" name="proveedor" id="proveedor" required readonly>'+
                                                    '</div>'+
                                                '</td>'+    
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Almacen</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td hidden>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obteneralmacenes()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="hidden" class="form-control" name="numeroalmacen" id="numeroalmacen" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="text" class="form-control" name="almacen" id="almacen" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Remisión</label>'+
                                        '<input type="text" class="form-control" name="remision" id="remision" onkeyup="tipoLetra(this)">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Factura</label>'+
                                        '<input type="text" class="form-control" name="factura" id="factura" required onkeyup="tipoLetra(this)">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+    
                                    '<div class="col-md-3">'+
                                        '<label>Tipo</label>'+
                                        '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required readonly>'+
                                        '</select>'+
                                    '</div>'+
                                    '<div class="col-md-6" id="divbuscarcodigoproducto">'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<label>Moneda</label>'+
                                                    '<select name="moneda" id="moneda" class="form-control select2" style="width:100% !important;" required>'+
                                                        '<option value="MXN">MXN</option>'+
                                                        '<option value="USD">USD</option>'+
                                                        '<option value="EUR">EUR</option>'+
                                                    '</select>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<label>Pesos</label>'+
                                                    '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                                '</td>'+
                                            '</tr>'+
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Cargar Ordenes de Compra</label>'+
                                        '<div class="btn btn-block bg-blue waves-effect" id="btnlistarordenesdecompra" onclick="listarordenesdecompra()" style="display:none">VER ORDENES DE COMPRA</div>'+
                                        '<input type="hidden" class="form-control" name="orden" id="orden" required readonly>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+   
                            '<div role="tabpanel" class="tab-pane fade" id="emisorreceptortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Emisor R.F.C.</label>'+
                                        '<input type="text" class="form-control" name="emisorrfc" id="emisorrfc"  required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Emisor Nombre</label>'+
                                        '<input type="text" class="form-control" name="emisornombre" id="emisornombre" required onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Receptor R.F.C.</label>'+
                                        '<input type="text" class="form-control" name="receptorrfc" id="receptorrfc"  required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Receptor Nombre</label>'+
                                        '<input type="text" class="form-control" name="receptornombre" id="receptornombre" required onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+   
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#productostab" data-toggle="tab">Productos</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12 table-responsive">'+
                                        '<table id="tablaproductodordencompra" class="table table-bordered tablaproductodordencompra">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                '<th>#</th>'+
                                                '<th class="customercolortheadth">Código</th>'+
                                                '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                                '<th class="customercolortheadth">Unidad</th>'+
                                                '<th>Por Surtir</th>'+
                                                '<th class="customercolortheadth">Cantidad</th>'+
                                                '<th class="customercolortheadth">Precio $</th>'+
                                                '<th>Importe $</th>'+
                                                '<th class="customercolortheadth">Dcto %</th>'+
                                                '<th class="customercolortheadth">Dcto $</th>'+
                                                '<th>Importe Descuento $</th>'+
                                                '<th class="customercolortheadth">Ieps %</th>'+
                                                '<th>Traslado Ieps $</th>'+
                                                '<th>SubTotal $</th>'+
                                                '<th class="customercolortheadth">Iva %</th>'+
                                                '<th>Traslado Iva $</th>'+
                                                '<th class="customercolortheadth">Retención Iva %</th>'+
                                                '<th>Retención Iva $</th>'+
                                                '<th class="customercolortheadth">Retención Isr %</th>'+
                                                '<th>Retención Isr $</th>'+
                                                '<th class="customercolortheadth">Retención Ieps %</th>'+
                                                '<th>Retención Ieps $</th>'+
                                                '<th>Total $</th>'+
                                                '<th class="customercolortheadth">Orden</th>'+
                                                '<th class="customercolortheadth">Depto</th>'+
                                                '<th class="customercolortheadth" hidden>Precio Moneda $</th>'+
                                                '<th class="customercolortheadth" hidden>Descuento $</th>'+
                                                '<th>ClaveProducto</th>'+
                                                '<th>Nombre ClaveProducto</th>'+
                                                '<th>ClaveUnidad</th>'+
                                                '<th>Nombre ClaveUnidad</th>'+
                                                '<th>Costo Catálogo</th>'+
                                                '<th>Costo Ingresado</th>'+
                                                '</tr>'+
                                            '</thead>'+
                                            '<tbody>'+           
                                            '</tbody>'+
                                        '</table>'+
                                    '</div>'+
                                '</div>'+ 
                                '<div class="row">'+
                                  '<div class="col-md-6">'+   
                                      '<label>Observaciones</label>'+
                                      '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" required readonly></textarea>'+
                                  '</div>'+ 
                                  '<div class="col-md-3 col-md-offset-3">'+
                                        '<table class="table table-striped table-hover">'+
                                            '<tr>'+
                                                '<td class="tdmod">Importe</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importexml" id="importexml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Descuento</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentoxml" id="descuentoxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trieps">'+
                                              '<td class="tdmod">Ieps</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ieps" id="ieps" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iepsxml" id="iepsxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">SubTotal</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotalxml" id="subtotalxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Iva</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ivaxml" id="ivaxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencioniva">'+
                                              '<td class="tdmod">Retención Iva</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencioniva" id="retencioniva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionivaxml" id="retencionivaxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencionisr">'+
                                              '<td class="tdmod">Retención Isr</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionisr" id="retencionisr" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionisrxml" id="retencionisrxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencionieps">'+
                                              '<td class="tdmod">Retención Ieps</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionieps" id="retencionieps" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencioniepsxml" id="retencioniepsxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Total</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalxml" id="totalxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                        '</table>'+
                                  '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                  '<div class="col-md-12">'+   
                                    '<h5 id="mensajecalculoscompra"></h5>'+  
                                  '</div>'+
                                '</div>'+
                            '</div>'+ 
                        '</div>'+
                    '</div>'+
                '</div>';
  $("#tabsform").html(tabs);
  obtenultimonumero();
  obtenertiposordenescompra()
  asignarfechaactual();
  //se debe motrar el input para buscar los productos
  $("#divbuscarcodigoproducto").show();
  //activar los input select
  $("#tipo").select2({disabled: true});
  $("#moneda").select2();
  $("#ModalAlta").modal('show');
}
//Cada que se elija un archivo
function cambiodexml(e) {
  $("#btnenviarxml").click();
}
//Agregar respuesta a la datatable
$("#btnenviarxml").on('click', function(e){
  e.preventDefault();
  var xml = $('#xml')[0].files[0];
  var form_data = new FormData();
  form_data.append('xml', xml);  
      $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          url:compras_cargar_xml_alta,
          data: form_data,
          type: 'POST',
          contentType: false,
          processData: false,
          success: function (data) {
            if(data.array_comprobante.Descuento == null){
              var importexml = data.array_comprobante.SubTotal[0];
              var descuentoxml = '0.'+numerocerosconfigurados;
              var subtotalxml = data.array_comprobante.SubTotal[0];
              var ivaxml = data.TotalImpuestosTrasladados[0];
              var totalxml = data.array_comprobante.Total[0];
            }else{
              var importexml = data.array_comprobante.SubTotal[0];
              var descuentoxml = data.array_comprobante.Descuento[0]
              var subtotalxml = new Decimal(importexml).minus(descuentoxml);
              var ivaxml = data.TotalImpuestosTrasladados[0];
              var totalxml = new Decimal(subtotalxml).plus(ivaxml);
            }
              $("#uuidxml").val(data.uuid[0]);
              $("#uuid").val(data.uuid[0]);
              $("#fechatimbrado").val(data.fechatimbrado[0]);
              $("#emisorrfc").val(data.array_emisor.Rfc[0]);
              $("#emisornombre").val(data.array_emisor.Nombre[0]);
              $("#receptorrfc").val(data.array_receptor.Rfc[0]);
              $("#receptornombre").val(data.array_receptor.Nombre[0]);
              $("#factura").val(data.array_comprobante.Serie[0]+data.array_comprobante.Folio[0]);
              $("#importexml").val(number_format(round(importexml, numerodecimales), numerodecimales, '.', ''));
              $("#descuentoxml").val(number_format(round(descuentoxml, numerodecimales), numerodecimales, '.', ''));
              $("#subtotalxml").val(number_format(round(subtotalxml, numerodecimales), numerodecimales, '.', ''));
              $("#ivaxml").val(number_format(round(ivaxml, numerodecimales), numerodecimales, '.', ''));
              $("#totalxml").val(number_format(round(totalxml, numerodecimales), numerodecimales, '.', ''));
              $("#fechaemitida").val(data.array_comprobante.Fecha[0]);
              //mostrar el total de la factura del proveedor
              //$("#totalfacturaproveedor").html("Total factura proveedor : "+ truncar(totalxml.toFixed(parseInt(numerodecimales)), numerodecimales));
              $("#totalfacturaproveedor").html("Total factura proveedor :"+ number_format(round(totalxml, numerodecimales), numerodecimales, '.', ''))
              //mostrar boton de buscar proveedores
              $("#btnobtenerproveedores").show();
              //vaciar la fecha de la compra
              $("#fecha").val("");
              if($("#total").val() > 0){
                calculartotalordencompra();
              }
          },
          error: function (data) {
              console.log(data);
          }
      });                      
});

//listar todas las ordenes de compra
function listarordenesdecompra (){
  ocultarformulario();
  var tablaordenescompra =  '<div class="modal-header bg-red">'+
                              '<h4 class="modal-title">Ordenes de Compra</h4>'+
                            '</div>'+
                            '<div class="modal-body">'+
                              '<div class="row">'+
                                  '<div class="col-md-12">'+
                                      '<div class="table-responsive">'+
                                          '<table id="tbllistadoordencompra" class="tbllistadoordencompra table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                              '<thead class="customercolor">'+
                                                  '<tr>'+
                                                      '<th>Operaciones</th>'+
                                                      '<th>Orden</th>'+
                                                      '<th>Fecha</th>'+
                                                      '<th>Referencia</th>'+
                                                      '<th>Tipo</th>'+
                                                      '<th>Almacen</th>'+
                                                      '<th>Total</th>'+
                                                      '<th>Autorizado</th>'+
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
    $("#contenidomodaltablas").html(tablaordenescompra);
    $('#tbllistadoordencompra').DataTable({
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
          url: compras_obtener_ordenes_compra,
          data: function (d) {
              d.numeroproveedor = $("#numeroproveedor").val();
          }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'Orden', name: 'Orden' },
            { data: 'Fecha', name: 'Fecha' },
            { data: 'Referencia', name: 'Referencia', orderable: false, searchable: false },
            { data: 'Tipo', name: 'Tipo', orderable: false, searchable: false },
            { data: 'Almacen', name: 'Almacen', orderable: false, searchable: false },
            { data: 'Total', name: 'Total', orderable: false, searchable: false },
            { data: 'AutorizadoPor', name: 'AutorizadoPor', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadoordencompra').DataTable().search( this.value ).draw();
                }
            });
        },
        "iDisplayLength": 8,
    });  
} 
//obtener todos los datos de la orden de compra seleccionada
function seleccionarordencompra(Folio, Orden){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(compras_obtener_orden_compra, {Folio:Folio, Orden:Orden}, function(data){
    $("#orden").val(Orden);
    $("#almacen").val(data.almacen.Nombre);
    $("#numeroalmacen").val(data.almacen.Numero);
    $("#observaciones").val(data.ordencompra.Obs);
    $("#plazo").val(data.ordencompra.Plazo);
    $("#tablaproductodordencompra tbody").html(data.filasdetallesordencompra);
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#total").val(data.total);
    seleccionartipoordencompra(data);
  })  
}
async function seleccionartipoordencompra(data){
  await retraso();
  $("#tipo").val(data.ordencompra.Tipo).change();
  calculartotalordencompra();
  mostrarformulario();
  $('.page-loader-wrapper').css('display', 'none');
} 

//calcular total de la orden de compra
function calculartotalesfilasordencompra(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var cantidadpartida = $(".cantidadpartida", this).val();
      var preciopartida = $('.preciopartida', this).val();
      var importepartida = $('.importepartida', this).val(); 
      var descuentopesospartida = $('.descuentopesospartida', this).val();
      var importedescuentopesospartida = $('.importedescuentopesospartida', this).val();
      var iepsporcentajepartida = $(".iepsporcentajepartida", this).val();
      var trasladoiepspesospartida = $(".trasladoiepspesospartida", this).val();
      var subtotalpartida = $(".subtotalpartida", this).val();
      var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
      var trasladoivapesospartida = $('.trasladoivapesospartida', this).val();
      var retencionivaporcentajepartida = $(".retencionivaporcentajepartida", this).val();
      var retencionivapesospartida = $(".retencionivapesospartida", this).val();
      var retencionisrporcentajepartida = $(".retencionisrporcentajepartida", this).val();
      var retencionisrpesospartida = $(".retencionisrpesospartida", this).val();
      var retencioniepsporcentajepartida = $(".retencioniepsporcentajepartida", this).val();
      var retencioniepspesospartida = $(".retencioniepspesospartida", this).val();
      var totalpesospartida = $('.totalpesospartida', this).val(); 
      //importe de la partida
      importepartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.importepartida', this).val(number_format(round(importepartida, numerodecimales), numerodecimales, '.', ''));
      //descuento porcentaje
      var multiplicaciondescuentoporcentajepartida  =  new Decimal(descuentopesospartida).times(100);
      if(multiplicaciondescuentoporcentajepartida.d[0] > parseInt(0)){
        var descuentoporcentajepartida = new Decimal(multiplicaciondescuentoporcentajepartida/importepartida);
        $('.descuentoporcentajepartida', this).val(number_format(round(descuentoporcentajepartida, numerodecimales), numerodecimales, '.', ''));
      }      
      //importe menos descuento de la partida
      importedescuentopesospartida =  new Decimal(importepartida).minus(descuentopesospartida);
      $('.importedescuentopesospartida', this).val(number_format(round(importedescuentopesospartida, numerodecimales), numerodecimales, '.', ''));
      //ieps partida
      var multiplicaciontrasladoiepspesospartida = new Decimal(importedescuentopesospartida).times(iepsporcentajepartida);
      trasladoiepspesospartida = new Decimal(multiplicaciontrasladoiepspesospartida/100);
      $('.trasladoiepspesospartida', this).val(number_format(round(trasladoiepspesospartida, numerodecimales), numerodecimales, '.', ''));
      //subtotal partida
      subtotalpartida = new Decimal(importedescuentopesospartida).plus(trasladoiepspesospartida);
      $(".subtotalpartida", this).val(number_format(round(subtotalpartida, numerodecimales), numerodecimales, '.', ''));
      //iva en pesos de la partida
      var multiplicacionivapesospartida = new Decimal(subtotalpartida).times(ivaporcentajepartida);
      trasladoivapesospartida = new Decimal(multiplicacionivapesospartida/100);
      $('.trasladoivapesospartida', this).val(number_format(round(trasladoivapesospartida, numerodecimales), numerodecimales, '.', ''));
      //retencion iva partida
      var multiplicacionretencionivapesospartida = new Decimal(subtotalpartida).times(retencionivaporcentajepartida);
      retencionivapesospartida = new Decimal(multiplicacionretencionivapesospartida/100);
      $('.retencionivapesospartida', this).val(number_format(round(retencionivapesospartida, numerodecimales), numerodecimales, '.', ''));
      //retencion isr partida
      var multiplicacionretencionisrpesospartida = new Decimal(subtotalpartida).times(retencionisrporcentajepartida);
      retencionisrpesospartida = new Decimal(multiplicacionretencionisrpesospartida/100);
      $('.retencionisrpesospartida', this).val(number_format(round(retencionisrpesospartida, numerodecimales), numerodecimales, '.', ''));
      //retencion ieps partida
      var multiplicacionretencioniepspesospartida = new Decimal(subtotalpartida).times(retencioniepsporcentajepartida);
      retencioniepspesospartida = new Decimal(multiplicacionretencioniepspesospartida/100);
      $('.retencioniepspesospartida', this).val(number_format(round(retencioniepspesospartida, numerodecimales), numerodecimales, '.', ''));
      //total en pesos de la partida
      var subtotalmastrasladoivapartida = new Decimal(subtotalpartida).plus(trasladoivapesospartida);
      var menosretencionivapesospartida = new Decimal(subtotalmastrasladoivapartida).minus(retencionivapesospartida);
      var menosretencionisrpesospartida = new Decimal(menosretencionivapesospartida).minus(retencionisrpesospartida);
      var menosretencioniepspesospartida = new Decimal(menosretencionisrpesospartida).minus(retencioniepspesospartida);
      totalpesospartida = new Decimal(menosretencioniepspesospartida);
      $('.totalpesospartida', this).val(truncar(totalpesospartida, numerodecimales).toFixed(parseInt(numerodecimales)));
      calculartotalordencompra();
      //asignar el traslado traslado iva partida
      $(".trasladoivapartida", this).val(ivaporcentajepartida+',Tasa');
    }  
    cuentaFilas++;
  });
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodecantidadopreciopartida(fila){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){  
      //$('.descuentopesospartida', this).val('0.'+numerocerosconfigurados); 
      //$('.descuentoporcentajepartida',this).val('0.'+numerocerosconfigurados);
      calculardescuentoporcentajepartida(fila);
      calculartotalesfilasordencompra(fila);
      calculartotalordencompra();
    }  
    cuentaFilas++;
  });  
}
//calcular el porcentaje de descuento cuando el descuento en pesos se modifique
function calculardescuentoporcentajepartida(fila){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){  
      //descuento en porcentaje de la partida
      var importepartida = $('.importepartida', this).val(); 
      var descuentopesospartida = $('.descuentopesospartida', this).val(); 
      var multiplicaciondescuentoporcentajepartida  =  new Decimal(descuentopesospartida).times(100);
      if(multiplicaciondescuentoporcentajepartida.d[0] > parseInt(0)){
        var descuentoporcentajepartida = new Decimal(multiplicaciondescuentoporcentajepartida/importepartida);
        $('.descuentoporcentajepartida', this).val(number_format(round(descuentoporcentajepartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilasordencompra(fila);
        calculartotalordencompra();
      }
    }  
    cuentaFilas++;
  });    
}
//calcular el descuento en pesos cuando hay cambios en el porcentaje de descuento
function calculardescuentopesospartida(fila){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){   
      //descuento en pesos de la partida
      var importepartida = $('.importepartida', this).val();
      var descuentoporcentajepartida = $('.descuentoporcentajepartida', this).val();
      var multiplicaciondescuentopesospartida  =  new Decimal(importepartida).times(descuentoporcentajepartida);
      if(multiplicaciondescuentopesospartida.d[0] > parseInt(0)){
        var descuentopesospartida = new Decimal(multiplicaciondescuentopesospartida/100);
        $('.descuentopesospartida', this).val(number_format(round(descuentopesospartida, numerodecimales), numerodecimales, '.', ''));
        calculartotalesfilasordencompra(fila);
        calculartotalordencompra();
      }
    }  
    cuentaFilas++;
  }); 
}      
//calcular totales de orden de compra
function calculartotalordencompra(){
  var importe = 0;
  var descuento = 0;
  var subtotal= 0;
  var iva = 0;
  var total = 0;
  var ieps = 0;
  var retencioniva = 0;
  var retencionisr = 0;
  var retencionieps = 0;
  $("tr.filasproductos").each(function(){
    importe = new Decimal(importe).plus($(".importepartida", this).val());
    descuento = new Decimal(descuento).plus($(".descuentopesospartida", this).val());
    subtotal = new Decimal(subtotal).plus($(".subtotalpartida", this).val());
    iva = new Decimal(iva).plus($(".trasladoivapesospartida", this).val());
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
    ieps = new Decimal(ieps).plus($(".trasladoiepspesospartida", this).val());
    retencioniva = new Decimal(retencioniva).plus($(".retencionivapesospartida", this).val());
    retencionisr = new Decimal(retencionisr).plus($(".retencionisrpesospartida", this).val());
    retencionieps = new Decimal(retencionieps).plus($(".retencioniepspesospartida", this).val());
  }); 
  $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
  $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  $("#iva").val(number_format(round(iva, numerodecimales), numerodecimales, '.', ''));
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
  $("#ieps").val(number_format(round(ieps, numerodecimales), numerodecimales, '.', ''));
  $("#retencioniva").val(number_format(round(retencioniva, numerodecimales), numerodecimales, '.', ''));
  $("#retencionisr").val(number_format(round(retencionisr, numerodecimales), numerodecimales, '.', ''));
  $("#retencionieps").val(number_format(round(retencionieps, numerodecimales), numerodecimales, '.', ''));
  //esconder tr's
  /*if(parseFloat(ieps) == 0){
    $("#trieps").hide();
  }else{
    $("#trieps").show();
  }
  if(parseFloat(retencioniva) == 0){
    $("#trretencioniva").hide();
  }else{
    $("#trretencioniva").show();
  }
  if(parseFloat(retencionisr) == 0){
    $("#trretencionisr").hide();
  }else{
    $("#trretencionisr").show();
  }
  if(parseFloat(retencionieps) == 0){
    $("#trretencionieps").hide();
  }else{
    $("#trretencionieps").show();
  }*/
  //machar totales factura proveedor y orden de compra
  var totalxml = $("#totalxml").val();
  if(parseFloat(total) > parseFloat(totalxml)){
    var diferencia = new Decimal(total).minus(totalxml);
    $("#diferenciafacturaproveedor").html("Diferencia del total por : $ "+number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
  }else if(parseFloat(total) < parseFloat(totalxml)){
    var diferencia = new Decimal(totalxml).minus(total);
    $("#diferenciafacturaproveedor").html("Diferencia del total por : $ -"+number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
  }else if(parseFloat(total) == parseFloat(totalxml)){
    $("#diferenciafacturaproveedor").html("");
  }
  $("#diferenciatotales").val(number_format(round(diferencia, numerodecimales), numerodecimales, '.', ''));
}
//validar que la fecha de la compra sea la misma que la fecha de emision de la factura del proveedor y validar que la fecha de la compra solo sea del mismo mes y año en curso
function validarmescompra(){
  var fechaxml = new Date($("#fechaemitida").val());
  var dia = ("0" + fechaxml.getDate()).slice(-2);
  var mes = ("0" + (fechaxml.getMonth() + 1)).slice(-2);
  var fechafactura = fechaxml.getFullYear()+"-"+(mes)+"-"+(dia) ;  
  var fechacompra = $("#fecha").val();
  if(fechafactura != fechacompra){
    $("#fecha").val("");
    msj_errorfechaigualafechafactura();
  }
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var diferenciatotales = $("#diferenciatotales").val();
  if(diferenciatotales <= 0.01){
    $("#tipo").prop("disabled", false);
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:compras_guardar,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          if(data == 1){
            msj_erroruuidexistente();
            $('.page-loader-wrapper').css('display', 'none');
          }else{
            msj_datosguardadoscorrectamente();
            limpiar();
            ocultarmodalformulario();
            limpiarmodales();
            $('.page-loader-wrapper').css('display', 'none');
          }
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
  }else{
    msj_errortotalpartidasnocoincide();
  }
});
//modificacion compra
function obtenerdatos(compramodificar){
  $("#titulomodal").html('Modificación Compra');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(compras_obtener_compra,{compramodificar:compramodificar },function(data){
    //formulario modificacion
    var tabs =    '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#compratab" data-toggle="tab">Compra</a>'+
                            '</li>'+
                            '<li role="presentation">'+
                                '<a href="#emisorreceptortab" data-toggle="tab">Emisor, Receptor</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="compratab">'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Compra <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b></label>'+
                                        '<input type="text" class="form-control" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                        '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly>'+
                                        '<input type="hidden" class="form-control" name="compra" id="compra" required readonly>'+
                                        '<input type="hidden" class="form-control" name="uuid" id="uuid" readonly required>'+
                                        '<input type="hidden" class="form-control" name="diferenciatotales" id="diferenciatotales" readonly required>'+
                                    '</div>'+   
                                    '<div class="col-md-3">'+
                                        '<label>Plazo Días (proveedor)</label>'+
                                        '<input type="text" class="form-control" name="plazo" id="plazo"  required readonly onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Fecha</label>'+
                                        '<input type="date" class="form-control" name="fecha" id="fecha"  required onchange="validasolomesactual();validarmescompra();">'+
                                        '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                        '<input type="hidden" class="form-control" name="meshoy" id="meshoy" value="'+meshoy+'">'+
                                    '</div>'+   
                                    '<div class="col-md-3">'+
                                        '<label>Emitida</label>'+
                                        '<input type="datetime-local" class="form-control" name="fechaemitida" id="fechaemitida"  required readonly>'+
                                        '<input type="hidden" class="form-control" name="fechatimbrado" id="fechatimbrado" >'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-3">'+
                                        '<label>Proveedor</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<div class="btn bg-blue waves-effect" id="btnobtenerproveedores" onclick="obtenerproveedores()" style="display:none">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="hidden" class="form-control" name="numeroproveedor" id="numeroproveedor" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="text" class="form-control" name="proveedor" id="proveedor" required readonly>'+
                                                    '</div>'+
                                                '</td>'+    
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Almacen</label>'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td hidden>'+
                                                    '<div class="btn bg-blue waves-effect" onclick="obteneralmacenes()">Seleccionar</div>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<div class="form-line">'+
                                                        '<input type="hidden" class="form-control" name="numeroalmacen" id="numeroalmacen" required readonly onkeyup="tipoLetra(this)">'+
                                                        '<input type="text" class="form-control" name="almacen" id="almacen" required readonly>'+
                                                    '</div>'+
                                                '</td>'+
                                            '</tr>'+    
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Remisión</label>'+
                                        '<input type="text" class="form-control" name="remision" id="remision" onkeyup="tipoLetra(this)">'+
                                    '</div>'+
                                    '<div class="col-md-3">'+
                                        '<label>Factura</label>'+
                                        '<input type="text" class="form-control" name="factura" id="factura" required onkeyup="tipoLetra(this)">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+    
                                    '<div class="col-md-3">'+
                                        '<label>Tipo</label>'+
                                        '<select name="tipo" id="tipo" class="form-control select2" style="width:100% !important;" required readonly>'+
                                        '</select>'+
                                    '</div>'+
                                    '<div class="col-md-6" id="divbuscarcodigoproducto">'+
                                        '<table class="col-md-12">'+
                                            '<tr>'+
                                                '<td>'+
                                                    '<label>Moneda</label>'+
                                                    '<select name="moneda" id="moneda" class="form-control select2" style="width:100% !important;" required>'+
                                                        '<option value="MXN">MXN</option>'+
                                                        '<option value="USD">USD</option>'+
                                                        '<option value="EUR">EUR</option>'+
                                                    '</select>'+
                                                '</td>'+
                                                '<td>'+
                                                    '<label>Pesos</label>'+
                                                    '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" name="pesosmoneda" id="pesosmoneda" value="1.'+numerocerosconfigurados+'" required data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                                '</td>'+
                                            '</tr>'+
                                        '</table>'+
                                    '</div>'+
                                    '<div class="col-md-3" id="divbtnlistarordenes">'+
                                        '<label>Cargar Ordenes de Compra</label>'+
                                        '<div class="btn bg-blue waves-effect" id="btnlistarordenesdecompra" onclick="listarordenesdecompra()" style="display:none">Ver Ordenes de Compra</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+   
                            '<div role="tabpanel" class="tab-pane fade" id="emisorreceptortab">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Emisor R.F.C.</label>'+
                                        '<input type="text" class="form-control" name="emisorrfc" id="emisorrfc"  required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Emisor Nombre</label>'+
                                        '<input type="text" class="form-control" name="emisornombre" id="emisornombre" required onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Receptor R.F.C.</label>'+
                                        '<input type="text" class="form-control" name="receptorrfc" id="receptorrfc"  required data-parsley-regexrfc="^[A-Z,0-9]{12,13}$" onkeyup="tipoLetra(this);mayusculas(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Receptor Nombre</label>'+
                                        '<input type="text" class="form-control" name="receptornombre" id="receptornombre" required onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+   
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-md-12">'+
                        '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                            '<li role="presentation" class="active">'+
                                '<a href="#productostab" data-toggle="tab">Productos</a>'+
                            '</li>'+
                        '</ul>'+
                        '<div class="tab-content">'+
                            '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                                '<div class="row">'+
                                    '<div class="col-md-12 table-responsive">'+
                                        '<table id="tablaproductodordencompra" class="table table-bordered tablaproductodordencompra">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                '<th>#</th>'+
                                                '<th class="customercolortheadth">Código</th>'+
                                                '<th class="customercolortheadth"><div style="width:200px !important;">Descripción</div></th>'+
                                                '<th class="customercolortheadth">Unidad</th>'+
                                                '<th hidden>Por Surtir</th>'+
                                                '<th class="customercolortheadth">Cantidad</th>'+
                                                '<th class="customercolortheadth">Precio $</th>'+
                                                '<th>Importe $</th>'+
                                                '<th class="customercolortheadth">Dcto %</th>'+
                                                '<th class="customercolortheadth">Dcto $</th>'+
                                                '<th>Importe Descuento $</th>'+
                                                '<th class="customercolortheadth">Ieps %</th>'+
                                                '<th>Traslado Ieps $</th>'+
                                                '<th>SubTotal $</th>'+
                                                '<th class="customercolortheadth">Iva %</th>'+
                                                '<th>Traslado Iva $</th>'+
                                                '<th class="customercolortheadth">Retención Iva %</th>'+
                                                '<th>Retención Iva $</th>'+
                                                '<th class="customercolortheadth">Retención Isr %</th>'+
                                                '<th>Retención Isr $</th>'+
                                                '<th class="customercolortheadth">Retención Ieps %</th>'+
                                                '<th>Retención Ieps $</th>'+
                                                '<th>Total $</th>'+
                                                '<th class="customercolortheadth">Orden</th>'+
                                                '<th class="customercolortheadth">Depto</th>'+
                                                '<th class="customercolortheadth" hidden>Precio Moneda $</th>'+
                                                '<th class="customercolortheadth" hidden>Descuento $</th>'+
                                                '<th>ClaveProducto</th>'+
                                                '<th>Nombre ClaveProducto</th>'+
                                                '<th>ClaveUnidad</th>'+
                                                '<th>Nombre ClaveUnidad</th>'+
                                                '<th>Costo Catálogo</th>'+
                                                '<th>Costo Ingresado</th>'+
                                                '</tr>'+
                                            '</thead>'+
                                            '<tbody>'+           
                                            '</tbody>'+
                                        '</table>'+
                                    '</div>'+
                                '</div>'+ 
                                '<div class="row">'+
                                  '<div class="col-md-6">'+   
                                      '<label>Observaciones</label>'+
                                      '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" required readonly></textarea>'+
                                  '</div>'+ 
                                  '<div class="col-md-3 col-md-offset-3">'+
                                        '<table class="table table-striped table-hover">'+
                                            '<tr>'+
                                                '<td class="tdmod">Importe</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importexml" id="importexml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Descuento</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuentoxml" id="descuentoxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trieps">'+
                                              '<td class="tdmod">Ieps</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ieps" id="ieps" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iepsxml" id="iepsxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">SubTotal</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotalxml" id="subtotalxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Iva</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="ivaxml" id="ivaxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencioniva">'+
                                              '<td class="tdmod">Retención Iva</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencioniva" id="retencioniva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionivaxml" id="retencionivaxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencionisr">'+
                                              '<td class="tdmod">Retención Isr</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionisr" id="retencionisr" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionisrxml" id="retencionisrxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr id="trretencionieps">'+
                                              '<td class="tdmod">Retención Ieps</td>'+
                                              '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencionieps" id="retencionieps" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                              '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="retencioniepsxml" id="retencioniepsxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<td class="tdmod">Total</td>'+
                                                '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                                '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="totalxml" id="totalxml" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                            '</tr>'+
                                        '</table>'+
                                  '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                  '<div class="col-md-12">'+   
                                    '<h5 id="mensajecalculoscompra"></h5>'+  
                                  '</div>'+
                                '</div>'+
                            '</div>'+ 
                        '</div>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //esconder el div del boton listar ordenes
    $("#divbtnlistarordenes").hide();
    $("#folio").val(data.compra.Folio);
    $("#compra").val(data.compra.Compra);
    $("#uuidxml").val(data.compra.UUID);
    $("#uuid").val(data.compra.UUID);
    $("#plazo").val(data.compra.Plazo);
    $("#fecha").val(data.fecha);
    $("#fechaemitida").val(data.fechaemitida)
    $("#proveedor").val(data.proveedor.Nombre)
    $("#numeroproveedor").val(data.proveedor.Numero);
    $("#almacen").val(data.almacen.Nombre);
    $("#numeroalmacen").val(data.almacen.Numero);
    $("#remision").val(data.compra.Remision);
    $("#factura").val(data.compra.Factura);
    $("#moneda").val(data.compra.Moneda).change();
    $("#pesosmoneda").val(data.tipocambio);
    $("#observaciones").val(data.compra.Obs);
    //cargar todos los detalles
    $("#tablaproductodordencompra tbody").html(data.filasdetallescompra);
    //totales compra
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#ieps").val(data.ieps);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#retencioniva").val(data.ivaretencion);
    $("#retencionisr").val(data.isrretencion);
    $("#retencionieps").val(data.iepsretencion);
    $("#total").val(data.total);
    //totales xml
    $("#importexml").val(data.importe);
    $("#descuentoxml").val(data.descuento);
    $("#iepsxml").val(data.ieps);
    $("#subtotalxml").val(data.subtotal);
    $("#ivaxml").val(data.iva);
    $("#retencionivaxml").val(data.ivaretencion);
    $("#retencionisrxml").val(data.isrretencion);
    $("#retencioniepsxml").val(data.iepsretencion);
    $("#totalxml").val(data.total);    
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    obtenertiposordenescompra();
    seleccionartipocompra(data);
  })
}
async function seleccionartipocompra(data){
  await retraso();
  $("#tipo").val(data.compra.Tipo).change();
  calculartotalordencompra();
  $("#tipo").select2({disabled: true});
  $("#moneda").select2();
  mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
  $('.page-loader-wrapper').css('display', 'none');
}
//revisar si hay existencias de la partida en el almacen
function revisarexistenciasalmacen(fila){
  var cuentaFilas = 0;
  var cantidadoperacionaritmetica = 0;
  var cantidadoperacionaritmetica = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){ 
      var cantidadinicialpartida = $(".cantidadinicialpartida", this).val();
      var cantidadpartida = $(".cantidadpartida", this).val();
      if(cantidadpartida > cantidadinicialpartida){
        $(".operacionaritmetica", this).val("suma");
        cantidadoperacionaritmetica = new Decimal(cantidadpartida).minus(cantidadinicialpartida);
        $(".cantidadoperacionaritmetica", this).val(number_format(round(cantidadoperacionaritmetica, numerodecimales), numerodecimales, '.', ''));
        //esconder error en existencias
        $(".cantidaderrorexistencias", this).css('display','none');
        $(".cantidaderrorexistencias", this).html("");
        $(".cantidadincorrecta", this).val(0);
      }else if(cantidadpartida < cantidadinicialpartida){
        $(".operacionaritmetica", this).val("resta");
        cantidadoperacionaritmetica = new Decimal(cantidadinicialpartida).minus(cantidadpartida);
        $(".cantidadoperacionaritmetica", this).val(number_format(round(cantidadoperacionaritmetica, numerodecimales), numerodecimales, '.', ''));
        var almacen = $("#numeroalmacen").val();
        var codigopartida = $(".codigoproductopartida", this).val();
        comprobarexistenciaspartida(almacen, codigopartida).then(existencias=>{
          if(cantidadoperacionaritmetica > existencias){
            //mostrar error en existencias
            $(".cantidaderrorexistencias", this).css('display','block');
            $(".cantidaderrorexistencias", this).html("Error el almacen no cuenta con existencias suficientes");
            
            $(".cantidadincorrecta", this).val(1);
          }else{
            //esconder error en existencias
            $(".cantidaderrorexistencias", this).css('display','none');
            $(".cantidaderrorexistencias", this).html("");
            $(".cantidadincorrecta", this).val(0);
          }
        })
      }else{
        $(".operacionaritmetica", this).val("");
        $(".cantidadoperacionaritmetica", this).val(0);
        //esconder error en existencias
        $(".cantidaderrorexistencias", this).css('display','none');
        $(".cantidaderrorexistencias", this).html("");
        $(".cantidadincorrecta", this).val(0);
      }
    }  
    cuentaFilas++;
  }); 
  //revisar si se mostrara o ocultara el boton de guardar
  cantidadesinsuficientesalmacen();
}
//funcion que revisa si se mostrara o ocultara el boton de guardar
async function cantidadesinsuficientesalmacen(){
  await retraso();
  var cantidadincorrecta = 0;
  $("tr.filasproductos").each(function () {
    if($(".cantidadincorrecta", this).val() == 1){
      cantidadincorrecta++;
    }
  });
  if(cantidadincorrecta > 0){
    $("#btnGuardarModificacion").hide();
  } else{
    $("#btnGuardarModificacion").show();
  }
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciaspartida(almacen, codigopartida){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(compras_obtener_existencias_partida,{'almacen':almacen,'codigopartida':codigopartida},existencias=>{
        return ejecuta(existencias);
      })
    },500);
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
      url:compras_guardar_modificacion,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        if(data == 1){
          msj_erroruuidexistente();
          $('.page-loader-wrapper').css('display', 'none');
        }else{
          msj_datosguardadoscorrectamente();
          limpiar();
          ocultarmodalformulario();
          limpiarmodales();
          $('.page-loader-wrapper').css('display', 'none');
        }
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
function desactivar(compradesactivar){
  $.get(compras_verificar_uso_en_modulos,{compradesactivar:compradesactivar}, function(data){
    if(data.resultado > 0 && data.numerodetallesconexistenciasinsuficientes == 0){
      $("#compradesactivar").val(0);
      $("#textomodaldesactivar").html('Error esta compra tiene registros de cuentas por pagar con el pago: ' + data.numerocuentaxpagar);
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else if(data.numerodetallesconexistenciasinsuficientes > 0){
      $("#compradesactivar").val(0);
      $("#textomodaldesactivar").html('Error el Almacen con cuenta con existencias suficientes para cancelar la compra: ' + compradesactivar);
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{
      $("#compradesactivar").val(compradesactivar);
      $("#textomodaldesactivar").html('Estas seguro de cambiar el estado el registro?');
      $("#divmotivobaja").show();
      $("#btnbaja").show();
      $('#estatusregistro').modal('show');
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
      url:compras_alta_o_baja,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#estatusregistro').modal('hide');
        msj_datosguardadoscorrectamente();
        $("#motivobaja").val("");
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

//obtener movimiento compra
function movimientoscompra(compra){
  $.get(compras_obtener_movimientos_compra, {compra:compra}, function(data){
    $("#modalmovimientoscompra").modal('show');
    $("#filasmovimientos").html(data.filasmovimientos);
  });
}

//hacer busqueda de folio para exportacion en pdf
function relistarbuscarstringlike(){
  var tabla = $('#tablafoliosencontrados').DataTable();
  tabla.ajax.reload();
}
function buscarstringlike(){
  var columnastablafoliosencontrados =  '<tr>'+
                                          '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                          '<th>Compra</th>'+
                                          '<th>UUID</th>'+
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
          url: compras_buscar_folio_string_like,
          data: function (d) {
            d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Compra', name: 'Compra' },
          { data: 'UUID', name: 'UUID'},
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
                                  '<label>DATOS COMPRA</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Compra" id="idCompra" class="filled-in datotabla" value="Compra" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idCompra">Compra</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idStatus">Status</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Proveedor" id="idProveedor" class="filled-in datotabla" value="Proveedor" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idProveedor">Proveedor</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Plazo" id="idPlazo" class="filled-in datotabla" value="Plazo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPlazo">Plazo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Fecha" id="idFecha" class="filled-in datotabla" value="Fecha" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFecha">Fecha</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaEmitida" id="idFechaEmitida" class="filled-in datotabla" value="FechaEmitida" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaEmitida">FechaEmitida</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Remision" id="idRemision" class="filled-in datotabla" value="Remision" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idRemision">Remision</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Factura" id="idFactura" class="filled-in datotabla" value="Factura" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFactura">Factura</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+    
                                  '<input type="checkbox" name="Tipo" id="idTipo" class="filled-in datotabla" value="Tipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTipo">Tipo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Almacen" id="idAlmacen" class="filled-in datotabla" value="Almacen" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAlmacen">Almacen</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Movimiento" id="idMovimiento" class="filled-in datotabla" value="Movimiento" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMovimiento">Movimiento</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="UUID" id="idUUID" class="filled-in datotabla" value="UUID" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idUUID">UUID</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Orden" id="idOrden" class="filled-in datotabla" value="Orden" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idOrden">Orden</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="SubTotal" id="idSubTotal" class="filled-in datotabla" value="SubTotal" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idSubTotal">SubTotal</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Iva" id="idIva" class="filled-in datotabla" value="Iva" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idIva">Iva</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Total" id="idTotal" class="filled-in datotabla" value="Total" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTotal">Total</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Abonos" id="idAbonos" class="filled-in datotabla" value="Abonos" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAbonos">Abonos</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Descuentos" id="idDescuentos" class="filled-in datotabla" value="Descuentos" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idDescuentos">Descuentos</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Saldo" id="idSaldo" class="filled-in datotabla" value="Saldo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idSaldo">Saldo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="TipoCambio" id="idTipoCambio" class="filled-in datotabla" value="TipoCambio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTipoCambio">TipoCambio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Obs" id="idObs" class="filled-in datotabla" value="Obs" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idObs">Obs</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Equipo" id="idEquipo" class="filled-in datotabla" value="Equipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEquipo">Equipo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Usuario" id="idUsuario" class="filled-in datotabla" value="Usuario" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idUsuario">Usuario</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Periodo" id="idPeriodo" class="filled-in datotabla" value="Periodo" onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idPeriodo">Periodo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Folio" id="idFolio" class="filled-in datotabla" value="Folio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFolio">Folio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Serie" id="idSerie" class="filled-in datotabla" value="Serie" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idSerie">Serie</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="MotivoBaja" id="idMotivoBaja" class="filled-in datotabla" value="MotivoBaja" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMotivoBaja">MotivoBaja</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ReceptorNombre" id="idReceptorNombre" class="filled-in datotabla" value="ReceptorNombre" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idReceptorNombre">ReceptorNombre</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ReceptorRfc" id="idReceptorRfc" class="filled-in datotabla" value="ReceptorRfc" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idReceptorRfc">ReceptorRfc</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="EmisorNombre" id="idEmisorNombre" class="filled-in datotabla" value="EmisorNombre" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEmisorNombre">EmisorNombre</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="EmisorRfc" id="idEmisorRfc" class="filled-in datotabla" value="EmisorRfc" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEmisorRfc">EmisorRfc</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaTimbrado" id="idFechaTimbrado" class="filled-in datotabla" value="FechaTimbrado" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaTimbrado">FechaTimbrado</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Moneda" id="idMoneda" class="filled-in datotabla" value="Moneda" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMoneda">Moneda</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ImpLocTraslados" id="idImpLocTraslados" class="filled-in datotabla" value="ImpLocTraslados" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idImpLocTraslados">ImpLocTraslados</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ImpLocRetenciones" id="idImpLocRetenciones" class="filled-in datotabla" value="ImpLocRetenciones" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idImpLocRetenciones">ImpLocRetenciones</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="IepsRetencion" id="idIepsRetencion" class="filled-in datotabla" value="IepsRetencion" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idIepsRetencion">IepsRetencion</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="IsrRetencion" id="idIsrRetencion" class="filled-in datotabla" value="IsrRetencion" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idIsrRetencion">IsrRetencion</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="IvaRetencion" id="idIvaRetencion" class="filled-in datotabla" value="IvaRetencion" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idIvaRetencion">IvaRetencion</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Ieps" id="idIeps" class="filled-in datotabla" value="Ieps" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idIeps">Ieps</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Descuento" id="idDescuento" class="filled-in datotabla" value="Descuento" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idDescuento">Descuento</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Importe" id="idImporte" class="filled-in datotabla" value="Importe" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idImporte">Importe</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="BloquearObsoleto" id="idBloquearObsoleto" class="filled-in datotabla" value="BloquearObsoleto" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idBloquearObsoleto">BloquearObsoleto</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Departamento" id="idDepartamento" class="filled-in datotabla" value="Departamento" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idDepartamento">Departamento</label>'+
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
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS ALMACEN</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="NumeroAlmacen" id="idNumeroAlmacen" class="filled-in datotabla" value="NumeroAlmacen"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNumeroAlmacen">NumeroAlmacen</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="NombreAlmacen" id="idNombreAlmacen" class="filled-in datotabla" value="NombreAlmacen"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNombreAlmacen">NombreAlmacen</label>'+                                     
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