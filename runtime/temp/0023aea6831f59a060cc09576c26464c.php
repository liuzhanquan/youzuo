<?php /*a:1:{s:63:"D:\phpstudy\WWW\youzuo\application\backman\view\index\home.html";i:1575864450;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>平台管理中心</title>
<link rel="icon" sizes="32*32" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="/static/css/font.css">
<link rel="stylesheet" type="text/css" href="/static/css/admin.css">
<link rel="stylesheet" type="text/css" href="//unpkg.com/iview/dist/styles/iview.css">
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/vue"></script>
<script type="text/javascript" src="//unpkg.com/iview/dist/iview.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/vue-resource@1.5.1"></script>
<script type="text/javascript" src="/static/js/jquery.js"></script>
<script type="text/javascript" src="/static/js/do.js"></script>
<script src="/static/lib/layui/layui.js" charset="utf-8"></script>
<script type="text/javascript" src="/static/js/admin.js"></script>
<script type="text/javascript" src="/static/js/package.js" data-path="/static/js/" data-root="/<?php echo request()->controller(); ?>/"  data-src="/static/js/common"></script>
<style type="text/css">
.layui-fluid{padding: 15px;}
</style>
</head>
<body>
<div class="layui-fluid">
	<div class="layui-row layui-col-space15">
		<div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body ">
                    <blockquote class="layui-elem-quote layui-quote-nm">欢迎【<?php echo htmlentities($adminGroup->name); ?>】：
                        <span class="x-red"><?php echo htmlentities($admin->username); ?></span>  当前时间：<?php echo date('Y-m-d H:i:s'); ?>
                    </blockquote>
                </div>
            </div>
        </div>
        <!--  -->
		<div class="layui-col-md12">
	        <div class="layui-card">
	            <div class="layui-card-header">数据统计</div>
	            <div class="layui-card-body ">
	                <ul class="layui-row layui-col-space10 layui-this x-admin-carousel x-admin-backlog">
	                    <li class="layui-col-md3 layui-col-xs6">
	                        <a href="javascript:;" class="x-admin-backlog-body">
	                            <h3>素材数</h3>
	                            <p><cite><?php echo htmlentities($info['friendCount']); ?></cite></p>
	                        </a>
	                    </li>
	                    <li class="layui-col-md3 layui-col-xs6">
	                        <a href="javascript:;" class="x-admin-backlog-body">
	                            <h3>会员数</h3>
	                            <p><cite><?php echo htmlentities($info['userCount']); ?></cite></p>
	                        </a>
	                    </li>
	                    <li class="layui-col-md3 layui-col-xs6">
	                        <a href="javascript:;" class="x-admin-backlog-body">
	                            <h3>代理数</h3>
	                            <p><cite><?php echo htmlentities($info['agentCount']); ?></cite></p>
	                        </a>
	                    </li>
	                    <li class="layui-col-md3 layui-col-xs6">
	                        <a href="javascript:;" class="x-admin-backlog-body">
	                            <h3>订单数</h3>
	                            <p><cite><?php echo htmlentities($info['orderCount']); ?></cite></p>
	                        </a>
	                    </li>
	                </ul>
	            </div>
	        </div>
	    </div>
        <div class="layui-col-sm12 layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">月度交易统计</div>
                <div class="layui-card-body" style="min-height: 500px;">
                    <div id="main2" class="layui-col-sm12" style="height: 500px;"></div>

                </div>
            </div>
        </div>
        <!--  -->
	</div>
</div>
<script src="https://cdn.bootcss.com/echarts/4.2.1-rc1/echarts.min.js"></script>
<script type="text/javascript">
// 基于准备好的dom，初始化echarts实例
var myChart = echarts.init(document.getElementById('main2'));

// 指定图表的配置项和数据
var option = {
    tooltip : {
        trigger: 'axis',
        axisPointer: {
            type: 'cross',
            label: {
                backgroundColor: '#6a7985'
            }
        }
    },
    legend: {
        data:['交易金额','收入金额','支出金额']
    },
    toolbox: {
        feature: {
            saveAsImage: {}
        }
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis : [
        {
            type : 'category',
            boundaryGap : false,
            data : ['2019年-12月','2019年-11月','2019年-10月'],
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    series : [
        {
            name:'交易金额',
            type:'line',
            stack: '总量',
            areaStyle: {normal: {}},
            data:[11,22,33]
        },
        {
            name:'收入金额',
            type:'line',
            stack: '总量',
            areaStyle: {normal: {}},
            data:[12,13,14]
        },
        {
            name:'支出金额',
            type:'line',
            stack: '总量',
            areaStyle: {normal: {}},
            data:[91,92,93]
        },
    ]
};


// 使用刚指定的配置项和数据显示图表。
myChart.setOption(option);


</script>