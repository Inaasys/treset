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
    //Route::get('/bancos_obtener_ultimo_numero', 'FolioComprobanteFacturaController@bancos_obtener_ultimo_numero')->name('bancos_obtener_ultimo_numero');
    //Route::post('/bancos_guardar', 'FolioComprobanteFacturaController@bancos_guardar')->name('bancos_guardar');
    //Route::post('/bancos_alta_o_baja', 'FolioComprobanteFacturaController@bancos_alta_o_baja')->name('bancos_alta_o_baja');
    //Route::get('/bancos_obtener_banco', 'FolioComprobanteFacturaController@bancos_obtener_banco')->name('bancos_obtener_banco');
    //Route::post('/bancos_guardar_modificacion', 'FolioComprobanteFacturaController@bancos_guardar_modificacion')->name('bancos_guardar_modificacion');  
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
    /* -----------------------------------||||||||||||||||||||FIN CATALOGOS||||||||||||||||||||||-------------------------------------*/

    /* -----------------------------------||||||||||||||||||||REGISTROS||||||||||||||||||||||-------------------------------------*/
    //Ordenes de Compra
    Route::get('/ordenes_compra', 'OrdenCompraController@ordenes_compra')->name('ordenes_compra')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener', 'OrdenCompraController@ordenes_compra_obtener')->name('ordenes_compra_obtener')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_ultimo_folio', 'OrdenCompraController@ordenes_compra_obtener_ultimo_folio')->name('ordenes_compra_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_tipos_ordenes_compra', 'OrdenCompraController@ordenes_compra_obtener_tipos_ordenes_compra')->name('ordenes_compra_obtener_tipos_ordenes_compra')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_proveedores', 'OrdenCompraController@ordenes_compra_obtener_proveedores')->name('ordenes_compra_obtener_proveedores')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_almacenes', 'OrdenCompraController@ordenes_compra_obtener_almacenes')->name('ordenes_compra_obtener_almacenes')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_obtener_productos', 'OrdenCompraController@ordenes_compra_obtener_productos')->name('ordenes_compra_obtener_productos')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_guardar', 'OrdenCompraController@ordenes_compra_guardar')->name('ordenes_compra_guardar')->middleware('revisarpermisos:registros.ordenescompra.altas');
    Route::post('/ordenes_compra_autorizar', 'OrdenCompraController@ordenes_compra_autorizar')->name('ordenes_compra_autorizar')->middleware('revisarpermisos:registros.ordenescompra.autorizar');
    Route::get('/ordenes_compra_verificar_uso_en_modulos', 'OrdenCompraController@ordenes_compra_verificar_uso_en_modulos')->name('ordenes_compra_verificar_uso_en_modulos')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_alta_o_baja', 'OrdenCompraController@ordenes_compra_alta_o_baja')->name('ordenes_compra_alta_o_baja')->middleware('revisarpermisos:registros.ordenescompra.bajas');
    Route::get('/ordenes_compra_obtener_orden_compra', 'OrdenCompraController@ordenes_compra_obtener_orden_compra')->name('ordenes_compra_obtener_orden_compra')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_guardar_modificacion', 'OrdenCompraController@ordenes_compra_guardar_modificacion')->name('ordenes_compra_guardar_modificacion')->middleware('revisarpermisos:registros.ordenescompra.cambios'); 
    Route::get('/ordenes_compra_buscar_folio_string_like', 'OrdenCompraController@ordenes_compra_buscar_folio_string_like')->name('ordenes_compra_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_generar_pdfs', 'OrdenCompraController@ordenes_compra_generar_pdfs')->name('ordenes_compra_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::get('/ordenes_compra_exportar_excel', 'OrdenCompraController@ordenes_compra_exportar_excel')->name('ordenes_compra_exportar_excel')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    Route::post('/ordenes_compra_guardar_configuracion_tabla', 'OrdenCompraController@ordenes_compra_guardar_configuracion_tabla')->name('ordenes_compra_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosordenescompra');
    //Compras
    Route::get('/compras', 'CompraController@compras')->name('compras')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener', 'CompraController@compras_obtener')->name('compras_obtener')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_ultimo_folio', 'CompraController@compras_obtener_ultimo_folio')->name('compras_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_tipos_ordenes_compra', 'CompraController@compras_obtener_tipos_ordenes_compra')->name('compras_obtener_tipos_ordenes_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_cargar_xml_alta', 'CompraController@compras_cargar_xml_alta')->name('compras_cargar_xml_alta')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_ordenes_compra', 'CompraController@compras_obtener_ordenes_compra')->name('compras_obtener_ordenes_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_proveedores', 'CompraController@compras_obtener_proveedores')->name('compras_obtener_proveedores')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_almacenes', 'CompraController@compras_obtener_almacenes')->name('compras_obtener_almacenes')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_orden_compra', 'CompraController@compras_obtener_orden_compra')->name('compras_obtener_orden_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_departamentos', 'CompraController@compras_obtener_departamentos')->name('compras_obtener_departamentos')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_claves_productos', 'CompraController@compras_obtener_claves_productos')->name('compras_obtener_claves_productos')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_claves_unidades', 'CompraController@compras_obtener_claves_unidades')->name('compras_obtener_claves_unidades')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_guardar', 'CompraController@compras_guardar')->name('compras_guardar')->middleware('revisarpermisos:registros.compras.altas');
    Route::get('/compras_obtener_movimientos_compra', 'CompraController@compras_obtener_movimientos_compra')->name('compras_obtener_movimientos_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_compra', 'CompraController@compras_obtener_compra')->name('compras_obtener_compra')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_obtener_existencias_partida', 'CompraController@compras_obtener_existencias_partida')->name('compras_obtener_existencias_partida')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_guardar_modificacion', 'CompraController@compras_guardar_modificacion')->name('compras_guardar_modificacion')->middleware('revisarpermisos:registros.compras.cambios');
    Route::get('/compras_verificar_uso_en_modulos', 'CompraController@compras_verificar_uso_en_modulos')->name('compras_verificar_uso_en_modulos')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_alta_o_baja', 'CompraController@compras_alta_o_baja')->name('compras_alta_o_baja')->middleware('revisarpermisos:registros.compras.bajas');
    Route::get('/compras_buscar_folio_string_like', 'CompraController@compras_buscar_folio_string_like')->name('compras_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_generar_pdfs', 'CompraController@compras_generar_pdfs')->name('compras_generar_pdfs')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::get('/compras_exportar_excel', 'CompraController@compras_exportar_excel')->name('compras_exportar_excel')->middleware('revisaraccesomenu:menuregistroscompras');
    Route::post('/compras_guardar_configuracion_tabla', 'CompraController@compras_guardar_configuracion_tabla')->name('compras_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistroscompras');
    //Ordenes de Trabajo
    Route::get('/ordenes_trabajo', 'OrdenTrabajoController@ordenes_trabajo')->name('ordenes_trabajo')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener', 'OrdenTrabajoController@ordenes_trabajo_obtener')->name('ordenes_trabajo_obtener')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_ultimo_folio', 'OrdenTrabajoController@ordenes_trabajo_obtener_ultimo_folio')->name('ordenes_trabajo_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_tipos_ordenes_trabajo', 'OrdenTrabajoController@ordenes_trabajo_obtener_tipos_ordenes_trabajo')->name('ordenes_trabajo_obtener_tipos_ordenes_trabajo')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_tipos_unidades', 'OrdenTrabajoController@ordenes_trabajo_obtener_tipos_unidades')->name('ordenes_trabajo_obtener_tipos_unidades')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_fecha_actual_datetimelocal', 'OrdenTrabajoController@ordenes_trabajo_obtener_fecha_actual_datetimelocal')->name('ordenes_trabajo_obtener_fecha_actual_datetimelocal')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_clientes_facturaa', 'OrdenTrabajoController@ordenes_trabajo_obtener_clientes_facturaa')->name('ordenes_trabajo_obtener_clientes_facturaa')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_clientes_delcliente', 'OrdenTrabajoController@ordenes_trabajo_obtener_clientes_delcliente')->name('ordenes_trabajo_obtener_clientes_delcliente')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_agentes', 'OrdenTrabajoController@ordenes_trabajo_obtener_agentes')->name('ordenes_trabajo_obtener_agentes')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::get('/ordenes_trabajo_obtener_vines', 'OrdenTrabajoController@ordenes_trabajo_obtener_vines')->name('ordenes_trabajo_obtener_vines')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
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
    Route::post('/ordenes_trabajo_generar_pdfs', 'OrdenTrabajoController@ordenes_trabajo_generar_pdfs')->name('ordenes_trabajo_generar_pdfs')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/ordenes_trabajo_exportar_excel', 'OrdenTrabajoController@ordenes_trabajo_exportar_excel')->name('ordenes_trabajo_exportar_excel')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    Route::post('/ordenes_trabajo_guardar_configuracion_tabla', 'OrdenTrabajoController@ordenes_trabajo_guardar_configuracion_tabla')->name('ordenes_trabajo_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosordenestrabajo');
    //Cuentas por Pagar
    Route::get('/cuentas_por_pagar', 'CuentasPorPagarController@cuentas_por_pagar')->name('cuentas_por_pagar')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener', 'CuentasPorPagarController@cuentas_por_pagar_obtener')->name('cuentas_por_pagar_obtener')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_ultimo_folio', 'CuentasPorPagarController@cuentas_por_pagar_obtener_ultimo_folio')->name('cuentas_por_pagar_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_proveedores', 'CuentasPorPagarController@cuentas_por_pagar_obtener_proveedores')->name('cuentas_por_pagar_obtener_proveedores')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_bancos', 'CuentasPorPagarController@cuentas_por_pagar_obtener_bancos')->name('cuentas_por_pagar_obtener_bancos')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_obtener_compras_proveedor', 'CuentasPorPagarController@cuentas_por_pagar_obtener_compras_proveedor')->name('cuentas_por_pagar_obtener_compras_proveedor')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::post('/cuentas_por_pagar_guardar', 'CuentasPorPagarController@cuentas_por_pagar_guardar')->name('cuentas_por_pagar_guardar')->middleware('revisarpermisos:registros.cuentas.x.pagar.altas');
    Route::post('/cuentas_por_pagar_baja', 'CuentasPorPagarController@cuentas_por_pagar_baja')->name('cuentas_por_pagar_baja')->middleware('revisarpermisos:registros.cuentas.x.pagar.bajas');
    Route::get('/cuentas_por_pagar_obtener_cuenta_por_pagar', 'CuentasPorPagarController@cuentas_por_pagar_obtener_cuenta_por_pagar')->name('cuentas_por_pagar_obtener_cuenta_por_pagar')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_buscar_folio_string_like', 'CuentasPorPagarController@cuentas_por_pagar_buscar_folio_string_like')->name('cuentas_por_pagar_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::post('/cuentas_por_pagar_generar_pdfs', 'CuentasPorPagarController@cuentas_por_pagar_generar_pdfs')->name('cuentas_por_pagar_generar_pdfs')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::get('/cuentas_por_pagar_exportar_excel', 'CuentasPorPagarController@cuentas_por_pagar_exportar_excel')->name('cuentas_por_pagar_exportar_excel')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    Route::post('/cuentas_por_pagar_guardar_configuracion_tabla', 'CuentasPorPagarController@cuentas_por_pagar_guardar_configuracion_tabla')->name('cuentas_por_pagar_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistroscuentasxpagar');
    //Contrarecibos
    Route::get('/contrarecibos', 'ContraRecibosController@contrarecibos')->name('contrarecibos')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener', 'ContraRecibosController@contrarecibos_obtener')->name('contrarecibos_obtener')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_ultimo_folio', 'ContraRecibosController@contrarecibos_obtener_ultimo_folio')->name('contrarecibos_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_proveedores', 'ContraRecibosController@contrarecibos_obtener_proveedores')->name('contrarecibos_obtener_proveedores')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_obtener_compras_proveedor', 'ContraRecibosController@contrarecibos_obtener_compras_proveedor')->name('contrarecibos_obtener_compras_proveedor')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::post('/contrarecibos_guardar', 'ContraRecibosController@contrarecibos_guardar')->name('contrarecibos_guardar')->middleware('revisarpermisos:registros.contrarecibos.altas');
    Route::post('/contrarecibos_baja', 'ContraRecibosController@contrarecibos_baja')->name('contrarecibos_baja')->middleware('revisarpermisos:registros.contrarecibos.bajas');
    Route::get('/contrarecibos_obtener_contrarecibo', 'ContraRecibosController@contrarecibos_obtener_contrarecibo')->name('contrarecibos_obtener_contrarecibo')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_buscar_folio_string_like', 'ContraRecibosController@contrarecibos_buscar_folio_string_like')->name('contrarecibos_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::post('/contrarecibos_generar_pdfs', 'ContraRecibosController@contrarecibos_generar_pdfs')->name('contrarecibos_generar_pdfs')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::get('/contrarecibos_exportar_excel', 'ContraRecibosController@contrarecibos_exportar_excel')->name('contrarecibos_exportar_excel')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    Route::post('/contrarecibos_guardar_configuracion_tabla', 'ContraRecibosController@contrarecibos_guardar_configuracion_tabla')->name('contrarecibos_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistroscontrarecibos');
    //Notas de Crédito Proveedores
    Route::get('/notas_credito_proveedores', 'NotasCreditoProveedoresController@notas_credito_proveedores')->name('notas_credito_proveedores')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener')->name('notas_credito_proveedores_obtener')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_ultimo_folio', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_ultimo_folio')->name('notas_credito_proveedores_obtener_ultimo_folio')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_proveedores', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_proveedores')->name('notas_credito_proveedores_obtener_proveedores')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_almacenes', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_almacenes')->name('notas_credito_proveedores_obtener_almacenes')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_compras', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_compras')->name('notas_credito_proveedores_obtener_compras')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_obtener_compra', 'NotasCreditoProveedoresController@notas_credito_proveedores_obtener_compra')->name('notas_credito_proveedores_obtener_compra')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedor_obtener_codigos_compra', 'NotasCreditoProveedoresController@notas_credito_proveedor_obtener_codigos_compra')->name('notas_credito_proveedor_obtener_codigos_compra')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedor_cargar_xml_alta', 'NotasCreditoProveedoresController@notas_credito_proveedor_cargar_xml_alta')->name('notas_credito_proveedor_cargar_xml_alta')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedor_obtener_existencias_partida', 'NotasCreditoProveedoresController@notas_credito_proveedor_obtener_existencias_partida')->name('notas_credito_proveedor_obtener_existencias_partida')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedor_guardar', 'NotasCreditoProveedoresController@notas_credito_proveedor_guardar')->name('notas_credito_proveedor_guardar')->middleware('revisarpermisos:registros.notas.credito.proveedores.altas');
    Route::get('/notas_credito_proveedores_buscar_folio_string_like', 'NotasCreditoProveedoresController@notas_credito_proveedores_buscar_folio_string_like')->name('notas_credito_proveedores_buscar_folio_string_like')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedores_generar_pdfs', 'NotasCreditoProveedoresController@notas_credito_proveedores_generar_pdfs')->name('notas_credito_proveedores_generar_pdfs')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::get('/notas_credito_proveedores_exportar_excel', 'NotasCreditoProveedoresController@notas_credito_proveedores_exportar_excel')->name('notas_credito_proveedores_exportar_excel')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    Route::post('/notas_credito_proveedor_guardar_configuracion_tabla', 'NotasCreditoProveedoresController@notas_credito_proveedor_guardar_configuracion_tabla')->name('notas_credito_proveedor_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosnotascreditoproveedores');
    //Asignación de herramienta
    Route::get('/asignacionherramienta', 'AsignacionHerramientaController@asignacionherramienta')->name('asignacionherramienta')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener', 'AsignacionHerramientaController@asignacion_herramienta_obtener')->name('asignacion_herramienta_obtener')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_ultimo_id', 'AsignacionHerramientaController@asignacion_herramienta_obtener_ultimo_id')->name('asignacion_herramienta_obtener_ultimo_id')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_personal_recibe', 'AsignacionHerramientaController@asignacion_herramienta_obtener_personal_recibe')->name('asignacion_herramienta_obtener_personal_recibe')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_personal_entrega', 'AsignacionHerramientaController@asignacion_herramienta_obtener_personal_entrega')->name('asignacion_herramienta_obtener_personal_entrega')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_herramienta', 'AsignacionHerramientaController@asignacion_herramienta_obtener_herramienta')->name('asignacion_herramienta_obtener_herramienta')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::get('/asignacion_herramienta_obtener_existencias_almacen', 'AsignacionHerramientaController@asignacion_herramienta_obtener_existencias_almacen')->name('asignacion_herramienta_obtener_existencias_almacen')->middleware('revisaraccesomenu:menuregistrosasignacionherramienta');
    Route::post('/asignacion_herramienta_guardar', 'AsignacionHerramientaController@asignacion_herramienta_guardar')->name('asignacion_herramienta_guardar')->middleware('revisarpermisos:registros.asignacion.herramienta.altas');
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
    Route::get('/prestamo_herramienta_obtener_ultimo_id', 'PrestamoHerramientaController@prestamo_herramienta_obtener_ultimo_id')->name('prestamo_herramienta_obtener_ultimo_id')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_personal', 'PrestamoHerramientaController@prestamo_herramienta_obtener_personal')->name('prestamo_herramienta_obtener_personal')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_herramienta_personal', 'PrestamoHerramientaController@prestamo_herramienta_obtener_herramienta_personal')->name('prestamo_herramienta_obtener_herramienta_personal')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_detalle_asignacion_seleccionada', 'PrestamoHerramientaController@prestamo_herramienta_obtener_detalle_asignacion_seleccionada')->name('prestamo_herramienta_obtener_detalle_asignacion_seleccionada')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_fecha_datetimelocal', 'PrestamoHerramientaController@prestamo_herramienta_obtener_fecha_datetimelocal')->name('prestamo_herramienta_obtener_fecha_datetimelocal')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
    Route::get('/prestamo_herramienta_obtener_personal_recibe', 'PrestamoHerramientaController@prestamo_herramienta_obtener_personal_recibe')->name('prestamo_herramienta_obtener_personal_recibe')->middleware('revisaraccesomenu:menuregistrosprestamoherramienta');
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
    Route::get('/cotizaciones_obtener_ultimo_id', 'CotizacionController@cotizaciones_obtener_ultimo_id')->name('cotizaciones_obtener_ultimo_id')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_obtener_remisiones', 'CotizacionController@cotizaciones_obtener_remisiones')->name('cotizaciones_obtener_remisiones')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_obtener_remision', 'CotizacionController@cotizaciones_obtener_remision')->name('cotizaciones_obtener_remision')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::post('/cotizaciones_guardar', 'CotizacionController@cotizaciones_guardar')->name('cotizaciones_guardar')->middleware('revisarpermisos:registros.cotizaciones.altas');
    Route::post('/cotizaciones_alta_o_baja', 'CotizacionController@cotizaciones_alta_o_baja')->name('cotizaciones_alta_o_baja')->middleware('revisarpermisos:registros.cotizaciones.bajas');
    Route::get('/cotizaciones_obtener_cotizacion', 'CotizacionController@cotizaciones_obtener_cotizacion')->name('cotizaciones_obtener_cotizacion')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::post('/cotizaciones_guardar_modificacion', 'CotizacionController@cotizaciones_guardar_modificacion')->name('cotizaciones_guardar_modificacion')->middleware('revisarpermisos:registros.cotizaciones.cambios');
    Route::get('/cotizaciones_exportar_excel', 'CotizacionController@cotizaciones_exportar_excel')->name('cotizaciones_exportar_excel')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::get('/cotizaciones_crear_formato_excel/{cotizacion}', 'CotizacionController@cotizaciones_crear_formato_excel')->name('cotizaciones_crear_formato_excel')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    Route::post('/cotizaciones_guardar_configuracion_tabla', 'CotizacionController@cotizaciones_guardar_configuracion_tabla')->name('cotizaciones_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistroscotizaciones');
    //Ajuste Inventario
    Route::get('/ajustesinventario', 'AjusteInventarioController@ajustesinventario')->name('ajustesinventario')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener', 'AjusteInventarioController@ajustesinventario_obtener')->name('ajustesinventario_obtener')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_ultimo_id', 'AjusteInventarioController@ajustesinventario_obtener_ultimo_id')->name('ajustesinventario_obtener_ultimo_id')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_almacenes', 'AjusteInventarioController@ajustesinventario_obtener_almacenes')->name('ajustesinventario_obtener_almacenes')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_productos', 'AjusteInventarioController@ajustesinventario_obtener_productos')->name('ajustesinventario_obtener_productos')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::get('/ajustesinventario_obtener_existencias_por_codigo_y_almacen', 'AjusteInventarioController@ajustesinventario_obtener_existencias_por_codigo_y_almacen')->name('ajustesinventario_obtener_existencias_por_codigo_y_almacen')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_guardar', 'AjusteInventarioController@ajustesinventario_guardar')->name('ajustesinventario_guardar')->middleware('revisarpermisos:registros.ajustes.inventario.altas');
    Route::get('/ajustesinventario_verificar_baja', 'AjusteInventarioController@ajustesinventario_verificar_baja')->name('ajustesinventario_verificar_baja')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_alta_o_baja', 'AjusteInventarioController@ajustesinventario_alta_o_baja')->name('ajustesinventario_alta_o_baja')->middleware('revisarpermisos:registros.ajustes.inventario.bajas');
    Route::get('/ajustesinventario_obtener_ajuste', 'AjusteInventarioController@ajustesinventario_obtener_ajuste')->name('ajustesinventario_obtener_ajuste')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_guardar_modificacion', 'AjusteInventarioController@ajustesinventario_guardar_modificacion')->name('ajustesinventario_guardar_modificacion')->middleware('revisarpermisos:registros.ajustes.inventario.cambios');
    Route::get('/ajustesinventario_exportar_excel', 'AjusteInventarioController@ajustesinventario_exportar_excel')->name('ajustesinventario_exportar_excel')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
    Route::post('/ajustesinventario_guardar_configuracion_tabla', 'AjusteInventarioController@ajustesinventario_guardar_configuracion_tabla')->name('ajustesinventario_guardar_configuracion_tabla')->middleware('revisaraccesomenu:menuregistrosajusteinventario');
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
    Route::post('/utilerias_empresa_guardar_modificacion', 'EmpresaController@utilerias_empresa_guardar_modificacion')->name('utilerias_empresa_guardar_modificacion');
    /* -----------------------------------||||||||||||||||||||FIN EMPRESA||||||||||||||||||||||-------------------------------------*/



});









