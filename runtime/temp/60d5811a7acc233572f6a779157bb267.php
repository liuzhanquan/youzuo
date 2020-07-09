<?php /*a:3:{s:63:"/www/web/youzuo/application/backman/view/material/index_op.html";i:1578024172;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1577170781;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1563374948;}*/ ?>
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
	    <li class="layui-this">新增图库</li>
	    <li><a href="<?php echo url('index_cate'); ?>">分类管理</a></li>
  	</ul>
</div>
<div class="layui-card-body">
	<form class="layui-form" data-model="form-submit">
		<div class="layui-form-item">
			<label class="layui-form-label">所属分类</label>
			<div class="layui-input-block">
				<select name="cid" lay-filter="aihao">
					<option value="0">未分类</option>
					<?php foreach($group as $vo): ?>
						<option value="<?php echo htmlentities($vo['id']); ?>"><?php echo $vo['cname']; ?></option>
	           		<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">图片库</label>
			<div class="layui-input-block">
				<div class="photo-group">
					<ul class="clearfloat" id="images-thumbnails">
						<li>
							<div class="rc-upload">
							<a class="add-goods" href="javascript:;" data-model="upload-photo"
		                        data-img-list='false'
		                        data-img-name="image"
		                        data-img-warp="#images-thumbnails"
		                        data-id="imageUpload">+ 添加图片</a>
							</div>
							<script type="text/plain" id="imageUpload" style="display:none;" ></script>
						</li>
					</ul>
				</div>
        	</div>
			<div class="layui-input-block">
				<div class="layui-form-mid layui-word-aux">（推荐尺寸为750px*1024px，大小不超过200k，支持jpeg、jpg、png、gif、jpeg格式）</div>
        	</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">图片名称</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="name" value="<?php echo htmlentities($info['name']); ?>" datatype="*">
			</div>
		</div>
		<div class="layui-form-item">
	    	<div class="layui-input-block">
	    		<input type="hidden" name="id" value="">
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
	var form = layui.form;
});
</script>

</body>
</html>