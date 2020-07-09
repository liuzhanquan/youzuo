<?php /*a:3:{s:59:"/www/web/youzuo/application/backman/view/setting/index.html";i:1582615255;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1582615244;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1582615247;}*/ ?>
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
				
<div class="layui-card-header"><?php echo htmlentities($pathCurrent['name']); ?></div>
<div class="layui-card-body">
	<form class="layui-form" data-model="form-submit">
		<?php foreach($list as $vo): if($vo['is_radio'] == 1): ?>
		<div class="layui-form-item">
	    	<label class="layui-form-label"><?php echo htmlentities($vo['title']); ?></label>
	    	<div class="layui-input-block">
	      		<input type="radio" name="<?php echo htmlentities($vo['name']); ?>" value="1" title="开启" <?php if($vo['value'] == 1): ?> checked="" <?php endif; ?>>
	      		<input type="radio" name="<?php echo htmlentities($vo['name']); ?>" value="2" title="关闭" <?php if($vo['value'] == 2): ?> checked="" <?php endif; ?>>
	    	</div>
	    	<div class="layui-form-mid layui-word-aux"><?php echo htmlentities($vo['remark']); ?></div>
	  	</div>
		<?php elseif($vo['is_radio'] == 2): ?>
		<label class="layui-form-label"><?php echo htmlentities($vo['title']); ?></label>
		<div class="layui-input-block">
			<div class="layui-upload">
				<button type="button" class="layui-btn" name="a222" id="test1">上传图片</button>
				<input type="hidden" name="<?php echo htmlentities($vo['name']); ?>" id="ewmlogo" value="<?php echo htmlentities($vo['value']); ?>">
				<div class="layui-upload-list">
				<img class="layui-upload-img" id="demo1" src="<?php echo htmlentities($vo['value']); ?>">
				<p id="demoText"></p>
				</div>
			</div> 
		</div>

		<?php else: ?>
		<div class="layui-form-item">
			<label class="layui-form-label"><?php echo htmlentities($vo['title']); ?></label>
			
			<div class="layui-input-block">
				<input type="text" name="<?php echo htmlentities($vo['name']); ?>" lay-verify="title" autocomplete="off" placeholder="请输入<?php echo htmlentities($vo['title']); ?>" class="layui-input" <?php if($vo['is_must'] == 1): ?> datatype="*" <?php endif; ?> value="<?php echo htmlentities($vo['value']); ?>">
				<div class="layui-form-mid layui-word-aux"><?php echo htmlentities($vo['remark']); ?></div>
			</div>
		</div>
		<?php endif; ?>
		<?php endforeach; ?>
		<div class="layui-form-item">
	    	<div class="layui-input-block">
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
layui.use(['form','upload'],function(){
	var form = layui.form
	var upload = layui.upload



	 //普通图片上传
	 var uploadInst = upload.render({
		elem: '#test1'
		,url: '/admin/Upload/alone'
		,before: function(obj){
		//预读本地文件示例，不支持ie8
		obj.preview(function(index, file, result){
			$('#demo1').attr('src', result); //图片链接（base64）
		});
		}
		,done: function(res){
		//如果上传失败
		if(res.status == 0){
			return layer.msg('上传失败');
		}else{
			$('#ewmlogo').val(res.data.url);
		}
		//上传成功

		}
		,error: function(){
		//演示失败状态，并实现重传
		var demoText = $('#demoText');
		demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
		demoText.find('.demo-reload').on('click', function(){
			uploadInst.upload();
		});
		}
	});



});
var mixin = {};
</script>

</body>
</html>