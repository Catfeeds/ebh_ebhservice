<?php
/**
 * 老年大学点名
 */
class RollcallModel {
	private $db;
	public function __construct() {
		$this->db = Ebh()->db;
	}
	/**
	* 需点名课件列表
	*/
	public function getCwList($param) {
		if(empty($param['crid'])){
			return array();
		}
		$sql = 'select r.rid,r.crid,r.uid,r.itemid,r.cwid,r.rname,r.dateline,r.starttime,r.endtime from ebh_rollcalls r';
		if(isset($param['q'])){
            $sql .= ' join ebh_coursewares cw on r.cwid=cw.cwid';
        }
		$wherearr[] = 'r.del=0';
		$wherearr[] = 'r.crid='.$param['crid'];
		if(!empty($param['uid'])){
			$wherearr[] = 'r.uid='.$param['uid'];
		}
		if(!empty($param['rid'])){
			$wherearr[] = 'r.rid='.$param['rid'];
		}
        if(!empty($param['rids'])){
            $wherearr[] = 'r.rid in ('.$param['rids'].')';
        }
        if(!empty($param['begintime'])){
            $wherearr[] = 'r.starttime >='.$param['begintime'];
        }
        if(!empty($param['lasttime'])){
            $wherearr[] = 'r.starttime<='.($param['lasttime']+86400);
        }
		if(isset($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$wherearr[] = '(r.rname like \'%'.$q.'%\' or cw.title like \'%'.$q.'%\')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' order by rid desc';
		if(empty($param['nolimit'])){
			if(!empty($param['limit'])) {
				$sql .= ' limit '. $param['limit'];
			} else {
				if (empty($param['page']) || $param['page'] < 1)
					$page = 1;
				else
					$page = $param['page'];
				$pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
				$start = ($page - 1) * $pagesize;
				$sql .= ' limit ' . $start . ',' . $pagesize;
			}
		}
		return $this->db->query($sql)->list_array();
	}
	
	/**
	* 需点名课件数量
	*/
	public function getCwCount($param) {
		if(empty($param['crid'])){
			return 0;
		}
		$sql = 'select count(*) count from ebh_rollcalls r';
        if(isset($param['q'])){
            $sql .= ' join ebh_coursewares cw on r.cwid=cw.cwid';
        }
		$wherearr[] = 'r.del=0';
		$wherearr[] = 'r.crid='.$param['crid'];
		if(!empty($param['uid'])){
			$wherearr[] = 'r.uid='.$param['uid'];
		}
		if(!empty($param['rid'])){
			$wherearr[] = 'r.rid='.$param['rid'];
		}
        if(!empty($param['rids'])){
            $wherearr[] = 'r.rid in ('.$param['rids'].')';
        }
        if(!empty($param['begintime'])){
            $wherearr[] = 'r.starttime >='.$param['begintime'];
        }
        if(!empty($param['lasttime'])){
            $wherearr[] = 'r.starttime<='.($param['lasttime']+86400);
        }
		if(isset($param['q'])){
			$q = $this->db->escape_str($param['q']);
            $wherearr[] = '(r.rname like \'%'.$q.'%\' or cw.title like \'%'.$q.'%\')';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
        $count = $this->db->query($sql)->row_array();
		return $count['count'];
	}
	
	/*
	添加一个点名课
	*/
	public function add($param){
		if(empty($param['crid']) || empty($param['rname']) || empty($param['cwid']) || empty($param['uid'])){
			return false;
		}
		$setarr = array('crid'=>$param['crid'],'rname'=>$param['rname'],'cwid'=>$param['cwid'],'uid'=>$param['uid'],'dateline'=>SYSTIME,'itemid'=>$param['itemid']);
		if(!empty($param['starttime'])){
			$setarr['starttime'] = $param['starttime'];
		}
		if(!empty($param['endtime'])){
			$setarr['endtime'] = $param['endtime'];
		}
		
		return $this->db->insert('ebh_rollcalls',$setarr);
		
	}
	
	/*
	去报名，给点名计划添加学生
	*/
	public function addUser($param){
		if(empty($param['rid'])){
			return FALSE;
		}
		$rid = $param['rid'];
		$this->db->begin_trans();
		$sql = 'select uid from ebh_rollcallusers where rid='.$rid;
		$userlist = $this->db->query($sql)->list_array();
		$userlist = array_column($userlist,'uid');
		$uids = explode(',',$param['uids']);
		$newuids = array_diff($uids,$userlist);
		$olduids = array_diff($userlist,$uids);
		if(!empty($newuids)){//加上新的
			$insertsql = 'insert into ebh_rollcallusers (rid,uid,dateline) values ';
			$tsql = '';
			foreach($newuids as $uid){
				$tsql .= "($rid,$uid,0),";
			}
			$insertsql.= rtrim($tsql,',');
			$this->db->query($insertsql);
		}
		if(!empty($olduids)){//删掉老的
			$delstr = 'uid in('.implode(',',$olduids).') and rid ='.$rid;
			$this->db->delete('ebh_rollcallusers',$delstr);
		}
		if($this->db->trans_status()===FALSE) {
            $this->db->rollback_trans();
            return FALSE;
        } else {
            $this->db->commit_trans();
        }
        return TRUE;
		
	}
	
	/*
	点名计划信息
	*/
	public function getRollCallInfo($param){
		if(empty($param['rid'])){
			return FALSE;
		}
		$sql = 'select crid,rid,uid,itemid,cwid,del,starttime,endtime from ebh_rollcalls';
		$sql.= ' where rid='.$param['rid'];
		return $this->db->query($sql)->row_array();
	}
	
	/*
	编辑一个点名课
	*/
	public function edit($param){
		if(empty($param['crid']) || empty($param['rid'])){
			return false;
		}
		$rid = $param['rid'];
		if(!empty($param['rname'])){
			$setarr['rname'] = $param['rname'];
		}
		if(!empty($param['starttime'])){
			$setarr['starttime'] = $param['starttime'];
		}
		if(!empty($param['endtime'])){
			$setarr['endtime'] = $param['endtime'];
		}
		if(!empty($param['cwid'])){
			$setarr['cwid'] = $param['cwid'];
		}
		if(!empty($param['itemid'])){
			$setarr['itemid'] = $param['itemid'];
		}
		if(!empty($setarr)){
			return $this->db->update('ebh_rollcalls',$setarr,array('rid'=>$rid));
		}
		return TRUE;
	}
	
	/*
	删除
	*/
	public function del($param){
		if(empty($param['crid']) || empty($param['rid'])){
			return false;
		}
		$setarr['del'] = 1;
		$rows = $this->db->update('ebh_rollcalls',$setarr,$param);
		if(!empty($rows)){
			$this->db->update('ebh_rollcallusers',$setarr,array('rid'=>$param['rid']));
		}
		return $rows;
	}
	
	/*
	点名操作
	*/
	public function call($param){
		if(empty($param['rid']) || empty($param['uid'])){
			return false;
		}
		$wherearr['rid'] = $param['rid'];
		$wherearr['uid'] = $param['uid'];
		if(!empty($param['isclear'])){
			$setarr['dateline'] = 0;
			$setarr['called'] = 0;
		} else {
			$setarr['dateline'] = empty($param['dateline'])?SYSTIME:$param['dateline'];
			$setarr['called'] = 1;
		}
		return $this->db->update('ebh_rollcallusers',$setarr,$wherearr);
	}
	
	/*
	点名的学生列表
	*/
	public function getRollList($param){
		$sql = 'select rcu.rid,rcu.uid,rcu.dateline,rcu.called,u.username,u.sex,u.face,u.realname,c.classname
				from ebh_rollcallusers rcu
				join ebh_classstudents cs on rcu.uid=cs.uid
				join ebh_classes c on cs.classid=c.classid
				join ebh_users u on rcu.uid=u.uid
				join ebh_roomusers ru on ru.uid=rcu.uid';
		if(!empty($param['rid'])){
			$wherearr[] = 'rid='.$param['rid'];
		}
		if(isset($param['called'])){
			$wherearr[] = 'rcu.called='.$param['called'];
		}
		if(isset($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$qarr[] = 'u.username like \'%'.$q.'%\'';
			$qarr[] = 'u.realname like \'%'.$q.'%\'';
			$qarr[] = 'c.classname like \'%'.$q.'%\'';
			$wherearr[] = '('.implode(' OR ',$qarr).')';
		}
		$wherearr[] = 'ru.crid='.$param['crid'];
		$wherearr[] = 'c.crid='.$param['crid'];
		$wherearr[] = 'rcu.del=0';
		$wherearr[] = 'ru.cstatus=1';
		$wherearr[] = 'u.status=1';
		$sql.= ' where '.implode(' AND ',$wherearr);
		if(empty($param['nolimit'])){
			if(!empty($param['limit'])) {
				$sql .= ' limit '. $param['limit'];
			} else {
				if (empty($param['page']) || $param['page'] < 1)
					$page = 1;
				else
					$page = $param['page'];
				$pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
				$start = ($page - 1) * $pagesize;
				$sql .= ' limit ' . $start . ',' . $pagesize;
			}
		}
		return $this->db->query($sql)->list_array();
	}
	/*
	点名的学生数量
	*/
	public function getRollCount($param){
		$sql = 'select count(*) count 
				from ebh_rollcallusers rcu
				join ebh_classstudents cs on rcu.uid=cs.uid
				join ebh_classes c on cs.classid=c.classid
				join ebh_users u on rcu.uid=u.uid
				join ebh_roomusers ru on ru.uid=rcu.uid';
		if(!empty($param['rid'])){
			$wherearr[] = 'rid='.$param['rid'];
		}
		if(isset($param['called'])){
			$wherearr[] = 'rcu.called='.$param['called'];
		}
		if(isset($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$qarr[] = 'u.username like \'%'.$q.'%\'';
			$qarr[] = 'u.realname like \'%'.$q.'%\'';
			$qarr[] = 'c.classname like \'%'.$q.'%\'';
			$wherearr[] = '('.implode(' OR ',$qarr).')';
		}
		$wherearr[] = 'ru.crid='.$param['crid'];
		$wherearr[] = 'c.crid='.$param['crid'];
		$wherearr[] = 'rcu.del=0';
		$wherearr[] = 'ru.cstatus=1';
		$wherearr[] = 'u.status=1';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$count = $this->db->query($sql)->row_array();
		return $count['count'];
	}
	
	/*
	 学生上课情况
	*/
	public function getCalledUser($param){
		$sql = 'select count(*) totalcount,count(CASE WHEN called=1 THEN rcu.uid END) calledcount,rcu.uid,rc.itemid 
				from ebh_rollcallusers rcu
				join ebh_rollcalls rc on rcu.rid=rc.rid';
		$wherearr[] = 'rcu.rid in ('.$param['rids'].')';
		$wherearr[] = 'rcu.uid in ('.$param['uids'].')';
		$wherearr[] = 'rcu.del=0';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by rcu.uid,rc.itemid';
		return $this->db->query($sql)->list_array();
	}
	
	/*
	 *上课次数(有点过名的课)
	 *@param $param array 带有uids和rids的数组
	*/
	public function getCalledCw($param){
		$sql = 'select count(*) count,count(CASE WHEN called=1 THEN rcu.uid END) calledcount,rcu.rid from ebh_rollcallusers rcu
				join ebh_roomusers ru on rcu.uid=ru.uid';
		if(!empty($param['uids'])){
			$wherearr[] = 'uid in ('.$param['uids'].')';
		}
		$wherearr[] = 'rid in ('.$param['rids'].')';
		$wherearr[] = 'del=0';
		$wherearr[] = 'ru.cstatus=1';
		$wherearr[] = 'ru.crid='.$param['crid'];
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by rid ';
		return $this->db->query($sql)->list_array('rid');
	}
	
	/*
	 *统计
	 *@param $param array 带有uids和rids的数组
	*/
	public function getStats($param){
		$sql = 'select sum(called) totalcount,count(DISTINCT CASE WHEN called=1 THEN rid END) rcount from ebh_rollcallusers';
		if(!empty($param['uids'])){
			$wherearr[] = 'uid in ('.$param['uids'].')';
		}
		$wherearr[] = 'rid in ('.$param['rids'].')';
		$wherearr[] = 'del=0';
		$sql.= ' where '.implode(' AND ',$wherearr);
		return $this->db->query($sql)->row_array();
	}
	
	/*
	 *最受欢迎课件
	 *@param $param array 带有uids和rids的数组
	*/
	public function getHotCw($param){
		$sql = 'select rid,sum(called) rcount,count(*) usercount from ebh_rollcallusers';
		if(!empty($param['uids'])){
			$wherearr[] = 'uid in ('.$param['uids'].')';
		}
		$wherearr[] = 'rid in ('.$param['rids'].')';
		$wherearr[] = 'del=0';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by rid';
		$sql.= ' having(rcount>0)';
		$sql.= ' order by rcount desc';
		$sql.= ' limit 1';
		return $this->db->query($sql)->row_array();
	}
    /**
     *根据班级ID查找上课列表信息
     *@param $param array 带有classids的数组
     *@return $param array 带有rid,classid的数组
    */
    public function getRidsByclass($param){
        $sql = 'SELECT rcl.rid,ct.classid,cl.classname FROM ebh_rollcallusers rcl JOIN ebh_classstudents ct ON rcl.uid=ct.uid JOIN ebh_classes cl ON cl.classid = ct.classid ';
        if(!empty($param['classids']) && is_array($param['classids'])){
            $wherearr[] = 'ct.classid in ('.implode(',',$param['classids']).')';
        }else{
            return FALSE;
        }
        $wherearr[] = 'del=0';
        $sql.= ' where '.implode(' AND ',$wherearr);
        $sql.= ' GROUP BY ct.classid,rcl.rid';
        return $this->db->query($sql)->list_array();
    }
	
	/*
	设置数据
	*/
	public function getSettings($crid){
		if(empty($crid)){
			return FALSE;
		}
		$sql = 'select rollcall from ebh_systemsettings';
		$sql.= ' where crid='.$crid;
		return $this->db->query($sql)->row_array();
	}
	
	public function updateSettings($settings,$crid){
		if(empty($settings) || empty($crid)){
			return FALSE;
		}
		$setarr['rollcall'] = $settings;
		$wherearr['crid'] = $crid;
		return $this->db->update('ebh_systemsettings',$setarr,$wherearr);
	}
}