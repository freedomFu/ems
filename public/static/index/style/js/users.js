layui.use(['element', 'table', 'layer', 'jquery'], function () {
    var element = layui.element,
        form = layui.form,
        layer = layui.layer,
        table = layui.table,
        $ = layui.$,
        layer_user;
    table.render({
        elem: '#users'
        , id: 'users'
        , height: 488
        , url: 'show' //数据接口
        , cellMinWidth: 60
        , page: true //开启分页
        , cols: [[ //表头
            { field: 'kid', title: 'ID', sort: true }
            , { field: 'username', title: '用户名' }
            , { field: 'name', title: '姓名' }
            , { field: 'lasttime', title: '最后操作时间' }
            , { align: 'center', toolbar: '#operation-bar', fixed: 'right', width: 178 }
        ]]
    });
    var $ = layui.$, active = {
        new: function () {
            layer_user=layer.open({
                type: 1,
                title: '添加人员',
                content: $('#add-user').html()
            });
        }
    };
    $('.btn-wrap .layui-btn').on('click', function () {
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });
    table.on('tool(users)', function(obj){
        var data = obj.data;
        if(obj.event === 'del'){
            layer.confirm('确认删除么', function(index){
                layui.layer.close(index);
                var json={}
                json.id=data.id;
                return beauty_ajax("del",json,function () {
                    table.reload('users', {
                        url: "show"
                    });
                });
            });
        } else if(obj.event === 'edit'){
            layer.confirm('确认进行该操作么', function(index){
                layui.layer.close(index);
                layer_user=layer.open({
                    type: 1,
                    title: '修改信息',
                    content: $('#add-user').html()
                });
                $('#user-template form>div').eq(2).addClass('layui-hide');
                $('#user-template button').attr('lay-filter','change-user');
                $('#user-template input').each(function () {
                    var cur_name=this.name;
                    if(data[cur_name] !== undefined){
                        this.value=data[cur_name];
                    }
                })
            });
        }
    });
    form.on('submit(add-user)', function(data){//新建用户
        return beauty_ajax("add",data.field,function () {
            table.reload('users', {
                url: "show"
            });
            layer.close(layer_user);
        });
    });
    form.on('submit(change-user)', function(data){//修改用户信息
        return beauty_ajax("edit",data.field,function () {
            table.reload('users', {
                url: "show"
            });
            layer.close(layer_user);
        });
    });
})