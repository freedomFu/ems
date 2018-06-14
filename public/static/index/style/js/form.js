layui.use(['element', 'form', 'jquery', 'layer'], function () {
    var element = layui.element,
        form = layui.form,
        layer = layui.layer,
        $ = layui.$;
    function getUrlParam(name) {
	    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
	    var r = window.location.search.substr(1).match(reg);  //匹配目标参数
	    if (r != null) return unescape(r[2]); return null; //返回参数值
	}
	if(getUrlParam('id')!=null){
		form.on('submit(form_btn)', function (data) {//修改
	        return beauty_ajax("../Reservations/rechange", data.field);
	    });
	}else{
        var myInfo = JSON.parse(localStorage.getItem('myInfo'));
        if(myInfo!=null){
            form.val("form", {
                "class_name": myInfo.class_name
                ,"teacher": myInfo.teacher
                ,"office": myInfo.office
                ,"equip_name": myInfo.equip_name
                ,"classes": myInfo.classes
                ,"sum_peo": myInfo.sum_peo
                ,"group_peo": myInfo.group_peo
                ,"cycle_peo": myInfo.cycle_peo
                ,"type": myInfo.type
                ,"major_class": myInfo.major_class
                ,"desc": myInfo.desc
            })
            form.render(null, 'form');
		}
	    form.on('submit(form_btn)', function (data) {//新增
	        return beauty_ajax("../Reservations/add", data.field,function () {
	        	localStorage.setItem('myInfo',JSON.stringify(data.field));
            });
	    });
	}
})