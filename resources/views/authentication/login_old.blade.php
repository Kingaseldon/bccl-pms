<html>
<head>
    <title>{{\config("app.name")}}</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <style>
        /*body#LoginForm{ background-image:url("https://hdwallsource.com/img/2014/9/blur-26347-27038-hd-wallpapers.jpg"); background-repeat:no-repeat; background-position:center; background-size:cover; padding:10px;}*/
        body#LoginForm{
            background:#e6e6e6;
        }
        .form-heading { color:#fff; font-size:23px;}
        .panel h2{ color:#fff; font-size:18px; margin:0 0 8px 0;}
        .panel p { color:#777777; font-size:14px; margin-bottom:30px; line-height:24px;}
        .login-form .form-control {
            background: #f7f7f7 none repeat scroll 0 0;
            border: 1px solid #d4d4d4;
            border-radius: 4px;
            font-size: 14px;
            height: 50px;
            line-height: 50px;
        }
        .main-div {
            background: #1e3f75 none repeat scroll 0 0;
            border-radius: 2px;
            margin: 0px auto 0;
            max-width: 100%;
            height: 100%;
        }

        .login-form .form-group {
            margin-bottom:10px;
        }
        .login-form{ text-align:center;}
        .forgot a {
            color: #fff;
            font-size: 14px;
            text-decoration: underline;
        }
        .login-form  .btn.btn-primary {
            color: #ffffff;
            font-size: 14px;
            width: 100%;
            height: 50px;
            line-height: 50px;
            padding: 0;
        }
        .forgot {
            text-align: left; margin-bottom:30px;
        }
        .botto-text {
            color: #fff;
            font-size: 14px;
            margin: auto;
            padding-top: 20px;
        }
        .login-form .btn.btn-primary.reset {
            background: #ff9900 none repeat scroll 0 0;
        }
        .back { text-align: left; margin-top:10px;}
        .back a {color: #444444; font-size: 13px;text-decoration: none;}
        .alert{
            padding-left:8px;
            padding-right:8px;
            font-size: 14px;
        }
        .btn.btn-primary{
            background: #5cb85c;
            border: 1px solid #fff;
        }

    </style>
</head>
<body id="LoginForm">
<div class="container-fluid" style="padding-left:0; padding-right:0;">
    <div class="login-form">
        <div class="main-div">
            <div class="row" style="margin-right:0; margin-left:0; padding-top:20px;">
                <div class="col-md-2">
                    <img src="{{asset('images/logo_large.png')}}" width="120"/>
                </div>
                <div class="col-md-3 offset-md-7">
                    <br/>
                    <h2 style="font-size:20px;color:yellow;">{{\config("app.name")}} <span style="font-style: italic; font-size: 16px;">(version 0.1)</span></h2>
                </div>
            </div>
            <div class="panel" style="padding-top: 62px;">
                <h2 style="font-size: 25px;">SIGN IN</h2><br>
                @if(Session::has('errormessage'))
                    <div class="alert alert-danger">
                        {!!Session::get('errormessage')!!}
                    </div>
                @endif
            </div>
            {{Form::open(['url'=>'auth','style'=>'width: 25%;margin: 0 auto;'])}}
            {{Form::hidden('RedirectAfterLogin',app('request')->input('redirect'))}}
            <div class="form-group">
                <input type="number" name="Email" class="form-control" required="required" id="inputEmail" placeholder="Username">
            </div>

            <div class="form-group">
                <input type="password" name="Password" class="form-control" required="required" id="inputPassword" placeholder="Password">
            </div>
            <div class="forgot">
                <a href="#">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary">LOGIN</button>
            {{Form::close()}}
            <p class="botto-text"> &copy; Copyright TashiCell {{date('Y')}}. All Rights Reserved.</p>
        </div>
    </div>
</div>
</body>
</html>
