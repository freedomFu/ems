layui.use(['element', 'table','jquery'], function () {
    var element = layui.element,
        table = layui.table,
        $ = layui.$;
    var ifdisable;
    $("#output-num-btn").click(function () {//修改密码
        layer.open({
            type: 1,
            title: '生成数目',
            content: $('#output_num').html()
        });
    });
    table.render({
        elem: '#reservation_user'
        , id: 'reservation_user'
        , height: 488
        , url: 'getmyre' //数据接口
        , cellMinWidth: 60
        , page: true //开启分页
        , cols: [[ //表头
            { field: 'kid', title: 'ID', sort: true }
            , { field: 'exp_zdt', title: '预约人' }
            , { field: 'lab_name', title: '实验室名称' }
            , { field: 'ifsubmit', title: '是否已提交' }
            , { field: 'ifpass',  title: '是否已通过' }
            , { field: 'sub_time', title: '提交时间' }
            , { align: 'center', toolbar: '#operation-bar', fixed: 'right', width: 140 }
        ]]
    });
    table.on('tool(reservation_user)', function (obj) {
        var data = obj.data;
        if (obj.event === 'del') {
            layer.confirm('确认撤销么', function (index) {
                layui.layer.close(index);
                var json = {}
                json.id = data.id;
                return beauty_ajax("undo", json, function () {
                    table.reload('reservation_user', {
                        url: "getmyre"
                    });
                });
            });
        } else if (obj.event === 'change') {
            layer.confirm('确认进行该操作么', function (index) {
                layui.layer.close(index);
                window.location.href = "reper?id=" + data.id;
            });
        }
    });
})