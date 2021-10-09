<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="images/iconapp1.png" sizes="16x16">
  <link rel="icon" type="image/png" href="images/iconapp1.png" sizes="32x32">
  <meta name="robots" content="noindex, nofollow">
  <title>INAASYS LOGS - ERRORES</title>
  <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
        crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
  <style>
    body {
      padding: 25px;
    }
    
    h1 {
      font-size: 1.5em;
      margin-top: 0;
    }

    #table-log {
        font-size: 0.85rem;
    }

    .sidebar {
        font-size: 0.85rem;
        line-height: 1;
    }

    .btn {
        font-size: 0.7rem;
    }

    .stack {
      font-size: 0.85em;
    }

    .date {
      min-width: 75px;
    }

    .text {
      word-break: break-all;
    }

    a.llv-active {
      z-index: 2;
      background-color: #f5f5f5;
      border-color: #777;
    }

    .list-group-item {
      word-break: break-word;
    }

    .folder {
      padding-top: 15px;
    }

    .div-scroll {
      height: 80vh;
      overflow: hidden auto;
    }
    .nowrap {
      white-space: nowrap;
    }



    /**
    * DARK MODE CSS
    */

    body[data-theme="dark"] {
      background-color: #151515;
      color: #cccccc;
    }

    [data-theme="dark"] a {
      color: #4da3ff;
    }

    [data-theme="dark"] a:hover {
      color: #a8d2ff;
    }

    [data-theme="dark"] .list-group-item {
      background-color: #1d1d1d;
      border-color: #444;
    }

    [data-theme="dark"] a.llv-active {
        background-color: #0468d2;
        border-color: rgba(255, 255, 255, 0.125);
        color: #ffffff;
    }

    [data-theme="dark"] a.list-group-item:focus, [data-theme="dark"] a.list-group-item:hover {
      background-color: #273a4e;
      border-color: rgba(255, 255, 255, 0.125);
      color: #ffffff;
    }

    [data-theme="dark"] .table td, [data-theme="dark"] .table th,[data-theme="dark"] .table thead th {
      border-color:#616161;
    }

    [data-theme="dark"] .page-item.disabled .page-link {
      color: #8a8a8a;
      background-color: #151515;
      border-color: #5a5a5a;
    }

    [data-theme="dark"] .page-link {
      background-color: #151515;
      border-color: #5a5a5a;
    }

    [data-theme="dark"] .page-item.active .page-link {
      color: #fff;
      background-color: #0568d2;
      border-color: #007bff;
    }

    [data-theme="dark"] .page-link:hover {
      color: #ffffff;
      background-color: #0051a9;
      border-color: #0568d2;
    }

    [data-theme="dark"] .form-control {
      border: 1px solid #464646;
      background-color: #151515;
      color: #bfbfbf;
    }

    [data-theme="dark"] .form-control:focus {
      color: #bfbfbf;
      background-color: #212121;
      border-color: #4a4a4a;
  }

  </style>

  <script>
    function initTheme() {
      const darkThemeSelected =
        localStorage.getItem('darkSwitch') !== null &&
        localStorage.getItem('darkSwitch') === 'dark';
      darkSwitch.checked = darkThemeSelected;
      darkThemeSelected ? document.body.setAttribute('data-theme', 'dark') :
        document.body.removeAttribute('data-theme');
    }

    function resetTheme() {
      if (darkSwitch.checked) {
        document.body.setAttribute('data-theme', 'dark');
        localStorage.setItem('darkSwitch', 'dark');
      } else {
        document.body.removeAttribute('data-theme');
        localStorage.removeItem('darkSwitch');
      }
    }
  </script>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col sidebar mb-3">
      <h1><i class="fa fa-calendar" aria-hidden="true"></i> INAASYS LOGS - ERRORES</h1>
      <p class="text-muted"><i>por Osbaldo</i></p>
      <p class="text-muted"><i><span class="fa fa-reply fa-lg"></span><a href="{{route('inicio')}}">Regresar a Inaasys</a></i></p>

      <div class="custom-control custom-switch" style="padding-bottom:20px;">
        <input type="checkbox" class="custom-control-input" id="darkSwitch">
        <label class="custom-control-label" for="darkSwitch" style="margin-top: 6px;">Modo Oscuro</label>
      </div>

      <div class="list-group div-scroll">
        @foreach($folders as $folder)
          <div class="list-group-item">
            <a href="?f={{ \Illuminate\Support\Facades\Crypt::encrypt($folder) }}">
              <span class="fa fa-folder"></span> {{$folder}}
            </a>
            @if ($current_folder == $folder)
              <div class="list-group folder">
                @foreach($folder_files as $file)
                  <a href="?l={{ \Illuminate\Support\Facades\Crypt::encrypt($file) }}&f={{ \Illuminate\Support\Facades\Crypt::encrypt($folder) }}"
                    class="list-group-item @if ($current_file == $file) llv-active @endif">
                    {{$file}}
                  </a>
                @endforeach
              </div>
            @endif
          </div>
        @endforeach
        @foreach($files as $file)
          <a href="?l={{ \Illuminate\Support\Facades\Crypt::encrypt($file) }}"
             class="list-group-item @if ($current_file == $file) llv-active @endif">
            {{$file}}
          </a>
        @endforeach
      </div>
    </div>
    <div class="col-10 table-container">
      @if ($logs === null)
        <div>
          Log file >50M, please download it.
        </div>
      @else
        <table id="table-log" class="table table-striped" data-ordering-index="{{ $standardFormat ? 2 : 0 }}">
          <thead>
          <tr>
            @if ($standardFormat)
              <th>Nivel Error</th>
              <th>Entorno</th>
              <th>Fecha</th>
            @else
              <th>Line number</th>
            @endif
            <th>Contenido</th>
          </tr>
          </thead>
          <tbody>

          @foreach($logs as $key => $log)
            <tr data-display="stack{{{$key}}}">
              @if ($standardFormat)
                <td class="nowrap text-{{{$log['level_class']}}}">
                  <span class="fa fa-{{{$log['level_img']}}}" aria-hidden="true"></span>&nbsp;&nbsp;{{$log['level']}}
                </td>
                <td class="text">{{$log['context']}}</td>
              @endif
              <td class="date">{{{$log['date']}}}</td>
              <td class="text">
                @if ($log['stack'])
                  <button type="button"
                          class="float-right expand btn btn-outline-dark btn-sm mb-2 ml-2"
                          data-display="stack{{{$key}}}">
                    <span class="fa fa-search"></span>
                  </button>
                @endif
                {{{$log['text']}}}
                @if (isset($log['in_file']))
                  <br/>{{{$log['in_file']}}}
                @endif
                @if ($log['stack'])
                  <div class="stack" id="stack{{{$key}}}"
                       style="display: none; white-space: pre-wrap;">{{{ trim($log['stack']) }}}
                  </div>
                @endif
              </td>
            </tr>
          @endforeach

          </tbody>
        </table>
      @endif
      <div class="p-3">
        @if($current_file)
          <a href="?dl={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
            <span class="fa fa-download"></span> Download file
          </a>
          -
          <a id="clean-log" href="?clean={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
            <span class="fa fa-sync"></span> Clean file
          </a>
          -
          <a id="delete-log" href="?del={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
            <span class="fa fa-trash"></span> Delete file
          </a>
          @if(count($files) > 1)
            -
            <a id="delete-all-log" href="?delall=true{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
              <span class="fa fa-trash-alt"></span> Delete all files
            </a>
          @endif
        @endif
      </div>
    </div>
  </div>
