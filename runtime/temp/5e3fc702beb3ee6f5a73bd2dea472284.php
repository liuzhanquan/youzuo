<?php /*a:3:{s:64:"/www/web/youzuo/application/backman/view/datacategory/index.html";i:1577445594;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1577170781;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1563374948;}*/ ?>
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
	.addBtnBox{
		width:55%;
		margin:30px auto;
		color:#5CBDF4;
		cursor:pointer;
		
	}
	#textstyle,#textstyle2{
		padding:8px 12px;
		border:1px solid #5CBDF4;
		border-radius:5px;
	}
	
	#listBox{
		margin-top:20px;
		padding-top:10px;
		border-top:1px solid #ccc;
	}
	.sonList{
		margin: 10px auto;
		width:400px;
		height:30px;
	}
	.sonValue{
		width:65%;
		light-height:23px;
		font-size:14px;
		padding:3px;
	}
	.delSonBtn{
		margin-left:10px;
		color:#5CBDF4;
		font-size:15px;
		padding:6px 12px;
		border-radius: 5px;
		border:1px solid #5CBDF4;
		cursor:pointer;
		margin-top:2px;
	}

</style>
<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
	    <li class="layui-this"><?php echo htmlentities($pathCurrent['name']); ?></li>
  	</ul>
</div>
<div class="fileBox" style="margin-top: 25px;margin-left:20px;">

	<button class="layui-btn" id="addDataText" data-id="" text="" data-type="addDataText">添加字典</button>
</div>
<div class="demoTable" style="margin-top: 25px;margin-left:20px;">
	字典名称：
	<div class="layui-inline">
		<input class="layui-input" name="title" id="title" autocomplete="off">
	</div>
	
	<button class="layui-btn" data-type="reload">搜索</button>
</div>

<div class="layui-card-body">
	<div data-model="table-bind">
		<table class="layui-hide" id="data_table" lay-filter="data_table" data-table></table>
	</div>
</div>

<script type="text/html" id="barTar">
	<a class="layui-btn layui-btn-xs" lay-event="modifyDataText" >编辑</a>
  	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" data-del data-id="{{d.id}}" data-table="user">删除</a>
