<?php /*a:1:{s:57:"/www/web/codecheck/application/home/view/login/login.html";i:1594437641;}*/ ?>
<html>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>

    <meta charset="utf-8">
    <title>登录(Login)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- CSS -->
    <link rel="stylesheet" href="/static/api/css/reset.css">
    <link rel="stylesheet" href="/static/api/css/supersized.css">
    <link rel="stylesheet" href="/static/api/css/style.css">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="/static/api/js/html5.js"></script>
    <![endif]-->
    <style>
        .connect a.facebook { background: url(/static/api/img/facebook.png) center center no-repeat; }
        .connect a.twitter { background: url(/static/api/img/twitter.png) center center no-repeat; }
    </style>
</head>

<body>

<div class="page-container">
    <h1>二维码核销管理</h1>
        <input type="text" name="username" class="username" placeholder="请输入您的用户名！">
        <input type="password" name="password" class="password" placeholder="请输入您的用户密码！">
        <button type="submit" class="submit_button">登录</button>
        <div class="error"><span>+</span></div>
</div>

<!-- Javascript -->
<script src="/static/api/js/jquery-1.8.2.min.js" ></script>
<script src="/static/api/js/supersized.3.2.7.min.js" ></script>
<script src="/static/api/js/supersized-init.js" ></script>
<script src="/static/api/js/scripts.js" ></script>
<script src="/static/api/js/jquery.cookie.js" ></script>

<script >

    if( $.cookie('token') && $.cookie('token') != 'undefined' ){
        window.location.href = "<?php echo url('home/index/index'); ?>"
    }

    $('.submit_button').on('click',function(){
        var result = {};
        result.username = $('.username').val();
        result.password = $('.password').val();
        if( result.username == '' ){
            alert('账号不能为空');
            return false;
        }
        if( result.password == '' ){
            alert('密码不能为空');
            return false;
        }

        var url = "<?php echo url('api/login/login'); ?>";
        $.post(url, result, function(data){
            if( data.status == 200 ){
                console.log(data.data.token);
                $.cookie('token',data.data.token,{ expires: 7, path: '/' });
                $.cookie('name',data.data.name,{ expires: 7, path: '/' });
                // window.location.href = "<?php echo url('home/index/index'); ?>"
            }else{
                alert(data.message);
            }
        },'json')

    })


</script>




</body>
</html>

