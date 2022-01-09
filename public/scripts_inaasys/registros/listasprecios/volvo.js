'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
    campos_a_filtrar_en_busquedas();
    listar();
}
function retraso(){
  return new Promise(resolve => setTimeout(resolve, 5000));
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
        "pageLength": 500,
        "sScrollX": "110%",
        "sScrollY": "350px", 
        processing: true,
        'language': {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"></div>'
        },
        serverSide: true,
        ajax: lista_precios_volvo_obtener,
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
      obtenerdatos(data.Codigo);
    }); 
}
//alta
function alta(){
    $("#titulomodal").html('Actualizar Lista Precios Volvo');
    mostrarmodalformulario('ALTA');
    mostrarformulario();
    //formulario alta
    var tabs =  '';
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
            url:productos_guardar,
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
function obtenerdatos(codigoproducto){
    $("#titulomodal").html('Modificación Producto');
    $('.page-loader-wrapper').css('display', 'block');
    $.get(productos_obtener_producto,{codigoproducto:codigoproducto },function(data){
    //formulario modificacion
    var tabs =  '<ul class="nav nav-tabs tab-col-blue-grey" role="tablist">'+
                    '<li role="presentation" class="active">'+
                        '<a href="#productotab" data-toggle="tab">Producto</a>'+
                    '</li>'+
                    '<li role="presentation" >'+
                        '<a href="#precioclientestab" data-toggle="tab">Precio Clientes</a>'+
                    '</li>'+
                    '<li role="presentation" >'+
                        '<a href="#consumostab" data-toggle="tab">Consumos</a>'+
                    '</li>'+
                    '<li role="presentation" >'+
                        '<a href="#fechastab" data-toggle="tab">Fechas</a>'+
                    '</li>'+
                '</ul>'+
                '<div class="tab-content">'+
                    '<div role="tabpanel" class="tab-pane fade in active" id="productotab">'+
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Marca<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombremarca"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarmarcas" class="btn bg-blue waves-effect" onclick="listarmarcas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-8">'+  
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="marca" id="marca" required>'+
                                            '<input type="hidden" class="form-control" name="marcaanterior" id="marcaanterior" required readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Linea<b style="color:#F44336 !important;">*</b><span class="label label-danger" id="textonombrelinea"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarlineas" class="btn bg-blue waves-effect" onclick="listarlineas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-8">'+  
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="linea" id="linea" required>'+
                                            '<input type="hidden" class="form-control" name="lineaanterior" id="lineaanterior" required readonly>'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Impuesto % <b style="color:#F44336 !important;">*</b></label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="impuesto" id="impuesto" required value="16.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onkeyup="reiniciartablautilidades();" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                        '</div>'+   
                        '<div class="row">'+
                            '<div class="col-md-4">'+
                                '<label>Costo (De última compra sin impuesto)</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="costo" id="costo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onkeyup="reiniciartablautilidades();" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<label>Ubicación</label>'+
                                '<input type="text" class="form-control inputnext" name="ubicacion" id="ubicacion" data-parsley-length="[1, 60]" onkeyup="tipoLetra(this)">'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Tipo Producto</label>'+
                                '<select name="tipo" id="tipo" class="form-control select2 " style="width:100% !important;" required>'+ 
                                '</select>'+
                            '</div>'+
                        '</div>'+ 
                        '<div class="row">'+
                            '<div class="col-md-3">'+
                                '<label>Costo de Lista</label>'+
                                '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnext" name="costodelista" id="costodelista" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                            '</div>'+
                            '<div class="col-md-3">'+
                                '<label>Moneda<span class="label label-danger" id="textonombremoneda"></span></label>'+
                                '<div class="row">'+
                                    '<div class="col-md-4">'+
                                        '<span class="input-group-btn">'+
                                            '<div id="buscarmonedas" class="btn bg-blue waves-effect" onclick="listarmonedas()">Seleccionar</div>'+
                                        '</span>'+
                                    '</div>'+  
                                    '<div class="col-md-8">'+  
                                        '<div class="form-line">'+
                                            '<input type="text" class="form-control inputnext" name="moneda" id="moneda" required data-parsley-length="[1, 5]" onkeyup="tipoLetra(this);">'+
                                            '<input type="hidden" class="form-control" name="monedaanterior" id="monedaanterior" readonly required data-parsley-length="[1, 5]">'+
                                        '</div>'+
                                    '</div>'+     
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-2" id="divcodigodebarras">'+
                                data.barcode+
                            '</div>'+
                            '<div class="col-md-1">'+
                                '<label>Num. Impre.</label>'+
                                '<input type="number" class="form-control inputnext" name="numimpresiones" id="numimpresiones" value="1" >'+
                            '</div>'+
                            '<div class="col-md-2">'+
                                '<label>Tamaño Etiquetas</label>'+
                                '<select id="tamanoetiquetas" name="tamanoetiquetas" class="form-control">'+
                                    '<option value="chica">chica</option>'+
                                    '<option value="grande">grande</option>'+
                                '</select>'+
                            '</div>'+
                            '<div class="col-md-1">'+
                                '<label>Imprimir</label>'+
                                '<div id="botoncambiardatosimprimircodigobarras" class="btn bg-blue btn-block waves-effect" onclick="imprimircodigosbarras();">Imprimir</div>'+       
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-md-7">'+
                                '<label>UTILIDADES '+tipodeutilidad+'</label>'+   
                                '<div class="table-container" style="height: 14em !important;">'+
                                    '<table id="tbllistadoutilidades" class="scroll tbllistadoutilidades">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                                '<th>Costo de Venta</th>'+
                                                '<th>Utilidad%</th>'+
                                                '<th>Utilidad$</th>'+
                                                '<th>Subtotal$</th>'+
                                                '<th>Iva$</th>'+
                                                '<th>Total$</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-5">'+
                                '<label>EXISTENCIAS</label>'+  
                                '<div class="table-container" style="height: 14em !important;">'+
                                    '<table id="tbllistadoexistenciaalmacen" class="scroll tbllistadoexistenciaalmacen">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                                '<th>Almacén</th>'+
                                                '<th>Nombre</th>'+
                                                '<th>Existen</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="precioclientestab">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="col-md-4">'+
                                    '<h5>PRECIOS A CLIENTES PARA ESTE PRODUCTO&nbsp;&nbsp;&nbsp;</h5>'+
                                '</div>'+    
                                '<div class="col-md-2">'+
                                    '<div id="botonbuscarclientes" class="btn btn-block bg-blue waves-effect" onclick="listarclientes()">Ver Clientes</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<input type="text" class="form-control" name="numeroabuscar" id="numeroabuscar" placeholder="Escribe el número de cliente y presiona enter">'+
                                    '<input type="hidden" class="form-control" name="numerofilasprecioscliente" id="numerofilasprecioscliente">'+
                                '</div>'+
                            '</div>'+
                        '</div>'+ 
                        '<div class="row">'+
                            '<div class="col-md-12">'+   
                                '<div class="table-container">'+
                                    '<table id="tablapreciosclientes" class="scroll tablapreciosclientes">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                            '<th>Operaciones</th>'+    
                                            '<th>Cliente</th>'+
                                            '<th>Nombre</th>'+
                                            '<th>Precio $</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="consumostab">'+
                        '<div class="row">'+
                            '<div class="col-md-12">'+
                                '<div class="col-md-12 form-check">'+
                                    '<label>Producto Terminado (Pt)</label>'+
                                    '<input type="radio" name="consumosproductoterminado" id="consumosproductoterminado" value="S">'+
                                    '<label for="consumosproductoterminado">SI</label>'+
                                    '<input type="radio" name="consumosproductoterminado" id="consumosproductoterminado1" value="N" checked>'+
                                    '<label for="consumosproductoterminado1">NO</label>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-12">'+
                                '<div class="col-md-4">'+
                                    '<h5>CONSUMOS DE PRODUCTO TERMINADO&nbsp;&nbsp;&nbsp;</h5>'+
                                '</div>'+
                                '<div class="col-md-2">'+
                                    '<div id="botonbuscarproductos" class="btn btn-block bg-blue waves-effect" onclick="listarproductos()">Ver Productos</div>'+
                                '</div>'+
                                '<div class="col-md-6">'+    
                                    '<input type="text" class="form-control" name="codigoabuscar" id="codigoabuscar" placeholder="Escribe el código del producto y presiona enter" onkeyup="tipoLetra(this)">'+
                                    '<input type="hidden" class="form-control" name="numerofilasconsumosproductoterminado" id="numerofilasconsumosproductoterminado">'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-12">'+
                                '<div class="table-container">'+
                                    '<table id="tablaconsumosproductoterminado" class="scroll tablaconsumosproductoterminado">'+
                                        '<thead class="'+background_tables+'">'+
                                            '<tr>'+
                                                '<th>Operaciones</th>'+
                                                '<th>Código</th>'+
                                                '<th>Producto</th>'+
                                                '<th>Cantidad $</th>'+
                                                '<th>Unidad</th>'+
                                                '<th>Inventariable</th>'+
                                                '<th>Costo</th>'+
                                                '<th>Precio Neto $</th>'+
                                            '</tr>'+
                                        '</thead>'+
                                        '<tbody>'+           
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div role="tabpanel" class="tab-pane fade" id="fechastab">'+
                        '<div class="row">'+
                            '<div class="col-md-6">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Comisión %</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnexttabfe" name="fechascomision" id="fechascomision" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Descuento %</label>'+
                                        '<input type="text" class="form-control inputnexttabfe" name="fechasdescuento" id="fechasdescuento" value="0.'+numerocerosconfigurados+'" onkeyup="tipoLetra(this);" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Mínimos </label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnexttabfe" name="fechasminimos" id="fechasminimos" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Máximos </label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnexttabfe" name="fechasmaximos" id="fechasmaximos" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Costo Máximo </label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnexttabfe" name="fechascostomaximo" id="fechascostomaximo" value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Fecha Ultima Compra</label>'+
                                        '<input type="date" class="form-control inputnexttabfe" name="fechasfechaultimacompra" id="fechasfechaultimacompra" readonly>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Ultimo Costo $</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnexttabfe" name="fechasultimocosto" id="fechasultimocosto" readonly value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Fecha Ultima Venta</label>'+
                                        '<input type="date" class="form-control inputnexttabfe" name="fechasfechaultimaventa" id="fechasfechaultimaventa" readonly>'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Ultima Venta $</label>'+
                                        '<input type="number" step="0.'+numerocerosconfiguradosinputnumberstep+'" class="form-control inputnexttabfe" name="fechasultimaventa" id="fechasultimaventa" readonly value="0.'+numerocerosconfigurados+'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'+numerodecimales+'}$/" onchange="formatocorrectoinputcantidades(this);">'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6">'+
                                '<div class="row">'+
                                    '<div class="col-md-6">'+
                                        '<label>Supercedido </label>'+
                                        '<input type="text" class="form-control inputnexttabfe" name="fechassupercedido" id="fechassupercedido" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);" >'+
                                    '</div>'+
                                    '<div class="col-md-6">'+
                                        '<label>Insumo </label>'+
                                        '<input type="text" class="form-control inputnexttabfe" name="fechasinsumo" id="fechasinsumo" data-parsley-length="[1, 20]" onkeyup="tipoLetra(this);">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="row">'+
                                    '<div class="col-md-12">'+
                                        '<label>Descripción </label>'+
                                        '<textarea class="form-control inputnexttabfe" name="fechasdescripcion" id="fechasdescripcion" rows="4" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></textarea>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';
    $("#tabsform").html(tabs);
    //obtener tipos
    obtenertipos();
    //datos principales
    $("#codigo").val(codigoproducto);
    $("#codigo").attr('readonly', 'readonly');
    $("#claveproducto").val(data.producto.ClaveProducto);
    obtenerclaveproductoporclave();
    $("#claveunidad").val(data.producto.ClaveUnidad);
    obtenerclaveunidadporclave();
    $("#producto").val(data.producto.Producto);
    $("#unidad").val(data.producto.Unidad);
    //datos tab producto
    $("#marca").val(data.producto.Marca);
    obtenermarcapornumero();
    $("#linea").val(data.producto.Linea);
    obtenerlineapornumero();
    $("#impuesto").val(data.impuesto);
    $("#costo").val(data.costo);
    $("#precio").val(data.precio);
    $("#ubicacion").val(data.producto.Ubicacion);
    $("#costodelista").val(data.costodelista);
    $("#moneda").val(data.producto.Moneda);
    obtenermonedaporclave();
    //datos tab precios clientes
    $("#tablapreciosclientes").append(data.filaspreciosclientes);
    $("#numerofilasprecioscliente").val(data.numerofilasprecioscliente);
    //datos tab consumos
    if(data.pt == ""){
        $('input[name=consumosproductoterminado][value="N"]').attr('checked', 'checked');  
    }else{
        $('input[name=consumosproductoterminado][value='+data.pt+']').attr('checked', 'checked');
    }
    $("#tablaconsumosproductoterminado").append(data.filasconsumos);
    $("#numerofilasconsumosproductoterminado").val(data.numerofilasconsumos);
    //datos tab fechas
    $("#fechasfechaultimacompra").val(data.fechaultimacompra);
    $("#fechasultimocosto").val(data.ultimocosto);
    $("#fechasfechaultimaventa").val(data.fechaultimaventa);
    $("#fechasultimaventa").val(data.ultimaventa);   
    $("#fechascomision").val(data.comision); 
    $("#fechasdescuento").val(data.descuento);
    $("#fechasminimos").val(data.minimos);
    $("#fechasmaximos").val(data.maximos);
    $("#fechascostomaximo").val(data.costomaximo);
    $("#fechaszonadeimpresion").val(data.producto.Zona);
    $("#fechasproductopeligroso").val(data.producto.ProductoPeligroso);
    $("#fechassupercedido").val(data.producto.Supercedido);
    $("#fechasinsumo").val(data.producto.Insumo);
    $("#fechasdescripcion").val(data.producto.Descripcion);
    listarutilidades();
    listarexistenciasalmacenes();
    activarbusquedaproducto();//importante activa la busqueda de productos por su codigo
    activarbusquedacliente();//importante activa la busqueda de clientes por su numero
    //activar busqueda para clave producto
    $('#claveproducto').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclaveproductoporclave();
        }
    });
    //regresar clave
    $('#claveproducto').on('change', function(e) {
        regresarclaveproducto();
    });
    //activar busqueda para clave unidad
    $('#claveunidad').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerclaveunidadporclave();
        }
    });
    //regresar clave
    $('#claveunidad').on('change', function(e) {
        regresarclaveunidad();
    });
    //activar busqueda para marca
    $('#marca').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenermarcapornumero();
        }
    });
    //regresar numero
    $('#marca').on('change', function(e) {
        regresarmarca();
    });
    //activar busqueda para linea
    $('#linea').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenerlineapornumero();
        }
    });
    //regresar numero
    $('#linea').on('change', function(e) {
        regresarlinea();
    });
    
    //activar busqueda para moneda
    $('#moneda').on('keypress', function(e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
            obtenermonedaporclave();
        }
    });
    //regresar clave
    $('#moneda').on('change', function(e) {
        regresarmoneda();
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
    $(".inputnexttabfe").keypress(function (e) {
        //recomentable para mayor compatibilidad entre navegadores.
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
        var index = $(this).index(".inputnexttabfe");          
            $(".inputnexttabfe").eq(index + 1).focus().select(); 
        }
    });
    seleccionartipo(data);
  }).fail( function() {
    msj_errorajax();
    $('.page-loader-wrapper').css('display', 'none');
  })
}
async function seleccionartipo(data){
    await retraso();
    $("#tipo").val(data.producto.TipoProd).change();
    $("#tipo").select2();
    setTimeout(function(){$("#codigo").focus();},500);
    mostrarmodalformulario('MODIFICACION');
    $('.page-loader-wrapper').css('display', 'none');
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
            url:productos_guardar_modificacion,
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
        msj_verificartodoslosdatos();
    }
});



