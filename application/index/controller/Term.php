<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;
use think\Request;

class Term extends Base
{
	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-19 21:47:20
	 * @Description: 打开设置页面
	 */
	public function index(){
		return $this->fetch('term/term');
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-20 13:54:09
	 * @Description: 添加单个数据
	 */
	private function addone($name,$value){
		$res1 = Db::name('set') 
				-> where('setname',$name)
				-> find();
		if($res1){
			if($res1['setvalue'] == $value){
				return 1;
			}

			$res2 = Db::name('set')
					-> where('setname',$name)
					-> update(['setvalue'=>$value]);
		}else{
			$data = [
				'setname'	=>	$name,
				'setvalue'	=>	$value
			];
			$res2 = Db::name('set')
					-> insert($data);
		}
		return $res2;
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-20 13:54:09
	 * @Description: 添加数组
	 */
	private function addarr($name,$value){
		$res1 = Db::name('set') 
				-> where('setname',$name)
				-> find();
		$str = implode('|', $value);
		if($res1){
			if($res1['setvalue'] == $str){
				return 1;
			}
			$res2 = Db::name('set')
					-> where('setname',$name)
					-> update(['setvalue'=>$str]);
		}else{
			$data = [
				'setname'	=>	$name,
				'setvalue'	=>	$str
			];
			$res2 = Db::name('set')
					-> insert($data);
		}
		return $res2;
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-22 20:26:47
	 * @Description: 根据第一天推断学期 并把他存入到数据库中
	 */
	private function getxq($fday){
		$farr = explode('-', $fday);
		$fyear = $farr[0];
		$fmonth = $farr[1];
		//学期
		if($fmonth >= '09'){
			$xq = $fyear.'-'.($fyear+1).'-1';
		}else{
			$xq = ($fyear-1).'-'.$fyear.'-2';
		}
		$res = Db::name('set')
				-> where('setname','xq')
				-> find();
		if($res){
			if($res['setvalue'] == $xq){
				return 1;
			}
			$res2 = Db::name('set')
					-> where('setname','xq')
					-> update(['setvalue'=>$xq]);
			return $res2;
		}else{
			$dataxq = [
				'setname'	=>	'xq',
				'setvalue'	=>	$xq
			];
			$res2 = Db::name('set')
					-> insert($dataxq);
			return $res2;
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-22 21:19:21
	 * @Description: 参数有开始日期,获取对应日期的周数 和 星期
	 */
	private function getweek($date,$fday){
		$count = count($date);

		$res = Db::name('holiday')->find();
		if($res){
			$res1 = Db::execute('TRUNCATE TABLE ems_holiday');
		}

		// echo $count.'<br>';
		//转化成时间戳
		$fday_str = strtotime($fday);
		//对传来的假期数组进行 循环遍历，并且获得周数和节次
		for($i=0;$i<$count;$i++){
			$now = $date[$i];
			$now_str = strtotime($now);
			//获取当前是第几周
			$diff = ($now_str - $fday_str) / 86400;
			$termweek = (int)($diff / 7 + 1); //周数
			$ndate = getdate($now_str);
			$wday = $ndate['wday']; //星期
			if($wday == 0){
				$wday = 7;
			}
			// echo $wday.'--'.$termweek.'<br>';
			//然后把数据加入到数据表中
			for($j=0;$j<5;$j++){
				$res = Db::name('holiday')
						-> insert(['exp_date'=>$termweek,'exp_week'=>$wday,'exp_sec'=>($j+1)]);
			}
		}
		for($k=1;$k<=25;$k++){
			for($p=3;$p<=4;$p++){
				$res1 = Db::name('holiday')
						-> insert(['exp_date'=>$k,'exp_week'=>4,'exp_sec'=>$p]);
			}
		}
	}
	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-10 11:10:24
	 * @Description: 把假期加入到假期表,要获取的是第几周的星期几
	 */
	private function isholiday($holiday){
		/*$holiday = [
			'2018-04-05',
			'2018-05-01',
			'2018-06-18',
			];*/

		$hoarr = explode('|', $holiday);
		$count = count($hoarr);

		for($i=0;$i<$count;$i++){
			$holiday = $hoarr[$i];
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-03-20 07:39:56
	 * @Description: 接收数据
	 */
	public function sets(){
		$fday = $_POST['fday'];
		$len = $_POST['len'];
		$holiday = array();
		for($i=0;$i<$len;$i++){
			$holiday[$i] = $_POST['holidays'][$i];
		}

		$res2 = $this->addone('fday',$fday);
		$res3 = $this->addarr('holiday',$holiday);
		$res4 = $this->getxq($fday);

		if($res2 && $res3 && $res4){
			echo json(['code'=>0])->getcontent();
			$this->getweek($holiday,$fday);
		}else{
			echo json(['code'=>1])->getcontent();
		}
	}
}