var vueObj = new Vue({
    el:"#app",
    data:{
        seen:"md-eye",
        passwdTy:"password"
    },
    mounted(){
        window.vueInfo = this.info;
    },
    mixins: [mixin],
    methods:{
        info:function(type,msg){
            switch(type){
                case "success": this.$Message.success(msg); break;
                case "info": this.$Message.info(msg); break;
                case "warning": this.$Message.warning(msg); break;
                case "error": this.$Message.error(msg); break;
                case "loading": this.$Message.loading(msg); break;
                case "cancel": this.$Message.destroy(); break;
            }
        },
        logout:function(){ // 退出登陆
            this.$http.get(webRoot + "auth/logout").then(function(res){
                var response = res.body;
                if(response.code){
                    this.$Message.success(response.msg);
                    setTimeout(function () {
                        window.location.href = response.url;
                    },3000);
                }
            });
        }
    }
});
/**
 * 页面框架
 */
(function ($, owner) {
    owner.frame = function () {
        //处理绑定组件
        $("[data-model]").each(function () {
            var data = $(this).data(), name = data['model'], names = name.split('-', 2);
            window[names[0]][names[1]](this, data);
        });

    };
    owner.menu = function ($el, config) {
        $($el).click(function () {
            $('body').toggleClass('mobile-menu');
        });
    };
}(jQuery, window.base = {}));


