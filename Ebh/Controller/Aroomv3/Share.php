<?php
/**
 * 分销
 */
class ShareController extends Controller{
	private $sharemodel;
	
	public function init(){
		$this->sharemodel = new ShareModel();
        $this->roomUserModel = new RoomUserModel();
		parent::init();
		
	}
	public function parameterRules(){
		return array(
			'upSettingsAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
				'isshare'  =>  array('name'=>'isshare','default'=>NULL,'type'=>'int'),
				'sharepercent'  =>  array('name'=>'sharepercent','default'=>NULL,'type'=>'float'),
			),
			'addUserPercentAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
				'uids'  =>  array('name'=>'uids','require'=>true,'type'=>'array'),
				'percent'  =>  array('name'=>'percent','default'=>0,'type'=>'float'),
			),
			'editUserPercentAction'   =>  array(
                'did'  =>  array('name'=>'did','require'=>true,'type'=>'int'),
				'percent'  =>  array('name'=>'percent','default'=>0,'type'=>'float'),
			),
            'delUserPercentAction'   =>  array(
				'did'  =>  array('name'=>'did','require'=>true,'type'=>'int'),
			),
			'getSettingsAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
			),
            'getOnePercentAction'   =>  array(
				'did'  =>  array('name'=>'did','require'=>true,'type'=>'int'),
			),
            'getPercentListAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'pagesize' =>	array('name'=>'pagesize','default'=>50,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>0,'type'=>'int'),

			),
            'shareListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'starttime' =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime' =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
                'pagesize' =>	array('name'=>'pagesize','default'=>50,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
                'q' =>  array('name'=>'q','default'=>'','type'=>'string'),
                'providercrid'  =>  array('name'=>'providercrid','type'=>'int'),
                'roomtype' =>  array('name'=>'roomtype','default'=>'edu','type'=>'string'),
            ),
            'sourceListsAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
            ),
            'roomStrudentSumAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int','min'=>1),
            ),
			
		);
	}
	public function getSettingsAction(){
		return $this->sharemodel->getSettings($this->crid);
	}
	/*
	修改基本设置
	*/
	public function upSettingsAction(){
        $param = array();
		$param['crid'] = $this->crid;
		if($this->isshare !== NULL){
			$param['isshare'] = $this->isshare;
		}
		if($this->sharepercent !== NULL){
			$param['sharepercent'] = $this->sharepercent;
		}
		return $this->sharemodel->upSettings($param);
	}
	
	/*
	批量添加用户分销比
	*/
	public function addUserPercentAction(){
        $param = array();
		$param['uids'] = $this->uids;
		$param['crid'] = $this->crid;
		$param['percent'] = $this->percent;
		return $this->sharemodel->addUserPercent($param);
	}
	
	/*
	编辑单个用户分销比
	*/
	public function editUserPercentAction(){
        $param = array();
		$param['did'] = $this->did;
		$param['percent'] = $this->percent;
		return $this->sharemodel->editUserPercent($param);
	}
	/*
	删除单个用户分销比
	*/
	public function delUserPercentAction(){
        $param = array();
		$param['did'] = $this->did;
		return $this->sharemodel->delUserPercent($param);
	}
	/*
	获取单个用户分销比
	*/
	public function getOnePercentAction(){
		return $this->sharemodel->getOnePercent($this->did);
	}
	/**
	*获取网校用户分销比列表
	*/
	public function getPercentListAction(){
	    $param = array();
		$param['crid'] = $this->crid;
        $param['pagesize'] = $this->pagesize;
        $param['page'] = $this->page;
        $percentlist = $this->sharemodel->getPercentList($param);
        $percentcount = $this->sharemodel->getPercentCount($param);
        if(empty($percentcount) && empty($percentlist)){
            return array('percentlist'=>array(),'percentcount'=>0);
        }else{
            return array('percentlist'=>$percentlist,'percentcount'=>$percentcount);
        }
	}
    /**
     *获取网校用户分销列表
     */
    public function shareListAction(){
        $param = array();
        $param['crid'] = $this->crid;
        if(!empty($this->starttime)){
            $param['starttime'] = $this->starttime;
        }
        if(!empty($this->endtime)){
            $param['endtime'] = $this->endtime;
        }
        if($this->q !== NULL && $this->q !== ''){
            $param['q'] = $this->q;
        }
        $param['pagesize'] = $this->pagesize;
        $param['page'] = $this->page;
        if(isset($this->providercrid)){
            $param['providercrid'] = $this->providercrid;
        }
        $sharelist = $this->sharemodel->shareList($param);
        $sharecount = $this->sharemodel->shareCount($param);
        $sharelist = !empty($sharelist) ? $sharelist : array();
        $sharecount = !empty($sharecount) ? $sharecount : 0;
        return array('sharelist'=>$sharelist,'sharecount'=>$sharecount);
    }

    /**
     *获取分销返现来源网校列表
     */
    public function sourceListsAction(){
        $param = array();
        $param['crid'] = $this->crid;
        $sourcelists = $this->sharemodel->sourceLists($param);
        $sourcelists = !empty($sourcelists) ? $sourcelists : array();
        return $sourcelists;
    }
    /**
     *获取当前班级学生
     */
    public function roomStrudentSumAction(){
        $param = array();
        $param['crid'] = $this->crid;
        $param['classid'] = $this->classid;
        $roomstudent = $this->roomUserModel->getStudentList($param);
        $roomstudent = !empty($roomstudent) ? $roomstudent : 0;
        return $roomstudent;
    }
}