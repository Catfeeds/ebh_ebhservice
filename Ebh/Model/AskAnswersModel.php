<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 17:18
 */
class AskAnswersModel{

    /**
     * 获取答案列表
     * @param $param
     * @return mixed
     */
    public function getList($param){
        $sql = 'select a.aid,a.qid,a.uid,a.answertype,a.message,a.audioname,a.audiosrc,a.imagename,a.imagesrc,a.coursename,a.coursesrc,a.isbest,a.thankcount,a.dateline,a.fromip,a.cwid,a.audiotime,u.username,u.realname,u.face,u.groupid,u.sex from ebh_askanswers a
                left join ebh_users u on u.uid=a.uid';
        if (isset($param['qid'])) {
            $whereArr[] = 'a.qid='.$param['qid'];
        }

        $whereArr[] = 'a.shield=0';
        $sql .= ' where '.implode(' and ',$whereArr);
        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }else{
            $sql.= ' order by a.aid desc';
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 添加回答
     */
    function addanswer($param = array()) {
        if (empty($param) || empty($param['qid']) || empty($param['uid']))
            return false;
        //新版为了调动用户积极性,即使已有最佳答案仍然可以回答
        // $sql = 'select status from ebh_askquestions where qid='.$param['qid'];
        // $res = $this->db->query($sql)->row_array();
        // if($res['status'] == 1)
        // 	return false;
        $setarr = array();
        $setarr['qid'] = $param['qid'];
        $setarr['uid'] = $param['uid'];
        if (!empty($param ['message'])) {
            $setarr['message'] = $param['message'];
        }
        if (!empty($param ['audioname'])) {
            $setarr['audioname'] = $param['audioname'];
        }
        if (!empty($param ['audiosrc'])) {
            $setarr['audiosrc'] = $param['audiosrc'];
        }
        if (!empty($param ['audiotime'])) {
            $setarr['audiotime'] = $param['audiotime'];
        }
        if (!empty($param ['imagename'])) {
            $setarr['imagename'] = $param['imagename'];
        }
        if (!empty($param ['imagesrc'])) {
            $setarr['imagesrc'] = $param['imagesrc'];
        }
        if (!empty($param ['attname'])) {
            $setarr['attname'] = $param['attname'];
        }
        if (!empty($param ['attsrc'])) {
            $setarr['attsrc'] = $param['attsrc'];
        }
        if (!empty($param ['fromip'])) {
            $setarr['fromip'] = $param['fromip'];
        }
        if (!empty($param['cwid'])) {
            $setarr['cwid'] = $param['cwid'];
        }
        if (!empty($param['cwsource'])) {
            $setarr['cwsource'] = $param['cwsource'];
        }
        $setarr['dateline'] = SYSTIME;
        $aid = Ebh()->db->insert('ebh_askanswers', $setarr);
        if ($aid) {
            $this->updateanswercount($param['qid'],2);
            return $aid;
        } else {
            return 0;
        }
    }

    /**
     * 更新问题的回答数
     * @param type $qid
     * @param type $count
     * @return type
     */
    public function updateanswercount($qid,$shield,$count = 1) {
        if($shield==1){//屏蔽回答,回答数更新
            $setarr = array('answercount' => 'answercount - ' . $count);
        }else{
            $setarr = array('answercount' => 'answercount + ' . $count);
        }
        $wherearr = array('qid' => $qid);
        $afrows = Ebh()->db->update('ebh_askquestions', array(), $wherearr, $setarr);
        return $afrows;
    }

    /****
     * 获取回答排名
     * @param int $crid 网校ID
     * @param int $grade 年级过滤参数，默认0不过滤
     * @return  array
     */
    public function getAskanswer($crid, $grade = 0){
        $sql
            = 'SELECT COUNT(ask.uid) as number ,ask.uid,q.crid,u.realname,u.sex,u.username,u.face FROM ebh_askanswers ask INNER JOIN ebh_users u ON ask.uid=u.uid';
        $sql .= ' INNER JOIN ebh_askquestions q ON q.qid=ask.qid';
        if ($grade > 0) {
            $sql .= ' JOIN ebh_classstudents cs on cs.uid=u.uid JOIN ebh_classes c on c.classid=cs.classid and c.crid=q.crid and c.grade='.$grade;
        }

        $sql .= ' WHERE  q.crid='.$crid.' GROUP BY ask.uid  ORDER BY number DESC,ask.dateline DESC LIMIT 100';
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 根据aid获取回答的详情
     */
    public function getAnswerInfoByAid($aid){
        if(empty($aid)){
            return false;
        }
        $sql = 'select isbest from `ebh_askanswers` where aid='.intval($aid);
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 获取问题详情
     * @param $aid
     * @return bool
     */
    public function detail($aid){
        if(empty($aid)){
            return false;
        }
        $sql = 'select aid,qid,uid,answertype,message,audioname,audiosrc,imagename,imagesrc,coursename,coursesrc,isbest,thankcount,dateline,attname,attsrc from `ebh_askanswers` where aid='.intval($aid);
        return Ebh()->db->query($sql)->row_array();
    }
    /**
     * 删除答案
     */
    function delanswer($param = array()) {
        if (empty($param) || empty($param['qid']) || empty($param['uid']) || empty($param['aid']))
            return false;
        $wherearr = array('aid' => $param['aid'], 'qid' => $param['qid'], 'uid' => $param['uid']);
        $afrows = Ebh()->db->delete('ebh_askanswers', $wherearr);
        if ($afrows > 0) {
            $this->updateanswercount($param['qid'],2,-1);
        }
        return $afrows;
    }

    /**
     * 获取问题和答案列表
     * @param $param
     * @return mixed
     */
    public function getAnswerList($param,$limits){
        if(empty($param['crid'])){
            return false;
        }
        $sql = 'select `a`.`aid`,`a`.`qid`,`a`.`uid`,`a`.`answertype`,`a`.`message`,`a`.`audioname`,`a`.`audiosrc`,`a`.`imagename`,`a`.`imagesrc`,`a`.`coursename`,`a`.`coursesrc`,`a`.`isbest`,`a`.`thankcount`,`a`.`dateline`,`a`.`fromip`,`a`.`cwid`,`a`.`audiotime`,`a`.`shield`,';
        $sql .= ' `u`.`username`,`u`.`realname`,`u`.`face`,`u`.`groupid`,`u`.`sex`,`q`.`title`,`q`.`cwname`,`q`.`folderid` from ebh_askanswers a';
        $sql .= ' left join ebh_users u on `u`.`uid`=`a`.`uid`';
        $sql .= ' left join ebh_askquestions q on `a`.`qid`=q.`qid`';
        if (isset($param['early'])) {
            $whereArr[] = '`a`.`dateline`>='.intval($param['early']);   //查询的开始时间
        }
        if (isset($param['latest'])) {
            $whereArr[] = '`a`.`dateline`<='.intval($param['latest']);  //查询的结束时间
        }
        if (!empty($param['k'])) {  //查询关键字
            $whereArr[] = '(`a`.`message` LIKE '.Ebh()->db->escape('%'.$param['k'].'%').'or `q`.`title` LIKE '.Ebh()->db->escape('%'.$param['k'].'%').')';
        }
        if (isset($param['shield']) && in_array($param['shield'],array(0,1,2))) {
            $whereArr[] = '`a`.`shield`='.intval($param['shield']);          //是否屏蔽,1屏蔽状态(国土:0审核通过，1审核不通过，2待审核)
        }
        $whereArr[] = '`q`.`crid`='.intval($param['crid']);
        if (isset($param['folderid'])) {
            $whereArr[] = '`q`.`folderid`='.intval($param['folderid']); //课程id
        }

        if (!empty($param['classids'])) {   //班级或部门id集
            if (isset($param['roomtype']) && $param['roomtype'] == 'com' && count($param['classids']) == 1) {//true查询当前部门及子部门
                $classid = reset($param['classids']);
                $class = Ebh()->db->query('SELECT `lft`,`rgt` FROM `ebh_classes` WHERE `classid`='.$classid)->row_array();
                if (empty($class)) {
                    return array();
                }
                $whereArr[] = '`c`.`lft`>='.$class['lft'];
                $whereArr[] = '`c`.`rgt`<='.$class['rgt'];
            } else {
                $whereArr[] = '`b`.`classid` IN('.implode(',', $param['classids']).')';
            }
            $sql .= ' JOIN `ebh_classstudents` `b` ON `b`.`uid`=`a`.`uid` JOIN `ebh_classes` `c` ON `c`.`classid`=`b`.`classid` AND `c`.`crid`=`q`.`crid` ';
        }
        $sql .= ' where '.implode(' and ',$whereArr);
        $offset = 0;
        $pagesize = 30;
        if (!empty($limits)) {  //分页
            if (is_array($limits)) {
                if (isset($limits['pagesize'])) {
                    $pagesize = max(1, intval($limits['pagesize']));
                }
                $page = 1;
                if (isset($limits['page'])) {
                    $page = max(1, intval($limits['page']));
                }
                $offset = ($page - 1) * $pagesize;
            } else {
                $pagesize = max(1, intval($limits));
            }
        }
        $sql.= ' ORDER BY `a`.`aid` DESC LIMIT '.$offset.','.$pagesize ;
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 获取问题和答案列表总数
     * @param $param
     * @return mixed
     */
    public function getAnswerCount($param){
        if(empty($param['crid'])){
            return false;
        }
        $sql = 'select count(1) count from ebh_askanswers a left join ebh_users u on `u`.`uid`=`a`.`uid` left join ebh_askquestions q on `a`.`qid`=q.`qid`';
        if (isset($param['early'])) {
            $whereArr[] = '`a`.`dateline`>='.intval($param['early']);   //查询的开始时间
        }
        if (isset($param['latest'])) {
            $whereArr[] = '`a`.`dateline`<='.intval($param['latest']);  //查询的结束时间
        }
        if (!empty($param['k'])) {  //查询关键字
            $whereArr[] = '(`a`.`message` LIKE '.Ebh()->db->escape('%'.$param['k'].'%').'or `q`.`title` LIKE '.Ebh()->db->escape('%'.$param['k'].'%').')';
        }
        if (isset($param['shield']) && in_array($param['shield'],array(0,1,2))) {
            $whereArr[] = '`a`.`shield`='.intval($param['shield']);          //是否屏蔽,1屏蔽状态(国土:0审核通过，1审核不通过，2待审核)
        }
        $whereArr[] = '`q`.`crid`='.intval($param['crid']);
        if (isset($param['folderid'])) {
            $whereArr[] = '`q`.`folderid`='.intval($param['folderid']); //课程id
        }
        if (!empty($param['classids'])) {   //班级或部门id集
            if (isset($param['roomtype']) && $param['roomtype'] == 'com' && count($param['classids']) == 1) {//true查询当前部门及子部门
                $classid = reset($param['classids']);
                $class = Ebh()->db->query('SELECT `lft`,`rgt` FROM `ebh_classes` WHERE `classid`='.$classid)->row_array();
                if (empty($class)) {
                    return array();
                }
                $whereArr[] = '`c`.`lft`>='.$class['lft'];
                $whereArr[] = '`c`.`rgt`<='.$class['rgt'];
            } else {
                $whereArr[] = '`b`.`classid` IN('.implode(',', $param['classids']).')';
            }
            $sql .= ' JOIN `ebh_classstudents` `b` ON `b`.`uid`=`a`.`uid` JOIN `ebh_classes` `c` ON `c`.`classid`=`b`.`classid` AND `c`.`crid`=`q`.`crid` ';
        }
        $sql .= ' where '.implode(' and ',$whereArr);

        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 设置解答屏蔽状态，屏蔽后扣除积分
     * @param $aid 答案ID
     * @param $crid 网校ID
     * @param $shield 状态：1屏蔽,0取消屏蔽(国土:1审核不通过,0审核通过)
     * @return mixed
     */
    public function setShield($param) {
        $aid = intval($param['aid']);
        $shield = intval($param['shield']);
        $crid = intval($param['crid']);
        if(empty($aid) || !isset($shield) || !in_array($shield,array(0,1))){
            return false;
        }
        //屏蔽或取消屏蔽当前解答
        $affectedRows = Ebh()->db->update('ebh_askanswers', array('shield' => $shield), '`aid`='.$aid);
        //国土不用同步积分
        if(empty($param['iszjdlr']) || ($param['iszjdlr'] != 1)){
            $sql = 'SELECT `a`.`credit` AS `ncredit`,`a`.`ruleid`,`a`.`toid`,`a`.`crid`,`b`.`action`,`b`.`credit` FROM `ebh_creditlogs` `a` 
                JOIN `ebh_creditrules` `b` ON `b`.`ruleid`=`a`.`ruleid` 
                JOIN `ebh_askanswers` `c` ON `c`.`uid`=`a`.`toid`  AND `a`.`dateline`=`c`.`dateline`
                WHERE `a`.`type`=3 AND `a`.`ruleid`=21 AND `a`.`crid`='.$crid.' AND `c`.`aid`='.$aid;
            $sql .= ' ORDER BY `a`.`logid` DESC LIMIT 1';
            //查询该解答是否获得积分
            $log = Ebh()->db->query($sql)->row_array();
            //同步更新积分，$affectedRows大于0(该解答有积分)，action(+或-),ncredit(积分)，toid(获得积分的用户)
            if (!empty($affectedRows) && ($affectedRows>0) && !empty($log['action']) && !empty($log['ncredit']) && !empty($log['toid'])) {
                $credit = $log['action'].$log['ncredit'];
                if ($shield == 0) {
                    $credit = 0 - $credit;
                }
                Ebh()->db->query('UPDATE `ebh_users` SET `credit`=`credit`-'.$credit.' WHERE `uid`='.$log['toid'], false);
            }
        }
        return $affectedRows;
    }

}