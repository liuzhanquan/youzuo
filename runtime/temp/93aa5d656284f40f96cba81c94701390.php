<?php /*a:3:{s:58:"/www/web/youzuo/application/backman/view/order/option.html";i:1583393384;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1583393382;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1583393382;}*/ ?>
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
	.layui-form-checkbox i{
		height:30px;
	}
	.layui-input-block-w{
		width:60%;
	}
	.layui-input-block-w2{
		width:90%;
	}
	.input-w{
		width:66.5%;
		display:inline-flex;
	}
	.layui-input-block-w2 .{
		width:80%;
	}
</style>
<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
	    <li><a href="<?php echo url('index'); ?>"><?php echo htmlentities($pathCurrent['name']); ?></a></li>
	    <li class="layui-this"><?php if(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty())): ?>新增<?php else: ?>编辑<?php endif; ?>检测单</li>
  	</ul>
</div>
<div class="layui-card-body">
	<form class="layui-form" data-model="form-submit">
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
		  	<legend>基本设置</legend>
		</fieldset>
		<div class="layui-form-item">
			<label class="layui-form-label">检测单号</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="order_sn" placeholder="请输入唯一检测单号，为空默认生成" value="<?php echo htmlentities($info['order_sn']); ?>" <?php if($info['status'] > 0): ?>readonly="readonly"<?php endif; ?>>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">客户名称</label>
			<div class="layui-input-block">
				<div class="layui-input-inline">
					<select name="cid" lay-verify="required" lay-search="">
						<option value="">直接选择或搜索选择</option>
						<?php if(is_array($customer) || $customer instanceof \think\Collection || $customer instanceof \think\Paginator): if( count($customer)==0 ) : echo "" ;else: foreach($customer as $key=>$item): ?>
							<option value="<?php echo htmlentities($item['id']); ?>" <?php if($item['id'] == $info['cid']['value']): ?> selected <?php endif; ?>><?php echo htmlentities($item['customer_name']); ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">产品名称</label>
			<div class="layui-input-block">
				<div class="layui-input-inline">
					<select name="gid" lay-verify="required" lay-search="">
						<option value="">直接选择或搜索选择</option>
						<?php if(is_array($goods) || $goods instanceof \think\Collection || $goods instanceof \think\Paginator): if( count($goods)==0 ) : echo "" ;else: foreach($goods as $key=>$item): ?>
							<option value="<?php echo htmlentities($item['id']); ?>" <?php if($item['id'] == $info['gid']['value']): ?> selected <?php endif; ?>><?php echo htmlentities($item['title']); ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="layui-form-item" >
			<label class="layui-form-label">检测流程</label>
			<div class="layui-input-block">
				<?php if($info['status'] > 0): if(is_array($detection) || $detection instanceof \think\Collection || $detection instanceof \think\Paginator): if( count($detection)==0 ) : echo "" ;else: foreach($detection as $key=>$item): if($item['id'] == $info['did']['value']): ?> 
								<input type="hidden" name="did" value="<?php echo htmlentities($item['id']); ?>" >
								<input type="text" class="layui-input" name="oooo" placeholder="请输入唯一检测单号，为空默认生成" value="<?php echo htmlentities($item['name']); ?>" <?php if($info['status'] > 0): ?>readonly="readonly"<?php endif; ?>>
							<?php endif; ?>
						<?php endforeach; endif; else: echo "" ;endif; else: ?>
					<div class="layui-input-inline" id="didBox" val="<?php echo htmlentities($info['did']['value']); ?>">
						<select name="did" lay-verify="required" id="did" lay-search="" >
							<option value="">直接选择或搜索选择</option>
							<?php if(is_array($detection) || $detection instanceof \think\Collection || $detection instanceof \think\Paginator): if( count($detection)==0 ) : echo "" ;else: foreach($detection as $key=>$item): ?>
								<option value="<?php echo htmlentities($item['id']); ?>" <?php if($item['id'] == $info['did']['value']): ?> selected <?php endif; ?>><?php echo htmlentities($item['name']); ?></option>
							<?php endforeach; endif; else: echo "" ;endif; ?>
						</select>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="layui-form-item">
				<label class="layui-form-label">检测流程环节</label>
				<?php if($info['status'] > 0): ?>
				<input type="hidden" id="gsid" name="gsid" value=<?php echo html_entity_decode($info["gsid"]); ?> >
				<div class="layui-input-block">
					<input type="text" class="layui-input" name=""  value="<?php echo sel_detection_son($info["gsid"]); ?>" <?php if($info['status'] > 0): ?>readonly="readonly"<?php endif; ?>>
				</div>
				<?php else: ?>
				<input type="hidden" id="gsid" name="gsid" value=<?php echo html_entity_decode($info["gsid"]); ?> >
				<div class="layui-input-block" id="gsonBox">
				</div>
				<?php endif; ?>
			</div>
		<div class="layui-form-item">
			<label class="layui-form-label">产品型号</label>
			<div class="layui-input-block layui-input-block-w">
				<input type="text" class="layui-input" name="spec" value="<?php echo htmlentities($info['spec']); ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">材料成分</label>
			<div class="layui-input-block  layui-input-block-w2">
				<input type="text" class="layui-input input-w" name="composition" value="<?php echo htmlentities($info['composition']); ?>">
				<input type="checkbox" name="is_show[]"  value="composition" class="requiredBox" <?php if(in_array_status('composition',$info['is_show'])): ?> checked="" <?php endif; ?> title="是否显示">
				<input type="checkbox" name="required_status[]" value="composition"  <?php if(in_array_status('composition',$info['required_status'])): ?> checked="" <?php endif; ?> class="showBox" title="是否必填">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">供应商</label>
			<div class="layui-input-block layui-input-block-w2">
				<input type="text" class="layui-input input-w" name="supplier" value="<?php echo htmlentities($info['supplier']); ?>">
				<input type="checkbox" name="is_show[]"  value="supplier" class="requiredBox"  <?php if(in_array_status('supplier',$info['is_show'])): ?> checked="" <?php endif; ?> title="是否显示">
				<input type="checkbox" name="required_status[]" value="supplier" class="showBox"  <?php if(in_array_status('supplier',$info['required_status'])): ?> checked="" <?php endif; ?> title="是否必填">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">注塑机台</label>
			<div class="layui-input-block layui-input-block-w2">
				<input type="text" class="layui-input input-w" name="machine" value="<?php echo htmlentities($info['machine']); ?>">
				<input type="checkbox" name="is_show[]"  value="machine" class="requiredBox"  <?php if(in_array_status('machine',$info['is_show'])): ?> checked="" <?php endif; ?> title="是否显示">
				<input type="checkbox" name="required_status[]" value="machine" class="showBox"  <?php if(in_array_status('machine',$info['required_status'])): ?> checked="" <?php endif; ?> title="是否必填">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">合同编号（物流编号）</label>
			<div class="layui-input-block layui-input-block-w2">
				<input type="text" class="layui-input input-w" name="contract_sn" value="<?php echo htmlentities($info['contract_sn']); ?>">
				<input type="checkbox" name="is_show[]"  value="contract_sn" class="requiredBox"  <?php if(in_array_status('contract_sn',$info['is_show'])): ?> checked="" <?php endif; ?> title="是否显示">
				<input type="checkbox" name="required_status[]" value="contract_sn" class="showBox"  <?php if(in_array_status('contract_sn',$info['required_status'])): ?> checked="" <?php endif; ?> title="是否必填">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">备注</label>
			<div class="layui-input-block layui-input-block-w2">
				<input type="textarea" class="layui-input input-w" name="remark" value="<?php echo htmlentities($info['remark']); ?>">
				<input type="checkbox" name="is_show[]"  value="remark" class="requiredBox"  <?php if(in_array_status('remark',$info['is_show'])): ?> checked="" <?php endif; ?> title="是否显示">
				<input type="checkbox" name="required_status[]" value="remark" class="showBox" <?php if(in_array_status('remark',$info['required_status'])): ?> checked="" <?php endif; ?> title="是否必填">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">检测类型</label>
			<div class="layui-input-block layui-input-block-w2">
				<div class="layui-input-inline"  style="margin-right:53.5%;" >
					<select name="test_type" lay-verify="required" lay-search="">
						<option value="">直接选择或搜索选择</option>
						<?php if(is_array($test_type) || $test_type instanceof \think\Collection || $test_type instanceof \think\Paginator): if( count($test_type)==0 ) : echo "" ;else: foreach($test_type as $key=>$item): ?>
							<option value="<?php echo htmlentities($item['id']); ?>" <?php if($item['id'] == $info['test_type']['value']): ?> selected <?php endif; ?>><?php echo htmlentities($item['name']); ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>

				<input type="checkbox" name="is_show[]"  value="test_type" class="requiredBox" <?php if(in_array_status('test_type',$info['is_show'])): ?> checked="" <?php endif; ?> title="是否显示">
				<input type="checkbox" name="required_status[]" value="test_type" class="showBox" <?php if(in_array_status('test_type',$info['required_status'])): ?> checked="" <?php endif; ?> title="是否必填">
			</div>
		</div>
		<div class="layui-form-item">
	    	<label class="layui-form-label">状态</label>
	    	<div class="layui-input-block layui-input-block-w">
					<input type="radio" name="status" value="0" title="未审核" <?php if($info['status'] == 0): ?> checked="" <?php endif; ?>>
	      		<input type="radio" name="status" value="1" title="已审核" <?php if($info['status'] == 1): ?> checked="" <?php endif; ?>>
	      		<input type="radio" name="status" value="2" title="已完成" <?php if($info['status'] == 2): ?> checked="" <?php endif; ?>>
	      		<input type="radio" name="status" value="3" title="审核不通过" <?php if($info['status'] == 3): ?> checked="" <?php endif; ?>>
	      		
	    	</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">检测结果</label>
			<div class="layui-input-block layui-input-block-w">
				<input type="radio" name="engding" value="0" title="待定" <?php if($info['engding'] == 0): ?> checked="" <?php endif; ?>>
				<input type="radio" name="engding" value="1" title="合格" <?php if($info['engding'] == 1): ?> checked="" <?php endif; ?>>
				<input type="radio" name="engding" value="2" title="风险警告" <?php if($info['engding'] == 2): ?> checked="" <?php endif; ?>>
				<input type="radio" name="engding" value="3" title="不合格" <?php if($info['engding'] == 3): ?> checked="" <?php endif; ?>>
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

