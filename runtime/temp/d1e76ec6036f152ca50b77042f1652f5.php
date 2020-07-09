<?php /*a:3:{s:60:"/www/web/codecheck/application/backman/view/goods/index.html";i:1594236218;s:55:"/www/web/codecheck/application/backman/view/layout.html";i:1594223536;s:52:"/www/web/codecheck/application/backman/view/nav.html";i:1594223536;}*/ ?>
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
	    <li><a href="<?php echo url('option'); ?>">添加产品</a></li>
  	</ul>
</div>
<div class="fileBox" style="margin-top: 25px;margin-left:20px;">

	<!--<button class="layui-btn" id="uploadExcel" data-type="">批量导入</button>-->
	<!--<button class="layui-btn" id="excelDemo" data-type="excelDemo" style="margin-left:10px;" >表格模板下载</button>-->
</div>
<div class="demoTable" style="margin-top: 25px;margin-left:20px;">

	添加时间：
	<div class="layui-inline">
		<input type="text" class="layui-input" id="start_time" name="start_time" placeholder="yyyy-MM-dd HH:mm:ss">
	</div>
	-
	<div class="layui-inline">
		<input type="text" class="layui-input" id="end_time" name="end_time" placeholder="yyyy-MM-dd HH:mm:ss">
	</div>
	&nbsp;
	 关键词：
	<div class="layui-inline">
		<input class="layui-input" name="title" id="title" placeholder="产品编号、名称" autocomplete="off">
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
  	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" data-del data-id="{{d.id}}" data-table="user">删除</a>
</script>
<script type="text/html" id="image">
	<img src="{{d.image}}" height="30">
</script>
<script type="text/html" id="sysTar">
	{{#  if(d.status == 1){ }}
  	<button type="button" class="layui-btn layui-btn-normal">显示</button>
  	{{#  } else { }}
  	<button type="button" class="layui-btn layui-btn-danger">隐藏</button>
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
layui.use(['table','upload','laydate'], function(){
	var table = layui.table
		,laydate = layui.laydate
		,upload = layui.upload;
	table.render({
		elem: '#data_table',
		url:SlefUrl,
		limit:20,
		cellMinWidth: 80,
		cols: [[
	      	{field:'id',width:80, title: 'ID', sort: true,align:'center'}
	      	,{field:'good_sn', title: '产品编号', width:180,align:'center'}
	      	,{field:'title', title: '产品名称'}
	      	// ,{field:'price', title: '产品价格'}
	      	,{field:'status', width:100, title: '商品状态',toolbar: '#sysTar'}
	      	,{field:'sort', title: '排序',width:80,sort: true,align:'center'}
	      	,{field:'timestamp', title: '添加时间',width:150}
	      	,{field:'op', title: '操作',toolbar: '#barTar', width:150,align:'center'}
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

    //日期时间选择器
    laydate.render({
        elem: '#start_time'
        ,type: 'datetime'
    });
    laydate.render({
        elem: '#end_time'
        ,type: 'datetime'
    });


	var $ = layui.$, active = 
	{
        reload: function(){
            var title = $('#title');
            var cid = $('#cid');

            //执行重载
            table.reload('testReload', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
                    title: title.val()
                    ,cid: cid.val()
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