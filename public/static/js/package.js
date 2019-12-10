/**
 * 初始化类库
 */
(function (win, doc) {
    /**
     * 设置包路径
     */
    var jsSelf = (function () {
        var files = doc.getElementsByTagName('script');
        return files[files.length - 1];
    })();
    window.packagePath = jsSelf.getAttribute('data-path');
    window.rootUrl = jsSelf.getAttribute('data-root');
    window.roleName = jsSelf.getAttribute('data-role');
    window.tplPath = jsSelf.getAttribute('data-tpl');
    window.commnSrc = jsSelf.getAttribute('data-src');
    window.commonPath = packagePath + 'lib/';

    window.mobile = false;
    /**
     * 公共类
     */
    Do.add('common', {
        path: commnSrc + '.js?v=1.1',
        type : 'js'
    });
    Do.add('global', {
        path: packagePath + 'global.js?v=1.1',
        type : 'js'
    });
    Do.add('jquery', {
        path: packagePath + 'jquery.js?v=1.1',
        type : 'js'
    });

    /**
     * 下拉增强
     */
    Do.add('select2Css', {
        path: commonPath + 'select2/select2.css',
        type: 'css'
    });
    Do.add('select2Src', {
        path: commonPath + 'select2/select2.full.min.js'
    });
    Do.add('select2', {
        path: commonPath + 'select2/i18n/zh-CN.js',
        requires: ['select2Css', 'select2Src']
    });
    /**
     * 时间日期
     */
    Do.add('dateCss', {
        path: commonPath + 'daterangepicker/daterangepicker.css',
        type: 'css'
    });
    Do.add('datemoment', {
        path: commonPath + 'daterangepicker/moment.min.js?v=1.1',
        type : 'js'
    });
    Do.add('date', {
        path: commonPath + 'daterangepicker/daterangepicker.js?v=1.1',
        type : 'js',
        requires: ['dateCss','datemoment']
    });
    Do.add('timeCss', {
        path: commonPath + 'datetimepicker/jquery.datetimepicker.css',
        type: 'css'
    });
    Do.add('time', {
        path: commonPath + 'datetimepicker/jquery.datetimepicker.min.js',
        requires: ['timeCss']
    });
    Do.add('tpl', {
        path: commonPath + 'tpl/laytpl.js?v=1.1',
        type : 'js'
    });
    /**
     * 表单验证
     */
    Do.add('form', {
        path: commonPath + 'form/jquery.form.js'
    });
    /**
     * 进度条加载
     */
    Do.add('nprogressCss', {
        path: commonPath + 'nprogress/nprogress.css',
        type: 'css'
    });
    Do.add('nprogress', {
        path: commonPath + 'nprogress/nprogress.js',
        requires: ['nprogressCss']
    });
    /**
     * 弹窗组件
     */
    Do.add('dialogCss', {
        path: commonPath + 'dialog/theme/default/layer.css',
        type: 'css'
    });
    Do.add('dialog', {
        path: commonPath + 'dialog/layer.js',
        requires: ['dialogCss']
    });
    /**
     * 手机端弹窗组件
     */
    Do.add('dialog_mCss', {
        path: commonPath + 'dialog_m/need/layer.css',
        type: 'css'
    });
    Do.add('dialog_m', {
        path: commonPath + 'dialog_m/layer.js',
        requires: ['dialog_mCss']
    });
    /**
     * 文本标签组件
     */
    Do.add('tagsCss', {
        path: commonPath + 'tags/amazeui.tagsinput.css',
        type: 'css'
    });
    Do.add('tag', {
        path: commonPath + 'tags/amazeui.tagsinput.min.js',
        requires: ['tagsCss']
    });
    /**
     * layui时间日期插件
     */
    Do.add('ltimeCss', {
        path: commonPath + 'laydate/theme/default/laydate.css',
        type: 'css'
    });
    Do.add('ltime', {
        path: commonPath + 'laydate/laydate.js',
        requires: ['ltimeCss']
    });
    /**
     * 编辑器
     */
    Do.add('ueditorConfig', {
        path: commonPath + '/ueditor/ueditor.config.js'
    });
    Do.add('ueditor', {
        path: commonPath + '/ueditor/ueditor.all.min.js',
        requires: ['ueditorConfig']
    });
    /**
     * 通知
     */
    Do.add('notifyCss', {
        path: commonPath + 'notify/amaran.min.css',
        type: 'css'
    });
    Do.add('notify', {
        path: commonPath + 'notify/jquery.amaran.min.js',
        requires: ['notifyCss']
    });
    Do.add('toasterCss', {
        path: commonPath + 'toaster/toast.style.css',
        type: 'css'
    });
    Do.add('toaster', {
        path: commonPath + 'toaster/toast.script.js',
        requires: ['toasterCss']
    });
    /**
     * 颜色选择
    **/
    Do.add('color', {
        path: commonPath + 'colorpicker/iColor-min.js?v=1.1',
        type : 'js',
        requires: ['colorCss']
    });
    Do.add('colorCss', {
        path: commonPath + 'colorpicker/iColor-min.css',
        type: 'css'
    });
    Do.add('echarts', {
        path: commonPath + 'echarts/echarts.common.min.js',
    });
    /**
     * 文件上传(百度插件)
    **/
    Do.add('uploaderCss', {
        path: commonPath + 'webuploader/webuploader.css',
        type: 'css'
    });
    Do.add('uploader', {
        path: commonPath + 'webuploader/webuploader.min.js',
        requires: ['uploaderCss']
    });
    /**
     * 上传
     */
    Do.add('uploadSrc', {
        path: commonPath + 'upload/plupload.full.min.js'
    });
    Do.add('upload', {
        path: commonPath + 'upload/zh_CN.js',
        requires: ['uploadSrc']
    });
    /**
     * checkbox、radio美化
    **/
    Do.add('icheckCss', {
        path: commonPath + 'icheck/skins/minimal/blue.css',
        type: 'css'
    });
    Do.add('icheck', {
        path: commonPath + 'icheck/icheck.min.js',
        requires: ['icheckCss']
    });
    /**
     * checkbox、radio美化2
    **/
    Do.add('labelCss', {
        path: commonPath + 'labelauty/jquery-labelauty.css',
        type: 'css'
    });
    Do.add('labelauty', {
        path: commonPath + 'labelauty/jquery-labelauty.js',
        requires: ['labelCss']
    });
    /**
     * msgBox
    **/
    Do.add('msgboxCss', {
        path: commonPath + 'msgbox/msgbox.css',
        type: 'css'
    });
    Do.add('msgbox', {
        path: commonPath + 'msgbox/msgbox.js',
        requires: ['msgboxCss']
    });
    /**
     * msgBox
    **/
    Do.add('tipsCss', {
        path: commonPath + 'tipso/tipso.css',
        type: 'css'
    });
    Do.add('tips', {
        path: commonPath + 'tipso/tipso.js',
        requires: ['tipsCss']
    });
    Do.add('sorttable', {
        path: commonPath + 'sortable/jquery-sortable-lists.min.js',
    });
    Do.add('webtipsCss', {
        path: commonPath + 'webtips/jquery.webui-popover.css',
        type: 'css'
    });
    Do.add('webtips', {
        path: commonPath + 'webtips/jquery.webui-popover.js',
        requires: ['webtipsCss']
    });
    /**
     * 上传类
    **/
    Do.add('webuploaderSrc', {
        path: commonPath + 'webuploader/webuploader.css',
        type: 'css'
    });
    Do.add('webuploader', {
        path: commonPath + 'webuploader/webuploader.min.js',
        requires: ['uploadSrc']
    });
    Do.add('underscore', {
        path: commonPath + 'underscore-min.js',
    });
    Do.add('fileUploader', {
        path: commonPath + 'fileUploader.js',
    });
    Do.add('jquery.jplayer', {
        path: commonPath + 'jplayer/jquery.jplayer.min.js',
    });
    /**
     * 网页基础特效
    **/
    Do.add('superslide', {
        path: commonPath + 'slide/SuperSlide.js',
        requires: ['tipsCss']
    });
    /**
     * 复制
     */
    Do.add('clip', {
        path: commonPath + 'zclip/jquery.zclip.min.js'
    });


})(window, document);