</div>
<!-- jQuery for Bootstrap -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
<!-- FontAwesome -->
<script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
<!-- Datatables -->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>

<script>

  // dark mode by https://github.com/coliff/dark-mode-switch
  const darkSwitch = document.getElementById('darkSwitch');

  // this is here so we can get the body dark mode before the page displays
  // otherwise the page will be white for a second... 
  initTheme();

  window.addEventListener('load', () => {
    if (darkSwitch) {
      initTheme();
      darkSwitch.addEventListener('change', () => {
        resetTheme();
      });
    }
  });

  // end darkmode js
        
  $(document).ready(function () {
    $('.table-container tr').on('click', function () {
      $('#' + $(this).data('display')).toggle();
    });
    $('#table-log').DataTable({
      "order": [$('#table-log').data('orderingIndex'), 'desc'],
      language:{
          "processing": "Procesando...",
          "lengthMenu": "Mostrar _MENU_ registros",
          "zeroRecords": "No se encontraron resultados",
          "emptyTable": "Ningún dato disponible en esta tabla",
          "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
          "infoFiltered": "(filtrado de un total de _MAX_ registros)",
          "search": "Buscar:",
          "infoThousands": ",",
          "loadingRecords": "Cargando...",
          "paginate": {
              "first": "Primero",
              "last": "Último",
              "next": "Siguiente",
              "previous": "Anterior"
          },
          "aria": {
              "sortAscending": ": Activar para ordenar la columna de manera ascendente",
              "sortDescending": ": Activar para ordenar la columna de manera descendente"
          },
          "buttons": {
              "copy": "Copiar",
              "colvis": "Visibilidad",
              "collection": "Colección",
              "colvisRestore": "Restaurar visibilidad",
              "copyKeys": "Presione ctrl o u2318 + C para copiar los datos de la tabla al portapapeles del sistema. <br \/> <br \/> Para cancelar, haga clic en este mensaje o presione escape.",
              "copySuccess": {
                  "1": "Copiada 1 fila al portapapeles",
                  "_": "Copiadas %d fila al portapapeles"
              },
              "copyTitle": "Copiar al portapapeles",
              "csv": "CSV",
              "excel": "Excel",
              "pageLength": {
                  "-1": "Mostrar todas las filas",
                  "_": "Mostrar %d filas"
              },
              "pdf": "PDF",
              "print": "Imprimir"
          },
          "autoFill": {
              "cancel": "Cancelar",
              "fill": "Rellene todas las celdas con <i>%d<\/i>",
              "fillHorizontal": "Rellenar celdas horizontalmente",
              "fillVertical": "Rellenar celdas verticalmentemente"
          },
          "decimal": ",",
          "searchBuilder": {
              "add": "Añadir condición",
              "button": {
                  "0": "Constructor de búsqueda",
                  "_": "Constructor de búsqueda (%d)"
              },
              "clearAll": "Borrar todo",
              "condition": "Condición",
              "conditions": {
                  "date": {
                      "after": "Despues",
                      "before": "Antes",
                      "between": "Entre",
                      "empty": "Vacío",
                      "equals": "Igual a",
                      "notBetween": "No entre",
                      "notEmpty": "No Vacio",
                      "not": "Diferente de"
                  },
                  "number": {
                      "between": "Entre",
                      "empty": "Vacio",
                      "equals": "Igual a",
                      "gt": "Mayor a",
                      "gte": "Mayor o igual a",
                      "lt": "Menor que",
                      "lte": "Menor o igual que",
                      "notBetween": "No entre",
                      "notEmpty": "No vacío",
                      "not": "Diferente de"
                  },
                  "string": {
                      "contains": "Contiene",
                      "empty": "Vacío",
                      "endsWith": "Termina en",
                      "equals": "Igual a",
                      "notEmpty": "No Vacio",
                      "startsWith": "Empieza con",
                      "not": "Diferente de"
                  },
                  "array": {
                      "not": "Diferente de",
                      "equals": "Igual",
                      "empty": "Vacío",
                      "contains": "Contiene",
                      "notEmpty": "No Vacío",
                      "without": "Sin"
                  }
              },
              "data": "Data",
              "deleteTitle": "Eliminar regla de filtrado",
              "leftTitle": "Criterios anulados",
              "logicAnd": "Y",
              "logicOr": "O",
              "rightTitle": "Criterios de sangría",
              "title": {
                  "0": "Constructor de búsqueda",
                  "_": "Constructor de búsqueda (%d)"
              },
              "value": "Valor"
          },
          "searchPanes": {
              "clearMessage": "Borrar todo",
              "collapse": {
                  "0": "Paneles de búsqueda",
                  "_": "Paneles de búsqueda (%d)"
              },
              "count": "{total}",
              "countFiltered": "{shown} ({total})",
              "emptyPanes": "Sin paneles de búsqueda",
              "loadMessage": "Cargando paneles de búsqueda",
              "title": "Filtros Activos - %d"
          },
          "select": {
              "cells": {
                  "1": "1 celda seleccionada",
                  "_": "%d celdas seleccionadas"
              },
              "columns": {
                  "1": "1 columna seleccionada",
                  "_": "%d columnas seleccionadas"
              },
              "rows": {
                  "1": "1 fila seleccionada",
                  "_": "%d filas seleccionadas"
              }
          },
          "thousands": ".",
          "datetime": {
              "previous": "Anterior",
              "next": "Proximo",
              "hours": "Horas",
              "minutes": "Minutos",
              "seconds": "Segundos",
              "unknown": "-",
              "amPm": [
                  "AM",
                  "PM"
              ],
              "months": {
                  "0": "Enero",
                  "1": "Febrero",
                  "10": "Noviembre",
                  "11": "Diciembre",
                  "2": "Marzo",
                  "3": "Abril",
                  "4": "Mayo",
                  "5": "Junio",
                  "6": "Julio",
                  "7": "Agosto",
                  "8": "Septiembre",
                  "9": "Octubre"
              },
              "weekdays": [
                  "Dom",
                  "Lun",
                  "Mar",
                  "Mie",
                  "Jue",
                  "Vie",
                  "Sab"
              ]
          },
          "editor": {
              "close": "Cerrar",
              "create": {
                  "button": "Nuevo",
                  "title": "Crear Nuevo Registro",
                  "submit": "Crear"
              },
              "edit": {
                  "button": "Editar",
                  "title": "Editar Registro",
                  "submit": "Actualizar"
              },
              "remove": {
                  "button": "Eliminar",
                  "title": "Eliminar Registro",
                  "submit": "Eliminar",
                  "confirm": {
                      "_": "¿Está seguro que desea eliminar %d filas?",
                      "1": "¿Está seguro que desea eliminar 1 fila?"
                  }
              },
              "error": {
                  "system": "Ha ocurrido un error en el sistema (<a target=\"\\\" rel=\"\\ nofollow\" href=\"\\\">Más información&lt;\\\/a&gt;).<\/a>"
              },
              "multi": {
                  "title": "Múltiples Valores",
                  "info": "Los elementos seleccionados contienen diferentes valores para este registro. Para editar y establecer todos los elementos de este registro con el mismo valor, hacer click o tap aquí, de lo contrario conservarán sus valores individuales.",
                  "restore": "Deshacer Cambios",
                  "noMulti": "Este registro puede ser editado individualmente, pero no como parte de un grupo."
              }
          },
          "info": "Mostrando _START_ a _END_ de _TOTAL_ registros"
      },
      "stateSave": true,
      "stateSaveCallback": function (settings, data) {
        window.localStorage.setItem("datatable", JSON.stringify(data));
      },
      "stateLoadCallback": function (settings) {
        var data = JSON.parse(window.localStorage.getItem("datatable"));
        if (data) data.start = 0;
        return data;
      }
    });
    $('#delete-log, #clean-log, #delete-all-log').click(function () {
      return confirm('Are you sure?');
    });
  });
</script>
</body>
</html>
