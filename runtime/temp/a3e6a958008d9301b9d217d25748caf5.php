<?php /*a:3:{s:61:"/www/web/youzuo/application/backman/view/customer/option.html";i:1576930595;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1577170781;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1563374948;}*/ ?>
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
	    <li class="layui-this"><?php if(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty())): ?>新增<?php else: ?>编辑<?php endif; ?>客户</li>
  	</ul>
</div>
<div class="layui-card-body">
	<form class="layui-form" data-model="form-submit">
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
		  	<legend>基本设置</legend>
		</fieldset>
		<div class="layui-form-item">
			<label class="layui-form-label">客户编号</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="customer_sn" value="<?php echo htmlentities($info['customer_sn']); ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">客户名称</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="customer_name" value="<?php echo htmlentities($info['customer_name']); ?>" datatype="*">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">客户分类</label>
			<div class="layui-input-block">
				<select name="cid">
					<?php foreach($list as $vo): ?>
					<option value="<?php echo htmlentities($vo['id']); ?>" <?php if($vo['id'] == $info['cid']['value']): ?>selected<?php endif; ?>><?php echo $vo['cname']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<style>
			.layui-form-selected dl{
				z-index: 9999;
			}
		</style>
		 <div class="layui-form-item" >
			<label class="layui-form-label">选择地区</label>
			<div class="layui-input-block">
				<div class="layui-form-item" id="picker" style="clear: inherit;">
					<div class="layui-input-inline" style="width: 200px;">
					<select name="province" class="province-selector" data-value="<?php echo htmlentities($info['province']); ?>" lay-filter="province-1">
						<option value="">请选择省</option>
					</select>
					</div>
					<div class="layui-input-inline" style="width: 200px;">
					<select name="city" class="city-selector" data-value="<?php echo htmlentities($info['city']); ?>" lay-filter="city-1">
						<option value="">请选择市</option>
					</select>
					</div>
					<div class="layui-input-inline" style="width: 200px;">
					<select name="county" class="county-selector" data-value="<?php echo htmlentities($info['county']); ?>" lay-filter="county-1">
						<option value="">请选择区</option>
					</select>
					</div>
				</div>
			</div>
        </div>
        <div class="layui-form-item">
			<label class="layui-form-label">详细地址</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="address" value="<?php echo htmlentities($info['address']); ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">负责人</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="name" value="<?php echo htmlentities($info['name']); ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">手机号码</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="phone" value="<?php echo htmlentities($info['phone']); ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">客户描述</label>
			<div class="layui-input-block">
				<textarea id="content" name="content" data-model="form-ueditor" placeholder=""><?php echo $info['content']; ?></textarea>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">客户排序</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="sort" value="<?php echo !empty($info['sort']) ? htmlentities($info['sort']) :  '99'; ?>">
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
    layui.config({
		base: '/static/lib/mods/'
		, version: '1.0'
	});
    layui.use(['form','layarea'],function(){
        var form = layui.form
        var layarea = layui.layarea
        
        layarea.render({
            elem: '#picker',
            // data: addrData,
            change: function (res) {
                //选择结果
                var str = JSON.stringify(res);
                if( res.province != '请选择省' && res.city != '请选择市' && res.county != '请选择区' ){
                    var str = JSON.stringify(res);
                }else{
                    // alert('地区选择错误');
                    str = '';
                }
            }
        });
        




    });
</script>

</body>
</html>