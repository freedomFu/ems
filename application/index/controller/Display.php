<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;

class Display extends Base
{
	/**
	 * @Author:      name
	 * @DateTime:    2018-03-10 10:51:10
	 * @Description: 获取当前学期和周数
	 */
	private function getinfo($now){
		$dateinfo = Db::name('set')
					-> where('setname','fday')
					-> find();
		$fday = $dateinfo['setvalue'];
		$farr = explode('-', $fday);
		$fyear = $farr[0];
		$fmonth = $farr[1];
		//学期
		if($fmonth > '09'){
			$xq = $fyear.' - '.($fyear+1).' -  2';
		}else{
			$xq = ($fyear-1).' - '.$fyear.' -  1';
		}
		//转化成时间戳
		$fday_str = strtotime($fday);
		$now_str = strtotime($now);
		//获取当前是第几周
		$diff = ($now_str - $fday_str) / 86400;
		$termweek = $diff / 7 + 1;
		$ndate = getdate($now_str);
		$wday = $ndate['wday'];
		if($wday == 0){
			$wday = 7;
		}
		$info = [
				'xq'		=>	$xq,
				'fday'		=>	$fday,
				'termweek'	=>	(int)$termweek,
				'wday'		=>	$wday,
			];
		return $info;
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-02-23 16:37:50
	 * @Description: 展示当前周数
	 */
	public function index(){
		//当前是哪一天
		$now = date("Y-m-d",time());
		$info = $this->getinfo($now);
		$lab_id = input('ids'); 

		$labid = Db::name('equip') 
		-> where('elab_id',$lab_id) 
		-> field('id')
		-> find();

        /**
         * 生成学期  67 68
         */

        $xq = Db::name('set')->where('setname','xq')->find();
        $exp_xq = $xq['setvalue'];

        if(input('weeks')){
			$dweek = input('weeks');
		}else{
			$dweek = $info['termweek']; //获取当前周数
		}

		$data = [];

		if(isset($dweek)){
			$data = Db::name('exper')
					->field('sum(exp_snum/exp_pnum),sum(exp_snum),exp_snum,exp_name,exp_class,exp_zdt,exp_sec,exp_week,exp_apply,exp_isallow')
					->where('exp_date',$dweek)
					->where('elab_id',$lab_id)
					->where('undo',0)
                    ->where('exp_xq',$exp_xq)   //新的学期   6.9修改
					// ->where('equip_id','in',$labid)
//					->where(['exp_apply'=>1,'exp_isallow'=>1])
					->group('exp_date,exp_week,exp_sec')
					->select();
		}else{
			$data = Db::name('exper')
					->field('sum(exp_snum/exp_pnum),sum(exp_snum),exp_snum,exp_name,exp_class,exp_zdt,exp_sec,exp_week,exp_apply,exp_isallow')
					->where('exp_date',18)
					->where('elab_id',$lab_id)
					->where('undo',0)
                    ->where('exp_xq',$exp_xq)   //新的学期   6.9修改
					// ->where('equip_id','in',$labid)
//					->where(['exp_apply'=>1,'exp_isallow'=>1])
					->group('exp_date,exp_week,exp_sec')
					->select();
		}

		$count = count($data);
		$arr = [];
		for($js=0;$js<5;$js++){
			$arr[$js] = [ [[]], [[]], [[]], [[]], [[]], [[]]
			];
		}
//        dump($data);
		// 对于$data的处理 获取到对应实验的实验设备数目 并且进行对比 然后把数据添加过去
		for($n=0;$n<$count;$n++){
			$oper_data = $data[$n];
			$apply = $data[$n]['exp_apply'];
			$isallow = $data[$n]['exp_isallow'];
			if($apply==0 | $isallow==0){
                $data[$n]['isAllow'] = 0;
            }else{
                $data[$n]['isAllow'] = 1;
            }
			// $equipid = $oper_data['equip_id'];
			// $edata = Db::name('equip')->where('id',$equipid)->find();
			$edata = Db::name('lab')->where('id',$lab_id)->find();
			$equipnum = $edata['lab_enum']; //实验室中存在的实验设备数目
			$applynum = (int)$oper_data['sum(exp_snum/exp_pnum)'];// 申请的数目
			$remain = $equipnum - $applynum;
			$data[$n]['exp_snum'] = $oper_data['sum(exp_snum)'];
			$data[$n]['equip_num'] = $equipnum;
			$data[$n]['remain_num'] = $remain;
		}
//		dump($data);


		$holiday = Db::name('holiday')->where('exp_date',$dweek)->select();
		$hcou = count($holiday);
        for($cc=0;$cc<$hcou;$cc++){
            $holiday[$cc]['isAllow']=1;
        }
		$data = array_merge($data,$holiday);
		$counts = count($data);

		$formdata = array();
		$num = array();
		$jdata = array();

		for($i=0;$i<5;$i++){
			for($j=0;$j<6;$j++){
				$formdata[$i][$j] = ' ';
				$num[$i][$j] = 0;
			}
		}
//        dump($data);

		for($j=0;$j<$counts;$j++){
			$row = $data[$j]['exp_sec']-1;
			$col = $data[$j]['exp_week']-1;
			$formdata[$row][$col] = $data[$j]['exp_name'];
			$jdata[$j] = [
				"实验课程名称"	    =>	$data[$j]['exp_name'],
				"专业班级"		=>	$data[$j]['exp_class'],
				"人数"			=>	$data[$j]['exp_snum'],
				"指导教师"		=>	$data[$j]['exp_zdt'],
				"可用设备数"	    =>	$data[$j]['remain_num'],
				"总实验设备数"	    =>	$data[$j]['equip_num'],
                "isAllow"       =>  $data[$j]['isAllow'],
			];
			$arr[$row][$col][0] = $jdata[$j];
		}

		for($js=0;$js<5;$js++){
			$jsondata[$js] = [
				"mon"=>$arr[$js][0],
				"tues"=>$arr[$js][1],
				"wed"=>$arr[$js][2],
				"thur"=>$arr[$js][3],
				"fri"=>$arr[$js][4],
				"sat"=>$arr[$js][5]
			];
		}

		echo json(['code'=>0,'fday'=>$info['fday'],'termweek'=>$dweek,'msg'=>'','data'=>$jsondata])->getcontent();
	}
}