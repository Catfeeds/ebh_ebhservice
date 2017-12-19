<?php

/**
 * Created by PhpStorm.
 * User: app
 * Date: 2017/4/7
 * Time: 9:50
 */
class CoursewareController extends Controller {
    public function parameterRules()
    {
        return array(
            //热门课件
            'hotListAction' => array(
                'crid' => array('name' => 'crid', 'require' => true, 'type' => 'int'),
                'num' => array('name' => 'num', 'require' => true, 'type' => 'int')
            ),
			'classCwStudyStatsAction'=> array(
				'crid' => array('name'=>'crid','require'=>true,'type'=>'int'),
				'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
				'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
				'starttime' =>	array('name'=>'starttime','default'=>0,'type'=>'int'),
				'endtime' =>	array('name'=>'endtime','default'=>0,'type'=>'int'),
				'classid' =>	array('name'=>'classid','default'=>0,'type'=>'int'),
				'itemid' =>	array('name'=>'itemid','default'=>0,'type'=>'int'),
				'q' =>	array('name'=>'q','default'=>'','type'=>'string'),
				'isreport'=>array('name'=>'isreport','default'=>0,'type'=>'int'),
			),
			'cwStudyListAction'=> array(
				'crid' => array('name'=>'crid','require'=>true,'type'=>'int'),
				'classid' =>	array('name'=>'classid','require'=>true,'type'=>'int'),
				'cwid' =>	array('name'=>'cwid','require'=>true,'type'=>'int'),
				'starttime' =>	array('name'=>'starttime','default'=>0,'type'=>'int'),
				'endtime' =>	array('name'=>'endtime','default'=>0,'type'=>'int'),
			),
			'statsClassCwAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'cwid' =>	array('name'=>'cwid','default'=>0,'type'=>'int'),
				'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
				'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
				'q' =>	array('name'=>'q','default'=>'','type'=>'string'),
			),
			'saveClassCwStatsAction' => array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'classids'  =>  array('name'=>'classids','default'=>array(),'type'=>'array'),
				'cwid'  =>  array('name'=>'cwid','default'=>TRUE,'type'=>'int'),
				'itemid'  =>  array('name'=>'itemid','default'=>0,'type'=>'int'),
				'isclear'  =>  array('name'=>'isclear','default'=>0,'type'=>'int'),
				'path'=> array('name'=>'path','default'=>array(),'type'=>'array'),
			),
        );
    }

    /**
     * 热门课件
     */
    public function hotListAction() {
        $model = new CoursewareModel();
        return $model->getHotList($this->crid, $this->num);
    }
	
	/*
	 *班级课程，课件学习统计
	*/
	public function classCwStudyStatsAction(){
		$ccmodel = new ClasscourseModel();
		$cwmodel = new CoursewareModel();
		$usermodel = new UserModel();
		$classmodel = new ClassesModel();
		$plmodel = new PlayLogModel();
		$param['crid'] = $this->crid;
		$param['pagesize'] = $this->pagesize;
		$param['page'] = $this->page;
		$param['classid'] = $this->classid;
		$param['itemid'] = $this->itemid;
		$param['q'] = $this->q;
		
		$cwlist = $cwmodel->getCwListStats($param);
		$cwcount = $cwmodel->getCwCountStats($param);
		if(empty($cwlist))
			return array();
		//老师信息
		$teacherids = array_column($cwlist,'uid');
		$teacherids = implode(',',$teacherids);
		$teacherlist = $usermodel->getUserByUids($teacherids);
		//学生数量信息
		$classids = array_column($cwlist,'classid');
		$classids = implode(',',$classids);
		$usercount = $classmodel->getDeptUserCount(array('classids'=>$classids,'crid'=>$this->crid));
		//学生列表
		$userlist = $classmodel->getSubDeptUsers(array('classids'=>$classids,'crid'=>$this->crid,'nolimit'=>1));
		$classuserarr = array();
		foreach($userlist as $user){
			$classuserarr[$user['classid']][] = $user['uid'];
			$userarr[$user['classid']][] = $user;
		}
		$conditionarr = array();
		foreach($cwlist as &$cw){
			$cw['realname'] = !empty($teacherlist[$cw['uid']])?$teacherlist[$cw['uid']]['realname']:'';
			$cw['usercount'] = empty($usercount[$cw['classid']])?0:$usercount[$cw['classid']]['count'];
			if(!empty($classuserarr[$cw['classid']])){
				$conditionarr[] = array('uids'=>implode(',',$classuserarr[$cw['classid']]),'cwid'=>$cw['cwid'],'classid'=>$cw['classid']);
			}
		}
		
		$studylist = $plmodel->getStudyInfoByClass(array('crid'=>$this->crid,'conditionarr'=>$conditionarr,'starttime'=>$this->starttime,'endtime'=>$this->endtime));
		foreach($cwlist as &$cw){
			$classid = $cw['classid'];
			$cwid = $cw['cwid'];
			$studycountkey = 'studycount_c'.$classid.'_cw'.$cwid;
			$studyusercountkey = 'studyusercount_c'.$classid.'_cw'.$cwid;
			$ltimekey = 'ltime_c'.$classid.'_cw'.$cwid;
			$cw['studycount'] = empty($studylist[$studycountkey])?0:$studylist[$studycountkey];
			$cw['studyusercount'] = empty($studylist[$studyusercountkey])?0:$studylist[$studyusercountkey];
			$cw['nostudycount'] = $cw['usercount'] - $cw['studyusercount'];
			$cw['ltime'] = empty($studylist[$ltimekey])?0:$studylist[$ltimekey];
		}
		if(empty($this->isreport)){//非导出的
			return array('cwlist'=>$cwlist,'cwcount'=>$cwcount);
		}
		
		//以下为导出的
		$mstudylist = $plmodel->getMultiCwStudyListByUser(array('crid'=>$this->crid,'conditionarr'=>$conditionarr,'starttime'=>$this->starttime,'endtime'=>$this->endtime));
		foreach($cwlist as &$cw){
			$cw['userlist'] = empty($userarr[$cw['classid']])?array():$userarr[$cw['classid']];
		}
		return array('cwlist'=>$cwlist,'studylist'=>$mstudylist);
		
	}
	
	/*
	 *单课件,学习统计
	*/
	public function cwStudyListAction(){
		$classmodel = new ClassesModel();
		$plmodel = new PlayLogModel();
		//学生列表
		$userlist = $classmodel->getSubDeptUsers(array('classids'=>$this->classid,'crid'=>$this->crid,'nolimit'=>1));
		$uids = array_column($userlist,'uid');
		$uids = implode(',',$uids);
		$loglist = $plmodel->getCwStudyListByUser(array('cwid'=>$this->cwid,'uids'=>$uids,'starttime'=>$this->starttime,'endtime'=>$this->endtime));
		foreach($userlist as &$user){
			$uid = $user['uid'];
			if(empty($loglist[$uid])){
				$loglist[$uid]['ltime'] = NULL;
				$loglist[$uid]['studycount'] = NULL;
			}
			$user = array_merge($user,$loglist[$uid]);
			$ltimearr[] = $loglist[$uid]['ltime'];
			$studycountarr[] = $loglist[$uid]['studycount'];
			$usernamearr[] = $user['username'];
		}
		array_multisort($ltimearr,SORT_DESC,$studycountarr,SORT_DESC,$usernamearr,SORT_ASC,$userlist);
		return $userlist;
	}
	
	/*
	 *学习统计分析 所有班级和课件
	*/
	public function statsClassCwAction(){
		$cwmodel = new CoursewareModel();
		$param = array('crid'=>$this->crid,'pagesize'=>$this->pagesize,'page'=>$this->page,'cwid'=>$this->cwid);
		$cwlist = $cwmodel->statsCw($param);
		$cwcount = $cwmodel->statsCwCount($param);
		if(!empty($cwlist)){
			$cwids = array_keys($cwlist);
			$cwids = implode(',',$cwids);
			$classlist = $cwmodel->statsClassCw(array('crid'=>$this->crid,'cwids'=>$cwids));
			foreach($classlist as $class){
				$cwid = $class['cwid'];
				$cwlist[$cwid]['class'][] = $class;
			}
		}
		// 
		return array('list'=>array_values($cwlist),'count'=>$cwcount);
	}
	
	/*
	 *保存班级课件
	*/
	public function saveClassCwStatsAction(){
		$cwmodel = new CoursewareModel();
		return $cwmodel->saveClassCwStats(array('classids'=>$this->classids,'itemid'=>$this->itemid,'cwid'=>$this->cwid,'isclear'=>$this->isclear,'path'=>$this->path,'crid'=>$this->crid));
	}
}