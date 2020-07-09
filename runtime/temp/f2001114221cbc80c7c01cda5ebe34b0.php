<?php /*a:3:{s:60:"/www/web/codecheck/application/backman/view/staff/index.html";i:1594252716;s:55:"/www/web/codecheck/application/backman/view/layout.html";i:1594223536;s:52:"/www/web/codecheck/application/backman/view/nav.html";i:1594223536;}*/ ?>
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
	    <li class="layui-this"><?php echo htmlentities($pathCurrent['name']); ?></li>
	    <li><a href="<?php echo url('option'); ?>">添加核销账号</a></li>
  	</ul>
</div>

<div class="demoTable" style="margin-top: 25px;margin-left:20px;">
	姓名：
	<div class="layui-inline" style="width:250px;">
		<input type="text" class="layui-input" name="name" id="name" placeholder="请输入姓名" autocomplete="off">
	</div>
	&nbsp;
	账号：
	<div class="layui-inline" style="width:250px;">
		<input type="text" class="layui-input" name="phone" id="phone" placeholder="请输入核销员账号" autocomplete="off">
	</div>
	&nbsp;
	账号状态：
	<div class="layui-inline">
		<form class="layui-form">
			<select class="layui-input" name="status" id="status" lay-filter="cid">
				<option value="">请选择状态</option>
				<option value="1">正常</option>
				<option value="-1">冻结</option>
				
			</select>
		</form>
	</div>
	<button class="layui-btn" data-type="reload">搜索</button>
</div>
<div class="layui-card-body">
	<div data-model="table-bind">
		<table class="layui-hide" id="data_table" lay-filter="data_table" data-table></table>
	</div>
</div>
<script type="text/html" id="barTar">
	<a class="layui-btn layui-btn-xs" href="{{d.op}}">编辑</a>
  	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" data-del data-id="{{d.id}}" data-table="staff">删除</a>
</script>
<script type="text/html" id="image">
	<img src="{{d.image}}" height="30">
</script>
<script type="text/html" id="sysTar">
	{{#  if(d.status == 1){ }}
  	<button type="button" class="layui-btn layui-btn-normal">正常</button>
  	{{#  } else { }}
  	<button type="button" class="layui-btn layui-btn-danger">禁用</button>
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
layui.use(['table','upload'], function(){
	var table = layui.table
		,upload = layui.upload;
	table.render({
		elem: '#data_table',
		url:SlefUrl,
		limit:20,
		cellMinWidth: 80,
		cols: [[
	      	{field:'id',width:80, title: 'ID', sort: true,align:'center'}
	      	,{field:'name',width:180, title: '姓名'}
	      	,{field:'phone', title: '账号'}
	      	,{field:'status', title: '状态',toolbar: '#sysTar'}
	      	,{field:'timestamp', title: '添加时间',width:150}
	      	,{field:'op', title: '操作',toolbar: '#barTar', width:150,align:'center'}
	    ]],
	    page: true,
        id: 'testReload'
	});
	table.on('tool(data_table)', function(obj){
		var data = obj.data;
		data.table = "staff";
		if(obj.event === 'del'){
			layer.confirm('确定删除吗？', function(index){
	        	layer.close(index);
	       		form.formDel(data,obj);
	      	});
		}
	});
    var $ = layui.$, active = {
        reload: function(){
            var title = $('#title');
            var cid = $('#cid');
            var status = $('#status');

            //执行重载
            table.reload('testReload', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
                    title: title.val()
                    ,cid: cid.val()
                    ,status: status.val()
                }
            }, 'data');
        },
		excelDemo: function(){
            window.location.href = '/excel/staff_demo.xls'
        }
    };

	upload.render({
		elem: '#uploadExcel'
		,url: '<?php echo url("savestudentImport"); ?>'
		,accept: 'file' //普通文件
		,auto:'xlsx|xls|csv'
		,done: function(res){
			layer.alert(res.msg);
			// setInterval(function(){
			// 	window.location.reload();
			// },3000)
			
		}
	});



    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

	$('#excelDemo').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    var $ = layui.$, active = {
        reload: function(){
            var name = $('#name');
            var phone = $('#phone');
            var status = $('#status');

            //执行重载
            table.reload('testReload', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
                    name: name.val()
                    ,phone: phone.val()
                    ,status: status.val()
                }
            }, 'data');
        }
    };

    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

});
</script>

</body>
</html>