</script>
<script type="text/html" id="image">
	<img src="{{d.image}}" height="30">
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
var dlearr = [];
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
	      	,{field:'name', title: '字典名称',align:'center'}
	      	,{field:'sontotal', title: '数据数量',align:'center'}
	      	,{field:'total', title: '绑定数量',align:'center'}
	      	// ,{field:'sort', title: '排序',width:60,sort: true,align:'center'}
	      	,{field:'op', title: '操作',toolbar: '#barTar',align:'center'}
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
		if(obj.event === 'modifyDataText'){
			dlearr = [];
			var id = data.id;
			var value = data.name;
			var addinputStart = '<div class="sonList" >字典数据：<input class="sonValue" name="datasonname" ';
			var addiinputEnd = 'value="" placeholder="请输入字典数据" />';
			var num = 0;
			var nStr = '';
			var content = '';
			$.post(webRoot+ webControl+'/getdatason',{'id':id},function(res){
				for(var i in res){
					nStr = nStr + '<div class="sonList" >字典数据：<input class="sonValue" name="datasonname" value="'+ res[i]['name'] +'" placeholder="请输入字典数据" /><span class="delSonBtn" vid="'+ res[i]['id'] +'" num="'+num+'"  >删除</span></div>'
					num++
				}
				content= '<div style="font-size:18px;margin:5px;" id="addDataBox" pid="'+ id +'">添加字典</div><hr style="height:1px;margin-bottom:40px;"><div class="" style="margin: 10px auto;width:400px;height:30px;">字典名称：<input style="width:80%;light-height:23px;font-size:14px;padding:3px;" name="dataname" class="parValue" value="'+value+'" placeholder="请输入字典名称" /></div> <div id="listBox">'+ nStr +'</div> <div class="addBtnBox"><span id="textstyle">添加字典</span></div>';
				



				

				layer.open({
				type: 1
				,title: false //不显示标题栏
				,closeBtn: false
				,area: ['500px','400px;']
				,shade: 0.8
				,id: 'modifyBox' //设定一个id，防止重复弹出
				,btn: ['修改', '取消']
				,btnAlign: 'c'
				,moveType: 1 //拖拽模式，0或者1
				,content: content
				,success: function(index,layero){

					$('#textstyle').on('click',function(){
						num++
						$('#listBox').append( addinputStart + addiinputEnd + '<span class="delSonBtn" vid="" num="'+ num +'" >删除</span></div>' );
					})

					$('#listBox').on('click','.delSonBtn',function(){
						
						var ind = 0;
						var thisnum = $(this).attr('num')
						$('#listBox .delSonBtn').length
						for( var i = 0 ; i < $('#listBox .delSonBtn').length ; i++ ){
							if( $('#listBox .delSonBtn').eq(i).attr('num') == thisnum ){
								ind = i;
							}
						}
						var sonid = $(this).attr('vid');

						// if( sonid ){
						// 	$.post(webRoot+ webControl+'/del2',{id:sonid},function(res){
						// 		if( res.code  ){
						// 			$('#listBox .sonList').eq(ind).remove();
						// 		}else{
						// 			alert(res.msg);
						// 		}
						// 	})
						// }else{
							$('#listBox .sonList').eq(ind).remove();
							dlearr.push(sonid);
						// }

					})


					$('#textstyle2').on('click',function(){
						var result = {};
						result['dataname'] = $('.parValue').val();
						
						result['datasonname'] = getAllSon();
					})

					function getAllSon(){
						var arr = [];
						var num = 0;
						$.each($('#listBox .sonValue'),function(index){
							if( $(this).val() != '' ){
								arr[num] = {};
								arr[num]['name'] = $(this).val();
								arr[num]['id'] = $('.delSonBtn').eq(index).attr('vid');
								num++;
							}
						})
						return arr;
					}

					$('.layui-layer-btn0').click(function(){
						var result = {};
						result['dataname'] = $('.parValue').val();
						result['dataid'] = $('#addDataBox').attr('pid');
						result['dlearr'] = dlearr;
						// console.log(getAllSon());
						// result['datasonname'] = JSON.stringify(getAllSon());
						result['datasonname'] = getAllSon();
						$.post(webRoot+ webControl+'/dataadd',result,function(res){
							
							if( res.code == 1 ){
								alert(res.msg);
								setInterval(function(){
									window.location.reload();
								},1000)
							}else{
								alert(res);
							}
							
						},'json')
					});

				}
			});



			},'json')


			
			
			


		}



	});
	var $ = layui.$, active = 
	{
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
		addDataText: function(){
			var that = this;
			var id = $(that).attr('data-id');
			var value = $(that).attr('text');
			var addinputStart = '<div class="sonList" >字典数据：<input class="sonValue" name="datasonname" ';
			var addiinputEnd = 'value="" placeholder="请输入字典数据" />';
			var num = 0;
			var content = '<div style="font-size:18px;margin:5px;" id="addDataBox" pid="">添加字典</div><hr style="height:1px;margin-bottom:40px;"><div class="" style="margin: 10px auto;width:400px;height:30px;">字典名称：<input style="width:80%;light-height:23px;font-size:14px;padding:3px;" name="dataname" class="parValue" value="'+value+'" placeholder="请输入字典名称" /></div> <div id="listBox">'+ addinputStart + addiinputEnd + '<span class="delSonBtn" num="'+ num +'" >删除</span></div>' +'</div> <div class="addBtnBox"><span id="textstyle">添加字典</span></div>';
			
			
			layer.open({
				type: 1
				,title: false //不显示标题栏
				,closeBtn: false
				,area: ['500px','400px;']
				,shade: 0.8
				,id: 'modifyBox' //设定一个id，防止重复弹出
				,btn: ['添加', '取消']
				,btnAlign: 'c'
				,moveType: 1 //拖拽模式，0或者1
				,content: content
				,success: function(index,layero){

					$('#textstyle').on('click',function(){
						num++
						$('#listBox').append( addinputStart + addiinputEnd + '<span class="delSonBtn" vid="" num="'+ num +'" >删除</span></div>' );
					})

					$('#listBox').on('click','.delSonBtn',function(index){
						var ind = 0;
						var thisnum = $(this).attr('num')
						$('#listBox .delSonBtn').length
						for( var i = 0 ; i < $('#listBox .delSonBtn').length ; i++ ){
							if( $('#listBox .delSonBtn').eq(i).attr('num') == thisnum ){
								ind = i;
							}
						}
						$('#listBox .sonList').eq(ind).remove();

					})


					$('#textstyle2').on('click',function(){
						var result = {};
						result['dataname'] = $('.parValue').val();
						result['datasonname'] = getAllSon();
					})

					function getAllSon(){
						var arr = [];
						var num = 0;
						$.each($('#listBox .sonValue'),function(index){
							if( $(this).val() != '' ){
								arr[num] = {};
								arr[num]['name'] = $(this).val();
								arr[num]['id'] = $('.delSonBtn').eq(index).val();
								num++;
							}
						})
						return arr;
					}

					$('.layui-layer-btn0').click(function(){
						var result = {};
						result['dataname'] = $('.parValue').val();
						result['dataid'] = $('#addDataBox').attr('pid');
						// console.log(getAllSon());
						// result['datasonname'] = JSON.stringify(getAllSon());
						result['datasonname'] = getAllSon();
						$.post(webRoot+ webControl+'/dataadd',result,function(res){
							
							if( res.code == 1 ){
								alert(res.msg);
								setInterval(function(){
									window.location.reload();
								},1000)
							}else{
								alert(res);
							}
							
						},'json')
					});

				}
			});
		}

    }

	

		// 
		

    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });
	$('#addDataText').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });
});
</script>

</body>
</html>