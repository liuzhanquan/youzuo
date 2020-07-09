<?php /*a:3:{s:59:"/www/web/youzuo/application/backman/view/order/lcindex.html";i:1578117479;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1577170781;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1563374948;}*/ ?>
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
				

<style>
	
.admin-sm .layui-btn {
    height: 24px;
    line-height: 24px;
    padding: 0 10px;
}

</style>
<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
	    <li><a href="<?php echo url('index'); ?>"><?php echo htmlentities($pathCurrent['name']); ?></a></li>
	    <li class="layui-this">检测单信息管理</li>
  	</ul>
</div>
<div class="demoTable" style="margin-top: 25px;margin-left:30px;font-size:18px;font-weight: bold;">
	 检测单id：
	<div class="layui-inline" style="margin-right:30px;">
		<!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		<?php echo htmlentities($info['id']); ?>
	</div>
	<!-- <button class="layui-btn" data-type="reload">搜索</button> -->
	检测单号：
	<div class="layui-inline" style="margin-right:30px;">
		<!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		<?php echo htmlentities($info['order_sn']); ?>
	</div>
</div>
<div class="layui-card-body">
	<div data-model="table-bind">
		<table class="layui-hide" id="data_table" lay-filter="data_table" data-table></table>
	</div>
</div>
<script type="text/html" id="barTar">
	<!-- <a class="layui-btn layui-btn-xs" lay-event="modify" >状态</a> -->
	<a class="layui-btn layui-btn-xs" lay-event="son" >流程预览</a>
  	<!-- <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" data-del data-id="{{d.id}}" data-table="user">删除</a> -->
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
    <button type="button" class="layui-btn ">已审核</button>
    {{#  } else if(d.status == 2) { }}
    <button type="button" class="layui-btn layui-btn-normal">已完成</button>
    {{#  } else if(d.status == 3) { }}
    <button type="button" class="layui-btn layui-btn-danger">审核不通过</button>
    {{#  } else { }}
    <button type="button" class="layui-btn layui-btn-warm">未审核</button>
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
var status_arr = ['未审核','合格','不合格'];
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
	      	// ,{field:'status', width:100, title: '状态',toolbar: '#sysTar'}
	      	,{field:'updated_time', title: '最近操作时间',width:200}
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

		if(obj.event === 'son'){
			var result = {};
			result['oid'] = '<?php echo htmlentities($info['id']); ?>'
			result['dsid'] = data.id
			$.post(webRoot+ webControl+'/getson',result,function(res){
				
				var str = '';
				if( data.type == 1 || (data.type == 0 && res.data == '' ) ){
					str = str + '<div style="padding: 10px 50px 0 50px;text-align:right;"><a href="/admin/order/spec/<?php echo htmlentities($info["id"]); ?>/'+data.id+'/-1">添加表单</a></div>';
				}
				for( var i in res.data ){
					
					var status = status_arr[res.data[i]['status']]
					str = str + '<div style="margin: 10px 30px 10px 30px;line-height:35px;background:rgba(92, 189, 244, 1);font-size:16px;color:#fff;"><span style="width:80px;display:inline-block;text-align:center;">表单 : '+ i +'</span> <span style="width:300px;display:inline-block;text-align:center;">录入时间：'+ res.data[i]['created_time'] +'</span> <span style="width:130px;display:inline-block;text-align:center;" id="sort'+res.data[i]['id']+'" num="'+res.data[i]['id']+'">状态：'+ status +'</span>'+res.data[i]['link']+res.data[i]['modify']+res.data[i]['del']+'</div>'
				}
				//表单信息---------------------
				layer.open({
					type: 1
					,area:['800px','500px']
					,offset: 'auto' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
					,id: 'layerDemoauto' //防止重复弹出
					,content: '<div style="padding: 10px 0 0 50px;">环节编号 : '+ data.d_son_sn +'</div><div style="padding: 10px 0 0 50px;">流程名称 : '+ data.name +'</div>' + str
					,btn: '关 闭'
					,btnAlign: 'c' //按钮居中
					,shade: 0 //不显示遮罩
					,yes: function(){
						layer.closeAll();
						
					}
					,success: function(){
						//审核表单信息-------------------
						$('.modify').on('click', function(){
							var that = $(this);
							var getid = $(this).attr('num');
							var status = $(this).attr('status');
							layer.open({
								type: 1
								,title: false //不显示标题栏
								,closeBtn: false
								,area: ['500px','250px;']
								,shade: 0.8
								,id: 'modifyBox' //设定一个id，防止重复弹出
								,btn: ['提交', '取消']
								,btnAlign: 'c'
								,moveType: 1 //拖拽模式，0或者1
								,content: '<div class="" style="width:90%;height:50%;margin:10px auto;"><div class="statusBox" style="margin-top:40px;font-size:15px;"><label>状态：</label><div class="" style="margin-left:20px;display:inline-block;"><label style="margin-right:20px;"><input type="radio" name="status" value="0"> 未审核</label><label style="margin-right:20px;"><input type="radio" name="status" value="1" > 合格</label><label style="margin-right:20px;"><input type="radio" name="status" value="2" > 不合格</label></div> </div>  </div>'
								,success: function(layero){
									if( status == 1 ){
										$('input[name="status"]').eq(1).prop('checked',true);
									}else if( status == 2 ){
										$('input[name="status"]').eq(2).prop('checked',true);
									}else{
										$('input[name="status"]').eq(0).prop('checked',true);
									}
									
									$('.layui-layer-btn0').click(function(){
										var result = {};
										var str1 = '',str2 = '';
										result['id'] = getid;
										$.each( $('input[name="status"]'), function(index){
											if( $(this).prop('checked') == true ){
												result['status'] = $(this).val();
											}
										})
										$.post(webRoot+ webControl+'/update_s_status',result,function(res){
											
											if( res.code ){
												that.attr('status',result['status']);
												$('#sort'+ getid ).html('状态：'+status_arr[result['status']]);
											}
										},'json')
									});
								}
							});
							
						});
						//审核表单信息------------------------------
					}
					
				});
				//表单信息---------------------
			})
		      
	    }

		// modify
		if(obj.event === 'modify'){
                layer.open({
                    type: 1
                    ,title: false //不显示标题栏
                    ,closeBtn: false
                    ,area: ['500px','250px;']
                    ,shade: 0.8
                    ,id: 'modifyBox' //设定一个id，防止重复弹出
                    ,btn: ['提交', '取消']
                    ,btnAlign: 'c'
                    ,moveType: 1 //拖拽模式，0或者1
                    ,content: '<div style="margin:20px 0 0 10px;">环节名称：'+data.name+'</div><div class="" style="width:90%;height:50%;margin:10px auto;"><div class="statusBox" style="margin-top:40px;font-size:15px;"><label>状态：</label><div class="" style="margin-left:20px;display:inline-block;"><label style="margin-right:20px;"><input type="radio" name="status" value="0"> 未审核</label><label style="margin-right:20px;"><input type="radio" name="status" value="1" > 已审核</label><label style="margin-right:20px;"><input type="radio" name="status" value="2" > 已完成</label><label><input type="radio" name="status" value="3" > 审核不通过</label></div> </div>  </div>'
                    ,success: function(layero){
                        if( data.status == 1 ){
                            $('input[name="status"]').eq(1).prop('checked',true);
                        }else if( data.status == 2 ){
                            $('input[name="status"]').eq(2).prop('checked',true);
                        }else if( data.status == 3 ){
                            $('input[name="status"]').eq(3).prop('checked',true);
                        }else{
                            $('input[name="status"]').eq(0).prop('checked',true);
                        }
                        
                        $('.layui-layer-btn0').click(function(){
                            var result = {};
                            var str1 = '',str2 = '';
                            result['oid'] = "<?php echo htmlentities($info['id']); ?>";
                            result['dsid'] = data.id;
                            $.each( $('input[name="status"]'), function(index){
                                if( $(this).prop('checked') == true ){
                                    result['status'] = $(this).val();
                                }
                            })
                            $.post(webRoot+ webControl+'/update_d_status',result,function(res){
                                if( res.code ){
                                    var phone = $('#phone');
                                    var name = $('#user_name');
                                    var order_sn = $('#order_sn');
                                    var status = $('#status');

                                    //执行重载
                                    table.reload('testReload', {
                                        page: {
                                            curr: 1 //重新从第 1 页开始
                                        }
                                        ,where: {
                                            phone: phone.val(), name: name.val(),order_sn:order_sn.val(),status:status.val()
                                        }
                                    }, 'data');
                                }
                            },'json')
                        });
                    }
                });
            }
		// modify


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