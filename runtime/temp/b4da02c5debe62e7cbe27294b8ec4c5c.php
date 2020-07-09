<?php /*a:3:{s:61:"/www/web/youzuo/application/backman/view/detection/index.html";i:1583393380;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1583393382;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1583393382;}*/ ?>
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
	    <li><a href="<?php echo url('option'); ?>">添加流程</a></li>
  	</ul>
</div>
<div class="demoTable" style="margin-top: 25px;margin-left:20px;">
	关键词：
	<div class="layui-inline" style="width:200px;">
		<input class="layui-input" name="title" id="title" placeholder="流程编号、流程名称、流程备注" autocomplete="off">
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
	<a class="layui-btn layui-btn-xs" lay-event="copy" >复制</a>
	<a class="layui-btn layui-btn-xs" lay-event="son" >流程预览</a>
	<a class="layui-btn layui-btn-xs" href="{{d.lc}}">流程列表</a>
  	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" data-del data-id="{{d.id}}" data-table="user">删除</a>
</script>
<script type="text/html" id="image">
	<img src="{{d.image}}" height="30">
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
layui.use(['table','upload'], function(){
	var table = layui.table;
	var upload = layui.upload;
	table.render({
		elem: '#data_table',
		url:SlefUrl,
		limit:20,
		cellMinWidth: 80,
		cols: [[
	      	{field:'id',width:80, title: 'ID', sort: true,align:'center'}
	      	,{field:'detection_sn', title: '流程编号', width:80,align:'center'}
	      	,{field:'name', title: '流程名称'}
	      	,{field:'num', title: '环节数量',width:100}
			,{field:'content', title: '流程说明',align:'center',event: 'setSign'}
	      	,{field:'status', width:100, title: '状态',toolbar: '#sysTar'}
	      	,{field:'timestamp', title: '创建时间',width:150}
	      	,{field:'op', title: '操作',toolbar: '#barTar', width:400,align:'center'}
	    ]],
	    page: true,
        id: 'testReload'
	});
	table.on('tool(data_table)', function(obj){
		var data = obj.data;
		data.table = "customer";
		if(obj.event === 'del'){
			layer.confirm('确定删除吗？', function(index){
	        	layer.close(index);
	       		form.formDel(data,obj);
	      	});
		}

		 if(obj.event === 'son'){
			var result = {};
			result['id'] = data.id
			$.post(webRoot+ webControl+'/getson',result,function(res){
				
				var str = '';
				for( var i in res.data ){
					str = str + '<div style="margin: 10px 30px 10px 30px;line-height:35px;background:rgba(92, 189, 244, 1);font-size:16px;color:#fff;"><span style="width:200px;display:inline-block;text-align:center;">环节名称 : '+ res.data[i]['name'] +'</span> <span style="width:200px;display:inline-block;text-align:center;">录入类型'+ res.data[i]['type'] +'</span> '+res.data[i]['link']+'</div>'
				}
				//一个公告层
				layer.open({
					type: 1
					,area:['600px','500px']
					,offset: 'auto' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
					,id: 'layerDemoauto' //防止重复弹出
					,content: '<div style="padding: 10px 0 0 50px;">流程编号 : '+ data.detection_sn +'</div><div style="padding: 10px 0 0 50px;">流程名称 : '+ data.name +'</div>' + str
					,btn: '关 闭'
					,btnAlign: 'c' //按钮居中
					,shade: 0 //不显示遮罩
					,yes: function(){
					layer.closeAll();
					}
				});
			})
		      
	    }

		if(obj.event === 'copy'){
                layer.confirm('确定复制检测流程吗？', function(index){
                    var result = {};
                    result['id'] = data.id;
                    $.post(webRoot+ webControl+'/copy',result,function(res){
                        layer.close(index);
                        layer.alert(res.msg);
                        if( res.code ){
                            var customer = $('#customer');
                            var goods = $('#goods');
                            var detection = $('#detection');
                            var like = $('#like');
                            var status = $('#status');
                            var start_time = $('#start_time');
                            var end_time = $('#end_time');

                            //执行重载
                            table.reload('testReload', {
                                page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                                ,where: {
                                    customer: customer.val(), goods: goods.val(),detection:detection.val(),like:like.val(),status:status.val(),start_time:start_time.val(),end_time:end_time.val()
                                }
                            }, 'data');
                        }
                    },'json')
                });
            }


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