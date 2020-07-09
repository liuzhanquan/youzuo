<?php /*a:3:{s:61:"/www/web/codecheck/application/backman/view/goods/option.html";i:1594303553;s:55:"/www/web/codecheck/application/backman/view/layout.html";i:1594223536;s:52:"/www/web/codecheck/application/backman/view/nav.html";i:1594223536;}*/ ?>
<!DOCTYPE html>
<html class="admin-sm">
<head>
<meta charset="utf-8">
<title>平台管理中心</title>
<link rel="icon" sizes="32*32" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="/static/css/font.css">
<link rel="stylesheet" type="text/css" href="/static/css/admin.css">
<link rel="stylesheet" type="text/css" href="/static/iview/iview.css">
<link rel="stylesheet" type="text/css" href="/static/css/iconf/iconfont.css">
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/vue"></script>
<script type="text/javascript" src="/static/iview/iview.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/vue-resource@1.5.1"></script>
	<script src="/static/lib/assets/jquery-1.12.4.js"></script>
<script type="text/javascript" src="/static/js/do.js"></script>
<script src="/static/lib/layui/layui.js" charset="utf-8"></script>
<script type="text/javascript" src="/static/js/admin.js"></script>
<script type="text/javascript" src="/static/js/package.js" data-path="/static/js/" data-root="/<?php echo request()->controller(); ?>/"  data-src="/static/js/common"></script>
</head>
<body>
<div id="app"></div>
<div class="x-nav">
    <span class="layui-breadcrumb">
        <a href="<?php echo url('index/home'); ?>" onclick="xadmin.add_tab('<?php echo url('index/home'); ?>','总揽')">首页</a>
        <?php foreach($path as $vv): ?>
        <a><?php echo htmlentities($vv['name']); ?></a>
        <?php endforeach; ?>
    </span>
    <a class="layui-btn layui-btn-small layui-btn-normal" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新"> <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-fluid">
	<div class="layui-row layui-col-space15">
		<div class="layui-col-md12">
			<div class="layui-card">
				
<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
	    <li><a href="<?php echo url('index'); ?>"><?php echo htmlentities($pathCurrent['name']); ?></a></li>
	    <li class="layui-this"><?php if(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty())): ?>添加<?php else: ?>编辑<?php endif; ?>产品</li>
  	</ul>
</div>
<div class="layui-card-body">
	<form class="layui-form" data-model="form-submit">
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
		  	<legend>基本设置</legend>
		</fieldset>
		<div class="layui-form-item">
			<label class="layui-form-label"><span style="color:red;">*</span>产品编码</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="good_sn" placeholder="不填写系统自动生成" value="<?php echo htmlentities($info['good_sn']); ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label"><span style="color:red;">*</span>产品名称</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="title" value="<?php echo htmlentities($info['title']); ?>" datatype="*">
			</div>
		</div>

		<div class="layui-form-item">
			<label class="layui-form-label">产品排序</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="sort" value="<?php echo !empty($info['sort']) ? htmlentities($info['sort']) :  '99'; ?>">
			</div>
		</div>
		<div class="layui-form-item">
	    	<label class="layui-form-label">状态</label>
	    	<div class="layui-input-block">
	    		<?php $state = isset($info['status']) ? $info['status'] : '1'; ?>
	      		<input type="radio" name="status" value="1" title="显示" <?php if($state == 1): ?> checked="" <?php endif; ?>>
	      		<input type="radio" name="status" value="2" title="隐藏" <?php if($state == 2): ?> checked="" <?php endif; ?>>
	    	</div>
	  	</div>
		<div class="layui-form-item">
	    	<div class="layui-input-block">
	    		<input type="hidden" name="id" value="<?php echo htmlentities($info['id']); ?>">
	      		<button class="layui-btn layui-btn-normal">保存</button>
	      		<button type="reset" class="layui-btn layui-btn-primary">重置</button>
	    	</div>
	  	</div>
	</form>
</div>

			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
var webRoot = "<?php echo request()->root(true); ?>/";
var webControl = "<?php echo request()->controller(); ?>";
Do.ready('common',function(){ base.frame(); });
</script>

<script type="text/javascript">
var mixin = {};
layui.use('form',function(){
	var form = layui.form
});
</script>

</body>
</html>