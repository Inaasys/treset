<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>INAASYS</title>
    <style type="text/css">
        @import url(http://fonts.googleapis.com/css?family=Droid+Sans);
        /* Take care of image borders and formatting */
        img {
            max-width: 600px;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }
        a {
            text-decoration: none;
            border: 0;
            outline: none;
            color: #bbbbbb;
        }
        a img {
            border: none;
        }
        td, h1, h2, h3  {
            font-family: Helvetica, Arial, sans-serif;
            font-weight: 400;
        }
        td {
            text-align: center;
        }
        body {
            -webkit-font-smoothing:antialiased;
            -webkit-text-size-adjust:none;
            width: 100%;
            height: 100%;
            color: #37302d;
            background: #ffffff;
            font-size: 16px;
        }
        table {
            border-collapse: collapse !important;
        }
        .headline {
            color: #ffffff;
            font-size: 36px;
        }
        .force-full-width {
        width: 100% !important;
        }
    </style>
    <style type="text/css" media="screen">
        @media screen {
            td, h1, h2, h3 {
            font-family: 'Droid Sans', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
            }
        }
    </style>
</head>
<body class="body" style="padding:0; margin:0; display:block; background:#ffffff; -webkit-text-size-adjust:none" bgcolor="#ffffff">
    <table align="center" cellpadding="0" cellspacing="0" width="100%" height="100%" >
        <tr>
            <td align="center" valign="top" bgcolor="#ffffff"  width="100%">
                <center>
                    <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="600" class="w320">
                        <tr>
                            <td align="center" valign="top">
                                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%" bgcolor="#4dbfbf">
                                    <tr>
                                        <td class="headline">
                                            Aviso!
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <center>
                                                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="color:#ffffff;font-size:17px;">
                                                            <br><b>{{$body}}</b><br><br><br>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </center>
                                        </td>
                                    </tr>
                                </table>
                                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f5774e">
                                <tr>
                                    <td>
                                        <br><img src="{{ $message->embed(public_path() . '/images/correos/icono24.png') }}" width="113" height="100" alt="meter image"><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <center>
                                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="90%" style="font-size:13px;">
                                                <tr>
                                                    <td style="color:#933f24;">
                                                        Personal que entrego herramienta:
                                                    </td>
                                                    <td style="color:#ffffff;">
                                                        {{$nombre_personal_entrega}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#933f24;">
                                                        Personal que recibio herramienta:
                                                    </td>
                                                    <td style="color:#ffffff;">
                                                        {{$nombre_personal_recibe}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#933f24;">
                                                        Fecha inicio de prestamo:
                                                    </td>
                                                    <td style="color:#ffffff;">
                                                        {{$inicio_prestamo}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#933f24;">
                                                        Fecha termino de prestamo:
                                                    </td>
                                                    <td style="color:#ffffff;">
                                                        {{$termino_prestamo}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#933f24;">
                                                        Fecha Prestamo:
                                                    </td>
                                                    <td style="color:#ffffff;">
                                                        {{$horaaccionespanol}}
                                                    </td>
                                                </tr>
                                            </table>
                                        </center>
                                    </td>
                                    <br>
                                </tr>
                                <tr>
                                    <td>
                                        <center>
                                            <table  width="90%" style="color:#ffffff;font-size:10px;border: 1px solid #ffffff;">
                                                <thead>
                                                    <tr>
                                                        <td>Herramienta</td>
                                                        <td>Descripción</td>
                                                        <td>Cantidad</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($filascorreo as $fc)
                                                        <tr>
                                                            <td>{{$fc->herramienta}}</td>
                                                            <td>{{$fc->descripcion}}</td>
                                                            <td>{{$fc->cantidad}}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                        </center>
                                    </td>
                                </tr>
                                </table>
                                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" class="force-full-width" bgcolor="#414141" style="margin: 0 auto">
                                    <tr>
                                        <td style="color:#bbbbbb; font-size:12px;">
                                            <br><a href="http://utpcamiones.com.mx">Visítanos</a> | <a href="http://www.utpcamiones.com.mx/correos.php">Contactanos</a><br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color:#bbbbbb; font-size:12px;">
                                            © 2020 Inaasys Todos los derechos reservados <br><br>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </center>
            </td>
        </tr>
    </table>
</body>
</html>