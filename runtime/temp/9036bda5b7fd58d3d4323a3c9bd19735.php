<?php /*a:3:{s:65:"/www/web/codecheck/application/backman/view/detection/option.html";i:1594311118;s:55:"/www/web/codecheck/application/backman/view/layout.html";i:1594223536;s:52:"/www/web/codecheck/application/backman/view/nav.html";i:1594223536;}*/ ?>
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
	.layui-form-item .layui-form-label{
		width:140px;
	}
	.layui-input-block{
		margin-left:140px;
	}
</style>
<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
	    <li><a href="<?php echo url('index'); ?>"><?php echo htmlentities($pathCurrent['name']); ?></a></li>
	    <li class="layui-this"><?php if(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty())): ?>添加<?php else: ?>编辑<?php endif; ?>分配记录</li>
  	</ul>
</div>
<div class="layui-card-body">
	<form class="layui-form" data-model="form-submit">
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
		  	<legend>基本设置</legend>
		</fieldset>
		<div class="layui-form-item">
			<label class="layui-form-label">产品选择</label>
			<div class="layui-input-block">
				<div class="layui-input-inline">
					<select name="goods_id" lay-verify="required" lay-search="">
						<option value="">直接选择或搜索选择</option>
						<?php if(is_array($goods) || $goods instanceof \think\Collection || $goods instanceof \think\Paginator): if( count($goods)==0 ) : echo "" ;else: foreach($goods as $key=>$item): ?>
						<option value="<?php echo htmlentities($item['id']); ?>" <?php if($item['id'] == $info['goods_id']['value']): ?> selected <?php endif; ?>><?php echo htmlentities($item['title']); ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">业务员选择</label>
			<div class="layui-input-block">
				<div class="layui-input-inline">
					<select name="customer_id" lay-verify="required" lay-search="">
						<option value="">直接选择或搜索选择</option>
						<?php if(is_array($customer) || $customer instanceof \think\Collection || $customer instanceof \think\Paginator): if( count($customer)==0 ) : echo "" ;else: foreach($customer as $key=>$item): ?>
						<option value="<?php echo htmlentities($item['id']); ?>" <?php if($item['id'] == $info['customer_id']['value']): ?> selected <?php endif; ?>><?php echo htmlentities($item['customer_name']); ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">起始二维码编号</label>
			<div class="layui-input-block">
				<input type="number" class="layui-input startNum"  name="start_num" value="<?php echo htmlentities($info['start_num']); ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">结束二维码编号</label>
			<div class="layui-input-block">
				<input type="number" class="layui-input endNum" name="end_num" value="<?php echo htmlentities($info['end_num']); ?>" datatype="*">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">配送数量</label>
			<div class="layui-input-block">
				<input type="number" class="layui-input countNum" readonly style="color:gray;background-color:#f1f1f1;" name="count_num" value="<?php echo htmlentities((isset($info['count_num']) && ($info['count_num'] !== '')?$info['count_num']:0)); ?>" >
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">备注说明</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="remark" value="<?php echo htmlentities($info['remark']); ?>">
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
    layui.use('form',function(){
        var form = layui.form
       
    });





    function countNumGet(){
        let startNum = $('.startNum').val();
        let endNum   = $('.endNum').val();
        console.log(startNum);
        console.log(endNum);
        if( startNum == null || startNum === '' || endNum == null || endNum === '' ) return false;

        $('.countNum').val(endNum - startNum + 1);


	};


    $('.startNum').on('keyup',function(){
        countNumGet();
    });

    $('.endNum').on('keyup',function(){
        countNumGet();
    });

</script>

</body>
</html>