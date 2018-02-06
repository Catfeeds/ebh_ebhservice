<?php
/**
 * 课程章节
 */
class SectionController extends Controller{
	public $sectionmodel;
	// public $sortmodel;
	public function init(){
		$this->sectionmodel = new SectionModel();
		parent::init();
		
	}
	public function parameterRules(){
		return array(
			'sectionListAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'folderid' => array('name'=>'folderid','require'=>true,'type'=>'int'),
			),
			'editAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
				'sname'  =>  array('name'=>'sname','require'=>true,'type'=>'string'),
			),
			'addAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int'),
				'sname'  =>  array('name'=>'sname','require'=>true,'type'=>'string'),
			),
			'changeOrderAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
				'isup'  =>  array('name'=>'isup','require'=>true,'type'=>'int'),
			),
			'delAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
			),
			'updateOrderAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
				'displayorder' => array('name'=>'displayorder','require'=>true,'type'=>'int')
			)
			
		);
	}
	
	/*
	课程的章节列表
	*/
	public function sectionListAction(){
		return $this->sectionmodel->getSections(array('crid'=>$this->crid,'folderid'=>$this->folderid));
	}
	
	/*
	编辑章节
	*/
	public function editAction(){
		$wherearr = array('sid'=>$this->sid,'crid'=>$this->crid);
		$param = array('sname'=>$this->sname);
		return $this->sectionmodel->update($param,$wherearr);
	}
	/*
	添加章节
	*/
	public function addAction(){
		$maxorder = $this->sectionmodel->getMaxOrder($this->folderid);
		$param = array('folderid'=>$this->folderid,'crid'=>$this->crid,'sname'=>$this->sname,'dateline'=>SYSTIME,'displayorder'=>$maxorder+1,'coursewarecount'=>0);
		return $this->sectionmodel->insert($param);
	}
	/*
	章节上下移动
	*/
	public function changeOrderAction(){
		$param['crid'] = $this->crid;
		$param['sid'] = $this->sid;
		$param['isup'] = $this->isup;
		if($this->isup == 1){
			$res = $this->sectionmodel->changeOrder($param);
		} else {
			$res = $this->sectionmodel->changeOrder($param);
		}
		return $res;
	}
	
	/*
	 * 直接输入排序号修改
	*/
	public function updateOrderAction(){
		$param['crid'] = $this->crid;
		$param['sid'] = $this->sid;
		$param['displayorder'] = $this->displayorder;
		$res = $this->sectionmodel->updateOrder($param);
		return $res;
	}
	
	public function delAction(){
		$param['crid'] = $this->crid;
		$param['sid'] = $this->sid;
		return $res = $this->sectionmodel->del($param);
	}
}