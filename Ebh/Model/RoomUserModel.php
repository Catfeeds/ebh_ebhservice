<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class RoomUserModel{



        /*
    修改学校学生信息
    @param array $param
    */
    public function editStudent($param){
        if(empty($param['crid'])) {
            return 0;
        }
        $crid = intval($param['crid']);
        if(!empty($param['cnname']))
            $ruarr['cnname'] = $param['cnname'];
        if(!empty($param['sex']))
            $ruarr['sex'] = $param['sex'];
        if(!empty($param['mobile']))
            $ruarr['mobile'] = $param['mobile'];
        $wherearr = array('crid'=>$param['crid'],'uid'=>$param['uid']);
        if(!empty($ruarr))
            $afrows = Ebh()->db->update('ebh_roomusers',$ruarr,$wherearr);

        if(!empty($param['classid'])){
            $csarr['classid'] = $param['classid'];
            $wherearr = array('uid'=>$param['uid'],'classid'=>$param['oldclassid']);

            $sql = 'select * from ebh_classstudents where uid='.$param['uid'].' and classid='.$param['oldclassid'];

            $rs = Ebh()->db->query($sql)->row_array();
            if(!$rs){
                $newid = Ebh()->db->insert('ebh_classstudents', array('classid' => $param['classid'], 'uid' => $param['uid']));
                $afrows = $newid > 0 ? 1 : 0;
            } else {
                $afrows = Ebh()->db->update('ebh_classstudents',$csarr,$wherearr);
            }

            $sql = 'select count(a.uid) as count from ebh_classstudents a join ebh_users b on a.uid=b.uid join ebh_members c on a.uid=c.memberid left join ebh_roomusers d on a.uid=d.uid where d.crid='.$crid.' and ifnull(c.memberid,0)>0 AND ifnull(b.uid,0)>0 and a.classid='.$param['classid'];
            $rs = Ebh()->db->query($sql)->row_array();
            $classCount = $rs['count'];
            $sql = 'select count(a.uid) as count from ebh_classstudents a join ebh_users b on a.uid=b.uid join ebh_members c on a.uid=c.memberid left join ebh_roomusers d on a.uid=d.uid where d.crid='.$crid.' and ifnull(c.memberid,0)>0 and ifnull(b.uid,0)>0 and a.classid='.$param['oldclassid'];
            $rs = Ebh()->db->query($sql)->row_array();
            $oldClassCount = $rs['count'];
            Ebh()->db->update('ebh_classes',array('stunum'=>$classCount),array('classid'=>$param['classid']));
            Ebh()->db->update('ebh_classes',array('stunum'=>$oldClassCount),array('classid'=>$param['oldclassid']));
        }
        return $afrows;
    }
    /**
     * 更新教室内的学员信息，需要带上$crid和$uid
     * @param type $param
     */
    public function update($param) {
        if (empty($param['crid']) || empty($param['uid']))
            return FALSE;
        $wherearr = array('crid'=>$param['crid'],'uid'=>$param['uid']);
        $setarr = array();
        if (!empty($param ['begindate'])) { //服务开始时间
            $setarr ['begindate'] = $param ['begindate'];
        }
        if (!empty($param ['enddate'])) {   //服务结束时间
            $setarr ['enddate'] = $param ['enddate'];
        }
        if (isset($param['cstatus'])) { //状态，1正常 0 锁定
            $setarr ['cstatus'] = $param['cstatus'];
        }
        if (!empty($param ['rbalance'])) {  //学员在教室内余额，单用于一个教室
            $setarr['rbalance'] = $param['rbalance'];
        }
        if(empty($setarr))
            return FALSE;
        $afrows = Ebh()->db->update('ebh_roomusers',$setarr,$wherearr);
        return $afrows;
    }
    /**
     * 插入学生信息
     * @param $param
     * @return bool
     */
    public function insert($param) {
        if (empty($param['crid']) || empty($param['uid']))
            return FALSE;
        $setarr = array();
        $setarr['crid'] = $param['crid'];
        $setarr['uid'] = $param['uid'];
        if (!empty($param ['cdateline'])) { //记录添加时间
            $setarr ['cdateline'] = $param ['cdateline'];
        } else {
            $setarr ['cdateline'] = time();
        }
        if (!empty($param ['begindate'])) { //服务开始时间
            $setarr ['begindate'] = $param ['begindate'];
        }
        if (!empty($param ['enddate'])) {   //服务结束时间
            $setarr ['enddate'] = $param ['enddate'];
        }
        if (!empty($param ['cnname'])) {   //学生真实姓名，此处只做存档用
            $setarr ['cnname'] = $param ['cnname'];
        }
        if (isset($param ['cstatus'])) { //状态，1正常 0 锁定
            $setarr ['cstatus'] = $param['cstatus'];
        }
        if (isset($param ['sex'])) {   //性别
            $setarr ['sex'] = $param ['sex'];
        }
        if (isset($param ['birthday'])) {   //出生日期
            $setarr ['birthday'] = $param ['birthday'];
        }
        if (!empty($param ['mobile'])) {   //联系方式
            $setarr ['mobile'] = $param ['mobile'];
        }
        if (!empty($param ['email'])) {   //邮箱
            $setarr ['email'] = $param ['email'];
        }

        $afrows = Ebh()->db->insert('ebh_roomusers',$setarr);
        return $afrows;
    }
    /**
     * 获取学生列表
     * @param $param
     * @return mixed
     */
    public function getStudentList($param){
        Ebh()->db->set_con(0);
        $wheres = array(
            '`a`.`crid`='.intval($param['crid'])
        );
        $filterClass = false;
        if (!empty($param['classid'])){
            $filterClass = true;
            $wheres[]= '`e`.`classid` = '.$param['classid'];
        }
        if (!empty($param['lft']) && !empty($param['rgt'])) {
            $filterClass = true;
            $wheres[] = '`e`.`lft`>='.intval($param['lft']);
            $wheres[] = '`e`.`rgt`<='.intval($param['rgt']);
        }
        if (!empty($param['q'])){
            $q = Ebh()->db->escape_str($param['q']);
            $wheres[]= '(`b`.`username` LIKE \'%'.$q.'%\' OR `b`.`realname` LIKE \'%'.$q.'%\')';
        }
        $fields = array('`a`.`crid`','`a`.cnname','`a`.`mobile` `smobile`','`a`.`cstatus` AS `status`','`b`.`email`','`b`.`mobile`','`b`.`uid`','`b`.`sex`','`b`.`username`','`b`.`groupid`','`b`.`realname`','`b`.`allowip`','`b`.`face`','`b`.`credit`','`b`.`dateline`','`c`.`birthdate`');
        if ($filterClass) {
            $fields[] = '`e`.`classid`';
            $fields[] = '`e`.`classname`';
            if (!empty($param['isEnterprise'])) {
                $fields[] = '`e`.`path`';
            }
            $fields = implode(',', $fields);
            $wheres[] = '`e`.`status`=0';
            $sql = 'SELECT '.$fields.' FROM `ebh_roomusers` `a` 
                    JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` 
                    JOIN `ebh_members` `c` ON `c`.`memberid`=`a`.`uid` 
                    LEFT JOIN `ebh_classstudents` `d` ON `d`.`uid`=`a`.`uid`
                    JOIN `ebh_classes` `e` ON `e`.`classid`=`d`.`classid` AND `e`.`crid`=`a`.`crid` 
                    WHERE '.implode(' AND ', $wheres);
            $sql .=' ORDER BY `a`.uid DESC ';
            if(isset($param['limit'])){
                $sql .= ' LIMIT '.$param['limit'];
            }else{
                $sql .= ' LIMIT 1000';
            }
            return Ebh()->db->query($sql)->list_array();
        }
        $fields = implode(',', $fields);
        $sql = 'SELECT '.$fields.' FROM `ebh_roomusers` `a` 
                    JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` 
                    JOIN `ebh_members` `c` ON `c`.`memberid`=`a`.`uid` 
                    WHERE '.implode(' AND ', $wheres);
        $sql .=' ORDER BY `a`.uid DESC ';
        if(isset($param['limit'])){
            $sql .= ' LIMIT '.$param['limit'];
        }else{
            $sql .= ' LIMIT 1000';
        }
        $students = Ebh()->db->query($sql)->list_array('uid');
        if (empty($students)) {
            return array();
        }
        $studentids = array_column($students, 'uid');
        $sql = 'SELECT `a`.`uid`,`b`.`classid`,`b`.`classname`,`b`.`path` FROM `ebh_classstudents` `a` JOIN `ebh_classes` `b` ON `b`.`classid`=`a`.`classid` WHERE a.uid in ('.implode(',', $studentids).') and b.crid='.$param['crid'].' and b.`status`=0';
        $classes = Ebh()->db->query($sql)->list_array('uid');
        array_walk($students, function(&$student, $uid, $classes) {
            if (!isset($classes[$uid])) {
                return;
            }
            $student['classid'] = $classes[$uid]['classid'];
            $student['classname'] = $classes[$uid]['classname'];
            $student['path'] = $classes[$uid]['path'];
        }, $classes);
        return array_values($students);
    }

    /**
     * 获取学生数量
     * @param array $param
     * @return mixed
     */
    public function getStudentCount($param){
        if (empty($param['crid'])) {
            return false;
        }
        $wheres = array(
            '`a`.`crid`='.intval($param['crid'])
        );
        $filterClass = false;
        if (!empty($param['classid'])) {
            $wheres[] = '`e`.`classid`='.intval($param['classid']);
            $filterClass = true;
        }
        if (!empty($param['lft']) && !empty($param['rgt'])) {
            $wheres[] = '`e`.`lft`>='.intval($param['lft']);
            $wheres[] = '`e`.`rgt`<='.intval($param['rgt']);
            $filterClass = true;
        }
        if (isset($param['q']) && $param['q'] != ''){
            $q = Ebh()->db->escape_str($param['q']);
            $wheres[]= '(`b`.`username` LIKE \'%'.$q.'%\' OR `b`.`realname` LIKE \'%'.$q.'%\')';
        }
        if ($filterClass) {
            $wheres[] = '`e`.`status`=0';
            $sql = 'SELECT COUNT(1) AS `count` FROM `ebh_roomusers` `a` 
                    JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` 
                    JOIN `ebh_members` `c` ON `c`.`memberid`=`a`.`uid` 
                    LEFT JOIN `ebh_classstudents` `d` ON `d`.`uid`=`a`.`uid`
                    JOIN `ebh_classes` `e` ON `e`.`classid`=`d`.`classid` AND `e`.`crid`=`a`.`crid` 
                    WHERE '.implode(' AND ', $wheres);
        } else {
            $sql = 'SELECT COUNT(1) AS `count` FROM `ebh_roomusers` `a` 
                JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` 
                JOIN `ebh_members` `c` ON `c`.`memberid`=`a`.`uid` 
                WHERE '.implode(' AND ', $wheres);
        }
        $count = Ebh()->db->query($sql)->row_array();
        if (empty($count['count'])) {
            return 0;
        }
        return $count['count'];
    }


    /**
     * 根据网校和学员编号获取学员在教室内的信息详情
     * @param type $crid
     * @param type $uid
     * @return type
     */
    public function getRoomuStudentDetail($crid,$uid) {
        $sql = "select ru.cstatus,ru.rbalance,ru.begindate,ru.enddate,ct.classid from ebh_roomusers ru left join ebh_classstudents ct on ct.uid=ru.uid where ru.crid=$crid and ru.uid=$uid";
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 网校学生统计条数
     * @param $crid
     * @param array $param
     * @return mixed
     */
    public function roomStudentReportCount($crid,$param = array()){
        $crid = intval($crid);
        $startSql = '';
        $starReviewSql = '';
        $starPlaylogsSql = '';
        $starExamSql = '';
        $endSql = '';
        $endReviewSql = '';
        $endPlaylogsSql = '';
        $endExamSql = '';
        if(!empty($param['starttime'])){
            $startSql = ' and dateline >='.$param['starttime'];
            $starReviewSql = ' and r.dateline >='.$param['starttime'];
            $starPlaylogsSql = ' and startdate >='.$param['starttime'];
            $starExamSql = ' and a.dateline >='.$param['starttime'];
        }

        if(!empty($param['endtime'])){
            $endSql = ' and dateline <='.$param['endtime'];
            $endReviewSql = ' and r.dateline <='.$param['endtime'];
            $endPlaylogsSql = ' and startdate <='.$param['endtime'];
            $endExamSql = ' and a.dateline <='.$param['endtime'];
        }
        $sql = "SELECT count(ru.uid) as count
			FROM ebh_roomusers ru
			LEFT JOIN ebh_users u ON ru.uid = u.uid
			join ebh_members m on u.uid=m.memberid
			LEFT JOIN ebh_classstudents st ON u.uid=st.uid
			LEFT JOIN ebh_classes cl ON st.classid = cl.classid";

        $wherearr[] = 'ru.crid='.$crid;
        $wherearr[] = 'cl.crid='.$crid;
        $wherearr[] = 'cl.status=0';

        if (!empty($param['q'])){
            $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }

        if(!empty($param['classid'])){
            if (!empty($param['isenterprise'])) {
                $root = Ebh()->db->query('SELECT `lft`,`rgt` FROM `ebh_classes` WHERE `classid`='.$param['classid'])->row_array();
                if (empty($root)) {
                    return array();
                }
                $wherearr[] = 'cl.lft>='.$root['lft'];
                $wherearr[] = 'cl.rgt<='.$root['rgt'];
            } else {
                $wherearr[] = 'cl.classid='.$param['classid'];
            }
        }
        if (!empty($param['isenterprise'])) {
            $wherearr[] = 'cl.category=0';
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

    /**
     * 获取指定网校学生统计列表
     * @param $crid
     * @param array $param
     */
    public function roomStudentReport($crid,$param = array()){
        $crid = intval($crid);
        $startSql = '';
        $starReviewSql = '';
        $starPlaylogsSql = '';
        $starExamSql = '';
        $endSql = '';
        $endReviewSql = '';
        $endPlaylogsSql = '';
        $endExamSql = '';
        if(!empty($param['starttime'])){
            $startSql = ' and dateline >='.$param['starttime'];
            $starReviewSql = ' and r.dateline >='.$param['starttime'];
            $starPlaylogsSql = ' and startdate >='.$param['starttime'];
            $starExamSql = ' and a.dateline >='.$param['starttime'];
        }

        if(!empty($param['endtime'])){
            $endSql = ' and dateline <='.$param['endtime'];
            $endReviewSql = ' and r.dateline <='.$param['endtime'];
            $endPlaylogsSql = ' and startdate <='.$param['endtime'];
            $endExamSql = ' and a.dateline <='.$param['endtime'];
        }
        $sql = "SELECT ru.crid,u.uid,u.username,u.realname,u.logincount,cl.classname,u.credit,u.face,u.groupid,u.sex".(!empty($param['isenterprise']) ? ',`cl`.`path`' : '')."
			FROM ebh_roomusers ru
			LEFT JOIN ebh_users u ON ru.uid = u.uid
			join ebh_members m on u.uid=m.memberid
			LEFT JOIN ebh_classstudents st ON u.uid=st.uid
			LEFT JOIN ebh_classes cl ON st.classid = cl.classid";

        $wherearr[] = 'ru.crid='.$crid;
        $wherearr[] = 'cl.crid='.$crid;
        $wherearr[] = 'cl.status=0';

        if (!empty($param['q'])){
            $wherearr[] = ' (u.username like \'%' . Ebh()->db->escape_str($param['q']) . '%\' or u.realname like \'%' . Ebh()->db->escape_str($param['q']) . '%\')';
        }

        if(!empty($param['classid'])){
            if (!empty($param['isenterprise'])) {
                $root = Ebh()->db->query('SELECT `lft`,`rgt` FROM `ebh_classes` WHERE `classid`='.intval($param['classid']))->row_array();
                if (empty($root)) {
                    return array();
                }
                $wherearr[] = 'cl.lft>='.$root['lft'];
                $wherearr[] = 'cl.rgt<='.$root['rgt'];
            } else {
                $wherearr[] = 'cl.classid='.$param['classid'];
            }
        }
        if (!empty($param['isenterprise'])) {
            $wherearr[] = 'cl.category=0';
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


        /*(select count(logid) from ebh_creditlogs where ruleid=22 and uid=u.uid {$startSql} {$endSql}) as sign_count,
			(select count(qid) from ebh_askquestions where crid=ru.crid and uid=u.uid {$startSql} {$endSql}) as ask_count,
			(select count(*) count from ebh_reviews r join ebh_coursewares c on c.cwid=r.toid join ebh_roomcourses rc on (c.cwid = rc.cwid) WHERE rc.crid=ru.crid AND c.status=1 AND r.opid=8192 AND r.type='courseware' and r.uid=u.uid {$starReviewSql} {$endReviewSql} ) as review_count,
			(select count(distinct(folderid)) from ebh_playlogs where  crid=ru.crid and uid=u.uid and totalflag=0 {$starPlaylogsSql} {$endPlaylogsSql}) as study_count,
			(SELECT count(e.eid) from ebh_schexams e LEFT JOIN ebh_schexamanswers a on (e.eid = a.eid ) WHERE e.crid=ru.crid and a.uid=u.uid {$starExamSql} {$endExamSql}) as exam_count*/
        $list = Ebh()->db->query($sql)->list_array();
        $studentList = array();
        if($list){
            foreach ($list as $student){
                $ids[] = $student['uid'];
                if(!empty($param['isenterprise'])) {
                    $student['classname'] = trim($student['path'], '/');
                    $student['classname'] = substr(strstr($student['classname'], '/'), 1);
                    $student['classname'] = preg_replace('/\//', '>', $student['classname']);
                    $student['classname'] = urldecode($student['classname']);
                    unset($student['path']);
                }
                $studentList[$student['uid']] = $student;
                $studentList[$student['uid']]['sign_count'] = 0;
                $studentList[$student['uid']]['ask_count'] = 0;
                $studentList[$student['uid']]['review_count'] = 0;
                $studentList[$student['uid']]['study_count'] = 0;
                $studentList[$student['uid']]['exam_count'] = 0;
                $studentList[$student['uid']]['ltime'] = 0;
                $studentList[$student['uid']]['totalscore'] = 0;
            }
        }
        $idStr = implode(',',$ids);
        //读取签到次数
        $sql = "select uid,count(logid) as counts from ebh_signlogs where crid={$crid} and uid in ({$idStr}) {$startSql} {$endSql} group by uid";
        $signCountList = Ebh()->db->query($sql)->list_array();
        //读取学习次数
        $sql = "select uid,count(distinct(folderid)) as counts from ebh_playlogs where  crid={$crid} and uid in ({$idStr})  and totalflag=0 {$starPlaylogsSql} {$endPlaylogsSql}  group by uid";
        $studyCountList = Ebh()->db->query($sql)->list_array();
        //读取提问次数
        $sql = "select uid,count(qid) as counts from ebh_askquestions where crid={$crid} and uid in ({$idStr}) {$startSql} {$endSql} group by uid";
        $askCountList = Ebh()->db->query($sql)->list_array();
        //读取评论次数
        $sql = "select r.uid,count(*) as counts from ebh_reviews r join ebh_coursewares c on c.cwid=r.toid join ebh_roomcourses rc on (c.cwid = rc.cwid) WHERE rc.crid={$crid} AND c.status=1 AND r.opid=8192 AND r.type='courseware' and r.uid in ({$idStr}) {$starReviewSql} {$endReviewSql} group by r.uid";
        $reviewsCountList = Ebh()->db->query($sql)->list_array();
        //作业次数
        $sql = "select a.uid,count(e.eid) as counts from ebh_schexams e LEFT JOIN ebh_schexamanswers a on (e.eid = a.eid ) WHERE e.crid={$crid} and a.uid in ({$idStr}) {$starExamSql} {$endExamSql} group by a.uid";
        $examCountList = Ebh()->db->query($sql)->list_array();
        //读取总学时
        $sql = "select uid,sum(ltime) as ltime from ebh_playlogs where  crid={$crid} and uid in ({$idStr})  and totalflag=0 {$starPlaylogsSql} {$endPlaylogsSql} group by uid";
        $totalTimeList = Ebh()->db->query($sql)->list_array();
        /**
         * 整理签到记录
         */
        foreach ($signCountList as $signCount){
            $studentList[$signCount['uid']]['sign_count'] = $signCount['counts'];
        }
        /**
         * 整理学习记录
         */
        foreach ($studyCountList as $studyCount){
            $studentList[$studyCount['uid']]['study_count'] = $studyCount['counts'];
        }
        /**
         * 整理提问记录
         */
        foreach ($askCountList as $askCount){
            $studentList[$askCount['uid']]['ask_count'] = $askCount['counts'];
        }
        /**
         * 整理作业记录
         */
        foreach ($examCountList as $examCount){
            $studentList[$examCount['uid']]['exam_count'] = $examCount['counts'];
        }
        /**
         * 整理评论记录
         */
        foreach ($reviewsCountList as $reviewsCount){
            $studentList[$reviewsCount['uid']]['review_count'] = $reviewsCount['counts'];
        }
        /**
         * 整理学时记录
         */
        foreach ($totalTimeList as $tltime){
            $tltime['ltime'] = round($tltime['ltime']/3600,2);
            $studentList[$tltime['uid']]['ltime'] = $tltime['ltime'];
        }
        return array_values($studentList);


    }

    /**
     * 读取网校学生课程数量
     * @param $crid
     * @param $uid
     * @param array $param
     * @return mixed
     */
    public function roomStudentFolderReportCount($crid,$uid,$param = array()){
        $sql = "select count(1) as count from (select count(pl.uid) as count
                from ebh_playlogs pl
                left join ebh_users u on (u.uid=pl.uid)
                left join ebh_classstudents st on (st.uid=u.uid)
                left join ebh_classes cl on st.classid = cl.classid
                left join ebh_folders f on pl.folderid=f.folderid";

        $wherearr[] = 'pl.uid='.$uid;
        $wherearr[] = 'pl.crid='.$crid;
        $wherearr[] = 'cl.crid='.$crid;
        $wherearr[] = 'pl.totalflag = 0';

        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        $sql .= ' group by pl.folderid ) aa';
        $count = Ebh()->db->query($sql)->row_array();

        return $count['count'];
    }

    /**
     * 获取指定学生学习的课程
     * @param $crid
     * @param $uid
     * @param array $param
     * @return mixed
     */
    public function roomStudentFolderReport($crid,$uid,$param = array()){
        $sql = "select u.realname,u.username,u.uid,u.face,u.groupid,u.sex,cl.classname,f.folderid,f.foldername,sum(pl.ltime) as studytime,count(pl.uid) as study_count
                from ebh_playlogs pl
                left join ebh_users u on (u.uid=pl.uid)
                left join ebh_classstudents st on (st.uid=u.uid)
                left join ebh_classes cl on st.classid = cl.classid
                left join ebh_folders f on pl.folderid=f.folderid";

        $wherearr[] = 'pl.uid='.$uid;
        $wherearr[] = 'pl.crid='.$crid;
        $wherearr[] = 'cl.crid='.$crid;
        $wherearr[] = 'pl.totalflag = 0';


        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        $sql .= ' group by pl.folderid';
        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }

        if(isset($param['limit'])){
            $sql .= ' LIMIT '.$param['limit'];
        }



        return Ebh()->db->query($sql)->list_array();

    }

    /**
     * 学生课件统计条数
     * @param $crid
     * @param $folderid
     * @param $uid
     * @param array $param
     * @return mixed
     */
    public function roomStudentCourseReportCount($crid,$folderid,$uid,$param = array()){
        $sql = "select count(1) as count from ( select count(1)
                from ebh_playlogs pl
                left join ebh_users u on (u.uid=pl.uid)
                left join ebh_classstudents st on (st.uid=u.uid)
                left join ebh_classes cl on st.classid = cl.classid
                left join ebh_folders f on pl.folderid=f.folderid
                left join ebh_coursewares c on c.cwid=pl.cwid";

        $wherearr[] = 'pl.uid='.$uid;
        $wherearr[] = 'pl.folderid='.$folderid;
        $wherearr[] = 'pl.crid='.$crid;
        $wherearr[] = 'cl.crid='.$crid;
        $wherearr[] = 'pl.totalflag = 0';

        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        $sql .= ' group by pl.cwid ) aa';

        $count = Ebh()->db->query($sql)->row_array();

        return $count['count'];
    }

    /**
     * 获取学生课件统计
     * @param $crid
     * @param $folderid
     * @param $uid
     * @return mixed
     */
    public function roomStudentCourseReport($crid,$folderid,$uid,$param = array()){
        $sql = "select pl.logid,cl.classname,u.realname,u.username,u.uid,u.face,u.groupid,u.sex,f.folderid,f.foldername,c.cwid,c.title,pl.ctime,sum(pl.ltime) as ltime,count(pl.logid) as study_count,pl.startdate,pl.lastdate
                from ebh_playlogs pl
                left join ebh_users u on (u.uid=pl.uid)
                left join ebh_classstudents st on (st.uid=u.uid)
                left join ebh_classes cl on st.classid = cl.classid
                left join ebh_folders f on pl.folderid=f.folderid
                left join ebh_coursewares c on c.cwid=pl.cwid";

        $wherearr[] = 'pl.uid='.$uid;
        $wherearr[] = 'pl.folderid='.$folderid;
        $wherearr[] = 'pl.crid='.$crid;
        $wherearr[] = 'cl.crid='.$crid;
        $wherearr[] = 'pl.totalflag = 0';

        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        $sql .= ' group by pl.cwid';

        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }

        if(isset($param['limit'])){
            $sql .= ' LIMIT '.$param['limit'];
        }



        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 检查帐号是否为网校学生，是返回学生ID
     * @param $crid 网校ID
     * @param $username 学生帐号
     * @return bool
     */
    public function judgeStudent($crid, $username) {
        $username = Ebh()->db->escape($username);
        $sql = 'SELECT `a`.`uid` FROM `ebh_users` `a` LEFT JOIN `ebh_roomusers` `b` ON `a`.`uid`=`b`.`uid` '.
            'WHERE `a`.`username`='.$username .' AND `b`.`crid`='.intval($crid);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['uid'];
        }
        return false;
    }
	
	/*
	按日期排列学生数量
	*/
	public function getStudentCountByDay($param){
		if(empty($param['crid'])){
			exit;
		}
		if(empty($param['byhour'])){
			$sql = "select count(*) count,cdateline from ebh_roomusers";
			$groupstr = " group by from_unixtime(cdateline,'%Y-%m-%d')";
		} else {
			$sql = "select count(*) count,cdateline from ebh_roomusers";
			$groupstr = " group by from_unixtime(cdateline,'%Y-%m-%d %H')";
		}
		$wherearr[] = 'crid='.$param['crid'];
		$wherearr[] = 'cdateline is not null';
		if(!empty($param['starttime'])){
			$wherearr[] = 'cdateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'cdateline<='.$param['endtime'];
		}
		if(!empty($wherearr)){
			$sql.= ' where '.implode(' AND ',$wherearr);
		}
		$sql.= $groupstr;
		return Ebh()->db->query($sql)->list_array();
	}
	
	/*
	学校学生uid列表
	*/
	public function getUidList($param){
		if(empty($param['crid'])){
			return FALSE;
		}
		$sql = 'select uid from ebh_roomusers';
		$wherearr[] = 'crid='.$param['crid'];
		if(!empty($param['starttime'])){
			$wherearr[] = 'cdateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'cdateline<='.$param['endtime'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		return Ebh()->db->query($sql)->list_array();
	}
    /**
     * 启用/禁用用户帐号
     * @param $status 状态
     * @param $uid 用户ID
     * @param $crid 所在网校ID
     */
    public function activate($status, $uid, $crid) {
        $status = intval($status);
        if (!in_array($status, array(0, 1))) {
            return false;
        }
        $uid = intval($uid);
        $crid = intval($crid);
        return Ebh()->db->update('ebh_roomusers', array('cstatus' => $status), '`uid`='.$uid.' AND `crid`='.$crid);
    }


    /**
     * 判断是否为校友
     * @param $crid
     * @param $uid
     * @return bool
     */
    public function isAlumni($crid, $uid) {
        $crid = (int) $crid;
        $uid = (int) $uid;
        if ($crid < 1 || $uid < 1) {
            return false;
        }
        $sql = 'SELECT `enddate` FROM `ebh_roomusers` WHERE `uid`='.$uid.' AND `crid`='.$crid;
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return false;
        }
        if (empty($ret['enddate'])) {
            return true;
        }
        if ($ret['enddate'] > SYSTIME - 86400) {
            //有效期延迟一天
            return true;
        }
        return false;
    }

    /**
     * 根据教室和学员编号获取学员在教室内的信息详情
     * @param type $crid
     * @param type $uid
     * @return type
     */
    public function getroomuserdetail($crid,$uid) {
        $sql = "select u.username,ru.cnname,ru.sex,ru.cstatus,ru.rbalance,ru.begindate,ru.enddate from ebh_roomusers ru join ebh_users u on ru.uid=u.uid where ru.crid=$crid and ru.uid=$uid";
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 获取网校指定积分排名
     * @param $crid
     * @return mixed
     */
    public function getRoomUserCreditRank($crid){
        $sql = 'select u.uid,u.username,u.realname,u.groupid,u.nickname,u.face,u.sex,u.credit from ebh_roomusers ru  join ebh_users u on u.uid=ru.uid where ru.crid='.$crid.' and u.groupid=6 order by u.credit desc,u.uid limit 50';
        return Ebh()->db->query($sql)->list_array();
        //(@rowno:=@rowno+1)
    }

    /**
     * 获取用户在指定网校的排名
     * @param $crid
     * @param $uid
     */
    public function getRoomUserRankByUid($crid,$uid){
        //获取用户的积分
        $sql = 'select credit from ebh_users where uid='.$uid;
        $mycredit = Ebh()->db->query($sql)->row_array();
        $mycredit = $mycredit ? $mycredit['credit'] : 0;
        //通过用户积分  获取用户在网校中的排名
        $sql = 'select count(u.uid) as count from ebh_roomusers ru  join ebh_users u on u.uid=ru.uid
where ru.crid='.$crid.' and u.groupid=6 and (u.credit > '.$mycredit.' or (u.credit = '.$mycredit.' and u.uid < '.$uid.' ))';
        $result = Ebh()->db->query($sql)->row_array();
        return $result['count']+1;
    }


    /**
     * 根据教室编号获取学员列表，一般适合于教师网校的学员列表
     * @param type $param
     * @return boolean
     */
    public function getRoomUserCount($param) {
        $count = 0;
        if (empty($param['crid']))
            return $count;
        $sql = 'select count(*) count from ebh_roomusers ru ' .
            'join ebh_users u on (ru.uid = u.uid) ';
        $wherearr = array();
        $wherearr[] = 'ru.crid=' . $param['crid'];
        if (isset($param['status']))
            $wherearr[] = 'ru.cstatus=' . $param['status'];
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
     *获取网校的用户id列表
     *@param int $crid 网校id
     *@param int $page 页号
     *@param int pagesize 每页记录数
     */
    public function getUserIdList($crid,$page=1,$pagesize=100) {
        $sql = "select uid from ebh_roomusers where crid=$crid";
        $start = ($page - 1) * $pagesize;
        $sql .= ' limit ' . $start . ',' . $pagesize;
        return Ebh()->db->query($sql)->list_array();
    }

	/**
     * 学生人数、登录次数按性别统计
     * @param int $crid 网校ID
     */
    public function studentCounts($crid) {
        $sql = 'SELECT SUM(`b`.`logincount`) AS `logincount`,SUM(`b`.`uid`) AS `c`,`b`.`sex` FROM `ebh_roomusers` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` WHERE `crid`='.$crid.' GROUP BY `b`.`sex`';
        return Ebh()->db->query($sql)->list_array('sex');
    }

    /**
     * 学生积分排行榜
     * @param int $crid 网校ID
     * @param mixed $limit 限量条件
     * @param int $orderType 0-降序，1-升序
     * @return mixed
     */
    public function getCreditRankList($crid, $limit = null, $orderType = 0) {
        $offset = 0;
        $top = 0;
        $order = $orderType == 0 ? 'DESC' : 'ASC';
        if ($limit !== null) {
            if (is_array($limit)) {
                $page = isset($limit['page']) ? intval($limit['page']) : 1;
                $page = max(1, $page);
                $top = isset($limit['pagesize']) ? intval($limit['pagesize']) : 1;
                $top = max(1, $top);
                $offset = ($page - 1) * $top;
            } else {
                $top = max(1, intval($limit));
            }
        }
        $sql = 'SELECT `b`.`username`,`b`.`realname`,`b`.`credit`,`b`.`face`,`b`.`sex`,`b`.`groupid` 
                FROM `ebh_roomusers` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` 
                WHERE `a`.`crid`='.$crid.' ORDER BY `b`.`credit` '.$order;
        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 学生最近登录时间
     * @param int $crid 网校ID
     * @return mixed
     */
    public function loginTimes($crid) {
        $sql = 'SELECT `b`.`lastlogintime` FROM `ebh_roomusers` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` WHERE `a`.`crid`='.$crid.' AND `b`.`lastlogintime`>0';
        return Ebh()->db->query($sql)->list_field();
    }

    /**
     *获取网校的用户id列表
     *@param int $crid 网校id
     *@param int $page 页号
     *@param int pagesize 每页记录数
     *@param str filterUids 过滤的uid
     *@param int getall 0为不全部数据，1为全部数据
     */
    public function getUserIdListFilter($crid,$page=1,$pagesize=100,$filterUids='',$getall=0) {
        if (!$filterUids) {
            $sql = 'select r.uid,r.cnname from ebh_roomusers r join ebh_users u using(uid) where r.crid='.$crid;
        } else {
            $sql = 'select r.uid,r.cnname from ebh_roomusers r join ebh_users u using(uid) where r.crid='.$crid.' and r.uid not in('.$filterUids.')';
        } 
        
        if (!$getall) {
            $start = ($page - 1) * $pagesize;
            $sql .= ' order by r.uid desc limit ' . $start . ',' . $pagesize;
        } else {
            //导出需要全部的数据
            $sql .= ' order by r.uid desc';
        }
        
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     *获取网校的用户id列表
     *@param int $crid 网校id
     *@param int $page 页号
     *@param int pagesize 每页记录数
     */
    public function getUserIdListFilterCount($crid,$filterUids='') {
        if (!$filterUids) {
            $sql = 'select count(1) as c from ebh_roomusers r join ebh_users u using(uid) where r.crid='.$crid;
        } else {
            $sql = 'select count(1) as c from ebh_roomusers r join ebh_users u using(uid) where r.crid='.$crid.' and uid not in('.$filterUids.')';
        } 
        $sql .= ' limit 1';
        $res = Ebh()->db->query($sql)->row_array();
        return $res['c'];
    }
	
	/*
	账号密码验证用户是否在某网校
	@return $uid
	*/
	public function verifyRoomUserByPassword($param){
		if(empty($param['username']) || empty($param['password']) || empty($param['crid'])){
			return FALSE;
		}
		$username = Ebh()->db->escape_str($param['username']);
		$password = Ebh()->db->escape_str($param['password']);
		$sql = 'select u.uid from ebh_roomusers ru 
				join ebh_users u on u.uid=ru.uid';
		$wherearr[] = 'username=\''.$username.'\'';
		$wherearr[] = 'password=\''.$password.'\'';
		$wherearr[] = 'crid='.$param['crid'];
		$sql.= ' where '.implode(' AND ',$wherearr);
		$user = Ebh()->db->query($sql)->row_array();
		return $user['uid'];
	}
}