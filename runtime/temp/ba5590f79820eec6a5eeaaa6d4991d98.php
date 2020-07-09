<?php /*a:3:{s:56:"/www/web/youzuo/application/backman/view/rate/index.html";i:1582634802;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1582615244;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1582615247;}*/ ?>
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
  	</ul>
<!-- </div>
<div class="fileBox" style="margin-top: 25px;margin-left:20px;">

	<button class="layui-btn" id="uploadExcel" data-type="">批量导入</button>
	<button class="layui-btn" id="excelDemo" data-type="excelDemo" style="margin-left:10px;" >表格模板下载</button>
</div> -->

<div class="demoTable" style="margin-top: 25px;margin-left:20px;">
	产品信息：
	<div class="layui-inline">
		<input class="layui-input" name="title" id="title" placeholder="产品编号、产品名称" style="width:220px;" autocomplete="off">
	</div>
	<!-- <div class="layui-inline">
		<form class="layui-form">
			<select class="layui-input" name="gid" id="gid" lay-filter="gid">
				<option value="">请选择产品分类</option>
				<?php foreach($category as $vo): ?>
				<option value="<?php echo htmlentities($vo['id']); ?>"><?php echo $vo['cname']; ?></option>
				<?php endforeach; ?>
			</select>
		</form>
	</div> -->
	产品型号：
	<div class="layui-inline">
		<input class="layui-input" name="spec" id="spec" placeholder="产品型号" style="width:220px;" autocomplete="off">
	</div>
	客户信息：
	<div class="layui-inline">
		<input class="layui-input" name="customer" id="customer" placeholder="客户编号、客户名称、手机号、负责人" style="width:220px;"  autocomplete="off">
	</div>
	<!-- <div class="layui-inline">
		<form class="layui-form">
			<select class="layui-input" name="cid" id="cid" lay-filter="cid">
				<option value="">请选择客户分类</option>
				<?php foreach($cus_category as $vo): ?>
				<option value="<?php echo htmlentities($vo['id']); ?>"><?php echo $vo['cname']; ?></option>
				<?php endforeach; ?>
			</select>
		</form>
	</div> -->
	
	<button class="layui-btn" data-type="reload">搜索</button>
</div>
<div class="layui-card-body">
	<div data-model="table-bind">
		<table class="layui-hide" id="data_table" lay-filter="data_table" data-table></table>
	</div>
</div>
<script type="text/html" id="toolbarDemo">
	<div class="layui-btn-container">
	</div>
  </script>
<!-- <script type="text/html" id="barTar">
	<a class="layui-btn layui-btn-xs" href="{{d.op}}">编辑</a>
  	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" data-del data-id="{{d.id}}" data-table="user">删除</a>
</script> -->
<script type="text/html" id="image">
	<img src="{{d.image}}" height="30">
</script>
<script type="text/html" id="sysTar">
	{{#  if(d.status == 1){ }}
  	<button type="button" class="layui-btn layui-btn-normal">正常</button>
  	{{#  } else { }}
  	<button type="button" class="layui-btn layui-btn-danger">关闭</button>
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
		toolbar: '#toolbarDemo' //开启头部工具栏，并为其绑定左侧模板
		,defaultToolbar: ['filter', 'exports'
		//, 'print'
		],
		limit:20,
		cellMinWidth: 80,
		cols: [[
	      	// {field:'id',width:80, title: 'ID', sort: true,align:'center'}
	      	{field:'good_sn', title: '产品编号', width:180,align:'center'}
	      	,{field:'good_name', title: '产品名称'}
	      	// ,{field:'price', title: '产品价格'}
	      	,{field:'spec', title: '产品型号'}
	      	// ,{field:'status', width:100, title: '商品状态',toolbar: '#sysTar'}
	      	,{field:'pass', title: '合格率',sort: true,align:'center'}
	      	,{field:'warn', title: '警告率',sort: true,align:'center'}
	      	// ,{field:'unpass', title: '不合格率',sort: true,align:'center'}
	      	// ,{field:'runing', title: '待评价',sort: true,align:'center'}
	      	,{field:'count', title: '产品总数',sort: true,align:'center'}
	      	// ,{field:'op', title: '操作',toolbar: '#barTar', width:150,align:'center'}
	    ]],
	    page: true,
        id: 'testReload'
	});
	table.on('tool(data_table)', function(obj){
		var data = obj.data;
		data.table = "goods";
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
            var gid = $('#gid');
            var spec = $('#spec');
            var customer = $('#customer');
            var cid = $('#cid');

            //执行重载
            table.reload('testReload', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
                    title: title.val()
                    ,gid : gid.val()
                    ,spec : spec.val()
                    ,customer: customer.val()
                    ,cid : cid.val()
                }
            }, 'data');
		},
		
		excelDemo: function(){
            window.location.href = '/excel/product_demo.xls'
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

		// 
		

    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });
	$('#excelDemo').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });
});
</script>

</body>
</html>