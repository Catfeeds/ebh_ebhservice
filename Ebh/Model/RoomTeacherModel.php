<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class RoomTeacherModel{

    /**
     * 指定教师是否在教室中
     * @param $crid
     * @param $uid
     * @return mixed
     */
    public function exists($crid,$uid){
        $sql = 'select 1 from ebh_roomteachers where crid='.$crid.' and tid='.$uid.' limit 1';
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 判断指定UID是否存在
     * @param $uid
     * @return mixed
     */
    public function uidIsExists($uid){
        $sql = 'select 1 from ebh_roomteachers where tid='.$uid.' limit 1';
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 新增教师
     * @param $param
     * @return mixed
     */
    public function add($param){
        if(!empty($param['tid'])){
            $setarr['tid'] = $param['tid'];
        }
        if(!empty($param['crid'])){
            $setarr['crid'] = $param['crid'];
        }
        if(isset($param['status'])){
            $setarr['status'] = $param['status'];
        }
        if(!empty($param['cdateline'])){
            $setarr['cdateline'] = $param['cdateline'];
        }
        if(!empty($param['role'])){
            $setarr['role'] = $param['role'];
        }
        if(!empty($param['mobile'])){
            $setarr['mobile'] = $param['mobile'];
        }

        Ebh()->db->update('ebh_classrooms',array(),array('crid'=>$param['crid']),array('teanum'=>'teanum+1'));
        return Ebh()->db->insert('ebh_roomteachers',$setarr);
    }
	
	/*
	 *删除学校老师信息
	 *@param $param
	 */
	public function delRoomTeacher($param){
		if(empty($param['crid']) || empty($param['uid'])){
			return FALSE;
		}
		$crid = $param['crid'];
		$uid = $param['uid'];
		$res = $this->exists($crid,$uid);
		if(empty($res)){
			return FALSE;
		}
		$wherearr['tid'] = $uid;
		$wherearr['crid'] = $crid;
		
		Ebh()->db->begin_trans();
		
		Ebh()->db->update('ebh_classrooms',array(),array('crid'=>$crid),array('teanum'=>'teanum-1'));
		Ebh()->db->delete('ebh_roomteachers',$wherearr);
		Ebh()->db->delete('ebh_teacherfolders',$wherearr);
		$sql = 'select classid from ebh_classes where crid='.$crid;
		$classes = Ebh()->db->query($sql)->list_array();
		if(!empty($classes)){
			$classids ='';
			foreach($classes as $class){
				if(!empty($classids))
					$classids.=','.$class['classid'];
				else
					$classids = $class['classid'];
			}
			$sql = 'delete from ebh_classteachers where uid = '.$uid.' and classid in ('.$classids.')';
			Ebh()->db->query($sql);
		}
		
		if (Ebh()->db->trans_status() === FALSE) {
            Ebh()->db->rollback_trans();
            return FALSE;
        } else {
            Ebh()->db->commit_trans();
        }
        return TRUE;
	}

    /**
     * 修改老师电话
     * @param $mobile
     * @param $tid
     * @param $crid
     * @return mixed
     */
	public function updateMobile($mobile, $tid, $crid) {
        $where = array('crid'=>$crid,'tid'=>$tid);
	    return Ebh()->db->update('ebh_roomteachers',array('mobile' => $mobile),$where);
    }
	
	/**
     * 获取教师详情
     * @param $param uid,crid
     * @return mixed
     */
	public function getDetail($param){
		if(empty($param['uid']) || empty($param['crid'])){
			return FALSE;
		}
		$sql = 'select rt.mobile tmobile,u.uid,u.username,u.realname,u.nickname,u.face,u.citycode,u.address,u.email,u.mysign,t.tag,t.schoolage,u.sex,t.phone,u.mobile,t.profile,t.fax,t.schoolage,t.message,t.bankcard,t.profitratio,t.vitae,t.agentid,t.agency,t.degree,t.graduateschool,t.birthdate,t.workunit,t.department,t.professionaltitle,t.position 
		from ebh_users u 
		left join ebh_teachers t on u.uid = t.teacherid 
		left join ebh_roomteachers rt on t.teacherid=rt.tid where u.uid = ' . $param['uid'] .' and rt.crid='.$param['crid'];
        return Ebh()->db->query($sql)->row_array();
	}

    /**
     * 验证有效的教师ID
     * @param $tids 教师ID
     * @param int $crid 网校ID
     * @return mixed
     */
	public function checkTeacherids($tids, $crid) {
	    if (!is_array($tids)) {
	        $tids = array(intval($tids));
        }
        $sql = 'SELECT `a`.`tid` FROM `ebh_roomteachers` `a` 
                JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`tid` 
                JOIN `ebh_teachers` `c` ON `c`.`teacherid`=`a`.`tid` 
                WHERE `a`.`crid`='.$crid.' AND `a`.`tid` IN('.implode(',', $tids).')';
	    return Ebh()->db->query($sql)->list_field();
    }


    /**
     * 根据教室编号获取教师列表记录数，一般适合于教师网校的教师列表
     * @param type $param
     * @return boolean
     */
    public function getRoomTeacherCount($param) {
        $count = 0;
        if (empty($param['crid']))
            return $count;
        $sql = 'select count(*) count from ebh_roomteachers rt ' .
            'join ebh_users u on (rt.tid = u.uid) ';
        $wherearr = array();
        $wherearr[] = 'rt.crid=' . $param['crid'];
        if (isset($param['status']))
            $wherearr[] = 'rt.status=' . $param['status'];
        if (!empty($param['q'])) {
            $q = Ebh()->db->escape_str($param['q']);
            $wherearr[] = '(u.username like \'%' . $q . '%\' OR u.realname like \'%' . $q . '%\')';
        }
        if (!empty($wherearr))
            $sql .= ' WHERE ' . implode(' AND ', $wherearr);
        $row = Ebh()->db->query($sql)->row_array();
        if (!empty($row))
            $count = $row['count'];
        return $count;
    }

    /**
     * 获取网校教师id列表
     * @param  integer $crid     网校id
     * @param  integer $page     页号
     * @param  integer $pagesize 每页记录数
     * @return array            教师ID数组
     */
    public function getTeacheIdList($crid,$page=1,$pagesize=100) {
        $sql = "select tid from ebh_roomteachers where crid=$crid";
        $start = ($page - 1) * $pagesize;
        $sql .= ' limit ' . $start . ',' . $pagesize;
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 获取网校教师id列表去除不需要的id
     * @param  integer $crid     网校id
     * @param  integer $page     页号
     * @param  integer $pagesize 每页记录数
     * @param  str $filterUids 过滤的uid
     * @param int getall 0为不全部数据，1为全部数据
     * @return array            教师ID数组
     */
    public function getTeacheIdListFilter($crid,$page=1,$pagesize=100,$filterUids='',$getall=0) {
        if ($filterUids) {
            $sql = 'select rt.tid as uid from ebh_roomteachers rt join ebh_users u on(rt.tid=u.uid)
            join ebh_teachers t on(t.teacherid=u.uid) where rt.crid='.$crid .' and rt.tid not in('.$filterUids.') ';
        } else {
            $sql = 'select rt.tid as uid from ebh_roomteachers rt join ebh_users u on(rt.tid=u.uid)
            join ebh_teachers t on(t.teacherid=u.uid) where rt.crid='.$crid;
        }
        if (!$getall) {//不是导出要分页
            $start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 获取网校教师id列表去除不需要的id的数量
     * @param  integer $crid     网校id
     * @param  integer $page     页号
     * @param  integer $pagesize 每页记录数
     * @return array            教师ID数组
     */
    public function getTeacheIdListFilterCount($crid,$filterUids='') {
        if ($filterUids) {
            $sql = 'select count(1) as c from ebh_roomteachers rt join ebh_users u on(rt.tid=u.uid)
            join ebh_teachers t on(t.teacherid=u.uid) where rt.crid='.$crid .' and rt.tid not in('.$filterUids.') ';
        } else {
            $sql = 'select count(1) as c from ebh_roomteachers rt join ebh_users u on(rt.tid=u.uid)
            join ebh_teachers t on(t.teacherid=u.uid) where rt.crid='.$crid;
        }
        $sql .= ' limit 1';
        $res = Ebh()->db->query($sql)->row_array();
        return $res['c'];
    }

}