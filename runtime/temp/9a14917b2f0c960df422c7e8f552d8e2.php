<?php /*a:3:{s:60:"/www/web/codecheck/application/backman/view/order/index.html";i:1594223536;s:55:"/www/web/codecheck/application/backman/view/layout.html";i:1594223536;s:52:"/www/web/codecheck/application/backman/view/nav.html";i:1594223536;}*/ ?>
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
    /* .layui-table-cell{
        display:table-cell;
        vertical-align: middle;
    } */
    .layui-table-cell {
        height: inherit;
    }
    .layui-table-cell a{
        margin: 5px 0;
    }
    .newOrderNum{
        width:90%;
        length-height:20px;
        font-size:16px;
    }
  </style>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this"><?php echo htmlentities($pathCurrent['name']); ?></li>
        <li><a href="<?php echo url('option'); ?>">添加检测单</a></li>
    </ul>
</div>
<div class="demoTable" style="margin-top: 25px;margin-left:20px;">
    关键词：
    <div class="layui-inline" style="width:360px;">
        <input class="layui-input" name="like" id="like" placeholder="检测单号、客户名称、产品编号、名称、产品类型、建单人、备注" autocomplete="off">
    </div>
    
     客户：
    <div class="layui-inline">
		<form class="layui-form">
			<select class="layui-input" name="customer" id="customer" lay-filter="customer">
				<option value="">请选择客户：</option>
				<?php foreach($customer as $vo): ?>
				<option value="<?php echo htmlentities($vo['id']); ?>"><?php echo htmlentities($vo['customer_name']); ?></option>
				<?php endforeach; ?>
			</select>
		</form>
    </div>
     产品：
    <div class="layui-inline">
		<form class="layui-form">
			<select class="layui-input" name="goods" id="goods" lay-filter="goods">
				<option value="">请选择产品</option>
				<?php foreach($goods as $vo): ?>
				<option value="<?php echo htmlentities($vo['id']); ?>"><?php echo htmlentities($vo['title']); ?></option>
				<?php endforeach; ?>
			</select>
		</form>
    </div>
     检测流程：
    <div class="layui-inline">
		<form class="layui-form">
			<select class="layui-input" name="detection" id="detection" lay-filter="detection">
				<option value="">请选择检测流程</option>
				<?php foreach($detection as $vo): ?>
				<option value="<?php echo htmlentities($vo['id']); ?>"><?php echo htmlentities($vo['name']); ?></option>
				<?php endforeach; ?>
			</select>
		</form>
    </div>
    <br>
    <br>
    
    检测结果：
    <div class="layui-inline" style="width: 200px;">
        <select class="layui-input" name="status" id="status" autocomplete="off">
            <option value="">请选择订单状态</option>
            <option value="0">未开始</option>
            <option value="1">进行中</option>
            <option value="2">已完成</option>
            <option value="3">审核不通过</option>
        </select>
    </div>
     建单时间：
    <div class="layui-inline">
        <input type="text" class="layui-input" id="start_time" name="start_time" placeholder="yyyy-MM-dd HH:mm:ss">
    </div>
    -
    <div class="layui-inline">
        <input type="text" class="layui-input" id="end_time" name="end_time" placeholder="yyyy-MM-dd HH:mm:ss">
    </div>
    <button class="layui-btn" data-type="reload"> 搜索</button>
</div>
<div class="layui-card-body">
    <div data-model="table-bind">
        <table class="layui-hide" id="data_table" lay-filter="data_table" data-table></table>
    </div>
</div>
<script type="text/html" id="toolbarDemo">
    <div class="layui-btn-group">
        <button class="layui-btn tableHBtn" data-type="getNewData">批量建单</button>
        <button class="layui-btn tableHBtn" data-type="getDownQuecode" style="margin-left:10px!important;">批量下载二维码</button>
        <button class="layui-btn tableHBtn" data-type="getDelData" style="margin-left:10px!important;">批量删除</button>
      </div>
