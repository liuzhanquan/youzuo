<?php /*a:3:{s:62:"/www/web/codecheck/application/backman/view/setting/group.html";i:1594223536;s:55:"/www/web/codecheck/application/backman/view/layout.html";i:1594223536;s:52:"/www/web/codecheck/application/backman/view/nav.html";i:1594223536;}*/ ?>
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
	    <li class="layui-this"><a href="<?php echo url('group'); ?>"><?php echo htmlentities($pathCurrent['name']); ?></a></li>
	    <li><a href="<?php echo url('group_op'); ?>">新增权限组</a></li>
  	</ul>
</div>
<div class="layui-card-body">
	<div data-model="table-bind">
		<table class="layui-hide" id="data_table" lay-filter="data_table" data-table></table>
	</div>
</div>
<script type="text/html" id="barTar">
	<a class="layui-btn layui-btn-xs" href="{{d.op}}">编辑</a>
	{{#  if(d.is_sys == 0){ }}
  	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" data-del data-id="{{d.id}}" data-table="user">删除</a>
  	{{#  } }}
</script>
<script type="text/html" id="sysTar">
	{{#  if(d.is_sys == 0){ }}
  	<button type="button" class="layui-btn layui-btn-danger">否</button>
  	{{#  } else { }}
  	<button type="button" class="layui-btn layui-btn-normal">是</button>
  	{{#  } }}
</script>


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
var SlefUrl = this.location.href;
layui.use('table', function(){
	var table = layui.table;
	table.render({
		elem: '#data_table',
		limit:20,
		url:SlefUrl,
		cellMinWidth: 80,
		cols: [[
	      	{field:'id',width:80, title: 'ID', sort: true,align:'center'}
	      	,{field:'name',title: '组别名称'}
	      	,{field:'is_sys',width:100, toolbar: '#sysTar',title: '系统内置',align:'center'}
	      	,{field:'op', title: '操作',toolbar: '#barTar', width:150}
	    ]],
	    page: true
	});
	table.on('tool(data_table)', function(obj){
		var data = obj.data;
		data.table = "group";
		if(obj.event === 'del'){
			layer.confirm('确定删除吗？', function(index){
	        	layer.close(index);
	       		form.formDel(data,obj);
	      	});
		}
	});
});
</script>

</body>
</html>