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
  $.get(asignacion_herramienta_obtener_ultimo_id, function(id){
    $("#id").val(id);
  })  
}
//cerrar modales
function limpiarmodales(){
  $("#tabsform").empty();
  $("#tabsformauditarherramientas").empty();
}
//limpiar todos los inputs del formulario alta
function limpiar(){
  $("#formparsley")[0].reset();
  $("#formauditarherramienta")[0].reset();
  //Resetear las validaciones del formulario alta
  $("#formparsley").parsley().reset();
  $("#formauditarherramienta").parsley().reset();
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
        url: asignacion_herramienta_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.status ==  `BAJA`){ $(row).addClass('bg-orange');}
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
//obtener registros de proveedores
function obtenerpersonalrecibe(){
  ocultarformulario();
  var tablapersonalrecibe = '<div class="modal-header bg-red">'+
                      '<h4 class="modal-title">Personal que recibe</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                      '<div class="row">'+
                          '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                  '<table id="tbllistadopersonalrecibe" class="tbllistadopersonalrecibe table table-bordered table-striped table-hover" style="width:100% !important">'+
                                      '<thead class="customercolor">'+
                                          '<tr>'+
                                              '<th>Operaciones</th>'+
                                              '<th>Numero</th>'+
                                              '<th>Nombre</th>'+
                                              '<th>Fecha Ingreso</th>'+
                                              '<th>Tipo Personal</th>'+
                                              '<th>Status</th>'+
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
    $("#contenidomodaltablas").html(tablapersonalrecibe);
    $('#tbllistadopersonalrecibe').DataTable({
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
            url: asignacion_herramienta_obtener_personal_recibe,
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'fecha_ingreso', name: 'fecha_ingreso' },
            { data: 'tipo_personal', name: 'tipo_personal' },
            { data: 'status', name: 'status', orderable: false, searchable: false }
        ],
        "initComplete": function() {
            var $buscar = $('div.dataTables_filter input');
            $buscar.unbind();
            $buscar.bind('keyup change', function(e) {
                if(e.keyCode == 13 || this.value == "") {
                $('#tbllistadopersonalrecibe').DataTable().search( this.value ).draw();
                }
            });
        },
        
    }); 
} 
//obtener registros de almacenes
function obtenerpersonalentrega(){
    ocultarformulario();
    var tablapersonalentrega = '<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Personal que entrega</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive ">'+
                                        '<table id="tbllistadopersonalentrega" class="tbllistadopersonalentrega table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Nombre</th>'+
                                                    '<th>Fecha Ingreso</th>'+
                                                    '<th>Tipo Personal</th>'+
                                                    '<th>Status</th>'+
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
      $("#contenidomodaltablas").html(tablapersonalentrega);
      $('#tbllistadopersonalentrega').DataTable({
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
            url: asignacion_herramienta_obtener_personal_entrega,
            data: function (d) {
              d.numeropersonalrecibe = $("#numeropersonalrecibe").val();
            }
          },
          columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'fecha_ingreso', name: 'fecha_ingreso' },
            { data: 'tipo_personal', name: 'tipo_personal' },
            { data: 'status', name: 'status', orderable: false, searchable: false }
          ],
          "initComplete": function() {
              var $buscar = $('div.dataTables_filter input');
              $buscar.unbind();
              $buscar.bind('keyup change', function(e) {
                  if(e.keyCode == 13 || this.value == "") {
                  $('#tbllistadopersonalentrega').DataTable().search( this.value ).draw();
                  }
              });
          },
          
      }); 
  } 
