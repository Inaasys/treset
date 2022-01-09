'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    campos_a_filtrar_en_busquedas();
    listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 1000));
}
function asignarfechaactual(){
  $.get(ordenes_compra_obtener_fecha_actual_datetimelocal, function(fechas){
    $("#fecha").val(fechas.fecha).attr('min', fechas.fechamin).attr('max', fechas.fechamax);
  }) 
}
//obtener el ultimo id de la tabla
function obtenultimonumero(){
    var serie = $("#serie").val();
    $.get(produccion_obtener_ultimo_folio,{serie:serie}, function(folio){
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
    var tabla = $('.tbllistado').DataTable();
    tabla.ajax.reload();
    cambiarurlexportarexcel();
}
//listar todos los registros de la tabla
function listar(){
    cambiarurlexportarexcel();
    //Campos ordenados a mostras
    var campos = columnas_ordenadas.split(",");
    var campos_busqueda = campos_busquedas.split(",");
    //agregar inputs de busqueda por columna
    $('#tbllistado tfoot th').each( function () {
      var titulocolumnatfoot = $(this).text();
      var valor_encontrado_en_array = campos_busqueda.indexOf(titulocolumnatfoot); 
      if(valor_encontrado_en_array >= 0){
        $(this).html( '<input type="text" placeholder="Buscar en columna '+titulocolumnatfoot+'" />' );
      }
    });
    // armar columas para datatable se arma desde funcionesglobales.js
    var campos_tabla = armar_columas_datatable(campos,campos_busqueda);
    tabla=$('#tbllistado').DataTable({
        "lengthMenu": [ 100, 250, 500, 1000 ],
        "pageLength": 100,
        "sScrollX": "110%",
        "sScrollY": "350px",
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: {
            url: produccion_obtener,
            data: function (d) {
                d.periodo = $("#periodo").val();
            }
        },
        "createdRow": function( row, data, dataIndex){
            if( data.Status ==  `BAJA`){ $(row).addClass('bg-orange');}
            else if( data.Status ==  `PRODUCIDO`){ $(row).addClass('bg-green');}
        },
        columns: campos_tabla,
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
              }
          });
        }
    });
    //modificacion al dar doble click
    $('#tbllistado tbody').on('dblclick', 'tr', function () {
      var data = tabla.row( this ).data();
      obtenerdatos(data.Produccion);
    });
}
//obtener series documento
function obtenerseriesdocumento(){
    ocultarformulario();
    var seriedefault = 'A';
    var tablaseriesdocumento=   '<div class="modal-header '+background_forms_and_modals+'">'+
                                    '<h4 class="modal-title">Series Documento &nbsp;&nbsp; <div class="btn bg-green btn-xs waves-effect" onclick="seleccionarseriedocumento(\''+seriedefault+'\')">Asignar Serie Default (A)</div></h4>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<div class="table-responsive">'+
                                        '<table id="tbllistadoseriedocumento" class="tbllistadoseriedocumento table table-bordered table-striped table-hover" style="width:100% !important;">'+
                                            '<thead class="'+background_tables+'">'+
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
    var tserdoc = $('#tbllistadoseriedocumento').DataTable({
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
          url: produccion_obtener_series_documento
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
    //seleccionar registro al dar doble click
    $('#tbllistadoseriedocumento tbody').on('dblclick', 'tr', function () {
      var data = tserdoc.row( this ).data();
      seleccionarseriedocumento(data.Serie);
    }); 
}
function seleccionarseriedocumento(Serie){
    $.get(produccion_obtener_ultimo_folio_serie_seleccionada, {Serie:Serie}, function(folio){
        $("#folio").val(folio);
        $("#serie").val(Serie);
        $("#serietexto").html("Serie: "+Serie);
        mostrarformulario();
    }) 
}
//obtener clientes
function obtenerclientes(){
    ocultarformulario();
    var tablaclientes = '<div class="modal-header '+background_forms_and_modals+'">'+
                              '<h4 class="modal-title">Clientes</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                              '<div class="row">'+
                                  '<div class="col-md-12">'+
                                      '<div class="table-responsive">'+
                                          '<table id="tbllistadocliente" class="tbllistadocliente table table-bordered table-striped table-hover" style="width:100% !important">'+
                                              '<thead class="'+background_tables+'">'+
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
        $("#contenidomodaltablas").html(tablaclientes);
        var tcli = $('#tbllistadocliente').DataTable({
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
                url: produccion_obtener_clientes
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
                    $('#tbllistadocliente').DataTable().search( this.value ).draw();
                    }
                });
            },
        }); 
        //seleccionar registro al dar doble click
        $('#tbllistadocliente tbody').on('dblclick', 'tr', function () {
            var data = tcli.row( this ).data();
            seleccionarcliente(data.Numero, data.Nombre, data.Credito, data.Saldo, data.NumeroAgente, data.NombreAgente);
        }); 
} 
//obtener datos de remision seleccionada
function seleccionarcliente(Numero, Nombre, Credito, Saldo, NumeroAgente, Agente){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = Numero;
    if(numeroclienteanterior != numerocliente){
        $("#numerocliente").val(Numero);
        $("#numeroclienteanterior").val(Numero);
        $("#cliente").val(Nombre);
        if(Nombre != null){
            $("#textonombrecliente").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
        calculartotal();//para calcular nuevo saldo
    }
}
//obtener almacenes
function obteneralmacenes(){
    ocultarformulario();
    var tablaalmacenes ='<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Almacenes</h4>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<div class="row">'+
                                '<div class="col-md-12">'+
                                    '<div class="table-responsive">'+
                                        '<table id="tbllistadoalmacen" class="tbllistadoalmacen table table-bordered table-striped table-hover" style="width:100% !important">'+
                                            '<thead class="'+background_tables+'">'+
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
    var talm = $('#tbllistadoalmacen').DataTable({
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
            url: produccion_obtener_almacenes,
            data: function (d) {
                d.numeroalmacena = $("#numeroalmacena").val();
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
                $('#tbllistadoalmacen').DataTable().search( this.value ).draw();
                }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadoalmacen tbody').on('dblclick', 'tr', function () {
        var data = talm.row( this ).data();
        seleccionaralmacen(data.Numero, data.Nombre);
    }); 
} 
//obtener datos de remision seleccionada
function seleccionaralmacen(Numero, Nombre){
    var numeroalmacenanterior = $("#numeroalmacenanterior").val();
    var numeroalmacen = Numero;
    if(numeroalmacenanterior != numeroalmacen){
        $("#numeroalmacen").val(Numero);
        $("#numeroalmacenanterior").val(Numero);
        $("#almacen").val(Nombre);
        if(Nombre != null){
            $("#textonombrealmacen").html(Nombre.substring(0, 40));
        }
        mostrarformulario();
        //recargar existencias actuales del nuevo almacen seleccionado en data-parsley-existencias de las partidas
        $("tr.filasproductos").each(function () {
        $('.cantidadpartida', this).change();
        });
    }
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
//obtener por numero
function obtenerclientepornumero(){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    var numerocliente = $("#numerocliente").val();
    if(numeroclienteanterior != numerocliente){
        if($("#numerocliente").parsley().isValid()){
            $.get(produccion_obtener_cliente_por_numero, {numerocliente:numerocliente}, function(data){
                $("#numerocliente").val(data.numero);
                $("#numeroclienteanterior").val(data.numero);
                $("#cliente").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrecliente").html(data.nombre.substring(0, 40));
                }               
                mostrarformulario();
                calculartotal();//para obtener nuevo saldo
            }) 
        }
    }
}
//regresar numero
function regresarnumerocliente(){
    var numeroclienteanterior = $("#numeroclienteanterior").val();
    $("#numerocliente").val(numeroclienteanterior);
}
//obtener por numero
function obteneralmacenpornumero(){
    var numeroalmacenanterior = $("#numeroalmacenanterior").val();
    var numeroalmacen = $("#numeroalmacen").val();
    if(numeroalmacenanterior != numeroalmacen){
        if($("#numeroalmacen").parsley().isValid()){
            $.get(produccion_obtener_almacen_por_numero, {numeroalmacen:numeroalmacen}, function(data){
                $("#numeroalmacen").val(data.numero);
                $("#numeroalmacenanterior").val(data.numero);
                $("#almacen").val(data.nombre);
                if(data.nombre != null){
                    $("#textonombrealmacen").html(data.nombre.substring(0, 40));
                }
                mostrarformulario();
                //recargar existencias actuales del nuevo almacen seleccionado en data-parsley-existencias de las partidas
                $("tr.filasproductos").each(function () {
                $('.cantidadpartida', this).change();
                });
            }) 
        }
    }
}
//regresar numero
function regresarnumeroalmacen(){
    var numeroalmacenanterior = $("#numeroalmacenanterior").val();
    $("#numeroalmacen").val(numeroalmacenanterior);
}
//listar productos para tab consumos
function listarproductos(){
    ocultarformulario();
    var tablaproductos = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Productos PT</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                            '<div class="row">'+
                              '<div class="col-md-12">'+
                                '<div class="table-responsive">'+
                                  '<table id="tbllistadoproducto" class="tbllistadoproducto table table-bordered table-striped table-hover" style="width:100% !important">'+
                                    '<thead class="'+background_tables+'">'+
                                      '<tr>'+
                                        '<th>Operaciones</th>'+
                                        '<th>Código</th>'+
                                        '<th>Marca</th>'+
                                        '<th>Producto</th>'+
                                        '<th>Ubicación</th>'+
                                        '<th>Existencias</th>'+
                                        '<th>Almacen</th>'+
                                        '<th>Pt</th>'+
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
    var tprod = $('#tbllistadoproducto').DataTable({
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
            url: produccion_obtener_productos,
            data: function (d) {
            d.codigoabuscar = $("#codigoabuscar").val();
            d.numeroalmacen = $("#numeroalmacen").val();
            d.tipooperacion = $("#tipooperacion").val();
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
            { data: 'Codigo', name: 'Codigo' },
            { data: 'Marca', name: 'Marca', orderable: false, searchable: false  },
            { data: 'Producto', name: 'Producto' },
            { data: 'Ubicacion', name: 'Ubicacion', orderable: false, searchable: false  },
            { data: 'Existencias', name: 'Existencias', orderable: false, searchable: false  },
            { data: 'Almacen', name: 'Almacen', orderable: false, searchable: false  },
            { data: 'Pt', name: 'Pt', orderable: false, searchable: false  },
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
    //seleccionar registro al dar doble click
    $('#tbllistadoproducto tbody').on('dblclick', 'tr', function () {
        var data = tprod.row( this ).data();
        seleccionarproducto(data.Codigo);
    });
}
//seleccionar pt
function seleccionarproducto(Codigo){
    var numeroalmacen = $("#numeroalmacen").val();
    var tipooperacion = $("#tipooperacion").val();
    $.get(produccion_obtener_producto_por_codigo,{codigoabuscar:Codigo,numeroalmacen:numeroalmacen,tipooperacion:tipooperacion}, function(data){
        if(parseInt(data.contarproductos) > 0){
            $("#tablaproductosremisiones tbody").html(data.filasdetallesproduccion);
            mostrarformulario();      
            comprobarfilas();
            calculartotalesfilas();
            //colocar valores a contadores
            contadorproductos = data.contadorproductos;
            contadorfilas = data.contadorfilas;
            if(data.productoterminado.Producto != null){
                $("#textopt").html(data.productoterminado.Producto.substring(0, 70));
            }
            $("#codigoabuscar").val(data.productoterminado.Codigo);
            $("#costo").val(data.productoterminado.Costo);
            $('.page-loader-wrapper').css('display', 'none');
        }else{
          msjnoseencontroningunproducto();
        }
        //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
        $(".inputnextdet").keypress(function (e) {
          //recomentable para mayor compatibilidad entre navegadores.
          var code = (e.keyCode ? e.keyCode : e.which);
          if(code==13){
            var index = $(this).index(".inputnextdet");          
            $(".inputnextdet").eq(index + 1).focus().select(); 
          }
        });
    }) 
}
//obtener pt por codigo
function obtenerproductoporcodigo(){
  var codigoabuscar = $("#codigoabuscar").val();
  var numeroalmacen = $("#numeroalmacen").val();
  var tipooperacion = $("#tipooperacion").val();
  $.get(produccion_obtener_producto_por_codigo,{codigoabuscar:codigoabuscar,numeroalmacen:numeroalmacen,tipooperacion:tipooperacion}, function(data){
    if(parseInt(data.contarproductos) > 0){
        $("#tablaproductosremisiones tbody").html(data.filasdetallesproduccion);
        mostrarformulario();      
        comprobarfilas();
        calculartotalesfilas();
        //colocar valores a contadores
        contadorproductos = data.contadorproductos;
        contadorfilas = data.contadorfilas;
        if(data.productoterminado.Producto != null){
            $("#textopt").html(data.productoterminado.Producto.substring(0, 70));
        }           
        $("#costo").val(data.productoterminado.Costo);
        $('.page-loader-wrapper').css('display', 'none');
        //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
        $(".inputnextdet").keypress(function (e) {
          //recomentable para mayor compatibilidad entre navegadores.
          var code = (e.keyCode ? e.keyCode : e.which);
          if(code==13){
            var index = $(this).index(".inputnextdet");          
            $(".inputnextdet").eq(index + 1).focus().select(); 
          }
        });
    }else{
      msjnoseencontroningunproducto();
    }
  }) 
}
//listar productos insumos para pt
function listarinsumospt(){
    ocultarformulario();
    var tablaproductos = '<div class="modal-header '+background_forms_and_modals+'">'+
                            '<h4 class="modal-title">Productos Insumos PT</h4>'+
                          '</div>'+
                          '<div class="modal-body">'+
                            '<div class="row">'+
                              '<div class="col-md-12">'+
                                '<div class="table-responsive">'+
                                  '<table id="tbllistadoproductoinsumo" class="tbllistadoproductoinsumo table table-bordered table-striped table-hover" style="width:100% !important">'+
                                    '<thead class="'+background_tables+'">'+
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
    var tinsupt = $('#tbllistadoproductoinsumo').DataTable({
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
            url: produccion_obtener_productos_insumos_pt,
            data: function (d) {
                d.codigoabuscarinsumo = $("#codigoabuscarinsumo").val();
                d.numeroalmacen = $("#numeroalmacen").val();
                d.tipooperacion = $("#tipooperacion").val();
            }
        },
        columns: [
            { data: 'operaciones', name: 'operaciones', orderable: false, searchable: false  },
            { data: 'Codigo', name: 'Codigo' },
            { data: 'Marca', name: 'Marca', orderable: false, searchable: false  },
            { data: 'Producto', name: 'Producto' },
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
                $('#tbllistadoproductoinsumo').DataTable().search( this.value ).draw();
            }
            });
        },
    }); 
    //seleccionar registro al dar doble click
    $('#tbllistadoproductoinsumo tbody').on('dblclick', 'tr', function () {
        var data = tinsupt.row( this ).data();
        var tipooperacion = $("#tipooperacion").val();
        agregarfilaproducto(data.Codigo, data.Producto, data.Unidad, data.Costo, number_format(round(data.Impuesto, numerodecimales), numerodecimales, '.', ''), data.SubTotal, data.Existencias, tipooperacion, data.Insumo, data.ClaveProducto, data.ClaveUnidad, number_format(round(data.CostoDeLista, numerodecimales), numerodecimales, '.', ''));
    });
}
//obtener insumo pt por codigo
function obtenerproductoinsumoptporcodigo(){
    var codigoabuscarinsumo = $("#codigoabuscarinsumo").val();
    var numeroalmacen = $("#numeroalmacen").val();
    var tipooperacion = $("#tipooperacion").val();
    var result = evaluarproductoexistente(codigoabuscarinsumo);
    if(result == false){
        $.get(produccion_obtener_producto_insumo_pt_por_codigo,{codigoabuscarinsumo:codigoabuscarinsumo,numeroalmacen:numeroalmacen,contadorproductos:contadorproductos,contadorfilas:contadorfilas}, function(data){
            if(parseInt(data.contarproductos) > 0){
                $("#tablaproductosremisiones").append(data.filainsumo);
                mostrarformulario();      
                comprobarfilas();
                calculartotalesfilas();
                contadorproductos = data.contadorproductos;
                contadorfilas = data.contadorfilas;
                $("#codigoabuscarinsumo").val("");
                $('.page-loader-wrapper').css('display', 'none');
            }else{
                msjnoseencontroningunproducto();
            }
            //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
            $(".inputnextdet").keypress(function (e) {
              //recomentable para mayor compatibilidad entre navegadores.
              var code = (e.keyCode ? e.keyCode : e.which);
              if(code==13){
                var index = $(this).index(".inputnextdet");          
                $(".inputnextdet").eq(index + 1).focus().select(); 
              }
            });
        }) 
    }else{
        msj_errorproductoyaagregado();
        $('.page-loader-wrapper').css('display', 'none');
    }  
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
        // obtener los datos de la fila:
        var cantidadpartida = $(".cantidadpartida", this).val();
        var mermapartida = $('.mermapartida', this).val();
        var consumopartida = $('.consumopartida', this).val();
        var costounitariopartida = $('.costounitariopartida', this).val();
        var costototalpartida = $('.costototalpartida', this).val();
        var cantidad = $("#cantidad").val();
        var cantidadtotal = 0;
        var restamerma = 0;
        //cantidad total
        cantidadtotal = new Decimal(cantidad).times(cantidadpartida);
        //resta merma
        restamerma = new Decimal(cantidadtotal).times(mermapartida);
        //consumo de la partida
        consumopartida =  new Decimal(cantidadtotal).minus(restamerma);
        $('.consumopartida', this).val(number_format(round(consumopartida, numerodecimales), numerodecimales, '.', ''));
        //costototal de la partida
        costototalpartida =  new Decimal(consumopartida).times(costounitariopartida);
        $('.costototalpartida', this).val(number_format(round(costototalpartida, numerodecimales), numerodecimales, '.', ''));
        calculartotal();
        cuentaFilas++;
    });
}    
//calcular totales de orden de compra
function calculartotal(){
    var total = 0;
    $("tr.filasproductos").each(function(){
        total = new Decimal(total).plus($(".costototalpartida", this).val());
    }); 
    $("#total").val(number_format(round(total, numerodecimales), numerodecimales, '.', ''));
}
//agregar una fila en la tabla de precios productos
var contadorproductos=0;
var contadorfilas = 0;
function agregarfilaproducto(Codigo, Producto, Unidad, Costo, Impuesto, SubTotal, Existencias, tipooperacion, Insumo, ClaveProducto, ClaveUnidad, CostoDeLista){
    $('.page-loader-wrapper').css('display', 'block');
    var result = evaluarproductoexistente(Codigo);
    if(result == false){
        var tipo = "alta";
        var fila=   
        '<tr class="filasproductos" id="filaproducto'+contadorproductos+'">'+
            '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('+contadorproductos+')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'+tipooperacion+'" readonly></td>'+
            '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'+Codigo+'" readonly data-parsley-length="[1, 20]">'+Codigo+'</td>'+
            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'+Producto+'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'+
            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" value="'+Unidad+'" data-parsley-length="[1, 5]"></td>'+
            '<td class="tdmod">'+
                '<input type="hidden" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly>'+
                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();">'+
                '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'+
            '</td>'+
            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm mermapartida" name="mermapartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();"></td>'+
            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm consumopartida" name="consumopartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnextdet divorinputmodsm costounitariopartida" name="costounitariopartida[]" value="'+Costo+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();"></td>'+
            '<td class="tdmod"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" readonly></td>'+
        '</tr>';
        contadorproductos++;
        contadorfilas++;
        $("#tablaproductosremisiones").append(fila);
        mostrarformulario();         
        comprobarfilas();
        calculartotalesfilas();
        $("#codigoabuscarinsumo").val("");
        $('.page-loader-wrapper').css('display', 'none');
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
        calculartotal();  
    }
}
//comprobar numero filas de la tabla precios clientes
function comprobarfilas(){
    var numerofilas = $("#tablaproductosremisiones tbody tr").length;
    $("#numerofilas").val(numerofilas);
}
//alta
function alta(){
  $("#titulomodal").html('Alta Producción');
  mostrarmodalformulario('ALTA', 1);
  mostrarformulario();
  //formulario alta
  var tabs ='<div class="col-md-12">'+
                '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#remisiontab" data-toggle="tab">Producción</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="remisiontab">'+
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Producción <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                                '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                                '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="botonobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnext" name="numerocliente" id="numerocliente" required data-parsley-type="integer" autocomplete="off">'+
                                                '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required data-parsley-type="integer">'+
                                                '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly>'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+  
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Almacén <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="botonobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                                        '</td>'+
                                        '<td>'+
                                            '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnext" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer" autocomplete="off">'+
                                                '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                                                '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Fecha </label>'+
                                '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required data-parsley-excluded="true" onkeydown="return false">'+
                                '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Escribe el código del PT a buscar y presiona la tecla ENTER<span class="label label-danger" id="textopt"></span></label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver PT´s</div>'+
                                        '</td>'+
                                        '<td>'+ 
                                            '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del PT" required autocomplete="off" onkeyup="tipoLetra(this);">'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Cantidad a fabricar PT</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="cantidad" id="cantidad" value="0.'+numerocerosconfigurados+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();" required>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Costo PT</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" readonly required>'+
                            '</div>'+
                            '<div class="col-md-4" hidden>'+
                                '<label>Escribe el código del Insumo a buscar y presiona la tecla ENTER</label>'+
                                '<table class="col-md-12">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<div class="btn bg-blue waves-effect" id="btnobtenerinsumospt" onclick="listarinsumospt()">Ver Insumos PT</div>'+
                                        '</td>'+
                                        '<td>'+ 
                                            '<div class="form-line">'+
                                            '<input type="text" class="form-control" name="codigoabuscarinsumo" id="codigoabuscarinsumo" placeholder="Escribe el código del insumo PT" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                            '</div>'+
                                        '</td>'+
                                    '</tr>'+    
                                '</table>'+
                            '</div>'+
                        '</div>'+
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
                            '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                '<table id="tablaproductosremisiones" class="table table-bordered tablaproductosremisiones">'+
                                    '<thead class="'+background_tables+'">'+
                                        '<tr>'+
                                            '<th class="'+background_tables+'">#</th>'+
                                            '<th class="'+background_tables+'">Código</th>'+
                                            '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                                            '<th class="'+background_tables+'">Unidad</th>'+
                                            '<th class="customercolortheadth">Cantidad</th>'+
                                            '<th class="customercolortheadth">Merma</th>'+
                                            '<th class="'+background_tables+'">Consumo</th>'+
                                            '<th class="customercolortheadth">Costo Unitario</th>'+
                                            '<th class="customercolortheadth">Costo Total</th>'+
                                        '</tr>'+
                                    '</thead>'+
                                    '<tbody>'+           
                                    '</tbody>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+ 
                        '<div class="row">'+
                            '<div class="col-md-6">'+   
                                '<label>Observaciones de PT</label>'+
                                '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" rows="2"></textarea>'+
                            '</div>'+ 
                            '<div class="col-md-3">'+  
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<table class="table table-striped table-hover">'+
                                    '<tr>'+
                                        '<td style="padding:0px !important;">Total</td>'+
                                        '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
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
    //activar seelct2
    $("#tipo").select2();
    $("#unidad").select2();
    //reiniciar los contadores
    contadorproductos=0;
    contadorfilas = 0;
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerproductoporcodigo();
        }
    });
    //activar busqueda de codigos insumos
    $("#codigoabuscarinsumo").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerproductoinsumoptporcodigo();
        }
    });
    //activar busqueda para clientes
    $('#numerocliente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclientepornumero();
        }
    });
    //regresar numero cliente
    $('#numerocliente').on('change', function(e) {
        regresarnumerocliente();
    });
    //activar busqueda para almacenes
    $('#numeroalmacen').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneralmacenpornumero();
        }
    });
    //regresar numero almacen
    $('#numeroalmacen').on('change', function(e) {
        regresarnumeroalmacen();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnext");          
        $(".inputnext").eq(index + 1).focus().select(); 
      }
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnextdet");          
        $(".inputnextdet").eq(index + 1).focus().select(); 
      }
    });
    setTimeout(function(){$("#folio").focus();},500);
    $("#ModalAlta").modal('show');
}
//guardar el registro
$("#btnGuardar").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formparsley")[0]);
    var form = $("#formparsley");
    if (form.parsley().isValid()){
        var numerofilas = $("#numerofilas").val();
        if(parseInt(numerofilas) > 0  && parseInt(numerofilas) < 500){
            $('.page-loader-wrapper').css('display', 'block');
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url:produccion_guardar,
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
            msj_erroralmenosunaentrada();
        }
    }else{
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});

