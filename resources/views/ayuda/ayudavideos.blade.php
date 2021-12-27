@extends('plantilla_maestra')
@section('titulo')
  Ayuda
@endsection
@section('additionals_css')
    <!-- Wait Me Css -->
    <link href="plugins/waitme/waitMe.css" rel="stylesheet" />
    <!-- Animation Css -->
    <link href="plugins/animate-css/animate.css" rel="stylesheet" />
    <!-- JQuery Nestable Css -->
    <link href="plugins/nestable/jquery-nestable.css" rel="stylesheet" />
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <small>Da click sobre el proceso que requires ver</small>
                            </h2>
                        </div>
                        <div class="body">
                            <div class="clearfix m-b-20">
                                <div class="dd">
                                    <ol class="dd-list">
                                        <li class="dd-item dd-collapsed" data-id="1">
                                            <div class="dd-handle">Registros</div>
                                            <ol class="dd-list">
                                                <li class="dd-item dd-collapsed" data-id="2">
                                                    <div class="dd-handle">Ordenes de Compra</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="3">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="4">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="5">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                        <li class="dd-item" data-id="5">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Autorizar Orden</div>
                                                        </li>
                                                        <li class="dd-item" data-id="5">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Quitar Autorización</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="6">
                                                    <div class="dd-handle">Compras</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="7">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="8">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="9">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                        <li class="dd-item" data-id="9">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Generar Excel</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Contrarecibos</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Cotizador</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Cotizaciones Productos</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Cotizaciones Servicios</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Remisiones</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Formato Requisición TYT</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Facturas</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Producción</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Producir</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Requisiciones</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Traspasos</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Ordenes Trabajo</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Terminar OT</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Asignar Mano de Obra</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Cuentas Por Pagar</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Cuentas Por Cobrar</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Notas Crédito Clientes</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Notas Crédito Proveedores</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Asignación Herramienta</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Prestamo Herramienta</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="10">
                                                    <div class="dd-handle">Ajuste Inventario</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="11">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="12">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="13">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                            </ol>
                                        </li>
                                        <li class="dd-item dd-collapsed" data-id="14">
                                            <div class="dd-handle">Catalogos</div>
                                            <ol class="dd-list">
                                                <li class="dd-item dd-collapsed" data-id="15">
                                                    <div class="dd-handle">Clientes</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="16">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="17">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="18">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="19">
                                                    <div class="dd-handle">Agentes</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="20">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="21">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="22">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Proveedores</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Almacenes</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Marcas</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Lineas</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Productos</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Bancos</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Técnicos</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Servicios</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Vines</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Folios Fiscales Facturas</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Folios Fiscales Notas</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="23">
                                                    <div class="dd-handle">Folios Fiscales Pagos</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="24">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Altas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="25">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Bajas</div>
                                                        </li>
                                                        <li class="dd-item" data-id="26">
                                                            <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4');"><i class="material-icons">play_circle_filled</i> Cambios</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                            </ol>
                                        </li>
                                        <li class="dd-item dd-collapsed" data-id="27">
                                            <div class="dd-handle">Reporte</div>
                                            <ol class="dd-list">
                                                <li class="dd-item dd-collapsed" data-id="28">
                                                    <div class="dd-handle">Ordenes de Compra</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="29">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="30">
                                                    <div class="dd-handle">Compras</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="31">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Contrarecibos</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Cotizaciones</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Remisiones</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Facturas</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Produccion</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Comprobantes</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Requisiciones</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Ordenes de Trabajo</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Cuentas Por Cobrar</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Cuentas Por Pagar</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Notas Crédito Clientes</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Notas Crédito Proveedores</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Inventario</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                                <li class="dd-item dd-collapsed" data-id="32">
                                                    <div class="dd-handle">Bitacoras</div>
                                                    <ol class="dd-list">
                                                        <li class="dd-item" data-id="33">
                                                            <div class="dd-nodrag"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                        </li>
                                                    </ol>
                                                </li>
                                            </ol>
                                        </li>
                                        <li class="dd-item dd-collapsed" data-id="34">
                                            <div class="dd-handle">Generar Excel Documentos</div>
                                            <ol class="dd-list">
                                                <li class="dd-item" data-id="35">
                                                    <div class="dd-nodrag" onclick="mostrarvideo('exportardatosexcel.mp4', 'Exportar datos a excel');"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                </li>
                                            </ol>
                                        </li>
                                        <li class="dd-item dd-collapsed" data-id="36">
                                            <div class="dd-handle">Configurar Tabla Documentos</div>
                                            <ol class="dd-list">
                                                <li class="dd-item" data-id="37">
                                                    <div class="dd-nodrag" onclick="mostrarvideo('configuraciontablas.mp4', 'Configuración Tablas de Documentos');"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                </li>
                                            </ol>
                                        </li>
                                        <li class="dd-item dd-collapsed" data-id="38">
                                            <div class="dd-handle">Generar PDF Documentos</div>
                                            <ol class="dd-list">
                                                <li class="dd-item" data-id="39">
                                                    <div class="dd-nodrag" onclick="mostrarvideo('generardocumentopdf.mp4', 'Generar Documento PDF');"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                </li>
                                            </ol>
                                        </li>
                                        <li class="dd-item dd-collapsed" data-id="40">
                                            <div class="dd-handle">Enviar PDF Documentos</div>
                                            <ol class="dd-list">
                                                <li class="dd-item" data-id="41">
                                                    <div class="dd-nodrag" onclick="mostrarvideo('enviardocumentosporemail.mp4', 'Enviar documentos PDF por email');"><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                </li>
                                            </ol>
                                        </li>
                                        <li class="dd-item dd-collapsed" data-id="42" >
                                            <div class="dd-handle">Búsqueda por columna en Documentos</div>
                                            <ol class="dd-list">
                                                <li class="dd-item" data-id="43">
                                                    <div class="dd-nodrag" onclick="mostrarvideo('busquedaporcolumna.mp4', 'Búsqueda por columna en Documentos');" ><i class="material-icons">play_circle_filled</i> Ver video</div>
                                                </li>
                                            </ol>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9" id="divprincipalvideosayuda">
                    <h2 id="titulovideoayuda" class="font-bold col-blue-grey"></h2>
                    <video class="video-fluid z-depth-1" autoplay loop controls muted style="width: 100%;height: auto;">
                        <source src="#" type="video/mp4" id="divvideosayuda" />
                    </video>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('additionals_js')
    <script>
        /*urls y variables renderizadas con blade*/
        var mayusculas_sistema = '{{$mayusculas_sistema}}';
        var numerodecimales = '{{$numerodecimales}}';
        var numerocerosconfigurados = '{{$numerocerosconfigurados}}';
        var numerocerosconfiguradosinputnumberstep = '{{$numerocerosconfiguradosinputnumberstep}}';
        var urlvideos = '{{asset("videosayuda/")}}/';
    </script>
    <script src="js/toastr/toastr.min.js"></script>
    <!-- scripit init-->
    <script src="js/toastr/toastr.init.js"></script>
    <script src="js/parsley/parsley.min.js"></script>
    <!-- Cambiar idioma de parsley -->
    <script src="js/parsley/i18n/es.js"></script>  
    <!-- Waves Effect Plugin Js -->
    <script src="plugins/node-waves/waves.js"></script>
    <!-- Jquery Nestable ORDERNAR COLUMNAS TABLAS -->
    <script src="plugins/nestable/jquery.nestable.js"></script>
    <!-- Custom Js -->
    <script src="js/admin.js"></script>
    <script src="js/pages/ui/sortable-nestable.js"></script>
    <script src="scripts_inaasys/ayuda/ayudavideos.js"></script>
@endsection