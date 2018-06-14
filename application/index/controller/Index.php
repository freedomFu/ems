<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;

class Index extends Base
{
    /**
     * @Author:      fyd
     * @DateTime:    2017-12-14 20:12:37
     * @Description: 显示实验室分类
     */
    public function index()
    {
        return $this->fetch('index');
    }

    /**
     * @Author:      fyd
     * @DateTime:    2017-12-14 20:13:51
     * @Description: 跳转到课程表页面，并传输数据
     */
    public function table(){

        $lab_id = input('Lab_ID');
        $labinfo = Db::name('lab')->where('id',($lab_id))->find();


        /**
         * 添加学期
         */
        $xq = Db::name('set')->where('setname','xq')->find();
        $exp_xq = $xq['setvalue'];
        $xq_arr = explode('-',$exp_xq);
        if($xq_arr[2]==1){
            $xq_arr[2]="一";
        }else{
            $xq_arr[2]="二";
        }
//        dump($xq_arr);
        $this->assign('xq_arr',$xq_arr);

        $this->assign('labinfo',$labinfo);
        return $this->fetch('table');
    }

    /**
     * @Author:      fyd
     * @DateTime:    2018-02-20 11:59:40
     * @Description: 跳转到输入课程信息界面，并传输数据
     */
    public function form(){
        $reper = 1;
        $this->assign('reper',$reper);
        $type = Db::name('type')->select();
        $jys = Db::name('jys')->select();

        $this->assign('jys',$jys);
        $this->assign('type',$type);
        $lab_id = input('Lab_ID');
        $equip = Db::name('equip')->where(['elab_id'=>$lab_id,'isdelete'=>0])->select();
        // 得到点击的位置的信息
        $gets = input('get.');
        $this->assign('gets',$gets);
        // dump($gets);
        $week = $gets['week'];
        $warr = ['mon','tues','wed','thur','fri','sat'];
        $day = array_search($gets['day'],$warr)+1;
        $period = $gets['period'];
        // echo $week.'---'.$day.'---'.$period;
        $count = count($equip);
        $xq = Db::name('set')->where('setname','xq')->find();
        $exp_xq = $xq['setvalue'];
        for($i=0;$i<$count;$i++){
            $remain = Db::name('exper')
                ->field('sum(exp_snum/exp_pnum) as sum')
                ->where(['equip_id'=>$equip[$i]['id'],'exp_date'=>$week,'exp_week'=>$day,'exp_sec'=>$period,'undo'=>0])
                ->where('exp_xq',$exp_xq)
                ->find();
            // dump($remain);
            $rdata = (int)$remain['sum'];
            $equip[$i]['remain_num'] = $equip[$i]['equip_num']-$rdata;
        }
        $this->assign('equip',$equip);
        // dump($equip);
        return $this->fetch('form');
    }

    /**
     * @Author:      fyd
     * @DateTime:    2018-02-20 18:01:07
     * @Description: 修改用户密码
     */
    public function ex_pass(){
        $username = session('ems_username');
        $identity = session('ems_identity');
        if($identity == 'staff'){
            $identity = 'user';
        }
        $info = Db::name($identity)->where('username',$username)->find();
        $password = $info['password'];

        if(request() -> isPost()){
            $ex_data = input('post.');
            $old_pass = md5($ex_data['old_pass']);
            $new_pass = md5($ex_data['new_pass']);
            if($old_pass == $password){
                if($new_pass == $password){
                    echo json(['code'=>1,'msg'=>'您输入的密码与原密码相同！'])->getcontent();  
                }else{
                    $result = Db::name($identity)
                    ->where('username',$username)
                    ->update([
                        'password' => $new_pass,
                    ]);

                    if($result){
                        session(null);
                        // echo '<script>
                        //         alert(123);
                        //     </script>';
                        // $this->redirect('Login/index');
                        echo json(['code'=>0])->getcontent();
                    }else{
                        echo json(['code'=>1])->getcontent();  
                    }
                }
            }else{
                echo json(['code'=>1,'msg'=>'原密码错误'])->getcontent();  
            }
        }
    }

    /**
     * @Author:      fyd
     * @DateTime:    2017-12-14 20:12:57
     * @Description: 退出登录
     */
    public function logout(){
        session(null);
        $this->success('退出成功！','Login/index');
    }
}
