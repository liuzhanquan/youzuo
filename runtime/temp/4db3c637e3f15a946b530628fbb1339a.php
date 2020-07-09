<?php /*a:3:{s:60:"/www/web/youzuo/application/backman/view/material/index.html";i:1578116838;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1577170781;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1563374948;}*/ ?>
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
	    <li><a href="<?php echo url('index_op'); ?>">新增图库</a></li>
	    <li><a href="<?php echo url('index_cate'); ?>">分类管理</a></li>
  	</ul>
</div>
<div class="layui-card-body" data-model="table-bind">
	<div class="demoTable" style="margin-top: 25px;margin-bottom: 20px;">
		<form method="get">
		分类：
		<div class="layui-inline" style="width: 200px;">
			<select class="layui-input" name="cate" id="cate" autocomplete="off">
				<option value="">请选择分类</option>
				<option value="-1" <?php if(-1 == $cate): ?> selected<?php endif; ?>>未分类</option>

				<?php foreach($group as $vo): ?>
				
					<!-- <optgroup label="<?php echo $vo['cname']; ?>"></optgroup> -->
					<option <?php if($vo['id'] == $cate): ?> selected<?php endif; ?> value="<?php echo htmlentities($vo['id']); ?>"><?php echo $vo['cname']; ?></option>

				<?php endforeach; ?>
			</select>
		</div>
		<button class="layui-btn" type="submit">搜索</button>
		</form>
	</div>
	<div class="demoTable" id="photoBoxAll" style="margin-top: 25px;margin-bottom: 20px;background:#eee;line-height: 40px;height:40px;font-size:16px;">
		<label style="margin-left:20px;"><input type="checkbox" id="allcheckbox" stat="0" /> 全选</label>
		<button type="button" data-method="transAll" class="layui-btn modify" style="margin-left:40px;" >批量分组</button>
		<button type="button" data-method="delAll" class="layui-btn layui-btn-danger modify">删除</button>
	</div>
	<div class="layui-row layui-col-space15 listforphoto" id="photoBox" data-table>
		<?php foreach($list as $vo): ?>
		<div class="layui-col-md1 photoList" >
			<img style="width:120px;" src="<?php echo htmlentities($vo['image']); ?>">
			<div class="" style="margin-top:5px; text-align: center;"><label><input type="checkbox" value="<?php echo htmlentities($vo['id']); ?>" class="photo_checkbox" /> <?php echo htmlentities($vo['name']); ?></label></div>
			<div class="del_link">
				<a href="javascript:;" data-method="confirmTrans" data-id="<?php echo htmlentities($vo['id']); ?>" type="name" text="<?php echo htmlentities($vo['name']); ?>" class="modify" >改名</a>
				<a href="javascript:;" data-method="confirmTrans" data-id="<?php echo htmlentities($vo['id']); ?>" type="cid" text="<?php echo htmlentities($vo['cid']); ?>" class="modify">分组</a>
				<a href="javascript:;" data-del data-id="<?php echo htmlentities($vo['id']); ?>" data-table="photo">删除</a>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<div class="page"><?php echo $page; ?></div>
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

