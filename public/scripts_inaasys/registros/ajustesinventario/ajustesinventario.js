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
    var hoy = fechahoy.getFullYear()+"-"+(mes)+"-"+(dia);
    $('#fecha').val(hoy);
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
  $.get(ajustesinventario_obtener_ultimo_id, function(folio){
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
        url: ajustesinventario_obtener,
        data: function (d) {
            d.periodo = $("#periodo").val();
        }
    },
    "createdRow": function( row, data, dataIndex){
        if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
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
//obtener registros de remisiones
function obteneralmacenes(){
  ocultarformulario();
  var tablaalmacenes = '<div class="modal-header bg-red">'+
                            '<h4 class="modal-title">Almacenes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="customercolor">'+
                                                '<tr>'+
                                                    '<th>Operaciones</th>'+
                                                    '<th>Numero</th>'+
                                                    '<th>Almacen</th>'+
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
            url: ajustesinventario_obtener_almacenes,
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
        
    }); 
} 
//obtener datos de remision seleccionada
function seleccionaralmacen(Numero, Nombre){
  $("#numeroalmacen").val(Numero);
  $("#almacen").val(Nombre);
  mostrarformulario();
  var tipooperacion = $("#tipooperacion").val();
  if(tipooperacion == 'alta'){
    $("#divbuscarcodigoproducto").show();
  }
  //si se cambia el almacen cambiar datos en las filas
  var numerofilas = $("#numerofilas").val();
  if(parseInt(numerofilas) > parseInt(0)){
    cargarnuevosdatosenfilas();
  }
}
function cargarnuevosdatosenfilas(){
  var numeroalmacen = $("#numeroalmacen").val();
  var serie = $("#serie").val();
  var folio = $("#folio").val();
  var fila = 0;
  $("tr.filasproductos").each(function () { 
      // obtener los datos de la fila:
      var codigopartida = $(".codigoproductopartida", this).val();
      var entradaspartida = $(".entradaspartida", this).val();
      var salidaspartida = $(".salidaspartida", this).val();
      obtenernuevosdatosfila(numeroalmacen, codigopartida, folio, serie, fila, entradaspartida, salidaspartida).then(nuevosdatosfila=>{
        console.log(nuevosdatosfila);
        $(".existenciaactualpartida", this).val(nuevosdatosfila.nuevaexistencia);
        $(".existencianuevapartida", this).val(nuevosdatosfila.existencianuevapartida);
      })
      fila++;
  });  
}
//funcion asincrona para buscar existencias de la partida
function obtenernuevosdatosfila(numeroalmacen, codigopartida, folio, serie, fila, entradaspartida, salidaspartida){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(ajustesinventario_obtener_nuevos_datos_fila,{'numeroalmacen':numeroalmacen,'codigopartida':codigopartida,'folio':folio,'serie':serie,'fila':fila,'entradaspartida':entradaspartida,'salidaspartida':salidaspartida},nuevosdatosfila=>{
        return ejecuta(nuevosdatosfila);
      })
    },500);
  })
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
                                  '<table id="tbllistadoproducto" class="tbllistadoproducto table table-bordered table-striped table-hover" style="width:100% !important">'+
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
    $("#contenidomodaltablas").html(tablaproductos);
    $('#tbllistadoproducto').DataTable({
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
        url: ajustesinventario_obtener_productos,
        data: function (d) {
          d.codigoabuscar = $("#codigoabuscar").val();
          d.tipooperacion = $("#tipooperacion").val();
          d.numeroalmacen = $("#numeroalmacen").val();
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
            $('#tbllistadoproducto').DataTable().search( this.value ).draw();
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
//calcular total de cada fila
function calculartotalesfilas(fila){
  // for each por cada fila:
  var cuentaFilas = 0;
  $("tr.filasproductos").each(function () { 
    if(fila === cuentaFilas){
      // obtener los datos de la fila:
      var cantidadpartida = $(".cantidadpartida", this).val();
      var preciopartida = $('.preciopartida', this).val();
      var importepartida = $('.importepartida', this).val();
      //importe de la partida
      importepartida =  new Decimal(cantidadpartida).times(preciopartida);
      $('.importepartida', this).val(number_format(round(importepartida, numerodecimales), numerodecimales, '.', ''));
      calculartotales();
    }  
    cuentaFilas++;
  });
}
//calculo subtotal entradas
function calcularsubtotalentradas(fila){
    $("#filaproducto"+fila+" .salidaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    $("#filaproducto"+fila+" .subtotalsalidaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    $("#filaproducto"+fila+" .subtotalentradaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    var subtotalentradaspartida = 0;
    var entradaspartida = $("#filaproducto"+fila+" .entradaspartida").val();
    var costopartida = $("#filaproducto"+fila+" .costopartida").val();
    subtotalentradaspartida= new Decimal(entradaspartida).times(costopartida);
    $("#filaproducto"+fila+" .subtotalentradaspartida").val(number_format(round(subtotalentradaspartida, numerodecimales), numerodecimales, '.', ''));
    calculartotales();
    $("#formparsley").parsley().validate();
}
//calculo subtotal salidas
function calcularsubtotalsalidas(fila){
    $("#filaproducto"+fila+" .entradaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    $("#filaproducto"+fila+" .subtotalsalidaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    $("#filaproducto"+fila+" .subtotalentradaspartida").val(number_format(round(0, numerodecimales), numerodecimales, '.', ''));
    var subtotalsalidaspartida = 0;
    var salidaspartida = $("#filaproducto"+fila+" .salidaspartida").val();
    var costopartida = $("#filaproducto"+fila+" .costopartida").val();
    subtotalsalidaspartida= new Decimal(salidaspartida).times(costopartida);
    $("#filaproducto"+fila+" .subtotalsalidaspartida").val(number_format(round(subtotalsalidaspartida, numerodecimales), numerodecimales, '.', ''));
    calculartotales();
    $("#formparsley").parsley().validate();
}
//cambio en costo
function cambiocosto(fila){
  var entradaspartida = $("#filaproducto"+fila+" .entradaspartida").val();
  var salidaspartida = $("#filaproducto"+fila+" .salidaspartida").val();
  if(parseFloat(entradaspartida) > parseFloat(0)){
    calcularsubtotalentradas(fila);
  }
  if(parseFloat(salidaspartida) > parseFloat(0)){
    calcularsubtotalsalidas(fila);
  }
}
//calcular existencia nueva
function calcularexistencianueva(fila){
  var existencianuevapartida = 0;
  var existenciaactualpartida = $("#filaproducto"+fila+" .existenciaactualpartida").val();
  var entradaspartida = $("#filaproducto"+fila+" .entradaspartida").val();
  var salidaspartida = $("#filaproducto"+fila+" .salidaspartida").val();
  var entradaspartidadb = $("#filaproducto"+fila+" .entradaspartidadb").val();
  var salidaspartidadb = $("#filaproducto"+fila+" .salidaspartidadb").val();
  //existencianuevapartida = new Decimal(existenciaactualpartida).plus(entradaspartida).minus(salidaspartida).plus(salidaspartidadb).minus(entradaspartidadb);
  existencianuevapartida = new Decimal(existenciaactualpartida).plus(entradaspartida).minus(salidaspartida);
  $("#filaproducto"+fila+" .existencianuevapartida").val(number_format(round(existencianuevapartida, numerodecimales), numerodecimales, '.', ''));
}
//calcular totales
function calculartotales(){
    var subtotalsalidas= 0;
    var subtotalentradas = 0;
    var total = 0;
    $("tr.filasproductos").each(function(){
        subtotalsalidas= new Decimal(subtotalsalidas).plus($(".subtotalsalidaspartida", this).val());
        subtotalentradas= new Decimal(subtotalentradas).plus($(".subtotalentradaspartida", this).val());
    }); 
    //total
    total = new Decimal(subtotalentradas).minus(subtotalsalidas);
    $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, tipooperacion){
  $('.page-loader-wrapper').css('display', 'block');
  var result = evaluarproductoexistente(Codigo);
  var folio = $("#folio").val();
  var serie = $("#serie").val();
  var numeroalmacen = $("#numeroalmacen").val();
  if(result == false){
    $.get(ajustesinventario_obtener_existencias_partida, {almacen:numeroalmacen,codigopartida:Codigo,folio:folio,serie:serie}, function(Existencias){
      var tipo = "alta";
      var fila=   '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
                          '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodmd codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]"></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'+Producto+'" readonly data-parsley-length="[1, 255]"></td>'+
                          '<td class="tdmod"><input type="text" class="form-control divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'+Unidad+'" readonly data-parsley-length="[1, 5]"></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm existenciaactualpartida" name="existenciaactualpartida[]" id="existenciaactualpartida[]" value="'+Existencias+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod">'+
                            '<input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm entradaspartidadb" name="entradaspartidadb[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly>'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm entradaspartida" name="entradaspartida[]" id="entradaspartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" onchange="formatocorrectoinputcantidades(this);calcularsubtotalentradas('+contadorfilas+');calcularexistencianueva('+contadorfilas+');colocardataparsleyminentradas('+contadorfilas+');">'+
                          '</td>'+
                          '<td class="tdmod">'+
                            '<input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm salidaspartidadb" name="salidaspartidadb[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/"  readonly>'+
                            '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm salidaspartida" name="salidaspartida[]" id="salidaspartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-existencias="'+Existencias+'"	 onchange="formatocorrectoinputcantidades(this);calcularsubtotalsalidas('+contadorfilas+');calcularexistencianueva('+contadorfilas+');revisarexistenciasalmacen('+contadorfilas+');colocardataparsleyminsalidas('+contadorfilas+');">'+
                          '</td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm existencianuevapartida" name="existencianuevapartida[]" id="existencianuevapartida[]" value="'+Existencias+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costopartida" name="costopartida[]" id="costopartida[]" value="'+Costo+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);cambiocosto('+contadorfilas+');"></td>'+
                          '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalentradaspartida" name="subtotalentradaspartida[]" id="subtotalentradaspartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                          '<td class="tdmod" hidden><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm subtotalsalidaspartida" name="subtotalsalidaspartida[]" id="subtotalsalidaspartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
                      '</tr>';
      contadorproductos++;
      contadorfilas++;
      $("#tablaproductosajuste").append(fila);
      mostrarformulario();
      comprobarfilas();
      calculartotales();
      $('.page-loader-wrapper').css('display', 'none');
    }) 
  }else{
    msj_errorproductoyaagregado();
    $('.page-loader-wrapper').css('display', 'none');
  }  
}
//eliminar una fila en la tabla de precios clientes
function eliminarfila(numerofila){
  var confirmacion = confirm("Esta seguro de eliminar la fila?"); 
  if (confirmacion == true) { 
    $("#filaproducto"+numerofila).remove();
    contadorfilas--;
    contadorproductos--;
    comprobarfilas();
    renumerarfilas();//importante para todos los calculo en el modulo de orden de compra 
    calculartotales();
  }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
  var numerofilas = $("#tablaproductosajuste tbody tr").length;
  $("#numerofilas").val(numerofilas);
}
//renumerar las filas de la orden de compra
function renumerarfilas(){
  var lista;
  //renumerar la cantidad de la partida
  lista = document.getElementsByClassName("entradaspartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calcularsubtotalentradas("+i+');calcularexistencianueva('+i+');colocardataparsleyminentradas('+i+')');
  }
  //renumero el precio de la partida
  lista = document.getElementsByClassName("salidaspartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);calcularsubtotalsalidas("+i+');calcularexistencianueva('+i+');revisarexistenciasalmacen('+i+');colocardataparsleyminsalidas('+i+')');
  }
  //renumero el costo de la partida
  lista = document.getElementsByClassName("costopartida");
  for (var i = 0; i < lista.length; i++) {
    lista[i].setAttribute("onchange", "formatocorrectoinputcantidades(this);cambiocosto("+i+')');
  }
}  
//revisar si hay existencias de la partida en el almacen
function revisarexistenciasalmacen(fila){
  var folio = $("#folio").val();
  var serie = $("#serie").val();
  var almacen = $("#numeroalmacen").val();
  var codigopartida = $("#filaproducto"+fila+" .codigoproductopartida").val();
  comprobarexistenciaspartida(almacen, codigopartida, folio, serie).then(nuevaexistencia=>{
    $("#filaproducto"+fila+" .salidaspartida").attr('data-parsley-existencias',nuevaexistencia);
    $("#filaproducto"+fila+" .salidaspartida").parsley().validate();
    $("#filaproducto"+fila+" .existencianuevapartida").parsley().validate();
  })
}
//funcion asincrona para buscar existencias de la partida
function comprobarexistenciaspartida(almacen, codigopartida, folio, serie){
  return new Promise((ejecuta)=>{
    setTimeout(function(){ 
      $.get(ajustesinventario_obtener_existencias_partida,{'almacen':almacen,'codigopartida':codigopartida,'folio':folio,'serie':serie},nuevaexistencia=>{
        return ejecuta(nuevaexistencia);
      })
    },500);
  })
}
function colocardataparsleyminentradas(fila){
  $("#filaproducto"+fila+" .entradaspartida ").attr('data-parsley-min', '0.'+numerocerosconfiguradosinputnumberstep);
  $("#filaproducto"+fila+" .salidaspartida").removeAttr('data-parsley-min');
  $("#filaproducto"+fila+" .salidaspartida").parsley().validate();
}
//validar que se ingrese al menos una entrada o salida por fila
function colocardataparsleyminsalidas(fila){
  $("#filaproducto"+fila+" .salidaspartida ").attr('data-parsley-min', '0.'+numerocerosconfiguradosinputnumberstep);
  $("#filaproducto"+fila+" .entradaspartida").removeAttr('data-parsley-min');
  $("#filaproducto"+fila+" .entradaspartida").parsley().validate();
}
//alta
function alta(){
  $("#titulomodal").html('Alta Ajuste Inventario');
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
                            '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<table id="tablaproductosajuste" class="table table-bordered tablaproductosajuste">'+
                                    '<thead class="customercolor">'+
                                        '<tr>'+
                                          '<th class="customercolor">#</th>'+
                                          '<th class="customercolor">Código</th>'+
                                          '<th class="customercolor"><div style="width:200px !important;">Producto</div></th>'+
                                          '<th class="customercolor">Unidad</th>'+
                                          '<th class="customercolor">Existencia Actual</th>'+
                                          '<th class="customercolortheadth">Entradas</th>'+
                                          '<th class="customercolortheadth">Salidas</th>'+
                                          '<th class="customercolor">Existencia Nueva</th>'+
                                          '<th class="customercolor">Costo $</th>'+
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
                            '<textarea class="form-control" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                          '</div>'+ 
                          '<div class="col-md-3 col-md-offset-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td class="tdmod">Total</td>'+
                                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" required readonly></td>'+
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
  $("#ModalAlta").modal('show');
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
  e.preventDefault();
  var numerofilas = $("#numerofilas").val();
  if(numerofilas > 0){
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:ajustesinventario_guardar,
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
  }else{
    msj_erroralmenosunaentrada();
  }
});
//bajas
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(ajustedesactivar){
  $.get(ajustesinventario_verificar_baja,{ajustedesactivar:ajustedesactivar}, function(data){
    if(data.Status == 'BAJA'){
      $("#ajustedesactivar").val(0);
      $("#textomodaldesactivar").html('Error, este ajuste de inventario ya fue dado de baja');
      $("#divmotivobaja").hide();
      $("#btnbaja").hide();
      $('#estatusregistro').modal('show');
    }else{ 
      if(data.resultadofechas != ''){
        $("#ajustedesactivar").val(0);
        $("#textomodaldesactivar").html('Error solo se pueden dar de baja los ajustes del mes actual, fecha del ajuste: ' + data.resultadofechas);
        $("#divmotivobaja").hide();
        $("#btnbaja").hide();
        $('#estatusregistro').modal('show');
      }else{
        if(data.errores != ''){
          $("#ajustedesactivar").val(0);
          $("#textomodaldesactivar").html(data.errores);
          $("#divmotivobaja").hide();
          $("#btnbaja").hide();
          $('#estatusregistro').modal('show');
        }else{
          $("#ajustedesactivar").val(ajustedesactivar);
          $("#textomodaldesactivar").html('Estas seguro de cambiar el estado el registro?');
          $("#divmotivobaja").show();
          $("#btnbaja").show();
          $('#estatusregistro').modal('show');
        }
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
      url:ajustesinventario_alta_o_baja,
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
//modificacion
function obtenerdatos(ajustemodificar){
  $("#titulomodal").html('Modificación Ajuste Inventario');
  $('.page-loader-wrapper').css('display', 'block');
  $.get(ajustesinventario_obtener_ajuste,{ajustemodificar:ajustemodificar },function(data){
    //formulario modificacion
    var tabs ='<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                  '<li role="presentation" class="active">'+
                      '<a href="#productostab" data-toggle="tab">Productos</a>'+
                  '</li>'+
              '</ul>'+
              '<div class="tab-content">'+
                  '<div role="tabpanel" class="tab-pane fade in active" id="productostab">'+
                      '<div class="row">'+
                          '<div class="col-md-12 table-responsive cabecerafija" style="height: 300px;overflow-y: scroll;padding: 0px 0px;">'+
                              '<table id="tablaproductosajuste" class="table table-bordered tablaproductosajuste">'+
                                  '<thead class="customercolor">'+
                                      '<tr>'+
                                        '<th class="customercolor">#</th>'+
                                        '<th class="customercolor">Código</th>'+
                                        '<th class="customercolor"><div style="width:200px !important;">Producto</div></th>'+
                                        '<th class="customercolor">Unidad</th>'+
                                        '<th class="customercolor">Existencia Actual</th>'+
                                        '<th class="customercolortheadth">Entradas</th>'+
                                        '<th class="customercolortheadth">Salidas</th>'+
                                        '<th class="customercolor">Existencia Nueva</th>'+
                                        '<th class="customercolor">Costo $</th>'+
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
                          '<textarea class="form-control" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]"></textarea>'+
                        '</div>'+ 
                        '<div class="col-md-3 col-md-offset-3">'+
                              '<table class="table table-striped table-hover">'+
                                  '<tr>'+
                                      '<td class="tdmod">Total</td>'+
                                      '<td class="tdmod"><input type="text" class="form-control divorinputmodmd" name="total" id="total" value="0.'+numerocerosconfigurados+'" required readonly></td>'+
                                  '</tr>'+
                              '</table>'+
                          '</div>'+
                      '</div>'+   
                  '</div>'+ 
              '</div>';
    $("#tabsform").html(tabs);
    $("#folio").val(data.ajuste.Folio);
    $("#serie").val(data.ajuste.Serie);
    $("#serietexto").html("Serie: "+data.ajuste.Serie);
    $("#fecha").val(data.fecha);
    $("#almacen").val(data.almacen.Nombre);
    $("#numeroalmacen").val(data.almacen.Numero);
    $("#almacendb").val(data.almacen.Nombre);
    $("#numeroalmacendb").val(data.almacen.Numero);
    $("#observaciones").val(data.ajuste.Obs);
    $("#total").val(data.total);
    //detalles
    $("#tablaproductosajuste tbody").html(data.filasdetallesajuste);
    $("#numerofilas").val(data.numerodetallesajuste);
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
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
      url:ajustesinventario_guardar_modificacion,
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
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
  $.get(ajustesinventario_obtener_datos_envio_email,{documento:documento}, function(data){
    $("#textomodalenviarpdfemail").html("Enviar email Ajuste de Inventario No." + documento);
    $("#emaildocumento").val(documento);
    $("#emailde").val(data.emailde);
    $("#emailasunto").val("AJUSTE DE INVENTARIO NO. " + documento +" DE USADOS TRACTOCAMIONES Y PARTES REFACCIONARIAS SA DE CV");
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
      url:ajustesinventario_enviar_pdfs_email,
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
                                          '<th>Ajuste</th>'+
                                          '<th>Total</th>'+
                                          '<th>Status</th>'+
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
          url: ajustesinventario_buscar_folio_string_like,
          data: function (d) {
              d.string = $("#buscarfolio").val();
          },
      },
      columns: [
          { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false },
          { data: 'Ajuste', name: 'Ajuste' },
          { data: 'Total', name: 'Total', orderable: false, searchable: false  },
          { data: 'Status', name: 'Status', orderable: false, searchable: false  },
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
                                    '<label>DATOS COTIZACIÓN</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Ajuste" id="idAjuste" class="filled-in datotabla" value="Ajuste" readonly onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                    '<label for="idAjuste">Ajuste</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Serie" id="idSerie" class="filled-in datotabla" value="Serie" readonly onchange="construirarraydatostabla(this);" />'+
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
                                    '<input type="checkbox" name="Obs" id="idObs" class="filled-in datotabla" value="Obs" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idObs">Obs</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Almacen" id="idAlmacen" class="filled-in datotabla" value="Almacen" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idAlmacen">Almacen</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Total" id="idTotal" class="filled-in datotabla" value="Total" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idTotal">Total</label>'+
                                '</div>'+
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                    '<label for="idStatus">Status</label>'+
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
                                '<div class="col-md-4 form-check">'+
                                    '<input type="checkbox" name="Periodo" id="idPeriodo" class="filled-in datotabla" value="Periodo" onchange="construirarraydatostabla(this);" />'+
                                    '<label for="idPeriodo">Periodo</label>'+
                                '</div>'+
                                '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                                '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                            '</div>'+
                            '<div class="col-md-6">'+
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