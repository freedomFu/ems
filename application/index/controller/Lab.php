<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;

class Lab extends Base
{

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-13 14:11:04
	 * @Description: 显示界面
	 */
	public function addinfo(){
		$lid = 0;

		$this->assign('lid',$lid);
		return $this->fetch('lab');
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-10 08:59:24
	 * @Description: 添加实验室信息
	 */
	public function add(){
		if(request() -> isPost()){
			$data = [
				'lab_name'	=> input('lab_name'),
				'lab_area'	=> input('lab_area'),
				'lab_stun'	=> input('lab_stun'),
				'lab_enum'	=> input('lab_enum'),
				'lab_rule'	=> input('lab_rule'),
			];

			$res = Db::name('lab') -> insert($data);

			if($res){
				$this->success('添加成功！','lst');
			}else{
				$this->success('添加失败！');
			}
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-14 14:10:28
	 * @Description: 展示实验室数据
	 */
	public function showedit(){
		$lid = input('id');
		$labinfo = Db::name('lab') -> where([
				'id'		=>	$lid,
				'isdelete'	=>	0,
			])->find();

		$this->assign('lid',$lid);
		$this->assign('labinfo',$labinfo);

		return $this->fetch('lab');
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-14 14:10:28
	 * @Description: 修改实验室数据
	 */
	public function edit(){
		$lid = input('lid');

		$exdata = input('post.');
		$res = Db::name('lab') -> where('id',$lid) -> update($exdata);

		if($res){
			$this->success('修改成功！','lst');
		}else{
			$this->error('修改失败！');
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-13 16:44:42
	 * @Description: 设备页面
	 */
	public function showequip(){
		// dump(input('get.'));
		$id = input('ids');
		$page = input('page');
        $limit = input('limit');

        $count = Db::name('equip')
        -> where('elab_id',$id)
        -> where('isdelete',0)
        -> count();

        $list = Db::name('equip')
        -> where('elab_id',$id)
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
	 * @Author:      fyd
	 * @DateTime:    2018-03-19 20:11:11
	 * @Description: 获取设备列表
	 */
	public function lstequip(){
		$id = input('ids');
		$this -> assign('id',$id);
		return $this -> fetch('equips');
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-19 20:11:46
	 * @Description: 添加设备(未完)
	 */
	public function addequip(){
		$id = input('idss');
		$type = input('equip_name');
		$num = input('equip_num');

		$res = Db::name('equip')
				-> insert([
						'equip_name'	=>	$type,
						'equip_num'		=>	$num,
						'elab_id'		=>	$id
					]);

		if($res){
			echo json(['code'=>0])->getcontent();
		}else{
			echo json(['code'=>1])->getcontent();
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-19 20:35:17
	 * @Description: 修改设备
	 */
	public function editequip(){
		$id = input('id');
		$type = input('equip_name');
		$num = input('equip_num');

		$res = Db::name('equip')
				-> where('id',$id)
				-> update(['equip_name'=>$type,'equip_num'=>$num]);

		if($res){
			echo json(['code'=>0])->getcontent();
		}else{
			echo json(['code'=>1])->getcontent();
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-19 20:27:06
	 * @Description: 删除设备
	 */
	public function delequip(){
		$id = input('id');

		$res = Db::name('equip')
				-> where('id',$id)
				-> update(['isdelete'=>1]);

		if($res){
			echo json(['code'=>0])->getcontent();
		}else{
			echo json(['code'=>1])->getcontent();
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-13 13:57:32
	 * @Description: 产生json数据
	 */
	public function show(){

		$page = input('page');
        $limit = input('limit');

        $count = Db::name('lab')
        -> where('isdelete',0)
        -> count();

        $list = Db::name('lab') 
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
	 * @Author:      fyd
	 * @DateTime:    2018-03-10 09:10:02
	 * @Description: 显示实验室列表
	 */
	public function lst(){

		/*$data = Db::name('lab')->select();
        $this->assign('exp',$data);*/

		return $this->fetch('labs');
	}
	
	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-10 09:12:42
	 * @Description: 删除实验室信息（逻辑删除）
	 */
	public function del(){
		$id = input('id');
		$res = Db::name('lab') -> where('id',$id) -> update(['isdelete'=>1]);
		$res1 = Db::name('equip') -> where(['elab_id'=>$id,'isdelete'=>0]) -> select();
		if($res1){
			$res2 = Db::name('equip') -> where('elab_id',$id) -> update(['isdelete'=>1]);
		}

		if($res){
            echo json(['code'=>0])->getcontent();
        }else{
            echo json(['code'=>1])->getcontent();
        }
	}
}