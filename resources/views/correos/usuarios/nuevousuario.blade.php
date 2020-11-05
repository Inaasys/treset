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
  /* General styling */
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
         /*Thanks Outlook 2013! http://goo.gl/XLxpyl*/
        td, h1, h2, h3 {
          font-family: 'Droid Sans', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
        }
      }
  </style>
  <style type="text/css" media="only screen and (max-width: 480px)">
    /* Mobile styles */
    @media only screen and (max-width: 480px) {
      table[class="w320"] {
        width: 320px !important;
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
                <table style="margin: -20px auto;" cellpadding="0" cellspacing="0" width="100%" style="margin:0 auto;" bgcolor="#e24b41">
                  <tr>
                    <td style="font-size: 30px; text-align:center;color: #ffffff;">
                      <br>
                        Inaasys
                      <br>
                      <br>
                    </td>
                  </tr>
                </table>
                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%" bgcolor="#4dbfbf">
                  <tr>
                    <td>
                    <br>
                      <img src="{{ $message->embed(public_path() . '/images/correos/icono12.png') }}" width="150" height="150">
                    </td>
                  </tr>
                  <tr>
                    <td class="headline">
                      Aviso!
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <center>
                        <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="60%">
                          <tr>
                            <td style="color:#187272;">
                            <br>
                            {{$body}}
                            <br>
                            <br>
                            <br>
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
                    <br>
                      <img src="{{ $message->embed(public_path() . '/images/correos/icono22.png') }}" width="113" height="100" alt="meter image">
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <center>
                        <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="60%">
                          <tr>
                            <td style="color:#933f24;">
                                {{$body}},
                                <br> Nombre: <b style="color:#ffffff" >{{$nombre}}</b><br>
                                Correo: <b style="color:#ffffff" >{{$correo}}</b><br>
                                Usuario: <b style="color:#ffffff" >{{$usuario}}</b><br>
                                Rol: <b style="color:#ffffff" >{{$rol}}</b><br>
                              </td>
                            </tr>
                            <tr>
                              <td style="color:#933f24;">
                              Hora exacta respaldo<br>
                              {{$horaaccionespanol}}
                              <br><br>
                            </td>
                          </tr>
                        </table>
                      </center>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <div>
                            <a href="http://utpcamiones.com.mx"
                      style="background-color:#ac4d2f;border-radius:4px;color:#ffffff;display:inline-block;font-family: Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">Visítanos</a>
                        </div>
                      <br>
                      <br>
                    </td>
                  </tr>
                </table>
                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" class="force-full-width" bgcolor="#414141" style="margin: 0 auto">
                  <tr>
                    <td style="color:#bbbbbb; font-size:12px;">
                      <br>
                      <a href="http://utpcamiones.com.mx">Visítanos</a> | <a href="http://www.utpcamiones.com.mx/correos.php">Contactanos</a>
                      <br><br>
                    </td>
                  </tr>
                  <tr>
                    <td style="color:#bbbbbb; font-size:12px;">
                       © 2020 Inaasys Todos los derechos reservados
                       <br>
                       <br>
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