function seleccionarpersonalrecibe(id, nombre){
    $("#numeropersonalrecibe").val(id);
    $("#personalrecibe").val(nombre);
    $("#btnbuscarpersonalqueentrega").show();
    mostrarformulario();
}
function seleccionarpersonalentrega(id, nombre){
    $("#numeropersonalentrega").val(id);
    $("#personalentrega").val(nombre);
    mostrarformulario();
}
//detectar cuando en el input de buscar por codigo de producto el usuario presione la tecla enter, si es asi se realizara la busqueda con el codigo escrito
$(document).ready(function(){
  $("#codigoabuscar").keypress(function(e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        listarherramientas();
      }
  });
});
//listar productos para tab consumos
function listarherramientas(){
  ocultarformulario();
  var tablaherramientas = '<div class="modal-header bg-red">'+
                          '<h4 class="modal-title">Productos</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<div class="row">'+
                            '<div class="col-md-12">'+
                              '<div class="table-responsive">'+
                                '<table id="tbllistadoherramienta" class="tbllistadoherramienta table table-bordered table-striped table-hover" style="width:100% !important">'+
                                  '<thead class="customercolor">'+
                                    '<tr>'+
                                      '<th>Operaciones</th>'+
                                      '<th>Código</th>'+
                                      '<th>Marca</th>'+
                                      '<th>Producto</th>'+
                                      '<th>Ubicación</th>'+
                                      '<th>Existencias</th>'+
                                      '<th>Almacen</th>'+
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
  $("#contenidomodaltablas").html(tablaherramientas);
  $('#tbllistadoherramienta').DataTable({
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
      url: asignacion_herramienta_obtener_herramienta,
      data: function (d) {
        d.codigoabuscar = $("#codigoabuscar").val();
        d.tipooperacion = $("#tipooperacion").val();
      }
    },
    columns: [
      { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
      { data: 'Codigo', name: 'Codigo' },
      { data: 'Marca', name: 'Marca', orderable: false, searchable: false  },
      { data: 'Producto', name: 'Producto', orderable: false, searchable: false  },
      { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false  },
      { data: 'Existencias', name: 'Existencias', orderable: false, searchable: false  },
      { data: 'Almacen', name: 'Almacen', orderable: false, searchable: false  },
      { data: 'Costo', name: 'Costo', orderable: false, searchable: false  },
      { data: 'SubTotal', name: 'SubTotal', orderable: false, searchable: false  } 
    ],
    "initComplete": function() {
      var $buscar = $('div.dataTables_filter input');
      $buscar.unbind();
      $buscar.bind('keyup change', function(e) {
        if(e.keyCode == 13 || this.value == "") {
          $('#tbllistadoherramienta').DataTable().search( this.value ).draw();
        }
      });
    },
    
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
function calculartotalesfilas(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var cantidadpartida = $(".cantidadpartida", this).val();
      var preciopartida = $('.preciopartida', this).val();
      var totalpesospartida = $('.totalpesospartida', this).val(); 
      //total de la partida
      totalpesospartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.totalpesospartida', this).val(number_format(round(totalpesospartida, numerodecimales), numerodecimales, '.', ''));
      calculartotalordencompra();
    }  
    cuentaFilas++;
  });
} 
//calcular totales de orden de compra
function calculartotalordencompra(){
  var total = 0;
  $("tr.filasproductos").each(function(){
    total = new Decimal(total).plus($(".totalpesospartida", this).val());
  }); 
  $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaherramienta(Codigo, Producto, Unidad, Costo, Existencias, selectalmacenes, tipooperacion){
  var result = evaluarproductoexistente(Codigo);
  if(result == false){
    var tipo = "alta";
    var fila=   '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'+Producto+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'+
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]">'+Unidad+'</td>'+
                        '<td class="tdmod">'+
                          '<select name="almacenpartida[]" class="form-control divorinputmodxl almacenpartida" style="width:100% !important;height: 28px !important;" onchange="obtenerexistenciasalmacen('+contadorproductos+')" required>'+
                            selectalmacenes+
                          '</select>'+
                        '</td>'+ 
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm existenciasalmacenpartida" name="existenciasalmacenpartida[]" id="existenciasalmacenpartida[]" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="1.'+numerocerosconfigurados+'" data-parsley-min="0.1" data-parsley-max="'+Existencias+'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');" required></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'+Costo+'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('+contadorfilas+');"></td>'+
                        '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" id="totalpesospartida[]" value="'+Costo+'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'+
                        '<td class="tdmod">'+
                          '<select name="estadopartida[]" class="form-control divorinputmodmd" style="width:100% !important;height: 28px !important;" required>'+
                              '<option selected disabled hidden>Selecciona</option>'+
                              '<option value="Nuevo">Nuevo</option>'+
                              '<option value="Usado">Usado</option>'+
                          '</select>'+
                        '</td>'+    
                '</tr>';
    contadorproductos++;
    contadorfilas++;
    $("#tablaherramientasasignadas").append(fila);
    mostrarformulario();
    calculartotalordencompra();
  }else{
    msj_errorproductoyaagregado();
  }  
}
//obtener las existencias actuales del almacen seleccionado
function obtenerexistenciasalmacen(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var almacenpartida = $(".almacenpartida", this).val();
      var codigoproductopartida = $(".codigoproductopartida", this).val();
      var cantidadpartida = $(".cantidadpartida", this).val();
      comprobarexistenciasalmacenpartida(almacenpartida, codigoproductopartida).then(existencias=>{
        if(parseFloat(existencias) > 0){
          var nuevaexistencia = new Decimal(existencias).plus(cantidadpartida);
          $(".existenciasalmacenpartida", this).val(number_format(round(existencias, numerodecimales), numerodecimales, '.', ''));
          $(".cantidadpartida", this).removeAttr('readonly');
          $(".cantidadpartida", this).attr('data-parsley-max', number_format(round(nuevaexistencia, numerodecimales), numerodecimales, '.', ''));
        }else{
          var nuevaexistencia = new Decimal(0).plus(cantidadpartida);
          $(".existenciasalmacenpartida", this).val(number_format(round(existencias, numerodecimales), numerodecimales, '.', ''));
          $(".cantidadpartida", this).val("0."+numerocerosconfigurados);
          $(".cantidadpartida", this).attr('readonly', 'readonly');
          $(".cantidadpartida", this).attr('data-parsley-max', number_format(round(nuevaexistencia, numerodecimales), numerodecimales, '.', ''));
        }
      }) 
    }  
    cuentaFilas++;
  });
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciasalmacenpartida(almacenpartida, codigoproductopartida){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(asignacion_herramienta_obtener_existencias_almacen,{'almacenpartida':almacenpartida,'codigoproductopartida':codigoproductopartida},existencias=>{
        return ejecuta(existencias);
      })
    },500);
  })
}
//eliminar una fila en la tabla de precios clientes
function eliminarfilapreciosproductos(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar la herramienta?"); 
  if (confirmacion == true) { 
    $("#filaproducto"+numerofila).remove();
    contadorfilas--; //importante para todos los calculo en el modulo de orden de compra se debe restar al contadorfilas la fila que se acaba de eliminar
    renumerarfilasordencompra();//importante para todos los calculo en el modulo de orden de compra 
    calculartotalordencompra();
  }
}
//renumerar las filas de la orden de compra
function renumerarfilasordencompra(){
  var lista;
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("cantidadpartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
  //renumerar la precio de la partida
  lista = document.getElementsByClassName("preciopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calculartotalesfilas("+i+')');
  }
}  
//alta clientes
function alta(){
  $("#titulomodal").html('Alta Asignación Herramienta');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs =    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#herramientastab" data-toggle="tab">Herramientas</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="herramientastab">'+
                        '<div class="row">'+
                            '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<table id="tablaherramientasasignadas" class="table table-bordered tablaherramientasasignadas">'+
                                    '<thead class="customercolor">'+
                                        '<tr>'+
                                            '<th class="customercolor">#</th>'+
                                            '<th class="customercolor">Herramienta</th>'+
                                            '<th class="customercolor"><div style="width:200px !important;">Descripción</div></th>'+
                                            '<th class="customercolortheadth">Unidad</th>'+
                                            '<th class="customercolortheadth">Almacén</th>'+
                                            '<th class="customercolortheadth">Existencias Almacén</th>'+
                                            '<th class="customercolortheadth">Cantidad</th>'+
                                            '<th class="customercolortheadth">Precio $</th>'+
                                            '<th class="customercolor">Total $</th>'+
                                            '<th class="customercolor">Estado Herramienta</th>'+
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
                            '<textarea class="form-control" name="observaciones" id="observaciones" rows="2" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
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
  $("#serie").val(serieusuario);
  $("#serietexto").html("Serie: "+serieusuario);
  obtenultimonumero();
  asignarfechaactual();
  //asignar el tipo de operacion que se realizara
  $("#tipooperacion").val("alta");
  //reiniciar contadores  
  contadorproductos=0;
  contadorfilas = 0;
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
      url:asignacion_herramienta_guardar,
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
function autorizarasignacion(asignacion){
  $.get(asignacion_herramienta_obtener_asignacion_herramienta_a_autorizar, {asignacion:asignacion}, function(filasdetallesasignacion){
    $("#asignacionautorizar").val(asignacion);
    $("#tablaautorizacionasignacionherramienta tbody").html(filasdetallesasignacion)
    $('#autorizarasignacion').modal('show');
  })  

}
$("#btnautorizar").on('click', function(e){
  e.preventDefault();
  var formData = new FormData($("#formautorizar")[0]);
  var form = $("#formautorizar");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:asignacion_herramienta_autorizar,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        $('#autorizarasignacion').modal('hide');
        msj_datosguardadoscorrectamente();
        $('.page-loader-wrapper').css('display', 'none');
      },
      error:function(data){
        if(data.status == 403){
          msj_errorenpermisos();
        }else{
          msj_errorajax();
        }
        $('#autorizarasignacion').modal('hide');
        $('.page-loader-wrapper').css('display', 'none');
      }
    })
  }else{
    form.parsley().validate();
  }
});
//verificar si la asignacion se esta utilizando en algun prestamo de herramienta
function desactivar(asignaciondesactivar){
  $.get(asignacion_herramienta_verificar_uso_en_modulos,{asignaciondesactivar:asignaciondesactivar}, function(data){
    if(data.status == 'BAJA'){
      $("#motivobaja").val("");
      $("#asignaciondesactivar").val(0);
      $("#textomodaldesactivar").html('Error, esta asignación ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{ 
      if(data.numero_prestamos > 0){
        $("#motivobaja").val("");
        $("#asignaciondesactivar").val(0);
        $("#textomodaldesactivar").html('Error esta asignación tiene herramienta prestada');
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        $("#motivobaja").val("");
        $("#asignaciondesactivar").val(asignaciondesactivar);
        $("#textomodaldesactivar").html('Estas seguro de dar de baja el registro?');
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
      url:asignacion_herramienta_alta_o_baja,
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
function obtenerdatos(asignacionmodificar){
  $("#titulomodal").html('Modificación Asignación Herramienta');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(asignacion_herramienta_obtener_asignacion_herramienta,{asignacionmodificar:asignacionmodificar },function(data){
    //formulario modificacion
    var tabs =    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                      '<li role="presentation" class="active">'+
                          '<a href="#herramientastab" data-toggle="tab">Herramientas</a>'+
                      '</li>'+
                  '</ul>'+
                  '<div class="tab-content">'+
                      '<div role="tabpanel" class="tab-pane fade in active" id="herramientastab">'+
                          '<div class="row">'+
                              '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                                  '<table id="tablaherramientasasignadas" class="table table-bordered tablaherramientasasignadas">'+
                                      '<thead class="customercolor">'+
                                          '<tr>'+
                                              '<th class="customercolor">#</th>'+
                                              '<th class="customercolor">Herramienta</th>'+
                                              '<th class="customercolor"><div style="width:200px !important;">Descripción</div></th>'+
                                              '<th class="customercolortheadth">Unidad</th>'+
                                              '<th class="customercolortheadth">Almacén</th>'+
                                              '<th class="customercolortheadth">Existencias Almacén</th>'+
                                              '<th class="customercolortheadth">Cantidad</th>'+
                                              '<th class="customercolortheadth">Precio $</th>'+
                                              '<th class="customercolor">Total $</th>'+
                                              '<th class="customercolor">Estado Herramienta</th>'+
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
                              '<textarea class="form-control" name="observaciones" id="observaciones" rows="2" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
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
    $("#id").val(data.Asignacion_Herramienta.id);
    $("#serie").val(data.Asignacion_Herramienta.serie);
    $("#serietexto").html("Serie: "+data.Asignacion_Herramienta.serie);
    $("#numeropersonalrecibe").val(data.personalrecibe.id);
    $("#personalrecibe").val(data.personalrecibe.nombre);
    $("#numeropersonalentrega").val(data.personalentrega.id);
    $("#personalentrega").val(data.personalentrega.nombre);
    $("#fecha").val(data.fecha);
    $("#observaciones").val(data.Asignacion_Herramienta.observaciones);
    $("#total").val(data.total);
    //tabs precios productos
    $("#tablaherramientasasignadas").append(data.filasdetallesasignacion);
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    //se deben asignar los valores a los contadores para que las sumas resulten correctas
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    mostrarmodalformulario('MODIFICACION', data.modificacionpermitida);
    $('.page-loader-wrapper').css('display', 'none');
  }).fail( function() {
    msj_errorajax();
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
      url:asignacion_herramienta_guardar_modificacion,
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
  var columnastablafoliosencontrados =  '<tr>'+
                                          '<th><div style="width:80px !important;">Generar Documento en PDF</div></th>'+
                                          '<th>Asignación</th>'+
                                          '<th>Recibe</th>'+
                                          '<th>Entrega</th>'+
                                          '<th>Total</th>'+
                                        '</tr>';
  $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
  tabla=$('#tablafoliosencontrados').DataTable({
      "paging":   false,
      "ordering": false,
      "info":     false,
      "searching": false,
      order: [1, 'asc'],
      processing: true,
      serverSide: true,
      ajax: {
          url: asignacion_herramienta_buscar_id_string_like,
          data: function (d) {
              d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'asignacion', name: 'Orden' },
          { data: 'nombre_recibe_herramienta', name: 'nombre_recibe_herramienta', orderable: false, searchable: false },
          { data: 'nombre_entrega_herramienta', name: 'nombre_entrega_herramienta', orderable: false, searchable: false  },
          { data: 'total', name: 'total', orderable: false, searchable: false  },
      ],
  });
}
//configurar tabla
function configurar_tabla(){
  //formulario modificacion
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
                                  '<label>DATOS ASIGNACIÓN HERRAMIENTA</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="id" id="idid" class="filled-in datotabla" value="id" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idid">id</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="asignacion" id="idasignacion" class="filled-in datotabla" value="asignacion" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idasignacion">asignacion</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="serie" id="idserie" class="filled-in datotabla" value="serie" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idserie">serie</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="fecha" id="idfecha" class="filled-in datotabla" value="fecha" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idfecha">fecha</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="recibe_herramienta" id="idrecibe_herramienta" class="filled-in datotabla" value="recibe_herramienta" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idrecibe_herramienta">recibe_herramienta</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="entrega_herramienta" id="identrega_herramienta" class="filled-in datotabla" value="entrega_herramienta" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="identrega_herramienta">entrega_herramienta</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="total" id="idtotal" class="filled-in datotabla" value="total" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idtotal">total</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="observaciones" id="idobservaciones" class="filled-in datotabla" value="observaciones" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idobservaciones">observaciones</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="autorizado_por" id="idautorizado_por" class="filled-in datotabla" value="autorizado_por" onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idautorizado_por">autorizado_por</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+    
                                  '<input type="checkbox" name="fecha_autorizacion" id="idfecha_autorizacion" class="filled-in datotabla" value="fecha_autorizacion" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idfecha_autorizacion">fecha_autorizacion</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="status" id="idstatus" class="filled-in datotabla" value="status" onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                  '<label for="idstatus">status</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="motivo_baja" id="idmotivo_baja" class="filled-in datotabla" value="motivo_baja" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idmotivo_baja">motivo_baja</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="equipo" id="idequipo" class="filled-in datotabla" value="equipo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idequipo">equipo</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="usuario" id="idusuario" class="filled-in datotabla" value="usuario" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idusuario">usuario</label>'+
                              '</div>'+
                              '<div class="col-md-4 form-check">'+
                                  '<input type="checkbox" name="periodo" id="idperiodo" class="filled-in datotabla" value="periodo" onchange="construirarraydatostabla(this);" />'+
                                  '<label for="idperiodo">periodo</label>'+
                              '</div>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                              '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                          '</div>'+
                          '<div class="col-md-6">'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS PERSONAL RECIBE</label>'+
                              '</div>'+
                              '<div class="col-md-6 form-check">'+
                                  '<input type="checkbox" name="nombre_recibe_herramienta" id="idnombre_recibe_herramienta" class="filled-in datotabla" value="nombre_recibe_herramienta"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idnombre_recibe_herramienta">nombre_recibe_herramienta</label>'+  
                              '</div>'+
                              '<div class="col-md-6 form-check">'+  
                                  '<input type="checkbox" name="tipo_recibe_herramienta" id="idtipo_recibe_herramienta" class="filled-in datotabla" value="tipo_recibe_herramienta"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idtipo_recibe_herramienta">tipo_recibe_herramienta</label>'+ 
                              '</div>'+
                              '<div class="col-md-12 form-check">'+
                                  '<label>DATOS PERSONAL ENTREGA</label>'+
                              '</div>'+
                              '<div class="col-md-6 form-check">'+
                                  '<input type="checkbox" name="nombre_entrega_herramienta" id="idnombre_entrega_herramienta" class="filled-in datotabla" value="nombre_entrega_herramienta"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idnombre_entrega_herramienta">nombre_entrega_herramienta</label>'+ 
                              '</div>'+
                              '<div class="col-md-6 form-check">'+
                                  '<input type="checkbox" name="tipo_entrega_herramienta" id="idtipo_entrega_herramienta" class="filled-in datotabla" value="tipo_entrega_herramienta"  onchange="construirarraydatostabla(this);"/>'+
                                  '<label for="idtipo_entrega_herramienta">tipo_entrega_herramienta</label>'+ 
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
//generar excel personal para auditoria
function mostrarmodalgenerarexcelpersonal(){
  $.get(asignacion_herramienta_generar_excel_obtener_personal, function(data){
    $("#personalexcel").empty();
    $("#personalexcel").append("<option selected disabled hidden>Selecciona el personal</option>");
    $.each(data,function(key, registro) {
      $("#personalexcel").append('<option value='+registro.id+'>'+registro.nombre+' - '+registro.tipo_personal+'</option>');
    });
    //formulario auditar herramienta a personal
    var tabs =    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                      '<li role="presentation" class="active">'+
                          '<a href="#herramientasasignadastab" data-toggle="tab">Herramientas Asignadas</a>'+
                      '</li>'+
                  '</ul>'+
                  '<div class="tab-content">'+
                      '<div role="tabpanel" class="tab-pane fade in active" id="herramientasasignadastab">'+
                          '<div class="row">'+
                              '<div class="col-md-12 table-responsive">'+
                                  '<table id="tablaherramientasasignadas" class="table table-bordered tablaherramientasasignadas">'+
                                      '<thead class="customercolor">'+
                                          '<tr>'+
                                              '<th class="customercolor">Asignación</th>'+
                                              '<th class="customercolor">Herramienta</th>'+
                                              '<th class="customercolor"><div style="width:200px !important;">Descripción</div></th>'+
                                              '<th class="customercolor" >Unidad</th>'+
                                              '<th class="customercolor" >Cantidad Asignada</th>'+
                                              '<th class="customercolor" >Precio $</th>'+
                                              '<th class="customercolor">Total $</th>'+
                                              '<th class="customercolortheadth">Estado Auditoria</th>'+
                                              '<th class="customercolortheadth">Cantidad Actual Auditoria</th>'+
                                          '</tr>'+
                                      '</thead>'+
                                      '<tbody>'+           
                                      '</tbody>'+
                                  '</table>'+
                              '</div>'+
                              '<div class="col-md-12">'+
                                '<input type="hidden" name="numerofilasherramientaasignada" id="numerofilasherramientaasignada" class="form-control" required>'+
                              '</div>'+
                          '</div>'+   
                      '</div>'+ 
                  '</div>';
    $("#tabsformauditarherramientas").html(tabs);
    $("#modalgenerarexcelpersonal").modal('show');
  }) 
}
//cargar toda la herramienta asignada al personal
function herramientaasignadapersonal(){
  var idpersonal = $("#personalexcel").val();
  $.get(asignacion_herramienta_obtener_herramienta_personal, {idpersonal:idpersonal}, function(data){
    if(parseInt(data.contadorfilas) > 0){
      //tabs asignacion herramientas
      $("#tablaherramientasasignadas tbody").html(data.filasdetallesasignacion);
      //se deben asignar los valores a los contadores para que las sumas resulten correctas
      contadorfilas = data.contadorfilas;
      $("#numerofilasherramientaasignada").val(data.contadorfilas);
      $("#btnGuardarAuditoria").show();
      $("#btnGenerarReporteAuditoria").show();
      $("#btnGenerarReporteAuditoria").attr("href", data.urlgenerarreporteauditoria);
      $("#btnGenerarReporteGeneral").show();
      $("#btnGenerarReporteGeneral").attr("href", data.urlgenerarreportegeneral);
      if(parseInt(data.contadorasignacionessinautorizar) > 0){
        msj_infopersonalconasignacionesporautorizar(); 
        $("#btnGuardarAuditoria").hide();
        $("#btnGenerarReporteAuditoria").hide();
        $("#btnGenerarReporteAuditoria").attr("href", "");   
        $("#btnGenerarReporteGeneral").hide();
        $("#btnGenerarReporteGeneral").attr("href", "");   
      }
    }else if(parseInt(data.contadorasignacionessinautorizar) > 0){
      msj_infopersonalconasignacionesporautorizar();
    }else{
      msj_errorpersonalsinherramientaasignada();
      $("#tablaherramientasasignadas tbody").html("");
      $("#btnGuardarAuditoria").hide();
      $("#btnGenerarReporteAuditoria").hide();
      $("#btnGenerarReporteAuditoria").attr("href", "");
      $("#btnGenerarReporteGeneral").hide();
      $("#btnGenerarReporteGeneral").attr("href", "");
    }
  })  
}
//compara el estado de la auditoria
function compararestadoauditoria(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
     if(fila === cuentaFilas){
       // obtener los datos de la fila:
       var estadoauditoria = $(".estadoauditoria", this).val();
       var cantidadpartida = $(".cantidadpartida", this).val();
       if(estadoauditoria == "FALTANTE"){
          $(".cantidadauditoriapartida", this).val("0."+numerocerosconfigurados);
          $(".cantidadauditoriapartida",this).removeAttr('readonly');
          $(".cantidadauditoriapartida",this).attr('data-parsley-min', "0."+numerocerosconfiguradosinputnumberstep);
       }else{
          $(".cantidadauditoriapartida",this).attr('readonly', 'readonly');
          $(".cantidadauditoriapartida", this).val(cantidadpartida);
          $(".cantidadauditoriapartida",this).removeAttr('data-parsley-min');
       }
     }  
     cuentaFilas++;
  });
}
//guardar la auditoria del personal
$("#btnGuardarAuditoria").on('click', function (e) {
  e.preventDefault();
  var formData = new FormData($("#formauditarherramienta")[0]);
  var form = $("#formauditarherramienta");
  if (form.parsley().isValid()){
    $('.page-loader-wrapper').css('display', 'block');
    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:asignacion_herramienta_guardar_auditoria,
      type: "post",
      dataType: "html",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success:function(data){
        msj_datosguardadoscorrectamente();
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