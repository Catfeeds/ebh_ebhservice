<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class TeacherModel{

    /**
     * 获取教师课件统计数量
     * @param $crid
     * @param array $param
     * @param bool $filterEmpty 过滤无课件课程
     * @return int
     */
    public function teacherCoursewaresReportCount($crid,$param = array(), $filterEmpty = false){
        if($crid <= 0){
            return 0;
        }
        if ($filterEmpty) {
            return $this->teacherCoursewaresReportCountFilterEmpty(intval($crid), $param);
        }
        $sql = "select count(f.folderid) as count
                from ebh_folders f
                join ebh_teacherfolders  tf on (tf.crid = {$crid} and tf.folderid=f.folderid)
                join ebh_users u on (u.uid=tf.tid)";
        $wherearr[] = 'f.folderid > 0';
        $wherearr[] = 'f.crid='.$crid;
        $wherearr[] = 'tf.crid='.$crid;
        if(!empty($param['folderid'])){
            $wherearr[] = 'f.folderid='.$param['folderid'];
        }

        if (!empty($param['q'])){
            $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }

        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }

        if(isset($param['limit'])){
            $sql .= ' LIMIT '.$param['limit'];
        }

        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];

        /*$sql = "select count(c.cwid) as count
                from ebh_coursewares c
                left join ebh_roomcourses rc  on (rc.cwid = c.cwid and rc.crid={$crid})
                left join ebh_folders f  on (f.folderid=rc.folderid)
                left join ebh_teachers t  on (t.teacherid=c.uid)
                left join ebh_users u  on (u.uid=c.uid)";
        $wherearr[] = 'f.folderid > 0';
        $wherearr[] = 'rc.crid='.$crid;
        if(!empty($param['folderid'])){
            $wherearr[] = 'f.folderid='.$param['folderid'];
        }
        if (!empty($param['q'])){
            $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }
        if(!empty($param['starttime'])){
            $wherearr[] = 'c.dateline >='.$param['starttime'];
        }
        if(!empty($param['endtime'])){
            $wherearr[] = 'c.dateline <='.$param['endtime'];
        }


        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];*/
    }

    /**
     * 教师课件统计
     */
    public function teacherCoursewaresReport($crid,$param = array(), $filterEmpty = false){
        if($crid <= 0){
            return array();
        }
        if ($filterEmpty) {
            $lists = $this->teacherCoursewaresReportFilterEmpty($crid, $param);
        } else {
            $sql = "select f.folderid,f.foldername,u.uid,u.username,u.realname,u.face,u.groupid,u.sex
                from ebh_folders f
                join ebh_teacherfolders  tf on (tf.crid = {$crid} and tf.folderid=f.folderid)
                join ebh_users u on (u.uid=tf.tid)";
            $wherearr[] = 'f.folderid > 0';
            $wherearr[] = 'f.crid='.$crid;
            $wherearr[] = 'tf.crid='.$crid;
            if(!empty($param['folderid'])){
                $wherearr[] = 'f.folderid='.$param['folderid'];
            }

            if (!empty($param['q'])){
                $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
            }

            if(!empty($wherearr)){
                $sql.= ' where '.implode(' AND ',$wherearr);
            }
            if(!empty($param['order'])){
                $sql.= ' order by '.$param['order'];
            }

            if(isset($param['limit'])){
                $sql .= ' LIMIT '.$param['limit'];
            }
            $lists = Ebh()->db->query($sql)->list_array();
        }
        if (empty($lists)) {
            return array();
        }

        $tidArr  = array();
        $folderIdArr = array();
        $folderList = array();

        foreach ($lists as $folder){
            $tidArr[] = $folder['uid'];
            $folderIdArr[] = $folder['folderid'];
            $folderList[$folder['uid'].'_'.$folder['folderid']] = $folder;
            $folderList[$folder['uid'].'_'.$folder['folderid']]['study_count'] = 0;
            $folderList[$folder['uid'].'_'.$folder['folderid']]['course'] = array();
        }
        $tidArr = array_unique($tidArr);
        $tidStr = implode(',',$tidArr);
        $folderIdArr = array_unique($folderIdArr);
        $folderIdStr  = implode(',',$folderIdArr);
        $sql = "select rc.folderid,c.uid,c.cwid,c.title,c.dateline,c.cwsize,c.cwlength,c.reviewnum,c.zannum
           
            from ebh_coursewares c
            left join ebh_roomcourses rc  on (rc.cwid = c.cwid and rc.crid={$crid})";
        //   (select count(attid) from ebh_attachments where cwid=c.cwid) as attachments_count,
        //(select count(logid) from ebh_playlogs where cwid=c.cwid and totalflag=0) as study_count
        $coursesWhere[] = ' rc.crid='.$crid;
        $coursesWhere[] = ' c.uid in ('.$tidStr.')';
        $coursesWhere[] = ' rc.folderid in ('.$folderIdStr.')';
        if(!empty($param['starttime'])){
            $coursesWhere[] = 'c.dateline >='.$param['starttime'];
        }
        if(!empty($param['endtime'])){
            $coursesWhere[] = 'c.dateline <='.$param['endtime'];
        }
        if(!empty($coursesWhere)){
            $sql.= ' where '.implode(' AND ',$coursesWhere);
        }
        //log_message('获取主数据sql:'.$sql);
        $courses =  Ebh()->db->query($sql)->list_array();
        //获取课件id
        $cwidArr = array_column($courses,'cwid');
        $cwidArr = array_unique($cwidArr);
        //获取课件的附件数量
        if (!empty($cwidArr)) {
            $attachSql  = 'SELECT COUNT(*) `attachments_count`,`cwid` FROM `ebh_attachments` WHERE `cwid` IN(' . implode(',', $cwidArr) . ') GROUP BY `cwid`';
            //log_message('获取课件附件：'.$attachSql);
            $attachList = Ebh()->db->query($attachSql)->list_array('cwid');
            //获取课件的学习次数
            $studySql  = 'SELECT COUNT(*) `study_count`,`cwid` FROM `ebh_playlogs` WHERE `totalflag`=0 AND `cwid` IN(' . implode(',', $cwidArr) . ') GROUP BY `cwid`';
            //log_message('获取学习次数：'.$studySql);
            $studyList = Ebh()->db->query($studySql)->list_array('cwid');
        }

        foreach ($courses as $course){
            $cwid                        = $course['cwid'];
            $attachCount                 = isset($attachList[$cwid]) && $attachList[$cwid]['attachments_count'] > 0 ? $attachList[$cwid]['attachments_count'] : 0;//课件附加数
            $studyCount                  = isset($studyList[$cwid]) && $studyList[$cwid]['study_count'] > 0 ? $studyList[$cwid]['study_count'] : 0;//课件学习次数
            $course['study_count']       = $studyCount;
            $course['attachments_count'] = $attachCount;
            $key                         = $course['uid'] . '_' . $course['folderid'];
            if (isset($folderList[$key])) {
                $folderList[$key]['study_count'] += $studyCount;//累计学习次数
                array_push($folderList[$key]['course'], $course);
                //$folderList[$key]['course'][] = $course;
            } else {
                $folderList[$key]['course'] = [];
                array_push($folderList[$key]['course'], $course);
                $folderList[$key]['study_count'] = $studyCount;
            }

        }
        return array_values($folderList);
        /*$sql = "select c.cwid,c.title,c.dateline,c.cwsize,c.cwlength,c.reviewnum,c.zannum,f.folderid,f.foldername,u.username,t.realname,u.face,u.groupid,u.sex,
                (select count(attid) from ebh_attachments where cwid=c.cwid) as attachments_count,
                (select count(logid) from ebh_playlogs where cwid=c.cwid and totalflag=0) as study_count
                from ebh_coursewares c
                left join ebh_roomcourses rc  on (rc.cwid = c.cwid and rc.crid={$crid})
                left join ebh_folders f  on (f.folderid=rc.folderid)
                left join ebh_teachers t  on (t.teacherid=c.uid)
                left join ebh_users u  on (u.uid=c.uid)";
        $wherearr[] = 'f.folderid > 0';
        $wherearr[] = 'rc.crid='.$crid;
        if(!empty($param['folderid'])){
            $wherearr[] = 'f.folderid='.$param['folderid'];
        }
        if (!empty($param['q'])){
            $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }
        if(!empty($param['starttime'])){
            $wherearr[] = 'c.dateline >='.$param['starttime'];
        }
        if(!empty($param['endtime'])){
            $wherearr[] = 'c.dateline <='.$param['endtime'];
        }


        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }

        if(isset($param['limit'])){
            $sql .= ' LIMIT '.$param['limit'];
        }
        $lists = Ebh()->db->query($sql)->list_array();

        foreach ($lists as $course){

        }

        return Ebh()->db->query($sql)->list_array();*/
    }

    public function teacherCoursewaresReportFilterEmpty($crid, $param = array()) {
        $whereArr = array(
            '`b`.`crid`='.$crid,
            '`b`.`tid`>0',
            '`e`.`status`=1'
        );
        if(!empty($param['folderid'])){
            $whereArr[] = '`b`.`folderid`='.intval($param['folderid']);
        }

        if (!empty($param['q'])){
            $whereArr[] = '(`c`.`username` LIKE \'%' . Ebh()->db->escape_str($param['q']) . '%\' OR `c`.`realname` LIKE \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }
        $sql = 'SELECT `c`.`uid`,`c`.`username`,`c`.`realname`,`c`.`sex`,`c`.`groupid`,`c`.`face`,`a`.`folderid`,`a`.`foldername`,SUM(`e`.`cwlength`) AS `cwlength` FROM `ebh_folders` `a`'.
            ' JOIN `ebh_teacherfolders` `b` ON `a`.`folderid`=`b`.`folderid`'.
            ' LEFT JOIN `ebh_users` `c` ON `b`.`tid`=`c`.`uid`'.
            ' LEFT JOIN `ebh_roomcourses` `d` ON `b`.`folderid`=`d`.`folderid`'.
            ' LEFT JOIN `ebh_coursewares` `e` ON `d`.`cwid`=`e`.`cwid` AND `b`.`tid`=`e`.`uid`'.
            ' WHERE '.implode(' AND ', $whereArr).
            ' GROUP BY `b`.`tid`,`b`.`folderid` HAVING `cwlength`>0';
        if(!empty($param['order'])){
            $sql .= ' ORDER BY '.$param['order'];
        }

        if(isset($param['limit'])){
            $sql .= ' LIMIT '.$param['limit'];
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 教师统计条目数量
     * @param $crid
     * @param array $param
     * @return int
     */
    public function teacherReportCount($crid,$param = array(), $filterEmpty = false){
        if($crid <= 0){
            return 0;
        }
        if ($filterEmpty) {
            return $this->teacherReportCountFilterEmpty($crid, $param);
        }
        $sql = "SELECT count(rt.tid) as count
			from ebh_roomteachers rt
			join ebh_users u on(rt.tid=u.uid)
			join ebh_teachers t on(t.teacherid=u.uid)";


        if(!empty($param['classid'])){
            $sql.= ' join ebh_classteachers ct on(ct.uid=u.uid)';
            if (!empty($param['isenterprise'])) {
                $sql .= ' left join `ebh_classes` `cc` ON `ct`.`classid`=`cc`.`classid`';
                $dept = Ebh()->db->query('SELECT `lft`,`rgt` FROM `ebh_classes` WHERE `classid`='.intval($param['classid']))->row_array();
                if (empty($dept)) {
                    return 0;
                }
                $wherearr[] = '`cc`.`lft`>='.$dept['lft'];
                $wherearr[] = '`cc`.`rgt`<='.$dept['rgt'];
            } else {
                $wherearr[] = 'ct.classid='.$param['classid'];
            }
        }
        if(!empty($param['groupid'])){
            $sql.= ' join ebh_teachergroups tg on(tg.tid=u.uid)';
            $wherearr[] = 'tg.groupid='.$param['groupid'];
        }
        $wherearr[] = 'rt.crid='.$crid;
        if (!empty($param['q'])){
            $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

    public function teacherReportCountFilterEmpty($crid,$param = array()) {

    }

    /**
     * 教师课件统计（过滤空课件）
     * @param $crid
     * @param array $param
     * @return int
     */
    public function teacherCoursewaresReportCountFilterEmpty($crid, $param = array()) {
        $whereArr = array(
            '`b`.`crid`='.$crid,
            '`b`.`tid`>0',
            '`e`.`status`=1'
        );
        if(!empty($param['folderid'])){
            $whereArr[] = '`b`.`folderid`='.intval($param['folderid']);
        }

        if (!empty($param['q'])){
            $whereArr[] = '(`c`.`username` LIKE \'%' . Ebh()->db->escape_str($param['q']) . '%\' OR `c`.`realname` LIKE \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }
        $sql = 'SELECT COUNT(1) AS `c` FROM ('.
            'SELECT SUM(`e`.`cwlength`) AS `cwlength` FROM `ebh_folders` `a`'.
            ' JOIN `ebh_teacherfolders` `b` ON `a`.`folderid`=`b`.`folderid`'.
            ' LEFT JOIN `ebh_users` `c` ON `b`.`tid`=`c`.`uid`'.
            ' LEFT JOIN `ebh_roomcourses` `d` ON `b`.`folderid`=`d`.`folderid`'.
            ' LEFT JOIN `ebh_coursewares` `e` ON `d`.`cwid`=`e`.`cwid` AND `b`.`tid`=`e`.`uid`'.
            ' WHERE '.implode(' AND ', $whereArr).
            ' GROUP BY `b`.`tid`,`b`.`folderid` HAVING `cwlength`>0'.
            ') AS `counts`';
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret['c'])) {
            return 0;
        }
        return $ret['c'];
    }

    /**
     * 教师统计
     */
    public function teacherReport($crid,$param = array()){
        if($crid <= 0){
            return array();
        }
        $endCoursewaresSql = $endReviewSql = $endSchexamsSql = $startSql = $endSql = $starAdkQuestionSql = $startSchexamsSql = $startReviewSql = $startCoursewaresSql = $endAdkQuestionSql = '';
        if(!empty($param['starttime'])){
            $startSql = ' and dateline >='.$param['starttime'];
            $starAdkQuestionSql = 'and a.dateline >= '.$param['starttime'];
            $startSchexamsSql = ' and se.dateline >= '.$param['starttime'];
            $startReviewSql = ' and r.dateline >= '.$param['starttime'];
            $startCoursewaresSql = ' and c.dateline >= '.$param['starttime'];

        }

        if(!empty($param['endtime'])){
            $endSql = ' and dateline <='.$param['endtime'];
            $endAdkQuestionSql =  'and a.dateline <= '.$param['endtime'];
            $endSchexamsSql = ' and se.dateline <= '.$param['endtime'];
            $endReviewSql = ' and r.dateline <= '.$param['endtime'];
            $endCoursewaresSql = ' and c.dateline <= '.$param['endtime'];
        }
        $sql = "SELECT t.teacherid,t.realname,u.username,u.logincount,u.face,u.groupid,u.sex,
			ifnull((select count(distinct(q.qid)) from ebh_askquestions q join ebh_askanswers a on q.qid=a.qid where a.uid = u.uid and q.crid={$crid} {$starAdkQuestionSql} {$endAdkQuestionSql} group by a.uid),0) as answernum,
			ifnull((select count(tid) from ebh_folders f join ebh_teacherfolders tf on f.folderid=tf.folderid  where f.crid={$crid} AND tf.tid = u.uid group by tf.tid),0) as foldernum,
			ifnull((select count(se.uid) from ebh_schexams se where se.crid={$crid} AND se.uid = u.uid {$startSchexamsSql} {$endSchexamsSql} group by se.uid),0) as examnum,
			ifnull((select sum(se.quescount) from ebh_schexams se where se.crid={$crid} AND se.uid = u.uid {$startSchexamsSql} {$endSchexamsSql} group by se.uid) ,0) as examquesnum,
			ifnull((select count(cw.cwid) from ebh_coursewares cw join ebh_roomcourses rc on cw.cwid=rc.cwid join ebh_reviews r on r.toid = rc.cwid where rc.crid={$crid} AND cw.uid=u.uid {$startReviewSql} {$endReviewSql} group by cw.uid),0) as reviewnum,
			ifnull((select count(rc.cwid) from ebh_roomcourses rc join ebh_coursewares c on rc.cwid=c.cwid where rc.crid={$crid} AND c.uid=u.uid AND c.status=1 AND c.islive=1 {$startCoursewaresSql} {$endCoursewaresSql}  group by c.uid),0) as livecoursenum,
			ifnull((select count(rc.cwid) from ebh_roomcourses rc join ebh_coursewares c on rc.cwid=c.cwid left join ebh_sources s on s.sid=c.sourceid where rc.crid={$crid} AND c.uid=u.uid AND c.status=1 AND c.islive=0 and (c.ism3u8=1 or s.filesuffix='flv' or s.filesuffix='m3u8' or s.filesuffix='avi'  or s.filesuffix='mp4'  or s.filesuffix='mpg' or s.filesuffix='mov'  ) {$startCoursewaresSql} {$endCoursewaresSql}  group by c.uid),0) as videocoursenum,
			ifnull((select count(logid) from ebh_signlogs where uid=u.uid and crid={$crid} {$startSql} {$endSql} group by uid),0) as signnum,
			ifnull((select group_concat(".(!empty($param['isenterprise']) ? 'c.path':'c.classname').") from ebh_classteachers ct join ebh_classes c on ct.classid=c.classid where c.crid={$crid} AND ct.uid=u.uid group by ct.uid) ,'')as class
			from ebh_roomteachers rt
			join ebh_users u on(rt.tid=u.uid)
			join ebh_teachers t on(t.teacherid=u.uid)";


        if(!empty($param['classid'])){
            $sql.= ' join ebh_classteachers ct on(ct.uid=u.uid)';
            if (!empty($param['isenterprise'])) {
                $sql .= ' left join `ebh_classes` `cc` ON `ct`.`classid`=`cc`.`classid`';
                $dept = Ebh()->db->query('SELECT `lft`,`rgt` FROM `ebh_classes` WHERE `classid`='.intval($param['classid']))->row_array();
                if (empty($dept)) {
                    return 0;
                }
                $wherearr[] = '`cc`.`lft`>='.$dept['lft'];
                $wherearr[] = '`cc`.`rgt`<='.$dept['rgt'];
            } else {
                $wherearr[] = 'ct.classid='.$param['classid'];
            }

        }
        if(!empty($param['groupid'])){
            $sql.= ' join ebh_teachergroups tg on(tg.tid=u.uid)';
            $wherearr[] = 'tg.groupid='.$param['groupid'];
        }
        $wherearr[] = 'rt.crid='.$crid;
        if (!empty($param['q'])){
            $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }

        if(isset($param['limit'])){
            $sql .= ' LIMIT '.$param['limit'];
        }
        return Ebh()->db->query($sql)->list_array();
    }



    /*
	获取学校的教师列表
	@param int $crid
	@param array $param
    @param bool $setKey 是否以uid做为数组键
	*/
    public function getRoomTeacherList($crid,$param = array(), $setKey = false){
        $sql = 'SELECT u.credit,u.sex,u.face,u.mobile,u.uid,u.username,u.groupid,u.lastlogintime,u.lastloginip,u.mysign,rt.status,rt.mobile tmobile,rt.role,t.teacherid,t.realname,t.profile,0 as folderid,t.professionaltitle
			from ebh_roomteachers rt
			join ebh_users u on(rt.tid=u.uid)
			join ebh_teachers t on(t.teacherid=u.uid)';
        if (isset($param['groupid']))
        {
            $sql = 'SELECT u.sex,u.face,u.mobile,u.uid,u.username,u.groupid,u.lastlogintime,u.lastloginip,u.status,t.teacherid,rt.mobile tmobile,rt.role,t.realname,tg.groupid,t.profile,t.professionaltitle,0 as folderid
			from ebh_roomteachers rt
			join ebh_users u on(rt.tid=u.uid)
			join ebh_teachers t on(t.teacherid=u.uid)
			left join ebh_teachergroups tg on t.teacherid=tg.tid';
        }
        $wherearr[] = 'rt.crid='.$crid;
        if (isset($param['q'])){
            $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }
        if (isset($param['role'])) {
            if (is_array($param['role'])) {
                $roles = $param['role'];
            } else {
                $roles = array($param['role']);
            }
            $roles = array_map('intval', $roles);
            $roles = array_filter($roles, function($role) {
                return $role > 0;
            });
            if (!empty($roles)) {
                $wherearr[] = 'rt.role in('.implode(',', $roles).')';
            }
        }

        if(isset($param['schoolname'])){
            $wherearr[] = 'u.schoolname = \''.Ebh()->db->escape_str($param['schoolname']).'\'';
        }
        if(!empty($param['uid'])){
            if (is_array($param['uid'])) {
                $wherearr[] = 'u.uid in('.implode(',', $param['uid']).')';
            } else {
                $wherearr[] = 'u.uid = '.$param['uid'];
            }
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }
        if(isset($param['limit'])){
            $sql .= ' LIMIT '.$param['limit'];
        }
        return Ebh()->db->query($sql)->list_array($setKey ? 'uid' : '');
    }

    /**
     * 获取学校的教师数量
     * @param $crid
     * @param array $param
     * @return mixed
     */
    public function getRoomTeacherCount($crid,$param = array()){
        $sql = 'select count(*) count from ebh_roomteachers rt
			join ebh_users u on(rt.tid=u.uid)
			join ebh_teachers t on(t.teacherid=u.uid)';
        $wherearr[] = 'rt.crid='.$crid;
        if (isset($param['q'])){
            $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }
        if (isset($param['role'])) {
            if (is_array($param['role'])) {
                $roles = $param['role'];
            } else {
                $roles = array($param['role']);
            }
            $roles = array_map('intval', $roles);
            $roles = array_filter($roles, function($role) {
                return $role > 0;
            });
            if (!empty($roles)) {
                $wherearr[] = 'rt.role in('.implode(',', $roles).')';
            }
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

    /**
     * 添加教师
     * @param $param
     * @return mixed
     */
    public function addTeacher($param) {
        $userArr = $param;
        if(!empty($param['password'])){
            $userArr['password'] = md5($param['password']);
        }
        $userArr['status'] = 1;
        $userArr['groupid'] = 5;
        if(!empty($param['mobile'])){
            unset($param['mobile']);//禁止向users表添加mobile
        }
        $uid = Ebh()->db->insert('ebh_users', $userArr);
        if($uid > 0){
            $teacherArr['teacherid'] = $uid;
            if(!empty($userArr['realname'])){
                $teacherArr['realname'] = $userArr['realname'];
            }
            if(isset($userArr['nickname'])){
                $teacherArr['nickname'] = $userArr['nickname'];
            }
            if(isset($param['sex'])){
                $teacherArr['sex'] = $userArr['sex'];
            }
            if(!empty($param['mobile'])){
                $teacherArr['mobile'] = $param['mobile'];
            }

            $res = Ebh()->db->insert('ebh_teachers', $teacherArr);
        }
        return $uid;
    }

    /**
     * 修改教师信息
     * @param $tid
     * @param $param
     * @return mixed
     */
    public function editTeacher($tid,$param){
        //修改teacher表信息
        $password = '';
        if (!empty($param['password'])) {
            $password = $param['password'];
            unset($param['password']);
        }
        $afrows =  Ebh()->db->update('ebh_teachers',$param,array('teacherid'=>$tid));

        if(!empty($password)){
            $param['password'] = md5($password);
        }
        if(!empty($param['mobile'])){
            unset($param['mobile']);//不允许更改user表的mobile
        }
        $afrows += Ebh()->db->update('ebh_users',$param,array('uid'=>$tid));
        
        return $afrows;
    }
	/*
	获取课程的教师列表
	@param array $param
	*/
	public function getCourseTeacherList($crid,$folderids=''){
		$sql = 'select tf.folderid,tf.tid,u.username,t.realname,u.sex,u.face
			from ebh_teacherfolders tf 
			join ebh_teachers t on t.teacherid=tf.tid
			join ebh_users u on tf.tid = u.uid
			join ebh_folders f on f.folderid = tf.folderid
			where tf.crid = '.$crid;
		if(!empty($folderids))
			$sql.=' AND f.folderid in('.$folderids.')';
	//echo $sql;
		return Ebh()->db->query($sql)->list_array();
	}


    /**
     * 启用/禁用教师帐号
     * @param $status 状态 0-用户无效，2-角色无效，1-有效
     * @param $uid 用户ID
     * @param $crid 所在网校ID
     */
    public function activate($status, $uid, $crid) {
        $status = intval($status);
        if (!in_array($status, array(0, 1, 2))) {
            return false;
        }
        $uid = intval($uid);
        $crid = intval($crid);
        return Ebh()->db->update('ebh_roomteachers', array('status' => $status), '`tid`='.$uid.' AND `crid`='.$crid);
    }
}