layui.use('layer', function(){ //独立版的layer无需执行这一句
  var layer = layui.layer;
  //触发事件
  var active = {
    confirmTrans: function(){
		var that = this;
		var id = $(that).attr('data-id');
		var type = $(that).attr('type');
		var value = $(that).attr('text');
		
		if( type == 'name' ){
			var content = '<div style="font-size:18px;margin:5px;">修改名称</div><hr style="height:1px;"><div class="" style="margin: 30px auto;width:300px;height:30px;"><input style="width:100%;light-height:23px;font-size:14px;padding:3px;" class="modifyValue" value="'+value+'" placeholder="请输入名称" /></div>';
		}
		if( type == 'cid' ){
			var str = '';
			var checked = '';
			for( var i = 1; i < $('#cate option').length; i++){
				checked = '';
				if( value == $('#cate option').eq(i).attr('value') ){
					checked = 'checked';
				}
				str = str + '<label style="margin-right:20px; font-size:14px;"> <input type="radio" class="modifyValue" name="modifycid" value="'+ $('#cate option').eq(i).attr('value') +'" '+ checked +' /> ' + $('#cate option').eq(i).html() + '</label>' ;
			};
			var content = '<div style="font-size:18px;margin:5px;">修改分组</div><hr style="height:1px;"><div class="" style="margin: 10px auto;width:80%;height:60%;">'+str+'</div>';
		}
		layer.open({
			type: 1
			,title: false //不显示标题栏
			,closeBtn: false
			,area: ['400px','200px;']
			,shade: 0.8
			,id: 'modifyBox' //设定一个id，防止重复弹出
			,btn: ['修改', '取消']
			,btnAlign: 'c'
			,moveType: 1 //拖拽模式，0或者1
			,content: content
			,success: function(layero){
				
				$('.layui-layer-btn0').click(function(){
					var result = {};
					result['id'] = id;
					result['type'] = type;
					if( type == 'name' ){
						result['value'] = $('.modifyValue').val();
					}
					
					if( type == 'cid' ){
						$.each($('.modifyValue'),function(){
							if( $(this).prop('checked') == true ) result['value'] = $(this).val();
						})
					}
					
					$.post(webRoot+ webControl+'/modifyPhoto',result,function(res){
						if(res.code){
							window.location.reload() ;
						}
					},'json')
				});
			}
		});
    },transAll: function(){
		
		var str = '';
		for( var i = 1; i < $('#cate option').length; i++){
			str = str + '<label style="margin-right:20px; font-size:14px;"> <input type="radio" class="modifyAllValue" name="modifycid" value="'+ $('#cate option').eq(i).attr('value') +'"  /> ' + $('#cate option').eq(i).html() + '</label>' ;
		};
		var content = '<div style="font-size:18px;margin:5px;">修改分组</div><hr style="height:1px;"><div class="" style="margin: 10px auto;width:80%;height:60%;">'+str+'</div>';
		layer.open({
			type: 1
			,title: false //不显示标题栏
			,closeBtn: false
			,area: ['400px','200px;']
			,shade: 0.8
			,id: 'modifyBox' //设定一个id，防止重复弹出
			,btn: ['修改', '取消']
			,btnAlign: 'c'
			,moveType: 1 //拖拽模式，0或者1
			,content: content
			,success: function(layero){
				
				$('.layui-layer-btn0').click(function(){
					var result = {};
					var id = [];
					$.each($('.photo_checkbox'),function(){
						if( $(this).prop('checked') == true ){
							id.push($(this).val());
						}
					})

					result['id'] = id;
					
					$.each($('.modifyAllValue'),function(){
						if( $(this).prop('checked') == true ) result['value'] = $(this).val();
					})
					
					$.post(webRoot+ webControl+'/modifyPhotoAll',result,function(res){
						if(res.code){
							window.location.reload() ;
						}
					},'json')
				});
			}
		});
    },delAll: function(){
		
		layer.confirm('确定删除吗？', function(index){
			var result = {};
			var id = [];
			$.each($('.photo_checkbox'),function(){
				if( $(this).prop('checked') == true ){
					id.push($(this).val());
				}
			})
			result['id'] = id;
			if( id.length == 0 ){
				layer.alert('请先选择图片');
				return false;
			}
			$.post(webRoot+ webControl+'/delAll',result,function(res){
				layer.alert(res.msg);
				if(res.code){
					setInterval(function(){
						window.location.reload();
					},800);
					
				}
			},'json')
		});
	},

  };
  
  $('#allcheckbox').on('click',function(){
	  if( $(this).attr('stat') == 1 ){
		$('.photo_checkbox').prop('checked',false);
		$(this).attr('stat','0');
	  }else{
		$('.photo_checkbox').prop('checked',true);
		$(this).attr('stat','1');
	  }
	  
	  
  });


  $('#photoBox .modify').on('click', function(){
    var othis = $(this), method = othis.data('method');
    active[method] ? active[method].call(this, othis) : '';
  });
  $('#photoBoxAll .modify').on('click', function(){
    var othis = $(this), method = othis.data('method');
    active[method] ? active[method].call(this, othis) : '';
  });
  
});


</script>

</body>
</html>