</script>
<script type="text/html" id="barTar">
    <a class="layui-btn layui-btn-xs" href="{{d.op}}">修改</a>
    {{#  if(d.status > 0){ }}
    <a class="layui-btn layui-btn-xs" lay-event="modify" >审核</a>
    {{#  } }}
    <a class="layui-btn layui-btn-xs" lay-event="copy" >复制</a>
    <br>
    {{#  if(d.status > 1){ }}
    <a class="layui-btn layui-btn-xs" href="{{d.print}}" target="_blank" >打印</a>
    {{#  } }}
    <a class="layui-btn layui-btn-xs" lay-event="code" >二维码</a>
    <a class="layui-btn layui-btn-xs" href="{{d.lc}}">信息管理</a>
    {{#  if(d.status <= 0){ }}
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" data-del data-id="{{d.id}}" data-table="staff">删除</a>
    {{#  } }}
</script>
<script type="text/html" id="image">
    <img src="{{d.image}}" height="30">
</script>
<script type="text/html" id="sysTar">
    {{#  if(d.status == 1){ }}
    <button type="button" class="layui-btn ">进行中</button>
    {{#  } else if(d.status == 2) { }}
    <button type="button" class="layui-btn layui-btn-normal">已完成</button>
    {{#  } else if(d.status == 3) { }}
    <button type="button" class="layui-btn layui-btn-danger">审核不通过</button>
    {{#  } else { }}
    <button type="button" class="layui-btn layui-btn-warm">未开始</button>
    {{#  } }}
</script>
<script type="text/html" id="ending"">
    {{#  if(d.engding == 1){ }}
    <button type="button" class="layui-btn layui-btn-normal">合格</button>
    {{#  } else if(d.engding == 2) { }}
    <button type="button" class="layui-btn layui-btn-warm">风险警告</button>
    {{#  } else if(d.engding == 3) { }}
    <button type="button" class="layui-btn layui-btn-warm">不合格</button>
    {{#  } else { }}
    <button type="button" class="layui-btn layui-btn-danger">待定</button>
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
   
    layui.use(['table','laydate'], function(){
        var table = layui.table;
        var laydate = layui.laydate;
        table.on('checkbox(demo)', function(obj){
            console.log(obj)
        });
        table.render({
            elem: '#data_table',
            url:SlefUrl,
            limit:10,
            toolbar: '#toolbarDemo',
            defaultToolbar: ['filter', 'print', { //自定义头部工具栏右侧图标。如无需自定义，去除该参数即可
            title: '导出Excel'
            ,layEvent: 'LAYTABLE_Excel'
            ,icon: 'layui-icon layui-icon-export'
            }],
            // cellMinWidth: 70,
            
            cols: [[
                {type: 'checkbox'}
                ,{field:'id', title: 'id', width:80, sort: true,align:'left'}
                ,{field:'order_sn', title: '订单号', width:200, sort: true,align:'left'}
                ,{field:'customer_name', title: '客户名称', sort: true,align:'left'}
                ,{field:'goods_title', title: '产品名称',  sort: true,align:'left'}
                ,{field:'spec', title: '产品型号', sort: true,align:'left'}
                ,{field:'supplier', title: '供应商', sort: true,align:'left'}
                ,{field:'staff', title: '建单人',width:120, sort: true,align:'left'}
                ,{field:'detection_name', title: '检测流程', sort: true,align:'left'}
                // ,{field:'contract_sn', title: '合同/物流编号', sort: true,align:'left'}
                // ,{field:'test_type_text', title: '检测类型', sort: true,align:'left'}
                
                // ,{field:'created_time', title: '建单时间', width:160, sort: true,align:'left'}
                ,{field:'status', title: '流程状态', sort: true,align:'left',toolbar:'#sysTar'}
                ,{field:'engding', title: '检测结果', sort: true,align:'left',toolbar:'#ending'}
                // ,{field:'remark', title: '备注', sort: true,align:'left'}
                
                ,{field:'op', title: '操作',toolbar: '#barTar',width:300,align:'center',fixed: 'right'}
            ]],
            page: true,
            id: 'testReload'
        });

        //日期时间选择器
        laydate.render({
            elem: '#start_time'
            ,type: 'datetime'
        });
        laydate.render({
            elem: '#end_time'
            ,type: 'datetime'
        });

        //头工具栏事件
        table.on('toolbar(data_table)', function(obj){
            
            if(obj.event === 'LAYTABLE_Excel'){
                var result = {};
                result.customer = $('#customer').val();
                result.goods = $('#goods').val();
                result.detection = $('#detection').val();
                result.like = $('#like').val();
                result.status = $('#status').val();
                result.start_time = $('#start_time').val();
                result.end_time = $('#end_time').val();

                DownLoadFile({'url':''+webRoot+ webControl+'/load_excel','data':result})
            }
            
        });
        table.on('tool(data_table)', function(obj){
            var data = obj.data;
            data.table = "order";
            if(obj.event === 'del'){
                layer.confirm('确定删除吗？', function(index){
                    layer.close(index);
                    form.formDel(data,obj);
                });
            }
            if(obj.event === 'copy'){
                layer.confirm('确定复制表单吗？', function(index){
                    var result = {};
                    result['id'] = data.id;
                    $.post(webRoot+ webControl+'/copy',result,function(res){
                        layer.close(index);
                        layer.alert(res.msg);
                        if( res.code ){
                            var customer = $('#customer');
                            var goods = $('#goods');
                            var detection = $('#detection');
                            var like = $('#like');
                            var status = $('#status');
                            var start_time = $('#start_time');
                            var end_time = $('#end_time');

                            //执行重载
                            table.reload('testReload', {
                                page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                                ,where: {
                                    customer: customer.val(), goods: goods.val(),detection:detection.val(),like:like.val(),status:status.val(),start_time:start_time.val(),end_time:end_time.val()
                                }
                            }, 'data');
                        }
                    },'json')
                });

                
            }

            

            // 下载二维码 code
            if(obj.event === 'code'){
                var result = {};
                result['order_sn'] = data.order_sn;
                $.post(webRoot+ webControl+'/erweimaImg',result,function(res){
                    // layer.open
                    var content = '';
                    var name = [];
                    for(var i in res.data){
                        content = content + '<img class="downloadImg" style="width:92%;margin-right:3%;" src="'+res.data[i]['url']+'" />';
                        name.push(data.order_sn + res.data[i]['type']+ '.png');
                    }
                    layer.open({
                        type: 1
                        ,title: false //不显示标题栏
                        ,closeBtn: false
                        ,area: ['600px','600px;']
                        ,shade: 0.8
                        ,id: 'modifyBox' //设定一个id，防止重复弹出
                        // ,btn: ['下载', '取消']
                        ,btn: ['关闭']
                        ,btnAlign: 'c'
                        ,moveType: 1 //拖拽模式，0或者1
                        ,content: '<div style="margin:5px 0;font-size:15px;text-align:center;">点击图片进行下载</div><div class="imgBox" style="width:90%;height:90%;margin-left:10%;margin-top:4%;"> '+content+' </div>'
                        ,success: function(layero){
                            
                            $.each($('.downloadImg'),function(index){
                                $(this).on('click',function(){
                                    var url = $(this).attr('src');
                                    downloadIamge(url,name[index])
                                })
                            })
                            // $('.layui-layer-btn0').click(function(){
                            //     downloadIamge(res.data[0]['url'],data.order_sn+'.png')
                            //     downloadIamge(res.data[1]['url'],data.order_sn+'.png')

                            // });
                        }
                    });
                    // layer.open
                },'json')

                // downloadIamge('http://www.youzuo.com/uploads/qrcode/20191222/JC2019122211563349555710.png','123.png')
            }
            // 下载二维码 code

            // modify
            if(obj.event === 'modify'){
                layer.open({
                    type: 1
                    ,title: false //不显示标题栏
                    ,closeBtn: false
                    ,area: ['550px','270px;']
                    ,shade: 0.8
                    ,id: 'modifyBox' //设定一个id，防止重复弹出
                    ,btn: ['提交', '取消']
                    ,btnAlign: 'c'
                    ,moveType: 1 //拖拽模式，0或者1
                    ,content: '<div style="margin:20px 0 0 10px;">订单号：'+data.order_sn+'</div><div class="" style="width:90%;height:50%;margin:10px auto;"><div class="statusBox" style="margin-top:40px;font-size:15px;"><label>流程状态：</label><div class="" style="margin-left:20px;display:inline-block;"><label style="margin-right:20px;"><input type="radio" name="status" value="0"> 未开始</label><label style="margin-right:20px;"><input type="radio" name="status" value="1" > 进行中</label><label  style="margin-right:20px;"><input type="radio" name="status" value="2" > 审核通过</label><label><input type="radio" name="status" value="3" > 审核不通过</label></div> </div> <div class="engdingBox" style="margin-top:20px;font-size:15px;"><label>检测结果：</label><div class="" style="margin-left:20px;display:inline-block;"><label style="margin-right:20px;"><input type="radio" name="engding" value="0" > 待定</label><label style="margin-right:20px;"><input type="radio" name="engding" value="1" > 合格</label><label style="margin-right:20px;"><input type="radio" name="engding" value="2"> 风险警告</label><label><input type="radio" name="engding" value="3"> 不合格</label></div> </div></div>'
                    ,success: function(layero){
                        if( data.status ){
                            $('input[name="status"]').eq(data.status).prop('checked',true);
                        }else{
                            $('input[name="status"]').eq(0).prop('checked',true);
                        }
                        if( data.engding ){
                            $('input[name="engding"]').eq(data.engding).prop('checked',true);
                        }else{
                            $('input[name="engding"]').eq(0).prop('checked',true);
                        }
                        
                        $('.layui-layer-btn0').click(function(){
                            var result = {};
                            var str1 = '',str2 = '';
                            result['id'] = data.id;
                            $.each( $('input[name="status"]'), function(index){
                                if( $(this).prop('checked') == true ){
                                    result['status'] = $(this).val();
                                }
                            })
                            $.each( $('input[name="engding"]'), function(index){
                                if( $(this).prop('checked') == true ){
                                    result['engding'] = $(this).val();
                                }
                            })
                            $.post(webRoot+ webControl+'/update_status',result,function(res){
                                if( res.code ){
                                    var customer = $('#customer');
                                    var goods = $('#goods');
                                    var detection = $('#detection');
                                    var like = $('#like');
                                    var status = $('#status');
                                    var start_time = $('#start_time');
                                    var end_time = $('#end_time');

                                    //执行重载
                                    table.reload('testReload', {
                                        page: {
                                            curr: 1 //重新从第 1 页开始
                                        }
                                        ,where: {
                                            customer: customer.val(), goods: goods.val(),detection:detection.val(),like:like.val(),status:status.val(),start_time:start_time.val(),end_time:end_time.val()
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
                var customer = $('#customer');
                var goods = $('#goods');
                var detection = $('#detection');
                var like = $('#like');
                var status = $('#status');
                var start_time = $('#start_time');
                var end_time = $('#end_time');

                //执行重载
                table.reload('testReload', {
                    page: {
                        curr: 1 //重新从第 1 页开始
                    }
                    ,where: {
                        customer: customer.val(), goods: goods.val(),detection:detection.val(),like:like.val(),status:status.val(),start_time:start_time.val(),end_time:end_time.val()
                    }
                }, 'data');
            },
            // 批量新建检测单
            getNewData: function(){ //获取选中数据
                    // layer.open
                    layer.open({
                        type: 1
                        ,title: false //不显示标题栏
                        ,closeBtn: false
                        ,area: ['300px','200px;']
                        ,shade: 0.8
                        ,id: 'modifyBox' //设定一个id，防止重复弹出
                        // ,btn: ['下载', '取消']
                        ,btn: ['提交','关闭']
                        ,btnAlign: 'c'
                        ,moveType: 1 //拖拽模式，0或者1
                        ,content: '<div style="margin:5px 0;font-size:15px;text-align:center;">请输入新建检测单数</div><div class="imgBox" style="width:90%;height:60%;margin-left:10%;margin-top:4%;"> <input type="number" class="newOrderNum" placeholder="请输入新建检测单数"> </div>'
                        ,success: function(layero){
                            $('.layui-layer-btn0').click(function(){
                                var result = {};
                                result.num = $('.newOrderNum').val();
                                $.post(webRoot+ webControl+'/batchOrder',result,function(res){
                                    layer.alert(res.msg);
                                    if( res.code ){
                                        setInterval(function(){
                                            location.reload();
                                        },1000)
                                    }
                                })
                            })
                            
                        }
                    });
                    // layer.open
                
                
            }
            // 批量下载二维码
            ,getDownQuecode: function(){ 
                //获取选中数目
                var checkStatus = table.checkStatus('testReload')
                ,data = checkStatus.data;
                var  order_sn = [];
                for( var i in data){
                    order_sn.push(data[i]['order_sn']);
                }
                if( order_sn.length > 0 ){
                    var result = {};
                    result.order_sn = order_sn;
                    // $.post(webRoot+ webControl+'/loadQuecode',result,function(res){
                    //     if( res.code ){
                    //         DownLoadFile(res.zip)
                    //     }
                    // })
                    DownLoadFile({'url':''+webRoot+ webControl+'/loadQuecode','data':result})

                }else{
                    layer.alert('请先选择检测单');
                }
                

            }
            // 批量删除
            ,getDelData: function(){ 
                //获取选中数目

                var checkStatus = table.checkStatus('testReload')
                ,data = checkStatus.data;
                var  id = [];
                for( var i in data){
                    id.push(data[i]['id']);
                }
                if( id.length > 0 ){
                    var result = {};
                    result.id = id;
                    layer.confirm('确定删除选中表单吗？', function(index){
                        $.post(webRoot+ webControl+'/delArr',result,function(res){
                            layer.alert(res.msg);
                            if( res.code ){
                                setInterval(function(){
                                    location.reload();
                                },1000)
                            }
                        })
                    })

                }else{
                    layer.alert('请先选择检测单');
                }
                

            }
        };

		
        $('.demoTable .layui-btn').on('click', function(){
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        });
		
		//tableHBtnJd
		$('body').on('click', '.tableHBtn',function(){
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        });

        var DownLoadFile = function (options) {
            var config = $.extend(true, { method: 'post' }, options);
            var $iframe = $('<iframe id="down-file-iframe" />');
            var $form = $('<form target="down-file-iframe" method="' + config.method + '" />');
            $form.attr('action', config.url);
            for (var key in config.data) {
                $form.append('<input type="hidden" name="' + key + '" value="' + config.data[key] + '" />');
            }
            $iframe.append($form);
            $(document.body).append($iframe);
            $form[0].submit();
            $iframe.remove();
        }
        
        function downloadIamge(imgsrc,name){
            let image = new Image();
            // 解决跨域 Canvas 污染问题
            image.setAttribute("crossOrigin", "anonymous");
            image.onload = function() {
                let canvas = document.createElement("canvas");
                canvas.width = image.width;
                canvas.height = image.height;
                let context = canvas.getContext("2d");
                context.drawImage(image, 0, 0, image.width, image.height);
                let url = canvas.toDataURL("image/png"); //得到图片的base64编码数据
                let a = document.createElement("a"); // 生成一个a元素
                let event = new MouseEvent("click"); // 创建一个单击事件
                a.download = name || "photo"; // 设置图片名称
                a.href = url; // 将生成的URL设置为a.href属性
                a.dispatchEvent(event); // 触发a的单击事件
            };
            image.src = imgsrc;
        }

    });
</script>

</body>
</html>