(function ($, owner) {

    /**
     * 表单提交
    **/
    owner.submit = function ($el, config) {
        Do('form', function () {
            var defaults = {
                postFun: {},
                returnFun: {},
                type:'1',
                callback:{},
            }
            var options = $.extend(defaults, config);
             $($el).Validform({
                ajaxPost: true,
                postonce: true,
                tiptype: function (msg, o, cssctl) {
                    if (!o.obj.is("form")) {
                        var objtip = o.obj.parent().find(".tip-alert");
                        if(o.type == 2){
                            objtip.hide();
                            var className = 'has-success';
                        }else if(o.type == 3){
                            vueInfo('info',msg);
                            objtip.html(msg).show();
                            var className = 'has-error';
                        }
                    }
                    o.obj.parents('.form-item').removeClass('has-success has-error');
                    o.obj.parents('.form-item').addClass(className);
                },
                beforeSubmit : function (){
                    vueInfo('loading','执行操作中...');
                    $($el).find("button[type=submit]").prepend('<i class="icon icon-spin icon-spinner"></i> ');
                    $($el).find("button").attr("disabled", true);
                },
                callback : function (json){
                    vueInfo('cancel','');
                    // ajax提交表单返回
                    //成功回调
                    if (typeof options.callback === 'function') {
                        options.callback(json);
                        return;
                    }
                    if (typeof options.callback === 'string') {
                        window[options.callback](json);
                        return;
                    }
                    if(json.code){
                        if(json.data == '1'){
                            // 弹窗关闭
                           vueInfo('success',json.msg);
                            setTimeout(function () {
                                parent.layer.closeAll();
                                if(json.url){
                                    parent.window.location.href = json.url;
                                }
                            },3000);
                        }else{
                           vueInfo('success',json.msg);
                           if(json.url){
                                setTimeout(function () {
                                    window.location.href = json.url;
                                },3000);
                            }
                        }
                    }else{
                        vueInfo('error',json.msg);
                        if(json.url){
                            setTimeout(function () {
                                window.location.href = json.url;
                            },3000);
                        }
                    }
                    $($el).find("button[type=submit]").find('i:first-child').remove();
                    $($el).find("button").attr("disabled", false);
                }
            });
        });
    };
    /**
     * tags标签
    **/
    owner.tag = function ($el, config) {
        var defaultConfig = {};
        config = $.extend(defaultConfig, config);
        Do('tag', function () {
            $($el).tagsinput(config);
        });
    };
    /**
     * 编辑器
    **/
    owner.ueditor = function ($el, config) {
        var defaultConfig = {
            option:{},
            callback:{}
        };
        config = $.extend(defaultConfig, config);
        var idName = $($el).attr('id');
        Do.ready('ueditor', function () {
            // UEDITOR_CONFIG.UEDITOR_HOME_URL = '/js/lib/ueditor/';
            UEDITOR_CONFIG.UEDITOR_HOME_URL = '/static/js/lib/ueditor/';
            //编辑器
            var editorConfig = {
                serverUrl:webRoot + 'upload/index2.html', //图片上传接口
                'elementPathEnabled': false,
                'initialFrameHeight': "380",
                'initialFrameWidth': "100%",
                'focus': false,
                'maximumWords': 9999999999999,
                'autoClearinitialContent': false,
                'toolbars': [['fullscreen', 'source',  '|', 'bold', 'italic', 'underline', 'strikethrough', 'forecolor', 'backcolor', '|',
                    'justifyleft', 'justifycenter', 'justifyright', '|', 'insertorderedlist', 'insertunorderedlist', 'blockquote', 'emotion','insertimage',
                    'link', 'removeformat', '|', 'rowspacingtop', 'rowspacingbottom', 'lineheight', 'indent', 'paragraph', 'fontsize', '|',
                    'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol',
                    'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|', 'map', 'print', 'drafts']],
                "autoHeightEnabled": false,//自动增高
                "autoFloatEnabled": false,

            }
            editorConfig = $.extend(editorConfig, config.option);
            var editor = UE.getEditor(idName, editorConfig);
            if ($.isFunction(config.callback)) {
                callback(editor);
            }
        });
    };
    /**
     * 边栏缩放
    **/
    owner.aside = function ($el, config) {
        var $hide = $($el).find('[data-hide]'), $show = $($el).find('[data-show]');
        $($hide).click(function(){
            var onActive = $(this).attr('class');
            if(onActive == 'on'){
                $(this).removeClass('on');
                $(this).parent().addClass('isHide');
                $($hide).parent().css('width','50px');
                $($show).css('left','50px');
            }else{
                $(this).addClass('on');
                $(this).parent().removeClass('isHide');
                $($hide).parent().css('width','180px');
                $($show).css('left','180px');
            }
        });
    };
    /**
     * 删除数据
    **/
    owner.formDel = function(data,obj){
        vueInfo('loading','执行中');
        $.post(webRoot+ webControl + '/'+obj.event ? obj.event :'del',{id: data.id,table:data.table},function(res){
            vueInfo('cancel','')
            if(res.code == '1'){
                if (res.url) {
                    vueInfo('success',res.msg);
                    obj.del();
                } else {
                    vueInfo('success',res.msg);
                }
            }else{
                vueInfo('error',res.msg);
            }
        },'JSON');
    };
    owner.dropdown = function ($el, config) {
        var $dropdown = $($el).find('[data-dropdown]'),$showClass = $dropdown.find('.dropdown-content'),$parent = config.parent;
        var defaultConfig = {};
        config = $.extend(defaultConfig, config);
        Do('superslide', function () {
            var html = "<script>jQuery('"+$parent+"').slide("+config.option+");</script>";
            $('body').append(html);
        });
    };
    /**
     * 提示操作
    **/
    owner.confirm = function ($el, config) {
        $($el).click(function(){
            dialogs.confirm({
                content:'是否确定执行该操作?',
                callback:function(result){
                    dialogs.loading({
                        content:'执行中...',
                        time:30000
                    });
                    $.post(config.url,{id: config.id},function(json){
                        dialogs.hide();
                        if(json.code){
                            msgbox.success({
                                content : json.msg
                            });
                        }else{
                            msgbox.error({
                                content : json.msg
                            });
                        }
                        if(json.url){
                            setTimeout(function () {
                                window.location.href = json.url;
                            },3000);
                        }
                    });
                }
            });
        });
    };
    /**
     * 侧边栏目
    **/
    owner.asideMenu = function ($el, config) {
        Do('superslide', function () {
            var html = '',list = menuList;
            html += '<div class="left_menuList">';
            if(list.length > 0){
                for(var i in list){
                    if(list[i].children.length > 0){
                        var subList = list[i].children;
                        html += '<h3><a href="javascript:;"><i class="iconfont">'+list[i].icon+'</i><span>'+list[i].name+'</span><em></em></a></h3><ul class="sub asideMenu">';
                        for(var j in subList){
                            html += '<li><a href="'+subList[j].url+'" target="main"><span>'+subList[j].name+'</span></a></li>';
                        }
                        html += '</ul>';
                    }else{
                        html += '<h3><a href="'+list[i].url+'" target="main"><i class="iconfont">'+list[i].icon+'</i><span>'+list[i].name+'</span></h3><ul class="sub asideMenu"></ul></li>';
                    }
                }
            }
            html += '</div><script>jQuery(\'.left_menuList\').slide({titCell:"h3", targetCell:"ul",defaultIndex:0,effect:"slideDown",delayTime:300,trigger:"click"});</script>'
            $(config.submenu).html(html);
        })
    }
    /**
     * 自动处理排序
     */
    owner.blur = function($el,config){
        var defaultConfig = {};
        $($el).blur(function(){
            defaultConfig['value'] = $($el).val();
            defaultConfig['name'] = $($el).attr('name');
            config = $.extend(defaultConfig, config);
            $.post(webRoot+'sorts',config,function(res){
                if(res.code){
                    msgbox.success({
                        content : res.msg
                    });
                }else{
                    msgbox.error({
                        content : res.msg
                    });
                }
            },'JSON');
        });
    };
    /**
     * 选择链接
     */
    owner.selectUrl = function ($el,config) {
        var defaultConfig = {
            val:{}
        };
        config = $.extend(defaultConfig, config);
        $($el).click(function(){
            Do('dialog', function () {
                layer.open({
                    type:2,
                    title: config.title ? config.title : '请选择',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['60%', '80%'],
                    content:config.url
                });
            });
        });
    };
    /**
     * 打开预览图片
     */
    owner.showImg = function ($el,config) {
        var defaultConfig = {
            val:{}
        };
        config = $.extend(defaultConfig, config);
        $($el).click(function(){
            Do('dialog', function () {
                layer.open({
                    type: 1,
                    title: false,
                    closeBtn: 1,
                    area: config.area,
                    skin: 'layui-layer-nobg', //没有背景色
                    shadeClose: true,
                    content: "<div><img src="+config.val+" style='width:100%;display:block;' /></div>"
                });
            });
        });
    };
    /**
     * 复制
     */
    owner.copy = function ($el, config) {
        Do('clip', function () {
            $($el).zclip({
                path:commonPath + 'zclip/ZeroClipboard.swf',
                copy: config.txt,
                afterCopy:function(){
                    msgbox.success({
                        content : '复制成功！'
                    });
                }
            });
        });
    };

    /**
     * 上传
     * @param $el
     * @param config
     */
    owner.upload = function ($el, config) {
        var defaultConfig = {
            url: webRoot + 'upload/alone.html',
            type: '*',
            size: 0,
            multi: true,
            resize: {},
            target: '',
            preview:'',
            file:'0',
            callback: ''
        };
        config = $.extend(defaultConfig, config);
        Do('upload', function () {
            var uploader = new plupload.Uploader({
                runtimes: 'html5,html4',
                browse_button: $($el).get(0),
                url: config.url + '?type='+config.file,
                filters: {
                    mime_types: [
                        {title: "指定文件", extensions: config.type}
                    ]
                },
                max_file_size: config.size,
                multipart: config.multi,
                resize: config.resize,
                init: {
                    PostInit: function () {
                        //初始化
                    },
                    FilesAdded: function (up, files) {
                        //添加文件
                        $($el).attr('disabled', true).append(' <span class="prs">[<strong>0%</strong>]</span>');
                        uploader.start();
                    },
                    UploadProgress: function (up, file) {
                        //上传进度
                        $($el).find('span').text(file.percent + '%');
                    },
                    FileUploaded: function (up, file, response) {
                        //文件上传完毕
                        var data = JSON.parse(response.response);
                        // var data = response.response;
                        // console.log(data);
                        if (!data.status) {
                            alert(data.info);
                            return;
                        }
                        //赋值地址
                        if (config.target) {
                            $(config.target).val(data.data.url);
                        }
                        if(config.file == '2'){
                            if (config.preview) {
                                var html = '<img src="/static/images/txt.jpg" style="width:105px;height:105px;" /><p>'+data.data.name+'</p>';
                                $(config.preview).html(html).show();
                            }
                        }else{
                            if (config.preview) {
                                $(config.preview).attr('src',data.data.url).show();
                            }
                        }
                        //设置回调
                        if (typeof config.callback === 'function') {
                            config.callback(data.data);
                        }
                        if (typeof config.callback === 'string' && config.callback) {
                            window[config.callback](data.data);
                        }
                    },
                    Error: function (up, err) {
                        //错误信息
                        $($el).attr('disabled', false).find('span').remove();
                        alert(err.message);
                    },
                    UploadComplete: function (up, num) {
                        //队列上传完毕
                        $($el).attr('disabled', false).find('span').remove();
                    }
                }
            });
            uploader.init();
        });
    };

}(jQuery, window.form = {}));

