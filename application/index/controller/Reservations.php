<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;

class Reservations extends Base
{

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-02-21 09:32:42
	 * @Description: 获得所有预约
	 */
	public function index(){
		return $this->fetch('reservations');
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-02-21 09:32:42
	 * @Description: 获得本人预约
	 */
	public function index_user(){
		return $this->fetch('reservation_user');
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-31 09:48:40
	 * @Description: 获取本人预约的数据
	 */
	public function getmyre(){

		$page = input('page');
        $limit = input('limit');
        $user = session('ems_name');

        $count = Db::name('exper')
        -> where('exp_user',$user)
        -> where('undo',0)
        -> order('exp_time')
        -> count();
		
		$data = Db::name('exper')
				->where('exp_user',$user)
				-> where('undo',0)

				->limit(($page-1)*$limit,$limit)
				->order('exp_time desc,exp_xq desc')  // 根据学期排序
				->select();
		$counts = count($data);

		for($i=1;$i<=$counts;$i++){
            $data[$i-1]['kid'] = $i;
        }
		$datas = [];
		for ($i=0; $i<$counts; $i++) { 
			$lab = Db::name('lab')->where('id',$data[$i]['elab_id'])->find();
			$datas[$i]['id'] = $data[$i]['id'];
			$datas[$i]['kid'] = $data[$i]['kid'];
			$datas[$i]['exp_zdt'] = $data[$i]['exp_zdt'];
			$datas[$i]['lab_name'] = $lab['lab_name'];
			$datas[$i]['ifsubmit'] = ($data[$i]['exp_apply'] == 1)? '是':'否'; 
			$datas[$i]['ifpass'] = ($data[$i]['exp_isallow'] == 1)? '是':'否'; 
			$datas[$i]['sub_time'] = $data[$i]['exp_time'];
		}
		echo json(['code'=>0,'count'=>$count,'msg'=>'','data'=>$datas])->getcontent();
	}


	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-18 18:16:36
	 * @Description: 获取预约数据
	 */
	public function getdata(){
		$page = input('page');
        $limit = input('limit');

        $count = Db::name('exper')-> where('undo',0) -> count();

		$user = session('ems_name');
		$data = Db::name('exper')
				-> where('undo',0)
				-> limit(($page-1)*$limit,$limit)
				-> order('exp_time desc,exp_xq desc') // 根据学期排序
				->select();
		$counts = count($data);

		for($i=1;$i<=$counts;$i++){
            $data[$i-1]['kid'] = $i;
        }
		$datas = [];
		for ($i=0; $i<$counts; $i++) { 
			$lab = Db::name('lab')->where('id',$data[$i]['elab_id'])->find();

			$datas[$i]['id'] = $data[$i]['id'];
			$datas[$i]['kid'] = $data[$i]['kid'];
			$datas[$i]['exp_zdt'] = $data[$i]['exp_zdt'];
			$datas[$i]['lab_name'] = $lab['lab_name'];
			$datas[$i]['ifsubmit'] = ($data[$i]['exp_apply'] == 1)? true:false; 
			$datas[$i]['ifpass'] = ($data[$i]['exp_isallow'] == 1)? true:false; 
			$datas[$i]['sub_time'] = $data[$i]['exp_time'];
		}
		echo json(['code'=>0,'count'=>$count,'msg'=>'','data'=>$datas])->getcontent();
	}



	/**
	 * @Author:      fyd
	 * @DateTime:    2018-04-09 16:57:41
	 * @Description: 撤销预约
	 */
	public function undo(){
		$id = input('id');
		$res = Db::name('exper')->where('id',$id)->update(['undo'=>1]);
		if($res){
			echo json(['code'=>0,'msg'=>'撤销成功'])->getcontent();
		}else{
			echo json(['code'=>1,'msg'=>'撤销失败'])->getcontent();
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-04-09 17:22:59
	 * @Description: 修改预约数据
	 */
	public function reper(){
		$reper = 0;
		$this->assign('reper',$reper);

		$id = input('id');
		$this->assign('eid',$id);
		$res = Db::name('exper')->where('id',$id)->update(['undo'=>1]);
		$experinfo = Db::name("exper")->where('id',$id)->find();
		$this->assign('experinfo',$experinfo);
        $equip = Db::name('equip')->where(['elab_id'=>$experinfo['elab_id'],'isdelete'=>0])->select();
        $week = $experinfo['exp_date'];
        $warr = ['mon','tues','wed','thur','fri','sat'];
        $day = array_search($experinfo['exp_week'],$warr)+1;
        $period = $experinfo['exp_sec'];
        // echo $week.'---'.$day.'---'.$period;
        $count = count($equip);
        for($i=0;$i<$count;$i++){
            $remain = Db::name('exper')->field('sum(exp_snum/exp_pnum) as sum')->where(['equip_id'=>$equip[$i]['id'],'exp_apply'=>1,'exp_isallow'=>1,'exp_date'=>$week,'exp_week'=>$day,'exp_sec'=>$period,'undo'=>0])->find();
            // dump($remain);
            $rdata = (int)$remain['sum'];
            $equip[$i]['remain_num'] = $equip[$i]['equip_num']-$rdata;
        }
        $this->assign('equip',$equip);

		$type = Db::name('type')->select();
        $jys = Db::name('jys')->select();
        $this->assign('jys',$jys);
        $this->assign('type',$type);
        $res = Db::name('exper')->where('id',$id)->update(['undo'=>0]);
		return $this->fetch('index/form');
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-04-10 07:46:04
	 * @Description: 把修改后的数据添加到数据库
	 */
	public function rechange(){
		$id = input('experid');
		$data = array();
		$exdata = input('post.');
		$exper_info = Db::name('exper')->where('id',$id)->find();
        $res1 = Db::name('exper')->where('id',$id)->update(['undo'=>1]);
		$termweek = $exper_info['exp_date'];
		$wday = $exper_info['exp_week'];
		$peroid = $exper_info['exp_sec'];
		$data = [
			'exp_name'=>$exdata['class_name'],
//			'exp_xs'=>$exdata['classes'],
			'exp_cycle'=>$exdata['cycle_peo'],
			'exp_bz'=>$exdata['desc'],
			'equip_id'=>$exdata['equip_name'],
			'exp_pnum'=>$exdata['group_peo'],
			'exp_class'=>$exdata['major_class'],
			'exp_jys'=>$exdata['office'],
			'exp_snum'=>$exdata['sum_peo'],
			'exp_zdt'=>$exdata['teacher'],
			'exp_type'=>$exdata['type']
		];

		$state = $this->state($exdata['class_name'],$exdata['major_class'],$exdata['teacher'],0,$exdata['sum_peo'],$exdata['group_peo'],$exdata['cycle_peo'],$exdata['equip_name'],$termweek,$wday,$peroid,$exper_info['elab_id']);

		if($state){
			$res = Db::name('exper') -> where('id',$id) -> update($data);
            Db::name('exper') -> where('id',$id) -> update(['undo'=>0]);
			if($res){
				echo json(['code'=>0,'msg'=>'修改成功'])->getcontent();
			}else{
				echo json(['code'=>1,'msg'=>'未修改数据'])->getcontent();
			}
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-18 18:37:42
	 * @Description: 私有函数
	 */
	private function ex($name,$id){
		$data = Db::name('exper')
				-> where('id',$id)
				-> find();
		if($data){
			$sub = $data[$name];
			
			$res = Db::name('exper')
				-> where('id',$id)
				-> update([$name=>!$sub]);

			if($res){
				echo json(['code'=>0])->getcontent();
			}else{
				echo json(['code'=>1])->getcontent();
			}
		}else{
			echo json(['code'=>1])->getcontent();
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-18 18:36:57
	 * @Description: 是否提交申请书
	 */
	public function exsub(){
		$id = input('id');
		$this->ex('exp_apply',$id);
	}
	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-18 18:36:57
	 * @Description: 是否通过申请
	 */
	public function exall(){
		$id = input('id');
		$this->ex('exp_isallow',$id);
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-04-10 17:13:35
	 * @Description: 判断预约条件
	 */
	private function state($ename,$eclass,$ezdt,$xs,$snum,$pnum,$cycle,$equipid,$termweek,$day,$peroid){
		$state = true;
		$a2=preg_match('/['.chr(0xa1).'-'.chr(0xff).']/', $eclass);
		$b2=preg_match('/[0-9]/', $eclass);
		$c2=preg_match('/[a-zA-Z]/', $eclass);
		$a3=preg_match('/['.chr(0xa1).'-'.chr(0xff).']/', $ezdt);
		$b3=preg_match('/[0-9]/', $ezdt);
		$c3=preg_match('/[a-zA-Z]/', $ezdt);
		if( !is_numeric($snum) | !is_numeric($pnum) | !is_numeric($cycle)){
			// echo "这些数据必须全部是数字<br>";
			echo json(['code'=>1,'msg'=>'人数信息必须全部是数字'])->getcontent();
			$state = false;
			return $state;
		}elseif( !($a3 && !$b3 && !$c3)){
			echo json(['code'=>1,'msg'=>'指导老师必须是中文'])->getcontent();
			$state = false;
			return $state;
		}elseif(!($a2 && $b2 && !$c2)){
			echo json(['code'=>1,'msg'=>'专业班级必须有中文和数字'])->getcontent();
			// echo "这个数据必须有中文和数字";
			$state = false;
			return $state;
		}
        $xq = Db::name('set')->where('setname','xq')->find();
        $exp_xq = $xq['setvalue'];

		//进行设备的处理
		$applynum = (int)($snum/$pnum);
		$remain = Db::name('exper')
            ->field('sum(exp_snum/exp_pnum) as sum')
            ->where([
                'equip_id'=>$equipid,
/*                'exp_apply'=>1,
                'exp_isallow'=>1,*/
                'exp_date'=>$termweek,
                'exp_week'=>$day,
                'exp_sec'=>$peroid,
                'undo'=>0,
                'exp_xq'=>$exp_xq
            ])
            ->find();

		$esum = Db::name('equip')->where('id',$equipid)->find();
		$sum = $esum['equip_num'];
		if($remain){
			$rdata = (int)$remain['sum'];
			$leftnum = $sum - $rdata;
			if($applynum > $leftnum){
				echo json(['code'=>1,'msg'=>'预约设备过多'])->getcontent();
				$state = false;
				return $state;
			}
		}else{
			if($applynum > $sum){
				echo json(['code'=>1,'msg'=>'预约设备过多'])->getcontent();
				$state = false;
				return $state;
			}
		}
		return $state;
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-02-21 15:40:40
	 * @Description: add
	 */
	public function add(){
		$posts = input('post.');
		$lab_id = $posts['Lab_ID'];
		$equipid = $posts['equip_name'];
		$equipdata = Db::name('equip')->where('id',$equipid)->find();
		$equipnum = $equipdata['equip_num'];
		$user = session('ems_name');
		$posts['exp_user'] = $user;
		$zdt = $posts['teacher'];

		// 获取课程号以及学期,获取接口后可以获取
		$exp_id = "1122334";
		$xq = Db::name('set')->where('setname','xq')->find();
		$exp_xq = $xq['setvalue'];

		$name = $posts['class_name']; //课程名称
//		$xs = $posts['classes']; //学时
		$snum = $posts['sum_peo'];
		$pnum = $posts['group_peo'];
		$cycle = $posts['cycle_peo'];
		$class = $posts['major_class'];
		$day = $posts['day'];

		$warr = ['mon','tues','wed','thur','fri','sat'];
		$wday = array_search($day,$warr)+1;

		$state = $this->state($name,$class,$zdt,0,$snum,$pnum,$cycle,$equipid,$posts['week'],$wday,$posts['period']);

		if($state){
			$termweek = $posts['week'];
			$insertdata = array(
				'exp_user'	=>	$user,
				'exp_xq' 	=>	$exp_xq,
				'exp_name'	=>	$name,
				'exp_id'	=>	$exp_id,
				'exp_jys'	=>	$posts['office'],
				'exp_zdt'	=>	$zdt,
//				'exp_xs'	=>	$xs,
				'elab_id'	=>	$lab_id,
				'equip_id'	=>	$equipid,
				'exp_snum'	=>	$snum,
				'exp_pnum'	=>	$pnum,
				'exp_cycle'	=>	$cycle,
				'exp_bz'	=>	$posts['desc'],
				'exp_class'	=>	$class,
				'exp_type'	=>	$posts['type'],
				'exp_date'	=>	$termweek,
				'exp_week'	=>	$wday,
				'exp_sec'	=>	$posts['period'],
				'exp_apply'	=>	0,
				'exp_isallow'=>	0
			);

			$res = Db::name('exper')->insert($insertdata);
			if($res){
				echo json(['code'=>0,'msg'=>'添加成功'])->getcontent();
			}else{
				// echo json(['code'=>1,'msg'=>'未修改信息'])->getcontent();
				echo json(['code'=>1,'msg'=>'添加失败'])->getcontent();
			}
		}
	}
}