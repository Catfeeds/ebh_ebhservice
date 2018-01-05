<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:32
 */
class AttendancesModel{

    /**
     * 考勤记录是否存在
     * @param $param
     * @return int
     */
    public function exist($param){
        $sql = 'select count(id) as count from ebh_attendances';
        $wherearr = array();

        if(!empty($param['crid'])){
            $wherearr[] = 'crid='.$param['crid'];
        }
        if(!empty($param['cwid'])){
            $wherearr[] = 'cwid='.$param['cwid'];
        }
        if(!empty($param['uid'])){
            $wherearr[] = 'uid='.$param['uid'];
        }
        $sql .= ' WHERE ' . implode(' AND ', $wherearr);

        $res =  Ebh()->db->query($sql)->row_array();
        if($res && $res['count'] > 0){
            return true;
        }
        return false;

    }
    /**
     * 插入考勤数据
     * @param $param
     * @return mixed
     */
    public function insert($param){

        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['crid'])){
            $setarr['crid'] = $param['crid'];
        }
        if(!empty($param['cwid'])){
            $setarr['cwid'] = $param['cwid'];
        }
        $setarr['dateline'] = SYSTIME;
        return Ebh()->db->insert('ebh_attendances',$setarr);
    }


    /**
     * 通过条件获取用户数
     * @param $param
     * @return mixed
     */
    public function getUserCount($param){
        $sql = 'select count(distinct u.uid) as count from ebh_userpermisions up 
                join ebh_users u on u.uid=up.uid
                left join ebh_classstudents cs on cs.uid=u.uid
                left join ebh_classes c on c.classid=cs.classid and c.crid='.$param['crid'].
                ' left join ebh_attendances att on  att.uid = u.uid and att.cwid = '.$param['cwid'];
        $wherearr = array();

        if(!empty($param['crid'])){
            $wherearr[] = 'up.crid='.$param['crid'];
            $wherearr[] = 'c.crid='.$param['crid'];
        }
        if(!empty($param['folderid'])){
            $wherearr[] = 'up.folderid='.$param['folderid'];
        }
        if (!empty($param['classids'])) {
            $wherearr[] = 'c.classid in (' . implode(',',$param['classids']).')';
        }

        if(!empty($param['name'])){
            $wherearr[] = '( u.realname like \'%' . Ebh()->db->escape_str($param['name']) . '%\' or u.username like \'%' . Ebh()->db->escape_str($param['name']) . '%\') ';
        }

        if(!empty($param['begindate'])){
            $wherearr[] = 'att.dateline>='.$param['begindate'];
        }

        if(!empty($param['enddate'])){
            $wherearr[] = 'att.dateline<'.$param['enddate'];
        }

        if(isset($param['state'])){
            if($param['state'] == 1){
                $wherearr[] = 'att.dateline > 0';
            }else{
                $wherearr[] = 'att.dateline is null';
            }

        }


        $sql .= ' WHERE '.implode(' AND ',$wherearr);


        $res =  Ebh()->db->query($sql)->row_array();
        return $res['count'];
    }

    /**
     * 通过条件获取考勤用户列表
     * @param $param
     * @return mixed
     */
    public function getUserList($param){
        if(!isset($param['cwid'])){
            return array();
        }
        $sql = 'select distinct u.uid,u.username,u.realname,u.nickname,u.face,u.groupid,u.sex,u.mobile,c.classid,c.classname,ll.ip,logid,ll.dateline as lastlogintime,ifnull(att.dateline,0) as jointime from ebh_userpermisions up 
                join ebh_users u on u.uid=up.uid
                left join ebh_loginlogs ll on  ll.logid = (select max(logid) from ebh_loginlogs where uid=u.uid)
                left join ebh_classstudents cs on cs.uid=u.uid
                left join ebh_classes c on c.classid=cs.classid and c.crid=' . $param['crid'] .
                ' left join ebh_attendances att on  att.uid = u.uid and att.cwid = '.$param['cwid'];
        $wherearr = array();
        if (!empty($param['crid'])) {
            $wherearr[] = 'up.crid=' . $param['crid'];
            $wherearr[] = 'c.crid=' . $param['crid'];
        }
        if (!empty($param['folderid'])) {
            $wherearr[] = 'up.folderid=' . $param['folderid'];
        }

        if (!empty($param['classids'])) {
            $wherearr[] = 'c.classid in (' . implode(',',$param['classids']).')';
        }

        if(!empty($param['name'])){
            $wherearr[] = ' (u.realname like \'%' . Ebh()->db->escape_str($param['name']) . '%\' or u.username like \'%' . Ebh()->db->escape_str($param['name']) . '%\') ';
        }

        if(!empty($param['begindate'])){
            $wherearr[] = 'att.dateline>='.$param['begindate'];
        }

        if(!empty($param['enddate'])){
            $wherearr[] = 'att.dateline<'.$param['enddate'];
        }

        if(isset($param['state'])){
            if($param['state'] == 1){
                $wherearr[] = ' att.dateline > 0';
            }else{
                $wherearr[] = ' att.dateline is null ';
            }

        }


        $sql .= ' WHERE ' . implode(' AND ', $wherearr);
        if (!empty($queryarr['order'])){
            $sql .= ' ORDER BY ' . $param['order'];
        }else{
            $sql .= ' ORDER BY att.dateline DESC ';
        }
        if (!empty($param['limit'])) {
            $sql .= ' limit ' . $param['limit'];
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 读取出勤列表
     * @param $param
     * @return mixed
     */
    public function getClassAttendance($param){
        //应到人数sql 取班级报名课程人数
        $sql = 'select count(distinct u.uid) as count from ebh_userpermisions up 
                join ebh_users u on u.uid=up.uid
                left join ebh_classstudents cs on cs.uid=u.uid';


        $wherearr = array();

        if(!empty($param['crid'])){
            $wherearr[] = 'up.crid='.$param['crid'];
        }
        if (!empty($param['folderid'])) {
            $wherearr[] = 'up.folderid=' . $param['folderid'];
        }

        if (!empty($param['classids'])) {
            $wherearr[] = 'cs.classid = ac.classid ';
        }

        $sql .= ' WHERE ' . implode(' AND ', $wherearr);


        //已到人数统计sql

        $sql2 =  'select count(distinct att.uid) as count from ebh_attendances att
                 join ebh_classstudents cs on cs.uid=att.uid ';
        $wherearr = array();
        if (!empty($param['classids'])) {
            $wherearr[] = 'cs.classid = ac.classid ';
        }
        if (!empty($param['cwid'])) {
            $wherearr[] = 'att.cwid = '.$param['cwid'];
        }
        $sql2 .= ' WHERE ' . implode(' AND ', $wherearr);

        $sql3 = 'select ac.classid,ac.classname,('.$sql.') as student_count,('.$sql2.') as join_count from ebh_classes ac';
        $wherearr = array();
        if (!empty($param['classids'])) {
            $wherearr[] = 'ac.classid in (' . implode(',',$param['classids']).')';
        }

        $sql3 .= ' WHERE ' . implode(' AND ', $wherearr);

        return Ebh()->db->query($sql3)->list_array();
        //WHERE up.crid=10194 AND c.crid=10194 AND up.folderid=29774 AND c.classid in (9) ) as count from ebh_classes  where classid=9
    }
}