/**
 * 上传处理
 */
(function ($, owner) {

    /**
     * 单图片上传
    **/
    owner.image = function ($el, config) {
        var defaultConfig = {
            option:{},
            callback:{}
        };
        config = $.extend(defaultConfig, config);
        var idName = config.id;
        Do.ready('ueditor', function () {
            UEDITOR_CONFIG.UEDITOR_HOME_URL = '/static/js/lib/ueditor/';
            var editorConfig = {
                serverUrl:webRoot + 'upload/index.html', //图片上传接口
            };
            editorConfig = $.extend(editorConfig, config.option);
            var editor = UE.getEditor(idName, editorConfig);
            editor.ready(function(){
                editor.hide();
                editor.addListener('beforeinsertimage',function(t,arg){
                    console.log(arg);
                    $("#"+config.target).val(arg[0].src);
                    $("#"+config.preview).attr("src", arg[0].src);
                });
            });
            $($el).click(function(){
                var myImage = editor.getDialog("insertimage");
                myImage.open();
            });
        });
    }
    /**
     * 多图片选择上传
    **/
    owner.photo = function ($el, config) {
        var defaultConfig = {
            imgWarp: '',
            imgName: '',
            imgList: {}
        };
        config = $.extend(defaultConfig, config);
        var idName = config.id;
        Do.ready('ueditor','tpl','sorttable', function () {
            // 多图模版
            var tpl = '<li>' +
                '<a href="{{ d.data.src }}" target="_blank"><img src="{{ d.data.src }}"></a>' +
                '<div class="info">' +
                '<a class="del">x</a>' +
                '</div>' +
                '<input type="hidden" name="{{ d.name }}[]" value="{{ d.data.src }}">' +
                '</li>';

            $(config.imgWarp).on('click', '.del', function () {
                $(this).parents('li').remove();
            });
            UEDITOR_CONFIG.UEDITOR_HOME_URL = '/static/js/lib/ueditor/';
            var editorConfig = {
                serverUrl:webRoot + 'upload/index.html', //图片上传接口
            };
            editorConfig = $.extend(editorConfig, config.option);
            var editor = UE.getEditor(idName, editorConfig);
            editor.ready(function(){
                editor.hide();
                editor.addListener('beforeinsertimage',function(t,arg){
                    $.each(arg, function (index, item) {
                        laytpl(tpl).render({name: config.imgName, data: item}, function (html) {
                            $(config.imgWarp).append(html);
                        });
                    });
                });
            });
            if (config.imgList) {
                $.each(config.imgList, function (index, item) {
                    laytpl(tpl).render({name: config.imgName, data: item}, function (html) {
                        $(config.imgWarp).append(html);
                    });
                });
            }
            $($el).click(function(){
                var myImage = editor.getDialog("insertimage");
                myImage.open();
            });
        });
    }
    /**
     * 单文件上传
    **/
    owner.file = function ($el, config) {

    }
    /**
     * 多文件上传
    **/
    owner.files = function ($el, config) {

    }
    /**
     * 上传音频文件
    **/
    owner.audio = function ($el, config) {

    }
    /**
     * 上传视频文件
    **/
    owner.video = function ($el, config) {

    }

}(jQuery, window.upload = {}));

