<?php /*a:3:{s:66:"/www/web/codecheck/application/backman/view/detection/lcindex.html";i:1594223536;s:55:"/www/web/codecheck/application/backman/view/layout.html";i:1594223536;s:52:"/www/web/codecheck/application/backman/view/nav.html";i:1594223536;}*/ ?>
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
	    <li class="layui-this">流程环节设置</li>
	    <li><a href="<?php echo url('lcoption',['id'=>$info['id']]); ?>">添加环节</a></li>
  	</ul>
</div>
<div class="demoTable" style="margin-top: 25px;margin-left:30px;font-size:18px;font-weight: bold;">
	 流程id：
	<div class="layui-inline" style="margin-right:30px;">
		<!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		<?php echo htmlentities($info['id']); ?>
	</div>
	<!-- <button class="layui-btn" data-type="reload">搜索</button> -->
	流程编号：
	<div class="layui-inline" style="margin-right:30px;">
		<!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		<?php echo htmlentities($info['detection_sn']); ?>
	</div>
	流程名称：
	<div class="layui-inline">
		<!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		<?php echo htmlentities($info['name']); ?>
	</div>
</div>
<div class="layui-card-body">
	<div data-model="table-bind">
		<table class="layui-hide" id="data_table" lay-filter="data_table" data-table></table>
	</div>
</div>
<script type="text/html" id="barTar">
	<a class="layui-btn layui-btn-xs" href="{{d.op}}">编辑</a>
	<a class="layui-btn layui-btn-xs" href="{{d.spec}}">表格规则</a>
  	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" data-del data-id="{{d.id}}" data-table="user">删除</a>
</script>
<script type="text/html" id="image">
	<img src="{{d.image}}" height="30">
</script>
<script type="text/html" id="input_type">
	{{#  if(d.type == 0){ }}
  	<button type="button" class="layui-btn layui-btn-normal">单条</button>
  	{{#  } else { }}
  	<button type="button" class="layui-btn">多条</button>
  	{{#  } }}
</script>
<script type="text/html" id="sysTar">
	{{#  if(d.status == 1){ }}
  	<button type="button" class="layui-btn layui-btn-normal">启用</button>
  	{{#  } else { }}
  	<button type="button" class="layui-btn layui-btn-danger">冻结</button>
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
		url:SlefUrl,
		limit:20,
		cellMinWidth: 80,
		cols: [[
	      	{field:'id',width:50, title: 'ID', sort: true,align:'center'}
	      	,{field:'d_son_sn', title: '环节编号', width:180,align:'center'}
	      	,{field:'name', title: '环节名称'}
	      	,{field:'content', title: '环节描述'}
	      	,{field:'type', title: '录入类型',width:80,toolbar: '#input_type'}
	      	,{field:'input_staff', title: '录入人员',width:150}
	      	,{field:'sort', width:120, title: '排序(点击修改)',edit:'number',event:'sort'}
	      	,{field:'status', width:100, title: '状态',toolbar: '#sysTar'}
	      	,{field:'updated_time', title: '更新时间',width:150}
	      	,{field:'op', title: '操作',toolbar: '#barTar', width:400,align:'center'}
	    ]],
	    page: true,
        id: 'testReload'
	});
	table.on('tool(data_table)', function(obj){
		var data = obj.data;
		
		data.table = "detection_son";
		if(obj.event === 'del'){
			layer.confirm('确定删除吗？', function(index){
	        	layer.close(index);
	       		form.formDel2(data,obj,'sondel');
	      	});
		}

		if(obj.event === 'content'){
		      //示范一个公告层
		      layer.open({
		        type: 1
		        ,offset: 'auto' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
		        ,id: 'layerDemoauto' //防止重复弹出
		        ,content: '<div style="padding: 20px 100px;">'+ data.content +'</div>'
		        ,btn: '关 闭'
		        ,btnAlign: 'c' //按钮居中
		        ,shade: 0 //不显示遮罩
		        ,yes: function(){
		          layer.closeAll();
		        }
		      });
	    }



	});
	table.on('edit(data_table)', function(obj){
		var value = obj.value //得到修改后的值
		,data = obj.data //得到所在行所有键值
		,field = obj.field; //得到字段
		var result = {}
		result['table'] = 'detection_son';
		result['id'] = data.id;
		result['value'] = value;
		result['field'] = field;
		// layer.msg('[ID: '+ data.id +'] ' + field + ' 字段更改为：'+ value);
		$.post(webRoot+ webControl+'/modifysort',result,function(res){
			layer.msg(res.msg);
			if( res.code ){
				window.onload()
			}
		},'json')
	});
    var $ = layui.$, active = {
        reload: function(){
            var title = $('#title');

            //执行重载
            table.reload('testReload', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
                    title: title.val()
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