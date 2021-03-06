<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//pruebas
//Si no hay usuario logueado
Route::group(['middleware' => 'guest'], function () {
    Route::get('/', function () { 
        Jenssegers\Date\Date::setLocale(config('app.locale'));
        Jenssegers\Date\Date::setUTF8(true);
        setlocale(LC_TIME, 'es_Es');
        $fechaaccionespanol = Jenssegers\Date\Date::now()->format('l j F Y H:i:s');
        $piensa = App\Piensa::all()->random(1);
        return view('auth.login', compact('piensa','fechaaccionespanol'));
    });

});
//si el usuario esta logueado
Auth::routes();
Route::group(['middleware' => ['auth']], function () {
    Route::get('/inicio', 'HomeController@inicio')->name('inicio');
});
//si el usuario esta logueado
Route::group(['middleware' => ['auth']], function () {
    Route::get('/pruebas', 'ProductoController@pruebas')->name('pruebas');
    Route::get('/pruebaswebscraping', 'PruebaController@pruebaswebscraping')->name('pruebaswebscraping');
    Route::post('/enviar_msj_whatsapp', 'PruebaController@enviar_msj_whatsapp')->name('enviar_msj_whatsapp');
    Route::get('/pruebas_vocales', 'PruebaController@pruebas_vocales')->name('pruebas_vocales');
    Route::get('/prueba_diferencias_arrays', 'PruebaController@prueba_diferencias_arrays')->name('prueba_diferencias_arrays');
    Route::get('/matchar_compras', 'PruebaController@matchar_compras')->name('matchar_compras');
    Route::get('/artisan', function () { 
        //return Artisan::call('config:cache');
    });
    Route::get('/obtener_valor_dolar_dof', 'MonedaController@obtener_valor_dolar_dof')->name('obtener_valor_dolar_dof');
    /* -----------------------------------||||||||||||||||||||CATALOGOS||||||||||||||||||||||-------------------------------------*/
    //Clientes
    Route::get('/clientes', 'ClienteController@clientes')->name('clientes')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener', 'ClienteController@clientes_obtener')->name('clientes_obtener')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_ultimo_numero', 'ClienteController@clientes_obtener_ultimo_numero')->name('clientes_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_paises', 'ClienteController@clientes_obtener_paises')->name('clientes_obtener_paises')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_estados', 'ClienteController@clientes_obtener_estados')->name('clientes_obtener_estados')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_codigos_postales', 'ClienteController@clientes_obtener_codigos_postales')->name('clientes_obtener_codigos_postales')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_municipios', 'ClienteController@clientes_obtener_municipios')->name('clientes_obtener_municipios')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_agentes', 'ClienteController@clientes_obtener_agentes')->name('clientes_obtener_agentes')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_formas_pago', 'ClienteController@clientes_obtener_formas_pago')->name('clientes_obtener_formas_pago')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_metodos_pago', 'ClienteController@clientes_obtener_metodos_pago')->name('clientes_obtener_metodos_pago')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_uso_cfdi', 'ClienteController@clientes_obtener_uso_cfdi')->name('clientes_obtener_uso_cfdi')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::get('/clientes_obtener_productos', 'ClienteController@clientes_obtener_productos')->name('clientes_obtener_productos')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::post('/clientes_guardar', 'ClienteController@clientes_guardar')->name('clientes_guardar')->middleware('revisarpermisos:catalogos.clientes.altas');
    Route::post('/clientes_alta_o_baja', 'ClienteController@clientes_alta_o_baja')->name('clientes_alta_o_baja')->middleware('revisarpermisos:catalogos.clientes.bajas');
    Route::get('/clientes_obtener_cliente', 'ClienteController@clientes_obtener_cliente')->name('clientes_obtener_cliente')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::post('/clientes_guardar_modificacion', 'ClienteController@clientes_guardar_modificacion')->name('clientes_guardar_modificacion')->middleware('revisarpermisos:catalogos.clientes.cambios');
    Route::get('/clientes_exportar_excel', 'ClienteController@clientes_exportar_excel')->name('clientes_exportar_excel')->middleware('revisaraccesomenu:menucatalogoclientes');
    Route::post('/clientes_guardar_configuracion_tabla', 'ClienteController@clientes_guardar_configuracion_tabla')->name('clientes_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menucatalogoclientes');
    //Agentes
    Route::get('/agentes', 'AgenteController@agentes')->name('agentes')->middleware('revisaraccesomenu:menucatalogoagentes');
    Route::get('/agentes_obtener', 'AgenteController@agentes_obtener')->name('agentes_obtener')->middleware('revisaraccesomenu:menucatalogoagentes');
    Route::get('/agentes_obtener_ultimo_numero', 'AgenteController@agentes_obtener_ultimo_numero')->name('agentes_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogoagentes');
    Route::get('/agentes_obtener_almacenes', 'AgenteController@agentes_obtener_almacenes')->name('agentes_obtener_almacenes')->middleware('revisaraccesomenu:menucatalogoagentes');
    Route::post('/agentes_guardar', 'AgenteController@agentes_guardar')->name('agentes_guardar')->middleware('revisarpermisos:catalogos.agentes.altas');
    Route::post('/agentes_alta_o_baja', 'AgenteController@agentes_alta_o_baja')->name('agentes_alta_o_baja')->middleware('revisarpermisos:catalogos.agentes.bajas');
    Route::get('/agentes_obtener_agente', 'AgenteController@agentes_obtener_agente')->name('agentes_obtener_agente')->middleware('revisaraccesomenu:menucatalogoagentes');
    Route::post('/agentes_guardar_modificacion', 'AgenteController@agentes_guardar_modificacion')->name('agentes_guardar_modificacion')->middleware('revisarpermisos:catalogos.agentes.cambios');
    Route::get('/agentes_exportar_excel', 'AgenteController@agentes_exportar_excel')->name('agentes_exportar_excel')->middleware('revisaraccesomenu:menucatalogoagentes');
    //Proveedores
    Route::get('/proveedores', 'ProveedorController@proveedores')->name('proveedores')->middleware('revisaraccesomenu:menucatalogoproveedores');
    Route::get('/proveedores_obtener', 'ProveedorController@proveedores_obtener')->name('proveedores_obtener')->middleware('revisaraccesomenu:menucatalogoproveedores');
    Route::get('/proveedores_obtener_ultimo_numero', 'ProveedorController@proveedores_obtener_ultimo_numero')->name('proveedores_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogoproveedores');
    Route::get('/proveedores_obtener_codigos_postales', 'ProveedorController@proveedores_obtener_codigos_postales')->name('proveedores_obtener_codigos_postales')->middleware('revisaraccesomenu:menucatalogoproveedores');
    Route::post('/proveedores_guardar', 'ProveedorController@proveedores_guardar')->name('proveedores_guardar')->middleware('revisarpermisos:catalogos.proveedores.altas');
    Route::post('/proveedores_alta_o_baja', 'ProveedorController@proveedores_alta_o_baja')->name('proveedores_alta_o_baja')->middleware('revisarpermisos:catalogos.proveedores.bajas');
    Route::get('/proveedores_obtener_proveedor', 'ProveedorController@proveedores_obtener_proveedor')->name('proveedores_obtener_proveedor')->middleware('revisaraccesomenu:menucatalogoproveedores');
    Route::post('/proveedores_guardar_modificacion', 'ProveedorController@proveedores_guardar_modificacion')->name('proveedores_guardar_modificacion')->middleware('revisarpermisos:catalogos.proveedores.cambios');
    Route::get('/proveedores_exportar_excel', 'ProveedorController@proveedores_exportar_excel')->name('proveedores_exportar_excel')->middleware('revisaraccesomenu:menucatalogoproveedores');
    Route::post('/proveedores_guardar_configuracion_tabla', 'ProveedorController@proveedores_guardar_configuracion_tabla')->name('proveedores_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menucatalogoproveedores');
    //Almacenes
    Route::get('/almacenes', 'AlmacenController@almacenes')->name('almacenes')->middleware('revisaraccesomenu:menucatalogoalmacenes');
    Route::get('/almacenes_obtener', 'AlmacenController@almacenes_obtener')->name('almacenes_obtener')->middleware('revisaraccesomenu:menucatalogoalmacenes');
    Route::get('/almacenes_obtener_ultimo_numero', 'AlmacenController@almacenes_obtener_ultimo_numero')->name('almacenes_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogoalmacenes');
    Route::post('/almacenes_guardar', 'AlmacenController@almacenes_guardar')->name('almacenes_guardar')->middleware('revisarpermisos:catalogos.almacenes.altas');
    Route::post('/almacenes_alta_o_baja', 'AlmacenController@almacenes_alta_o_baja')->name('almacenes_alta_o_baja')->middleware('revisarpermisos:catalogos.almacenes.bajas');
    Route::get('/almacenes_obtener_almacen', 'AlmacenController@almacenes_obtener_almacen')->name('almacenes_obtener_almacen')->middleware('revisaraccesomenu:menucatalogoalmacenes');
    Route::post('/almacenes_guardar_modificacion', 'AlmacenController@almacenes_guardar_modificacion')->name('almacenes_guardar_modificacion')->middleware('revisarpermisos:catalogos.almacenes.cambios');
    Route::get('/almacenes_exportar_excel', 'AlmacenController@almacenes_exportar_excel')->name('almacenes_exportar_excel')->middleware('revisaraccesomenu:menucatalogoalmacenes');
    //Lineas
    Route::get('/lineas', 'LineaController@lineas')->name('lineas')->middleware('revisaraccesomenu:menucatalogolineas');
    Route::get('/lineas_obtener', 'LineaController@lineas_obtener')->name('lineas_obtener')->middleware('revisaraccesomenu:menucatalogolineas');
    Route::get('/lineas_obtener_ultimo_numero', 'LineaController@lineas_obtener_ultimo_numero')->name('lineas_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogolineas');
    Route::post('/lineas_guardar', 'LineaController@lineas_guardar')->name('lineas_guardar')->middleware('revisarpermisos:catalogos.lineas.altas');
    Route::post('/lineas_alta_o_baja', 'LineaController@lineas_alta_o_baja')->name('lineas_alta_o_baja')->middleware('revisarpermisos:catalogos.lineas.bajas');
    Route::get('/lineas_obtener_linea', 'LineaController@lineas_obtener_linea')->name('lineas_obtener_linea')->middleware('revisaraccesomenu:menucatalogolineas');
    Route::post('/lineas_guardar_modificacion', 'LineaController@lineas_guardar_modificacion')->name('lineas_guardar_modificacion')->middleware('revisarpermisos:catalogos.lineas.cambios');
    Route::get('/lineas_exportar_excel', 'LineaController@lineas_exportar_excel')->name('lineas_exportar_excel')->middleware('revisaraccesomenu:menucatalogolineas');
    //Marcas
    Route::get('/marcas', 'MarcaController@marcas')->name('marcas')->middleware('revisaraccesomenu:menucatalogomarcas');
    Route::get('/marcas_obtener', 'MarcaController@marcas_obtener')->name('marcas_obtener')->middleware('revisaraccesomenu:menucatalogomarcas');
    Route::get('/marcas_obtener_ultimo_numero', 'MarcaController@marcas_obtener_ultimo_numero')->name('marcas_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogomarcas');
    Route::post('/marcas_guardar', 'MarcaController@marcas_guardar')->name('marcas_guardar')->middleware('revisarpermisos:catalogos.marcas.altas');
    Route::post('/marcas_alta_o_baja', 'MarcaController@marcas_alta_o_baja')->name('marcas_alta_o_baja')->middleware('revisarpermisos:catalogos.marcas.bajas');
    Route::get('/marcas_obtener_marca', 'MarcaController@marcas_obtener_marca')->name('marcas_obtener_marca')->middleware('revisaraccesomenu:menucatalogomarcas');
    Route::post('/marcas_guardar_modificacion', 'MarcaController@marcas_guardar_modificacion')->name('marcas_guardar_modificacion')->middleware('revisarpermisos:catalogos.marcas.cambios');  
    Route::get('/marcas_exportar_excel', 'MarcaController@marcas_exportar_excel')->name('marcas_exportar_excel')->middleware('revisaraccesomenu:menucatalogomarcas');
    //Productos
    Route::get('/productos', 'ProductoController@productos')->name('productos')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener', 'ProductoController@productos_obtener')->name('productos_obtener')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_claves_productos', 'ProductoController@productos_obtener_claves_productos')->name('productos_obtener_claves_productos')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_claves_unidades', 'ProductoController@productos_obtener_claves_unidades')->name('productos_obtener_claves_unidades')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_marcas', 'ProductoController@productos_obtener_marcas')->name('productos_obtener_marcas')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_lineas', 'ProductoController@productos_obtener_lineas')->name('productos_obtener_lineas')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_monedas', 'ProductoController@productos_obtener_monedas')->name('productos_obtener_monedas')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_utilidades', 'ProductoController@productos_obtener_utilidades')->name('productos_obtener_utilidades')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_existencias_almacenes', 'ProductoController@productos_obtener_existencias_almacenes')->name('productos_obtener_existencias_almacenes')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_clientes', 'ProductoController@productos_obtener_clientes')->name('productos_obtener_clientes')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_productos_consumos', 'ProductoController@productos_obtener_productos_consumos')->name('productos_obtener_productos_consumos')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::get('/productos_obtener_kardex', 'ProductoController@productos_obtener_kardex')->name('productos_obtener_kardex')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::post('/productos_guardar', 'ProductoController@productos_guardar')->name('productos_guardar')->middleware('revisarpermisos:catalogos.productos.altas');
    Route::post('/productos_alta_o_baja', 'ProductoController@productos_alta_o_baja')->name('productos_alta_o_baja')->middleware('revisarpermisos:catalogos.productos.bajas');
    Route::get('/productos_obtener_producto', 'ProductoController@productos_obtener_producto')->name('productos_obtener_producto')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::post('/productos_guardar_modificacion', 'ProductoController@productos_guardar_modificacion')->name('productos_guardar_modificacion')->middleware('revisarpermisos:catalogos.productos.cambios');  
    Route::get('/productos_exportar_excel', 'ProductoController@productos_exportar_excel')->name('productos_exportar_excel')->middleware('revisaraccesomenu:menucatalogoproductos');
    Route::post('/productos_guardar_configuracion_tabla', 'ProductoController@productos_guardar_configuracion_tabla')->name('productos_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menucatalogoproductos');
    //Bancos
    Route::get('/bancos', 'BancoController@bancos')->name('bancos')->middleware('revisaraccesomenu:menucatalogobancos');
    Route::get('/bancos_obtener', 'BancoController@bancos_obtener')->name('bancos_obtener')->middleware('revisaraccesomenu:menucatalogobancos');
    Route::get('/bancos_obtener_ultimo_numero', 'BancoController@bancos_obtener_ultimo_numero')->name('bancos_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogobancos');
    Route::post('/bancos_guardar', 'BancoController@bancos_guardar')->name('bancos_guardar')->middleware('revisarpermisos:catalogos.bancos.altas');
    Route::post('/bancos_alta_o_baja', 'BancoController@bancos_alta_o_baja')->name('bancos_alta_o_baja')->middleware('revisarpermisos:catalogos.bancos.bajas');
    Route::get('/bancos_obtener_banco', 'BancoController@bancos_obtener_banco')->name('bancos_obtener_banco')->middleware('revisaraccesomenu:menucatalogobancos');
    Route::post('/bancos_guardar_modificacion', 'BancoController@bancos_guardar_modificacion')->name('bancos_guardar_modificacion')->middleware('revisarpermisos:catalogos.bancos.cambios'); 
    Route::get('/bancos_exportar_excel', 'BancoController@bancos_exportar_excel')->name('bancos_exportar_excel')->middleware('revisaraccesomenu:menucatalogobancos');
    //Tecnicos
    Route::get('/tecnicos', 'TecnicoController@tecnicos')->name('tecnicos')->middleware('revisaraccesomenu:menucatalogotecnicos');
    Route::get('/tecnicos_obtener', 'TecnicoController@tecnicos_obtener')->name('tecnicos_obtener')->middleware('revisaraccesomenu:menucatalogotecnicos');
    Route::get('/tecnicos_obtener_ultimo_numero', 'TecnicoController@tecnicos_obtener_ultimo_numero')->name('tecnicos_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogotecnicos');
    Route::post('/tecnicos_guardar', 'TecnicoController@tecnicos_guardar')->name('tecnicos_guardar')->middleware('revisarpermisos:catalogos.tecnicos.altas');
    Route::post('/tecnicos_alta_o_baja', 'TecnicoController@tecnicos_alta_o_baja')->name('tecnicos_alta_o_baja')->middleware('revisarpermisos:catalogos.tecnicos.bajas');
    Route::get('/tecnicos_obtener_tecnico', 'TecnicoController@tecnicos_obtener_tecnico')->name('tecnicos_obtener_tecnico')->middleware('revisaraccesomenu:menucatalogotecnicos');
    Route::post('/tecnicos_guardar_modificacion', 'TecnicoController@tecnicos_guardar_modificacion')->name('tecnicos_guardar_modificacion')->middleware('revisarpermisos:catalogos.tecnicos.cambios'); 
    Route::get('/tecnicos_exportar_excel', 'TecnicoController@tecnicos_exportar_excel')->name('tecnicos_exportar_excel')->middleware('revisaraccesomenu:menucatalogotecnicos');
    //Servicios
    Route::get('/servicios', 'ServicioController@servicios')->name('servicios')->middleware('revisaraccesomenu:menucatalogoservicios');
    Route::get('/servicios_obtener', 'ServicioController@servicios_obtener')->name('servicios_obtener')->middleware('revisaraccesomenu:menucatalogoservicios');
    Route::get('/servicios_obtener_familias', 'ServicioController@servicios_obtener_familias')->name('servicios_obtener_familias')->middleware('revisaraccesomenu:menucatalogoservicios');
    Route::get('/servicios_obtener_claves_productos', 'ServicioController@servicios_obtener_claves_productos')->name('servicios_obtener_claves_productos')->middleware('revisaraccesomenu:menucatalogoservicios');
    Route::get('/servicios_obtener_claves_unidades', 'ServicioController@servicios_obtener_claves_unidades')->name('servicios_obtener_claves_unidades')->middleware('revisaraccesomenu:menucatalogoservicios');
    Route::post('/servicios_guardar', 'ServicioController@servicios_guardar')->name('servicios_guardar')->middleware('revisarpermisos:catalogos.servicios.altas');
    Route::post('/servicios_alta_o_baja', 'ServicioController@servicios_alta_o_baja')->name('servicios_alta_o_baja')->middleware('revisarpermisos:catalogos.servicios.bajas');
    Route::get('/servicios_obtener_servicio', 'ServicioController@servicios_obtener_servicio')->name('servicios_obtener_servicio')->middleware('revisaraccesomenu:menucatalogoservicios');
    Route::post('/servicios_guardar_modificacion', 'ServicioController@servicios_guardar_modificacion')->name('servicios_guardar_modificacion')->middleware('revisarpermisos:catalogos.servicios.cambios');
    Route::get('/servicios_exportar_excel', 'ServicioController@servicios_exportar_excel')->name('servicios_exportar_excel')->middleware('revisaraccesomenu:menucatalogoservicios');
    //Vines
    Route::get('/vines', 'VineController@vines')->name('vines')->middleware('revisaraccesomenu:menucatalogovines');
    Route::get('/vines_obtener', 'VineController@vines_obtener')->name('vines_obtener')->middleware('revisaraccesomenu:menucatalogovines');
    Route::get('/vines_obtener_clientes', 'VineController@vines_obtener_clientes')->name('vines_obtener_clientes')->middleware('revisaraccesomenu:menucatalogovines');
    Route::post('/vines_guardar', 'VineController@vines_guardar')->name('vines_guardar')->middleware('revisarpermisos:catalogos.vines.altas');
    Route::post('/vines_alta_o_baja', 'VineController@vines_alta_o_baja')->name('vines_alta_o_baja')->middleware('revisarpermisos:catalogos.vines.bajas');
    Route::get('/vines_obtener_vine', 'VineController@vines_obtener_vine')->name('vines_obtener_vine')->middleware('revisaraccesomenu:menucatalogovines');
    Route::post('/vines_guardar_modificacion', 'VineController@vines_guardar_modificacion')->name('vines_guardar_modificacion')->middleware('revisarpermisos:catalogos.vines.cambios');
    Route::get('/vines_exportar_excel', 'VineController@vines_exportar_excel')->name('vines_exportar_excel')->middleware('revisaraccesomenu:menucatalogovines');
    //Folio Comprobante Facturas
    Route::get('/folios_comprobantes_facturas', 'FolioComprobanteFacturaController@folios_comprobantes_facturas')->name('folios_comprobantes_facturas')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosfacturas');
    Route::get('/folios_comprobantes_facturas_obtener', 'FolioComprobanteFacturaController@folios_comprobantes_facturas_obtener')->name('folios_comprobantes_facturas_obtener')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosfacturas');
    Route::post('/folios_comprobantes_facturas_predeterminar', 'FolioComprobanteFacturaController@folios_comprobantes_facturas_predeterminar')->name('folios_comprobantes_facturas_predeterminar')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosfacturas');
    Route::get('/folios_comprobantes_facturas_obtener_ultimo_numero', 'FolioComprobanteFacturaController@folios_comprobantes_facturas_obtener_ultimo_numero')->name('folios_comprobantes_facturas_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosfacturas');
    Route::post('/folios_comprobantes_facturas_guardar', 'FolioComprobanteFacturaController@folios_comprobantes_facturas_guardar')->name('folios_comprobantes_facturas_guardar')->middleware('revisarpermisos:catalogos.folios.fiscales.folios.facturas.altas');
    Route::post('/folios_comprobantes_facturas_alta_o_baja', 'FolioComprobanteFacturaController@folios_comprobantes_facturas_alta_o_baja')->name('folios_comprobantes_facturas_alta_o_baja')->middleware('revisarpermisos:catalogos.folios.fiscales.folios.facturas.bajas');
    Route::get('/folios_comprobantes_facturas_obtener_folio', 'FolioComprobanteFacturaController@folios_comprobantes_facturas_obtener_folio')->name('folios_comprobantes_facturas_obtener_folio')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosfacturas');
    Route::post('/folios_comprobantes_facturas_guardar_modificacion', 'FolioComprobanteFacturaController@folios_comprobantes_facturas_guardar_modificacion')->name('folios_comprobantes_facturas_guardar_modificacion')->middleware('revisarpermisos:catalogos.folios.fiscales.folios.facturas.cambios');
    //Folio Comprobante Notas
    Route::get('/folios_comprobantes_notas', 'FolioComprobanteNotaController@folios_comprobantes_notas')->name('folios_comprobantes_notas')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosnotas');
    Route::get('/folios_comprobantes_notas_obtener', 'FolioComprobanteNotaController@folios_comprobantes_notas_obtener')->name('folios_comprobantes_notas_obtener')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosnotas');
    Route::post('/folios_comprobantes_notas_predeterminar', 'FolioComprobanteNotaController@folios_comprobantes_notas_predeterminar')->name('folios_comprobantes_notas_predeterminar')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosnotas');
    Route::get('/folios_comprobantes_notas_obtener_ultimo_numero', 'FolioComprobanteNotaController@folios_comprobantes_notas_obtener_ultimo_numero')->name('folios_comprobantes_notas_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosnotas');
    Route::post('/folios_comprobantes_notas_guardar', 'FolioComprobanteNotaController@folios_comprobantes_notas_guardar')->name('folios_comprobantes_notas_guardar')->middleware('revisarpermisos:catalogos.folios.fiscales.folios.notas.altas');
    Route::post('/folios_comprobantes_notas_alta_o_baja', 'FolioComprobanteNotaController@folios_comprobantes_notas_alta_o_baja')->name('folios_comprobantes_notas_alta_o_baja')->middleware('revisarpermisos:catalogos.folios.fiscales.folios.notas.bajas');
    Route::get('/folios_comprobantes_notas_obtener_folio', 'FolioComprobanteNotaController@folios_comprobantes_notas_obtener_folio')->name('folios_comprobantes_notas_obtener_folio')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliosnotas');
    Route::post('/folios_comprobantes_notas_guardar_modificacion', 'FolioComprobanteNotaController@folios_comprobantes_notas_guardar_modificacion')->name('folios_comprobantes_notas_guardar_modificacion')->middleware('revisarpermisos:catalogos.folios.fiscales.folios.notas.cambios');
    //Folio Comprobante Pagos
    Route::get('/folios_comprobantes_pagos', 'FolioComprobantePagoController@folios_comprobantes_pagos')->name('folios_comprobantes_pagos')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliospagos');
    Route::get('/folios_comprobantes_pagos_obtener', 'FolioComprobantePagoController@folios_comprobantes_pagos_obtener')->name('folios_comprobantes_pagos_obtener')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliospagos');
    Route::post('/folios_comprobantes_pagos_predeterminar', 'FolioComprobantePagoController@folios_comprobantes_pagos_predeterminar')->name('folios_comprobantes_pagos_predeterminar')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliospagos');
    Route::get('/folios_comprobantes_pagos_obtener_ultimo_numero', 'FolioComprobantePagoController@folios_comprobantes_pagos_obtener_ultimo_numero')->name('folios_comprobantes_pagos_obtener_ultimo_numero')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliospagos');
    Route::post('/folios_comprobantes_pagos_guardar', 'FolioComprobantePagoController@folios_comprobantes_pagos_guardar')->name('folios_comprobantes_pagos_guardar')->middleware('revisarpermisos:catalogos.folios.fiscales.folios.pagos.altas');
    Route::post('/folios_comprobantes_pagos_alta_o_baja', 'FolioComprobantePagoController@folios_comprobantes_pagos_alta_o_baja')->name('folios_comprobantes_pagos_alta_o_baja')->middleware('revisarpermisos:catalogos.folios.fiscales.folios.pagos.bajas');
    Route::get('/folios_comprobantes_pagos_obtener_folio', 'FolioComprobantePagoController@folios_comprobantes_pagos_obtener_folio')->name('folios_comprobantes_pagos_obtener_folio')->middleware('revisaraccesomenu:menucatalogofoliosfiscalesfoliospagos');
    Route::post('/folios_comprobantes_pagos_guardar_modificacion', 'FolioComprobantePagoController@folios_comprobantes_pagos_guardar_modificacion')->name('folios_comprobantes_pagos_guardar_modificacion')->middleware('revisarpermisos:catalogos.folios.fiscales.folios.pagos.cambios');
    //Personal
    Route::get('/personal', 'PersonalController@personal')->name('personal')->middleware('revisaraccesomenu:menucatalogopersonal');
    Route::get('/personal_obtener', 'PersonalController@personal_obtener')->name('personal_obtener')->middleware('revisaraccesomenu:menucatalogopersonal');
    Route::get('/personal_obtener_usuarios_y_tecnicos', 'PersonalController@personal_obtener_usuarios_y_tecnicos')->name('personal_obtener_usuarios_y_tecnicos')->middleware('revisaraccesomenu:menucatalogopersonal');
    Route::post('/personal_guardar_usuarios_y_tecnicos', 'PersonalController@personal_guardar_usuarios_y_tecnicos')->name('personal_guardar_usuarios_y_tecnicos')->middleware('revisarpermisos:catalogos.personal.altas');
    Route::get('/personal_exportar_excel', 'PersonalController@personal_exportar_excel')->name('personal_exportar_excel')->middleware('revisaraccesomenu:menucatalogopersonal');
    Route::post('/personal_alta_o_baja', 'PersonalController@personal_alta_o_baja')->name('personal_alta_o_baja')->middleware('revisarpermisos:catalogos.personal.bajas');
    Route::get('/personal_obtener_personal', 'PersonalController@personal_obtener_personal')->name('personal_obtener_personal')->middleware('revisaraccesomenu:menucatalogopersonal');
    Route::post('/personal_guardar_modificacion', 'PersonalController@personal_guardar_modificacion')->name('personal_guardar_modificacion')->middleware('revisarpermisos:catalogos.personal.cambios');
    //Usuarios
    Route::get('usuarios', 'UserController@usuarios')->name('usuarios');    
    Route::get('usuarios_obtener', 'UserController@usuarios_obtener')->name('usuarios_obtener');
    Route::get('/usuarios_obtener_ultimo_numero', 'UserController@usuarios_obtener_ultimo_numero')->name('usuarios_obtener_ultimo_numero'); 
    Route::get('/usuarios_obtener_roles', 'UserController@usuarios_obtener_roles')->name('usuarios_obtener_roles'); 
    Route::post('/usuarios_guardar', 'UserController@usuarios_guardar')->name('usuarios_guardar'); 
    Route::post('/cambiar_contrasena', 'UserController@cambiar_contrasena')->name('cambiar_contrasena'); 
    Route::post('/usuarios_alta_o_baja', 'UserController@usuarios_alta_o_baja')->name('usuarios_alta_o_baja'); 
    Route::get('/usuarios_obtener_usuario', 'UserController@usuarios_obtener_usuario')->name('usuarios_obtener_usuario'); 
    Route::post('/usuarios_guardar_modificacion', 'UserController@usuarios_guardar_modificacion')->name('usuarios_guardar_modificacion'); 
    Route::get('/usuarios_obtener_permisos', 'UserController@usuarios_obtener_permisos')->name('usuarios_obtener_permisos'); 
    Route::post('/usuarios_guardar_permisos', 'UserController@usuarios_guardar_permisos')->name('usuarios_guardar_permisos');
    Route::get('/usuarios_obtener_submenus_activos', 'UserController@usuarios_obtener_submenus_activos')->name('usuarios_obtener_submenus_activos');
    Route::get('/usuarios_obtener_series_documentos_usuario', 'UserController@usuarios_obtener_series_documentos_usuario')->name('usuarios_obtener_series_documentos_usuario');
    Route::get('/usuarios_obtener_tipos_documentos', 'UserController@usuarios_obtener_tipos_documentos')->name('usuarios_obtener_tipos_documentos');
    Route::post('/usuarios_guardar_serie_documento', 'UserController@usuarios_guardar_serie_documento')->name('usuarios_guardar_serie_documento'); 
    Route::post('/usuarios_guardar_modificacion_serie_documento', 'UserController@usuarios_guardar_modificacion_serie_documento')->name('usuarios_guardar_modificacion_serie_documento'); 
    //Existencias
    Route::get('/existencias', 'ExistenciaController@existencias')->name('existencias')->middleware('revisaraccesomenu:menucatalogoexistencias');
    Route::get('/existencias_obtener', 'ExistenciaController@existencias_obtener')->name('existencias_obtener')->middleware('revisaraccesomenu:menucatalogoexistencias');
    Route::get('/existencias_exportar_excel', 'ExistenciaController@existencias_exportar_excel')->name('existencias_exportar_excel')->middleware('revisaraccesomenu:menucatalogoexistencias');
    Route::post('/existencias_guardar_configuracion_tabla', 'ExistenciaController@existencias_guardar_configuracion_tabla')->name('existencias_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menucatalogoexistencias');
    /* -----------------------------------||||||||||||||||||||FIN CATALOGOS||||||||||||||||||||||-------------------------------------*/

    /* -----------------------------------||||||||||||||||||||REGISTROS||||||||||||||||||||||-------------------------------------*/
    //Ordenes de Compra
    Route::get('/ordenes_compra', 'OrdenCompraController@ordenes_compra')->name('ordenes_compra')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener', 'OrdenCompraController@ordenes_compra_obtener')->name('ordenes_compra_obtener')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_series_documento', 'OrdenCompraController@ordenes_compra_obtener_series_documento')->name('ordenes_compra_obtener_series_documento')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_ultimo_folio_serie_seleccionada', 'OrdenCompraController@ordenes_compra_obtener_ultimo_folio_serie_seleccionada')->name('ordenes_compra_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_ultimo_folio', 'OrdenCompraController@ordenes_compra_obtener_ultimo_folio')->name('ordenes_compra_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_fecha_actual_datetimelocal', 'OrdenCompraController@ordenes_compra_obtener_fecha_actual_datetimelocal')->name('ordenes_compra_obtener_fecha_actual_datetimelocal')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_tipos_ordenes_compra', 'OrdenCompraController@ordenes_compra_obtener_tipos_ordenes_compra')->name('ordenes_compra_obtener_tipos_ordenes_compra')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_proveedores', 'OrdenCompraController@ordenes_compra_obtener_proveedores')->name('ordenes_compra_obtener_proveedores')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_almacenes', 'OrdenCompraController@ordenes_compra_obtener_almacenes')->name('ordenes_compra_obtener_almacenes')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_ordenes_trabajo', 'OrdenCompraController@ordenes_compra_obtener_ordenes_trabajo')->name('ordenes_compra_obtener_ordenes_trabajo')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_orden_trabajo_por_folio', 'OrdenCompraController@ordenes_compra_obtener_orden_trabajo_por_folio')->name('ordenes_compra_obtener_orden_trabajo_por_folio')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_productos', 'OrdenCompraController@ordenes_compra_obtener_productos')->name('ordenes_compra_obtener_productos')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_proveedor_por_numero', 'OrdenCompraController@ordenes_compra_obtener_proveedor_por_numero')->name('ordenes_compra_obtener_proveedor_por_numero')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_almacen_por_numero', 'OrdenCompraController@ordenes_compra_obtener_almacen_por_numero')->name('ordenes_compra_obtener_almacen_por_numero')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_guardar', 'OrdenCompraController@ordenes_compra_guardar')->name('ordenes_compra_guardar')->middleware('revisarpermisos:registros.ordenescompra.altas');
    Route::post('/ordenes_compra_autorizar', 'OrdenCompraController@ordenes_compra_autorizar')->name('ordenes_compra_autorizar')->middleware('revisarpermisos:registros.ordenescompra.autorizar');
    Route::get('/ordenes_compra_verificar_uso_en_modulos', 'OrdenCompraController@ordenes_compra_verificar_uso_en_modulos')->name('ordenes_compra_verificar_uso_en_modulos')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_alta_o_baja', 'OrdenCompraController@ordenes_compra_alta_o_baja')->name('ordenes_compra_alta_o_baja')->middleware('revisarpermisos:registros.ordenescompra.bajas');
    Route::get('/ordenes_compra_obtener_orden_compra', 'OrdenCompraController@ordenes_compra_obtener_orden_compra')->name('ordenes_compra_obtener_orden_compra')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_guardar_modificacion', 'OrdenCompraController@ordenes_compra_guardar_modificacion')->name('ordenes_compra_guardar_modificacion')->middleware('revisarpermisos:registros.ordenescompra.cambios'); 
    Route::get('/ordenes_compra_buscar_folio_string_like', 'OrdenCompraController@ordenes_compra_buscar_folio_string_like')->name('ordenes_compra_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_generar_pdfs', 'OrdenCompraController@ordenes_compra_generar_pdfs')->name('ordenes_compra_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_datos_envio_email', 'OrdenCompraController@ordenes_compra_obtener_datos_envio_email')->name('ordenes_compra_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_enviar_pdfs_email', 'OrdenCompraController@ordenes_compra_enviar_pdfs_email')->name('ordenes_compra_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_generar_pdfs_indiv/{documento}', 'OrdenCompraController@ordenes_compra_generar_pdfs_indiv')->name('ordenes_compra_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_exportar_excel', 'OrdenCompraController@ordenes_compra_exportar_excel')->name('ordenes_compra_exportar_excel')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_guardar_configuracion_tabla', 'OrdenCompraController@ordenes_compra_guardar_configuracion_tabla')->name('ordenes_compra_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    //Compras
    Route::get('/compras', 'CompraController@compras')->name('compras')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener', 'CompraController@compras_obtener')->name('compras_obtener')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_series_documento', 'CompraController@compras_obtener_series_documento')->name('compras_obtener_series_documento')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_ultimo_folio_serie_seleccionada', 'CompraController@compras_obtener_ultimo_folio_serie_seleccionada')->name('compras_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_ultimo_folio', 'CompraController@compras_obtener_ultimo_folio')->name('compras_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_tipos_ordenes_compra', 'CompraController@compras_obtener_tipos_ordenes_compra')->name('compras_obtener_tipos_ordenes_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_cargar_xml_alta', 'CompraController@compras_cargar_xml_alta')->name('compras_cargar_xml_alta')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_ordenes_compra', 'CompraController@compras_obtener_ordenes_compra')->name('compras_obtener_ordenes_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_proveedores', 'CompraController@compras_obtener_proveedores')->name('compras_obtener_proveedores')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_almacenes', 'CompraController@compras_obtener_almacenes')->name('compras_obtener_almacenes')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_almacen_por_numero', 'CompraController@compras_obtener_almacen_por_numero')->name('compras_obtener_almacen_por_numero')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_productos', 'CompraController@compras_obtener_productos')->name('compras_obtener_productos')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_proveedor_por_numero', 'CompraController@compras_obtener_proveedor_por_numero')->name('compras_obtener_proveedor_por_numero')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_orden_compra', 'CompraController@compras_obtener_orden_compra')->name('compras_obtener_orden_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_departamentos', 'CompraController@compras_obtener_departamentos')->name('compras_obtener_departamentos')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_claves_productos', 'CompraController@compras_obtener_claves_productos')->name('compras_obtener_claves_productos')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_claves_unidades', 'CompraController@compras_obtener_claves_unidades')->name('compras_obtener_claves_unidades')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_guardar', 'CompraController@compras_guardar')->name('compras_guardar')->middleware('revisarpermisos:registros.compras.altas');
    Route::get('/compras_obtener_movimientos_compra', 'CompraController@compras_obtener_movimientos_compra')->name('compras_obtener_movimientos_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_compra', 'CompraController@compras_obtener_compra')->name('compras_obtener_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_existencias_partida', 'CompraController@compras_obtener_existencias_partida')->name('compras_obtener_existencias_partida')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_existencias_almacen', 'CompraController@compras_obtener_existencias_almacen')->name('compras_obtener_existencias_almacen')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_guardar_modificacion', 'CompraController@compras_guardar_modificacion')->name('compras_guardar_modificacion')->middleware('revisarpermisos:registros.compras.cambios');
    Route::get('/compras_verificar_uso_en_modulos', 'CompraController@compras_verificar_uso_en_modulos')->name('compras_verificar_uso_en_modulos')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_alta_o_baja', 'CompraController@compras_alta_o_baja')->name('compras_alta_o_baja')->middleware('revisarpermisos:registros.compras.bajas');
    Route::get('/compras_buscar_folio_string_like', 'CompraController@compras_buscar_folio_string_like')->name('compras_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_generar_pdfs', 'CompraController@compras_generar_pdfs')->name('compras_generar_pdfs')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_datos_envio_email', 'CompraController@compras_obtener_datos_envio_email')->name('compras_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_enviar_pdfs_email', 'CompraController@compras_enviar_pdfs_email')->name('compras_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_generar_pdfs_indiv/{documento}', 'CompraController@compras_generar_pdfs_indiv')->name('compras_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_exportar_excel', 'CompraController@compras_exportar_excel')->name('compras_exportar_excel')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_guardar_configuracion_tabla', 'CompraController@compras_guardar_configuracion_tabla')->name('compras_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistroscompras');
    //Ordenes de Trabajo
    Route::get('/ordenes_trabajo', 'OrdenTrabajoController@ordenes_trabajo')->name('ordenes_trabajo')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener', 'OrdenTrabajoController@ordenes_trabajo_obtener')->name('ordenes_trabajo_obtener')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_series_documento', 'OrdenTrabajoController@ordenes_trabajo_obtener_series_documento')->name('ordenes_trabajo_obtener_series_documento')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_ultimo_folio_serie_seleccionada', 'OrdenTrabajoController@ordenes_trabajo_obtener_ultimo_folio_serie_seleccionada')->name('ordenes_trabajo_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_ultimo_folio', 'OrdenTrabajoController@ordenes_trabajo_obtener_ultimo_folio')->name('ordenes_trabajo_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_tipos_ordenes_trabajo', 'OrdenTrabajoController@ordenes_trabajo_obtener_tipos_ordenes_trabajo')->name('ordenes_trabajo_obtener_tipos_ordenes_trabajo')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_tipos_unidades', 'OrdenTrabajoController@ordenes_trabajo_obtener_tipos_unidades')->name('ordenes_trabajo_obtener_tipos_unidades')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_fecha_actual_datetimelocal', 'OrdenTrabajoController@ordenes_trabajo_obtener_fecha_actual_datetimelocal')->name('ordenes_trabajo_obtener_fecha_actual_datetimelocal')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_clientes_facturaa', 'OrdenTrabajoController@ordenes_trabajo_obtener_clientes_facturaa')->name('ordenes_trabajo_obtener_clientes_facturaa')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_cliente_facturaa_por_numero', 'OrdenTrabajoController@ordenes_trabajo_obtener_cliente_facturaa_por_numero')->name('ordenes_trabajo_obtener_cliente_facturaa_por_numero')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_clientes_delcliente', 'OrdenTrabajoController@ordenes_trabajo_obtener_clientes_delcliente')->name('ordenes_trabajo_obtener_clientes_delcliente')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_cliente_delcliente_por_numero', 'OrdenTrabajoController@ordenes_trabajo_obtener_cliente_delcliente_por_numero')->name('ordenes_trabajo_obtener_cliente_delcliente_por_numero')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_agentes', 'OrdenTrabajoController@ordenes_trabajo_obtener_agentes')->name('ordenes_trabajo_obtener_agentes')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_agente_por_numero', 'OrdenTrabajoController@ordenes_trabajo_obtener_agente_por_numero')->name('ordenes_trabajo_obtener_agente_por_numero')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_vines', 'OrdenTrabajoController@ordenes_trabajo_obtener_vines')->name('ordenes_trabajo_obtener_vines')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_vin_por_numero', 'OrdenTrabajoController@ordenes_trabajo_obtener_vin_por_numero')->name('ordenes_trabajo_obtener_vin_por_numero')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_servicios', 'OrdenTrabajoController@ordenes_trabajo_obtener_servicios')->name('ordenes_trabajo_obtener_servicios')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_tecnicos', 'OrdenTrabajoController@ordenes_trabajo_obtener_tecnicos')->name('ordenes_trabajo_obtener_tecnicos')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::post('/ordenes_trabajo_guardar', 'OrdenTrabajoController@ordenes_trabajo_guardar')->name('ordenes_trabajo_guardar')->middleware('revisarpermisos:registros.ordenes.trabajo.altas');
    Route::get('/ordenes_trabajo_obtener_orden_trabajo', 'OrdenTrabajoController@ordenes_trabajo_obtener_orden_trabajo')->name('ordenes_trabajo_obtener_orden_trabajo')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::post('/ordenes_trabajo_guardar_modificacion', 'OrdenTrabajoController@ordenes_trabajo_guardar_modificacion')->name('ordenes_trabajo_guardar_modificacion')->middleware('revisarpermisos:registros.ordenes.trabajo.cambios');
    Route::get('/ordenes_trabajo_verificar_uso_en_modulos', 'OrdenTrabajoController@ordenes_trabajo_verificar_uso_en_modulos')->name('ordenes_trabajo_verificar_uso_en_modulos')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::post('/ordenes_trabajo_alta_o_baja', 'OrdenTrabajoController@ordenes_trabajo_alta_o_baja')->name('ordenes_trabajo_alta_o_baja')->middleware('revisarpermisos:registros.ordenes.trabajo.bajas');
    Route::get('/ordenes_trabajo_verificar_status_orden', 'OrdenTrabajoController@ordenes_trabajo_verificar_status_orden')->name('ordenes_trabajo_verificar_status_orden')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::post('/ordenes_trabajo_terminar_orden', 'OrdenTrabajoController@ordenes_trabajo_terminar_orden')->name('ordenes_trabajo_terminar_orden')->middleware('revisarpermisos:registros.ordenes.trabajo.terminar');
    Route::get('/ordenes_trabajo_buscar_folio_string_like', 'OrdenTrabajoController@ordenes_trabajo_buscar_folio_string_like')->name('ordenes_trabajo_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::post('/ordenes_trabajo_generar_pdfs', 'OrdenTrabajoController@ordenes_trabajo_generar_pdfs')->name('ordenes_trabajo_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_datos_envio_email', 'OrdenTrabajoController@ordenes_trabajo_obtener_datos_envio_email')->name('ordenes_trabajo_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::post('/ordenes_trabajo_enviar_pdfs_email', 'OrdenTrabajoController@ordenes_trabajo_enviar_pdfs_email')->name('ordenes_trabajo_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_generar_pdfs_indiv/{documento}', 'OrdenTrabajoController@ordenes_trabajo_generar_pdfs_indiv')->name('ordenes_trabajo_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');    
    Route::get('/ordenes_trabajo_exportar_excel', 'OrdenTrabajoController@ordenes_trabajo_exportar_excel')->name('ordenes_trabajo_exportar_excel')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::post('/ordenes_trabajo_guardar_configuracion_tabla', 'OrdenTrabajoController@ordenes_trabajo_guardar_configuracion_tabla')->name('ordenes_trabajo_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    //Cuentas por Pagar
    Route::get('/cuentas_por_pagar', 'CuentasPorPagarController@cuentas_por_pagar')->name('cuentas_por_pagar')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener', 'CuentasPorPagarController@cuentas_por_pagar_obtener')->name('cuentas_por_pagar_obtener')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_series_documento', 'CuentasPorPagarController@cuentas_por_pagar_obtener_series_documento')->name('cuentas_por_pagar_obtener_series_documento')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_ultimo_folio_serie_seleccionada', 'CuentasPorPagarController@cuentas_por_pagar_obtener_ultimo_folio_serie_seleccionada')->name('cuentas_por_pagar_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_ultimo_folio', 'CuentasPorPagarController@cuentas_por_pagar_obtener_ultimo_folio')->name('cuentas_por_pagar_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_proveedores', 'CuentasPorPagarController@cuentas_por_pagar_obtener_proveedores')->name('cuentas_por_pagar_obtener_proveedores')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_proveedor_por_numero', 'CuentasPorPagarController@cuentas_por_pagar_obtener_proveedor_por_numero')->name('cuentas_por_pagar_obtener_proveedor_por_numero')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_bancos', 'CuentasPorPagarController@cuentas_por_pagar_obtener_bancos')->name('cuentas_por_pagar_obtener_bancos')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_banco_por_numero', 'CuentasPorPagarController@cuentas_por_pagar_obtener_banco_por_numero')->name('cuentas_por_pagar_obtener_banco_por_numero')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_compras_proveedor', 'CuentasPorPagarController@cuentas_por_pagar_obtener_compras_proveedor')->name('cuentas_por_pagar_obtener_compras_proveedor')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::post('/cuentas_por_pagar_guardar', 'CuentasPorPagarController@cuentas_por_pagar_guardar')->name('cuentas_por_pagar_guardar')->middleware('revisarpermisos:registros.cuentas.x.pagar.altas');
    Route::get('/cuentas_por_pagar_comprobar_baja', 'CuentasPorPagarController@cuentas_por_pagar_comprobar_baja')->name('cuentas_por_pagar_comprobar_baja')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::post('/cuentas_por_pagar_baja', 'CuentasPorPagarController@cuentas_por_pagar_baja')->name('cuentas_por_pagar_baja')->middleware('revisarpermisos:registros.cuentas.x.pagar.bajas');
    Route::get('/cuentas_por_pagar_obtener_cuenta_por_pagar', 'CuentasPorPagarController@cuentas_por_pagar_obtener_cuenta_por_pagar')->name('cuentas_por_pagar_obtener_cuenta_por_pagar')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::post('/cuentas_por_pagar_guardar_modificacion', 'CuentasPorPagarController@cuentas_por_pagar_guardar_modificacion')->name('cuentas_por_pagar_guardar_modificacion')->middleware('revisarpermisos:registros.cuentas.x.pagar.cambios');
    Route::get('/cuentas_por_pagar_buscar_folio_string_like', 'CuentasPorPagarController@cuentas_por_pagar_buscar_folio_string_like')->name('cuentas_por_pagar_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::post('/cuentas_por_pagar_generar_pdfs', 'CuentasPorPagarController@cuentas_por_pagar_generar_pdfs')->name('cuentas_por_pagar_generar_pdfs')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_datos_envio_email', 'CuentasPorPagarController@cuentas_por_pagar_obtener_datos_envio_email')->name('cuentas_por_pagar_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::post('/cuentas_por_pagar_enviar_pdfs_email', 'CuentasPorPagarController@cuentas_por_pagar_enviar_pdfs_email')->name('cuentas_por_pagar_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_generar_pdfs_indiv/{documento}', 'CuentasPorPagarController@cuentas_por_pagar_generar_pdfs_indiv')->name('cuentas_por_pagar_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_exportar_excel', 'CuentasPorPagarController@cuentas_por_pagar_exportar_excel')->name('cuentas_por_pagar_exportar_excel')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::post('/cuentas_por_pagar_guardar_configuracion_tabla', 'CuentasPorPagarController@cuentas_por_pagar_guardar_configuracion_tabla')->name('cuentas_por_pagar_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    //Cuentas por Cobrar
    Route::get('/cuentas_por_cobrar', 'CuentasPorCobrarController@cuentas_por_cobrar')->name('cuentas_por_cobrar')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener')->name('cuentas_por_cobrar_obtener')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_ultimo_folio', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_ultimo_folio')->name('cuentas_por_cobrar_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_fecha_datetime', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_fecha_datetime')->name('cuentas_por_cobrar_obtener_fecha_datetime')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_clientes', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_clientes')->name('cuentas_por_cobrar_obtener_clientes')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_cliente_por_numero', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_cliente_por_numero')->name('cuentas_por_cobrar_obtener_cliente_por_numero')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_bancos', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_bancos')->name('cuentas_por_cobrar_obtener_bancos')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_banco_por_numero', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_banco_por_numero')->name('cuentas_por_cobrar_obtener_banco_por_numero')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_facturas_cliente', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_facturas_cliente')->name('cuentas_por_cobrar_obtener_facturas_cliente')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_metodos_pago', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_metodos_pago')->name('cuentas_por_cobrar_obtener_metodos_pago')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_codigos_postales', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_codigos_postales')->name('cuentas_por_cobrar_obtener_codigos_postales')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_lugar_expedicion_por_clave', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_lugar_expedicion_por_clave')->name('cuentas_por_cobrar_obtener_lugar_expedicion_por_clave')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_regimenes_fiscales', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_regimenes_fiscales')->name('cuentas_por_cobrar_obtener_regimenes_fiscales')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_regimen_fiscal_por_clave', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_regimen_fiscal_por_clave')->name('cuentas_por_cobrar_obtener_regimen_fiscal_por_clave')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_tipos_relacion', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_tipos_relacion')->name('cuentas_por_cobrar_obtener_tipos_relacion')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_tipo_relacion_por_clave', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_tipo_relacion_por_clave')->name('cuentas_por_cobrar_obtener_tipo_relacion_por_clave')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_formas_pago', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_formas_pago')->name('cuentas_por_cobrar_obtener_formas_pago')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_forma_pago_por_clave', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_forma_pago_por_clave')->name('cuentas_por_cobrar_obtener_forma_pago_por_clave')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_facturas', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_facturas')->name('cuentas_por_cobrar_obtener_facturas')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_factura', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_factura')->name('cuentas_por_cobrar_obtener_factura')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_folios_fiscales', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_folios_fiscales')->name('cuentas_por_cobrar_obtener_folios_fiscales')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_ultimo_folio_serie_seleccionada', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_ultimo_folio_serie_seleccionada')->name('cuentas_por_cobrar_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::post('/cuentas_por_cobrar_guardar', 'CuentasPorCobrarController@cuentas_por_cobrar_guardar')->name('cuentas_por_cobrar_guardar')->middleware('revisarpermisos:registros.cuentas.x.cobrar.altas');
    Route::get('/cuentas_por_cobrar_comprobar_baja', 'CuentasPorCobrarController@cuentas_por_cobrar_comprobar_baja')->name('cuentas_por_cobrar_comprobar_baja')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::post('/cuentas_por_cobrar_baja', 'CuentasPorCobrarController@cuentas_por_cobrar_baja')->name('cuentas_por_cobrar_baja')->middleware('revisarpermisos:registros.cuentas.x.cobrar.bajas');
    Route::get('/cuentas_por_cobrar_obtener_cuenta_por_cobrar', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_cuenta_por_cobrar')->name('cuentas_por_cobrar_obtener_cuenta_por_cobrar')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::post('/cuentas_por_cobrar_guardar_modificacion', 'CuentasPorCobrarController@cuentas_por_cobrar_guardar_modificacion')->name('cuentas_por_cobrar_guardar_modificacion')->middleware('revisarpermisos:registros.cuentas.x.cobrar.cambios');
    Route::get('/cuentas_por_cobrar_buscar_folio_string_like', 'CuentasPorCobrarController@cuentas_por_cobrar_buscar_folio_string_like')->name('cuentas_por_cobrar_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::post('/cuentas_por_cobrar_generar_pdfs', 'CuentasPorCobrarController@cuentas_por_cobrar_generar_pdfs')->name('cuentas_por_cobrar_generar_pdfs')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_obtener_datos_envio_email', 'CuentasPorCobrarController@cuentas_por_cobrar_obtener_datos_envio_email')->name('cuentas_por_cobrar_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::post('/cuentas_por_cobrar_enviar_pdfs_email', 'CuentasPorCobrarController@cuentas_por_cobrar_enviar_pdfs_email')->name('cuentas_por_cobrar_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::get('/cuentas_por_cobrar_generar_pdfs_indiv/{documento}', 'CuentasPorCobrarController@cuentas_por_cobrar_generar_pdfs_indiv')->name('cuentas_por_cobrar_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');    
    Route::get('/cuentas_por_cobrar_exportar_excel', 'CuentasPorCobrarController@cuentas_por_cobrar_exportar_excel')->name('cuentas_por_cobrar_exportar_excel')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::post('/cuentas_por_cobrar_guardar_configuracion_tabla', 'CuentasPorCobrarController@cuentas_por_cobrar_guardar_configuracion_tabla')->name('cuentas_por_cobrar_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    //---///---///---///---///---/// INTEGRACION FACTURAPI ////----/////----/////----/////_----/////-----/////
    Route::get('/cuentas_por_cobrar_verificar_si_continua_timbrado', 'CuentasPorCobrarController@cuentas_por_cobrar_verificar_si_continua_timbrado')->name('cuentas_por_cobrar_verificar_si_continua_timbrado')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::post('/cuentas_por_cobrar_timbrar_pago', 'CuentasPorCobrarController@cuentas_por_cobrar_timbrar_pago')->name('cuentas_por_cobrar_timbrar_pago')->middleware('revisarpermisos:registros.cuentas.x.cobrar.timbrar');
    Route::get('/cuentas_por_cobrar_verificar_si_continua_baja_timbre', 'CuentasPorCobrarController@cuentas_por_cobrar_verificar_si_continua_baja_timbre')->name('cuentas_por_cobrar_verificar_si_continua_baja_timbre')->middleware('revisaraccesomenu:menuregistroscuentasxcobrar');
    Route::post('/cuentas_por_cobrar_baja_timbre', 'CuentasPorCobrarController@cuentas_por_cobrar_baja_timbre')->name('cuentas_por_cobrar_baja_timbre')->middleware('revisarpermisos:registros.cuentas.x.cobrar.cancelartimbres');
    //Contrarecibos
    Route::get('/contrarecibos', 'ContraRecibosController@contrarecibos')->name('contrarecibos')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener', 'ContraRecibosController@contrarecibos_obtener')->name('contrarecibos_obtener')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_series_documento', 'ContraRecibosController@contrarecibos_obtener_series_documento')->name('contrarecibos_obtener_series_documento')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_ultimo_folio_serie_seleccionada', 'ContraRecibosController@contrarecibos_obtener_ultimo_folio_serie_seleccionada')->name('contrarecibos_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_ultimo_folio', 'ContraRecibosController@contrarecibos_obtener_ultimo_folio')->name('contrarecibos_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_proveedores', 'ContraRecibosController@contrarecibos_obtener_proveedores')->name('contrarecibos_obtener_proveedores')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_compras_proveedor', 'ContraRecibosController@contrarecibos_obtener_compras_proveedor')->name('contrarecibos_obtener_compras_proveedor')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_compras_proveedor_por_numero', 'ContraRecibosController@contrarecibos_obtener_compras_proveedor_por_numero')->name('contrarecibos_obtener_compras_proveedor_por_numero')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::post('/contrarecibos_guardar', 'ContraRecibosController@contrarecibos_guardar')->name('contrarecibos_guardar')->middleware('revisarpermisos:registros.contrarecibos.altas');
    Route::get('/contrarecibos_verificar_si_continua_baja', 'ContraRecibosController@contrarecibos_verificar_si_continua_baja')->name('contrarecibos_verificar_si_continua_baja')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::post('/contrarecibos_baja', 'ContraRecibosController@contrarecibos_baja')->name('contrarecibos_baja')->middleware('revisarpermisos:registros.contrarecibos.bajas');
    Route::get('/contrarecibos_obtener_contrarecibo', 'ContraRecibosController@contrarecibos_obtener_contrarecibo')->name('contrarecibos_obtener_contrarecibo')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::post('/contrarecibos_guardar_modificacion', 'ContraRecibosController@contrarecibos_guardar_modificacion')->name('contrarecibos_guardar_modificacion')->middleware('revisarpermisos:registros.contrarecibos.cambios');
    Route::get('/contrarecibos_buscar_folio_string_like', 'ContraRecibosController@contrarecibos_buscar_folio_string_like')->name('contrarecibos_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::post('/contrarecibos_generar_pdfs', 'ContraRecibosController@contrarecibos_generar_pdfs')->name('contrarecibos_generar_pdfs')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_datos_envio_email', 'ContraRecibosController@contrarecibos_obtener_datos_envio_email')->name('contrarecibos_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::post('/contrarecibos_enviar_pdfs_email', 'ContraRecibosController@contrarecibos_enviar_pdfs_email')->name('contrarecibos_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_generar_pdfs_indiv/{documento}', 'ContraRecibosController@contrarecibos_generar_pdfs_indiv')->name('contrarecibos_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_exportar_excel', 'ContraRecibosController@contrarecibos_exportar_excel')->name('contrarecibos_exportar_excel')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::post('/contrarecibos_guardar_configuracion_tabla', 'ContraRecibosController@contrarecibos_guardar_configuracion_tabla')->name('contrarecibos_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    //Notas de Crédito Proveedores
    Route::get('/notas_credito_proveedores', 'NotasCreditoProveedoresController@notas_credito_proveedores')->name('notas_credito_proveedores')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener')->name('notas_credito_proveedores_obtener')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_series_documento', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_series_documento')->name('notas_credito_proveedores_obtener_series_documento')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_ultimo_folio_serie_seleccionada', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_ultimo_folio_serie_seleccionada')->name('notas_credito_proveedores_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_ultimo_folio', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_ultimo_folio')->name('notas_credito_proveedores_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedor_obtener_tipos_ordenes_compra', 'NotasCreditoProveedoresController@notas_credito_proveedor_obtener_tipos_ordenes_compra')->name('notas_credito_proveedor_obtener_tipos_ordenes_compra')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_productos', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_productos')->name('notas_credito_proveedores_obtener_productos')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_claves_productos', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_claves_productos')->name('notas_credito_proveedores_obtener_claves_productos')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_claves_unidades', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_claves_unidades')->name('notas_credito_proveedores_obtener_claves_unidades')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_proveedores', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_proveedores')->name('notas_credito_proveedores_obtener_proveedores')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_proveedor_por_numero', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_proveedor_por_numero')->name('notas_credito_proveedores_obtener_proveedor_por_numero')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_almacenes', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_almacenes')->name('notas_credito_proveedores_obtener_almacenes')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_almacen_por_numero', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_almacen_por_numero')->name('notas_credito_proveedores_obtener_almacen_por_numero')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_compras', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_compras')->name('notas_credito_proveedores_obtener_compras')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_compra', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_compra')->name('notas_credito_proveedores_obtener_compra')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedor_obtener_codigos_compra', 'NotasCreditoProveedoresController@notas_credito_proveedor_obtener_codigos_compra')->name('notas_credito_proveedor_obtener_codigos_compra')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_datos_almacen', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_datos_almacen')->name('notas_credito_proveedores_obtener_datos_almacen')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedor_cargar_xml_alta', 'NotasCreditoProveedoresController@notas_credito_proveedor_cargar_xml_alta')->name('notas_credito_proveedor_cargar_xml_alta')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedor_obtener_existencias_partida', 'NotasCreditoProveedoresController@notas_credito_proveedor_obtener_existencias_partida')->name('notas_credito_proveedor_obtener_existencias_partida')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedor_guardar', 'NotasCreditoProveedoresController@notas_credito_proveedor_guardar')->name('notas_credito_proveedor_guardar')->middleware('revisarpermisos:registros.notas.credito.proveedores.altas');
    Route::get('/notas_credito_proveedores_verificar_uso_en_modulos', 'NotasCreditoProveedoresController@notas_credito_proveedores_verificar_uso_en_modulos')->name('notas_credito_proveedores_verificar_uso_en_modulos')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedores_alta_o_baja', 'NotasCreditoProveedoresController@notas_credito_proveedores_alta_o_baja')->name('notas_credito_proveedores_alta_o_baja')->middleware('revisarpermisos:registros.notas.credito.proveedores.bajas');
    Route::get('/notas_credito_proveedores_obtener_nota_proveedor', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_nota_proveedor')->name('notas_credito_proveedores_obtener_nota_proveedor')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedores_guardar_modificacion', 'NotasCreditoProveedoresController@notas_credito_proveedores_guardar_modificacion')->name('notas_credito_proveedores_guardar_modificacion')->middleware('revisarpermisos:registros.notas.credito.proveedores.cambios');
    Route::get('/notas_credito_proveedores_buscar_folio_string_like', 'NotasCreditoProveedoresController@notas_credito_proveedores_buscar_folio_string_like')->name('notas_credito_proveedores_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedores_generar_pdfs', 'NotasCreditoProveedoresController@notas_credito_proveedores_generar_pdfs')->name('notas_credito_proveedores_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_datos_envio_email', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_datos_envio_email')->name('notas_credito_proveedores_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedores_enviar_pdfs_email', 'NotasCreditoProveedoresController@notas_credito_proveedores_enviar_pdfs_email')->name('notas_credito_proveedores_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_generar_pdfs_indiv/{documento}', 'NotasCreditoProveedoresController@notas_credito_proveedores_generar_pdfs_indiv')->name('notas_credito_proveedores_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');    
    Route::get('/notas_credito_proveedores_exportar_excel', 'NotasCreditoProveedoresController@notas_credito_proveedores_exportar_excel')->name('notas_credito_proveedores_exportar_excel')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedor_guardar_configuracion_tabla', 'NotasCreditoProveedoresController@notas_credito_proveedor_guardar_configuracion_tabla')->name('notas_credito_proveedor_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    //Notas de Crédito Clientes
    Route::get('/notas_credito_clientes', 'NotasCreditoClientesController@notas_credito_clientes')->name('notas_credito_clientes')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener', 'NotasCreditoClientesController@notas_credito_clientes_obtener')->name('notas_credito_clientes_obtener')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_ultimo_folio', 'NotasCreditoClientesController@notas_credito_clientes_obtener_ultimo_folio')->name('notas_credito_clientes_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_clientes', 'NotasCreditoClientesController@notas_credito_clientes_obtener_clientes')->name('notas_credito_clientes_obtener_clientes')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_cliente_por_numero', 'NotasCreditoClientesController@notas_credito_clientes_obtener_cliente_por_numero')->name('notas_credito_clientes_obtener_cliente_por_numero')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_almacenes', 'NotasCreditoClientesController@notas_credito_clientes_obtener_almacenes')->name('notas_credito_clientes_obtener_almacenes')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_almacen_por_numero', 'NotasCreditoClientesController@notas_credito_clientes_obtener_almacen_por_numero')->name('notas_credito_clientes_obtener_almacen_por_numero')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');    
    Route::get('/notas_credito_clientes_obtener_codigos_postales', 'NotasCreditoClientesController@notas_credito_clientes_obtener_codigos_postales')->name('notas_credito_clientes_obtener_codigos_postales')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_lugar_expedicion_por_clave', 'NotasCreditoClientesController@notas_credito_clientes_obtener_lugar_expedicion_por_clave')->name('notas_credito_clientes_obtener_lugar_expedicion_por_clave')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_regimenes_fiscales', 'NotasCreditoClientesController@notas_credito_clientes_obtener_regimenes_fiscales')->name('notas_credito_clientes_obtener_regimenes_fiscales')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_regimen_fiscal_por_clave', 'NotasCreditoClientesController@notas_credito_clientes_obtener_regimen_fiscal_por_clave')->name('notas_credito_clientes_obtener_regimen_fiscal_por_clave')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_tipos_relacion', 'NotasCreditoClientesController@notas_credito_clientes_obtener_tipos_relacion')->name('notas_credito_clientes_obtener_tipos_relacion')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_tipo_relacion_por_clave', 'NotasCreditoClientesController@notas_credito_clientes_obtener_tipo_relacion_por_clave')->name('notas_credito_clientes_obtener_tipo_relacion_por_clave')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');    
    Route::get('/notas_credito_clientes_obtener_formas_pago', 'NotasCreditoClientesController@notas_credito_clientes_obtener_formas_pago')->name('notas_credito_clientes_obtener_formas_pago')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_forma_pago_por_clave', 'NotasCreditoClientesController@notas_credito_clientes_obtener_forma_pago_por_clave')->name('notas_credito_clientes_obtener_forma_pago_por_clave')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_metodos_pago', 'NotasCreditoClientesController@notas_credito_clientes_obtener_metodos_pago')->name('notas_credito_clientes_obtener_metodos_pago')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_metodo_pago_por_clave', 'NotasCreditoClientesController@notas_credito_clientes_obtener_metodo_pago_por_clave')->name('notas_credito_clientes_obtener_metodo_pago_por_clave')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_usos_cfdi', 'NotasCreditoClientesController@notas_credito_clientes_obtener_usos_cfdi')->name('notas_credito_clientes_obtener_usos_cfdi')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_uso_cfdi_por_clave', 'NotasCreditoClientesController@notas_credito_clientes_obtener_uso_cfdi_por_clave')->name('notas_credito_clientes_obtener_uso_cfdi_por_clave')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');    
    Route::get('/notas_credito_clientes_obtener_residencias_fiscales', 'NotasCreditoClientesController@notas_credito_clientes_obtener_residencias_fiscales')->name('notas_credito_clientes_obtener_residencias_fiscales')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_residencia_fiscal_por_clave', 'NotasCreditoClientesController@notas_credito_clientes_obtener_residencia_fiscal_por_clave')->name('notas_credito_clientes_obtener_residencia_fiscal_por_clave')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_cliente_comprobar_cantidad_nota_vs_cantidad_factura', 'NotasCreditoClientesController@notas_credito_cliente_comprobar_cantidad_nota_vs_cantidad_factura')->name('notas_credito_cliente_comprobar_cantidad_nota_vs_cantidad_factura')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_facturas', 'NotasCreditoClientesController@notas_credito_clientes_obtener_facturas')->name('notas_credito_clientes_obtener_facturas')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_factura', 'NotasCreditoClientesController@notas_credito_clientes_obtener_factura')->name('notas_credito_clientes_obtener_factura')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_datos_almacen', 'NotasCreditoClientesController@notas_credito_clientes_obtener_datos_almacen')->name('notas_credito_clientes_obtener_datos_almacen')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_productos', 'NotasCreditoClientesController@notas_credito_clientes_obtener_productos')->name('notas_credito_clientes_obtener_productos')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_claves_productos', 'NotasCreditoClientesController@notas_credito_clientes_obtener_claves_productos')->name('notas_credito_clientes_obtener_claves_productos')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_claves_unidades', 'NotasCreditoClientesController@notas_credito_clientes_obtener_claves_unidades')->name('notas_credito_clientes_obtener_claves_unidades')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_folios_fiscales', 'NotasCreditoClientesController@notas_credito_clientes_obtener_folios_fiscales')->name('notas_credito_clientes_obtener_folios_fiscales')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_ultimo_folio_serie_seleccionada', 'NotasCreditoClientesController@notas_credito_clientes_obtener_ultimo_folio_serie_seleccionada')->name('notas_credito_clientes_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::post('/notas_credito_clientes_guardar', 'NotasCreditoClientesController@notas_credito_clientes_guardar')->name('notas_credito_clientes_guardar')->middleware('revisarpermisos:registros.notas.credito.clientes.altas');
    Route::get('/notas_credito_clientes_verificar_si_continua_baja', 'NotasCreditoClientesController@notas_credito_clientes_verificar_si_continua_baja')->name('notas_credito_clientes_verificar_si_continua_baja')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::post('/notas_credito_clientes_alta_o_baja', 'NotasCreditoClientesController@notas_credito_clientes_alta_o_baja')->name('notas_credito_clientes_alta_o_baja')->middleware('revisarpermisos:registros.notas.credito.clientes.bajas');
    Route::get('/notas_credito_clientes_obtener_nota_cliente', 'NotasCreditoClientesController@notas_credito_clientes_obtener_nota_cliente')->name('notas_credito_clientes_obtener_nota_cliente')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::post('/notas_credito_clientes_guardar_modificacion', 'NotasCreditoClientesController@notas_credito_clientes_guardar_modificacion')->name('notas_credito_clientes_guardar_modificacion')->middleware('revisarpermisos:registros.notas.credito.clientes.cambios');
    Route::get('/notas_credito_clientes_buscar_folio_string_like', 'NotasCreditoClientesController@notas_credito_clientes_buscar_folio_string_like')->name('notas_credito_clientes_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::post('/notas_credito_clientes_generar_pdfs', 'NotasCreditoClientesController@notas_credito_clientes_generar_pdfs')->name('notas_credito_clientes_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_obtener_datos_envio_email', 'NotasCreditoClientesController@notas_credito_clientes_obtener_datos_envio_email')->name('notas_credito_clientes_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::post('/notas_credito_clientes_enviar_pdfs_email', 'NotasCreditoClientesController@notas_credito_clientes_enviar_pdfs_email')->name('notas_credito_clientes_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::get('/notas_credito_clientes_generar_pdfs_indiv/{documento}', 'NotasCreditoClientesController@notas_credito_clientes_generar_pdfs_indiv')->name('notas_credito_clientes_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');    
    Route::get('/notas_credito_clientes_exportar_excel', 'NotasCreditoClientesController@notas_credito_clientes_exportar_excel')->name('notas_credito_clientes_exportar_excel')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::post('/notas_credito_clientes_guardar_configuracion_tabla', 'NotasCreditoClientesController@notas_credito_clientes_guardar_configuracion_tabla')->name('notas_credito_clientes_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    //---///---///---///---///---/// INTEGRACION FACTURAPI ////----/////----/////----/////_----/////-----/////
    Route::get('/notas_credito_clientes_verificar_si_continua_timbrado', 'NotasCreditoClientesController@notas_credito_clientes_verificar_si_continua_timbrado')->name('notas_credito_clientes_verificar_si_continua_timbrado')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::post('/notas_credito_clientes_timbrar_nota', 'NotasCreditoClientesController@notas_credito_clientes_timbrar_nota')->name('notas_credito_clientes_timbrar_nota')->middleware('revisarpermisos:registros.notas.credito.clientes.timbrar');
    Route::get('/notas_credito_clientes_verificar_si_continua_baja_timbre', 'NotasCreditoClientesController@notas_credito_clientes_verificar_si_continua_baja_timbre')->name('notas_credito_clientes_verificar_si_continua_baja_timbre')->middleware('revisaraccesomenu:menuregistrosnotascreditoclientes');
    Route::post('/notas_credito_clientes_baja_timbre', 'NotasCreditoClientesController@notas_credito_clientes_baja_timbre')->name('notas_credito_clientes_baja_timbre')->middleware('revisarpermisos:registros.notas.credito.clientes.cancelartimbres');
    //Asignación de herramienta
    Route::get('/asignacionherramienta', 'AsignacionHerramientaController@asignacionherramienta')->name('asignacionherramienta')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener', 'AsignacionHerramientaController@asignacion_herramienta_obtener')->name('asignacion_herramienta_obtener')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_series_documento', 'AsignacionHerramientaController@asignacion_herramienta_obtener_series_documento')->name('asignacion_herramienta_obtener_series_documento')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_ultimo_folio_serie_seleccionada', 'AsignacionHerramientaController@asignacion_herramienta_obtener_ultimo_folio_serie_seleccionada')->name('asignacion_herramienta_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_ultimo_id', 'AsignacionHerramientaController@asignacion_herramienta_obtener_ultimo_id')->name('asignacion_herramienta_obtener_ultimo_id')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_personal_recibe', 'AsignacionHerramientaController@asignacion_herramienta_obtener_personal_recibe')->name('asignacion_herramienta_obtener_personal_recibe')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_personal_recibe_por_numero', 'AsignacionHerramientaController@asignacion_herramienta_obtener_personal_recibe_por_numero')->name('asignacion_herramienta_obtener_personal_recibe_por_numero')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');    
    Route::get('/asignacion_herramienta_obtener_personal_entrega', 'AsignacionHerramientaController@asignacion_herramienta_obtener_personal_entrega')->name('asignacion_herramienta_obtener_personal_entrega')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_personal_entrega_por_numero', 'AsignacionHerramientaController@asignacion_herramienta_obtener_personal_entrega_por_numero')->name('asignacion_herramienta_obtener_personal_entrega_por_numero')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_herramienta', 'AsignacionHerramientaController@asignacion_herramienta_obtener_herramienta')->name('asignacion_herramienta_obtener_herramienta')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_existencias_almacen', 'AsignacionHerramientaController@asignacion_herramienta_obtener_existencias_almacen')->name('asignacion_herramienta_obtener_existencias_almacen')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::post('/asignacion_herramienta_guardar', 'AsignacionHerramientaController@asignacion_herramienta_guardar')->name('asignacion_herramienta_guardar')->middleware('revisarpermisos:registros.asignacion.herramienta.altas');
    Route::get('/asignacion_herramienta_obtener_asignacion_herramienta_a_autorizar', 'AsignacionHerramientaController@asignacion_herramienta_obtener_asignacion_herramienta_a_autorizar')->name('asignacion_herramienta_obtener_asignacion_herramienta_a_autorizar')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::post('/asignacion_herramienta_autorizar', 'AsignacionHerramientaController@asignacion_herramienta_autorizar')->name('asignacion_herramienta_autorizar')->middleware('revisarpermisos:registros.asignacion.herramienta.autorizar');
    Route::get('/asignacion_herramienta_obtener_asignacion_herramienta', 'AsignacionHerramientaController@asignacion_herramienta_obtener_asignacion_herramienta')->name('asignacion_herramienta_obtener_asignacion_herramienta')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::post('/asignacion_herramienta_guardar_modificacion', 'AsignacionHerramientaController@asignacion_herramienta_guardar_modificacion')->name('asignacion_herramienta_guardar_modificacion')->middleware('revisarpermisos:registros.asignacion.herramienta.cambios');
    Route::get('/asignacion_herramienta_verificar_uso_en_modulos', 'AsignacionHerramientaController@asignacion_herramienta_verificar_uso_en_modulos')->name('asignacion_herramienta_verificar_uso_en_modulos')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::post('/asignacion_herramienta_alta_o_baja', 'AsignacionHerramientaController@asignacion_herramienta_alta_o_baja')->name('asignacion_herramienta_alta_o_baja')->middleware('revisarpermisos:registros.asignacion.herramienta.bajas');
    Route::get('/asignacion_herramienta_buscar_id_string_like', 'AsignacionHerramientaController@asignacion_herramienta_buscar_id_string_like')->name('asignacion_herramienta_buscar_id_string_like')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::post('/asignacion_herramienta_generar_pdfs', 'AsignacionHerramientaController@asignacion_herramienta_generar_pdfs')->name('asignacion_herramienta_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_generar_excel_obtener_personal', 'AsignacionHerramientaController@asignacion_herramienta_generar_excel_obtener_personal')->name('asignacion_herramienta_generar_excel_obtener_personal')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_herramienta_personal', 'AsignacionHerramientaController@asignacion_herramienta_obtener_herramienta_personal')->name('asignacion_herramienta_obtener_herramienta_personal')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::post('/asignacion_herramienta_guardar_auditoria', 'AsignacionHerramientaController@asignacion_herramienta_guardar_auditoria')->name('asignacion_herramienta_guardar_auditoria')->middleware('revisarpermisos:registros.asignacion.herramienta.auditoria.altas');
    Route::get('/asignacion_herramienta_generar_reporte_auditoria/{id}', 'AsignacionHerramientaController@asignacion_herramienta_generar_reporte_auditoria')->name('asignacion_herramienta_generar_reporte_auditoria')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_generar_reporte_general/{id}', 'AsignacionHerramientaController@asignacion_herramienta_generar_reporte_general')->name('asignacion_herramienta_generar_reporte_general')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_exportar_excel', 'AsignacionHerramientaController@asignacion_herramienta_exportar_excel')->name('asignacion_herramienta_exportar_excel')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::post('/asignacion_herramienta_guardar_configuracion_tabla', 'AsignacionHerramientaController@asignacion_herramienta_guardar_configuracion_tabla')->name('asignacion_herramienta_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    //Prestamo Herramienta
    Route::get('/prestamoherramienta', 'PrestamoHerramientaController@prestamoherramienta')->name('prestamoherramienta')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener', 'PrestamoHerramientaController@prestamo_herramienta_obtener')->name('prestamo_herramienta_obtener')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_series_documento', 'PrestamoHerramientaController@prestamo_herramienta_obtener_series_documento')->name('prestamo_herramienta_obtener_series_documento')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_ultimo_folio_serie_seleccionada', 'PrestamoHerramientaController@prestamo_herramienta_obtener_ultimo_folio_serie_seleccionada')->name('prestamo_herramienta_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_ultimo_id', 'PrestamoHerramientaController@prestamo_herramienta_obtener_ultimo_id')->name('prestamo_herramienta_obtener_ultimo_id')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_personal', 'PrestamoHerramientaController@prestamo_herramienta_obtener_personal')->name('prestamo_herramienta_obtener_personal')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_herramienta_personal', 'PrestamoHerramientaController@prestamo_herramienta_obtener_herramienta_personal')->name('prestamo_herramienta_obtener_herramienta_personal')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_detalle_asignacion_seleccionada', 'PrestamoHerramientaController@prestamo_herramienta_obtener_detalle_asignacion_seleccionada')->name('prestamo_herramienta_obtener_detalle_asignacion_seleccionada')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_fecha_datetimelocal', 'PrestamoHerramientaController@prestamo_herramienta_obtener_fecha_datetimelocal')->name('prestamo_herramienta_obtener_fecha_datetimelocal')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_personal_recibe', 'PrestamoHerramientaController@prestamo_herramienta_obtener_personal_recibe')->name('prestamo_herramienta_obtener_personal_recibe')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_personal_recibe_por_numero', 'PrestamoHerramientaController@prestamo_herramienta_obtener_personal_recibe_por_numero')->name('prestamo_herramienta_obtener_personal_recibe_por_numero')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::post('/prestamo_herramienta_guardar', 'PrestamoHerramientaController@prestamo_herramienta_guardar')->name('prestamo_herramienta_guardar')->middleware('revisarpermisos:registros.prestamo.herramienta.altas');
    Route::post('/prestamo_herramienta_terminar_prestamo', 'PrestamoHerramientaController@prestamo_herramienta_terminar_prestamo')->name('prestamo_herramienta_terminar_prestamo')->middleware('revisarpermisos:registros.prestamo.herramienta.terminar');
    Route::post('/prestamo_herramienta_alta_o_baja', 'PrestamoHerramientaController@prestamo_herramienta_alta_o_baja')->name('prestamo_herramienta_alta_o_baja')->middleware('revisarpermisos:registros.prestamo.herramienta.bajas');
    Route::get('/prestamo_herramienta_obtener_prestamo_herramienta', 'PrestamoHerramientaController@prestamo_herramienta_obtener_prestamo_herramienta')->name('prestamo_herramienta_obtener_prestamo_herramienta')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::post('/prestamo_herramienta_guardar_modificacion', 'PrestamoHerramientaController@prestamo_herramienta_guardar_modificacion')->name('prestamo_herramienta_guardar_modificacion')->middleware('revisarpermisos:registros.prestamo.herramienta.cambios');
    Route::post('/prestamo_herramienta_guardar_configuracion_tabla', 'PrestamoHerramientaController@prestamo_herramienta_guardar_configuracion_tabla')->name('prestamo_herramienta_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_exportar_excel', 'PrestamoHerramientaController@prestamo_herramienta_exportar_excel')->name('prestamo_herramienta_exportar_excel')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    //Cotizaciones
    Route::get('/cotizaciones', 'CotizacionController@cotizaciones')->name('cotizaciones')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_obtener', 'CotizacionController@cotizaciones_obtener')->name('cotizaciones_obtener')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_obtener_series_documento', 'CotizacionController@cotizaciones_obtener_series_documento')->name('cotizaciones_obtener_series_documento')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_obtener_ultimo_folio_serie_seleccionada', 'CotizacionController@cotizaciones_obtener_ultimo_folio_serie_seleccionada')->name('cotizaciones_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_obtener_ultimo_id', 'CotizacionController@cotizaciones_obtener_ultimo_id')->name('cotizaciones_obtener_ultimo_id')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_obtener_remisiones', 'CotizacionController@cotizaciones_obtener_remisiones')->name('cotizaciones_obtener_remisiones')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_obtener_remision', 'CotizacionController@cotizaciones_obtener_remision')->name('cotizaciones_obtener_remision')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::post('/cotizaciones_guardar', 'CotizacionController@cotizaciones_guardar')->name('cotizaciones_guardar')->middleware('revisarpermisos:registros.cotizaciones.altas');
    Route::get('/cotizaciones_verificar_uso_en_modulos', 'CotizacionController@cotizaciones_verificar_uso_en_modulos')->name('cotizaciones_verificar_uso_en_modulos')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::post('/cotizaciones_alta_o_baja', 'CotizacionController@cotizaciones_alta_o_baja')->name('cotizaciones_alta_o_baja')->middleware('revisarpermisos:registros.cotizaciones.bajas');
    Route::get('/cotizaciones_obtener_cotizacion', 'CotizacionController@cotizaciones_obtener_cotizacion')->name('cotizaciones_obtener_cotizacion')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::post('/cotizaciones_guardar_modificacion', 'CotizacionController@cotizaciones_guardar_modificacion')->name('cotizaciones_guardar_modificacion')->middleware('revisarpermisos:registros.cotizaciones.cambios');
    Route::get('/cotizaciones_exportar_excel', 'CotizacionController@cotizaciones_exportar_excel')->name('cotizaciones_exportar_excel')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_crear_formato_excel/{cotizacion}', 'CotizacionController@cotizaciones_crear_formato_excel')->name('cotizaciones_crear_formato_excel')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::post('/cotizaciones_guardar_configuracion_tabla', 'CotizacionController@cotizaciones_guardar_configuracion_tabla')->name('cotizaciones_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    //Ajuste Inventario
    Route::get('/ajustesinventario', 'AjusteInventarioController@ajustesinventario')->name('ajustesinventario')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener', 'AjusteInventarioController@ajustesinventario_obtener')->name('ajustesinventario_obtener')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_series_documento', 'AjusteInventarioController@ajustesinventario_obtener_series_documento')->name('ajustesinventario_obtener_series_documento')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_ultimo_folio_serie_seleccionada', 'AjusteInventarioController@ajustesinventario_obtener_ultimo_folio_serie_seleccionada')->name('ajustesinventario_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_ultimo_id', 'AjusteInventarioController@ajustesinventario_obtener_ultimo_id')->name('ajustesinventario_obtener_ultimo_id')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_almacenes', 'AjusteInventarioController@ajustesinventario_obtener_almacenes')->name('ajustesinventario_obtener_almacenes')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_almacen_por_numero', 'AjusteInventarioController@ajustesinventario_obtener_almacen_por_numero')->name('ajustesinventario_obtener_almacen_por_numero')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_productos', 'AjusteInventarioController@ajustesinventario_obtener_productos')->name('ajustesinventario_obtener_productos')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_existencias_partida', 'AjusteInventarioController@ajustesinventario_obtener_existencias_partida')->name('ajustesinventario_obtener_existencias_partida')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_guardar', 'AjusteInventarioController@ajustesinventario_guardar')->name('ajustesinventario_guardar')->middleware('revisarpermisos:registros.ajustes.inventario.altas');
    Route::get('/ajustesinventario_verificar_baja', 'AjusteInventarioController@ajustesinventario_verificar_baja')->name('ajustesinventario_verificar_baja')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_alta_o_baja', 'AjusteInventarioController@ajustesinventario_alta_o_baja')->name('ajustesinventario_alta_o_baja')->middleware('revisarpermisos:registros.ajustes.inventario.bajas');
    Route::get('/ajustesinventario_obtener_nuevos_datos_fila', 'AjusteInventarioController@ajustesinventario_obtener_nuevos_datos_fila')->name('ajustesinventario_obtener_nuevos_datos_fila')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_ajuste', 'AjusteInventarioController@ajustesinventario_obtener_ajuste')->name('ajustesinventario_obtener_ajuste')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_guardar_modificacion', 'AjusteInventarioController@ajustesinventario_guardar_modificacion')->name('ajustesinventario_guardar_modificacion')->middleware('revisarpermisos:registros.ajustes.inventario.cambios');
    Route::get('/ajustesinventario_buscar_folio_string_like', 'AjusteInventarioController@ajustesinventario_buscar_folio_string_like')->name('ajustesinventario_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_generar_pdfs', 'AjusteInventarioController@ajustesinventario_generar_pdfs')->name('ajustesinventario_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_datos_envio_email', 'AjusteInventarioController@ajustesinventario_obtener_datos_envio_email')->name('ajustesinventario_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_enviar_pdfs_email', 'AjusteInventarioController@ajustesinventario_enviar_pdfs_email')->name('ajustesinventario_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_generar_pdfs_indiv/{documento}', 'AjusteInventarioController@ajustesinventario_generar_pdfs_indiv')->name('ajustesinventario_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_exportar_excel', 'AjusteInventarioController@ajustesinventario_exportar_excel')->name('ajustesinventario_exportar_excel')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_guardar_configuracion_tabla', 'AjusteInventarioController@ajustesinventario_guardar_configuracion_tabla')->name('ajustesinventario_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    //traspasos
    Route::get('/traspasos', 'TraspasoController@traspasos')->name('traspasos')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener', 'TraspasoController@traspasos_obtener')->name('traspasos_obtener')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_series_documento', 'TraspasoController@traspasos_obtener_series_documento')->name('traspasos_obtener_series_documento')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_ultimo_folio_serie_seleccionada', 'TraspasoController@traspasos_obtener_ultimo_folio_serie_seleccionada')->name('traspasos_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_ultimo_folio', 'TraspasoController@traspasos_obtener_ultimo_folio')->name('traspasos_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_almacenes', 'TraspasoController@traspasos_obtener_almacenes')->name('traspasos_obtener_almacenes')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_almacen_de_por_numero', 'TraspasoController@traspasos_obtener_almacen_de_por_numero')->name('traspasos_obtener_almacen_de_por_numero')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_almacenes_foraneos', 'TraspasoController@traspasos_obtener_almacenes_foraneos')->name('traspasos_obtener_almacenes_foraneos')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_almacen_a_por_numero', 'TraspasoController@traspasos_obtener_almacen_a_por_numero')->name('traspasos_obtener_almacen_a_por_numero')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_ordenes_trabajo', 'TraspasoController@traspasos_obtener_ordenes_trabajo')->name('traspasos_obtener_ordenes_trabajo')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_orden_trabajo_por_folio', 'TraspasoController@traspasos_obtener_orden_trabajo_por_folio')->name('traspasos_obtener_orden_trabajo_por_folio')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_productos', 'TraspasoController@traspasos_obtener_productos')->name('traspasos_obtener_productos')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_existencias_partida', 'TraspasoController@traspasos_obtener_existencias_partida')->name('traspasos_obtener_existencias_partida')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::post('/traspasos_guardar', 'TraspasoController@traspasos_guardar')->name('traspasos_guardar')->middleware('revisarpermisos:registros.traspasos.altas');
    Route::get('/traspasos_verificar_baja', 'TraspasoController@traspasos_verificar_baja')->name('traspasos_verificar_baja')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::post('/traspasos_alta_o_baja', 'TraspasoController@traspasos_alta_o_baja')->name('traspasos_alta_o_baja')->middleware('revisarpermisos:registros.traspasos.bajas');
    Route::get('/traspasos_obtener_traspaso', 'TraspasoController@traspasos_obtener_traspaso')->name('traspasos_obtener_traspaso')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_existencias_almacen_foraneo', 'TraspasoController@traspasos_obtener_existencias_almacen_foraneo')->name('traspasos_obtener_existencias_almacen_foraneo')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::post('/traspasos_guardar_modificacion', 'TraspasoController@traspasos_guardar_modificacion')->name('traspasos_guardar_modificacion')->middleware('revisarpermisos:registros.traspasos.cambios');
    Route::get('/traspasos_buscar_folio_string_like', 'TraspasoController@traspasos_buscar_folio_string_like')->name('traspasos_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::post('/traspasos_generar_pdfs', 'TraspasoController@traspasos_generar_pdfs')->name('traspasos_generar_pdfs')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_obtener_datos_envio_email', 'TraspasoController@traspasos_obtener_datos_envio_email')->name('traspasos_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::post('/traspasos_enviar_pdfs_email', 'TraspasoController@traspasos_enviar_pdfs_email')->name('traspasos_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_generar_pdfs_indiv/{documento}', 'TraspasoController@traspasos_generar_pdfs_indiv')->name('traspasos_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::get('/traspasos_exportar_excel', 'TraspasoController@traspasos_exportar_excel')->name('traspasos_exportar_excel')->middleware('revisaraccesomenu:menuregistrostraspasos');
    Route::post('/traspasos_guardar_configuracion_tabla', 'TraspasoController@traspasos_guardar_configuracion_tabla')->name('traspasos_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrostraspasos');
    //Remisiones
    Route::get('/remisiones', 'RemisionController@remisiones')->name('remisiones')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener', 'RemisionController@remisiones_obtener')->name('remisiones_obtener')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_series_documento', 'RemisionController@remisiones_obtener_series_documento')->name('remisiones_obtener_series_documento')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_ultimo_folio_serie_seleccionada', 'RemisionController@remisiones_obtener_ultimo_folio_serie_seleccionada')->name('remisiones_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_ultimo_folio', 'RemisionController@remisiones_obtener_ultimo_folio')->name('remisiones_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_clientes', 'RemisionController@remisiones_obtener_clientes')->name('remisiones_obtener_clientes')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_cliente_por_numero', 'RemisionController@remisiones_obtener_cliente_por_numero')->name('remisiones_obtener_cliente_por_numero')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_agente_por_numero', 'RemisionController@remisiones_obtener_agente_por_numero')->name('remisiones_obtener_agente_por_numero')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_almacen_por_numero', 'RemisionController@remisiones_obtener_almacen_por_numero')->name('remisiones_obtener_almacen_por_numero')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_agentes', 'RemisionController@remisiones_obtener_agentes')->name('remisiones_obtener_agentes')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_almacenes', 'RemisionController@remisiones_obtener_almacenes')->name('remisiones_obtener_almacenes')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_tipos_cliente', 'RemisionController@remisiones_obtener_tipos_cliente')->name('remisiones_obtener_tipos_cliente')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_tipos_unidad', 'RemisionController@remisiones_obtener_tipos_unidad')->name('remisiones_obtener_tipos_unidad')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_productos', 'RemisionController@remisiones_obtener_productos')->name('remisiones_obtener_productos')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_existencias_almacen', 'RemisionController@remisiones_obtener_existencias_almacen')->name('remisiones_obtener_existencias_almacen')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::post('/remisiones_guardar', 'RemisionController@remisiones_guardar')->name('remisiones_guardar')->middleware('revisarpermisos:registros.remisiones.altas');
    Route::get('/remisiones_verificar_baja', 'RemisionController@remisiones_verificar_baja')->name('remisiones_verificar_baja')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::post('/remisiones_alta_o_baja', 'RemisionController@remisiones_alta_o_baja')->name('remisiones_alta_o_baja')->middleware('revisarpermisos:registros.remisiones.bajas');
    Route::get('/remisiones_obtener_remision', 'RemisionController@remisiones_obtener_remision')->name('remisiones_obtener_remision')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::post('/remisiones_guardar_modificacion', 'RemisionController@remisiones_guardar_modificacion')->name('remisiones_guardar_modificacion')->middleware('revisarpermisos:registros.remisiones.cambios');
    Route::get('/remisiones_buscar_folio_string_like', 'RemisionController@remisiones_buscar_folio_string_like')->name('remisiones_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::post('/remisiones_generar_pdfs', 'RemisionController@remisiones_generar_pdfs')->name('remisiones_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_obtener_datos_envio_email', 'RemisionController@remisiones_obtener_datos_envio_email')->name('remisiones_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::post('/remisiones_enviar_pdfs_email', 'RemisionController@remisiones_enviar_pdfs_email')->name('remisiones_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_generar_pdfs_indiv/{documento}', 'RemisionController@remisiones_generar_pdfs_indiv')->name('remisiones_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::get('/remisiones_exportar_excel', 'RemisionController@remisiones_exportar_excel')->name('remisiones_exportar_excel')->middleware('revisaraccesomenu:menuregistrosremisiones');
    Route::post('/remisiones_guardar_configuracion_tabla', 'RemisionController@remisiones_guardar_configuracion_tabla')->name('remisiones_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosremisiones');
    //Facturas
    Route::get('/facturas', 'FacturaController@facturas')->name('facturas')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener', 'FacturaController@facturas_obtener')->name('facturas_obtener')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_ultimo_folio', 'FacturaController@facturas_obtener_ultimo_folio')->name('facturas_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrosfacturas');    
    Route::get('/facturas_obtener_tipos', 'FacturaController@facturas_obtener_tipos')->name('facturas_obtener_tipos')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_tipos_unidades', 'FacturaController@facturas_obtener_tipos_unidades')->name('facturas_obtener_tipos_unidades')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_clientes', 'FacturaController@facturas_obtener_clientes')->name('facturas_obtener_clientes')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_cliente_por_numero', 'FacturaController@facturas_obtener_cliente_por_numero')->name('facturas_obtener_cliente_por_numero')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_agentes', 'FacturaController@facturas_obtener_agentes')->name('facturas_obtener_agentes')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_agente_por_numero', 'FacturaController@facturas_obtener_agente_por_numero')->name('facturas_obtener_agente_por_numero')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_codigos_postales', 'FacturaController@facturas_obtener_codigos_postales')->name('facturas_obtener_codigos_postales')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_lugar_expedicion_por_clave', 'FacturaController@facturas_obtener_lugar_expedicion_por_clave')->name('facturas_obtener_lugar_expedicion_por_clave')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_regimenes_fiscales', 'FacturaController@facturas_obtener_regimenes_fiscales')->name('facturas_obtener_regimenes_fiscales')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_regimen_fiscal_por_clave', 'FacturaController@facturas_obtener_regimen_fiscal_por_clave')->name('facturas_obtener_regimen_fiscal_por_clave')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_tipos_relacion', 'FacturaController@facturas_obtener_tipos_relacion')->name('facturas_obtener_tipos_relacion')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_tipo_relacion_por_clave', 'FacturaController@facturas_obtener_tipo_relacion_por_clave')->name('facturas_obtener_tipo_relacion_por_clave')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_formas_pago', 'FacturaController@facturas_obtener_formas_pago')->name('facturas_obtener_formas_pago')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_forma_pago_por_clave', 'FacturaController@facturas_obtener_forma_pago_por_clave')->name('facturas_obtener_forma_pago_por_clave')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_metodos_pago', 'FacturaController@facturas_obtener_metodos_pago')->name('facturas_obtener_metodos_pago')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_metodo_pago_por_clave', 'FacturaController@facturas_obtener_metodo_pago_por_clave')->name('facturas_obtener_metodo_pago_por_clave')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_usos_cfdi', 'FacturaController@facturas_obtener_usos_cfdi')->name('facturas_obtener_usos_cfdi')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_uso_cfdi_por_clave', 'FacturaController@facturas_obtener_uso_cfdi_por_clave')->name('facturas_obtener_uso_cfdi_por_clave')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_residencias_fiscales', 'FacturaController@facturas_obtener_residencias_fiscales')->name('facturas_obtener_residencias_fiscales')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_residencia_fiscal_por_clave', 'FacturaController@facturas_obtener_residencia_fiscal_por_clave')->name('facturas_obtener_residencia_fiscal_por_clave')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_folios_fiscales', 'FacturaController@facturas_obtener_folios_fiscales')->name('facturas_obtener_folios_fiscales')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_ultimo_folio_serie_seleccionada', 'FacturaController@facturas_obtener_ultimo_folio_serie_seleccionada')->name('facturas_obtener_ultimo_folio_serie_seleccionada')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_datos_agente', 'FacturaController@facturas_obtener_datos_agente')->name('facturas_obtener_datos_agente')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_remisiones', 'FacturaController@facturas_obtener_remisiones')->name('facturas_obtener_remisiones')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_remision', 'FacturaController@facturas_obtener_remision')->name('facturas_obtener_remision')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_ordenes', 'FacturaController@facturas_obtener_ordenes')->name('facturas_obtener_ordenes')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_orden', 'FacturaController@facturas_obtener_orden')->name('facturas_obtener_orden')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_productos', 'FacturaController@facturas_obtener_productos')->name('facturas_obtener_productos')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_claves_productos', 'FacturaController@facturas_obtener_claves_productos')->name('facturas_obtener_claves_productos')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_claves_unidades', 'FacturaController@facturas_obtener_claves_unidades')->name('facturas_obtener_claves_unidades')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::post('/facturas_cargar_xml_uuid_relacionado', 'FacturaController@facturas_cargar_xml_uuid_relacionado')->name('facturas_cargar_xml_uuid_relacionado')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::post('/facturas_guardar', 'FacturaController@facturas_guardar')->name('facturas_guardar')->middleware('revisarpermisos:registros.facturas.altas');
    Route::get('/facturas_obtener_factura', 'FacturaController@facturas_obtener_factura')->name('facturas_obtener_factura')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::post('/facturas_guardar_modificacion', 'FacturaController@facturas_guardar_modificacion')->name('facturas_guardar_modificacion')->middleware('revisarpermisos:registros.facturas.cambios');
    Route::get('/facturas_verificar_si_continua_baja', 'FacturaController@facturas_verificar_si_continua_baja')->name('facturas_verificar_si_continua_baja')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::post('/facturas_alta_o_baja', 'FacturaController@facturas_alta_o_baja')->name('facturas_alta_o_baja')->middleware('revisarpermisos:registros.facturas.bajas');
    Route::get('/facturas_buscar_folio_string_like', 'FacturaController@facturas_buscar_folio_string_like')->name('facturas_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::post('/facturas_generar_pdfs', 'FacturaController@facturas_generar_pdfs')->name('facturas_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_obtener_datos_envio_email', 'FacturaController@facturas_obtener_datos_envio_email')->name('facturas_obtener_datos_envio_email')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::post('/facturas_enviar_pdfs_email', 'FacturaController@facturas_enviar_pdfs_email')->name('facturas_enviar_pdfs_email')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_generar_pdfs_indiv/{documento}', 'FacturaController@facturas_generar_pdfs_indiv')->name('facturas_generar_pdfs_indiv')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::get('/facturas_exportar_excel', 'FacturaController@facturas_exportar_excel')->name('facturas_exportar_excel')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::post('/facturas_guardar_configuracion_tabla', 'FacturaController@facturas_guardar_configuracion_tabla')->name('facturas_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosfacturas');
    //---///---///---///---///---/// INTEGRACION FACTURAPI ////----/////----/////----/////_----/////-----/////
    Route::get('/facturas_verificar_si_continua_timbrado', 'FacturaController@facturas_verificar_si_continua_timbrado')->name('facturas_verificar_si_continua_timbrado')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::post('/facturas_timbrar_factura', 'FacturaController@facturas_timbrar_factura')->name('facturas_timbrar_factura')->middleware('revisarpermisos:registros.facturas.timbrar');
    Route::get('/facturas_verificar_si_continua_baja_timbre', 'FacturaController@facturas_verificar_si_continua_baja_timbre')->name('facturas_verificar_si_continua_baja_timbre')->middleware('revisaraccesomenu:menuregistrosfacturas');
    Route::post('/facturas_baja_timbre', 'FacturaController@facturas_baja_timbre')->name('facturas_baja_timbre')->middleware('revisarpermisos:registros.facturas.cancelartimbres');
    /* -----------------------------------||||||||||||||||||||FIN REGISTROS||||||||||||||||||||||-------------------------------------*/
    /* -----------------------------------||||||||||||||||||||REPORTES||||||||||||||||||||||-------------------------------------*/
    //reporte diario de ventas
    Route::get('/reporte_diario_ventas', 'ReporteFacturaController@reporte_diario_ventas')->name('reporte_diario_ventas')->middleware('revisaraccesomenu:menureportesfacturasventasdiarias');
    Route::get('/generar_reporte_diario_ventas', 'ReporteFacturaController@generar_reporte_diario_ventas')->name('generar_reporte_diario_ventas')->middleware('revisaraccesomenu:menureportesfacturasventasdiarias');
    Route::get('/reporte_ventas_diarias_obtener_clientes', 'ReporteFacturaController@reporte_ventas_diarias_obtener_clientes')->name('reporte_ventas_diarias_obtener_clientes')->middleware('revisaraccesomenu:menureportesfacturasventasdiarias');
    Route::post('/generar_excel_reporte_diario_ventas', 'ReporteFacturaController@generar_excel_reporte_diario_ventas')->name('generar_excel_reporte_diario_ventas')->middleware('revisaraccesomenu:menureportesfacturasventasdiarias');
    //reporte caja chica
    Route::get('/reporte_caja_chica', 'ReporteCajaChicaController@reporte_caja_chica')->name('reporte_caja_chica')->middleware('revisaraccesomenu:menureportescomprascajachica');
    Route::get('/generar_reporte_caja_chica', 'ReporteCajaChicaController@generar_reporte_caja_chica')->name('generar_reporte_caja_chica')->middleware('revisaraccesomenu:menureportescomprascajachica');
    Route::get('/reporte_caja_chica_generar_formato_excel', 'ReporteCajaChicaController@reporte_caja_chica_generar_formato_excel')->name('reporte_caja_chica_generar_formato_excel')->middleware('revisaraccesomenu:menureportescomprascajachica');
    //reporte horas tecnico
    Route::get('/reporte_ordenes_trabajo_horas_tecnico', 'ReportesOrdenesTrabajoController@reporte_ordenes_trabajo_horas_tecnico')->name('reporte_ordenes_trabajo_horas_tecnico')->middleware('revisaraccesomenu:menureportesordenestrabajohorastecnico');
    Route::get('/generar_reporte_horas_tecnico', 'ReportesOrdenesTrabajoController@generar_reporte_horas_tecnico')->name('generar_reporte_horas_tecnico')->middleware('revisaraccesomenu:menureportesordenestrabajohorastecnico');
    Route::get('/reporte_horas_tecnico_obtener_tecnicos', 'ReportesOrdenesTrabajoController@reporte_horas_tecnico_obtener_tecnicos')->name('reporte_horas_tecnico_obtener_tecnicos')->middleware('revisaraccesomenu:menureportesordenestrabajohorastecnico');
    Route::get('/reporte_horas_tecnico_generar_formato_excel', 'ReportesOrdenesTrabajoController@reporte_horas_tecnico_generar_formato_excel')->name('reporte_horas_tecnico_generar_formato_excel')->middleware('revisaraccesomenu:menureportesordenestrabajohorastecnico');
    /* -----------------------------------||||||||||||||||||||FIN REPORTES||||||||||||||||||||||-------------------------------------*/
    /* -----------------------------------||||||||||||||||||||EMPRESA||||||||||||||||||||||-------------------------------------*/
    Route::get('/empresa', 'EmpresaController@empresa')->name('empresa');
    Route::get('/empresa_obtener_paises', 'EmpresaController@empresa_obtener_paises')->name('empresa_obtener_paises');
    Route::get('/empresa_obtener_estados', 'EmpresaController@empresa_obtener_estados')->name('empresa_obtener_estados');
    Route::get('/empresa_obtener_municipios', 'EmpresaController@empresa_obtener_municipios')->name('empresa_obtener_municipios');
    Route::get('/empresa_obtener_lugares_expedicion', 'EmpresaController@empresa_obtener_lugares_expedicion')->name('empresa_obtener_lugares_expedicion');
    Route::get('/empresa_obtener_regimenes_fiscales', 'EmpresaController@empresa_obtener_regimenes_fiscales')->name('empresa_obtener_regimenes_fiscales');
    Route::get('/empresa_obtener_monedas', 'EmpresaController@empresa_obtener_monedas')->name('empresa_obtener_monedas');
    Route::post('/utilerias_empresa_guardar_modificacion', 'EmpresaController@utilerias_empresa_guardar_modificacion')->name('utilerias_empresa_guardar_modificacion');
    Route::post('/empresa_guardar_modificacion_domicilio_fiscal', 'EmpresaController@empresa_guardar_modificacion_domicilio_fiscal')->name('empresa_guardar_modificacion_domicilio_fiscal');
    Route::post('/empresa_guardar_modificacion_lugar_expedicion', 'EmpresaController@empresa_guardar_modificacion_lugar_expedicion')->name('empresa_guardar_modificacion_lugar_expedicion');
    Route::post('/empresa_guardar_modificacion_configurar', 'EmpresaController@empresa_guardar_modificacion_configurar')->name('empresa_guardar_modificacion_configurar');
    Route::post('/empresa_guardar_modificacion_logo_y_tema', 'EmpresaController@empresa_guardar_modificacion_logo_y_tema')->name('empresa_guardar_modificacion_logo_y_tema');
    /* -----------------------------------||||||||||||||||||||FIN EMPRESA||||||||||||||||||||||-------------------------------------*/
    /* -----------------------------------||||||||||||||||||||CONFIGURACIONES Y PRUEBAS||||||||||||||||||||||-------------------------------------*/
    Route::get('/asignar_valores_por_defecto_busquedas_y_ordenamiento', 'PruebaController@asignar_valores_por_defecto_busquedas_y_ordenamiento')->name('asignar_valores_por_defecto_busquedas_y_ordenamiento');
    /* -----------------------------------||||||||||||||||||||FIN CONFIGURACIONES Y PRUEBAS||||||||||||||||||||||-------------------------------------*/

});









