<?php /*a:3:{s:60:"/www/web/youzuo/application/backman/view/detection/spec.html";i:1577678764;s:52:"/www/web/youzuo/application/backman/view/layout.html";i:1577170781;s:49:"/www/web/youzuo/application/backman/view/nav.html";i:1563374948;}*/ ?>
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
				
<link href="/static/admin/css/diycss/bootstrap.min.css" rel="stylesheet">
<link href="/static/admin/css/diycss/fileinput.min.css" rel="stylesheet">
<link href="/static/admin/css/diycss/bootstrap-datetimepicker.min.css" rel="stylesheet">
<link href="/static/admin/css/diycss/form_builder/animate.min.css" rel="stylesheet">
<link href="/static/admin/css/diycss/form_builder/summernote.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="http://cdn.bootcss.com/font-awesome/4.6.0/css/font-awesome.min.css">
<link href="/static/admin/css/diycss/form_builder/style.min862f.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/static/admin/css/diycss/index.css">

<script src="/static/admin/css/diycss/bootstrap.min.js"></script>
<script src="/static/admin/css/diycss/fileinput.min.js"></script>
<script src="/static/admin/css/diycss/bootstrap-datetimepicker.min.js"></script>
<script src="/static/admin/css/diycss/distpicker.data.min.js"></script>
<script src="/static/admin/css/diycss/distpicker.min.js"></script>
<script src="/static/admin/css/diycss/bootstrap-datetimepicker.zh-CN.js"></script>
<script src="/static/admin/css/diycss/zh.js"></script>
<script src="/static/admin/css/diycss/jquery-ui.min.js"></script>
<script src="/static/admin/css/diycss/form_builder/beautifyhtml.js"></script>
<script src="/static/admin/css/diycss/index.js"></script>

<style>
	.admin-sm .layui-icon {
		font-size: 24px;
	}
	.tools a:nth-child(1) i {
		font-size: 24px;
	}
	.btn {
		padding: 3px 12px;
	}
	.btnBox{
		padding:30px 0;
		
	}
	.btnBox button{
		margin-left:20%;
	}
	.file-input-new .file-input-new{
		display:none;
	}
	.btntext1 {
		height: 34px;
		line-height: 34px;
		border: 1px solid #fff;
		background-color: #ecf1f4;
	}
	.btntext1 {
		height: 34px;
		line-height: 34px;
		background-color: rgb(236, 241, 244);
		border-width: 1px;
		border-style: solid;
		border-color: rgb(255, 255, 255);
		border-image: initial;
	}
	.btntext1 label {
			cursor: move;
		}
	.btntext1 img {
		width: 34px;
		float: right;
	}
</style>

<div class="layui-tab layui-tab-brief">
		<ul class="layui-tab-title">
			<li><a href="<?php echo url('index'); ?>"><?php echo htmlentities($pathCurrent['name']); ?></a></li>
			<li><a href="<?php echo url('lcindex',['pid'=>$parent['id']]); ?>">流程环节设置</a></li>
			<li class="layui-this"><?php if(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty())): ?>新增<?php else: ?>编辑<?php endif; ?>表格规则</li>
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
	   <div class="layui-inline" style="margin-right:30px;">
		   <!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		   <?php echo htmlentities($parent['name']); ?>
	   </div>
	   环节名称：
	   <div class="layui-inline" style="margin-right:30px;">
		   <!-- <input class="layui-input" name="title" id="title" autocomplete="off"> -->
		   <?php echo htmlentities($son['name']); ?>
	   </div>
	</div>
	
