<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 11:04
 */
class UserpermisionsModel{

    /**
     * 校验用户是否拥有课件权限
     * @param $uid
     * @param $folderid
     * @return bool
     */
    public function check($crid,$uid,$folderid){
        $userModel = new UserModel();
        $user = $userModel->getUserByUid($uid);
        if(!$user){
            return false;
        }
        $classRoomModel = new ClassRoomModel();
        $classRoomInfo = $classRoomModel->getModel($crid);
        if(!$classRoomInfo){
            return false;
        }
        $sql = 'select crid from ebh_folders where folderid='.$folderid;
        $folder  = Ebh()->db->query($sql)->row_array();
        if(!$folder){
            return false;
        }

        //如果不是分成网校
        if($classRoomInfo['isschool'] != 7 ){
            if($classRoomInfo['crid'] !=  $folder['crid']){
                return false;
            }

            $classStudent = new ClassstudentsModel();
            $classId = $classStudent->getClassIdByUid($uid);
            //如果课程不在用户的班级类
            $sql = 'select count(classid) as count from  ebh_classcourses where classid='.$classId.' and folderid='.$folderid;
            $rs = Ebh()->db->query($sql)->row_array();
            if(!$rs || $rs['count'] == 0){
                return false;
            }
            return true;
        }

        if($user['groupid'] == 6){
            //如果是学生 判断userpermision表
            $sql = 'select count(pid) as count from ebh_userpermisions where  folderid='.$folderid.' and uid='.$uid.' and crid='.$crid.' and enddate >'.SYSTIME;
            $rs  = Ebh()->db->query($sql)->row_array();
            if($rs && $rs['count'] > 0){
                return true;
            }else{
                return false;
            }
        }else{
            //如果是老师 查看老师是否拥有此课程权限
            $sql = 'select count(crid) as count from ebh_teacherfolders where folderid='.$folderid.' and tid='.$uid.' and crid='.$crid;
            $rs  = Ebh()->db->query($sql)->row_array();
            if($rs && $rs['count'] > 0){
                return true;
            }else{
                return false;
            }
        }

    }




