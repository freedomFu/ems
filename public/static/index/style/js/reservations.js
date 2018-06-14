layui.use(['element', 'table', 'form', 'layer', 'jquery'], function () {
    var element = layui.element,
        form = layui.form,
        layer = layui.layer,
        table = layui.table,
        $ = layui.$;
    table.render({
        elem: '#reservations'
        , id: 'reservations'
        , height: 488
        , url: 'getdata' //数据接口
        , cellMinWidth: 60
        , page: true //开启分页
        , cols: [[ //表头
            { field: 'kid', title: 'ID', sort: true }
            , { field: 'exp_zdt', title: '预约人' }
            , { field: 'lab_name', title: '实验室名称' }
            , { field: 'ifsubmit', templet: '#switch-submit', title: '是否已提交', unresize: true }
            , { field: 'ifpass', templet: '#switch-pass', title: '是否已通过', unresize: true }
            , { field: 'sub_time', title: '提交时间' }
        ]]
    });
    form.on('switch(ifsubmit)', function(obj){
        var _this = this;
        layer.confirm('确认该操作么', function(index){
            layer.close(index);
            var json={};
            json.id=_this.value;
            json[_this.name]=obj.elem.checked;
            return beauty_ajax("exsub",json);
        }, function(index){
            layer.close(index);
            if(obj.elem.checked == true){
                obj.elem.checked = false;
                form.render();
            }else{
                obj.elem.checked = true;
                form.render();
            }
        });
    });
    form.on('switch(ifpass)', function(obj){
        var _this = this;
        layer.confirm('确认该操作么', function(index){
            layer.close(index);
            var json={};
            json.id=_this.value;
            json[_this.name]=obj.elem.checked;
            return beauty_ajax("exall",json);
        }, function(index){
            layer.close(index);
            if(obj.elem.checked == true){
                obj.elem.checked = false;
                form.render();
            }else{
                obj.elem.checked = true;
                form.render();
            }
        });
    });
});