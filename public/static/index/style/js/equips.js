layui.use(['element', 'table', 'layer', 'jquery'], function () {
    var element = layui.element,
        form = layui.form,
        layer = layui.layer,
        table = layui.table,
        $ = layui.$,
        layer_equip;
    var lab_id = window.location.href.split('=')[1];
    // console.log(lab_id);
    table.render({
        elem: '#equips'
        , id: 'equips'
        , height: 488
        , url: 'showequip/ids/'+lab_id //数据接口
        , cellMinWidth: 60
        , page: true //开启分页
        , cols: [[ //表头
            { field: 'kid', title: 'ID', sort: true }
            , { field: 'equip_name', title: '设备类别' }
            , { field: 'equip_num', title: '设备数量' }
            , { align: 'center', toolbar: '#operation-bar', fixed: 'right', width: 178 }
        ]]
    });
    table.on('tool(equips)', function(obj){
        var data = obj.data;
        if(obj.event === 'del'){
            layer.confirm('确认删除么', function(index){
                layui.layer.close(index);
                var json={}
                json.id=data.id;
                return beauty_ajax("delequip",json,function () {//删除设备
                    obj.del();
                    table.reload('equips', {
                        url: "showequip/ids/"+lab_id
                    });
                });
            });
        } else if(obj.event === 'edit'){
            layer.confirm('确认进行该操作么', function(index){
                layui.layer.close(index);
                layer_equip=layer.open({
                    type: 1,
                    title: '修改信息',
                    content: $('#add-equip').html()
                });
                $('#equip-template button').attr('lay-filter', 'change-equip');
                $('#equip-template input').each(function () {
                    var cur_name = this.name;
                    if (data[cur_name] !== undefined) {
                        this.value = data[cur_name];
                    }
                })
            });
        }
    });
    var $ = layui.$, active = {
        new: function () {
            layer_equip=layer.open({
                type: 1,
                title: '新建设备',
                content: $('#add-equip').html()
            });
        }
    };
    $('.btn-wrap .layui-btn').on('click', function () {
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });
    form.on('submit(add-equip)', function(data){
        var id = window.location.href.split('=')[1];
        return beauty_ajax("addequip",data.field,function () {//新建设备
            table.reload('equips', {
                url: "showequip/ids/"+id
            });
            layer.close(layer_equip);
        });
    });
    form.on('submit(change-equip)', function (data) {//修改设备信息
        var id = window.location.href.split('=')[1];
        return beauty_ajax("editequip", data.field, function () {
            table.reload('equips', {
                url: "showequip/ids/"+id
            });
            layer.close(layer_equip);
        });
    });
})