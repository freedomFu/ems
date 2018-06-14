layui.use(['element', 'layer','laydate', 'jquery'], function () {
    var element = layui.element,
        form = layui.form,
        layer = layui.layer,
        laydate = layui.laydate,
        $ = layui.$;
    var holidays=[];
    laydate.render({
        elem: '#fday-select'
        , position: 'static'
        , change: function (value, date) { //监听日期被切换
            lay('#fday').val(value);
        }
    });
    laydate.render({
        elem: '#holidays-select'
        , position: 'static'
        , done: function (value, date) { //监听日期被切换
            lay('#holidays').val(value);
            $(".holiday-title").text('当前已选节假日');
            if(holidays.indexOf(value) == -1){
                holidays.push(value);
                var li = $('<li></li>', { html: value +'<i class="layui-icon">ဆ</i>', class: 'layui-btn layui-btn-primary layui-btn-sm' });
                li.appendTo('.selected-holidays');
            };
            $(".selected-holidays li i").on('click', function () {
                var parent_li = $(this).parent('li')
                var click_li = parent_li.text().substring(0,10);
                var index = holidays.indexOf(click_li);
                if(index > -1){
                    holidays.splice(index,1);
                    parent_li.remove();
                }
                if(holidays.length == 0){
                    $(".holiday-title").text('');
                    $("#holidays").val("");
                }
            })
        }
    });
    form.on('submit(term)', function (data) {
        var post_data={};
        post_data.fday = data.field.fday;
        post_data.holidays=holidays;
        post_data.len = holidays.length;
        // alert(holidays.length);
        return beauty_ajax("sets", post_data);
    });
})