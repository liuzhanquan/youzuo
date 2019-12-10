<?php /*a:3:{s:65:"D:\phpstudy\WWW\youzuo\application\backman\view\staff\option.html";i:1575958775;s:59:"D:\phpstudy\WWW\youzuo\application\backman\view\layout.html";i:1566201690;s:56:"D:\phpstudy\WWW\youzuo\application\backman\view\nav.html";i:1563374948;}*/ ?>
<!DOCTYPE html>
<html class="admin-sm">
<head>
<meta charset="utf-8">
<title>平台管理中心</title>
<link rel="icon" sizes="32*32" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="/static/css/font.css">
<link rel="stylesheet" type="text/css" href="/static/css/admin.css">
<link rel="stylesheet" type="text/css" href="/static/iview/iview.css">
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
    #check_box .layui-icon{
        height:30px;
    }
</style>

<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
	    <li><a href="<?php echo url('index'); ?>"><?php echo htmlentities($pathCurrent['name']); ?></a></li>
	    <li class="layui-this"><?php if(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty())): ?>新增<?php else: ?>编辑<?php endif; ?>员工</li>
  	</ul>
</div>
<div class="layui-card-body">
	<form class="layui-form" data-model="form-submit">
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
		  	<legend>基本设置</legend>
		</fieldset>
		<div class="layui-form-item">
			<label class="layui-form-label">员工编码</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="staff_sn" value="<?php echo htmlentities($info['staff_sn']); ?>">
			</div>
		</div>
		
		<div class="layui-form-item">
			<label class="layui-form-label">员工分类</label>
			<div class="layui-input-block">
				<select name="cid">
					<?php foreach($list as $vo): ?>
					<option value="<?php echo htmlentities($vo['id']); ?>" <?php if($vo['id'] == $info['cid']): ?>selected=""<?php endif; ?>><?php echo $vo['cname']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="layui-form-item">
            <label class="layui-form-label">员工名称</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" name="name" value="<?php echo htmlentities($info['name']); ?>" datatype="*">
            </div>
        </div>
		<div class="layui-form-item">
			<label class="layui-form-label">手机号(登录用)</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="phone" value="<?php echo htmlentities($info['phone']); ?>">
			</div>
		</div>
		<div class="layui-form-item">
            <label class="layui-form-label">密码</label>
            <div class="layui-input-block">
                <input type="password" class="layui-input" name="password" value="">
            </div>
        </div>
		<div class="layui-form-item">
			<label class="layui-form-label">邮箱</label>
			<div class="layui-input-block">
				<input type="text" class="layui-input" name="email" value="<?php echo htmlentities($info['email']); ?>">
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
        
        <div class="layui-form-item" id="check_box">
            <label class="layui-form-label">权限</label>
            <div class="layui-input-block">
                <?php if(is_array($power) || $power instanceof \think\Collection || $power instanceof \think\Paginator): if( count($power)==0 ) : echo "" ;else: foreach($power as $key=>$item): ?>
                    <input class="powerCheckbox" type="checkbox" pid="<?php echo htmlentities($item['parent_id']); ?>" name="power[]" <?php if(powerStatus($item['id'],$info['power'])): ?>checked<?php endif; ?> value="<?php echo htmlentities($item['id']); ?>" title="<?php echo htmlentities($item['name']); ?>">
                        <?php if($item['son']): ?>
                            <br/>
                            <span style="margin-left:20px;">┗　</span>
                            <?php if(is_array($item['son']) || $item['son'] instanceof \think\Collection || $item['son'] instanceof \think\Paginator): if( count($item['son'])==0 ) : echo "" ;else: foreach($item['son'] as $key=>$i): ?>
                                <input class="powerCheckbox powerCheckbox<?php echo htmlentities($i['parent_id']); ?>" type="checkbox" pid="<?php echo htmlentities($i['parent_id']); ?>" name="power[]" value="<?php echo htmlentities($i['id']); ?>" <?php if(powerStatus($i['id'],$info['power'])): ?>checked<?php endif; ?> title="<?php echo htmlentities($i['name']); ?>">
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        <?php endif; ?>
                    <br/>
                <?php endforeach; endif; else: echo "" ;endif; ?>
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
    var cs = 0;
    layui.use('form',function(){
        var form = layui.form
        
       $.each($('#check_box .layui-unselect'),function(index){
            $(this).click(function(){
                if ( $('.powerCheckbox').eq(index).attr('pid') == 0 && cs == 0 ) {
                    var status = $('.powerCheckbox').eq(index).prop('checked')
                    checkboxStatus( $('.powerCheckbox').eq(index).val(), $('.powerCheckbox').eq(index).prop('checked'), index);
                }else if( $('.powerCheckbox').eq(index).attr('pid') != 0 && cs == 0 ){
                    checkboxSon($('.powerCheckbox').eq(index).attr('pid'))
                }
            });

        })

    });


    function checkboxStatus( val, status, ind ){
        cs = 1
        $.each($('#check_box .layui-unselect'),function(index){
            if ( $('.powerCheckbox').eq(index).attr('pid') == val ) {
                $(this).trigger("click");
            }
        })
        cs = 0
    }
    function checkboxSon( val ){
        cs = 1
        var num = 0
        var total = 0
        var ind = -1
        //$(this).trigger("click");
        $.each($('#check_box .layui-unselect'),function(index){
            if( $('.powerCheckbox').eq(index).val() == val ){
                ind = index
            }
            if ( $('.powerCheckbox').eq(index).attr('pid') == val ) {
                total++;
                if ( $('.powerCheckbox').eq(index).prop('checked') == true ) {
                    num++
                }
            }
        })

        if( total == num ){
            $('#check_box .layui-unselect').eq(ind).trigger("click");
        }else{
            if ( $('.powerCheckbox').eq(ind).prop('checked') == true ) {
                $('#check_box .layui-unselect').eq(ind).trigger("click");
            }
        }
        cs = 0
    }


    
    

</script>

</body>
</html>