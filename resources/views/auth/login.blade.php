<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="icon" type="image/png" href="images/iniciarsesion2.png" sizes="16x16">
    <link rel="icon" type="image/png" href="images/iniciarsesion2.png" sizes="32x32">
    <title>inaasys - login</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
    <!-- Bootstrap Core Css -->
    <link href="plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <!-- Waves Effect Css -->
    <link href="plugins/node-waves/waves.css" rel="stylesheet" />
    <!-- Animation Css -->
    <link href="plugins/animate-css/animate.css" rel="stylesheet" />
    <!-- Custom Css -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-box">
        <div class="logo text-center">
            <img  src="images/iniciarsesion2.png" height="130px" width="135px;">
        </div>
        <div class="card">
            <div class="body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="msg">Iniciar sesión</div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">person</i>
                        </span>
                        <div class="form-line">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Usuario" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror   
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">lock</i>
                        </span>
                        <div class="form-line">
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Contraseña">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror                        
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-block bg-blue waves-effect" type="submit"> Iniciar sesión</button>
                        </div>
                    </div>
                    {{--
                    <div class="row m-t-15 m-b--20">
                        <div class="col-xs-12 align-right">
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">Olvidaste tu contraseña?</a>
                            @endif
                        </div>
                    </div>
                    --}}
                </form>
            </div>
        </div>
        {{--<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="body bg-cyan">
                    <b class="text-justify" style="font-size:15px;">{{$piensa[0]->Mensaje}} </b>
                    <p style="text-align:right;"><span class="badge bg-teal">{{$fechaaccionespanol}}</span></p>
                </div>
            </div>
        </div>--}}
    </div>
    <!-- Jquery Core Js -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap Core Js -->
    <script src="plugins/bootstrap/js/bootstrap.js"></script>
    <!-- Waves Effect Plugin Js -->
    <script src="plugins/node-waves/waves.js"></script>
    <!-- Validation Plugin Js -->
    <script src="plugins/jquery-validation/jquery.validate.js"></script>
    <!-- Custom Js -->
    <script src="js/admin.js"></script>
    <script src="js/pages/examples/sign-in.js"></script>
</body>
</html>