/**
 * 表格处理
 */
(function ($, owner) {
    owner.bind = function ($el, config) {
        var defaultConfig = {}, config = $.extend(defaultConfig, config);
        var $table = $($el).find('[data-table]'), $del = $table.find('[data-del]');
        //更改状态
        $table.on('click', '[data-status]', function () {
            var data = $(this).data(), $obj = this;
            console.log(data)
            if (data.status == 1) {
                var status = 0;
                var css = 'text-danger';
            } else {
                var status = 1;
                var css = 'text-success';
            }
            $.post(webRoot + rootUrl + 'setStatus',{id: data.id,name: data.name,table: data.table,field:data.field,status: data.status},function(res){
                if(res.code == '1'){
                    notify.success({
                        content : res.msg
                    });
                    $($obj).removeClass('text-success text-danger').addClass(css).data('status', status);
                }
            },'JSON');

        });
        //全选
        $table.find('[data-all]').click(function () {
             $table.find("[name='ids[]']").each(function () {
                if ($(this).prop("checked")) {
                    // $(this).iCheck('uncheck');
                    $(this).prop("checked",false);
                    $table.find('[data-all]').text('全选');
                } else {
                    // $(this).iCheck('check');
                    $(this).prop("checked",true);
                    $table.find('[data-all]').text('取消');
                }
            });
        });
        // 删除操作
        $del.click(function () {
            var data = $(this).data(), $tr = $(this).parents('tr');
             dialogs.confirm({
                content: data.tips ? data.tips : '是否确认删除?',
                callback:function(result){
                    dialogs.loading({
                        content:'执行中...',
                        time:300000000000
                    });
                    $.post(webRoot+ webControl + '/del',{id: data.id,table:data.table},function(res){
                        dialogs.hide();
                        if(res.code == '1'){
                            if (res.url) {
                                msgbox.success({
                                    content : res.msg
                                });
                                setTimeout(function () {
                                    window.location.href = res.url;
                                },2000);
                                $tr.remove();
                            } else {
                                msgbox.success({
                                    content : res.msg
                                });
                            }
                        }else{
                            msgbox.error({
                                content : res.msg
                            });
                        }
                    },'JSON');
                }
            });
        });
        // 其它操作
        $table.find('[data-confirm]').click(function(){
            var data = $(this).data();
            dialogs.confirm({
                content: data.tips ? data.tips : '确认执行该操作吗?',
                callback:function(result){
                    if(result){
                        dialogs.loading();
                        $.post(data.url,{id: data.id,val:data.val},function(res){
                            dialogs.hide();
                            if(res.code == '1'){
                                notify.success({
                                    content : res.msg
                                });
                            }else{
                                notify.error({
                                    content : res.msg
                                });
                            }
                            if (res.url) {
                                setTimeout(function () {
                                    window.location.href = res.url;
                                },2000);
                            }
                        },'JSON');
                    }
                }
            });
        });
        //批量操作
        var $batch = $($el).find('[data-batch]');
        $batch.click(function () {
            event.stopPropagation();
            var data = {}, ids = [];
            $.each($batch.serializeArray(), function (index, vo) {
                data[vo.name] = vo.value;
            });
            $table.find('input[type=checkbox]:checked').each(function () {
                ids.push($(this).val());
            });
            if(ids.length == 0){
                msgbox.error({
                    content : '请选择至少一条数据'
                });
                return false;
            }
            var postUrl = $(this).attr('action');
            data['ids'] = ids.join(',');
            dialogs.confirm({
                title:"操作提示",
                content:'确认执行该操作吗?',
                callback:function(result){
                    if(result){
                        dialogs.loading();
                        $.post(postUrl,{data:data},function(res){
                            dialogs.hide();
                            if(res.code == '1'){
                                notify.success({
                                    content : res.msg
                                });
                                setTimeout(function () {
                                    location.reload();
                                },2000);
                            }else{
                                notify.error({
                                    content : res.msg
                                });
                            }
                        },'JSON');
                        return false;
                    }
                }
            });
        });
    }
}(jQuery, window.table = {}));

