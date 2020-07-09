<?php /*a:3:{s:56:"/www/web/youzuo/application/backman/view/order/spec.html";i:1577369595;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1577170781;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1563374948;}*/ ?>
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
	.layui-anim-upbit{
		z-index: 99999!important;
	}
</style>
<div class="layui-tab layui-tab-brief">
		<ul class="layui-tab-title">
			<li><a href="<?php echo url('index'); ?>"><?php echo htmlentities($pathCurrent['name']); ?></a></li>
			<li><a href="<?php echo url('lcindex',['id'=>$order['id'],'sid'=>$order['did']['value']]); ?>">检测单信息管理</a></li>
			<li class="layui-this"><?php if(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty())): ?>新增<?php else: ?>编辑<?php endif; ?>表单信息</li>
		</ul>
</div>
<div class="demoTable" style="margin-top: 25px;margin-left:30px;font-size:18px;font-weight: bold;">
		检测单id：
	   <div class="layui-inline" style="margin-right:30px;">
		   <!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		   <?php echo htmlentities($order['id']); ?>
	   </div>
	   <!-- <button class="layui-btn" data-type="reload">搜索</button> -->
	   检测单号：
	   <div class="layui-inline" style="margin-right:30px;">
		   <!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		   <?php echo htmlentities($order['order_sn']); ?>
	   </div>
	   环节名称：
	   <div class="layui-inline" style="margin-right:30px;">
		   <!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		   <?php echo htmlentities($son['name']); ?>
	   </div>
	</div>
