<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Login extends Model
{
    /**
     * @Author:      fyd
     * @DateTime:    2017-12-14 20:07:49
     * @Description: 根据用户的输入判断
     */
    public function login($data,$table){//教职工登录
        // $user = Db::name($table) -> where('username','=',$data['username']) -> find();
        $user = Db::name($table) 
                -> where([
                    'username'  => $data['username'],
                    'isdelete'  => 0,
                ])
                ->find();
        if($user){
            //补充：密码的加密
            if($user['password'] == md5($data['password'])){
                if(is_null(session('ems_id'))){
                    echo '<script>alert("注意：您已经登录了其他用户！")</script>';
                }
                session(null);
                session('ems_id',$user['id']);
                session('ems_name',$user['name']);
                session('ems_username',$user['username']);
                session('ems_identity',$data['identity']);
                $str = date("Y-m-d H:i",time());
                $res = Db::name($table)->where('username',$data['username'])->update(['lasttime'=>$str]);
                //dump($_SESSION);
                return 1;//信息正确
            }else{
                return 2;//密码错误
            }
        }else{
            return 3;//用户不存在
        }
    }
    /**
     * @Author:      fyd
     * @DateTime:    2017-12-14 20:10:41
     * @Description: 游客登录
     */
    public function see(){//以游客身份登陆
        session(null);
        session('ems_name','游客');
        session('ems_identity','visitor');
    }
}