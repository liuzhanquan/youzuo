<?php /*a:1:{s:56:"/www/web/youzuo/application/backman/view/auth/login.html";i:1583393378;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>管理后台登录</title>
<link rel="icon" sizes="32*32" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="/static/css/admin.css">
<link rel="stylesheet" type="text/css" href="/static/iview/iview.css">
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/vue"></script>
<script type="text/javascript" src="/static/iview/iview.min.js"></script>
<script type="text/javascript" src="/static/js/jquery.js"></script>
<script type="text/javascript" src="/static/js/do.js"></script>
<script type="text/javascript" src="/static/js/package.js" data-path="/static/js/" data-root="/<?php echo request()->controller(); ?>/"  data-src="/static/js/common"></script>
</head>
<style>
.login .form-btn {
    margin: 30px 0 20px;
}
.ivu-btn, .ivu-btn:active, .ivu-btn:focus {
    outline: 0;
}
.ivu-btn {
    display: inline-block;
    margin-bottom: 0;
    font-weight: 400;
    text-align: center;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    background-image: none;
    border: 1px solid transparent;
    white-space: nowrap;
    line-height: 1.5;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    height: 32px;
    padding: 0 15px;
    font-size: 14px;
    border-radius: 4px;
    -webkit-transition: color .2s linear,background-color .2s linear,border .2s linear,-webkit-box-shadow .2s linear;
    transition: color .2s linear,background-color .2s linear,border .2s linear,-webkit-box-shadow .2s linear;
    transition: color .2s linear,background-color .2s linear,border .2s linear,box-shadow .2s linear;
    transition: color .2s linear,background-color .2s linear,border .2s linear,box-shadow .2s linear,-webkit-box-shadow .2s linear;
    color: #515a6e;
    background-color: #fff;
    border-color: #dcdee2;
}
.ivu-btn-primary {
    color: #fff;
    background-color: #2d8cf0;
    border-color: #2d8cf0;
}
.ivu-btn>i, .ivu-btn>span {
    display: inline-block;
}
.ivu-btn-large {
    height: 40px;
    padding: 0 15px;
    font-size: 16px;
    border-radius: 4px;
}
.ivu-btn-long {
    width: 100%;
}
.login .form-btn .ivu-btn-large {
    padding: 10px 15px;
}



</style>
<body id="login-bg" style="overflow: hidden;">
<div id="box"></div>
<div id="app">
	<div class="login">
		<form data-model="form-submit">
			<div class="form-item">
				<label>登录账号：</label>
				<div class="form-input">
					<input placeholder="请输入登陆账号" name="name" clearable="true" class="ivu-input ivu-input-default" datatype="*">
				</div>
			</div>
			<div class="form-item">
				<label>登陆密码：</label>
				<div class="form-input">
					<input :type="passwdTy" class="ivu-input ivu-input-default" placeholder="请输入密码" name="password" datatype="*">
					<div class="passwIcon" @click="changeType()">
						<Icon :type="seen" size="20" />
					</div>
				</div>
			</div>
			<div class="form-item">
				<label>验证码：</label>
				<div class="form-input">
					<input placeholder="请输入验证码" name="captcha" datatype="*" class="ivu-input ivu-input-default"></input>
					<div class="captcha-pr"><?php echo captcha_img(); ?></div>
				</div>
			</div>
			<div class="form-btn">
				<i-button html-type="submit" type="primary" long size="large">登录</i-button>
			</div>
		</form>
	</div>
	<div class="login-footer">
		<p>© 2015-2019</p>
	</div>
</div>
<script type="text/javascript" src="/static/js/particles.js"></script>
<script type="text/javascript" src="/static/js/background.js"></script>
<script type="text/javascript">
var mixin = {
	methods:{
		changeType(){
            this.passwdTy = this.passwdTy === 'password' ? 'text' : 'password';
            this.seen = this.seen === 'md-eye' ? 'md-eye-off' : 'md-eye';
        }
	}
};
var webRoot = "<?php echo request()->root(true); ?>/";
var webControl = "<?php echo request()->controller(); ?>";
Do.ready('common',function(){ base.frame(); });
</script>
</body>
</html>