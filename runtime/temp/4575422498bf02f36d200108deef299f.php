<?php /*a:3:{s:68:"D:\phpstudy\WWW\youzuo\application\backman\view\staff\powerlist.html";i:1575959340;s:59:"D:\phpstudy\WWW\youzuo\application\backman\view\layout.html";i:1566201690;s:56:"D:\phpstudy\WWW\youzuo\application\backman\view\nav.html";i:1563374948;}*/ ?>
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
	    <li class="layui-this"><?php echo htmlentities($pathCurrent['name']); ?></li>
	    <!-- <li><a href="<?php echo url('power_op'); ?>">新增权限</a></li> -->
  	</ul>
</div>
<div class="layui-card-body" data-model="table-bind">
	<blockquote class="layui-elem-quote layui-quote-nm">注：员工分类最多添加2级</blockquote>
	<table class="layui-table" data-table>
		<thead>
	      	<tr>
	        	<th width="80"><center>ID</center></th>
	        	<th>分类名称</th>
	        	<th width="150"><center>分类排序</center></th>
	        	<th width="150"><center>操作</center></th>
	      	</tr> 
	    </thead>
	    <tbody>
	    	<?php foreach($list as $vo): ?>
	    	<tr>
	    		<td><center><?php echo htmlentities($vo['id']); ?></center></td>
	    		<td><?php echo $vo['cname']; ?></td>
	    		<td><center><?php echo htmlentities($vo['sort']); ?></td>
	    		<td><center>
	    			<a class="layui-btn layui-btn-xs" href="<?php echo url('power_op',['id'=>$vo['id']]); ?>">编辑</a>
					<?php if($vo['id'] != '51'): ?>
	    			<a class="layui-btn layui-btn-danger layui-btn-xs" data-del data-id="<?php echo htmlentities($vo['id']); ?>" data-table="sta_category">删除</a><?php endif; ?></center>
	    		</td>
	    	</tr>
	    	<?php endforeach; ?>
	    </tbody>
	</table>
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
</script>

</body>
</html>