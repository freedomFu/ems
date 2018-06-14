var WIDTH = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
    HEIGHT = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight,
    POINT = 0.02*WIDTH;
var canvas = document.getElementById('Mycanvas');
canvas.width = WIDTH,
    canvas.height = HEIGHT;
var context = canvas.getContext('2d');
context.strokeStyle = 'rgba(0,0,0,0.05)',
    context.strokeWidth = 1,
    context.fillStyle = 'rgba(0,0,0,0.05)';
var circleArr = [];

//线条：开始xy坐标，结束xy坐标，线条透明度
function Line (x, y, _x, _y, o) {
    this.beginX = x,
        this.beginY = y,
        this.closeX = _x,
        this.closeY = _y,
        this.o = o;
}
//点：圆心xy坐标，半径，每帧移动xy的距离
function Circle (x, y, r, moveX, moveY) {
    this.x = x,
        this.y = y,
        this.r = r,
        this.moveX = moveX,
        this.moveY = moveY;
}
//生成max和min之间的随机数
function num (max, _min) {
    var min = arguments[1] || 0;
    return Math.floor(Math.random()*(max-min+1)+min);
}
// 绘制原点
function drawCricle (cxt, x, y, r, moveX, moveY) {
    var circle = new Circle(x, y, r, moveX, moveY)
    cxt.beginPath()
    cxt.arc(circle.x, circle.y, circle.r, 0, 2*Math.PI)
    cxt.closePath()
    cxt.fill();
    return circle;
}
//绘制线条
function drawLine (cxt, x, y, _x, _y, o) {
    var line = new Line(x, y, _x, _y, o)
    cxt.beginPath()
    cxt.strokeStyle = 'rgba(0,0,0,'+ o +')'
    cxt.moveTo(line.beginX, line.beginY)
    cxt.lineTo(line.closeX, line.closeY)
    cxt.closePath()
    cxt.stroke();

}
//初始化生成原点
function init () {
    circleArr = [];
    for (var i = 0; i < POINT; i++) {
        circleArr.push(drawCricle(context, num(WIDTH), num(HEIGHT), num(15, 2), num(10, -10)/40, num(10, -10)/40));
    }
    draw();
}

//每帧绘制
function draw () {
    context.clearRect(0,0,canvas.width, canvas.height);
    for (var i = 0; i < POINT; i++) {
        drawCricle(context, circleArr[i].x, circleArr[i].y, circleArr[i].r);
    }
    for (var i = 0; i < POINT; i++) {
        for (var j = 0; j < POINT; j++) {
            if (i + j < POINT) {
                var A = Math.abs(circleArr[i+j].x - circleArr[i].x),
                    B = Math.abs(circleArr[i+j].y - circleArr[i].y);
                var lineLength = Math.sqrt(A*A + B*B);
                var C = 1/lineLength*7-0.009;
                var lineOpacity = C > 0.03 ? 0.03 : C;
                if (lineOpacity > 0) {
                    drawLine(context, circleArr[i].x, circleArr[i].y, circleArr[i+j].x, circleArr[i+j].y, lineOpacity);
                }
            }
        }
    }
};
// 验证码
layui.use(['jquery','layer','form'], function(){
    var $ = layui.$,
        layer = layui.layer,
        form = layui.form;
    var code;
    var codeLength = 5; //验证码的长度
    var checkCode = $(".code");
    var codeChars = new Array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
        'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'); //所有候选组成验证码的字符，当然也可以用中文的
    var _code_color1 = ['#fffff0', '#f0ffff', '#f0fff0', '#fff0f0'];
    var _code_color2 = ['#FF0033', '#006699', '#993366', '#FF9900', '#66CC66', '#FF33CC'];
    function createCode() {
        code = "";
        var color1Num = Math.floor(Math.random() * 3);
        var color2Num = Math.floor(Math.random() * 5);
        for (var i = 0; i < codeLength; i++)
        {
            var charNum = Math.floor(Math.random() * 62);
            code += codeChars[charNum];
        }
        checkCode.text(code);
        checkCode.css({'color':_code_color2[color2Num],'backgroundColor':_code_color1[color1Num]});
    }
    function validateCode()
    {
        var form=$("form");
        var inputCode = $(".code_input");
        var flag=true;
        form.submit(function(){
            if (inputCode.val().toUpperCase() != code.toUpperCase())
            {
                layer.msg('验证码出错啦', {icon: 5});
                createCode();
                inputCode.val("");
                flag=false;
            }
            else
            {
                flag=true;
            }
            return flag;
        });
    }
    $(function () {
        var nav_labels=$(".nav-slider label");
        var nav_slider_bar=$('.nav-slider-bar');
        nav_labels.click(function () {
            $(this).addClass('active').siblings('label').removeClass('active');
            var le=($(this).index()-1)*3;
            nav_slider_bar.css({'left':le+'em'});
        });
        createCode();
        validateCode();
        checkCode.click(function(){
            createCode();
        });
        init();
        setInterval(function () {
            for (var i = 0; i < POINT; i++) {
                var cir = circleArr[i];
                cir.x += cir.moveX;
                cir.y += cir.moveY;
                if (cir.x > WIDTH) cir.x = 0;
                else if (cir.x < 0) cir.x = WIDTH;
                if (cir.y > HEIGHT) cir.y = 0;
                else if (cir.y < 0) cir.y = HEIGHT;
            }
            draw();
        }, 16);
    });
});