<?php /*a:1:{s:57:"/www/web/codecheck/application/home/view/index/index.html";i:1594438376;}*/ ?>
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
        .page-container{width:400px;}
        .connect a.facebook { background: url(/static/api/img/facebook.png) center center no-repeat; }
        .connect a.twitter { background: url(/static/api/img/twitter.png) center center no-repeat; }
        .d_f div{float:left;}
        .l_h20 div{line-height: 30px;}
        .headerBox{wdith:100%;height:45px;}
        .headerUser{text-align:center;width:300px;}
        .headerLeft,.headerOut{width:50px;height:30px;}
        .codeBox{padding-top:20px;width:100%;height:45px;}
        .codeBox div{line-height:40px;margin-top:0px;}
        input{margin-top:0px;color:#000;}
        .codeLeft{width:70px;margin-right:5px;}
        .codeButton{width:40px;margin-left:10px;padding:0px 10px;background:#169bd5;border-radius:5px;cursor:pointer;}
        .codeInput{width:250px;}
        .titleH{
            width:97%;
            padding-left:3%;
            height:40px;
            line-height:40px;
            background:#f1f1f1;
            text-align:left;
            margin-top:20px;
        }
        .checkBox,.orderBox{
            text-align:left;
            width:100%;
            line-height:30px;
        }
        .orderList{
            background:#f1f1f1;
            padding:15px 20px;
            margin-top:20px;
        }
        .orderList div{


        }
    </style>
</head>

<body>

<div class="page-container" style="margin-top:0;color:#202020;">
    <div class="headerBox d_f l_h20">
        <div class="headerLeft"></div>
        <div class="headerUser">核销员：</div>
        <div class="headerOut">退出</div>
    </div>
    <div class="codeBox d_f">
        <div class="codeLeft">防伪码:</div>
        <div class="codeInput"><input type="text" class="keyword"></div>
        <div class="codeButton">核销</div>
    </div>
    <div class="titleH">状态监控</div>
    <div class="checkBox">
        <div class="checkCode">
            流水号：
            <span class="checkCodeText"></span>
        </div>
        <div class="checkCode">
            产　品：
            <span class="checkGoodsText"></span>
        </div>
        <div class="checkCode">
            结　果：
            <span class="checkResText"></span>
        </div>
    </div>
    <div class="titleH">核销记录</div>
    <div class="orderBox">
        <div class="orderList">
            <div class="orderId">　ID　：3</div>
            <div class="orderCode"> 流水号：</div>
            <div class="orderGoods"> 产　品：</div>
            <div class="orderTime">核销时间：</div>
        </div>
    </div>
</div>

<!-- Javascript -->
<script src="/static/api/js/jquery-1.8.2.min.js" ></script>
<script src="/static/api/js/supersized.3.2.7.min.js" ></script>
<!--<script src="/static/api/js/supersized-init.js" ></script>-->
<!--<script src="/static/api/js/scripts.js" ></script>-->
<script src="/static/api/js/jquery.cookie.js" ></script>

<script >
    if( !($.cookie('token')) || $.cookie('token') == 'undefined' ){
        window.location.href = "<?php echo url('home/login/login'); ?>"
    }
    var list = [];
    var page = 1;
    var count = 1;

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
                $.cookie('token',data.token,{ expires: 7, path: '/' });
                $.cookie('name',data.name,{ expires: 7, path: '/' });
                window.location.href = "<?php echo url('home/index/index'); ?>"
            }else{
                alert(data.message);
            }
        },'json')

    })

    $('.codeButton').on('click',function(){
        query();
    });

    function query(){
        var result = {};
        result.keyword = $('.keyword').val();
        result.userInfo = $.cookie('token');
        var url = "<?php echo url('home/index/checkOrder'); ?>";
        if( result['keyword'] == '' ) return false;
        $.post(url, result, function(data){

            $('.checkResText').html(data.message);
            if( data.status == 200 ){
                $('.checkCodeText').html(data.data.code);
                $('.checkGoodsText').html(data.data.goods_id.text);

                $('.checkResText').css('color','green');
            }else{
                if( data.status == 401 ){
                    $('.checkCodeText').html(data.data.code);
                    $('.checkGoodsText').html(data.data.goods_id.text);
                }else{
                    $('.checkCodeText').html('');
                    $('.checkGoodsText').html('');
                }
                $('.checkResText').css('color','red');
            }
        },'json')
    }

    function getList(){
        if( page > count ) return false;
        var result = {};
        result.page = page;
        result.userInfo = $.cookie('token');
        var url = "<?php echo url('home/index/checkOrder'); ?>";
        if( result['keyword'] == '' ) return false;
        $.post(url, result, function(data){

            $('.checkResText').html(data.message);
            if( data.status == 200 ){
                $('.checkCodeText').html(data.data.code);
                $('.checkGoodsText').html(data.data.goods_id.text);

                $('.checkResText').css('color','green');
            }else{
                if( data.status == 401 ){
                    $('.checkCodeText').html(data.data.code);
                    $('.checkGoodsText').html(data.data.goods_id.text);
                }else{
                    $('.checkCodeText').html('');
                    $('.checkGoodsText').html('');
                }
                $('.checkResText').css('color','red');
            }
        },'json')
    }

    function listText(){
        if( list.length > 0 ){
            var html = '';
            for( i = 0; i < list.length; i++ ){
                html = html + '<div class="orderList"><div class="orderId">　ID　：'+ list[i]['id'] +'</div><div class="orderCode"> 流水号：'+ list[i]['code'] +'</div><div class="orderGoods"> 产　品：'+ list[i]['goods_id']['text'] +'</div><div class="orderTime">核销时间：'+ list[i]['create_time'] +'</div></div>';
            }
            $('.orderBox').html(html);
        }
    }

</script>




</body>
</html>

