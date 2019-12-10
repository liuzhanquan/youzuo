<?php /*a:1:{s:63:"D:\phpstudy\WWW\youzuo\application\backman\view\auth\login.html";i:1563517114;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>管理后台登录</title>
<link rel="icon" sizes="32*32" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="/static/css/admin.css">
<link rel="stylesheet" type="text/css" href="//unpkg.com/iview/dist/styles/iview.css">
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/vue"></script>
<script type="text/javascript" src="//unpkg.com/iview/dist/iview.min.js"></script>
<script type="text/javascript" src="/static/js/jquery.js"></script>
<script type="text/javascript" src="/static/js/do.js"></script>
<script type="text/javascript" src="/static/js/package.js" data-path="/static/js/" data-root="/<?php echo request()->controller(); ?>/"  data-src="/static/js/common"></script>
</head>
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