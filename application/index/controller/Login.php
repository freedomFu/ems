<?php
namespace app\index\controller;
use app\index\model\Login as LoginModel;
use think\Controller;
use think\Db;

class Login extends Controller
{
    /**
     * @Author:      fyd
     * @DateTime:    2017-12-14 20:11:06
     * @Description: 判断是否登录成功
     */
    private function atz($res){
        if($res == 1){
            $this->redirect('index/index');
            // $this->success('恭喜你，登陆成功！','index/index');
        }elseif($res == 2){
            $this->error('密码错误！');
        }else{
            $this->error('此用户不存在！');
        }
    }
    /**
     * @Author:      fyd
     * @DateTime:    2017-12-14 20:11:36
     * @Description: 主函数，根据用户输入的对错响应
     */
    public function index()
    {
        if(request() -> isPost()){
            $data = input('post.');
            $login = new LoginModel();
            
            if(isset($data['identity']) && $data['identity']=='staff'){
                $res = $login->login($data,'user');
                $this->atz($res);
            }elseif(isset($data['identity']) && $data['identity']=='admin'){
                $res = $login->login($data,'admin');
                $this->atz($res);
            }
        }
        return $this->fetch('login');
    }

    /**
     * @Author:      fyd
     * @DateTime:    2017-12-14 20:12:16
     * @Description: 游客登录
     */
    public function visitor(){
        $login = new LoginModel();
        $login -> see();
        $this->redirect('Index/index');
        // $this->success('恭喜你以游客身份登录成功！','index/index');
    }

}
