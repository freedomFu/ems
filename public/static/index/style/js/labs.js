layui.use(['element', 'table', 'layer', 'jquery'], function () {
    var element = layui.element,
        form = layui.form,
        layer = layui.layer,
        table = layui.table,
        $ = layui.$;
    table.render({
        elem: '#labs'
        , id: 'labs'
        , height: 488
        , url: 'show' //数据接口
        , cellMinWidth: 60
        , page: true //开启分页
        , cols: [[ //表头
            { field: 'kid', title: 'ID', sort: true }
            , { field: 'lab_name', title: '实验室名称' }
            , { field: 'lab_area', title: '实验室地点' }
            , { field: 'lab_enum', title: '总设备数量' }
            , { align: 'center', toolbar: '#operation-bar', fixed: 'right', width: 178 }
        ]]
    });
    table.on('tool(labs)', function(obj){
        var data = obj.data;
        if(obj.event === 'del'){
            layer.confirm('确认删除么', function(index){
                layui.layer.close(index);
                var json={}
                json.id=data.id;
                return beauty_ajax("del",json,function () {
                    table.reload('labs', {
                        url: "show"
                    });
                });
            });
        } else if(obj.event === 'edit'){
            layer.confirm('确认进行该操作么', function(index){
                layui.layer.close(index);
                window.location.href="showedit.html?id="+data.id;
            });
        }else if(obj.event === 'add'){
            window.location.href="lstequip.html?ids="+data.id;
        }
    });
    form.on('submit(add-equipment)', function(data){
        return beauty_ajax("addequip",data.field,function () {
            table.reload('labs', {
                url: "show"
            });
        });
    });
})