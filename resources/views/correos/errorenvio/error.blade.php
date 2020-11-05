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
                                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ff0000">
									<tr>
                                        <td class="headline">
                                            Aviso!
                                        </td>
                                    </tr>
									<tr>
										<td>
											<br><img src="{{ $message->embed(public_path() . '/images/correos/icono11.png') }}" width="80" height="80" alt="meter image"><br>
										</td>
									</tr>
									<tr>
                                        <td>
                                            <center>
                                                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="color:#ffffff;font-size:15px;">
                                                            <br><b>{{$msj}}</b><br><br><br>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="color:#ffffff;font-size:11px;">
                                                            <br><b>{{$e}}</b><br><br><br>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </center>
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