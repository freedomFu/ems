<?php
namespace app\index\controller;
use think\Db;
use PHPExcel_IOFactory;
use PHPExcel;
use PHPExcel_Style_Border;
use app\index\controller\Base;

class Excel extends Base
{
	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-14 21:50:38
	 * @Description: 合并单元格
	 */
	private function mergeExcel($phpsheet,$mergestr){
		$phpsheet->mergeCells($mergestr);
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-15 16:56:00
	 * @Description: 设置字体
	 */
	private function setFont($phpsheet,$cell,$n,$size){
		for($i=0;$i<$n;$i++){
			$phpsheet->getStyle($cell[$i])->getFont()->setSize($size[$i]);
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-16 08:19:43
	 * @Description: 设置单元格边框
	 */
	private function setborder($phpsheet,$style,$colorstr,$cellstr,$n){
		/*$border = array(
		       'borders' => array (  
		             'outline' => array (  
		                   'style' => PHPExcel_Style_Border::BORDER_THIN,   //设置border样式  
		                   //'style' => PHPExcel_Style_Border::BORDER_THICK,  另一种样式  
		                   'color' => array ('argb' => 'FF000000'),//设置border颜色  
		            ),  
		      ),  
		); */
		$border = array(
			'borders' => array(
				'outline' => array(
					'style' => $style,
					'color' => array('argb' => $colorstr),
				),
                'allborders' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                ),
			),
		);
		for($i=0;$i<$n;$i++){
			$phpsheet->getStyle($cellstr[$i])->applyFromArray($border);
		}
		
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-14 21:48:16
	 * @Description: 设置宽度
	 */
	private function setWidth($phpsheet,$cell,$n,$width){
		for($i=0;$i<$n;$i++){
			$phpsheet->getColumnDimension($cell[$i])->setWidth($width[$i]);
		}
	}

	/**
	 * @Author:      居中
	 * @DateTime:    2017-12-15 16:47:43
	 * @Description: Description
	 */
	private function setCenter($phpsheet,$cell,$n){
		for($i=0;$i<$n;$i++){
			$phpsheet->getStyle($cell[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpsheet->getStyle($cell[$i])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-14 21:49:47
	 * @Description: 设置高度
	 */
	private function setHeight($phpsheet,$cell,$n,$height){
		for($i=0;$i<$n;$i++){
			$phpsheet->getRowDimension($cell[$i])->setRowHeight($height[$i]);
		}
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2018-04-08 16:43:39
	 * @Description: 获取最新的数据。本人提交的还未提交申请书的 前五条
	 */
	private function getLimitData($num){
		$user = session('ems_name'); //获取当前登录的用户
		$res = Db::name('exper')
            ->field('exp_xq,exp_name,exp_bz,exp_xs,exp_class,exp_snum,exp_date,exp_week,exp_sec')
            ->where(['exp_user'=>$user,'exp_apply'=>0,'undo'=>0])
            ->limit($num)
            ->order('exp_time desc')
            ->select();
		return $res;
	}

	/**
	 * @Author:      fyd
	 * @DateTime:    2017-12-14 21:44:20
	 * @Description: 利用PHPExcel生成实验申请表
	 */
    public function index()
    {
        $path = dirname(__FILE__);
        $PHPExcel = new PHPExcel();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle('实验申请表');

        //设置excel表格默认属性
        $PHPSheet->getDefaultStyle()->getFont()->setSize(14);
        $num = $_POST['output_num']; //获取get数据
//        $num = 10;
        $data = $this->getLimitData($num);
        $count = count($data);
        $bzh = 6+$count; //备注行起始处

       	//设置其他属性
       	//合并单元格
       	$this->mergeExcel($PHPSheet,'A1:H2');
       	$this->mergeExcel($PHPSheet,'A3:H3');
       	$this->mergeExcel($PHPSheet,'D4:F4');
       	$this->mergeExcel($PHPSheet,'C5:D5');

       	for($i=0;$i<$count;$i++){
            $this->mergeExcel($PHPSheet,'C'.($i+6).':D'.($i+6));
        }

        $this->mergeExcel($PHPSheet,'B'.$bzh.':H'.$bzh);
        $this->mergeExcel($PHPSheet,'C'.($bzh+1).':D'.($bzh+1));
        $this->mergeExcel($PHPSheet,'G'.($bzh+1).':H'.($bzh+1));

       	//设置字体大小
       	$fcell = ['A1'];
       	$size = ['20'];
       	$this->setFont($PHPSheet,$fcell,1,$size);

       	//设置宽高
       	$wcell = ['A','B','C','D','E','F','G','H'];
       	$width = [30,30,10,10,10,10,10,10];
       	$this->setWidth($PHPSheet,$wcell,count($wcell),$width);
       	$this->setCenter($PHPSheet,$wcell,count($wcell));
       	$wce = ['A1','A3','D4','C'.($bzh+1),'G'.($bzh+1)];
       	$this->setCenter($PHPSheet,$wce,count($wce));
        //gei
       	$tablecell = array();
        $tableheight = array();
       	for($i=0;$i<$count;$i++){
       	    $tablecell[$i] = $i+6;
            $tableheight[$i] = 26;
        }
        $this->setHeight($PHPSheet,$tablecell,count($tablecell),$tableheight);

       	$hcell = [$bzh];
       	$height = [40];
       	$this->setHeight($PHPSheet,$hcell,count($hcell),$height);
       	//设置边框
//       	$border = ['A5:A11','B5:B11','C5:C11','D5:D11','E5:E11','F5:F11','G5:G11','H5:H11','A5:H5','A6:H6','A7:H7','A8:H8','A9:H9','A10:H10','A11:H11'];
       	$border = ['A5:H'.$bzh];
        $this->setborder($PHPSheet,PHPExcel_Style_Border::BORDER_THIN,'FF000000',$border,count($border));

        /**
         * 新添加
         */
        $xq = Db::name('set')->where('setname','xq')->find();
        $exp_xq = $xq['setvalue'];
        $xq_arr = explode('-',$exp_xq);
        if($xq_arr[2]==1){
            $xq_arr[2]="一";
        }else{
            $xq_arr[2]="二";
        }
        $bt = $xq_arr[0].' - '.$xq_arr[1].'学年第 '.$xq_arr[2].' 学期'; //表头
       	//填写数据
       	$PHPSheet->setCellValue('A1','计算机系实验中心实验任务书');
       	$PHPSheet->setCellValue('A3',$bt);
       	$PHPSheet->setCellValue('A4','实验课程');
       	$PHPSheet->setCellValue('B4','申请人：'.session('ems_name'));
       	$PHPSheet->setCellValue('D4','实验室名称');
       	$PHPSheet->setCellValue('A5','实验/授课名称');
       	$PHPSheet->setCellValue('B5','实验/授课内容');
       	$PHPSheet->setCellValue('C5','班级');
       	$PHPSheet->setCellValue('E5','人数');
       	$PHPSheet->setCellValue('F5','周次');
       	$PHPSheet->setCellValue('G5','星期');
       	$PHPSheet->setCellValue('H5','节次');
       	$PHPSheet->setCellValue('A'.$bzh,'备注:');
       	$PHPSheet->setCellValue('A'.($bzh+1),'填报人：');
       	$PHPSheet->setCellValue('B'.($bzh+1),'教研室主任签字:');
       	$PHPSheet->setCellValue('C'.($bzh+1),'系教学主任签字:');
       	$PHPSheet->setCellValue('G'.($bzh+1),date('Y年m月d日',time()));

       	for($i=0;$i<$count;$i++){
       		$PHPSheet->setCellValue('A'.(6+$i),$data[$i]['exp_name']);
       		$PHPSheet->setCellValue('B'.(6+$i),$data[$i]['exp_bz']);
       		$PHPSheet->setCellValue('C'.(6+$i),$data[$i]['exp_class']);
//       		$PHPSheet->setCellValue('D'.(6+$i),$data[$i]['exp_class']);
       		$PHPSheet->setCellValue('E'.(6+$i),$data[$i]['exp_snum']);
       		$PHPSheet->setCellValue('F'.(6+$i),$data[$i]['exp_date']);
       		$PHPSheet->setCellValue('G'.(6+$i),$data[$i]['exp_week']);
       		$PHPSheet->setCellValue('H'.(6+$i),$data[$i]['exp_sec']);
       	}

        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="实验申请表.xlsx"');
        header('Cache-Control: max-age=0');
        $PHPWriter->save("php://output");
    }

    /**************************************  生成课程表-6.9  *************************************************/
    //设置单元格自动换行
    private function setWrap($phpsheet,$data){
        $count = count($data);
        for($i=0;$i<$count;$i++){
            $phpsheet->getStyle($data[$i])->getAlignment()->setWrapText(true);
        }
    }

    private function a_array_unique($array)//写的比较好
    {
        $out = array();
        foreach ($array as $key=>$value) {
            if (!in_array($value, $out))
            {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    /**
     * @Author:      fyd
     * @DateTime:    2018/6/9 19:46
     * @Description: 根据周数和实验室的内容 生成数据
     */
    private function displayTable($lab_id,$exp_week){
        $table = 'exper';
        $where['elab_id']=$lab_id;
        $where['exp_date']=$exp_week;
        $where['exp_apply']=1;
        $where['exp_isallow']=1;
        $where['undo']=0;
        $field1 = 'exp_name,exp_class,exp_snum,exp_zdt,exp_week,exp_sec';
        $field2 = 'exp_week,exp_sec';
        $tableData = Db::name($table)
                        ->field($field2)
                        ->where($where)
                        ->select();
        $weekarr = $this->a_array_unique($tableData);
        $weekcou = count($weekarr);

        $dataArr = [[],[],[],[],[],[]];
        for($w=0;$w<$weekcou;$w++){
            $tableArr = Db::name($table)
                ->field($field1)
                ->where($where)
                ->where($weekarr[$w])
                ->select();
            for($i=0;$i<5;$i++){//节数
                for($j=0;$j<6;$j++){//星期几
                    if(($j+1)==$weekarr[$w]['exp_week'] && ($i+1)==$weekarr[$w]['exp_sec']){
                        $dataArr[$j][$i] = $tableArr;
                    }
                }
            }
        }

        return $dataArr;
    }

    public function test(){
        $lab_id = $_GET['lab_id'];
        $exp_week = $_GET['week']; //获取周数
        $tableArr = $this->displayTable($lab_id,$exp_week);
        $col = ['B','C','D','E','F','G'];
        $row = [13,14,15,16,17];
        dump($tableArr);
        $count = count($tableArr);
        for($i=0;$i<$count;$i++){ //一共六列
            foreach( array_keys($tableArr[$i]) as $k1 ) {
                $cou = count($tableArr[$i][$k1]);
                echo $col[$i].$row[$k1];
                for($c=0;$c<$cou;$c++){
                    //$i+1 第几列
                    $exp_name = $tableArr[$i][$k1][$c]['exp_name'];
                    $exp_class = $tableArr[$i][$k1][$c]['exp_class'];
                    $exp_snum = $tableArr[$i][$k1][$c]['exp_snum'];
                    $exp_zdt = $tableArr[$i][$k1][$c]['exp_zdt'];
                    $expStr = $exp_name."\n" .$exp_class."\n"
                        .$exp_snum."人\n".$exp_zdt;
                    echo $expStr.'<br>';
                }
                echo '<br>';
            }
        }
    }

    /**
     * @Author:      fyd
     * @DateTime:    2018/6/9 21:33
     * @Description: 生成excel
     */
    public function excelTable(){
        //获取数据
        $lab_id = $_GET['lab_id'];
        $exp_week = $_GET['week']; //获取周数
        $tableArr = $this->displayTable($lab_id,$exp_week);

        //取出实验室其他数据
        $lab = Db::name('lab')
            ->field('lab_name,lab_area')
            ->where('id',$lab_id)
            ->find();
        $lab_name = $lab['lab_name'];  //获取实验室名称
        $lab_area = $lab['lab_area'];  //获取实验室位置


        /* 获取学期 */
        $xq = Db::name('set')->where('setname','xq')->find();
        $exp_xq = $xq['setvalue'];
        $xq_arr = explode('-',$exp_xq);
        if($xq_arr[2]==1){
            $xq_arr[2]="一";
        }else{
            $xq_arr[2]="二";
        }
        $bt = $xq_arr[0].'-'.$xq_arr[1].'学年第 '.$xq_arr[2].' 学期'.$lab_name.'值班表'; //表头

        /* 获取首日 与 最后一天 */
        $fday = Db::name('set')->where('setname','fday')->find();
        $exp_fday = $fday['setvalue'];
        $fday_str = strtotime($exp_fday);
        $day_away = ($exp_week-1)*7*3600*24;
        $week_fday = date('m.d',$fday_str+$day_away);
        $week_final = date('m.d',$fday_str+$day_away+5*3600*24);
        $week_day = $week_fday.' - '.$week_final;

        //生成excel
        $path = dirname(__FILE__);
        $PHPExcel = new PHPExcel();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle($lab_name.'实验室值班表');

        //设置excel表格默认属性
        $PHPSheet->getDefaultStyle()->getFont()->setSize(12);

        $wrap_arr = [
            'A4','B13','B14','B15','B16','B17'
            ,'C13','C14','C15','C16','C17'
            ,'D13','D14','D15','D16','D17'
            ,'E13','E14','E15','E16','E17'
            ,'F13','F14','F15','F16','F17'
            ,'G13','G14','G15','G16','G17'
            ,'H13','H14','H15','H16','H17'
        ];
        $count=count($wrap_arr);
        //改变字体
        for($i=0;$i<$count;$i++){
            $PHPSheet->getStyle($wrap_arr[$i])->getFont()->setSize(10);
        }
        $this->setWrap($PHPSheet,$wrap_arr);
        $PHPSheet->getStyle('A4')->getAlignment()->setWrapText(true);

        //设置其他属性
        //合并单元格
        $this->mergeExcel($PHPSheet,'A1:H2');
        $this->mergeExcel($PHPSheet,'B3:C3');
        $this->mergeExcel($PHPSheet,'G3:H3');
        $this->mergeExcel($PHPSheet,'A4:A7');
        $this->mergeExcel($PHPSheet,'A9:H10');
        $this->mergeExcel($PHPSheet,'B11:C11');
        $this->mergeExcel($PHPSheet,'G11:H11');
        $this->mergeExcel($PHPSheet,'G18:H18');
        /*$this->mergeExcel($PHPSheet,'H13:H17');
        $PHPSheet->getStyle('H13')->getBorders()->setDiagonalDirection(\PHPExcel_Style_Borders::DIAGONAL_DOWN );
        $PHPSheet->getStyle('H13')->getBorders()->getDiagonal()-> setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);*/

        //设置字体大小
        $fcell = ['A1','A9','A4'];
        $size = ['18','18','12'];
        $this->setFont($PHPSheet,$fcell,3,$size);

        //设置宽高
        $wcell = ['A','B','C','D','E','F','G','H'];
        $width = [10,10,10,10,10,10,10,10];
        $this->setWidth($PHPSheet,$wcell,8,$width);
        $this->setCenter($PHPSheet,$wcell,8);
        $wce = ['A1','B3','A4','A9','B11','G11','G18'];
        $this->setCenter($PHPSheet,$wce,7);

        $hcell = [5,6,7,8,13,14,15,16,17];
        $height = [24,24,24,24,80,80,80,80,80];
        $this->setHeight($PHPSheet,$hcell,count($hcell),$height);
        //设置边框
        $border = ['A4:A7','B4:B7','C4:C7','D4:D7','E4:E7','F4:F7','G4:G7','H4:H7'
            ,'A4:H4','A5:H5','A6:H6','A7:H7','A12:A17','B12:B17','C12:C17'
            ,'D12:D17','E12:E17','F12:F17','G12:G17','H12:H17','A12:H12'
            ,'A13:H13','A14:H14','A15:H15','A16:H16','A17:H17'
        ];
        $this->setborder($PHPSheet,PHPExcel_Style_Border::BORDER_THIN,'FF000000',$border,count($border));
        //填写数据
        $PHPSheet->setCellValue('A1',$bt);
        $PHPSheet->setCellValue('B3','第'.$exp_week.'周');
        $PHPSheet->setCellValue('F3','日期：');
        $PHPSheet->setCellValue('G3',$week_day);
        $PHPSheet->setCellValue('A4',$lab_name.'值班表');
        $PHPSheet->setCellValue('C4','一');
        $PHPSheet->setCellValue('D4','二');
        $PHPSheet->setCellValue('E4','三');
        $PHPSheet->setCellValue('F4','四');
        $PHPSheet->setCellValue('G4','五');
        $PHPSheet->setCellValue('H4','六');
        $PHPSheet->setCellValue('B5','上午');
        $PHPSheet->setCellValue('B6','下午');
        $PHPSheet->setCellValue('B7','晚上');
        $PHPSheet->setCellValue('A9',$lab_name.'实验课表');
        $PHPSheet->setCellValue('B11','第'.$exp_week.'周');
        $PHPSheet->setCellValue('F11','日期：');
        $PHPSheet->setCellValue('G11',$week_day);

        $PHPSheet->setCellValue('A12','星期');
        $PHPSheet->setCellValue('A13','第一大节');
        $PHPSheet->setCellValue('A14','第二大节');
        $PHPSheet->setCellValue('A15','第三大节');
        $PHPSheet->setCellValue('A16','第四大节');
        $PHPSheet->setCellValue('A17','第五大节');
        $PHPSheet->setCellValue('B12','一');
        $PHPSheet->setCellValue('C12','二');
        $PHPSheet->setCellValue('D12','三');
        $PHPSheet->setCellValue('E12','四');
        $PHPSheet->setCellValue('F12','五');
        $PHPSheet->setCellValue('G12','六');
        $PHPSheet->setCellValue('H12','日');
        $PHPSheet->setCellValue('F18','地点：');
        $PHPSheet->setCellValue('G18',$lab_area);

        $col = ['B','C','D','E','F','G'];
        $row = [13,14,15,16,17];
        $count = count($tableArr);
        for($i=0;$i<$count;$i++){ //一共六列
            foreach( array_keys($tableArr[$i]) as $k) {
                $cou = count($tableArr[$i][$k]);
                //$i+1 第几列
                $expStr="";
                for($c=0;$c<$cou;$c++) {
                    $exp_name = $tableArr[$i][$k][$c]['exp_name'];
                    $exp_class = $tableArr[$i][$k][$c]['exp_class'];
                    $exp_snum = $tableArr[$i][$k][$c]['exp_snum'];
                    $exp_zdt = $tableArr[$i][$k][$c]['exp_zdt'];
                    $expStr .= $exp_name . "\n" . $exp_class . "\n"
                        . $exp_snum . "人\n" . $exp_zdt."\n\n";
                    $PHPSheet->setCellValue($col[$i] . $row[$k], $expStr);
                }
            }
        }

        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$lab_name.'实验申请表第'.$exp_week.'周.xlsx"');
        header('Cache-Control: max-age=0');
        $PHPWriter->save("php://output");
    }

}
