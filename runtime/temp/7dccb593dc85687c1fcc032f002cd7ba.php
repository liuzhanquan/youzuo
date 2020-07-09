<?php /*a:3:{s:67:"/www/web/codecheck/application/backman/view/detection/lcoption.html";i:1594223536;s:55:"/www/web/codecheck/application/backman/view/layout.html";i:1594223536;s:52:"/www/web/codecheck/application/backman/view/nav.html";i:1594223536;}*/ ?>
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
	    <li><a href="<?php echo url('lcindex',['pid'=>$parent['id']]); ?>">流程环节设置</a></li>
	    <li class="layui-this"><?php if(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty())): ?>新增<?php else: ?>编辑<?php endif; ?>流程环节</li>
  	</ul>
</div>
<div class="demoTable" style="margin-top: 25px;margin-left:30px;font-size:18px;font-weight: bold;">
	流程id：
   <div class="layui-inline" style="margin-right:30px;">
	   <!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
	   <?php echo htmlentities($parent['id']); ?>
   </div>
   <!-- <button class="layui-btn" data-type="reload">搜索</button> -->
   流程编号：
   <div class="layui-inline" style="margin-right:30px;">
	   <!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
	   <?php echo htmlentities($parent['detection_sn']); ?>
   </div>
   流程名称：
   <div class="layui-inline">
	   <!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
	   <?php echo htmlentities($parent['name']); ?>
   </div>
</div>
<div class="layui-card-body">
	<form class="layui-form" data-model="form-submit">
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
		  	<legend>基本设置</legend>
		</fieldset>
		<div class="layui-form-item">
			<label class="layui-form-label">环节编号</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="d_son_sn" value="<?php echo htmlentities($info['d_son_sn']); ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">环节名称</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="name" value="<?php echo htmlentities($info['name']); ?>" datatype="*">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">录入类型</label>
			<div class="layui-input-block">
				<input type="radio" name="type" value="0" title="单次录入" <?php if($info['type'] == 0): ?> checked="" <?php endif; ?>>
				<input type="radio" name="type" value="1" title="多次录入" <?php if($info['type'] == 1): ?> checked="" <?php endif; ?>>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">录入人员</label>
			<div class="layui-input-block" id="input_status">
				<input type="radio" name="input_status" value="0" title="全部" 		<?php if($info['input_status'] == 0): ?> checked="" <?php endif; ?>>
				<input type="radio" name="input_status" value="1" title="指定人员" 	<?php if($info['input_status'] == 1): ?> checked="" <?php endif; ?>>
			</div>
		</div>
		
		<div class="layui-form-item" id="input_staff" style="<?php if($info['input_status']): ?> display:block; <?php else: ?> display:none; <?php endif; ?>">
			<label class="layui-form-label">人员授权</label>
			<input type="hidden" name="input_staff" id="input_staff_val" value=<?php echo html_entity_decode($info["input_staff"]); ?>>
			<div class="layui-input-block">
					<div id="test4" class="demo-transfer"></div>
			</div>
		</div>
		


		<div class="layui-form-item">
			<label class="layui-form-label">录入时间</label>
			<div class="layui-input-block">
				<input type="radio" name="time_status" value="0" title="系统时间" <?php if($info['time_status'] == 0): ?> checked="" <?php endif; ?>>
				<input type="radio" name="time_status" value="1" title="手动时间"<?php if($info['time_status'] == 1): ?> checked="" <?php endif; ?>>
			</div>
		</div>

		<div class="layui-form-item">
			<label class="layui-form-label">环节描述</label>
			<div class="layui-input-block">
				<textarea id="content" name="content" data-model="form-ueditor" placeholder=""><?php echo $info['content']; ?></textarea>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">排序</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="sort" value="<?php echo isset($info['sort']) ? $info['sort'] : '100'; ?>" datatype="*">
				<div class="layui-form-mid layui-word-aux">数字越小越靠前</div>
			</div>
		</div>
		<div class="layui-form-item">
	    	<label class="layui-form-label">状态</label>
	    	<div class="layui-input-block">
	    		<?php $state = isset($info['status']) ? $info['status'] : '1'; ?>
	      		<input type="radio" name="status" value="1" title="启用" <?php if($state == 1): ?> checked="" <?php endif; ?>>
	      		<input type="radio" name="status" value="2" title="冻结" <?php if($state == 2): ?> checked="" <?php endif; ?>>
	    	</div>
	  	</div>
		<div class="layui-form-item">
	    	<div class="layui-input-block">
	    		<input type="hidden" name="id" value="<?php echo htmlentities($info['id']); ?>">
	    		<input type="hidden" name="parent_id" value="<?php echo htmlentities($parent['id']); ?>">
	      		<button class="layui-btn layui-btn-normal">保存</button>
	      		<button type="reset" class="layui-btn layui-btn-primary">重置</button>
	    	</div>
	  	</div>
	</form>
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

<script type="text/javascript" src="/static/lib/assets/data.js"></script>
<script type="text/javascript">
    var mixin = {};
    layui.use(['form','util','layer','transfer'],function(){
		var $ = layui.$
			,transfer = layui.transfer
			,layer = layui.layer
			,util = layui.util;
       

		// var data1 = [
		// 	{"value": "1", "title": "李白"}
		// 	,{"value": "1", "title": "李1白"}
		// 	,{"value": "2", "title": "杜甫"}
		// 	,{"value": "5", "title": "鲁迅", "disabled": true}
		// ]
		var data1 = [
			<?php if(is_array($staff) || $staff instanceof \think\Collection || $staff instanceof \think\Paginator): if( count($staff)==0 ) : echo "" ;else: foreach($staff as $key=>$item): ?>
				{"value": "<?php echo htmlentities($item['id']); ?>", "title": "<?php echo htmlentities($item['name']); ?>"},
			<?php endforeach; endif; else: echo "" ;endif; ?>
		];
		
		//显示搜索框
		transfer.render({
			elem: '#test4'
			,data: data1
			,title: ['未授权员工', '已授权员工']
			<?php if(!(empty($info['input_status']) || (($info['input_status'] instanceof \think\Collection || $info['input_status'] instanceof \think\Paginator ) && $info['input_status']->isEmpty()))): ?>
			,value:JSON.parse('<?php echo html_entity_decode($info["input_staff"]); ?>')
			// ,value:JSON.parse('<?php echo html_entity_decode($info["input_staff"]); ?>')
			<?php endif; ?>
			,showSearch: true
			,id:'keyInputStaff'
		})

		
		$.each($('#input_status .layui-form-radio'),function(index){
			$(this).on('click',function(){
				
				if( index == 1 ){
					$('#input_staff').show(300);
				}else{
					$('#input_staff').hide(300);

				}
				
				
			})
		})

		//批量办法定事件
		$('#test4').on('click',function(){
			var getData = transfer.getData('keyInputStaff'); //获取右侧数据
			var arr = [];
			for( i in getData ){
				
				arr.push(getData[i]['value']);
			}
			$('#input_staff_val').val(JSON.stringify(arr));
			
		})

    });
	
	
</script>

</body>
</html>