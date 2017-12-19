// 设置一个默认的提示消息
var layer = layer ? layer : {};
    layer.syserror = function(){
    layer.alert('系统错误, 请联系运维人员或开发人处理!', {icon: 5});
}

// 设置默认的ajax请求参数
$.ajaxSetup({
    type: 'post',
    dataType: 'json',
    global: true,
    success: function(data){
        if(!this.noneCheck()){
            if(data && data.result === 1){
                this.succeeded(data);
            }else{
                if(!this.failed(data)) layer.alert(data.message, {icon: 2});
            }
        }
    },
    error: function(e){
        layer.syserror();
    },
    beforeSend: function(XHR){
        this.loaderLayer = layer.load(2);
        this._beforeSend(XHR);
    },
    _beforeSend: function(XHR){

    },
    complete: function(XHR, TS){
        layer.close(this.loaderLayer);
        this._complete(XHR, TS);
    },
    _complete: function(XHR, TS){

    },

    // 当返回的数据中的result为1时回调
    succeeded: function(data){
        return true;
    },
    // 当返回的数据中的result不为1时回调
    failed: function(data){
        return false;
    },
    // 不自动检查, 直接回调; 只有return true时才不会自动检查
    noneCheck: function(data){
        return false;
    }
});

$(function(){
    // 将所有class包含fromUnixtimestamp的标签内的时间戳改成yyyy-MM-dd HH:mm:ss的时间
    $(".fromUnixtimestamp").each(function(index, item){
        try{
            var date = new Date($(item).html()*1000);
            $(item).html(getFormattedTime(date));
        }catch(e){
            log.error(e);
        }
    });
});

// 获取格式化后的时间
function getFormattedTime(date){
    date = date ? date : new Date();
    return date.getFullYear()+"-"+(date.getMonth() < 9 ? '0' : '')+(date.getMonth()+1)+"-"+(date.getDate() < 10 ? '0' : '')+date.getDate()+" "+(date.getHours() < 10 ? '0' : '')+date.getHours()+":"+(date.getMinutes() < 10 ? '0' : '')+date.getMinutes()+":"+(date.getSeconds() < 10 ? '0' : '')+date.getSeconds();
}

/**
* 
* @param input标签 input 文件input标签
* @param Funtion callback 成功获取信息后的回调
*/
function uploadFile(input, callback){
    var formData = new FormData();
    formData.append("file", $(input)[0].files[0]);
    $.ajax({
        url: "__ROOT__/index.php/index/index/upload",
        data: formData,
        processData: false, 
        contentType: false,
        succeeded: function(data){
            if(callback instanceof Function) callback('__ROOT__' + data.data);
        }
    });
}

// 获取cookie
function getCookie(name){
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if(arr = document.cookie.match(reg)) return unescape(arr[2]);
    else return null;
}

// 调用jQuery的fadeOut()后调用remove()
function removeout(item, type, callback){
    $(item).fadeOut(type ? type : 'normal', function(){
        if(callback !== null && callback instanceof Function) callback();
        $(item).remove();
    });
}

// 自定制typeahead事项
function initTypeahead(input, template, emptyTemplate, source, onClick){
    $.typeahead({
        input: input,
        minLength: 1,
        maxItem: 20,
        order: "asc",
        delay: 300,
        dynamic: true,
        cancelButton: false,
        template: template,
        emptyTemplate: emptyTemplate,
        source: source,
        callback: {
            onResult: function(node, query, result, resultCount, resultCountPerGroup){
                if(query != '') $(".typeahead__result").show();
            },
            onLayoutBuiltBefore: function(node, query, result, resultHtmlList){
                if(result.length > 0){
                    resultHtmlList.find("li").each(function(index, item){
                        $(item).removeAttr("disabled");
                        $(item).append(`<data style="display: none;">`+JSON.stringify(result[index])+`</data>`);
                        $(item).bind({
                            click: function(){
                                // 格式化数据
                                var data = JSON.parse($(this).children("data").html());
                                // 设置数据
                                onClick(node, data, query);
                                // 隐藏结果栏
                                $(".typeahead__result").hide();
                            }
                        });
                    });
                }
                return resultHtmlList;
            }
        }
    });

    $(input).bind({
        click: function(){
            if($(this).val() != "")
                $(this).trigger("input.typeahead");
        }
    });
}

function base64Img2Blob(code){
    var parts = code.split(';base64,');
    var contentType = parts[0].split(':')[1];
    var raw = window.atob(parts[1]);
    var rawLength = raw.length;

    var uInt8Array = new Uint8Array(rawLength);

    for (var i = 0; i < rawLength; ++i) {
        uInt8Array[i] = raw.charCodeAt(i);
    }

    return new Blob([uInt8Array], {type: contentType}); 
}