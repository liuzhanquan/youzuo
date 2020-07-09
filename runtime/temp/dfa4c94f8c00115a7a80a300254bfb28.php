<?php /*a:1:{s:57:"/www/web/youzuo/application/backman/view/order/idnex.html";i:1582615306;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="renderer" content="webkit"/>
<meta name="force-rendering" content="webkit"/>
<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/>
<title>首页</title>
<link href="/static/admin/css/diycss/idenx.css" rel="stylesheet">
<script src="/static/js/jquery.js"></script>
<script src="/static/admin/css/diycss/jQueryMigrate-v1.4.1.js"></script>
<script src="/static/admin/css/diycss/jquery.jqprint-0.3.js"></script>
</head>
<body>
<div id="app">
	<div class="s_wid1200" style="width: 1060px;">
		<div class="header s_text_c s_po_re" style="line-height: 65px;">
			<h1>佛山市优坐家具检测服务有限公司
				<br>
				检测报告
			</h1>
			<img class="ewm s_po_ab" src="<?php echo htmlentities($qrcode); ?>">
		</div>
		<div style="margin-bottom:20px;">以上信息由委托方客户提供并承担真实性：</div>
		<ul class="s_flex_ali s_wrap">
			<li class="s_wid33 s_mb20">检测单号：<?php echo htmlentities($order['order_sn']); ?></li>
			<li class="s_wid33 s_mb20">客户名称：<?php echo htmlentities($customer['customer_name']); ?></li>
			<li class="s_wid33 s_mb20">客户地址：<?php echo htmlentities($customer['province']); ?><?php echo htmlentities($customer['city']); ?><?php echo htmlentities($customer['county']); ?><?php echo htmlentities($customer['address']); ?></li>
			<li class="s_wid33 s_mb20">产品名称：<?php echo htmlentities($order['gid']['text']); ?></li>
			<li class="s_wid33 s_mb20">产品型号：<?php echo htmlentities($order['spec']); ?></li>
			<li class="s_wid33 s_mb20">检测流程：<?php echo htmlentities($order['did']['text']); ?></li>
			
			<?php if(!(empty($order['supplier']) || (($order['supplier'] instanceof \think\Collection || $order['supplier'] instanceof \think\Paginator ) && $order['supplier']->isEmpty()))): ?>
			<li class="s_wid33 s_mb20">供应商：<?php echo htmlentities($order['supplier']); ?></li>
			<?php endif; if(!(empty($order['composition']) || (($order['composition'] instanceof \think\Collection || $order['composition'] instanceof \think\Paginator ) && $order['composition']->isEmpty()))): ?>
			<li class="s_wid33 s_mb20">材料成分：<?php echo htmlentities($order['composition']); ?></li>
			<?php endif; if(!(empty($order['machine']) || (($order['machine'] instanceof \think\Collection || $order['machine'] instanceof \think\Paginator ) && $order['machine']->isEmpty()))): ?>
			<li class="s_wid33 s_mb20">注塑机台：<?php echo htmlentities($order['machine']); ?></li>
			<?php endif; if(!(empty($order['contract_sn']) || (($order['contract_sn'] instanceof \think\Collection || $order['contract_sn'] instanceof \think\Paginator ) && $order['contract_sn']->isEmpty()))): ?>
			<li class="s_wid33 s_mb20">合同 / 物流编号：<?php echo htmlentities($order['contract_sn']); ?></li>
			<?php endif; if(!(empty($order['test_type']['text']) || (($order['test_type']['text'] instanceof \think\Collection || $order['test_type']['text'] instanceof \think\Paginator ) && $order['test_type']['text']->isEmpty()))): ?>
			<li class="s_wid33 s_mb20">检测类型：<?php echo htmlentities($order['test_type']['text']); ?></li>
			<?php endif; if(!(empty($order['remark']) || (($order['remark'] instanceof \think\Collection || $order['remark'] instanceof \think\Paginator ) && $order['remark']->isEmpty()))): ?>
			<li class="s_wid33 s_mb20">备注：<?php echo htmlentities($order['remark']); ?></li>
			<?php endif; ?>
			<li class="s_wid33 s_mb20">建单人：<?php echo htmlentities($order['sid']); ?></li>
			<li class="s_wid33 s_mb20">建单时间：<?php echo htmlentities($order['created_time']); ?></li>
			<li class="s_wid33 s_mb20">检测结果：<?php echo htmlentities($order['engding']); ?></li>
		</ul>
		<ul class="tab s_mt30">
			<li class="s_flex_ali s_bb1">
				<div class="s_wid20 s_p15 s_text_c">检测项目</div>
				<div class="s_wid80 s_p15 s_text_c tab_right">检测数据及记录</div>
			</li>
			<?php if(is_array($resList) || $resList instanceof \think\Collection || $resList instanceof \think\Paginator): if( count($resList)==0 ) : echo "" ;else: foreach($resList as $key=>$item): ?>
			<li class="s_flex_ali">
				<!-- 左边 -->
				<div class="s_wid20 s_p15 s_text_c"><?php echo htmlentities($son[$key]['name']); ?></div>
				<!-- 右边 -->
				<div class="s_wid80 s_text_c tab_right">
					<!-- 右边循环项 -->
					<?php if(is_array($item['son']) || $item['son'] instanceof \think\Collection || $item['son'] instanceof \think\Paginator): if( count($item['son'])==0 ) : echo "" ;else: foreach($item['son'] as $key=>$so): ?>
					<div class="s_flex_bian s_p15 tab_right_box">
						<!-- 右边的左边 -->
						<div class="s_wid30 s_text_l">
							<div class="s_c314 s_mb10">录入时间：<?php echo htmlentities($so['created_time']); ?></div>
							<div class="s_c314 s_mb10">录入人员：<?php echo htmlentities($so['name']); ?></div>
						</div>
						<!-- 右边的右边 -->
						<div class=" s_wid70 s_text_l s_flex_bian s_wrap">
							<?php if(is_array($so['list']) || $so['list'] instanceof \think\Collection || $so['list'] instanceof \think\Paginator): if( count($so['list'])==0 ) : echo "" ;else: foreach($so['list'] as $key=>$spec): if(!(empty(html_array_string($spec['value'],$spec['type'])) || ((html_array_string($spec['value'],$spec['type']) instanceof \think\Collection || html_array_string($spec['value'],$spec['type']) instanceof \think\Paginator ) && html_array_string($spec['value'],$spec['type'])->isEmpty()))): ?>
								<div class="s_c314 s_mb15 s_wid48"><?php echo htmlentities($spec['title']); ?><?php echo html_array_string($spec['value'],$spec['type']); ?></div>
							<?php endif; ?>
							<!-- <div class="s_c314 s_mb15 s_wid48">输入框：输入框信息#</div>
							<div class="s_c314 s_mb15 s_wid48">单选：单选</div>
							<div class="s_c314 s_mb15 s_wid48">多选：<span class="s_c314">多选1，</span><span class="s_c314">多选2</span></div>
							<div class="s_c314 s_mb15 s_wid48">选择：选择框信息#</div>
							<div class="s_c314 s_mb15 s_wid48">超链接：#录入信息#</div>
							<div class="s_c314 s_mb15 s_wid48">省市区：#录入信息#</div>
							<div class="s_c314 s_mb15 s_wid48">图片： 
								<div class="s_flex_ali">
									<img src="1562989437.png"><img src="1562989437.png"><img src="1562989437.png">
								</div>	
							</div>
							<div class="s_c314 s_mb15 s_wid48">富文本：富文本内容同富文本内容同富文本内容同富文本内容同富文本内容同富文本内容同 
								<div class="s_flex_ali">
									<img src="1562989437.png"><img src="1562989437.png">
								</div>
							</div> -->
							<?php endforeach; endif; else: echo "" ;endif; ?>
						</div>
					</div>
					<?php endforeach; endif; else: echo "" ;endif; ?>
				</div>
			</li>
			<?php endforeach; endif; else: echo "" ;endif; ?>
			
			
		</ul>
		
	</div>
	<div class="header s_text_c s_po_re" style="line-height: 85px;height: 85px;">
		<h1>报告结束</h1>
	</div>
</div>
<div class="s_flex_center s_ptb40">
	<div class="dayinBnt s_text_c s_c316" id="dayinBnt">打印</div>
	<div class="dayinBnt s_text_c s_c316 s_ml20" id="close_window">关闭</div>
</div>
<script>
	$('#dayinBnt').on('click',function(){
        $("#app").jqprint();
    }) 
	$('#close_window').on('click',function(){
		window.close();
	})
</script>
</body>
</html>