<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;

class User extends Base
{
	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-10 08:04:33
	 * @Description: 添加用户
	 */
    public function add(){
        if(request() -> isPost()){
            $datas = input('post.');

            $data = [
                'username'  => $datas['username'],
                'name'      => $datas['name'],
                'password'  => md5($datas['password'])
            ];

            $res = Db::name('user') -> insert($data);

            if($res){
                echo json(['code'=>0])->getcontent();
            }else{
                echo json(['code'=>1])->getcontent();
            }
        }	
    }

    /**
     * @Author:      fyd
     * @DateTime:    2018-03-12 18:11:14
     * @Description: 产生json数据
     */
    public function show(){
        $page = input('page');
        $limit = input('limit');

        $count = Db::name('user')
        -> where('isdelete',0)
        -> count();

        $list = Db::name('user') 
        -> where('isdelete',0) 
        -> limit(($page-1)*$limit,$limit)
        -> select();

        $counts = count($list);

        for($i=1;$i<=$counts;$i++){
            $list[$i-1]['kid'] = $i;
        }

        if($count){
            echo json(['code'=>0,'count'=>$count,'msg'=>'','data'=>$list])->getcontent();
        }else{
            echo json(['code'=>1,'count'=>$count,'msg'=>'','data'=>$list])->getcontent();
        }
    }

    /**
     * @Author:      修改用户数据
     * @DateTime:    2018-03-12 18:11:34
     * @Description: Description
     */
    public function edit(){
        $data = input('post.');
        $id = $data['id'];
        $name = $data['name'];
        $username = $data['username'];
        $password = $data['password'];

        $res = Db::name('user') -> where('id',$id) -> update([
                'username' => $username,
                'name'     => $name,
            ]);

        if($res){
            echo json(['code'=>0])->getcontent();
        }else{
            echo json(['code'=>1])->getcontent();
        }

    }


    /**
     * @Author:      fyd
     * @DateTime:    2018-03-10 08:08:55
     * @Description: 显示用户列表
     */
    public function lst(){

    	return $this->fetch('User/user');

    }

    /**
     * @Author:      fyd
     * @DateTime:    2018-03-10 08:04:23
     * @Description: 删除用户(逻辑删除)
     */
    public function del(){
    	$id = input('id');
    	$res = Db::name('user') -> where('id',$id) -> update(['isdelete'=>1]);
        if($res){
            echo json(['code'=>0])->getcontent();
        }else{
            echo json(['code'=>1])->getcontent();
        }
        

    }

}