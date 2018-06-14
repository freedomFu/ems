"use strict";
layui.use(['form', 'jquery', 'layer'], function () {
    var layer = layui.layer
        , form = layui.form
        , $ = layui.$;
    var pass_layer;
    $("#password-btn").click(function () {//修改密码
        pass_layer=layer.open({
            type: 1,
            title: '修改密码',
            content: $('#passwordTp').html()
        });
    });
    form.on('submit(password)', function (data) {
        if (data.field.new_pass != data.field.confirm_pass) {
            layer.msg('两次密码输入不同！', {icon: 5});
            return false;
        }else{
            return beauty_ajax("index/ex_pass", data.field, function(){
                layer.close(pass_layer);
                window.location.href="Login/index"; 
            });
        }
    });
});
function beauty_ajax(url,data,success_func) {//ajax表单提交
    var $ = layui.$
        , layer = layui.layer;
    var submitting = layer.msg('正在提交', {
        icon: 16
        , shade: 0.1
        , time: 0
    });
    console.log(data);          //打印即将发送的数据
    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (data) {
            console.log(data);  //打印接受到的数据
            data = JSON.parse(data);
            if (data.code === 0 || data.status === 1) {
                layer.close(submitting);
                layer.msg('提交成功', {
                    icon: 1
                    , shade: 0.1
                    , time: 1000
                })
                if(success_func !== undefined){
                    success_func();
                }
            } else {
                if(data.msg!=""){
                    layer.msg(data.msg, {
                        icon: 2
                        , shade: 0.1
                        , time: 2000
                    });
                }else{
                    layer.msg('未知错误', {
                        icon: 2
                        , shade: 0.1
                        , time: 2000
                    });
                } 
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            layer.msg(XMLHttpRequest.status + '提交失败', {
                icon: 2
                , shade: 0.1
                , time: 2000
            })
        },
        complete: function (XMLHttpRequest, textStatus) {
            this;
        }
    });
    return false;
};