<div class="layui-card-body">
		<div class="container-fluid" style="padding:0px; margin:0px;">
				<div class="row-fluid clearfix">
					<div class="col-md-12 column">
						<div style="width:1180px; margin:0 auto; margin-top:20px;">
							<div class="conference-cont" style=" margin-top:20px;">
								<div class="set-til">第一步<span style="padding-left:10px;">报名表单设置</span></div>
								<div class="wrapper wrapper-content" style="margin-top: 55px;">
									<div class="row" style="border-left: 2px dashed rgba(0,0,0,0.12);border-right: 2px dashed rgba(0,0,0,0.12);border-bottom: 2px dashed rgba(0,0,0,0.12);">
										<div id="colzuo" class="col-sm-9" style="border-right: 2px dashed rgba(0,0,0,0.12);">
											<div class="ibox float-e-margins">
												<div class="ibox-title">
													<h5>拖拽/点击右侧表单项到此区域</h5>
													<div class="ibox-tools">
														
														<button type="button" class="btn btn-yulan">预览</button>
													</div>
												</div>
												<div class="ibox-content">
													<div class="row form-body form-horizontal m-t">
														<div class="col-md-12 droppable sortable ui-droppable ui-sortable">

														</div>
													</div>
													
												</div>
											</div>
										</div>
										
										<div id="colyou" class="col-sm-3">
											<div class="ibox float-e-margins">
												<div class="ibox-title">
													<h5>自定义表单项</h5>
												</div>
												<div class="ibox-content">
													<form role="form" class="form-horizontal m-t">
														<!-- <p><b>常用项</b></p>
														<table>
															<tr>
																<td>
																	<div id="name" class="draggable ui-draggable btntexts">
																		姓名
																	</div>
																</td>
																<td>
																	<div id="phone" class="draggable ui-draggable btntexts">
																		电话
																	</div>
																</td>
																<td>
																	<div id="email" class="draggable ui-draggable btntexts">
																		邮箱
																	</div>
																</td>
																
															</tr>
															<tr>
																<td>
																	<div id="card" class="draggable ui-draggable btntexts">
																		身份证
																	</div>
																</td>
																<td>
																	<div id="www" class="draggable ui-draggable btntexts">
																		个人网站
																	</div>
																</td>
																<td>
																	<div id="logo" class="draggable ui-draggable btntexts">
																		上传Logo
																	</div>
																</td>
															   
															</tr>
															<tr>
																<td>
																	<div id="sex" class="draggable ui-draggable btntexts">
																		性别
																	</div>
																</td>
																 <td>
																	<div id="occupation" class="draggable ui-draggable btntexts">
																		职位
																	</div>
																</td>
																<td>
																	<div id="profile" class="draggable ui-draggable btntexts">
																		个人简介
																	</div>
																</td>
															</tr>
														</table> -->
														<p><b>自定义项</b></p>
														<div id="text" class="form-group draggable ui-draggable btntext">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 文本框 <img src="/static/admin/css/diycss/image/add_form_img01.png"></label>
														</div>
														<div id="selectFun" data-method="selectAll" class="form-group draggable ui-draggable btntext1">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 下拉框 <img src="/static/admin/css/diycss/image/add_form_img05.png"></label>
														</div>
														<!-- <div id="select" class="form-group draggable ui-draggable btntext">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 下拉框 <img src="/static/admin/css/diycss/image/add_form_img05.png"></label>
														</div> -->
														<div id="radioFun" data-method="radioAll" class="form-group draggable ui-draggable btntext1">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 单选 <img src="/static/admin/css/diycss/image/add_form_img03.png"></label>
														</div>
														<!-- <div id="radio" class="form-group draggable ui-draggable btntext">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 单选 <img src="/static/admin/css/diycss/image/add_form_img03.png"></label>
														</div> -->
														<div id="checkboxFun" data-method="checkAll" class="form-group draggable ui-draggable btntext1">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 多选 <img src="/static/admin/css/diycss/image/add_form_img04.png"></label>
														</div>
														<!-- <div id="checkbox" class="form-group draggable ui-draggable btntext">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 多选 <img src="/static/admin/css/diycss/image/add_form_img04.png"></label>
														</div> -->
														<div id="textarea" class="form-group draggable ui-draggable btntext">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 多行文本 <img src="/static/admin/css/diycss/image/add_form_img02.png"></label>
														</div>
														<div id="www" class="form-group draggable ui-draggable btntext">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 超链接 <img src="/static/admin/css/diycss/image/add_form_img02.png"></label>
														</div>
														<div id="datetime" class="form-group draggable ui-draggable btntext">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 时间 <img src="/static/admin/css/diycss/image/add_form_img06.png"></label>
														</div>
														<div id="file" class="form-group draggable ui-draggable btntext">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 上传 <img src="/static/admin/css/diycss/image/add_form_img07.png"></label>
														</div>
														<div id="picker" class="form-group draggable ui-draggable btntext">
															<label class="col-sm-12"><i class="fa fa-arrows"></i> 省市区 <img src="/static/admin/css/diycss/image/add_form_img08.png"></label>
														</div>
													</form>
													<div class="clearfix"></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- <div class="btnBox"><button type="submit" class="btn btn-warning" data-clipboard-text="testing" id="copy-to-clipboard">复制代码</button></div> -->
			<div class="btnBox"><button type="submit" class="btn btn-warning" data-clipboard-text="testing" id="update">保存</button></div>
			<div id="checkbox" class="form-group draggable ui-draggable btntext" style="display:none;">
				<label class="col-sm-12"><i class="fa fa-arrows"></i> 多选 <img src="/static/admin/css/diycss/image/add_form_img04.png"></label>
			</div>
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
	var data  = [];
	var dataid = JSON.parse('<?php echo $datacate["id"]; ?>'),
		datename = JSON.parse('<?php echo $datacate["name"]; ?>'),
		datesonid = JSON.parse('<?php echo $datacate["sonid"]; ?>'),
		datesonname = JSON.parse('<?php echo $datacate["sonname"]; ?>'),
		selecttable = JSON.parse('<?php echo $C_cate["table"]; ?>'),
		selectname = JSON.parse('<?php echo $C_cate["name"]; ?>');

		selectlist = [],

		<?php if(is_array($C_cate['list']) || $C_cate['list'] instanceof \think\Collection || $C_cate['list'] instanceof \think\Paginator): if( count($C_cate['list'])==0 ) : echo "" ;else: foreach($C_cate['list'] as $key=>$item): ?>

			selectlist[<?php echo htmlentities($key); ?>] = [];
			selectlist[<?php echo htmlentities($key); ?>]['id'] = [];
			selectlist[<?php echo htmlentities($key); ?>]['name'] = [];
			selectlist[<?php echo htmlentities($key); ?>]['sonid'] = [];
			selectlist[<?php echo htmlentities($key); ?>]['sonname'] = [];
			selectlist[<?php echo htmlentities($key); ?>]['id'] = JSON.parse('<?php echo $item["id"]; ?>')
			selectlist[<?php echo htmlentities($key); ?>]['name'] = JSON.parse('<?php echo $item["name"]; ?>')
			selectlist[<?php echo htmlentities($key); ?>]['sonid'] = JSON.parse('<?php echo $item["sonid"]; ?>')
			selectlist[<?php echo htmlentities($key); ?>]['sonname'] = JSON.parse('<?php echo $item["sonname"]; ?>')

		<?php endforeach; endif; else: echo "" ;endif; ?>

		// var datacateStr = "<?php echo htmlentities($datacate['name']); ?>";
		// [&quot;一级字典&quot;,&quot;十万个为什么&quot;,&quot;新华字典&quot;]
		// console.log(selectname);



    
	
	

	$(document).ready(function() {
		function tableLoad(id, name="", title="", placeholder=[],must = false, datacate = '', types = '', cid = 0 ) {
			
        $(tableList(id,name,title,placeholder,must,datacate,types,cid)).appendTo($(".ui-sortable"));
        $(".labcheck input").unbind('click');
        //时间初始化
                $(".form_datetime").datetimepicker({
                    language: 'zh-CN', //日期
                    format: "yyyy/mm/dd hh:ii",
                    initialDate: new Date(), //初始化当前日期
                    autoclose: true, //选中自动关闭
                    todayBtn: true //显示今日按钮
                });
                //上传初始化
                $('.uploadfile').fileinput({
                    language: 'zh'
                });
                //省市区初始化
                $('.distpicker').distpicker({
                    province: '省份名',
                    city: '城市名',
                    district: '区名',
                    autoSelect: true,
                    placeholder: false
                });
    }
		var parr = [];
		var must = false;
		<?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$i): ?>
			must = false;
			parr = [];
			<?php if(!(empty($i['must']) || (($i['must'] instanceof \think\Collection || $i['must'] instanceof \think\Paginator ) && $i['must']->isEmpty()))): ?>
				must = '<?php echo htmlentities($i['must']); ?>';
			<?php endif; if(!(empty($i['placeholder']) || (($i['placeholder'] instanceof \think\Collection || $i['placeholder'] instanceof \think\Paginator ) && $i['placeholder']->isEmpty()))): if(is_array($i['placeholder']) || $i['placeholder'] instanceof \think\Collection || $i['placeholder'] instanceof \think\Paginator): if( count($i['placeholder'])==0 ) : echo "" ;else: foreach($i['placeholder'] as $key=>$v): ?>
					parr.push('<?php echo htmlentities($v); ?>');
				<?php endforeach; endif; else: echo "" ;endif; ?>
			<?php endif; ?>
			tableLoad('<?php echo htmlentities($i['type']); ?>','<?php echo htmlentities($i['name']); ?>','<?php echo htmlentities($i['title']); ?>',parr,must,'<?php echo htmlentities($i['datacate']); ?>','<?php echo htmlentities($i['types']); ?>','<?php echo htmlentities($i['cid']); ?>');
			picker_show();
		<?php endforeach; endif; else: echo "" ;endif; ?>


		layui.use('form',function(){
			var form = layui.form
		
			var active = {
				selectAll: function(){
					var str = '<select class="diySelect" style="width:100%;font-size:14px;padding:5px 10px;" >';
					var str2 = '<select class="diySelect" style="width:100%;font-size:14px;padding:5px 10px;" >';
					var son = '';
					var status = 0; 
					for( var i = 0; i < selectname.length; i++){
						str = str + '<option value="'+ i +'">'+selectname[i]+'</option>';
					};
					
					str = str + '</select>';
						
					for( var i = 0; i < selectlist[0]['name'].length; i++){
						str2 = str2 + '<option value="'+ i +'">'+selectlist[0]['name']+'</option>';
						
					};
					str2 = str2 + '</select>';
					for( var j = 0; j < selectlist[0]['sonname'].length; j++ ){
						son = son + ' ' + selectlist[0]['sonname'][j] + '&nbsp;&nbsp;';
					}
					var content = '<div style="font-size:18px;margin:5px;">下拉选择</div><hr style="height:1px;"> <div style="margin: 10px auto;width:80%;height:10%;"><select class="dataStatus" style="padding:5px 10px;"><option value="1">基础信息</option><option value="2">数据字典</option></select></div> <div class="diySelectText" style="margin:10px;font-size:16px;">字典选择</div><div class="diySelectHtml" style="margin: 10px auto;width:80%;height:10%;">'+str+'</div> <div class="diySelectHtml2" style="margin: 10px auto;width:80%;height:10%;display:block;">'+str2+'</div> <div class="diySonText" style="margin-left:10%;"> '+ son +' </div>';
					layer.open({
						type: 1
						,title: false //不显示标题栏
						,closeBtn: false
						,area: ['400px','400px;']
						,shade: 0.8
						,id: 'modifyBox' //设定一个id，防止重复弹出
						,btn: ['确定', '取消']
						,btnAlign: 'c'
						,moveType: 1 //拖拽模式，0或者1
						,content: content
						,success: function(layero){
							$('.dataStatus').on('click',function(){
								status = $(this).val(); 
								var str = '<select class="diySelect" style="width:100%;font-size:14px;padding:5px 10px;" >';
								var	str2 = '<select class="diySelectSon" style="width:100%;font-size:14px;padding:5px 10px;" >';
								var son = '';

								if( status == 1 ){
									$('.diySelectHtml2').css('display','block');
									$('.diySelectText').html('基本信息');
									for( var i = 0; i < selectname.length; i++){
										str = str + '<option value="'+ i +'">'+selectname[i]+'</option>';
									};
									
									
									str = str + '</select>';
									
						
									for( var i = 0; i < selectlist[0]['name'].length; i++){
										str2 = str2 + '<option value="'+ i +'">'+selectlist[0]['name']+'</option>';
										
									};
									str2 = str2 + '</select>';

									for( var j = 0; j < selectlist[0]['name'].length; j++ ){
										son = son + ' ' + selectlist[0]['name'][j] + '&nbsp;&nbsp;';
									}
									$('.diySelectHtml2').html(str2);

								}

								if( status == 2 ){
									$('.diySelectHtml2').css('display','none');
									$('.diySelectText').html('数据字典');
									for( var i = 0; i < datename.length; i++){
										str = str + '<option value="'+ i +'">'+datename[i]+'</option>';
									};
									for( var j = 0; j < datesonname[0].length; j++ ){
										son = son + ' ' + datesonname[0][j] + '&nbsp;&nbsp;';
									}
									str = str + '</select>';
								}
								
								$('.diySelectHtml').html(str);
								$('.diySonText').html(son);
							})

							$('.diySelectHtml').on('click',function(){
								
								var s = $('.dataStatus').val();
								var num = 0;
								$.each($('.diySelectHtml .diySelect option'),function(index){
									// console.log($(this).prop('selected'));
									if( $(this).prop('selected') == true ){
										num = index;
									}
								})
								
								var son = '';
								if( s == 1 ){
									var	str2 = '<select class="diySelectSon" style="width:100%;font-size:14px;padding:5px 10px;" >';
								
									for( var i = 0; i < selectlist[num]['name'].length; i++){
										str2 = str2 + '<option value="'+ i +'">'+selectlist[num]['name'][i]+'</option>';
									};
									str2 = str2 + '</select>';
									
									$('.diySelectHtml2').html(str2);

									for( var j = 0; j < selectlist[num]['sonname'][0].length; j++ ){
										son = son + ' ' + selectlist[num]['sonname'][0][j] + '&nbsp;&nbsp;';
									}
								}
								
								if( s == 2 ){
									for( var j = 0; j < datesonname[num].length; j++ ){
										son = son + ' ' + datesonname[num][j] + '&nbsp;&nbsp;';
									}
								}
								$('.diySonText').html(son);
							})

							$('.diySelectHtml2').on('click',function(){
								
								var s = $('.dataStatus').val();
								var num = 0;
								var num2 = 0;
								$.each($('.diySelectHtml .diySelect option'),function(index){
									// console.log($(this).prop('selected'));
									if( $(this).prop('selected') == true ){
										num = index;
									}
								})
								$.each($('.diySelectHtml2 .diySelectSon option'),function(index){
									// console.log($(this).prop('selected'));
									if( $(this).prop('selected') == true ){
										num2 = index;
									}
								})
								var son = '';
								if(selectlist[num]['sonname'][num2]){
									for( var j = 0; j < selectlist[num]['sonname'][num2].length; j++ ){
										son = son + ' ' + selectlist[num]['sonname'][num2][j] + '&nbsp;&nbsp;';
									}
								}
								
								$('.diySonText').html(son);
							})



							$('.layui-layer-btn0').click(function(){
								var num = 0;
								var num2 = 0;
								var datacate = 0;
								var type = '';
								var cid = 0;
								var s = $('.dataStatus').val();
								$.each($('.diySelectHtml .diySelect option'),function(index){
									if( $(this).prop('selected') == true ){
										num = index;
									}
								})
								if( s == 1 ){
									
									$.each($('.diySelectHtml2 .diySelectSon option'),function(index){
										if( $(this).prop('selected') == true ){
											num2 = index;
										}
									})
									type = selecttable[num];
									var title = selectlist[num]['name'][num2]+' ：';
									var placeholder = selectlist[num]['sonname'][num2];
									cid = selectlist[num]['sonid'][num2][0]
									datacate = selectlist[num]['id'][num2]
								}else{
									type = 'datacate';
									var title = datename[num]+' ：';
									var placeholder = datesonname[num]
									datacate = dataid[num]
								}
								var id = 'select';
								var name = '';
								
								var must = false;
								
								// console.log(num);
								
								// $.each($('.photo_checkbox'),function(){
								// 	if( $(this).prop('checked') == true ){
								// 		id.push($(this).val());
								// 	}
								// })
								
								$(tableList(id,name,title,placeholder,must,datacate,type,cid)).appendTo($(".ui-sortable"));
							});
						}
					});
				},
				radioAll: function(){
					
					var str = '<select class="diySelect" style="width:100%;font-size:14px;padding:5px 10px;" >';
					var son = '';
					for( var i = 0; i < datename.length; i++){
						str = str + '<option value="'+ i +'">'+datename[i]+'</option>';
					};
					for( var j = 0; j < datesonname[0].length; j++ ){
						son = son + ' ' + datesonname[0][j] + '&nbsp;&nbsp;';
					}
					str = str + '</select>';
					var content = '<div style="font-size:18px;margin:5px;">单选</div><hr style="height:1px;"><div style="margin:10px;font-size:16px;">字典选择</div><div class="" style="margin: 10px auto;width:80%;height:20%;">'+str+'</div> <div class="diySonText" style="margin-left:10%;"> '+ son +' </div>';
					layer.open({
						type: 1
						,title: false //不显示标题栏
						,closeBtn: false
						,area: ['400px','300px;']
						,shade: 0.8
						,id: 'modifyBox' //设定一个id，防止重复弹出
						,btn: ['确定', '取消']
						,btnAlign: 'c'
						,moveType: 1 //拖拽模式，0或者1
						,content: content
						,success: function(layero){
							
							$('.diySelect').on('click',function(){
								var s = $('.dataStatus').val();
								var num = $(this).val();
								var son = '';
								for( var j = 0; j < datesonname[num].length; j++ ){
									son = son + ' ' + datesonname[num][j] + '&nbsp;&nbsp;';
								}
								$('.diySonText').html(son);
							})

							$('.layui-layer-btn0').click(function(){
								var id = 'radio';
								var num = $('.diySelect').val();
								var name = '';
								var title = datename[num]+' ：';
								var must = false;
								
								// console.log(num);
								var placeholder = datesonname[num]
								// $.each($('.photo_checkbox'),function(){
								// 	if( $(this).prop('checked') == true ){
								// 		id.push($(this).val());
								// 	}
								// })
								$(tableList(id,name,title,placeholder,must,dataid[num],'datacate')).appendTo($(".ui-sortable"));
							});
						}
					});
				},
				checkAll: function(){
					
					var str = '<select class="diySelect" style="width:100%;font-size:14px;padding:5px 10px;" >';
					var son = '';
					for( var i = 0; i < datename.length; i++){
						str = str + '<option value="'+ i +'">'+datename[i]+'</option>';
					};
					for( var j = 0; j < datesonname[0].length; j++ ){
						son = son + ' ' + datesonname[0][j] + '&nbsp;&nbsp;';
					}
					str = str + '</select>';
					var content = '<div style="font-size:18px;margin:5px;">多选</div><hr style="height:1px;"><div style="margin:10px;font-size:16px;">字典选择</div><div class="" style="margin: 10px auto;width:80%;height:20%;">'+str+'</div> <div class="diySonText" style="margin-left:10%;"> '+ son +' </div>';
					layer.open({
						type: 1
						,title: false //不显示标题栏
						,closeBtn: false
						,area: ['400px','300px;']
						,shade: 0.8
						,id: 'modifyBox' //设定一个id，防止重复弹出
						,btn: ['确定', '取消']
						,btnAlign: 'c'
						,moveType: 1 //拖拽模式，0或者1
						,content: content
						,success: function(layero){
							
							$('.diySelect').on('click',function(){
								var s = $('.dataStatus').val();
								var num = $(this).val();
								var son = '';
								for( var j = 0; j < datesonname[num].length; j++ ){
									son = son + ' ' + datesonname[num][j] + '&nbsp;&nbsp;';
								}
								$('.diySonText').html(son);
							})

							$('.layui-layer-btn0').click(function(){
								var id = 'checkbox';
								var num = $('.diySelect').val();
								var name = '';
								var title = datename[num]+' ：';
								var must = false;
								
								// console.log(num);
								var placeholder = datesonname[num]
								// $.each($('.photo_checkbox'),function(){
								// 	if( $(this).prop('checked') == true ){
								// 		id.push($(this).val());
								// 	}
								// })
								$(tableList(id,name,title,placeholder,must,dataid[num],'datacate')).appendTo($(".ui-sortable"));
							});
						}
					});
				},

			};

			$('#checkboxFun').on('mousedown', function(){
				var othis = $(this), method = othis.data('method');
				active[method] ? active[method].call(this, othis) : '';
			});
			$('#radioFun').on('mousedown', function(){
				var othis = $(this), method = othis.data('method');
				active[method] ? active[method].call(this, othis) : '';
			});
			$('#selectFun').on('mousedown', function(){
				var othis = $(this), method = othis.data('method');
				active[method] ? active[method].call(this, othis) : '';
			});


		});



	});

	$('#update').click(function(){
		var result = {}
		var arr = datacate();
		result['text'] = $('.droppable').html();
		result['d_son_sn'] = '<?php echo htmlentities($son['d_son_sn']); ?>';
		result['must'] = must();
		result['datacate'] = arr['datacate'];
		result['cid'] = arr['cid'];
		result['type'] = arr['type'];
		result['son_id'] = '<?php echo htmlentities($son['id']); ?>';
		result['parent_id'] = '<?php echo htmlentities($parent['id']); ?>';
		$.post('',result,function(data){
			if( data.code ){
				window.location.href = data.url;
			}else{
				alert(data.msg);
			}
		},'json')
	});

	function must(){
		var str = [];
		$.each($('.labcheck input'),function(index){
			if( $(this).prop('checked') == true ){
				str.push(index);
			}
			
		})
		return str;
	}
	function datacate(){
		var str = [];
		str['datacate'] = [];
		str['type'] = [];
		str['cid'] = [];
		$.each($('.labcheck input'),function(index){
			str['datacate'].push($(this).attr('datacate'));
			str['type'].push($(this).attr('types'));
			str['cid'].push($(this).attr('cid'));
			
		})
		return str;
	}
	
	



</script>

</body>
</html>