'use strict'
var tabla;
var form;
//funcion que se ejecuta al inicio
function init(){
   listar();
}
//listar todos los registros de la tabla
function listar(){
    //Campos ordenados a mostras
    var campos = columnas_ordenadas.split(",");
    var campos_tabla  = [];
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
        ajax: existencias_obtener,
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
                                        '<label>DATOS PRODUCTO</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Codigo" id="idCodigo" class="filled-in datotabla" value="Codigo"  onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                        '<label for="idCodigo">Codigo</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Status" id="idStatus" class="filled-in datotabla" value="Status" onchange="construirarraydatostabla(this);" onclick="javascript: return false;"/>'+
                                        '<label for="idStatus">Status</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Producto" id="idProducto" class="filled-in datotabla" value="Producto" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idProducto">Producto</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Unidad" id="idUnidad" class="filled-in datotabla" value="Unidad" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idUnidad">Unidad</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Ubicacion" id="idUbicacion" class="filled-in datotabla" value="Ubicacion" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idUbicacion">Ubicacion</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Almacen" id="idAlmacen" class="filled-in datotabla" value="Almacen" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idAlmacen">Almacen</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Existencias" id="idExistencias" class="filled-in datotabla" value="Existencias" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idExistencias">Existencias</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Costo" id="idCosto" class="filled-in datotabla" value="Costo" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idCosto">Costo</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="totalCostoInventario" id="idtotalCostoInventario" class="filled-in datotabla" value="totalCostoInventario" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idtotalCostoInventario">totalCostoInventario</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="CostoDeLista" id="idCostoDeLista" class="filled-in datotabla" value="CostoDeLista" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idCostoDeLista">CostoDeLista</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+    
                                        '<input type="checkbox" name="Moneda" id="idMoneda" class="filled-in datotabla" value="Moneda" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idMoneda">Moneda</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="CostoDeVenta" id="idCostoDeVenta" class="filled-in datotabla" value="CostoDeVenta" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idCostoDeVenta">CostoDeVenta</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Utilidad" id="idUtilidad" class="filled-in datotabla" value="Utilidad" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idUtilidad">Utilidad</label>'+
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
                                        '<input type="checkbox" name="Marca" id="idMarca" class="filled-in datotabla" value="Marca" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idMarca">Marca</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Linea" id="idLinea" class="filled-in datotabla" value="Linea" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idLinea">Linea</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="FechaUltimaCompra" id="idFechaUltimaCompra" class="filled-in datotabla" value="FechaUltimaCompra" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idFechaUltimaCompra">FechaUltimaCompra</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="FechaUltimaVenta" id="idFechaUltimaVenta" class="filled-in datotabla" value="FechaUltimaVenta" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idFechaUltimaVenta">FechaUltimaVenta</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="ClaveProducto" id="idClaveProducto" class="filled-in datotabla" value="ClaveProducto" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idClaveProducto">ClaveProducto</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="ClaveUnidad" id="idClaveUnidad" class="filled-in datotabla" value="ClaveUnidad" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idClaveUnidad">ClaveUnidad</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="Precio" id="idPrecio" class="filled-in datotabla" value="Precio" onchange="construirarraydatostabla(this);" />'+
                                        '<label for="idPrecio">Precio</label>'+
                                    '</div>'+
                                    '<input type="hidden" class="form-control" name="string_datos_tabla_true" id="string_datos_tabla_true" required>'+
                                    '<input type="hidden" class="form-control" name="string_datos_tabla_false" id="string_datos_tabla_false" required>'+
                                '</div>'+
                                '<div class="col-md-6">'+
                                    '<div class="col-md-12 form-check">'+
                                        '<label>DATOS MARCA PRODUCTO</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+  
                                        '<input type="checkbox" name="NombreMarca" id="idNombreMarca" class="filled-in datotabla" value="NombreMarca"  onchange="construirarraydatostabla(this);"/>'+
                                        '<label for="idNombreMarca">NombreMarca</label>'+ 
                                    '</div>'+
                                    '<div class="col-md-12 form-check">'+
                                        '<label>DATOS LINEA PRODUCTO</label>'+
                                    '</div>'+
                                    '<div class="col-md-4 form-check">'+
                                        '<input type="checkbox" name="NombreLinea" id="idNombreLinea" class="filled-in datotabla" value="NombreLinea"  onchange="construirarraydatostabla(this);"/>'+
                                        '<label for="idNombreLinea">NombreLinea</label>'+
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