    /**
     *根据用户编号和cwid编号获取权限(单课收费)
     */
    public function getPermissionByCwId($cwid,$uid){
        $sql = "select p.pid,p.itemid,p.type,p.powerid,p.uid,p.crid,p.folderid,p.cwid,p.startdate,p.enddate,p.dateline from ebh_userpermisions p where p.cwid=$cwid and p.uid = $uid";
        return Ebh()->db->query($sql)->row_array();
    }
    public function getPermissionDomainByFolderId($folderid,$uid){
        $sql ='select p.enddate,f.crid from `ebh_userpermisions` p left join `ebh_folders` f on (p.folderid = f.folderid) where p.folderid ='.$folderid.' and p.itemid<>0 and p.cwid=0 and p.uid='.$uid;
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     *根据用户编号和folderid编号获取权限
     */
    public function getPermissionByFolderId($folderid,$uid,$crid=0) {
        $sql = "select p.pid,p.itemid,p.type,p.powerid,p.uid,p.crid,p.folderid,p.cwid,p.startdate,p.enddate,p.dateline,c.domain from ebh_userpermisions p left join `ebh_classrooms` c on (p.crid = c.crid ) where p.folderid=$folderid and p.itemid<>0 and p.cwid=0 and p.uid = $uid";
        if(!empty($crid)){
            $sql.= ' and p.crid='.$crid;
        }
        return Ebh()->db->query($sql)->row_array();
    }
    /**
     *根据用户编号和itemid编号获取权限
     */
    public function getPermissionByItemIds($param) {
        if(empty($param['itemids']) || empty($param['uid']) || empty($param['crid'])){
            return array();
        }
        $sql = 'select pid,itemid,p.uid,p.crid,p.folderid,p.cwid,p.dateline from ebh_userpermisions p';

        $enddate = SYSTIME - 86400;
        $wherearr[] = 'p.enddate>'.$enddate;
        $wherearr[] = 'uid='.$param['uid'];
        $wherearr[] = 'crid='.$param['crid'];
        $wherearr[] = 'itemid in('.$param['itemids'].')';
        $sql.= ' where '.implode(' AND ',$wherearr);
        return Ebh()->db->query($sql)->list_array('itemid');
    }

    /**
     *根据用户编号和itemid编号获取权限
     */
    public function getPermissionByItemId($itemid,$uid) {
        $sql = "select p.pid,p.itemid,p.type,p.powerid,p.uid,p.crid,p.folderid,p.cwid,p.startdate,p.enddate,p.dateline from ebh_userpermisions p where p.itemid=$itemid and p.uid = $uid";
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     *根据订单明细内容生成订单信息
     */
    public function addPermission($param = array()) {
        if(empty($param))
            return FALSE;
        $setarr = array();
        if(!empty($param['itemid']))
            $setarr['itemid'] = $param['itemid'];
        if(!empty($param['type']))
            $setarr['type'] = $param['type'];
        if(!empty($param['powerid']))
            $setarr['powerid'] = $param['powerid'];
        if(!empty($param['uid']))
            $setarr['uid'] = $param['uid'];
        if(!empty($param['crid']))
            $setarr['crid'] = $param['crid'];
        if(!empty($param['folderid']))
            $setarr['folderid'] = $param['folderid'];
        if(!empty($param['cwid']))
            $setarr['cwid'] = $param['cwid'];
        if(!empty($param['startdate']))
            $setarr['startdate'] = $param['startdate'];
        if(!empty($param['enddate']))
            $setarr['enddate'] = $param['enddate'];
        if(!empty($param['dateline']))
            $setarr['dateline'] = $param['dateline'];
        else
            $setarr['dateline'] = SYSTIME;
        $pid = Ebh()->db->insert('ebh_userpermisions',$setarr);
        return $pid;
    }

    /**
     *更新订单信息，如果包含明细，则同时更新明细信息
     */
    public function updatePermission($param = array()) {
        if(empty($param) || empty($param['pid']))
            return FALSE;
        $setarr = array();
        $wherearr = array('pid'=>$param['pid']);
        if(!empty($param['itemid']))
            $setarr['itemid'] = $param['itemid'];
        if(!empty($param['type']))
            $setarr['type'] = $param['type'];
        if(!empty($param['powerid']))
            $setarr['powerid'] = $param['powerid'];
        if(!empty($param['uid']))
            $setarr['uid'] = $param['uid'];
        if(!empty($param['crid']))
            $setarr['crid'] = $param['crid'];
        if(!empty($param['folderid']) && empty($param['cwid'])){ //课程开通,排除课件的
            $setarr['folderid'] = $param['folderid'];
            $setarr['cwid'] = 0;
        }
        if(!empty($param['cwid']))
            $setarr['cwid'] = $param['cwid'];
        if(!empty($param['startdate']))
            $setarr['startdate'] = $param['startdate'];
        if(!empty($param['enddate']))
            $setarr['enddate'] = $param['enddate'];
        $afrows = Ebh()->db->update('ebh_userpermisions',$setarr,$wherearr);
        return $afrows;
    }

    /**
     * 获取课程购买数
     * @param $crid
     * @param $folderid
     * @return mixed
     */
    public function getCountByFolderId($crid,$folderid){
        $sql = 'select count(pid) as count from ebh_userpermisions where folderid='.$folderid.' and crid='.$crid;

        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

    /**
     *获取用户已开通的课程
     */
    public function getUserPayFolderList($param = array()) {
        if(empty($param['uid']))
            return FALSE;
        $sql = "select p.pid,p.itemid,p.crid,p.folderid from ebh_userpermisions p";
        $wherearr = array();
        $wherearr[] = 'p.uid='.$param['uid'];
        if(!empty($param['crid'])) {
            $wherearr[] = 'p.crid='.$param['crid'];
        }
        if(!empty($param['filterdate'])) {	//过滤已过期
            $enddate = SYSTIME - 86400;
            $wherearr[] = 'p.enddate>'.$enddate;
        }
        if(!empty($param['folderid'])){
            $wherearr[] = 'p.folderid='.$param['folderid'];
        }
        $wherearr[] = 'p.cwid=0';
        $wherearr[] = 'p.itemid<>0';
        $sql .= ' WHERE '.implode(' AND ',$wherearr);
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     *获取用户已开通的课件
     */
    public function getUserPayCwList($param){
        if(empty($param['uid']))
            return FALSE;
        $sql = "select p.pid,p.cwid,p.crid,p.folderid from ebh_userpermisions p join ebh_roomcourses rc on p.cwid=rc.cwid";
        $wherearr = array();
        $wherearr[] = 'p.uid='.$param['uid'];
        if(!empty($param['crid'])) {
            $wherearr[] = 'p.crid='.$param['crid'];
        }
        if(!empty($param['filterdate'])) {	//过滤已过期
            $enddate = SYSTIME - 86400;
            $wherearr[] = 'p.enddate>'.$enddate;
        }
        if(!empty($param['folderid'])){
            $wherearr[] = 'p.folderid='.$param['folderid'];
        }
        if(!empty($param['cwids'])){
            $wherearr[] = 'p.cwid in('.$param['cwids'].')';
        }
        $wherearr[] = 'p.cwid<>0';
        $sql .= ' WHERE '.implode(' AND ',$wherearr);
        return Ebh()->db->query($sql)->list_array();
    }
	/**
     * (删除方法)检查课程开通权限
     * @param array $folderids 课程ID集
     * @param int $uid 学生ID
     * @param int $crid 网校ID
     * @return array
     */
    public function checkPermission($folderids, $uid, $crid) {
        $now = SYSTIME - 86400;
        $wheres = array(
            '`uid`='.$uid,
            '`crid`='.$crid,
            '`cwid`=0',
            '`enddate`>='.$now
        );
        if (!empty($folderids)) {
            if (is_array($folderids)) {
                $wheres[] = '`folderid` IN('.implode(',', $folderids).')';
            } else {
                $wheres[] = '`folderid`='.intval($folderids);
            }
        }
        $sql = 'SELECT `folderid` FROM `ebh_userpermisions` WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_field('folderid', 'folderid');
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 获取学生权限，包括过期的权限
     * @param int $uid 学生ID
     * @param int $crid 网校ID
     * @param int $type 权限类型：0-课程，1-课件
     * @return array
     */
    public function getPermissions($uid, $crid, $type = 0) {
        $wheres = array(
            '`uid`='.$uid,
            '`crid`='.$crid
        );
        if ($type == 1) {
            $wheres[] = '`cwid`>0';
            $sql = 'SELECT `cwid`,MAX(`enddate`) AS `enddate` FROM `ebh_userpermisions` WHERE '.implode(' AND ', $wheres).' GROUP BY `cwid`';
            return Ebh()->db->query($sql)->list_array('cwid');
        } else {
            $wheres[] = '`cwid`=0';
            $sql = 'SELECT `folderid`,MAX(`enddate`) AS `enddate` FROM `ebh_userpermisions` WHERE '.implode(' AND ', $wheres).' GROUP BY `folderid`';
            return Ebh()->query($sql)->list_array('folderid');
        }
    }

    /**
     * 通过条件获取用户列表
     * @param $param
     * @return mixed
     */
    public function getUserList($param){
        $sql = 'select distinct u.uid,u.username,u.realname,u.nickname,u.face,u.groupid,u.sex,u.mobile,c.classid,c.classname,ll.ip,logid,ll.dateline as lastlogintime from ebh_userpermisions up 
                join ebh_users u on u.uid=up.uid
                left join ebh_loginlogs ll on  ll.logid = (select max(logid) from ebh_loginlogs where uid=u.uid)
                left join ebh_classstudents cs on cs.uid=u.uid
                left join ebh_classes c on c.classid=cs.classid and c.crid='.$param['crid'];
        $wherearr = array();

        if(!empty($param['crid'])){
            $wherearr[] = 'up.crid='.$param['crid'];
            $wherearr[] = 'c.crid='.$param['crid'];
        }
        if(!empty($param['folderid'])){
            $wherearr[] = 'up.folderid='.$param['folderid'];
        }
        $sql .= ' WHERE '.implode(' AND ',$wherearr);
        return Ebh()->db->query($sql)->list_array();
    }
}