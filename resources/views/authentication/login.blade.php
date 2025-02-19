<!DOCTYPE html>
<html>

<head>
    <title>{{\config("app.name")}}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script type="text/javascript" src="{{asset("js/pleaserotate.min.js")}}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="{{asset("assets/jqueryconfirm/jquery-confirm.min.css")}}"/>
    <link rel="icon" href="{{asset("assets/images/favicon.ico")}}" type="image/x-icon">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" integrity="sha384-gfdkjb5BdAXd+lj+gudLWI+BXq4IuLW5IT+brZEZsLFm++aCMlF1V92rMkPaX4PP" crossorigin="anonymous">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            background: white;
            background: -webkit-linear-gradient(white, #065fb8);
            background: -o-linear-gradient(white, #065fb8);
            background: -moz-linear-gradient(white, #065fb8);
            background: linear-gradient(white, #065fb8);

        }
        .user_card {
            min-height: 360px;
            width: 400px;
            margin-top: 15%;
            background: #fff;
            position: relative;
            display: flex;
            justify-content: center;
            flex-direction: column;
            padding: 10px;
            box-shadow: 0 17px 29px 15px rgba(0, 0, 0, 0.2), 0 19px 43px 0 rgba(0, 0, 0, 0.19);
            -webkit-box-shadow: 0 17px 29px 15px rgba(0, 0, 0, 0.2), 0 19px 43px 0 rgba(0, 0, 0, 0.19);
            -moz-box-shadow: 0 17px 29px 15px rgba(0, 0, 0, 0.2), 0 19px 43px 0 rgba(0, 0, 0, 0.19);
            border-radius: 5px;
            clear:both;

        }
        .brand_logo_container {
            position: absolute;
            height: 134px;
            width: 189px;
            top: -72px;
            border-radius: 56%;
            padding: 10px;
            text-align: center;
        }
        .brand_logo {
            height: 115px;
            width: 170px;
            border-radius: 50%;
            /*border: 2px solid white;*/
        }
        .form_container {
            margin-top: 20px;
        }
        .login_btn {
            width: 100%;;
            background: #f58220 !important;
            color: white !important;
        }
        .login_btn:focus {
            box-shadow: none !important;
            outline: 0 !important;
        }
        .login_container {
        }
        .input-group-text {
            background: #007dc5 !important;
            color: white !important;
            border: 0 !important;
            border-radius: 0.25rem 0 0 0.25rem !important;
        }
        .input_user,
        .input_pass:focus {
            box-shadow: none !important;
            outline: 0 !important;
        }
        .custom-checkbox .custom-control-input:checked~.custom-control-label::before {
            background-color: #f58220 !important;
        }
    </style>
</head>
<body data-baseurl="{{url('/')}}" >
<div class="container h-100">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-center h-100">

                <div class="user_card">
                    <div class="d-flex justify-content-center">
                        <div class="brand_logo_container">
                            <img src="{{"images/logo_large.png"}}" class="brand_logo" alt="Logo">
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <br><br>
                            @if(Session::has('errormessage'))
                                <div class="alert alert-danger" style="margin-top:10px;font-size:13px;">
                                    {!!Session::get('errormessage')!!}
                                </div><div class="clearfix"></div>
                            @endif
                            @if(Session::has('successmessage'))
                                <div class="alert alert-success" style="margin-top:10px;font-size:13px;">
                                    {!!Session::get('successmessage')!!}
                                </div><div class="clearfix"></div>
                            @endif
                            <span class="h6 text-center">Online PMS</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center form_container">
                        <form action="/auth" method="POST">
                            @csrf
                        <input type="hidden" name="redirect" value="{{app('request')->input('redirect')}}">
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" autocomplete="off" name="EmpId" required="required" class="form-control input_user" value="" placeholder="Emp ID">
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <input type="password" name="Password" required="required" class="form-control input_pass" value="" placeholder="Password">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="RememberMe" class="custom-control-input" id="customControlInline">
                                <label class="custom-control-label" for="customControlInline">Remember me</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-3 login_container">
                            <button type="submit" name="button" class="btn login_btn">LOGIN</button>
                        </div>
                        </form>
                    </div>

                    <div>
                        <div class="d-flex justify-content-center links" style="padding-top:10px;">
                            <a href="#" id="forgot-pw" style="font-size:13px;">Forgot your password?</a>
                        </div>
                    </div>
                    
                    <span style="font-size:11px; text-align:center;padding-top:10px;">&copy;<a href="https://www.tashicell.com/">TashiCell</a> {{date('Y')}}, All Rights Reserved.<br>Powered By <strong>Software & Application Section, MIS Department, TashiCell</strong></span>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{asset("assets/jqueryconfirm/jquery-confirm.min.js")}}"></script>
<script>
    @if(Session::has('reload'))
        $.alert({
            content: "Form Session has expired. Page will reload automatically to refresh session.",
            onDestroy: function () {
                window.location.reload();
            },
        });
    @endif

    $(document).on('click','#forgot-pw',function(){
        var baseUrl = $("body").data('baseurl');
        $.confirm({
            title: '',
            content: '' +
                '<form action="'+baseUrl+'/resetpassword" class="formName" >' +
                '<div class="form-group">' +
                '<label>Enter your Employee Id</label>' +
                '<input type="text" placeholder="Employee Id" autocomplete="off" name="username" id="username" class="form-control" required />' +
                '<br/><label>Enter your CID No.</label>' +
                '<input type="text" placeholder="CID No." autocomplete="off" name="cid" id="cid" class="form-control" required />' +
                '</div>' +
                '</form>',
            buttons: {
                cancel: function () {
                    //close
                },
                formSubmit: {
                    text: 'Reset Password',
                    btnClass: 'btn-blue',
                    action: function () {
                        var username = this.$content.find('#username').val();
                        var cid = this.$content.find('#cid').val();
                        if(!username || !cid){
                            $.alert('Please enter both your Employee Id and CID No.');
                            return false;
                        }else{
                            username = encodeURIComponent(encodeURI(username));
                            cid = encodeURIComponent(encodeURI(cid));
                            window.location.href=baseUrl+"/forgotpassword?cid="+cid+"&username="+username;
                        }
                    }
                },
            },
            onContentReady: function () {
                // bind to events
                var jc = this;
                this.$content.find('form').on('submit', function (e) {
                    // if the user submits the form by pressing enter in the field.
                    e.preventDefault();
                    jc.$$formSubmit.trigger('click'); // reference the button and click it
                });
            }
        });
    });

</script>
</body>
</html>