function actualizartipocambio(){
    $("#modalactualizartipocambio").modal('show');
}

function obtenervalordolarhoydof(){ 
    $('.page-loader-wrapper').css('display', 'block');
    $.get(lista_precios_volvo_obtener_valor_dolar_hoy_dof, function(valordolar){
        if(valordolar == 'sinactualizacion'){
            msj_errorajax();
        }else{
            $("#valortipocambio").val(number_format(round(valordolar, numerodecimales), numerodecimales, '.', ''));
        }
        $('.page-loader-wrapper').css('display', 'none');
    })  
}

//guardar el registro
$("#btnguardartipodecambio").on('click', function (e) {
    e.preventDefault();
    var formData = new FormData($("#formactualizartipocambio")[0]);
    var form = $("#formactualizartipocambio");
    if (form.parsley().isValid()){
        $('.page-loader-wrapper').css('display', 'block');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:lista_precios_volvo_guardar_valor_tipo_cambio,
            type: "post",
            dataType: "html",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success:function(data){
                msj_datosguardadoscorrectamente();
                $('#modalactualizartipocambio').modal('hide');
                $("#formactualizartipocambio")[0].reset();
                //Resetear las validaciones del formulario alta
                form = $("#formactualizartipocambio");
                form.parsley().reset();
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
        msj_verificartodoslosdatos();
    }
});




//configurar tabla
function configurar_tabla(){
    var checkboxscolumnas = '';
    var optionsselectbusquedas = '';
    var campos = campos_activados.split(",");
    for (var i = 0; i < campos.length; i++) {
      var returncheckboxfalse = '';
      if(campos[i] == 'Codigo' || campos[i] == 'Status'){
        returncheckboxfalse = 'onclick="javascript: return false;"';
      }
      checkboxscolumnas = checkboxscolumnas + '<div class="col-md-2 form-check">'+
                                                '<input type="checkbox" name="'+campos[i]+'" id="id'+campos[i]+'" class="filled-in datotabla" value="'+campos[i]+'" readonly onchange="construirarraydatostabla(this);" '+returncheckboxfalse+'/>'+
                                                '<label for="id'+campos[i]+'">'+campos[i]+'</label>'+
                                              '</div>';
      optionsselectbusquedas = optionsselectbusquedas + '<option value="'+campos[i]+'">'+campos[i]+'</option>';
    }
    var campos = campos_desactivados.split(",");
    for (var i = 0; i < campos.length; i++) {
      checkboxscolumnas = checkboxscolumnas + '<div class="col-md-2 form-check">'+
                                                '<input type="checkbox" name="'+campos[i]+'" id="id'+campos[i]+'" class="filled-in datotabla" value="'+campos[i]+'" readonly onchange="construirarraydatostabla(this);"/>'+
                                                '<label for="id'+campos[i]+'">'+campos[i]+'</label>'+
                                              '</div>';
      optionsselectbusquedas = optionsselectbusquedas + '<option value="'+campos[i]+'">'+campos[i]+'</option>';
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