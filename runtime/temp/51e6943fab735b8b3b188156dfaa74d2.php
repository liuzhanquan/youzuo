<?php /*a:3:{s:69:"D:\phpstudy\WWW\youzuo\application\backman\view\setting\group_op.html";i:1563518814;s:59:"D:\phpstudy\WWW\youzuo\application\backman\view\layout.html";i:1566201690;s:56:"D:\phpstudy\WWW\youzuo\application\backman\view\nav.html";i:1563374948;}*/ ?>
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
	    <li><a href="<?php echo url('group'); ?>"><?php echo htmlentities($pathCurrent['name']); ?></a></li>
	    <li class="layui-this"><?php if(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty())): ?>新增<?php else: ?>编辑<?php endif; ?>权限组</li>
  	</ul>
</div>
<div class="layui-card-body">
	<form class="layui-form layui-form-pane" data-model="form-submit">
		<div class="layui-form-item">
			<label class="layui-form-label">组别名称</label>
			<div class="layui-input-block">
	           	<input type="text" class="layui-input" name="name" datatype="*" value="<?php echo htmlentities($info['name']); ?>">
			</div>
		</div>
		<div class="layui-form-item layui-form-text">
			<label class="layui-form-label">拥有权限 <span>( 勾选即代表赋予权限 )</span></label>
			<table  class="layui-table layui-input-block">
				<tbody>
					<?php foreach($topNav as $vo): ?>
					<tr>
						<td width="150">
							<input type="checkbox" name="menu_power[]" value="<?php echo htmlentities($vo['id']); ?>" lay-skin="primary" <?php if(in_array($vo['id'],$menu)){ echo 'checked';} ?> title="<?php echo htmlentities($vo['name']); ?>" lay-filter="father">
						</td>
						<td style="padding: 0;">
							<table  class="layui-table layui-input-block">
								<?php if(is_array($vo['children']) || $vo['children'] instanceof \think\Collection || $vo['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?>
								<tr>
									<td style="border-width: 0;border-top-width: 1px;">
										<input type="checkbox" name="menu_power[]" value="<?php echo htmlentities($sub['id']); ?>" lay-skin="primary" <?php if(in_array($sub['id'],$menu)){ echo 'checked';} ?> title="<?php echo htmlentities($sub['name']); ?>" lay-filter="son">
									</td>
								</tr>
								<?php endforeach; endif; else: echo "" ;endif; ?>
							</table>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="layui-form-item">
			<input type="hidden" name="id" value="<?php echo htmlentities($info['id']); ?>">
	  		<button class="layui-btn layui-btn-normal">保存</button>
	  		<button type="reset" class="layui-btn layui-btn-primary">重置</button>
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
	form.on('checkbox(father)', function(data){
		if(data.elem.checked){
            $(data.elem).parent().siblings('td').find('input').prop("checked", true);
            form.render(); 
        }else{
           $(data.elem).parent().siblings('td').find('input').prop("checked", false);
            form.render();  
        }
	})
});
</script>

</body>
</html>