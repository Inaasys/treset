<!--MUY IMPORTANTE NO QUITAR LOS ID A LOS HREF DE LOS MENU-->
<nav class="navbar {{$empresa->background_navbar}}" id="colornavbar">
  <div class="container-fluid">
    <div class="navbar-header">
      <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
      <a href="javascript:void(0);" class="bars"></a>
      <a class="navbar-brand" href="#"><img id="navbarlogotipoempresa" src="logotipo_empresa/{{$empresa->Logo}}" width="125" height="50"></a>
    </div>
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="navbar-collapse">
      <ul class="nav navbar-nav">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Registros <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="{{ route('ordenes_compra') }}" id="menuregistrosordenescompra">Ordenes de Compra <b>--> SHIFT+O</b></a></li>
            <li><a href="{{ route('compras') }}" id="menuregistroscompras">Compras <b>--> CTRL+S</b></a></li>
            <li><a href="{{ route('contrarecibos') }}"  id="menuregistroscontrarecibos">ContraRecibos</a></li> 
            <li class="dropdown-submenu">
              <a class="test" href="#">Cotizador TYT <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="{{ route('cotizaciones') }}" id="menuregistroscotizaciones">Cotizaciones</a></li>
                <li><a  href="{{ route('lista_precios_volvo') }}" id="menuregistroslistapreciosvolvo">Lista Precio Volvo</a></li>
                <li><a  href="{{ route('lista_precios_cummins') }}" id="menuregistroslistaprecioscummins">Lista Precio Cummins</a></li>
              </ul>
            </li>  
            <li class="dropdown-submenu">
              <a class="test" href="#">Cotizaciones <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="{{ route('cotizaciones_productos') }}" id="menuregistroscotizacionesproductos">Cotizaciones Productos</a></li>
                <li><a  href="{{ route('cotizaciones_servicios') }}" id="menuregistroscotizacionesservicios">Cotizaciones Servicios</a></li>
              </ul>
            </li>  
            <!--<li><a href="#" id="menuregistrospedidos">Pedidos</a></li>-->
            <li><a href="{{ route('remisiones') }}" id="menuregistrosremisiones">Remisiones <b>--> CTRL+R</b></a></li>
            <li><a href="{{ route('facturas') }}" id="menuregistrosfacturas">Facturas <b>--> CTRL+F</b></a></li>         
            
            <li class="dropdown-submenu">
              <a class="test" href="#">Producción<span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="{{ route('produccion') }}" id="menuregistrosproduccion">Producción <b>--> SHIFT+P</b></a></li>
              </ul>
            </li> 
            <li><a href="{{ route('requisiciones') }}" id="menuregistrosrequisiciones">Requisiciones <b>--> SHIFT+R</b></a></li> 
            <li><a href="{{ route('traspasos') }}" id="menuregistrostraspasos">Traspasos <b>--> SHIFT+T</b></a></li>                    
            <li><a href="{{ route('ordenes_trabajo') }}" id="menuregistrosordenestrabajo">Ordenes de Trabajo <b>--> ALT+O</b></a></li>
            <li><a href="{{ route('cuentas_por_pagar') }}" id="menuregistroscuentasxpagar">Cuentas por Pagar</a></li>
            <li><a href="{{ route('cuentas_por_cobrar') }}" id="menuregistroscuentasxcobrar">Cuentas por Cobrar</a></li>            
            <li><a href="{{ route('notas_credito_clientes') }}" id="menuregistrosnotascreditoclientes">Notas de Crédito Clientes</a></li>
            <li><a href="{{ route('notas_credito_proveedores') }}" id="menuregistrosnotascreditoproveedores">Notas de Crédito Proveedores</a></li>
            <li><a href="{{ route('carta_porte') }}" id="menuregistroscartasporte">Cartas Porte</a></li>
            <!--<li><a href="#" id="menuregistrosciclicos">Cíclicos</a></li>-->
            <li><a href="{{ route('asignacionherramienta') }}" id="menuregistrosasignacionherramienta">Asignación Herramienta</a></li>
            <li><a href="{{ route('prestamoherramienta') }}" id="menuregistrosprestamoherramienta">Prestamo Herramienta</a></li>
            <li><a href="{{ route('ajustesinventario') }}" id="menuregistrosajusteinventario">Ajustes de Inventario <b>--> CTRL+A</b></a></li>
            <li><a href="{{ route('firmardocumentos') }}" id="menuregistrosfirmardocumentos">Firmar Documentos </a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Catálogos <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="{{ route('clientes') }}" id="menucatalogoclientes">Clientes <b>--> ALT+C</b></a></li>
            <li><a href="{{ route('agentes') }}" id="menucatalogoagentes">Agentes</a></li>
            <li><a href="{{ route('proveedores') }}" id="menucatalogoproveedores">Proveedores <b>--> ALT+P</b></a></li>                  
            <li><a href="{{ route('almacenes') }}" id="menucatalogoalmacenes">Almacenes</a></li>                   
            <li><a href="{{ route('marcas') }}" id="menucatalogomarcas">Marcas</a></li>
            <li><a href="{{ route('lineas') }}" id="menucatalogolineas">Lineas</a></li>
            <li><a href="{{ route('productos') }}" id="menucatalogoproductos">Productos <b>--> CTRL+P</b></a></li>               
            <li><a href="{{ route('existencias') }}" id="menucatalogoexistencias">Existencias <b>--> CTRL+E</b></a></li>              
            <li><a href="{{ route('bancos') }}" id="menucatalogobancos">Bancos</a></li>              
            <li><a href="{{ route('tecnicos') }}" id="menucatalogotecnicos">Técnicos</a></li>
            <li><a href="{{ route('servicios') }}" id="menucatalogoservicios">Servicios</a></li>
            <li><a href="{{ route('vines') }}" id="menucatalogovines">Vines</a></li>           
            <li class="dropdown-submenu">
              <a class="test" href="#">Folios Fiscales<span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="{{ route('folios_comprobantes_facturas') }}" id="menucatalogofoliosfiscalesfoliosfacturas">Folios Facturas</a></li>
                <li><a href="{{ route('folios_comprobantes_notas') }}" id="menucatalogofoliosfiscalesfoliosnotas">Folios Notas de Crédito</a></li>
                <li><a href="{{ route('folios_comprobantes_pagos') }}" id="menucatalogofoliosfiscalesfoliospagos">Folios Pagos</a></li>
                <li><a href="{{ route('folios_comprobantes_cartasporte') }}" id="menucatalogofoliosfiscalesfolioscartasporte">Folios Cartas Porte</a></li>
              </ul>
            </li>
            <li><a href="{{ route('personal') }}" id="menucatalogopersonal">Personal</a></li>
            @if(Auth::user()->role_id == 1)
              <li><a href="{{ route('usuarios') }}">Usuarios</a></li>
            @endif
          </ul>
        </li>
        <!--<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Liberar <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#">Liberar Utilidad por Cliente y Marca</a></li>
            <li><a href="#">Liberar Utilidad por Producto</a></li>          
            <li><a href="#">Garantías</a></li>
            <li><a href="#">Garantías Detalles</a></li>
            <li><a href="#">Garantías Series</a></li>                
            <li><a href="#">Lista de Precios</a></li>                 
            <li><a href="#">Bitacora Seguimiento</a></li>
          </ul>
        </li>-->
        <!--<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">SAT <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#">Comprobantes</a></li>
            <li><a href="#">Comprobantes Descarga XML SAT</a></li>
            <li class="dropdown-submenu">
              <a class="test"  href="#">Catálogos Definidos por el SAT <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#">1. Aduana</a></li>
                <li class="dropdown-submenu">
                  <a class="test" href="#">2. Producto o Servicio <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li><a href="#">1. Producto o Servicio</a></li>
                    <li><a href="#">2. Producto o Servicio Clase</a></li>
                  </ul>
                </li>
                <li><a  href="#">3. Unidad</a></li>
                <li><a  href="#">4. Código Postal</a></li>
                <li><a  href="#">5. Forma de Pago</a></li>
                <li><a  href="#">6. Impuesto</a></li>
                <li><a  href="#">7. Método de Pago</a></li>
                <li><a  href="#">8. Moneda</a></li>
                <li><a  href="#">9. Número de Pedimento Aduana</a></li>
                <li><a  href="#">10. País</a></li>
                <li><a  href="#">11. Patente Aduanal</a></li>
                <li><a  href="#">12. Régimen Fiscal</a></li>
                <li><a  href="#">13. Tasa o Cuota</a></li>
                <li><a  href="#">14. Tipo de Comprobante</a></li>
                <li><a  href="#">15. Tipo Factor</a></li>
                <li><a  href="#">16. Tipo Relación</a></li>
                <li><a  href="#">17. Uso de CFDi</a></li>                         
                <li class="dropdown-submenu">
                  <a class="test" href="#">Comercio Exterior <span class="caret"></span></a>
                  <ul class="dropdown-menu " style="margin-top: -237px !important;">
                    <li><a href="#">1. Clave Pedimento</a></li>
                    <li><a href="#">2. Colonia</a></li>
                    <li><a href="#">3. Estado</a></li>
                    <li><a href="#">4. Fracción Arancelaria</a></li>
                    <li><a href="#">5. Incoterm</a></li>
                    <li><a href="#">6. Localidad</a></li>
                    <li><a href="#">7. Motivo Traslado</a></li>
                    <li><a href="#">8. Municipio</a></li>
                    <li><a href="#">9. Tipo Operación</a></li>
                    <li><a href="#">10. Unidad Aduana</a></li>
                  </ul>
                </li>
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test"  href="#">Catálogos Internos del Sistema <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#">Conceptos</a></li>
                <li><a  href="#">Departamentos</a></li>
                <li><a  href="#">Familia</a></li>
                <li><a  href="#">Obsoletos</a></li>
                <li><a  href="#">Piensa</a></li>
                <li><a  href="#">Tipos Cliente</a></li>
                <li><a  href="#">Tipos Proveedor</a></li>
                <li><a  href="#">Tipos Orden Trabajo</a></li>
                <li><a  href="#">Tipos Unidades</a></li>
              </ul>
            </li>            
            <li><a href="#">Tipos de Cambio</a></li>
          </ul>
        </li>-->
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reportes <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li class="dropdown-submenu">
              <a class="test"  href="#">Ordenes de Compra <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="{{route('reporte_relacion_ordenes_compra')}}" id="menureporterelacionordenescompra">Relación de Ordenes de Compra</a></li>
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test"  href="#">Compras <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="{{route('reporte_relacion_compras')}}" id="menureporterelacioncompras">Relación de Compras por Proveedor</a></li>                        
                <li><a  href="{{route('reporte_caja_chica')}}" id="menureportescomprascajachica">Caja Chica</a></li>   
                <!--<li><a  href="#">Proyección de Pagos a Proveedores</a></li>
                <li><a  href="#">Cartera Vencida a Proveedores</a></li>                      
                <li><a  href="#">Productos + Comprados</a></li>-->
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test" href="#">ContraRecibos <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="{{route('reporte_relacion_contrarecibos')}}" id="menureporterelacioncontrarecibos">Relación de ContraRecibos</a></li>
              </ul>
            </li>                 
            <li class="dropdown-submenu">
              <a class="test"  href="#">Cotizaciones <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="{{route('reporte_relacion_cotizaciones')}}" id="menureporterelacioncotizaciones">Relación de Cotizaciones</a></li>
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test"  href="#">Remisiones <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="{{route('reporte_relacion_remisiones')}}" id="menureporterelacionremisiones">Relación de Remisiones por Clientes</a></li>
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test"  href="#">Facturas <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="{{route('reporte_diario_ventas')}}" id="menureportesfacturasventasdiarias">Ventas Diarias</a></li>
                <li><a  href="{{route('reporte_facturas_ventas_cliente')}}" id="menureporterelacionfacturasventasclientes">Relación de Ventas por Clientes</a></li>
                <li><a  href="#" id="menureporterelacionfacturasventasagentes">Relación de Ventas por Agentes</a></li>
                <li><a  href="#" id="menureporterelacionfacturasventasmarcas">Relación de Ventas por Marcas</a></li>                       
                <!--<li><a  href="#">Proyección de Cobranza a Clientes</a></li>
                <li><a  href="#">Cartera Vencida de Clientes</a></li>                        
                <li><a  href="#">Productos + Vendidos</a></li>                        
                <li><a  href="#">Ventas Servicio</a></li>                       
                <li><a  href="#">Ventas Perdidas</a></li>   -->                    
                <li><a  href="{{route('reporte_antiguedad_saldos')}}" id="menureportefacturasantiguedadsaldos">Antiguedad de Saldos</a></li>                     
                <!--<li><a  href="#">Estado de Cuenta de Clientes</a></li> -->
              </ul>
            </li>            
            <li class="dropdown-submenu">
              <a class="test"  href="#">Producción <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#" id="menureporterelacionproduccion">Relación de Producción</a></li>
              </ul>
            </li>            
            <li class="dropdown-submenu">
              <a class="test"  href="#">Comprobantes <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#" id="menureporterelacioncomprobantes">Relación de Comprobantes</a></li>
              </ul>
            </li>           
            <li class="dropdown-submenu">
              <a class="test"  href="#">Requisiciones <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#" id="menureporterelacionrequisiciones">Relación de Requisiciones</a></li>
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test"  href="#">Ordenes de Trabajo <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <!--<li><a  href="#">Avance Unidades en Reparación</a></li>-->
                <li><a  href="{{route('reporte_ordenes_trabajo_horas_tecnico')}}" id="menureportesordenestrabajohorastecnico">Horas Técnico</a></li>
                <!--
                <li><a  href="#">Unidades Servicio</a></li>
                <li><a  href="#">Relación de Encuestas</a></li>
                <li><a  href="#">Precios Mano de Obra</a></li> --> 
              </ul>
            </li>             
            <li class="dropdown-submenu">
              <a class="test"  href="#">Cuentas x Cobrar <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#" id="menureporterelacioncxc">Relación de Cobranza a Clientes</a></li>                       
                <!--<li><a  href="#">Entrega de Facturas a Cobrar</a></li>-->
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test"  href="#">Cuentas x Pagar <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#" id="menureporterelacioncxp">Relación de Pagos a Proveedores</a></li>
              </ul>
            </li>                  
            <li class="dropdown-submenu">
              <a class="test"  href="#">Notas de Crédito Clientes <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#" id="menureporterelacionnotasclientes">Relación de Notas de Crédito Clientes </a></li>
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test"  href="#">Notas de Crédito Proveedores <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#" id="menureporterelacionnotasproveedores">Relación de Notas de Crédito Proveedores</a></li>
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test" href="#">Inventario <span class="caret"></span></a>
              <ul class="dropdown-menu " style="margin-top: -150px !important;">
                <li><a  href="#" id="menureportecostoinventario">Costo del Inventario </a></li>
                <li><a  href="#" id="menureportecostoinventarioparametros">Costo del Inventario (Costo Ultimo, Promedio y Más Alto)</a></li>
                <li><a  href="#" id="menureporteproductossincomprasyventas">Productos sin Movimiento de Compras y Ventas </a></li>
                <li><a  href="#" id="menureportemovimientosalinventario">Movimientos al Inventario </a></li>
                <!--<li><a  href="#">Productos Obsoletos </a></li>
                <li><a  href="#">Productos Reman (CORES)</a></li>
                <li><a  href="#">Existencias en Sucursales </a></li>-->
                <li><a  href="#" id="menureporteultimafechaycostosproductoscomprados">Ultima Fecha y Costos de Productos Comprados </a></li>
                <li><a  href="#" id="menureporteultimafechaypreciosproductosfacturados">Ultima Fecha y Precios de Productos Facturados </a></li>
                <!--<li><a  href="#">Lpa </a></li>-->
                <li><a  href="#" id="menureporteinventariomaximosyminimos">Máximos y Mínimos </a></li>
                <!--<li><a  href="#">Etiquetas </a></li>-->
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="test"  href="#">Bitácoras <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="#" id="menureportebitacorasdocumentosyclientes">Documentos y Clientes  </a></li>
              </ul>
            </li>             
            <li class="dropdown-submenu">
              <a class="test" href="#">Usuarios <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="#" id="menureportepermisosusuarios">Permisos de Usuarios  </a></li>
              </ul>
            </li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Utilerias <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <!--<li><a href="#">Visor de Archivos XML</a></li>       
            <li class="dropdown-submenu">
              <a class="test"  href="#">Importar y Predeterminar <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#">Catálogos Definidos del SAT</a></li>
                <li><a  href="#">Catálogos del Sistema</a></li>                
                <li><a  href="#">Comprobantes Descargados del SAT</a></li>            
                <li><a  href="#">Predeterminar Columnas</a></li>
              </ul>
            </li>                
            <li class="dropdown-submenu">
              <a class="test"  href="#">Mantenimiento <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a  href="#">Inventarios y Existencias de los Almacenes</a></li>
                <li><a  href="#">Saldos y Pagos de Clientes y Proveedores</a></li>
                <li><a  href="#">Actualizar Periodos en Documentos</a></li>
                <li><a  href="#">Actualizar al Ultimo Costo de Compra en Catálogo de Productos</a></li>                  
                <li><a  href="#">Actualizar Precios Netos a Usar en Punto de Venta de Utilidad Marcas</a></li>
                <li><a  href="#">Ingresar Precios Netos a Usar en Punto de Venta</a></li>
              </ul>
            </li>-->
            @if(Auth::user()->role_id == 1)
              <li><a href="{{ route('empresa') }}">Empresa</a></li>
              <li><a href="{{ route('errors_inaasys') }}"><b>LOGS-ERRORS</b></a></li>
              <li><a href="{{ route('sesiones') }}"><b>SESIONES</b></a></li>
            @endif
            <li><a href="{{ route('ayuda') }}">Ayuda</a></li>
            <li class="bg-deep-orange">
              <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <b>CERRAR SESIÓN</b>
              </a>
            </li>  
          </ul>
        </li>      
      </ul>
      <ul class="nav navbar-nav navbar-right infousuario">  
        <!-- Valores Dolar -->
        <li class="dropdown">
          <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
            <i class="material-icons">attach_money</i>
          </a>
          <ul class="dropdown-menu">
            <li class="header">VALOR DOLAR</li>
            <li class="body">
              <ul class="menu">
                <li>
                  <a href="javascript:void(0);">
                    <div class="icon-circle bg-grey">
                      <i class="material-icons">attach_money</i>
                    </div>
                    <div class="menu-info">
                      <h4 style="font-size:11px;">Valor Dolar Volvo : $ {{$ultimo_valor_tipo_cambio_volvo}}</h4>
                      <p style="font-size:9px;">
                        <i class="material-icons">update</i>Ultima Actualización {{$ultima_fecha_actualización_tipo_cambio_volvo}}
                      </p>
                    </div>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);">
                    <div class="icon-circle bg-red">
                      <i class="material-icons">attach_money</i>
                    </div>
                    <div class="menu-info">
                      <h4 style="font-size:11px;">Valor Dolar Cummins : $ 20.589000</h4>
                      <p style="font-size:9px;">
                        <i class="material-icons">update</i>Ultima Actualización {{$timbresegresonotastotalesactivosfacturapi}}
                      </p>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </li> 
        <!-- Notificaciones -->
        <li class="dropdown">
          <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
            <i class="material-icons">info</i>
          </a>
          <ul class="dropdown-menu">
            <li class="header">NOTIFICATIONS</li>
            <li class="body">
              <ul class="menu">
                <li>
                  <a href="javascript:void(0);">
                    <div class="icon-circle bg-light-green">
                      <i class="material-icons">description</i>
                    </div>
                    <div class="menu-info">
                      <h4>Timbres Ingreso - Facturas</h4>
                      <p>
                        <i class="material-icons">check_box</i>Activos {{$timbresingresofacturastotalesactivosfacturapi}}
                        <i class="material-icons">indeterminate_check_box</i>Cancelados {{$timbresingresofacturascanceladastotalesactivosfacturapi}}
                      </p>
                    </div>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);">
                    <div class="icon-circle bg-cyan">
                      <i class="material-icons">description</i>
                    </div>
                    <div class="menu-info">
                      <h4>Timbres Egreso - Notas</h4>
                      <p>
                        <i class="material-icons">check_box</i>Activos {{$timbresegresonotastotalesactivosfacturapi}}
                        <i class="material-icons">indeterminate_check_box</i>Cancelados {{$timbresegresonotascanceladastotalesactivosfacturapi}}
                      </p>
                    </div>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);">
                    <div class="icon-circle bg-red">
                      <i class="material-icons">description</i>
                    </div>
                    <div class="menu-info">
                      <h4><b>Timbres Pago - CXC</b></h4>
                      <p>
                        <i class="material-icons">check_box</i>Activos {{$timbrespagocxctotalesactivosfacturapi}}
                        <i class="material-icons">indeterminate_check_box</i>Cancelados {{$timbrespagocxccanceladastotalesactivosfacturapi}}
                      </p>
                    </div>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);">
                    <div class="icon-circle bg-orange">
                      <i class="material-icons">description</i>
                    </div>
                    <div class="menu-info">
                      <h4><b>Totales</b></h4>
                        <p>
                          <i class="material-icons">check_box</i>Activos {{$timbrestotalesactivosfacturapi}}
                          <i class="material-icons">indeterminate_check_box</i>Cancelados {{$timbrestotalescanceladosfacturapi}}
                        </p>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </li>   
        <li class="text-center">
          <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ Auth::user()->name }}</div>
          <div class="btn-group user-helper-dropdown">
            <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
            <ul class="dropdown-menu pull-right">
              <li><a href="{{ route('empresa') }}"><i class="material-icons">person</i>Perfil Empresa</a></li>
              <li>
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class="material-icons">input</i>Cerrar Sesión
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                  @csrf
                </form>
              </li>
            </ul>
          </div>                    
        </li>        
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>