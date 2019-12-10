function copyPlace(url,path) {
    Do.ready('dialog', function () {
        layer.open({
            type: 1,
            area: ['550px', '245px'],
            title: "复制链接",
            skin: 'layui-layer-demo', //样式类名
            closeBtn: 1, //不显示关闭按钮
            shift: 2,
            content: '<div class="page-pop-copyLink f-c"><div class="erweima"><img src="' + path + '"><span>微信扫一扫立即预览</span></div><div class="lianjie f-c"><p>链接地址</p><div class="input"><input id="urladress" value="' + url + '" type="text"><button class="btn btn-primary urlcopy ml-10"  data-clipboard-action="copy" data-clipboard-target="#urladress">复制</button></div><p class="shuoming2">可将链接复制到您的公众号菜单中</p></div></div>'
        });

        var clipboard = new Clipboard('.urlcopy');
        clipboard.on('success', function (e) {
            console.info('Action:', e.action);
            layer.closeAll();
            layer.msg("复制成功",{icon:1});
            e.clearSelection();
        });
    })
}

function copyPlace2(url) {
    Do.ready('dialog', function () {
        layer.open({
            type: 1,
            area: ['420px', '180px'],
            title: "复制链接",
            skin: 'layui-layer-demo', //样式类名
            closeBtn: 1, //不显示关闭按钮
            shift: 2,
            content: '<div class="pd-20 t-c mt-20"><input id="urladress" style="display:inline-block;width:300px;" class="form-control" value="' + url + '"  style="width:280px" type="text"><button class="btn btn-primary urlcopy ml-10"  data-clipboard-action="copy" data-clipboard-target="#urladress">复制</button></div>'
        });

        var clipboard = new Clipboard('.urlcopy');
        clipboard.on('success', function (e) {
            console.info('Action:', e.action);
            layer.closeAll();
            layer.msg("复制成功",{icon:1});
            e.clearSelection();
        });
    })
}
 