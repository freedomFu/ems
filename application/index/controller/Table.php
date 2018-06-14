<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;

class Table extends Base
{
	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-20 12:59:13
	 * @Description: 用于处理实验表，从数据库获取数据并传入到前端中
	 */
	private function index(){
		//需要前台传过来的数据：学期   哪个实验室   第几周   星期几  第几课时   
		//还有显示在表格上的数据：实验课程名称、专业班级、人数、指导老师、可用实验设备数、总实验设备数（数据由后台传入）
		//http://localhost:8080/Experimental_management_system/public/index/Table/index/index?exp_date=18&exp_week=5&exp_sec=5
		$info = input('get.');
		$exp_date = $info['exp_date'];
		$exp_week = $info['exp_week'];
		$exp_sec = $info['exp_sec'];
		$data = Db::name('exper')->where(array(
				'exp_date' => $exp_date,
				'exp_week' => $exp_week,
				'exp_sec'  => $exp_sec
			))->find();
		$this->assign('data',$data);
		dump($data);
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-28 08:52:08
	 * @Description: 实验是否已经递交实验申请书或者实验是否通过申请
	 */
	private function is_apply($id,$state,$str){
		$data = [
			$str => $state
		];
		$data = Db::name('exper')->where('id',$id)->update($data);
		return $data;
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-28 09:01:39
	 * @Description: 是否递交实验申请书
	 */
    private function apply(){
		$id = input('id');
		$state_apply = input('state_apply');
		$data = $this->is_apply($id,$state_apply,'exp_apply');
		dump($data);
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-28 08:59:07
	 * @Description: 实验是否通过申请
	 */
    private function allow(){
		$id = input('id');
		$state_allow = input('state_allow');
		$data = $this->is_apply($id,$state_allow,'exp_allow');
		dump($data);
	}
	
}