//producir
function producir(produccionproducir){
    $.get(produccion_verificar_existencias_insumos_produccion,{produccionproducir:produccionproducir}, function(data){
        if(data.produccion.Status == 'BAJA'){
            $("#produccionproducir").val(0);
            $("#textomodalproduccion").html('Error, esta Producción ya fue dado de baja');
            $("#btnproducir").hide();
            $('#modalproducir').modal('show');
        }else{   
            if(data.resultadofechas != ''){
                $("#produccionproducir").val(0);
                $("#textomodalproduccion").html('Error solo se pueden realizar las producciones del mes actual, fecha de la producción: ' + data.resultadofechas);
                $("#btnproducir").hide();
                $('#modalproducir').modal('show');
            }else{
                if(data.errores != ''){
                    $("#produccionproducir").val(0);
                    $("#textomodalproduccion").html(data.errores);
                    $("#btnproducir").hide();
                    $('#modalproducir').modal('show');
                }else{
                    $("#produccionproducir").val(produccionproducir);
                    $("#textomodalproduccion").html('Estas seguro de realizar la producción? No'+ produccionproducir);
                    $("#btnproducir").show();
                    $('#modalproducir').modal('show');
                }
            }
        }
    })
}
$("#btnproducir").on('click', function(e){
    e.preventDefault();
    var formData = new FormData($("#formproducir")[0]);
    var form = $("#formproducir");
    if (form.parsley().isValid()){
      $('.page-loader-wrapper').css('display', 'block');
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:produccion_realizar_produccion,
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data){
          $('#modalproducir').modal('hide');
          msj_datosguardadoscorrectamente();
          $('.page-loader-wrapper').css('display', 'none');
        },
        error:function(data){
          if(data.status == 403){
            msj_errorenpermisos();
          }else{
            msj_errorajax();
          }
          $('#modalproducir').modal('hide');
          $('.page-loader-wrapper').css('display', 'none');
        }
      })
    }else{
      form.parsley().validate();
    }
});
//bajas
//verificar si la orden de compra se esta utilzando en alguna orden de compra
function desactivar(producciondesactivar){
    $.get(produccion_verificar_baja,{producciondesactivar:producciondesactivar}, function(data){
        if(data.produccion.Status == 'BAJA'){
            $("#producciondesactivar").val(0);
            $("#textomodaldesactivar").html('Error, esta Producción ya fue dado de baja');
            $("#divmotivobaja").hide();
            $("#btnbaja").hide();
            $('#estatusregistro').modal('show');
        }else{   
            if(data.resultadofechas != ''){
                $("#producciondesactivar").val(0);
                $("#textomodaldesactivar").html('Error solo se pueden dar de baja las producciones del mes actual, fecha de la producción: ' + data.resultadofechas);
                $("#divmotivobaja").hide();
                $("#btnbaja").hide();
                $('#estatusregistro').modal('show');
            }else{
                if(data.errores != ''){
                    $("#producciondesactivar").val(0);
                    $("#textomodaldesactivar").html(data.errores);
                    $("#divmotivobaja").hide();
                    $("#btnbaja").hide();
                    $('#estatusregistro').modal('show');
                }else{
                    $("#producciondesactivar").val(producciondesactivar);
                    $("#textomodaldesactivar").html('Estas seguro de desactivar la producción? No'+ producciondesactivar);
                    $("#motivobaja").val("");
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
        url:produccion_alta_o_baja,
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
function obtenerdatos(produccionmodificar){
  $('.page-loader-wrapper').css('display', 'block');
  $.get(produccion_obtener_produccion,{produccionmodificar:produccionmodificar },function(data){
    $("#titulomodal").html('Modificación Producción --- STATUS : ' + data.produccion.Status);
    //formulario modificacion
    var tabs =  '<div class="col-md-12">'+
                    '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                        '<li role="presentation" class="active">'+
                            '<a href="#remisiontab" data-toggle="tab">Producción</a>'+
                        '</li>'+
                    '</ul>'+
                    '<div class="tab-content">'+
                        '<div role="tabpanel" class="tab-pane fade in active" id="remisiontab">'+
                            '<div class="row">'+
                                '<div class="col-md-3">'+
                                    '<label>Producción <b style="color:#F44336 !important;" id="serietexto"> Serie: '+serieusuario+'</b>&nbsp;&nbsp <div class="btn btn-xs bg-red waves-effect" id="btnobtenerseriesdocumento" onclick="obtenerseriesdocumento()">Cambiar</div></label>'+
                                    '<input type="text" class="form-control inputnext" name="folio" id="folio" required readonly onkeyup="tipoLetra(this);">'+
                                    '<input type="hidden" class="form-control" name="serie" id="serie" value="'+serieusuario+'" required readonly data-parsley-length="[1, 10]">'+
                                    '<input type="hidden" class="form-control" name="numerofilas" id="numerofilas" value="0" readonly>'+
                                    '<input type="hidden" class="form-control" name="tipooperacion" id="tipooperacion" value="alta" readonly>'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Cliente <span class="label label-danger" id="textonombrecliente"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" id="botonobtenerclientes" onclick="obtenerclientes()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="text" class="form-control inputnext" name="numerocliente" id="numerocliente" required data-parsley-type="integer" autocomplete="off">'+
                                                    '<input type="hidden" class="form-control" name="numeroclienteanterior" id="numeroclienteanterior" required data-parsley-type="integer">'+
                                                    '<input type="hidden" class="form-control" name="cliente" id="cliente" required readonly>'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+  
                                    '</table>'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Almacén <span class="label label-danger" id="textonombrealmacen"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" id="botonobteneralmacenes" onclick="obteneralmacenes()">Seleccionar</div>'+
                                            '</td>'+
                                            '<td>'+
                                                '<div class="form-line">'+
                                                    '<input type="text" class="form-control inputnext" name="numeroalmacen" id="numeroalmacen" required data-parsley-type="integer" autocomplete="off">'+
                                                    '<input type="hidden" class="form-control" name="numeroalmacenanterior" id="numeroalmacenanterior" required data-parsley-type="integer">'+
                                                    '<input type="hidden" class="form-control" name="almacen" id="almacen" required readonly>'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<label>Fecha </label>'+
                                    '<input type="datetime-local" class="form-control" name="fecha" id="fecha"  required data-parsley-excluded="true" onkeydown="return false">'+
                                    '<input type="hidden" class="form-control" name="periodohoy" id="periodohoy" value="'+periodohoy+'">'+
                                '</div>'+
                            '</div>'+
                            '<div class="row">'+
                                '<div class="col-md-4">'+
                                    '<label>Escribe el código del PT a buscar y presiona la tecla ENTER<span class="label label-danger" id="textopt"></span></label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" id="btnobtenerproductos" onclick="listarproductos()">Ver PT´s</div>'+
                                            '</td>'+
                                            '<td>'+ 
                                                '<div class="form-line">'+
                                                '<input type="text" class="form-control inputnext" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del PT" required autocomplete="off" onkeyup="tipoLetra(this);">'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+
                                '<div class="col-md-4">'+
                                    '<label>Cantidad a fabricar PT</label>'+
                                    '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="cantidad" id="cantidad" value="0.'+numerocerosconfigurados+'" data-parsley-min="0.'+numerocerosconfiguradosinputnumberstep+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas();" required>'+
                                '</div>'+
                                '<div class="col-md-4">'+
                                    '<label>Costo PT</label>'+
                                    '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" readonly required>'+
                                '</div>'+
                                '<div class="col-md-4" hidden>'+
                                    '<label>Escribe el código del Insumo a buscar y presiona la tecla ENTER</label>'+
                                    '<table class="col-md-12">'+
                                        '<tr>'+
                                            '<td>'+
                                                '<div class="btn bg-blue waves-effect" id="btnobtenerinsumospt" onclick="listarinsumospt()">Ver Insumos PT</div>'+
                                            '</td>'+
                                            '<td>'+ 
                                                '<div class="form-line">'+
                                                '<input type="text" class="form-control" name="codigoabuscarinsumo" id="codigoabuscarinsumo" placeholder="Escribe el código del insumo PT" autocomplete="off" onkeyup="tipoLetra(this);">'+
                                                '</div>'+
                                            '</td>'+
                                        '</tr>'+    
                                    '</table>'+
                                '</div>'+
                            '</div>'+
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
                                '<div class="col-md-12 table-responsive cabecerafija" style="height: 200px;overflow-y: scroll;padding: 0px 0px;">'+
                                    '<table id="tablaproductosremisiones" class="table table-bordered tablaproductosremisiones">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                                '<th class="'+background_tables+'">#</th>'+
                                                '<th class="'+background_tables+'">Código</th>'+
                                                '<th class="'+background_tables+'"><div style="width:200px !important;">Descripción</div></th>'+
                                                '<th class="'+background_tables+'">Unidad</th>'+
                                                '<th class="customercolortheadth">Cantidad</th>'+
                                                '<th class="customercolortheadth">Merma</th>'+
                                                '<th class="'+background_tables+'">Consumo</th>'+
                                                '<th class="customercolortheadth">Costo Unitario</th>'+
                                                '<th class="customercolortheadth">Costo Total</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+ 
                            '<div class="row">'+
                                '<div class="col-md-6">'+   
                                    '<label>Observaciones de PT</label>'+
                                    '<textarea class="form-control inputnextdet" name="observaciones" id="observaciones" onkeyup="tipoLetra(this);" required data-parsley-length="[1, 255]" rows="2"></textarea>'+
                                '</div>'+ 
                                '<div class="col-md-3">'+  
                                '</div>'+
                                '<div class="col-md-3">'+
                                    '<table class="table table-striped table-hover">'+
                                        '<tr>'+
                                            '<td style="padding:0px !important;">Total</td>'+
                                            '<td style="padding:0px !important;"><input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control" style="width:100% !important;height:25px !important;" name="total" id="total" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" required readonly></td>'+
                                        '</tr>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+ 
                        '</div>'+ 
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    $("#periodohoy").val(data.produccion.Periodo);
    $("#folio").val(data.produccion.Folio);
    $("#serie").val(data.produccion.Serie);
    $("#serietexto").html("Serie: "+data.produccion.Serie);
    $("#fecha").val(data.fecha).attr('min', data.fechasdisponiblesenmodificacion.fechamin).attr('max', data.fechasdisponiblesenmodificacion.fechamax);
    $("#cliente").val(data.cliente.Nombre);
    if(data.cliente.Nombre != null){
        $("#textonombrecliente").html(data.cliente.Nombre.substring(0, 40));
    }
    $("#numerocliente").val(data.cliente.Numero);
    $("#numeroclienteanterior").val(data.cliente.Numero);
    $("#almacen").val(data.almacen.Nombre);
    if(data.almacen.Nombre != null){
        $("#textonombrealmacen").html(data.almacen.Nombre.substring(0, 40));
    }
    $("#numeroalmacen").val(data.almacen.Numero);
    $("#numeroalmacenanterior").val(data.almacen.Numero);

    $("#codigoabuscar").val(data.produccion.Codigo);
    $("#cantidad").val(data.cantidad);
    $("#costo").val(data.costo);
    $("#observaciones").val(data.produccion.Obs);
    $("#total").val(data.total);
    //detalles
    $("#tablaproductosremisiones tbody").html(data.filasdetallesproduccion);
    $("#numerofilas").val(data.numerodetallesproduccion);
    //colocar valores a contadores
    contadorproductos = data.contadorproductos;
    contadorfilas = data.contadorfilas;
    //ocultar botones de seleccion
    $("#botonobtenerclientes").show();
    $("#botonobteneralmacenes").hide();
    //activar busqueda de codigos
    $("#codigoabuscar").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerproductoporcodigo();
        }
    });
    //activar busqueda de codigos insumos
    $("#codigoabuscarinsumo").keypress(function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerproductoinsumoptporcodigo();
        }
    });
    //activar busqueda para clientes
    $('#numerocliente').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclientepornumero();
        }
    });
    //regresar numero cliente
    $('#numerocliente').on('change', function(e) {
        regresarnumerocliente();
    });
    //activar busqueda para almacenes
    $('#numeroalmacen').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obteneralmacenpornumero();
        }
    });
    //regresar numero almacen
    $('#numeroalmacen').on('change', function(e) {
        regresarnumeroalmacen();
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnext").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnext");          
        $(".inputnext").eq(index + 1).focus().select(); 
      }
    });
    //hacer que los inputs del formulario pasen de una  otro al dar enter en TAB PRINCIPAL
    $(".inputnextdet").keypress(function (e) {
      //recomentable para mayor compatibilidad entre navegadores.
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        var index = $(this).index(".inputnextdet");          
        $(".inputnextdet").eq(index + 1).focus().select(); 
      }
    });
    //asignar el tipo de operacion que se realizara
    $("#tipooperacion").val("modificacion");
    setTimeout(function(){$("#folio").focus();},500);
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
        var numerofilas = $("#numerofilas").val();
        if(parseInt(numerofilas) > 0  && parseInt(numerofilas) < 500){
            $('.page-loader-wrapper').css('display', 'block');
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url:produccion_guardar_modificacion,
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
            msj_erroralmenosunaentrada();
        }
    }else{
        msjfaltandatosporcapturar();
    }
    //validar formulario
    form.parsley().validate();
});
//obtener datos para el envio del documento por email
function enviardocumentoemail(documento){
    $.get(produccion_obtener_datos_envio_email,{documento:documento}, function(data){
      $("#textomodalenviarpdfemail").html("Enviar email Producción No." + documento);
      $("#emaildocumento").val(documento);
      $("#emailde").val(data.emailde);
      $("#emailpara").val(data.emailpara);
      $("#email2cc").val(data.email2cc);
      $("#email3cc").val(data.email3cc);
      $("#emailasunto").val("PRODUCCIÓN NO. " + documento +" DE "+ nombreempresa);
      $(".dropify-clear").trigger("click");
      $("#divadjuntararchivo").hide();
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
        url:produccion_enviar_pdfs_email,
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
                                            '<th>Documento</th>'+
                                            '<th>Cliente</th>'+
                                            '<th>Total</th>'+
                                            '<th>Status</th>'+
                                          '</tr>';
    $("#columnastablafoliosencontrados").html(columnastablafoliosencontrados);
    $("#columnasfootertablafoliosencontrados").html(columnastablafoliosencontrados);
    //agregar inputs de busqueda por columna
    $('#tablafoliosencontrados tfoot th').each( function () {
      var titulocolumnatfoot = $(this).text();
      $(this).html( '<input type="text" placeholder="Buscar en columna '+titulocolumnatfoot+'" />' );
    });
    var tablafolenc=$('#tablafoliosencontrados').DataTable({
        "paging":   false,
        "sScrollX": "100%",
        "sScrollY": "250px",
        processing: true,
        serverSide: true,
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        ajax: {
            url: produccion_buscar_folio_string_like,
            data: function (d) {
                d.string = $("#buscarfolio").val();
            },
        },
        columns: [
            { data: 'Produccion', name: 'Produccion', orderable: false, searchable: true },
            { data: 'Cliente', name: 'Cliente', orderable: false, searchable: true },
            { data: 'Total', name: 'Total', orderable: false, searchable: true  },
            { data: 'Status', name: 'Status', orderable: false, searchable: true  },
        ],
        initComplete: function () {
            // Aplicar busquedas por columna
            this.api().columns().every( function () {
                var that = this;
                $('input',this.footer()).on('keyup', function(){
                if(that.search() !== this.value){
                    that.search(this.value).draw();
                }
                });
            });
            $(".dataTables_filter").css('display', 'none');
        }
    });
    //modificacion al dar doble click
    $('#tablafoliosencontrados tbody').on('dblclick', 'tr', function () {
        tablafolenc = $("#tablafoliosencontrados").DataTable();
        var data = tablafolenc.row( this ).data();
        agregararraypdf(data.Produccion);
    });
}
//configurar tabla
function configurar_tabla(){
    var checkboxscolumnas = '';
    var optionsselectbusquedas = '';
    if(campos_activados != ""){
        var campos = campos_activados.split(",");
        for (var i = 0; i < campos.length; i++) {
        var returncheckboxfalse = '';
        if(campos[i] == 'Compra' || campos[i] == 'Status' || campos[i] == 'Periodo'){
            returncheckboxfalse = 'onclick="javascript: return false;"';
        }
        checkboxscolumnas = checkboxscolumnas + '<div class="col-md-2 form-check">'+
                                                    '<input type="checkbox" name="'+campos[i]+'" id="id'+campos[i]+'" class="filled-in datotabla" value="'+campos[i]+'" readonly onchange="construirarraydatostabla(this);" '+returncheckboxfalse+'/>'+
                                                    '<label for="id'+campos[i]+'">'+campos[i]+'</label>'+
                                                '</div>';
        optionsselectbusquedas = optionsselectbusquedas + '<option value="'+campos[i]+'">'+campos[i]+'</option>';
        }
    }
    if(campos_desactivados != ""){
        var campos = campos_desactivados.split(",");
        for (var i = 0; i < campos.length; i++) {
        checkboxscolumnas = checkboxscolumnas + '<div class="col-md-2 form-check">'+
                                                    '<input type="checkbox" name="'+campos[i]+'" id="id'+campos[i]+'" class="filled-in datotabla" value="'+campos[i]+'" readonly onchange="construirarraydatostabla(this);"/>'+
                                                    '<label for="id'+campos[i]+'">'+campos[i]+'</label>'+
                                                '</div>';
        optionsselectbusquedas = optionsselectbusquedas + '<option value="'+campos[i]+'">'+campos[i]+'</option>';
        }
    }

    //formulario configuracion tablas se arma desde funcionesglobales.js
    var tabs = armar_formulario_configuracion_tabla(checkboxscolumnas,optionsselectbusquedas);
    $("#tabsconfigurartabla").html(tabs);
    if(rol_usuario_logueado == 1){
      $("#divorderbystabla").show();
    }
    $("#string_datos_ordenamiento_columnas").val(columnas_ordenadas);
    $("#string_datos_tabla_true").val(campos_activados);
    $("#string_datos_tabla_false").val(campos_desactivados);
    $("#modalconfigurartabla").modal('show');
    $("#titulomodalconfiguraciontabla").html("Configuración de la tabla");
    $('.dd').nestable();
    $("#selectorderby1").val(primerordenamiento).select2();
    $("#deorderby1").val(formaprimerordenamiento).select2();
    $("#selectorderby2").val(segundoordenamiento).select2();
    $("#deorderby2").val(formasegundoordenamiento).select2();
    $("#selectorderby3").val(tercerordenamiento).select2();
    $("#deorderby3").val(formatercerordenamiento).select2();
    $.each(campos_busquedas.split(","), function(i,e){
      $("#selectfiltrosbusquedas option[value='" + e + "']").prop("selected", true);
    });
    $("#selectfiltrosbusquedas").select2();
    //colocar checked a campos activados
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