/**
 * 弹窗组件
 */
(function ($, owner) {

    /**
     * 产品选择
    **/
    owner.goods = function($el,config){
        var linkUrl = webRoot + "api/getgoods.html";
        var defaultConfig = {
            val:{}
        };
        config = $.extend(defaultConfig, config);
        $($el).click(function(){
            Do('dialog', function () {
                layer.open({
                    type:2,
                    title: config.title ? config.title : '商品选择',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['60%', '80%'],
                    content:linkUrl
                });
            });
        });
    }
    /**
     * 设置奖品
    **/
    owner.draw = function($el,config){
        var linkUrl = webRoot + "api/setdraw.html";
        var defaultConfig = {
            val:{}
        };
        config = $.extend(defaultConfig, config);
        $($el).click(function(){
            Do('dialog', function () {
                layer.open({
                    type:2,
                    title: config.title ? config.title : '设置奖品',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['60%', '80%'],
                    content:linkUrl
                });
            });
        });
    }
    /**
     * 优惠券选择
    **/
    owner.coupon = function($el,config){
        var linkUrl = webRoot + "api/getcoupon.html";
        var defaultConfig = {
            val:{}
        };
        config = $.extend(defaultConfig, config);
        $($el).click(function(){
            Do('dialog', function () {
                layer.open({
                    type:2,
                    title: config.title ? config.title : '优惠券选择',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['60%', '80%'],
                    content:linkUrl
                });
            });
        });
    }

    /**
     * 打开窗口
     */
    owner.open = function ($el, config) {
        var title = '';
        if(config.title){
            title = config.title;
        }else{
            title = $($el).text();
        }
        var defaultConfig = {
            type:2,
            title:title,
            shadeClose: false,
            area:[config.width, config.height],
            content:config.url
        };
        config = $.extend(defaultConfig, config);
        if(config.type == '1'){
            config.content = $(config.content);
        }
        $($el).click(function(){
            Do('dialog', function () {
                layer.open(config);
            })
        });
    }
    owner.video = function ($el, config) {
        var defaultConfig = {
            type: 2,
            title: false,
            area: ['630px', '460px'],
            shade: 0.8,
            closeBtn: 1,
            shadeClose: true,
            content:config.url
        };
        config = $.extend(defaultConfig, config);
        if(config.type == '1'){
            config.content = $(config.content);
        }
        $($el).click(function(){
            Do('dialog', function () {
                layer.open(config);
            })
        });
    }
    /**
     * confirm确认提示
     */
    owner.confirm = function (config) {
        var defaultConfig = {
            closeBtn:0,
            content: '确定执行该操作吗1？',
            callback:{}
        };
        config = $.extend(defaultConfig, config);
        Do('dialog', function () {
            layer.confirm(config.content,{closeBtn:0,title:config.title,icon:3},config.callback);
        });
    }
    /**
     * alert
     */
    owner.alert = function ($el, config) {
        var defaultConfig = {
            closeBtn:0,
            content: config.content ? config.content : '默认提示信息',
            icon:10
        };
        config = $.extend(defaultConfig, config);
        Do('dialog', function () {
            layer.alert(config.content,config);
        });
    }
    /**
     * msg
     */
    owner.msg = function ($el, config) {
        var defaultConfig = {
            content:  config.content ? config.content : '默认提示信息',
            time:3000,
            callback:{}
        };
        config = $.extend(defaultConfig, config);
        Do('dialog', function () {
            layer.msg(config.content,config,config.callback);
        });
    }
    owner.hide = function (config) {
        Do('dialog', function () {
            layer.closeAll();
        });
    }
    /**
     * loading
     */
    owner.loading = function (config) {
        var defaultConfig = {
            content: '处理中',
            time: 6000,
            icon:16,
            shade:0.01
        };
        config = $.extend(defaultConfig, config);
        Do('dialog', function () {
            layer.msg(config.content,config);
        });
    }
}(jQuery, window.dialogs = {}));
/**
 * 通知组件
 */
