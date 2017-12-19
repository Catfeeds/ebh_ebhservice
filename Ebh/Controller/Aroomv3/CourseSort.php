<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class CourseSortController extends Controller{
	public $spmodel;
	public $sortmodel;
    public function init(){
		$this->spmodel = new PaypackageModel();
		$this->sortmodel = new PaysortModel();
        parent::init();
		
    }
    public function parameterRules(){
        return array(
            'spListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'issimple'  =>  array('name'=>'issimple','type'=>'int'),
            ),
			'sortListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pids' => array('name'=>'pids','require'=>true,'type'=>'string'),
				'showbysort' => array('name'=>'showbysort','type'=>'int'),
				'issimple'  =>  array('name'=>'issimple','type'=>'int'),
            ),
			'addSpAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
				'pname'  =>  array('name'=>'pname','require'=>true,'type'=>'string'),
			),
			'addSortAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
				'sname'  =>  array('name'=>'sname','require'=>true,'type'=>'string'),
			),
			'saveSpNameAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
				'pname'  =>  array('name'=>'pname','require'=>true,'type'=>'string'),
			),
			'saveSortNameAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
				'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
				'sname'  =>  array('name'=>'sname','require'=>true,'type'=>'string'),
			),
			'changeSpOrderAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
				'isup'  =>  array('name'=>'isup','require'=>true,'type'=>'int'),
			),
			'changeSortOrderAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
				'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
				'isup'  =>  array('name'=>'isup','require'=>true,'type'=>'int'),
			),
			'deleteSpAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
			),
			'deleteSortAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
				'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
			),
			'spNameCheckAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pname'  =>  array('name'=>'pname','require'=>true,'type'=>'string'),
			),
			'sortNameCheckAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
				'sname'  =>  array('name'=>'sname','require'=>true,'type'=>'string'),
			)
        );
    }
	
	/*
	一级分类(服务包)列表
	*/
	public function spListAction(){
		$splist = $this->spmodel->getSimpleSpList(array('issimple'=>$this->issimple,'crid'=>$this->crid,'limit'=>1000,'status'=>1,'displayorder'=>'itype asc,displayorder asc,pid desc'));
		return $splist;
		
	}
	/*
	二级分类(服务分类)列表
	*/
	public function sortListAction(){
		$showbysort = $this->showbysort;
		$sortparam = array('issimple'=>$this->issimple,'pids'=>$this->pids,'order'=>'sdisplayorder asc','crid'=>$this->crid);
		if($showbysort !== NULL){
			$sortparam['showbysort'] = $showbysort;
		}
		$sortlist = $this->sortmodel->getSortsByPids($sortparam);
		return $sortlist;
		
	}
	/*
	添加一级分类
	*/
	public function addSpAction(){
		
		$displayorder = $this->spmodel->getCurdisplayorder(array('crid'=>$this->crid));
		$sparr['pname'] = $this->pname;
		$sparr['crid'] = $this->crid;
		$sparr['uid'] = $this->uid;
		$sparr['displayorder'] = $displayorder == NULL ? 0 : $displayorder+1;
		$pid = $this->spmodel->add($sparr);
		return array('pid'=>$pid);
	}
	
	/*
	添加二级分类
	*/
	public function addSortAction(){
		if(!$this->checksp()){
			return FALSE;
		}
		$sarr['pid'] = $this->pid;
		$sarr['sname'] = $this->sname;
		$sdisplayorder = $this->sortmodel->getCurdisplayorder(array('crid'=>$this->crid,'pid'=>$this->pid));
		$sarr['sdisplayorder'] = $sdisplayorder == NULL ? 0 : $sdisplayorder+1;
		$sid = $this->sortmodel->add($sarr);
		return array('sid'=>$sid);
	}
	
	/*
	修改一级分类名称
	*/
	public function saveSpNameAction(){
		if(!$this->checksp())
			return false;
		$status = $this->spmodel->edit(array('pid'=>$this->pid,'pname'=>$this->pname));
		return array('status'=>$status);
		// updateRoomCache($roominfo['crid'],'paypackage');
	}
	/*
	修改二级分类名称
	*/
	public function saveSortNameAction(){
		if(!$this->checksp())
			return false;
		$status = $this->sortmodel->edit(array('pid'=>$this->pid,'sname'=>$this->sname,'sid'=>$this->sid));
		return array('status'=>$status);
		// updateRoomCache($roominfo['crid'],'paypackage');
	}
	/*
	检验是否有服务包的权限
	*/
	private function checksp(){
		$sp = $this->spmodel->getPackByPid($this->pid);
		if(empty($sp) || $sp['crid'] != $this->crid)
			return false;
		return true;
	}
	
	
	/**
     * 更改服务包的排序
     */
	public function changeSpOrderAction() {
        $status = $this->spmodel->changeOrder($this->pid, $this->crid, $this->isup);
        return array('status'=>intval($status));
    }

    /**
     * 更改服务包下分类的排序
     */
    public function changeSortOrderAction() {
        if(!$this->checksp())
			return false;
        $status = $this->sortmodel->changeOrder($this->sid, $this->pid, $this->isup);
        return array('status'=>intval($status));
    }
	
	/*
	删除服务包
	*/
	public function deleteSpAction(){
		$delarr = array('crid'=>$this->crid,'pid'=>$this->pid);
		$res = $this->spmodel->hasCheck($delarr);
		if(empty($res['pid'])){
			$msg = '删除失败,该分类已不存在';
			$status = 0;
		}elseif($res['itemcount']>0){
			$msg = '该分类下还有课程,不能删除';
			$status = -1;
		}else{
			$this->spmodel->deletePack($this->pid);
			$this->spmodel->deleteSort($this->pid);
			$msg = '删除成功';
			$status = 1;
		}
		return array('status'=>$status);
	}
	
	
	/*
	删除服务包
	*/
	public function deleteSortAction(){
		
		$delarr = array('crid'=>$this->crid,'pid'=>$this->pid,'sid'=>$this->sid);
		$res = $this->sortmodel->hasCheck($delarr);
		if(empty($res)){
			$msg = '删除失败,该分类已不存在';
			$status = 0;
		}else{
			$this->sortmodel->del($this->sid);
			$this->sortmodel->setItemSidToZero($this->sid);
			$msg = '删除成功';
			$status = 1;
		}
		return array('status'=>$status);
	}
	
	/*
	一级分类名是否存在
	*/
	public function spNameCheckAction(){
		$param['crid'] = $this->crid;
		$param['pname'] = $this->pname;
		return $this->spmodel->nameCheck($param);
	}
	
	/*
	二级分类名是否存在
	*/
	public function sortNameCheckAction(){
		$param['pid'] = $this->pid;
		$param['sname'] = $this->sname;
		return $this->sortmodel->nameCheck($param);
	}
}