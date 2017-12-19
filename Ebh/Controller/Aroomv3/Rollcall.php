<?php
/**
 * 点名系统
 */
class RollcallController extends Controller{
	public function init(){
		$this->rcmodel = new RollcallModel();
        $this->classesModel = new ClassesModel();
		parent::init();
		
	}
	public function parameterRules(){
		return array(
			'listAction'  =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'uid'  =>  array('name'=>'uid','detault'=>0,'type'=>'int'),
				'rid'  =>  array('name'=>'rid','detault'=>0,'type'=>'int'),
				'pagesize'=> array('name'=>'pagesize','detault'=>0,'type'=>'int'),
				'page'=> array('name'=>'page','detault'=>0,'type'=>'int'),
				'q'=> array('name'=>'q','detault'=>'','type'=>'string'),
			),
			'addAction' =>array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'uid' => array('name'=>'uid','require'=>TRUE,'type'=>'int'),
				'starttime'=> array('name'=>'starttime','detault'=>0,'type'=>'int'),
				'endtime'=> array('name'=>'endtime','detault'=>0,'type'=>'int'),
				'cwid'=> array('name'=>'cwid','require'=>TRUE,'type'=>'int'),
				'itemid'=> array('name'=>'itemid','require'=>TRUE,'type'=>'int'),
				'rname'=> array('name'=>'rname','detault'=>'','type'=>'string'),
			),
			'addUserAction' =>array(
				'rid'  =>  array('name'=>'rid','require'=>TRUE,'type'=>'int'),
				'uid'  =>  array('name'=>'uid','require'=>TRUE,'type'=>'int'),
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'uids'=> array('name'=>'uids','detault'=>'','type'=>'string'),
			),
			'editAction'=>array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'uid'  =>  array('name'=>'uid','require'=>TRUE,'type'=>'int'),
				'starttime'=> array('name'=>'starttime','detault'=>0,'type'=>'int'),
				'endtime'=> array('name'=>'endtime','detault'=>0,'type'=>'int'),
				'cwid'=> array('name'=>'cwid','require'=>TRUE,'type'=>'int'),
				'itemid'=> array('name'=>'itemid','require'=>TRUE,'type'=>'int'),
				'rname'=> array('name'=>'rname','detault'=>'','type'=>'string'),
				'rid' =>  array('name'=>'rid','require'=>TRUE,'type'=>'int'),
			),
			'delAction'=>array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'rid' =>  array('name'=>'rid','require'=>TRUE,'type'=>'int'),
			),
			'callAction' =>array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'uid' => array('name'=>'uid','require'=>TRUE,'type'=>'int'),
				'rid' => array('name'=>'rid','require'=>TRUE,'type'=>'int'),
				'dateline'=>array('name'=>'dateline','default'=>0,'type'=>'int'),
				'isclear' =>array('name'=>'isclear','default'=>0,'type'=>'int'),
			),
			'rollListAction'  =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'rid'  =>  array('name'=>'rid','require'=>TRUE,'type'=>'int'),
				'called' =>array('name'=>'called','detault'=>NULL,'type'=>'int'),
				'pagesize'=> array('name'=>'pagesize','detault'=>0,'type'=>'int'),
				'page'=> array('name'=>'page','detault'=>0,'type'=>'int'),
				'q'=> array('name'=>'q','detault'=>'','type'=>'string'),
			),
			'statsAction'  =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'uids' =>array('name'=>'uids','require'=>TRUE,'type'=>'string'),
			),
            'classroomStatusAction'  =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
			),
            'subDepartmentAction' =>array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'classid'  =>  array('name'=>'classid','type'=>'int'),
            ),
            'classListAction'  =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'rids'  =>  array('name'=>'rids','type'=>'string'),
                'begintime'  =>  array('name'=>'begintime','type'=>'int'),
                'lasttime'  =>  array('name'=>'lasttime','type'=>'int'),
                'pagesize'=> array('name'=>'pagesize','detault'=>0,'type'=>'int'),
                'page'=> array('name'=>'page','detault'=>0,'type'=>'int'),
                'q'=> array('name'=>'q','detault'=>'','type'=>'string'),
                'classids'  =>  array('name'=>'classids','type'=>'array')
            ),
			'settingsAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
			),
			'updateSettingsAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'minusernum' => array('name'=>'minusernum','detault'=>NULL,'type'=>'int'),
				'addusertime' => array('name'=>'addusertime','detault'=>NULL,'type'=>'int'),
				'calltime' => array('name'=>'calltime','detault'=>NULL,'type'=>'int'),
			),
		);
	}
	
	/*
	点名，课件列表
	*/
	public function listAction(){
		$param = array('crid'=>$this->crid,'pagesize'=>$this->pagesize,'page'=>$this->page,'rid'=>$this->rid,'uid'=>$this->uid);
		if($this->q !== NULL && $this->q !== ''){
			$param['q'] = $this->q;
		}
		$cwlist = $list = $this->getCwList($param);
		
		$cwcount = $this->rcmodel->getCwCount($param);
		
		return array('cwlist'=>$cwlist,'cwcount'=>$cwcount);
	}
	
	private function getCwList($param){
		$cwlist = $this->rcmodel->getCwList($param);
		if(!empty($cwlist)){
			$cwids = array_column($cwlist,'cwid');
			$cwmodel = new CoursewareModel();
			$cwinfolist = $cwmodel->getSimpleInfoByIds($cwids,TRUE);//课件信息
			
			$rids = array_column($cwlist,'rid');
			$rids = implode(',',$rids);
			$countlist = $this->rcmodel->getCalledCw(array('rids'=>$rids,'crid'=>$param['crid']));//人数，点名信息
			$setting = $this->getSettings();
			foreach($cwlist as &$cw){
				$cwid = $cw['cwid'];
				$rid = $cw['rid'];
				$cw['cwlength'] = empty($cwinfolist[$cwid])?'':$cwinfolist[$cwid]['cwlength'];
				$cw['title'] = empty($cwinfolist[$cwid])?'':$cwinfolist[$cwid]['title'];
				$cw['totalcount'] = empty($countlist[$rid])?0:$countlist[$rid]['count'];
				$cw['calledcount'] = empty($countlist[$rid])?0:$countlist[$rid]['calledcount'];
				
				//人数达标可以点名
				$cw['userstatus'] = $cw['totalcount'] >= $setting['minusernum']?1:0;
				
				//上课前N久截止报名
				$cw['addstatus'] = $cw['starttime'] - $setting['addusertime'] > SYSTIME ?1:0;
				$cwlength = empty($cwinfolist[$cwid])?0:$cwinfolist[$cwid]['cwlength'];
				
				//开课+课件时长 前后N久可以点名
				$cw['callstatus'] = ($cw['starttime']-$setting['calltime'] <SYSTIME && 
									$cw['starttime']+$setting['calltime'] >SYSTIME) ?1:0;
				//点名结束
				$cw['finished'] = !empty($cw['calledcount']) && $cw['starttime']+$setting['calltime'] <SYSTIME ? 1:0;
			}
		}
		return $cwlist;
	}
	/*
	添加点名课
	*/
	public function addAction(){
		$param['crid'] = $this->crid;
		$param['uid'] = $this->uid;
		$param['starttime'] = $this->starttime;
		$param['endtime'] = $this->endtime;
		$param['cwid'] = $this->cwid;
		$param['itemid'] = $this->itemid;
		$param['rname'] = $this->rname;
		return $this->rcmodel->add($param);
	}
	
	/*
	去报名，给点名计划添加学生
	*/
	public function addUserAction(){
		
		$checkarr['rid'] = $this->rid;
		$checkarr['crid'] = $this->crid;
		$cwlist = $this->getCwList($checkarr);
		if(empty($cwlist)){
			return FALSE;
		}
		$cw = $cwlist[0];
		if(empty($cw) || $cw['crid']!= $this->crid || $cw['uid'] != $this->uid || empty($cw['addstatus'])){
			return FALSE;
		}
		
		$param['rid'] = $this->rid;
		$param['uids'] = $this->uids;
		return $this->rcmodel->addUser($param);
	}
	
	/*
	编辑点名课
	*/
	public function editAction(){
		$param['crid'] = $this->crid;
		$param['rid'] = $this->rid;
		$cw = $this->rcmodel->getCwList($param);
		if(empty($cw[0]) || $cw[0]['crid'] != $param['crid'] || $cw[0]['uid'] != $this->uid){
			return FALSE;
		}
		$param['starttime'] = $this->starttime;
		$param['endtime'] = $this->endtime;
		$param['cwid'] = $this->cwid;
		$param['rname'] = $this->rname;
		
		return $this->rcmodel->edit($param);
	}
	
	/*
	删除
	*/
	public function delAction(){
		$param['crid'] = $this->crid;
		$param['rid'] = $this->rid;
		return $this->rcmodel->del($param);
	}
	
	/*
	点名
	*/
	public function callAction(){
		$param['crid'] = $this->crid;
		$param['rid'] = $this->rid;
		$cwlist = $this->getCwList($param);
		if(empty($cwlist)){
			return FALSE;
		}
		$cw = $cwlist[0];
		if($cw['crid'] != $param['crid'] || empty($cw['userstatus']) || empty($cw['callstatus'])){
			return FALSE;
		}
		$param['uid'] = $this->uid;
		$param['dateline'] = $this->dateline;
		$param['isclear'] = $this->isclear;
		return $this->rcmodel->call($param);
	}
	
	/*
	需点名的学生列表
	*/
	public function rollListAction(){
		$param['rid'] = $this->rid;
		$param['crid'] = $this->crid;
		$cw = $this->rcmodel->getCwList($param);
		if(empty($cw[0]) || $cw[0]['crid'] != $param['crid']){
			return array();
		}
		if($this->called !== NULL){
			$param['called'] = $this->called;
		}
		$param['pagesize'] = $this->pagesize;
		$param['page'] = $this->page;
		if($this->q !== NULL && $this->q !== ''){
			$param['q'] = $this->q;
		}
		$rolllist = $this->rcmodel->getRollList($param);
		$rollcount = $this->rcmodel->getRollCount($param);
		return array('rolllist'=>$rolllist,'rollcount'=>$rollcount);
	}
	
	/*
	统计
	*/
	public function statsAction(){
		$param['crid'] = $this->crid;
		$param['nolimit'] = 1;
		$cwlist = $this->rcmodel->getCwList($param);
		$ridarr = array_column($cwlist,'rid');
		$rids = implode(',',$ridarr);
		
		$userlist = array();
		$rcount = 0;
		$totalcount = 0;
		if(!empty($rids)){
			$statsparam = array('uids'=>$this->uids,'rids'=>$rids);
			$stats = $this->rcmodel->getStats($statsparam);//到课数，总到课人次
			$userlist = $this->rcmodel->getCalledUser($statsparam);//各学生到课数
			if(!empty($userlist)){
				$itemids = array_column($userlist,'itemid');
				$itemids = implode(',',$itemids);
				$pimodel = new PayitemModel();
				$itemlist = $pimodel->getSimpleByIds($itemids);
				foreach($userlist as &$user){
					$itemid = $user['itemid'];
					$user['iname'] = empty($itemlist[$itemid])?'':$itemlist[$itemid]['iname'];
				}
			}
			$totalcount = $stats['totalcount'];
			$rcount = $stats['rcount'];
			$hotcw[0] = $this->rcmodel->getHotCw($statsparam);//最受欢迎课件
			if(!empty($hotcw[0])){
				$key = array_search($hotcw[0]['rid'],$ridarr);
				$cwid = $cwlist[$key]['cwid'];
				$cwmodel = new CoursewareModel();
				$cwinfo = $cwmodel->getSimpleInfoByIds($cwid);//课件信息
				if(!empty($cwinfo)){
					$hotcw[0]['title'] = $cwinfo[0]['title'];
				} 
			}
		}
		
		$hotcw = empty($hotcw[0])?array():$hotcw;
		// $stats = $this->rcmodel->getStats();
		return array('userlist'=>$userlist,'rcount'=>$rcount,'totalcount'=>$totalcount,'hotcw'=>$hotcw);
	}
	//网校学习状态总览,返回总学习次数$cwcount,总学习人次calledcount和到课率$percent
    public function classroomStatusAction(){
        $cwcount = 0;   //新建上课总数
        $percent = 0;    //平均到课率
        $totalcount = 0;//需要点名总数
        $calledcount = 0;//总学习人次
        $param['crid'] = $this->crid;
        $param['nolimit'] = TRUE;
        $rinfo = $this->rcmodel->getCwList($param);
        if(!empty($rinfo)) {
            $ridarr = array_column($rinfo, 'rid');
            $param['rids'] = implode(',',$ridarr);
            $cwcount = count($ridarr);
            $rcalls = $this->rcmodel->getCalledCw($param);
            if(!empty($rcalls)){
                foreach ($rcalls as $call){
                    $totalcount += $call['count'];
                    $calledcount += $call['calledcount'];
                }
                $percent = round(($calledcount/$totalcount)*100,2);
            }
        }
        return  array('cwcount'=>$cwcount,'calledcount'=>$calledcount,'percent'=>$percent);
    }

    //获取当前部门及子部门的classid集合
    public function subDepartmentAction(){
        $param['crid'] = $this->crid;
        $classinfo = $this->classesModel->getDeptmentTree($this->crid, true);
        $classids = array();
        if(!empty($this->classid)){
            $param['classid'] = $this->classid;
            $classids=$this->getSubclass($classinfo,$param['classid']);
            array_push($classids, $param['classid']);
        }else{
            $classids = array_column($classinfo,'classid');
        }
        return $classids;
    }
    //递归获取子部门id
    public function getSubclass($class,$superior=0){
        static $arr=array();
        foreach($class as $k=>$v){
            if($v['superior']==$superior){
                $arr[]=$v['classid'];
                unset($class[$k]);//已经获取子部门id的,从数组中移除，提高性能
                $this->getSubclass($class,$v['classid']);
            }
        }
        return $arr;
    }
    /**
    *获取当前社区及子社区点名上课列表
    */
    public function classListAction(){
        //获取参数
        $param = array('crid'=>$this->crid,'pagesize'=>$this->pagesize,'page'=>$this->page);
        if(!empty($this->begintime)){
            $param['begintime'] = $this->begintime;
        }
        if(!empty($this->lasttime)){
            $param['lasttime'] = $this->lasttime;
        }
        if($this->q !== NULL && $this->q !== ''){
            $param['q'] = $this->q;
        }
        if(!empty($this->classids)){
            $param['classids'] = $this->classids;
            $classrid = $this->rcmodel->getRidsByclass($param);//获取当前部门及子部门上课列表rid集合
            $ridstr = array_column($classrid,'rid');
            $ridstr = !empty($ridstr) ? implode(',',$ridstr) : -1;
        }
        if(!empty($this->rids)){
            $param['rids'] = $this->rids;
        }elseif(!empty($ridstr)){
            $param['rids'] = $ridstr;
        }
        //查询结果
        $cwlist = $this->rcmodel->getCwList($param);
        if(!empty($cwlist)){
            $cwids = array_column($cwlist,'cwid');
            $cwmodel = new CoursewareModel();
            $cwinfolist = $cwmodel->getSimpleInfoByIds($cwids,TRUE);//课件信息
            $rids = array_column($cwlist,'rid');
            $rids = implode(',',$rids);
            $countlist = $this->rcmodel->getCalledCw(array('rids'=>$rids,'crid'=>$this->crid));//人数，点名信息
            foreach($cwlist as &$cw){
                $cwid = $cw['cwid'];
                $rid = $cw['rid'];
                $cw['title'] = empty($cwinfolist[$cwid])?'':$cwinfolist[$cwid]['title'];
                $cw['totalcount'] = empty($countlist[$rid])?0:$countlist[$rid]['count'];
                $cw['calledcount'] = empty($countlist[$rid])?0:$countlist[$rid]['calledcount'];
            }
        }
        $crwcount = 0;
        $crwcount = $this->rcmodel->getCwCount($param);
        //处理结果
        foreach ($cwlist as &$cwl){
            foreach ($classrid as $cr){
                if($cr['rid'] == $cwl['rid']){
                    if(empty($cwl['classname'])){
                        $cwl['classname'] = $cr['classname'];
                    }else{
                        $cwl['classname'] .= ','.$cr['classname'];
                    }
                }
            }
        }
        return array('crwcount'=>$crwcount,'cwrlist'=>array_values($cwlist));
    }
	
	/*
	点名系统设置
	*/
	public function settingsAction(){
		return $this->getSettings();
	}
	
	private function getSettings(){
		$settings = $this->rcmodel->getSettings($this->crid);
		$settingarr = json_decode($settings['rollcall']);
		$settingkeys = array('minusernum','addusertime','calltime');
		
		foreach($settingkeys as $key){
			$rcsetting[$key] = empty($settingarr->$key)?0:$settingarr->$key;
		}
		return $rcsetting;
	}
	public function updateSettingsAction(){
		$settings = $this->getSettings();
		foreach($settings as $key=>$set){
			$settings[$key] = ($this->$key === null)?$set:$this->$key;
		}
		return $this->rcmodel->updateSettings(json_encode($settings),$this->crid);
	}
}