/*
*功能：获取并更新下拉框(若要显示"全部"选项请在后台返回数据添加)
*domId：下拉框id
*_data：访问参数
*_value：下拉框保存值字段
*_text：下拉框显示字段
*_url：获取下拉框数据URL访问地址(返回数据形式:[{key:value,key:value...},{key:value,key:value...}...])
*_selValue：选择值
*/
function GetCombobox(domId, _url, _value, _text, _data, _selValue) {
    $.ajax({
        type: "post",
        url: _url,
        data: _data,
        success: function (result) {
            if (result.data) {
                if (result.data.length > 0) {
                    var html = "";
                    $.each(result.data, function (i, data) {
                        if (data[_value] == _selValue) {
                            html += "<option value=" + data[_value] + " selected>" + data[_text] + "</option>";
                        }
                        else {
                            html += "<option value=" + data[_value] + ">" + data[_text] + "</option>";
                        }
                    });
                    $("#" + domId).append(html);
                }
            }
            else if (result.length > 0) {
                var html = "";
                $.each(result, function (i, data) {
                    if (data[_value] == _selValue) {
                        html += "<option value=" + data[_value] + " selected>" + data[_text] + "</option>";
                    }
                    else {
                        html += "<option value=" + data[_value] + ">" + data[_text] + "</option>";
                    }
                });
                $("#" + domId).append(html);
            }
        },
        error: function (result) {
            alert(result.responseText);
        }
    });
}
/*功能：获取并更新下拉框(默认显示"全部"选项)
***domAId：下拉框A的id                            ******domBId：下拉框B的id
***_url：获取下拉框数据URL访问地址(返回数据形式:[{key:value,key:value...},{key:value,key:value...}...])
***_value：下拉框保存值字段                       ******_text：下拉框显示字段
***_data：访问参数
***idKey：需要通过下拉框A获取到的值的参数键(默认为id)
***nullValue：为空时显示的选项的值,默认为"-1"     ******nullText：为空时显示的选项的文本,默认为"全部"
***_selValue：选择值(可为空)
***domANullValue：下拉框为空(未选)时的值(默认为"-1"或"")
***isHideOptionAll：是否隐藏下拉框B中的"全部"选项(有值且不为false时隐藏)
*/
function GetComboboxTwo(domAId, domBId, _url, _value, _text, _data, idKey, nullValue, nullText, _selValue, domANullValue, isHideOptionAll) {
    if (!idKey) {
        nullValue = "id";
    }
    if (!nullValue) {
        nullValue = "-1";
    }
    if (!nullText) {
        nullText = "全部";
    }

    var domAIdValue = $('#' + domAId).val();
    var domB = $('#' + domBId);

    $('#' + domAId).one("change", function () {
        GetComboboxTwo(domAId, domBId, _url, _value, _text, _data, idKey, nullValue, nullText, _selValue, domANullValue, isHideOptionAll);
    });

    domB.html("");//赋值之前先清空
    domB.append("<option value='" + nullValue + "' selected>" + nullText + "</option>");//赋值之前先清空
    if (isHideOptionAll) {
        domB.html("");//清空
    }

    if (domANullValue || domANullValue == "") {
        if (domAIdValue == domANullValue) return;
    }
    else {
        if (domAIdValue == -1 || domAIdValue == "") return;
    }
    _data[idKey] = domAIdValue;
    GetCombobox(domBId, _url, _value, _text, _data, _selValue);
}