<?php
/**
 * 课程章节
 */
class SchsourceController extends Controller{
	public $ssmodel;
	public function init(){
		$this->ssmodel = new SchsourceModel();
		parent::init();
		
	}
	public function parameterRules(){
		return array(
			'schsourceListAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
			),	
			'itemListAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'q'  =>  array('name'=>'q','type'=>'string'),
				'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
				'q' =>	array('name'=>'page','default'=>'','type'=>'string'),
				'pid' =>	array('name'=>'pid','default'=>0,'type'=>'int'),
				'sid' =>	array('name'=>'sid','default'=>0,'type'=>'int'),
				'sourceid' =>	array('name'=>'sourceid','default'=>0,'type'=>'int'),
			),
			'selItemsAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'sourceid'  =>  array('name'=>'sourceid','require'=>TRUE,'type'=>'int'),
			),
			'crinfoAction' => array(
				'sourceid'  =>  array('name'=>'sourceid','require'=>TRUE,'type'=>'int'),
			),
			'classItemListAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'classids'  =>  array('name'=>'classids','require'=>TRUE,'type'=>'string'),
			),
			'classItemCountAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'classids'  =>  array('name'=>'classids','require'=>TRUE,'type'=>'string'),
				'sourcecrid'  =>  array('name'=>'sourcecrid','default'=>0,'type'=>'int'),
			),
			'deptItemListAction' => array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'classid'  =>  array('name'=>'classid','require'=>TRUE,'type'=>'int'),
            ),
			'saveClassitemAction'  =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'classid'  =>  array('name'=>'classid','require'=>TRUE,'type'=>'int'),
				'itemids'  =>  array('name'=>'itemids','default'=>array(),'type'=>'array'),
				'uids'  =>  array('name'=>'uids','default'=>array(),'type'=>'array'),
				'isclear'  =>  array('name'=>'isclear','default'=>0,'type'=>'int'),
			),
			'addUserPermisionAction'  =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'classid'  =>  array('name'=>'classid','require'=>TRUE,'type'=>'int'),
				'itemlist'  =>  array('name'=>'itemlist','default'=>array(),'type'=>'array'),
				'uids'  =>  array('name'=>'uids','default'=>array(),'type'=>'array'),
			),
			'delUserPermisionAction'  =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'oldclassid'  =>  array('name'=>'oldclassid','require'=>TRUE,'type'=>'int'),
				'uids'  =>  array('name'=>'uids','default'=>array(),'type'=>'array'),
			),
		);
	}
	
	/*
	企业选课列表
	*/
	public function schsourceListAction(){
		return $this->ssmodel->getSourceList(array('crid'=>$this->crid,'limit'=>1000));
	}
	/*
	课程列表
	*/
	public function itemListAction(){
		$param['crid'] = $this->crid;
		$param['sourceid'] = $this->sourceid;
		$selitems = $this->ssmodel->getSelectedItems($param);
		$itemids = array_column($selitems,'itemid');
		$itemids = implode(',',$itemids);
		
		// $iparam['pagesize'] = $this->pagesize;
		// $iparam['page'] = $this->page;
		// $iparam['q'] = $this->q;
		// $iparam['pid'] = $this->pid;
		// $iparam['sid'] = $this->sid;
		$itemlist = array();
		if(!empty($itemids)){
			$iparam['itemids'] = $itemids;
			$itemlist = $this->ssmodel->getItemList($iparam);
			$pimodel = new PayitemModel();
			
			$courselist = $pimodel->getFolderListByItemids($itemids);
			foreach($itemlist as $k=>$item){
				$itemid = $item['itemid'];
				$itemlist[$k]['img'] = $courselist[$itemid]['img'];
			}
		}
		return $itemlist;
	}
	
	/*
	根据sourceid获取学校信息
	*/
	public function selItemsAction(){
		$param['crid'] = $this->crid;
		$param['sourceid'] = $this->sourceid;
		return $this->ssmodel->getSelectedItems($param);
	}
	
	/*
	班级企业选课
	*/
	public function classItemListAction(){
		$param['crid'] = $this->crid;
		$param['classids'] = $this->classids;
		return $this->ssmodel->getClassItems($param);
	}
	
	/*
	班级企业选课数量
	*/
	public function classItemCountAction(){
		$param['crid'] = $this->crid;
		$param['classids'] = $this->classids;
		$param['sourcecrid'] = $this->sourcecrid;
		return $this->ssmodel->getClassItemCount($param);
	}
	
    /*
    企业部门选课
    */
    public function deptItemListAction(){
        return $this->ssmodel->getDeptItems($this->classid, $this->crid);
    }
	
	/*
	保存班级选课
	*/
	public function saveClassitemAction(){
		return $this->ssmodel->saveClassitem(array('classid'=>$this->classid,'itemids'=>$this->itemids,'crid'=>$this->crid,'uids'=>$this->uids,'isclear'=>$this->isclear));
	}
	
	/*
	添加用户,增加班级课程权限
	*/
	public function addUserPermisionAction(){
		$param['crid'] = $this->crid;
		$param['classid'] = $this->classid;
		$param['uids'] = $this->uids;
		$param['itemlist'] = $this->itemlist;
		$this->ssmodel->addUserpermision($param);
	}
	
	/*
	更换班级,删除老的班级数据
	*/
	public function delUserPermisionAction(){
		$param['crid'] = $this->crid;
		$param['classid'] = $this->oldclassid;
		$param['uids'] = $this->uids;
		$this->ssmodel->delUserpermision($param);
	}
	
	/*
	 *根据记录id,获取学校信息
	 */
	public function crinfoAction(){
		return $this->ssmodel->getCrinfoBySourceid($this->sourceid);
	}
	
}