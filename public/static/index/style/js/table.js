layui.use(['element','jquery','form','table','layer'], function(){
    var element = layui.element,
        $ = layui.$,
        table=layui.table,
        layer = layui.layer;
    layer.open({
        content: rule
        ,skin: 'layui-layer-molv' //样式类名
        ,closeBtn: 0
        ,title :'该实验室规章制度'
        ,area: '760px'
    });
    table.render({
        elem:'#reservation',
        id: 'reservation',
        skin: 'col', //表格风格
        page: false, //是否显示分页
        cols:[[
            {field:'period',title:'课时',width:160},
            {field:'mon',title:'周一',width:162},
            {field:'tues',title:'周二',width:162},
            {field:'wed',title:'周三',width:162},
            {field:'thur',title:'周四',width:162},
            {field:'fri',title:'周五',width:162},
            {field:'sat',title:'周六',width:162}
        ]],
        data:[{
            'period':'第一大节',
        },{
            'period':'第二大节',
        },{
            'period':'第三大节',
        },{
            'period':'第四大节',
        },{
            'period':'第五大节',
        }]
    });
    $("td[data-field!='period'] div").text("可预约").css({'color':'#5FB878'});
    $("td[data-field!='period']").attr('data-reservation','true').css({'cursor':'pointer'});

    get_table("../Display/index");//first load


});
function get_table(url,post_data) {
    var $ = layui.$
        , form = layui.form
        , table = layui.table
        , layer = layui.layer;
    var lab_id = window.location.href.split('=')[1];
    if(post_data === undefined){
        var post_data = {}
        post_data.ids=lab_id;
    }
    $("table td").unbind();
    // console.log(post_data);
    $.ajax({
        url: url,
        type: "get",
        data: post_data,
        dataType: "json",
        success: function (data) {
            var _data = data.data;
            // console.log(_data)
            var fday = data.fday;
            var week = data.termweek;
            $(".week-select option[value=" + week + "]").attr('selected','true');
            if(table_identity != 'admin'){
                if(post_data['weeks'] == undefined){
                    $(".week-select option[value=" + week + "]").prevAll().remove();
                }
            }
            form.render('select');
            form.on('select', function (data) {
                click_week = data.value;
                var click_data={};
                click_data.ids=lab_id;
                click_data.weeks=click_week;
                get_table(url, click_data);
            });
            //周数显示
            var ftime = new Date(fday).getTime();
            var cur_week_fday = ftime + (week-1)*7*24*60*60*1000;
            var cur_week_lday = ftime + week * 7 * 24 * 60 * 60 * 1000 - 172800000;
            var _cur_week_fday = new Date(cur_week_fday).Format("MM月dd日");
            var _cur_week_lday = new Date(cur_week_lday).Format("MM月dd日");
            var week_date = _cur_week_fday + " 至 " + _cur_week_lday;
            $("#week-date").text(week_date);
            for(var i=0;i<_data.length;i++){//表格数据的处理
                var _data_tr=_data[i];
                for(var key in _data_tr){
                    (function () {
                        var available=_data_tr[key][0]['可用设备数'];
                        var isAllow=_data_tr[key][0]['isAllow'];
                        if(available=='0'){
                            $("tr[data-index=" + i + "] td[data-field=" + key + "]").attr('data-reservation','false').css({'cursor':'not-allowed','background-color':'#d2d2d2'});
                            $("tr[data-index=" + i + "] td[data-field=" + key + "] div").text("不可预约或已满").css({'color':'red'})
                        }else if(isAllow=='1' && available!='0'){
                            $("tr[data-index=" + i + "] td[data-field=" + key + "]").attr('data-reservation','true').css({'cursor':'pointer','background-color':'#dddddd'});
                            $("tr[data-index=" + i + "] td[data-field=" + key + "] div").text("已有通过预约，未满").css({'color':'#FF5722'})
                        }else if(isAllow=='0' && available!='0'){
                            $("tr[data-index=" + i + "] td[data-field=" + key + "]").attr('data-reservation','true').css({'cursor':'pointer','background-color':'#e2e2e2'});
                            $("tr[data-index=" + i + "] td[data-field=" + key + "] div").text("已有预约，尚未通过").css({'color':'#5E0CED'})
                        }else{
                            $("tr[data-index=" + i + "] td[data-field=" + key + "]").attr('data-reservation','true').css({'cursor':'pointer','background-color':'inherit'});
                            $("tr[data-index=" + i + "] td[data-field=" + key + "] div").text("可预约").css({'color':'#5FB878'})
                        }
                        var tip="";
                        for(var key_detail in _data_tr[key][0]){
                            if(key_detail != 'isAllow'){
                                var single=key_detail+':'+_data_tr[key][0][key_detail];
                                tip=tip+single+'<br>';
                            }
                        }
                        //tootip
                        $("tr[data-index=" + i + "] td[data-field=" + key + "]").on('mouseover mouseout mousemove',function(event) {
                            var left = event.pageX, top = event.pageY;
                            var type = event.originalEvent.type;
                            if (type == 'mouseover') {
                                if (tip != null && tip != "") {
                                    var showEle = $('<div></div>', {html: tip, class: 'title-box'});
                                    showEle.appendTo('body');
                                }
                            } else if (type == 'mouseout') {
                                $('.title-box').remove();
                            } else if (type == 'mousemove') {
                                var title_box = $('.title-box');
                                var win = $(window);
                                var _top=0,_left=0;
                                if (left + title_box.width() + 20 > win.width()) {
                                    _top= top + 15,
                                        _left= left - title_box.width() - 15
                                } else if (top + title_box.height() + 20 > win.height()) {
                                    _left= left + 15,
                                        _top= top - title_box.height() - 15
                                } else {
                                    _top= top + 15,
                                        _left= left + 15
                                }
                                title_box.css({
                                    top:_top,
                                    left:_left
                                })
                            }
                        });
                        //cell click
                        $("td[data-field!='period']").on('click',function () {
                            var _this=$(this);
                            if(_this.attr('data-reservation')==='false'){
                                return
                            }else{
                                var labs_id = window.location.href.split('=')[1];
                                var period=parseInt(_this.parents("tr").attr('data-index'))+1;
                                var field=_this.attr("data-field");
                                if(click_week==undefined){
                                    var click_week=week;
                                }
                                window.location.href="form.html?week="+click_week+"&day="+field+"&period="+period+"&Lab_ID="+labs_id;//跳转到预约表单页面
                            }
                        });
                    })();
                }
            }
            //生成按钮url更新
            var labs_id = window.location.href.split('=')[1];
            if(typeof(click_week) == 'undefined'){
                $("#output").attr('href','../Excel/excelTable?lab_id='+labs_id+'&week='+week);
            }else{
                $("#output").attr('href','../Excel/excelTable?lab_id='+labs_id+'&week='+click_week);
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            layer.msg(XMLHttpRequest.status + '操作失败', {
                icon: 2
                , shade: 0.1
                , time: 2000
            })
        },
        complete: function (XMLHttpRequest, textStatus) {
            this; // 调用本次AJAX请求时传递的options参数
        }
    });
}
Date.prototype.Format = function (fmt) { //author: meizz
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}