(function ($, owner) {
    owner.success = function (config) {
        var defaultConfig = {
            content: "处理成功",
            time: 6
        };
        config = $.extend(defaultConfig, config, {status: 'success'});
        owner.show(config);
    };
    owner.warning = function (config) {
        var defaultConfig = {
            content: "处理中断",
            time: 6
        };
        config = $.extend(defaultConfig, config, {status: 'warning'});
        owner.show(config);
    };
    owner.error = function (config) {
        var defaultConfig = {
            content: "处理失败",
            time: 6
        };
        config = $.extend(defaultConfig, config, {status: 'error'});
        owner.show(config);
    };
    owner.hide = function (config) {
        ZENG.msgbox._hide();
    };
    owner.show = function (config) {
        Do('notify', function () {
            var status = {
                success: ['ok', '#27ae60'],
                warning: ['warning', '#e0690c'],
                error: ['error', '#dd514c']
            };
            $.amaran({
                theme: 'default ' + status[config.status][0],
                delay: config.time * 1000,
                content: {
                    message: config.content,
                    color: status[config.status][1]
                },
                position:'bottom right',
                outEffect :'slideBottom'

            });
        });
    }
}(jQuery, window.notify = {}));

(function ($, owner) {
    owner.success = function (config) {
        var defaultConfig = {
            content: "处理成功",
            type: 4,
            time:3000,
            callback:{}
        };
        config = $.extend(defaultConfig, config);
        owner.show(config);
    };
    owner.error = function (config) {
        var defaultConfig = {
            content: "处理失败",
            type: 5,
            time:3000,
            callback:{}
        };
        config = $.extend(defaultConfig, config);
        owner.show(config);
    };
    owner.warning = function (config) {
        var defaultConfig = {
            content: "处理中断",
            type: 1,
            time:3000,
            callback:{}
        };
        config = $.extend(defaultConfig, config);
        owner.show(config);
    };
    owner.loading = function (config) {
        var defaultConfig = {
            content: "正在处理中",
            type: 6,
            time:3000
        };
        config = $.extend(defaultConfig, config);
        owner.show(config);
    };
    owner.hide = function (config) {
        ZENG.msgbox._hide();
    };
    owner.show = function (config) {
        Do('msgbox', function () {
            ZENG.msgbox.show(config.content,config.type,config.time);
            if(typeof(config.callback)=="function"){
                setTimeout(config.callback,config.time);
            }
        });
    }
}(jQuery, window.msgbox = {}));

$(function(){
    $("textarea[name=sms_content]").keyup(function(event) {
        var content = $(this).val();
        var leng = content.length;
        count_str($(this),content)
        if(leng > 500){
            alert('短信内容不能超过500')
            count_str($(this),content)
        }
    });

    $("textarea[name=sms_phone]").keyup(function(event) {
        var content = $(this).val();
        var phones = content.split("\n");
        $(".phone_count").text(phones.length)
        if(phones.length > 800){
            alert('号码不能超过800个')
            var con = phones.slice(0,800);
            $(this).val(con.join("\n"))
            $('.phone_count').text(con.length)
        }
    });
})
function count_str(e,con){
    var content = con.substr(0,500);
    e.val(content)
    var leng = content.length;
    $('.str_now').text(leng+'/66');
    var count = Math.ceil(leng/66);
    $('.str_count').text(count)
}

