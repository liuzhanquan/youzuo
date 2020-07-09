<?php /*a:2:{s:60:"/www/web/codecheck/application/backman/view/index/index.html";i:1594223536;s:59:"/www/web/codecheck/application/backman/view/layout_con.html";i:1594223536;}*/ ?>
<!DOCTYPE html>
<html class="admin-sm">
<head>
<meta charset="utf-8">
<title>平台管理中心</title>
<link rel="icon" sizes="32*32" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="/static/css/font.css">
<link rel="stylesheet" type="text/css" href="/static/css/admin.css">
<link rel="stylesheet" type="text/css" href="/static/iview/iview.css">
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/vue"></script>
<script type="text/javascript" src="/static/iview/iview.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/vue-resource@1.5.1"></script>
<script type="text/javascript" src="/static/js/jquery.js"></script>
<script type="text/javascript" src="/static/js/do.js"></script>
<script src="/static/lib/layui/layui.js" charset="utf-8"></script>
<script type="text/javascript" src="/static/js/admin.js"></script>
<script type="text/javascript" src="/static/js/package.js" data-path="/static/js/" data-root="/<?php echo request()->controller(); ?>/"  data-src="/static/js/common"></script>
</head>
<body>


<div id="app">
	
<script type="text/javascript">
var is_remember = false; // 关闭记忆模式
</script>
<div class="container">
	<div class="logo"><a href="javascript:;">管理中心</a></div>
	<div class="left_open"><a><i title="展开左侧栏" class="iconfont">&#xe7f6;</i></a></div>
	<ul class="layui-nav right" lay-filter="" style="padding: 0 0 0 20px;">
		<li class="layui-nav-item">
			<a href="javascript:;"><?php echo htmlentities($admin->username); ?></a>
			<dl class="layui-nav-child">
				<!-- 二级菜单 -->
				<dd><a onclick="xadmin.add_tab('管理员列表','<?php echo url('setting/option',['id'=>$admin['id']]); ?>')">个人信息</a></dd>
				<dd><a href="javascript:;" @click="logout">退出</a></dd>
			</dl>
		</li>
	</ul>
</div>
<div class="left-nav">
	<div id="side-nav">
		<ul id="nav">
			<?php foreach($topNav as $k=>$vo): if(!(empty($vo['children']) || (($vo['children'] instanceof \think\Collection || $vo['children'] instanceof \think\Paginator ) && $vo['children']->isEmpty()))): ?>
				<li>
					<a href="javascript:;" class="h3"><i class="iconfont left-nav-li" lay-tips="<?php echo htmlentities($vo['name']); ?>"><?php echo $vo['icon']; ?></i><cite><?php echo htmlentities($vo['name']); ?></cite>
						<i class="iconfont nav_right">&#xe8f2;</i></a>
					<ul class="sub-menu">
						<?php foreach($vo['children'] as $k2=>$vv): ?>
						<li><a onclick="xadmin.add_tab('<?php echo htmlentities($vv['name']); ?>','<?php echo htmlentities($vv['url']); ?>',true)"><i class="iconfont">&#xe7eb;</i><cite><?php echo htmlentities($vv['name']); ?></cite></a></li>
						<?php endforeach; ?>
					</ul>
				</li>
				<?php else: ?>
				<li>
					<a href="javascript:;" class="h3" onclick="xadmin.add_tab('<?php echo htmlentities($vo['name']); ?>','<?php echo htmlentities($vo['url']); ?>')"><i class="iconfont left-nav-li" lay-tips="<?php echo htmlentities($vo['name']); ?>"><?php echo $vo['icon']; ?></i><cite><?php echo htmlentities($vo['name']); ?></cite><i class="iconfont nav_right">&#xe8f2;</i></a>
				</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<div class="page-content">
	<div class="layui-tab tab" lay-filter="xbs_tab" lay-allowclose="false">
		<ul class="layui-tab-title" style="display: none;">
			<li class="home"><i class="iconfont">&#xe7c6;</i>我的桌面</li>
		</ul>
		<div class="layui-unselect layui-form-select layui-form-selected" id="tab_right">
			<dl>
				<dd data-type="this">关闭当前</dd>
				<dd data-type="other">关闭其它</dd>
				<dd data-type="all">关闭全部</dd>
			</dl>
		</div>
		<div class="layui-tab-content">
			<div class="layui-tab-item layui-show"><iframe src="<?php echo url('home'); ?>" frameborder="0" scrolling="yes" class="x-iframe"></iframe></div>
		</div>
		<div id="tab_show"></div>
	</div>
</div>
<div class="page-content-bg"></div>
<style id="theme_style"></style>

</div>
<script type="text/javascript">
var webRoot = "<?php echo request()->root(true); ?>/";
var webControl = "<?php echo request()->controller(); ?>";
Do.ready('common',function(){ base.frame(); });
</script>

<script type="text/javascript">
var mixin = {};
</script>

</body>
</html>