<script type="text/javascript">
var mixin = {};
layui.use('form',function(){
	var form = layui.form

	
	
	
	$('#didBox dd').on('click',function(){
		var val = $(this).attr('lay-value');
		$('#gsid').val('');
		checkSel(val);
		
	});

	function checkSel(val = ''){
		var old = $('#didBox').attr('val');
		var result = {};
		var str = '';
		if( val != old ){
			if( val == '' ){
				val = old
			}
			result['id'] = val;
			$('#didBox').attr('val',val);
			$.post(webRoot+ webControl+'/getdetectionSon',result,function(res){
				
				if( res.data.length ){
					for( var i in res.data ){
						str = str + '<label style="margin-right:20px;font-size:14px;"><input style="display:inline-block;height:15px;width:15px;" type="checkbox" value="'+res.data[i]['id']+'" name="gsidb[]" title="'+res.data[i]['name']+'"> '+ res.data[i]['name']+'</label>';
					}
					
				}
				
				$('#gsonBox').html(str);
				
				selCheckBox();
			},'json')
		}
	}
	
	$('#gsonBox').on('click',function(){
		var arr = [];
		$.each($('#gsonBox input'),function( index ){
			if( $(this).prop('checked') == true ){
				arr.push($(this).val())
			}
		})
		$('#gsid').val(JSON.stringify(arr));
	});

	function selCheckBox(){
		var arr = [];
		if( $('#gsid').val() ){
			arr = JSON.parse($('#gsid').val());
			$.each($('#gsonBox input'),function( index ){
				if( arr.indexOf($(this).val() ) !== (-1) ){
					$(this).prop('checked',true);
				}
			})

		}
	}

	$(document).ready(function() {
		<?php if($info['status'] == 0): ?>
		checkSel();
		<?php endif; ?>

	})

});
</script>

</body>
</html>