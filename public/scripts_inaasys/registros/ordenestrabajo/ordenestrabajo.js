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
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  $.get(ordenes_compra_obtener_ultimo_folio, function(folio){
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
  //reiniciar los contadores de la tabla de detalle de la orden de compra
  contadorproductos=0;
  contadorfilas = 0;
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
          'searchable': false
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
            url: ordenes_trabajo_obtener,
            data: function (d) {
                d.periodo = $("#periodo").val();
            }
        },
        "createdRow": function( row, data, dataIndex){
            if( data.Status ==  `ABIERTA`){ $(row).addClass('bg-red');}
            else if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
            else if( data.Status ==  `CERRADA`){ $(row).addClass('bg-light-green');}
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
//obtener tipos ordenes de compra
function obtenertiposordenescompra(){
    $.get(ordenes_compra_obtener_tipos_ordenes_compra, function(select_tipos_ordenes_compra){
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
                                  '<table id="tbllistadoproveedor" class="tbllistadoproveedor table table-bordered table-striped table-hover">'+
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
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: ordenes_compra_obtener_proveedores,
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
                                        '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover">'+
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
          processing: true,
          'language': {
              'loadingRecords': '&nbsp;',
              'processing': '<div class="spinner"></div>'
          },
          serverSide: true,
          ajax: {
              url: ordenes_compra_obtener_almacenes,
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
function seleccionarproveedor(Numero, Nombre, Plazo){
    $("#numeroproveedor").val(Numero);
    $("#proveedor").val(Nombre);
    //colocar el plazo del proveedor
    $("#plazo").val(Plazo);
    mostrarformulario();
}
function seleccionaralmacen(Numero, Nombre){
    $("#numeroalmacen").val(Numero);
    $("#almacen").val(Nombre);
    mostrarformulario();
}
//detectar cuando en el input de buscar por codigo de producto el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
$(document).ready(function(){
  $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        listarproductos();
      }
  });
});
//listar productos para tab consumos
function listarproductos(){
  ocultarformulario();
  var tablaproductos = '<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Productos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                            '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                '<table id="tbllistadoproducto" class="tbllistadoproducto table table-bordered table-striped table-hover">'+
                                  '<thead class="customercolor">'+
                                    '<tr>'+
                                      '<th>Operaciones</th>'+
                                      '<th>Código</th>'+
                                      '<th>Marca</th>'+
                                      '<th>Producto</th>'+
                                      '<th>Almacen</th>'+
                                      '<th>Ubicación</th>'+
                                      '<th>Existen</th>'+
                                      '<th>Costo $</th>'+
                                      '<th>Sub Total $</th>'+
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
  $("#contenidomodaltablas").html(tablaproductos);
  $('#tbllistadoproducto').DataTable({
    processing: true,
      'language': {
        'loadingRecords': '&nbsp;',
        'processing': '<div class="spinner"></div>'
      },
    serverSide: true,
    ajax: {
      url: ordenes_compra_obtener_productos,
      data: function (d) {
        d.codigoabuscar = $("#codigoabuscar").val();
      }
    },
    columns: [
      { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
      { data: 'Codigo', name: 'Codigo' },
      { data: 'Marca', name: 'Marca', orderable: false, searchable: false  },
      { data: 'Producto', name: 'Producto', orderable: false, searchable: false  },
      { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false  },
      { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false  },
      { data: 'Existencias', name: 'Existencias', orderable: false, searchable: false  },
      { data: 'Costo', name: 'Costo', orderable: false, searchable: false  },
      { data: 'SubTotal', name: 'SubTotal', orderable: false, searchable: false  } 
    ],
    "initComplete": function() {
      var $buscar = $('div.dataTables_filter input');
      $buscar.unbind();
      $buscar.bind('keyup change', function(e) {
        if(e.keyCode == 13 || this.value == "") {
          $('#tbllistadoproducto').DataTable().search( this.value ).draw();
        }
      });
    },
    "iDisplayLength": 8,
  });
}
//función que evalua si la partida que quieren ingresar ya existe o no en el detalle de la orden de compra
function evaluarproductoexistente(Codigo){
  var sumaiguales=0;
  var sumadiferentes=0;
  var sumatotal=0;
  $("tr.filasproductos").each(function () {
      var codigoproducto = $('.codigoproductopartida', this).val();
      if(Codigo === codigoproducto){
          sumaiguales++;
      }else{
          sumadiferentes++;
      }
      sumatotal++;
  });
  var resta = parseInt(sumadiferentes) - parseInt(sumaiguales);
  var total = sumatotal;
  if(resta != total){
      var result = true;
  }else{
      var result = false;
  }
  return result;
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
      var subtotalpartida = $('.subtotalpartida', this).val();
      var ivaporcentajepartida = $('.ivaporcentajepartida', this).val();
      var ivapesospartida = $('.ivapesospartida', this).val();
      var totalpesospartida = $('.totalpesospartida', this).val(); 
      //importe de la partida
      importepartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.importepartida', this).val(number_format(round(importepartida, numerodecimales), numerodecimales, '.', ''));
      //subtotal de la partida
      subtotalpartida =  new Decimal(importepartida).minus(descuentopesospartida);
      $('.subtotalpartida', this).val(number_format(round(subtotalpartida, numerodecimales), numerodecimales, '.', ''));
      //iva en pesos de la partida
      var multiplicacionivapesospartida = new Decimal(subtotalpartida).times(ivaporcentajepartida);
      ivapesospartida = new Decimal(multiplicacionivapesospartida/100);
      $('.ivapesospartida', this).val(number_format(round(ivapesospartida, numerodecimales), numerodecimales, '.', ''));
      //total en pesos de la partida
      totalpesospartida = new Decimal(subtotalpartida).plus(ivapesospartida);
      $('.totalpesospartida', this).val(number_format(round(totalpesospartida, numerodecimales), numerodecimales, '.', ''));
      calculartotalordencompra();
    }  
    cuentaFilas++;
  });
}
//dejar en 0 los descuentos cuando el precio de la partida se cambie
function cambiodecantidadopreciopartida(fila,tipo){
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () {
    if(fila === cuentaFilas){  
      $('.descuentopesospartida', this).val('0.'+numerocerosconfigurados); 
      $('.descuentoporcentajepartida',this).val('0.'+numerocerosconfigurados);
      //asignar el valor de cantidad a por surtir
      if(tipo == "alta"){
        $(".porsurtirpartida", this).val($(".cantidadpartida", this).val());
      }else if(tipo == "modificacion"){
        var calculoporsurtir = new Decimal($(".cantidadpartida",this).val()).minus($(".cantidadyasurtidapartida", this).val());
        $('.porsurtirpartida', this).val(number_format(round(calculoporsurtir, numerodecimales), numerodecimales, '.', ''));
      }
      calculartotalesfilasordencompra(fila);
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
  $("tr.filasproductos").each(function(){
    importe= new Decimal(importe).plus($(".importepartida", this).val());
    descuento = new Decimal(descuento).plus($(".descuentopesospartida", this).val());
    subtotal= new Decimal(subtotal).plus($(".subtotalpartida", this).val());
    iva = new Decimal(iva).plus($(".ivapesospartida", this).val());
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
  }); 
  $("#importe").val(number_format(round(importe, numerodecimales), numerodecimales, '.', ''));
  $("#descuento").val(number_format(round(descuento, numerodecimales), numerodecimales, '.', ''));
  $("#subtotal").val(number_format(round(subtotal, numerodecimales), numerodecimales, '.', ''));
  $("#iva").val(number_format(round(iva, numerodecimales), numerodecimales, '.', ''));
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
  //if el status de la orden es backorder se debe comparar el total de la orden de compra con el total de la(s) compras en las que se ha utilizado la orden de compra
  if($("#statusordencompra").val() == "BACKORDER"){
    if($("#sumatotalcompras").val() != number_format(round(total, numerodecimales), numerodecimales, '.', '')){
      $("#btnGuardarModificacion").hide();
      msj_errortotalordencompra();
    }else{
      $("#btnGuardarModificacion").show();
    }
  }
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto){
  var result = evaluarproductoexistente(Codigo);
  if(result == false){
    var multiplicacioncostoimpuesto =  new Decimal(Costo).times(Impuesto);      
    var ivapesos = new Decimal(multiplicacioncostoimpuesto/100);
    var total = new Decimal(Costo).plus(ivapesos);
    var tipo = "alta";
    var fila=   '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('+contadorproductos+')">X</div></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'+Codigo+'" readonly>'+Codigo+'</td>'+
                        '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'+Producto+'" readonly>'+Producto+'</div></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'+Unidad+'" readonly>'+Unidad+'</td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm porsurtirpartida" name="porsurtirpartida[]" id="porsurtirpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');cambiodecantidadopreciopartida('+contadorfilas+',\''+tipo +'\');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm importepartida" name="importepartida[]" id="importepartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" id="descuentoporcentajepartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" id="descuentopesospartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" id="subtotalpartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" id="ivaporcentajepartida[]" value="'+Impuesto+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" id="ivapesospartida[]" value="'+number_format(round(ivapesos, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" id="totalpesospartida[]" value="'+number_format(round(total, numerodecimales), numerodecimales, '.', '')+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                    '</tr>';
    contadorproductos++;
    contadorfilas++;
    $("#tablaproductodordencompra").append(fila);
    mostrarformulario();
    comprobarfilaspreciosproductos();
    calculartotalordencompra();
  }else{
    msj_errorproductoyaagregado();
  }  
}
//eliminar una fila en la tabla de precios clientes
function eliminarfilapreciosproductos(numerofila){
  $("#filaproducto"+numerofila).remove();
  contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
  comprobarfilaspreciosproductos();
  renumerarfilasordencompra();//importante para todos los calculo en el modulo de orden de compra 
  calculartotalordencompra();
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilaspreciosproductos(){
  var numerofilas = $("#tablaproductodordencompra tbody tr").length;
  $("#numerofilasproducto").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilasordencompra(){
  var lista;
  var tipo = "alta";
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "calculartotalesfilasordencompra("+i+');cambiodecantidadopreciopartida('+i+',\''+tipo +'\')');
  }
  //renumero el precio de la partida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "calculartotalesfilasordencompra("+i+');cambiodecantidadopreciopartida('+i+',\''+tipo +'\')');
  }
  //renumerar descuento en pesos
  lista = document.getElementsByClassName("descuentoporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "calculardescuentopesospartida("+i+')');
  }
  //renumerar porcentaje de descuento
  lista = document.getElementsByClassName("descuentopesospartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "calculardescuentoporcentajepartida("+i+')');
  }
  //renumerar porcentaje de iva
  lista = document.getElementsByClassName("ivaporcentajepartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "calculartotalesfilasordencompra("+i+')');
  }
}  
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Orden de Compra');
  mostrarmodalformulario('ALTA', 1);
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
                                          '<th>SubTotal $</th>'+
                                          '<th class="customercolortheadth">Iva %</th>'+
                                          '<th>Iva $</th>'+
                                          '<th>Total $</th>'+
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
                            '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" required></textarea>'+
                          '</div>'+ 
                          '<div class="col-md-3 col-md-offset-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td class="tdmod">Importe</td>'+
                                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td class="tdmod">Descuento</td>'+
                                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td class="tdmod">SubTotal</td>'+
                                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
                                    '<tr>'+
                                        '<td class="tdmod">Iva</td>'+
                                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodmd" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                    '</tr>'+
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
  obtenultimonumero();
  obtenertiposordenescompra()
  asignarfechaactual();
  //se debe motrar el input para buscar los productos
  $("#divbuscarcodigoproducto").show();
  $("#ModalAlta").modal('show');
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
      url:ordenes_compra_guardar,
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
//autorizar orden de compra
function autorizarordencompra(ordenautorizar){
  $("#ordenautorizar").val(ordenautorizar);
  $('#autorizarorden').modal('show');
}
$("#btnautorizar").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formautorizar")[0]);
  var form = $("#formautorizar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:ordenes_compra_autorizar,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#autorizarorden').modal('hide');
        msj_datosguardadoscorrectamente();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#autorizarorden').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(ordendesactivar){
  $.get(ordenes_compra_verificar_uso_en_modulos,{ordendesactivar:ordendesactivar}, function(data){
    if(data.resultado > 0){
      $("#ordendesactivar").val(0);
      $("#textomodaldesactivar").html('Error esta orden de compra se esta utilizando en la compra : ' + data.numerocompra);
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{
      $("#ordendesactivar").val(ordendesactivar);
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
      url:ordenes_compra_alta_o_baja,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#estatusregistro').modal('hide');
        msj_statuscambiado();
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
function obtenerdatos(ordenmodificar){
  $("#titulomodal").html('Modificación Orden Compra');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(ordenes_compra_obtener_orden_compra,{ordenmodificar:ordenmodificar },function(data){
    //formulario modificacion
    var tabs ='<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
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
                                          '<th>SubTotal $</th>'+
                                          '<th class="customercolortheadth">Iva %</th>'+
                                          '<th>Iva $</th>'+
                                          '<th>Total $</th>'+
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
                          '<textarea class="form-control" name="observaciones" id="observaciones" rows="5" onkeyup="tipoLetra(this);" required></textarea>'+
                        '</div>'+ 
                        '<div class="col-md-3 col-md-offset-3">'+
                              '<table class="table table-striped table-hover">'+
                                  '<tr>'+
                                      '<td style="padding:0px !important;">Importe</td>'+
                                      '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="importe" id="importe" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td style="padding:0px !important;">Descuento</td>'+
                                      '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="descuento" id="descuento" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td style="padding:0px !important;">SubTotal</td>'+
                                      '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="subtotal" id="subtotal" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td style="padding:0px !important;">Iva</td>'+
                                      '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="iva" id="iva" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr>'+
                                      '<td style="padding:0px !important;">Total</td>'+
                                      '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                  '</tr>'+
                                  '<tr hidden>'+
                                      '<td style="padding:0px !important;">Total Suma Com</td>'+
                                      '<td style="padding:0px !important;" ><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="sumatotalcompras" id="sumatotalcompras" value="'+data.sumatotalcompras+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                      '<td style="padding:0px !important;" hidden><input type="text" class="form-control" style="width:100% !important;height:25px !important;" name="statusordencompra" id="statusordencompra" value="'+data.statusordencompra+'" required readonly></td>'+
                                  '</tr>'+
                              '</table>'+
                          '</div>'+
                      '</div>'+   
                  '</div>'+ 
              '</div>';
    $("#tabsform").html(tabs);
    $("#folio").val(data.ordencompra.Folio);
    $("#plazo").val(data.ordencompra.Plazo);
    $("#referencia").val(data.ordencompra.Referencia);
    $("#fecha").val(data.fecha);
    $("#numeroproveedor").val(data.proveedor.Numero);
    $("#proveedor").val(data.proveedor.Nombre);
    $("#numeroalmacen").val(data.almacen.Numero);
    $("#almacen").val(data.almacen.Nombre);
    $("#observaciones").val(data.ordencompra.Obs)
    $("#importe").val(data.importe);
    $("#descuento").val(data.descuento);
    $("#subtotal").val(data.subtotal);
    $("#iva").val(data.iva);
    $("#total").val(data.total);
    //tabs precios productos
    $("#tablaproductodordencompra").append(data.filasdetallesordencompra);
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    obtenertiposordenescompra();
    seleccionartipoordencompra(data);
  })
}
async function seleccionartipoordencompra(data){
  await retraso();
  $("#tipo").val(data.ordencompra.Tipo).change();
  //se debe esconder el input para buscar los productos porque en la modificacion no se permiten agregar productos
  $("#divbuscarcodigoproducto").hide();
  mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
  $('.page-loader-wrapper').css('display', 'none');
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
function relistarbuscarstringlike(){
  var tabla = $('#tablafoliosencontrados').DataTable();
  tabla.ajax.reload();
}
function buscarstringlike(){
  var columnastablafoliosencontrados =    '<tr>'+
                                                '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                                '<th>Orden</th>'+
                                                '<th>Cliente</th>'+
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
          url: ordenes_trabajo_buscar_folio_string_like,
          data: function (d) {
            d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Orden', name: 'Orden' },
          { data: 'Cliente', name: 'Cliente', orderable: false, searchable: false },
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
                                  '<label>DATOS ORDEN TRABAJO</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Orden" id="idOrden" class="filled-in datotabla" value="Orden" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idOrden">Orden</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idStatus">Status</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Caso" id="idCaso" class="filled-in datotabla" value="Caso" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCaso">Caso</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Fecha" id="idFecha" class="filled-in datotabla" value="Fecha" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFecha">Fecha</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Tipo" id="idTipo" class="filled-in datotabla" value="Tipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTipo">Tipo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Unidad" id="idUnidad" class="filled-in datotabla" value="Unidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idUnidad">Unidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Cliente" id="idCliente" class="filled-in datotabla" value="Cliente" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCliente">Cliente</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Total" id="idTotal" class="filled-in datotabla" value="Total" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTotal">Total</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Vin" id="idVin" class="filled-in datotabla" value="Vin" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idVin">Vin</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+    
                                  '<input type="checkbox" name="Pedido" id="idPedido" class="filled-in datotabla" value="Pedido" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPedido">Pedido</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Marca" id="idMarca" class="filled-in datotabla" value="Marca" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMarca">Marca</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Economico" id="idEconomico" class="filled-in datotabla" value="Economico" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEconomico">Economico</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Placas" id="idPlacas" class="filled-in datotabla" value="Placas" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPlacas">Placas</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Año" id="idAño" class="filled-in datotabla" value="Año" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAño">Año</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Kilometros" id="idKilometros" class="filled-in datotabla" value="Kilometros" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idKilometros">Kilometros</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Reclamo" id="idReclamo" class="filled-in datotabla" value="Reclamo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idReclamo">Reclamo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Motor" id="idMotor" class="filled-in datotabla" value="Motor" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMotor">Motor</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="MotivoBaja" id="idMotivoBaja" class="filled-in datotabla" value="MotivoBaja" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idMotivoBaja">MotivoBaja</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Usuario" id="idUsuario" class="filled-in datotabla" value="Usuario" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idUsuario">Usuario</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Equipo" id="idEquipo" class="filled-in datotabla" value="Equipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEquipo">Equipo</label>'+
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
                                  '<input type="checkbox" name="DelCliente" id="idDelCliente" class="filled-in datotabla" value="DelCliente" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idDelCliente">DelCliente</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Agente" id="idAgente" class="filled-in datotabla" value="Agente" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idAgente">Agente</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Plazo" id="idPlazo" class="filled-in datotabla" value="Plazo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPlazo">Plazo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Entrega" id="idEntrega" class="filled-in datotabla" value="Entrega" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEntrega">Entrega</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Laminado" id="idLaminado" class="filled-in datotabla" value="Laminado" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idLaminado">Laminado</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ServicioEnAgencia" id="idServicioEnAgencia" class="filled-in datotabla" value="ServicioEnAgencia" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idServicioEnAgencia">ServicioEnAgencia</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="RetrabajoOrden" id="idRetrabajoOrden" class="filled-in datotabla" value="RetrabajoOrden" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idRetrabajoOrden">RetrabajoOrden</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Impuesto" id="idImpuesto" class="filled-in datotabla" value="Impuesto" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idImpuesto">Impuesto</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Importe" id="idImporte" class="filled-in datotabla" value="Importe" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idImporte">Importe</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Descuento" id="idDescuento" class="filled-in datotabla" value="Descuento" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idDescuento">Descuento</label>'+
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
                                  '<input type="checkbox" name="Facturado" id="idFacturado" class="filled-in datotabla" value="Facturado" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFacturado">Facturado</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Costo" id="idCosto" class="filled-in datotabla" value="Costo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCosto">Costo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Comision" id="idComision" class="filled-in datotabla" value="Comision" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idComision">Comision</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Utilidad" id="idUtilidad" class="filled-in datotabla" value="Utilidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idUtilidad">Utilidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Operador" id="idOperador" class="filled-in datotabla" value="Operador" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idOperador">Operador</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="OperadorCelular" id="idOperadorCelular" class="filled-in datotabla" value="OperadorCelular" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idOperadorCelular">OperadorCelular</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Modelo" id="idModelo" class="filled-in datotabla" value="Modelo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idModelo">Modelo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Color" id="idColor" class="filled-in datotabla" value="Color" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idColor">Color</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Combustible" id="idCombustible" class="filled-in datotabla" value="Combustible" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCombustible">Combustible</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Bahia" id="idBahia" class="filled-in datotabla" value="Bahia" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idBahia">Bahia</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Forma" id="idForma" class="filled-in datotabla" value="Forma" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idForma">Forma</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ObsOrden" id="idObsOrden" class="filled-in datotabla" value="ObsOrden" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idObsOrden">ObsOrden</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ObsUnidad" id="idObsUnidad" class="filled-in datotabla" value="ObsUnidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idObsUnidad">ObsUnidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Campaña" id="idCampaña" class="filled-in datotabla" value="Campaña" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCampaña">Campaña</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Falla" id="idFalla" class="filled-in datotabla" value="Falla" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFalla">Falla</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Causa" id="idCausa" class="filled-in datotabla" value="Causa" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCausa">Causa</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Correccion" id="idCorreccion" class="filled-in datotabla" value="Correccion" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idCorreccion">Correccion</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Rodar" id="idRodar" class="filled-in datotabla" value="Rodar" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idRodar">Rodar</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Terminada" id="idTerminada" class="filled-in datotabla" value="Terminada" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTerminada">Terminada</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Facturada" id="idFacturada" class="filled-in datotabla" value="Facturada" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFacturada">Facturada</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="HoraEntrada" id="idHoraEntrada" class="filled-in datotabla" value="HoraEntrada" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idHoraEntrada">HoraEntrada</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="HoraEntrega" id="idHoraEntrega" class="filled-in datotabla" value="HoraEntrega" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idHoraEntrega">HoraEntrega</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="HorasReales" id="idHorasReales" class="filled-in datotabla" value="HorasReales" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idHorasReales">HorasReales</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Promocion" id="idPromocion" class="filled-in datotabla" value="Promocion" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idPromocion">Promocion</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="TipoServicio" id="idTipoServicio" class="filled-in datotabla" value="TipoServicio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idTipoServicio">TipoServicio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="KmProximoServicio" id="idKmProximoServicio" class="filled-in datotabla" value="KmProximoServicio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idKmProximoServicio">KmProximoServicio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaRecordatorio" id="idFechaRecordatorio" class="filled-in datotabla" value="FechaRecordatorio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaRecordatorio">FechaRecordatorio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaIngresoUnidad" id="idFechaIngresoUnidad" class="filled-in datotabla" value="FechaIngresoUnidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaIngresoUnidad">FechaIngresoUnidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaAsignacionUnidad" id="idFechaAsignacionUnidad" class="filled-in datotabla" value="FechaAsignacionUnidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaAsignacionUnidad">FechaAsignacionUnidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FechaTerminoUnidad" id="idFechaTerminoUnidad" class="filled-in datotabla" value="FechaTerminoUnidad" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idFechaTerminoUnidad">FechaTerminoUnidad</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="EstadoServicio" id="idEstadoServicio" class="filled-in datotabla" value="EstadoServicio" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idEstadoServicio">EstadoServicio</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Refactura" id="idRefactura" class="filled-in datotabla" value="Refactura" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idRefactura">Refactura</label>'+
                              '</div>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                          '</div>'+
                          '<div class="col-md-6">'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS CLIENTE</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="NumeroCliente" id="idNumeroCliente" class="filled-in datotabla" value="NumeroCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNumeroCliente">NumeroCliente</label>'+  
                              '</div>'+
                              '<div class="col-md-4 form-check">'+  
                                  '<input type="checkbox" name="NombreCliente" id="idNombreCliente" class="filled-in datotabla" value="NombreCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idNombreCliente">NombreCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="RfcCliente" id="idRfcCliente" class="filled-in datotabla" value="RfcCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idRfcCliente">RfcCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="CalleCliente" id="idCalleCliente" class="filled-in datotabla" value="CalleCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idCalleCliente">CalleCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="noExteriorCliente" id="idnoExteriorCliente" class="filled-in datotabla" value="noExteriorCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idnoExteriorCliente">noExteriorCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ColoniaCliente" id="idColoniaCliente" class="filled-in datotabla" value="ColoniaCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idColoniaCliente">ColoniaCliente</label>'+ 
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="LocalidadCliente" id="idLocalidadCliente" class="filled-in datotabla" value="LocalidadCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idLocalidadCliente">LocalidadCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="MunicipioCliente" id="idMunicipioCliente" class="filled-in datotabla" value="MunicipioCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idMunicipioCliente">MunicipioCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="EstadoCliente" id="idEstadoCliente" class="filled-in datotabla" value="EstadoCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idEstadoCliente">EstadoCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="PaisCliente" id="idPaisCliente" class="filled-in datotabla" value="PaisCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idPaisCliente">PaisCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="CodigoPostalCliente" id="idCodigoPostalCliente" class="filled-in datotabla" value="CodigoPostalCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idCodigoPostalCliente">CodigoPostalCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="ReferenciaCliente" id="idReferenciaCliente" class="filled-in datotabla" value="ReferenciaCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idReferenciaCliente">ReferenciaCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="TelefonosCliente" id="idTelefonosCliente" class="filled-in datotabla" value="TelefonosCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idTelefonosCliente">TelefonosCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="Email1Cliente" id="idEmail1Cliente" class="filled-in datotabla" value="Email1Cliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idEmail1Cliente">Email1Cliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="AnotacionesCliente" id="idAnotacionesCliente" class="filled-in datotabla" value="AnotacionesCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idAnotacionesCliente">AnotacionesCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="FormaPagoCliente" id="idFormaPagoCliente" class="filled-in datotabla" value="FormaPagoCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idFormaPagoCliente">FormaPagoCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="MetodoPagoCliente" id="idMetodoPagoCliente" class="filled-in datotabla" value="MetodoPagoCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idMetodoPagoCliente">MetodoPagoCliente</label>'+                                     
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="UsoCfdiCliente" id="idUsoCfdiCliente" class="filled-in datotabla" value="UsoCfdiCliente"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idUsoCfdiCliente">UsoCfdiCliente</label>'+                                     
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