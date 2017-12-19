<?php
/**
 * 登录日志.
 * Author: ckx
 */
class LoginlogController extends Controller{
	public $llmodel;
    public function init(){
		$this->llmodel = new LoginlogModel();
        parent::init();
		
    }
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'starttime' =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
				'endtime' =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
				'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
				'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
				'q' =>  array('name'=>'q','default'=>'','type'=>'string'),
				'citycode' =>  array('name'=>'citycode','default'=>0,'type'=>'int'),
				'screen' =>  array('name'=>'screen','default'=>'','type'=>'string'),
				'groupid' =>  array('name'=>'groupid','default'=>0,'type'=>'int'),
				'system' =>  array('name'=>'system','default'=>'','type'=>'string'),
				'browser' =>  array('name'=>'browser','default'=>'','type'=>'string'),
				'uids' =>  array('name'=>'uids','default'=>'','type'=>'string'),
				'order' =>  array('name'=>'order','default'=>'','type'=>'string'),
            ),
			'regionListAction' =>  array(
                'pcode'  =>  array('name'=>'pcode','default'=>-1,'type'=>'int'),
                'codes'  =>  array('name'=>'codes','default'=>'','type'=>'string'),
            ),
			'clientListAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
            ),
			'screenListAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
            ),
			'distributeListAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'starttime' =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
				'endtime' =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
				'citycode' =>  array('name'=>'citycode','default'=>0,'type'=>'int'),
				'allcities' =>  array('name'=>'allcities','default'=>0,'type'=>'int'),
            ),
			'signListAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'starttime' =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
				'endtime' =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
				'codes' =>  array('name'=>'codes','default'=>'','type'=>'string'),
            ),
			'chooseIntentionAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'uid' =>  array('name'=>'uid','require'=>true,'type'=>'int'),
				'intention' =>  array('name'=>'intention','default'=>0,'type'=>'int'),
            ),
			
        );
    }
	
	/*
	登录日志列表
	*/
	public function listAction(){
		$param['crid'] = $this->crid;
		$param['starttime'] = $this->starttime;
		$param['endtime'] = $this->endtime;
		$param['q'] = $this->q;
		$param['citycode'] = $this->citycode;
		$param['screen'] = $this->screen;
		$param['groupid'] = $this->groupid;
		$param['system'] = $this->system;
		$param['browser'] = $this->browser;
		$param['pagesize'] = $this->pagesize;
		$param['page'] = $this->page;
		$param['uids'] = $this->uids;
		$param['order'] = $this->order;
		$loglist = $this->llmodel->getLogList($param);
		$logcount = $this->llmodel->getLogCount($param);
		return array('loglist'=>$loglist,'logcount'=>$logcount);
	}
	
	/*
	地区列表
	*/
	public function regionListAction(){
		$param['pcode'] = $this->pcode;
		$param['codes'] = $this->codes;
		return $this->llmodel->getRegionList($param);
	}
	
	/*
	设备列表
	*/
	public function clientListAction(){
		return $this->llmodel->getClientList($this->crid);
	}
	
	/*
	分辨率列表
	*/
	public function screenListAction(){
		return $this->llmodel->getScreenList($this->crid);
	}
	
	/*
	地域分布列表
	*/
	public function distributeListAction(){
		$param['crid'] = $this->crid;
		$param['starttime'] = $this->starttime;
		$param['endtime'] = $this->endtime;
		$param['citycode'] = $this->citycode;
		$param['allcities'] = $this->allcities;
		return $this->llmodel->getDistributeList($param);
	}
	
	/*
	签到列表
	*/
	public function signListAction(){
		$param['crid'] = $this->crid;
		$param['starttime'] = $this->starttime;
		$param['endtime'] = $this->endtime;
		$param['codes'] = $this->codes;
		return $this->llmodel->getSignListByCity($param);
	}
	
	/*
	选择意向
	*/
	public function chooseIntentionAction(){
		$param['crid'] = $this->crid;
		$param['uid'] = $this->uid;
		$param['intention'] = $this->intention;
		return $this->llmodel->update($param);
	}
}