<div class="layui-card-body">
	<form class="layui-form" enctype="multipart/form-data" data-model="form-submit">
		<div class="layui-form-item">
			<label class="layui-form-label">表单创建时间</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="os_created_time" id="os_created_time" value="<?php echo htmlentities($created_time); ?>" placeholder="yyyy-MM-dd HH:ii:ss">
			</div>
		</div>
		<?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$item): if($item['type'] == 'text'): ?>
				<div class="layui-form-item">
					<label class="layui-form-label"><?php if(!(empty($item['must']) || (($item['must'] instanceof \think\Collection || $item['must'] instanceof \think\Paginator ) && $item['must']->isEmpty()))): ?><span style="color:red;">*</span><?php endif; ?><?php echo htmlentities($item['title']); ?></label>
					<div class="layui-input-block">
						<input type="text" class="layui-input" name="<?php echo htmlentities($item['name']); ?>" placeholder="<?php echo htmlentities($item['placeholder'][0]); ?>" value="<?php echo htmlentities($item['value']); ?>">
					</div>
				</div>
			<?php endif; if($item['type'] == 'www'): ?>
				<div class="layui-form-item">
					<label class="layui-form-label"><?php if(!(empty($item['must']) || (($item['must'] instanceof \think\Collection || $item['must'] instanceof \think\Paginator ) && $item['must']->isEmpty()))): ?><span style="color:red;">*</span><?php endif; ?><?php echo htmlentities($item['title']); ?></label>
					<div class="layui-input-block">
						<input type="text" class="layui-input" name="<?php echo htmlentities($item['name']); ?>" placeholder="<?php echo htmlentities($item['placeholder'][0]); ?>" value='<?php echo html_entity_decode( $item['value'] ); ?>'>
					</div>
				</div>
			<?php endif; if($item['type'] == 'select'): ?>
				<div class="layui-form-item"style="z-index:9999;" >
					<label class="layui-form-label"><?php if(!(empty($item['must']) || (($item['must'] instanceof \think\Collection || $item['must'] instanceof \think\Paginator ) && $item['must']->isEmpty()))): ?><span style="color:red;">*</span><?php endif; ?> <?php echo htmlentities($item['title']); ?></label>
					<div class="layui-input-block">
						<div class="layui-input-inline">
							<select name="<?php echo htmlentities($item['name']); ?>" lay-verify="required" lay-search="">
								<option value="">直接选择或搜索选择</option>
								<?php if(is_array($item['placeholder']) || $item['placeholder'] instanceof \think\Collection || $item['placeholder'] instanceof \think\Paginator): if( count($item['placeholder'])==0 ) : echo "" ;else: foreach($item['placeholder'] as $key=>$i): ?>
									<option value="<?php echo htmlentities($i); ?>" <?php if($item['value'] == $i): ?> selected <?php endif; ?> ><?php echo htmlentities($i); ?></option>
								<?php endforeach; endif; else: echo "" ;endif; ?>
							</select>
						</div>
					</div>
				</div>
			<?php endif; if($item['type'] == 'picker'): ?>
				<div class="layui-form-item" >
					<label class="layui-form-label"><?php if(!(empty($item['must']) || (($item['must'] instanceof \think\Collection || $item['must'] instanceof \think\Paginator ) && $item['must']->isEmpty()))): ?><span style="color:red;">*</span><?php endif; ?> <?php echo htmlentities($item['title']); ?></label>
					<div class="layui-input-block">
						<input type="hidden" name="<?php echo htmlentities($item['name']); ?>" id="input<?php echo htmlentities($item['name']); ?>">
						<div class="layui-form-item" id="picker<?php echo htmlentities($item['name']); ?>" style="clear: inherit;">
							<div class="layui-input-inline" style="width: 200px;">
							  <select name="province" class="province-selector" data-value="" lay-filter="province-1">
								<option value="">请选择省</option>
							  </select>
							</div>
							<div class="layui-input-inline" style="width: 200px;">
							  <select name="city" class="city-selector" data-value="" lay-filter="city-1">
								<option value="">请选择市</option>
							  </select>
							</div>
							<div class="layui-input-inline" style="width: 200px;">
							  <select name="county" class="county-selector" data-value="" lay-filter="county-1">
								<option value="">请选择区</option>
							  </select>
							</div>
						  </div>

					</div>
				</div>
			<?php endif; if($item['type'] == 'datetime'): ?>
				<div class="layui-form-item">
					<label class="layui-form-label"><?php if(!(empty($item['must']) || (($item['must'] instanceof \think\Collection || $item['must'] instanceof \think\Paginator ) && $item['must']->isEmpty()))): ?><span style="color:red;">*</span><?php endif; ?><?php echo htmlentities($item['title']); ?></label>
					<div class="layui-input-block">
						<input type="text" class="layui-input" name="<?php echo htmlentities($item['name']); ?>" id="<?php echo htmlentities($item['name']); ?>" value="<?php echo htmlentities($item['value']); ?>" placeholder="yyyy-MM-dd HH:ii:ss">
					</div>
				</div>
			<?php endif; if($item['type'] == 'radio'): ?>
				<div class="layui-form-item">
					<label class="layui-form-label"><?php if(!(empty($item['must']) || (($item['must'] instanceof \think\Collection || $item['must'] instanceof \think\Paginator ) && $item['must']->isEmpty()))): ?><span style="color:red;">*</span><?php endif; ?><?php echo htmlentities($item['title']); ?></label>
					<div class="layui-input-block">
						<?php if(is_array($item['placeholder']) || $item['placeholder'] instanceof \think\Collection || $item['placeholder'] instanceof \think\Paginator): if( count($item['placeholder'])==0 ) : echo "" ;else: foreach($item['placeholder'] as $key=>$i): ?>
							<input type="radio" name="<?php echo htmlentities($item['name']); ?>" value="<?php echo htmlentities($i); ?>" <?php if($item['value'] == $i): ?> checked <?php endif; ?> title="<?php echo htmlentities($i); ?>">
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
				</div>
			<?php endif; if($item['type'] == 'checkbox'): ?>
			<div class="layui-form-item">
					<label class="layui-form-label"><?php if(!(empty($item['must']) || (($item['must'] instanceof \think\Collection || $item['must'] instanceof \think\Paginator ) && $item['must']->isEmpty()))): ?><span style="color:red;">*</span><?php endif; ?><?php echo htmlentities($item['title']); ?></label>
					<div class="layui-input-block">
						<?php if(is_array($item['placeholder']) || $item['placeholder'] instanceof \think\Collection || $item['placeholder'] instanceof \think\Paginator): if( count($item['placeholder'])==0 ) : echo "" ;else: foreach($item['placeholder'] as $key=>$i): ?>
							<input type="checkbox" name="<?php echo htmlentities($item['name']); ?>[]" value="<?php echo htmlentities($i); ?>" <?php if(powerStatus($i,$item['value'])): ?> checked <?php endif; ?> title="<?php echo htmlentities($i); ?>">
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
				  </div>
			<?php endif; if($item['type'] == 'textarea22'): ?>
				<div class="layui-form-item">
					<label class="layui-form-label"><?php if(!(empty($item['must']) || (($item['must'] instanceof \think\Collection || $item['must'] instanceof \think\Paginator ) && $item['must']->isEmpty()))): ?><span style="color:red;">*</span><?php endif; ?><?php echo htmlentities($item['title']); ?></label>
					<div class="layui-input-block">
						<!-- <input type="textarea" class="layui-input" name="<?php echo htmlentities($item['name']); ?>"  placeholder="<?php echo htmlentities($item['placeholder'][0]); ?>" value="<?php echo htmlentities($item['value']); ?>"> -->
						<textarea name="<?php echo htmlentities($item['name']); ?>"  placeholder="<?php echo htmlentities($item['placeholder'][0]); ?>" class="layui-textarea"><?php echo htmlentities($item['value']); ?></textarea>
					</div>
				</div>
			<?php endif; if($item['type'] == 'textarea'): ?>
				<div class="layui-form-item">
					<label class="layui-form-label">产品详情</label>
					<div class="layui-input-block">
						<textarea id="<?php echo htmlentities($item['name']); ?>" name="<?php echo htmlentities($item['name']); ?>" data-model="form-ueditor" placeholder=""><?php echo $item['value']; ?></textarea>
					</div>
				</div>
			<?php endif; if($item['type'] == 'file'): ?>
				<div class="layui-form-item">
					<label class="layui-form-label"><?php if(!(empty($item['must']) || (($item['must'] instanceof \think\Collection || $item['must'] instanceof \think\Paginator ) && $item['must']->isEmpty()))): ?><span style="color:red;">*</span><?php endif; ?><?php echo htmlentities($item['title']); ?></label>
					<input type="hidden" name="<?php echo htmlentities($item['name']); ?>" id="input<?php echo htmlentities($item['name']); ?>" value=<?php echo html_entity_decode($item['value']); ?>>
					<div class="layui-input-block">
							<button type="button" class="layui-btn" id="<?php echo htmlentities($item['name']); ?>">多图片上传</button> <button style="margin-left:10px;" type="button" class="layui-btn layui-btn-warm" id="<?php echo htmlentities($item['name']); ?>Del">重新上传</button> 
							<blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 10px;">
							  预览图：
							  <div class="layui-upload-list" id="n<?php echo htmlentities($item['name']); ?>">
								  <?php if(!(empty($item['photo']) || (($item['photo'] instanceof \think\Collection || $item['photo'] instanceof \think\Paginator ) && $item['photo']->isEmpty()))): if(is_array($item['photo']) || $item['photo'] instanceof \think\Collection || $item['photo'] instanceof \think\Paginator): if( count($item['photo'])==0 ) : echo "" ;else: foreach($item['photo'] as $key=>$p): ?>
								  <img src="<?php echo htmlentities($p); ?>" alt="hzp.png" style="width:20%;" class="layui-upload-img">
								  <?php endforeach; endif; else: echo "" ;endif; ?>
								  <?php endif; ?>
							  </div>
						   </blockquote>
					</div>
				</div>
			<?php endif; ?>

		<?php endforeach; endif; else: echo "" ;endif; ?>




		<div class="layui-form-item">
			<div class="layui-input-block">
				<input type="hidden" name="oid" value="<?php echo htmlentities($order['id']); ?>">
				<input type="hidden" name="did" value="<?php echo htmlentities($son['parent_id']); ?>">
				<input type="hidden" name="dsid" value="<?php echo htmlentities($son['id']); ?>">
				<input type="hidden" name="d_son_sn" value="<?php echo htmlentities($son['d_son_sn']); ?>">
				<input type="hidden" name="sort" value="<?php echo htmlentities($sort); ?>">
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
<script type="text/javascript" >
    var mixin = {};
	//配置插件目录
	layui.config({
		base: '/static/lib/mods/'
		, version: '1.0'
	});
    layui.use(['form','laydate','upload','layarea'],function(){
        var form = layui.form
		var laydate = layui.laydate;
		var upload = layui.upload
		var layarea = layui.layarea
		
		
		laydate.render({
			elem: '#os_created_time'
			// ,value: '1989-10-14'
			,isInitValue: true
			,type:'datetime'
		});

		//初始赋值
		<?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$item): if($item['type'] == 'datetime'): ?>
				laydate.render({
					elem: '#<?php echo htmlentities($item['name']); ?>'
					// ,value: '1989-10-14'
					,isInitValue: true
					,type:'datetime'
				});
			<?php endif; if($item['type'] == 'picker'): ?>
				var addrData = {province:'',city:'',county:''}; 
				<?php if(!(empty($item['value']['province']) || (($item['value']['province'] instanceof \think\Collection || $item['value']['province'] instanceof \think\Paginator ) && $item['value']['province']->isEmpty()))): ?>
					$('#input<?php echo htmlentities($item['name']); ?>').val(JSON.stringify({province:"<?php echo htmlentities($item['value']['province']); ?>",city:"<?php echo htmlentities($item['value']['city']); ?>",county:"<?php echo htmlentities($item['value']['county']); ?>"}));
					addrData = {
							province: '<?php echo htmlentities($item["value"]["province"]); ?>',
							city: '<?php echo htmlentities($item["value"]["city"]); ?>',
							county: '<?php echo htmlentities($item["value"]["county"]); ?>',
						}
				<?php endif; ?>
				layarea.render({
					elem: '#picker<?php echo htmlentities($item['name']); ?>',
					data: addrData,
					change: function (res) {
						//选择结果
						var str = JSON.stringify(res);
						if( res.province != '请选择省' && res.city != '请选择市' && res.county != '请选择区' ){
							var str = JSON.stringify(res);
						}else{
							// alert('地区选择错误');
							str = '';
						}
						$('#input<?php echo htmlentities($item['name']); ?>').val(str);
					}
				});
			<?php endif; if($item['type'] == 'file'): ?>
				upload.render({
					elem: '#<?php echo htmlentities($item['name']); ?>'
					,url: '/admin/upload/index2.html?action=uploadimage&encode=utf-8'
					,multiple: true
					,field: 'upfile'
					,before: function(obj){
					//预读本地文件示例，不支持ie8
					obj.preview(function(index, file, result){
						$('#n<?php echo htmlentities($item['name']); ?>').append('<img src="'+ result +'" alt="'+ file.name +'" class="layui-upload-img">')
						
					});
					}
					,done: function(res){
						var newStr = [];
						if( $('#input<?php echo htmlentities($item['name']); ?>').val() ){
							newStr = JSON.parse( $('#input<?php echo htmlentities($item['name']); ?>').val() );
						}
						
						newStr.push(res.url);
						$('#input<?php echo htmlentities($item['name']); ?>').val( JSON.stringify( newStr ) );
					}
				});

				$('#<?php echo htmlentities($item['name']); ?>Del').on('click',function(){
					$('#input<?php echo htmlentities($item['name']); ?>').val( '' );
					$('#n<?php echo htmlentities($item['name']); ?>').html('');
				})

			<?php endif; ?>
			


		<?php endforeach; endif; else: echo "" ;endif; ?>
	});
	



</script>

</body>
</html>