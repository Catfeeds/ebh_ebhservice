<?php

/**
 * 提问
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/23
 * Time: 15:12
 */
class AskQuestionModel {


    /**
     * 添加问题
     * @param type $param
     * @return int
     */
    public function insert($param) {
        if (!empty($param ['crid'])) {
            $setarr['crid'] = $param['crid'];
        }
        if (!empty($param ['folderid'])) {
            $setarr['folderid'] = $param['folderid'];
        }
        if (!empty($param ['catid'])) {
            $setarr['catid'] = $param['catid'];
        }
        if (!empty($param ['grade'])) {
            $setarr['grade'] = $param['grade'];
        }
        if (!empty($param ['uid'])) {
            $setarr['uid'] = $param['uid'];
        }
        if (!empty($param ['title'])) {
            $setarr['title'] = $param['title'];
        }
        if (!empty($param ['message'])) {
            $setarr['message'] = $param['message'];
        }
        if (!empty($param ['catpath'])) {
            $setarr['catpath'] = $param['catpath'];
        }
        if (!empty($param ['audioname'])) {
            $setarr['audioname'] = $param['audioname'];
        }
        if (!empty($param ['audiosrc'])) {
            $setarr['audiosrc'] = $param['audiosrc'];
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
        if (!empty($param ['catpath'])) {
            $setarr['catpath'] = $param['catpath'];
        }
        $setarr['dateline'] = SYSTIME;
        if (!empty($param ['fromip'])) {
            $setarr['fromip'] = $param['fromip'];
        }
        if (isset($param ['tid'])) {
            $setarr['tid'] = $param['tid'];
        }
        if(!empty($param['cwid']))
            $setarr['cwid'] = $param['cwid'];
        if(!empty($param['cwname']))
            $setarr['cwname'] = $param['cwname'];
        if(!empty($param['reward']))
            $setarr['reward'] = $param['reward'];
        if(!empty($param['audiotime']))
            $setarr['audiotime'] = $param['audiotime'];
        if(!empty($param['coverimg'])){
			$setarr['coverimg'] = $param['coverimg'];
		}else{
			$setarr['coverimg'] = '';
		}
            
        $qid = Ebh()->db->insert('ebh_askquestions', $setarr);
        return $qid;
    }

    /**
     * 更新问题
     * @param type $param
     * @return boolean
     */
    public function update($param) {
        if (empty($param['qid']) && empty($param['uid']))
            return FALSE;
        $wherearr = array('qid' => $param['qid'], 'uid' => $param['uid']);
        if (!empty($param ['folderid'])) {
            $setarr['folderid'] = $param['folderid'];
        }
        if (!empty($param ['catid'])) {
            $setarr['catid'] = $param['catid'];
        }
        if (!empty($param ['grade'])) {
            $setarr['grade'] = $param['grade'];
        }
        if (!empty($param ['title'])) {
            $setarr['title'] = $param['title'];
        }
        if (!empty($param ['message'])) {
            $setarr['message'] = $param['message'];
        }
        if (!empty($param ['catpath'])) {
            $setarr['catpath'] = $param['catpath'];
        }
        if (isset($param ['audioname'])) {
            $setarr['audioname'] = $param['audioname'];
        }
        if (isset($param ['audiosrc'])) {
            $setarr['audiosrc'] = $param['audiosrc'];
        }
        if (isset($param ['imagename'])) {
            $setarr['imagename'] = $param['imagename'];
        }
        if (isset($param ['imagesrc'])) {
            $setarr['imagesrc'] = $param['imagesrc'];
        }
        if (!empty($param ['attname'])) {
            $setarr['attname'] = $param['attname'];
        }
        if (!empty($param ['attsrc'])) {
            $setarr['attsrc'] = $param['attsrc'];
        }
        if (!empty($param ['catpath'])) {
            $setarr['catpath'] = $param['catpath'];
        }
        if (!empty($param ['fromip'])) {
            $setarr['fromip'] = $param['fromip'];
        }
        if (isset($param ['tid'])) {
            $setarr['tid'] = $param['tid'];
        }
        if (!empty($param['lastansweruid'])){
            $setarr['lastansweruid'] = $param['lastansweruid'];
        }
        if (isset($param['cwid'])){
            $setarr['cwid'] = $param['cwid'];
        }
        if (isset($param['cwname'])){
            $setarr['cwname'] = $param['cwname'];
        }
        if (!empty($param['isrewarded'])){
            $setarr['isrewarded'] = $param['isrewarded'];
        }
        if (isset($param['reward'])){
            $setarr['reward'] = $param['reward'];
        }
        if(isset($param['audiotime'])){
            $setarr['audiotime'] = $param['audiotime'];
        }
        if(isset($param['coverimg'])){
            $setarr['coverimg'] = $param['coverimg'];
        }
        $afrows = Ebh()->db->update('ebh_askquestions', $setarr, $wherearr);
        return $afrows;
    }
    /**
     * 问题列表
     * @param $crid 网校ID
     * @param $filters 搜索条件参数
     * @param int $limits 分页参数
     * @param bool $setKey 是否设置键
     * @return mixed
     */
    public function getList($crid, $filters, $limits = 20, $setKey = false) {
        $fieldArr = array(
            '`a`.`qid`',
            '`a`.`folderid`',
            '`a`.`uid`',
            '`a`.`title`',
            '`a`.`message`',
            '`a`.`audioname`',
            '`a`.`audiosrc`',
            '`a`.`imagename`',
            '`a`.`imagesrc`',
            '`a`.`answercount`',
            '`a`.`status`',
            '`a`.`dateline`',
            '`a`.`viewnum`',
            '`a`.`shield`',
            '`a`.`cwid`',
            '`a`.`cwname`',
            '`a`.`audiotime`'
        );
        $whereArr = array(
            '`a`.`crid`='.intval($crid)
        );
        if (isset($filters['folderid'])) {
            $whereArr[] = '`a`.`folderid`='.intval($filters['folderid']);
        }
        if (isset($filters['cwid'])) {
            $whereArr[] = '`a`.`cwid`='.intval($filters['cwid']);
        }
        if (isset($filters['shield'])) {
            $shield = intval($filters['shield']);
            $whereArr[] = '`a`.`shield`='.($shield > 0 ? 1 : 0);
        }
        if (isset($filters['early'])) {
            $whereArr[] = '`a`.`dateline`>='.intval($filters['early']);
        }
        if (isset($filters['latest'])) {
            $whereArr[] = '`a`.`dateline`<='.intval($filters['latest']);
        }
        if (!empty($filters['k'])) {
            $whereArr[] = '`a`.`title` LIKE '.Ebh()->db->escape('%'.$filters['k'].'%');
        }
        $offset = 0;
        $pagesize = 20;
        if (!empty($limits)) {
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
        if (!empty($filters['classids'])) {
            if (isset($filters['roomtype']) && $filters['roomtype'] == 'com' && count($filters['classids']) == 1) {
                $classid = reset($filters['classids']);
                $class = Ebh()->db->query('SELECT `lft`,`rgt` FROM `ebh_classes` WHERE `classid`='.$classid)->row_array();
                if (empty($class)) {
                    return array();
                }
                $whereArr[] = '`c`.`lft`>='.$class['lft'];
                $whereArr[] = '`c`.`rgt`<='.$class['rgt'];
            } else {
                $whereArr[] = '`b`.`classid` IN('.implode(',', $filters['classids']).')';
            }

            $sql = 'SELECT '.implode(',', $fieldArr).' FROM `ebh_askquestions` `a` JOIN `ebh_classstudents` `b` ON `b`.`uid`=`a`.`uid` JOIN `ebh_classes` `c` ON `c`.`classid`=`b`.`classid` AND `c`.`crid`=`a`.`crid` WHERE '.
                implode(' AND ', $whereArr).' ORDER BY `a`.`qid` DESC LIMIT '.$offset.','.$pagesize ;
        } else {
            $sql = 'SELECT '.implode(',', $fieldArr).' FROM `ebh_askquestions` `a` WHERE '.
                implode(' AND ', $whereArr).' ORDER BY `a`.`qid` DESC LIMIT '.$offset.','.$pagesize ;
        }
        if (!$setKey) {
            return Ebh()->db->query($sql)->list_array();
        }
        return Ebh()->db->query($sql)->list_array('qid');
    }

    /**
     * 统计问题
     * @param $crid 网校ID
     * @param $filters 搜索条件参数
     * @return int
     */
    public function getCount($crid, $filters) {
        $whereArr = array(
            '`crid`='.intval($crid)
        );
        if (isset($filters['folderid'])) {
            $whereArr[] = '`folderid`='.intval($filters['folderid']);
        }
        if (isset($filters['cwid'])) {
            $whereArr[] = '`cwid`='.intval($filters['cwid']);
        }
        if (isset($filters['shield'])) {
            $shield = intval($filters['shield']);
            $whereArr[] = '`shield`='.($shield > 0 ? 1 : 0);
        }
        if (isset($filters['early'])) {
            $whereArr[] = '`dateline`>='.intval($filters['early']);
        }
        if (isset($filters['latest'])) {
            $whereArr[] = '`dateline`<='.intval($filters['latest']);
        }
        if (!empty($filters['k'])) {
            $whereArr[] = '`title` LIKE '.Ebh()->db->escape('%'.$filters['k'].'%');
        }
        if (isset($filters['hasbest'])) {
            $whereArr[] = '`hasbest`='.intval($filters['hasbest']);
        }

        if(isset($filters['waitanswer']) && $filters['waitanswer'] && isset($filters['uid'])){
            //等待回答
            $whereArr[] = 'qid not  in'.'(select aq.qid from ebh_askquestions aq left join ebh_askanswers aa on (aq.qid = aa.qid) where aq.crid ='.$crid.' and aa.uid = '.$filters['uid'].' )';
        }else if (isset($filters['uid'])) {
            //自己的
            $whereArr[] = '`uid`='.intval($filters['uid']);
        }
        if (isset($filters['classids'])) {
            $whereArr = array_map(function($where) {
                return '`a`.'.$where;
            }, $whereArr);
            if (isset($filters['roomtype']) && $filters['roomtype'] == 'com' && count($filters['classids']) == 1) {
                $classid = reset($filters['classids']);
                $class = Ebh()->db->query('SELECT `lft`,`rgt` FROM `ebh_classes` WHERE `classid`='.$classid)->row_array();
                if (empty($class)) {
                    return 0;
                }
                $whereArr[] = '`c`.`lft`>='.$class['lft'];
                $whereArr[] = '`c`.`rgt`<='.$class['rgt'];
            } else {
                $whereArr[] = '`b`.`classid` IN('.implode(',', $filters['classids']).')';
            }
            $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_askquestions` `a` JOIN `ebh_classstudents` `b` ON `b`.`uid`=`a`.`uid` JOIN `ebh_classes` `c` ON `c`.`classid`=`b`.`classid` AND `c`.`crid`=`a`.`crid` WHERE '.implode(' AND ', $whereArr);
        } else {
            $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_askquestions` WHERE '.implode(' AND ', $whereArr);
        }
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['c'];
        }
        return 0;
    }

    /**
     * 设置问题屏蔽状态，屏蔽后扣除积分
     * @param $qid 问题ID
     * @param $crid 网校ID
     * @param $shield 屏蔽状态
     * @return mixed
     */
    public function setShield($qid, $crid, $shield) {
        $sql = 'SELECT `a`.`credit` AS `ncredit`,`a`.`ruleid`,`a`.`toid`,`a`.`crid`,`b`.`action`,`b`.`credit` FROM `ebh_creditlogs` `a` 
                JOIN `ebh_creditrules` `b` ON `b`.`ruleid`=`a`.`ruleid` 
                WHERE `a`.`uid`='.$qid.' AND `a`.`type`=3 AND `a`.`ruleid`=15';
        $creditLogs = Ebh()->db->query($sql)->list_array();
        Ebh()->db->begin_trans();
        $affectedRows = Ebh()->db->update('ebh_askquestions', array('shield' => intval($shield)), '`qid`='.intval($qid).' AND `crid`='.intval($crid));
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        if (!empty($affectedRows)) {
            foreach ($creditLogs as $log) {
                $credit = $log['action'].$log['ncredit'];
                if (empty($shield)) {
                    $credit = 0 - $credit;
                }
                Ebh()->db->query('UPDATE `ebh_users` SET `credit`=`credit`-'.$credit.' WHERE `uid`='.$log['toid'], false);
                if (Ebh()->db->trans_status() === false) {
                    Ebh()->db->rollback_trans();
                    return false;
                }
            }
        }
        Ebh()->db->commit_trans();
        return $affectedRows;
    }


    /**
     * 读取答疑列表
     * @param $crid
     * @param $filters
     * @return mixed
     */
    public function getAskList($crid,$param){
        $sql = 'select a.qid,a.crid,a.folderid,a.uid,a.title,a.message,a.audioname,a.audiosrc,a.audiotime,a.imagename,a.imagesrc,a.answercount,a.thankcount,a.hasbest,a.dateline,a.viewnum,a.cwid,a.cwname,a.reward,u.username,u.realname,u.nickname,u.face,u.groupid,u.sex from  ebh_askquestions a
                join ebh_users u on a.uid=u.uid';
        $whereArr = array(
            '`a`.`crid`='.intval($crid)
        );
        if (isset($param['folderid'])) {
            $whereArr[] = '`a`.`folderid`='.intval($param['folderid']);
        }
        if (isset($param['cwid'])) {
            $whereArr[] = '`a`.`cwid`='.intval($param['cwid']);
        }
        if (isset($param['shield'])) {
            $shield = intval($param['shield']);
            $whereArr[] = '`a`.`shield`='.($shield > 0 ? 1 : 0);
        }
        if (isset($param['early'])) {
            $whereArr[] = '`a`.`dateline`>='.intval($param['early']);
        }
        if (isset($param['latest'])) {
            $whereArr[] = '`a`.`dateline`<='.intval($param['latest']);
        }
        if (!empty($param['k'])) {
            $whereArr[] = '`a`.`title` LIKE '.Ebh()->db->escape('%'.$param['k'].'%');
        }

        if (isset($param['hasbest'])) {
            $whereArr[] = '`a`.`hasbest`='.intval($param['hasbest']);
        }

        if(isset($param['waitanswer']) && $param['waitanswer'] && isset($param['uid'])){
            //等待回答
            $whereArr[] = 'a.qid not  in'.'(select aq.qid from ebh_askquestions aq left join ebh_askanswers aa on (aq.qid = aa.qid) where aq.crid ='.$crid.' and aa.uid = '.$param['uid'].' )';
        }else if (isset($param['uid'])) {
            //自己的
            $whereArr[] = '`a`.`uid`='.intval($param['uid']);
        }

        $sql .= ' where '.implode(' and ',$whereArr);

        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }else{
            $sql.= ' order by a.qid desc';
        }

        if(isset($param['limit'])){
            $sql .= ' limit '.$param['limit'];
        }
        //echo $sql;exit;
        return Ebh()->db->query($sql)->list_array();

    }


    /**
     * 读取问题详情
     * @param $param
     * @return mixed
     */
    public function detail($param){
        $sql = 'select q.qid,q.crid,q.folderid,q.uid,q.title,q.message,q.audioname,q.audiosrc,q.imagename,q.imagesrc,q.answercount,q.thankcount,q.hasbest,q.status,q.dateline,q.viewnum,q.fromip,q.shield,q.tid,q.answered,q.lastansweruid,q.cwid,q.cwname,q.reward,q.isrewarded,q.audiotime,q.coverimg,f.foldername,u.username,u.realname,u.face,u.groupid,u.sex from ebh_askquestions q
        left join ebh_users u on u.uid=q.uid 
        left join ebh_folders f on f.folderid=q.folderid';
        if (isset($param['qid'])) {
            $whereArr[] = 'q.qid='.$param['qid'];
        }
        //当进行屏蔽或取消屏蔽问题的操作时，不需要判断屏蔽状态是否为0
        if(empty($param['changestatus']) || ($param['changestatus'] !=1)){
            $whereArr[] = 'q.shield=0';
        }
        $sql .= ' where '.implode(' and ',$whereArr);
        return Ebh()->db->query($sql)->row_array();
    }

    /*
	设为最佳答案
	@param array $param uid,qid,aid
	*/
    public function setBest($param){
        if(empty($param['uid'])||empty($param['qid'])||empty($param['aid']))
            return false;
        $sql = 'select count(*) count 
			from ebh_askquestions q 
			join ebh_askanswers a on q.qid=a.qid';
        $warr = array();
        $warr[]= 'q.uid='.$param['uid'];
        $warr[]= 'q.qid='.$param['qid'];
        $warr[]= 'q.hasbest=0';
        $warr[]= 'a.aid='.$param['aid'];
        $sql.= ' where '.implode(' AND ',$warr);
        $count = Ebh()->db->query($sql)->row_array();
        if($count['count']>0){
            $qarr['hasbest'] = 1;
            $qarr['status'] = 1;
            $wherearr['qid'] = $param['qid'];
            $afrow = Ebh()->db->update('ebh_askquestions',$qarr,$wherearr);
            $aarr['isbest'] = 1;
            $wherearr2['aid'] = $param['aid'];
            $afrow = Ebh()->db->update('ebh_askanswers',$aarr,$wherearr2);
            return $afrow;
        }else{
            return false;
        }
    }

    /**
     * 访问+1
     * @param $param
     * @return mixed
     *
     */
    public function updateViewnum($param){
        if (isset($param['qid'])) {
            $whereArr['qid'] = $param['qid'];
        }

        $whereArr['shield'] = 0;

        return Ebh()->db->update('ebh_askquestions',array(),$whereArr ,array('viewnum'=>'viewnum + 1'));
    }

    /*
      删除答疑
      @param int $qid
      @return bool
     */

    public function deleteaskquestion($qid) {
        return Ebh()->db->delete('ebh_askquestions','qid='.$qid);
    }
    /****
     * 获取提问排名
     * @return  array
     */
    public function getQuestions($crid) {
        $sql = 'SELECT COUNT(*) as number ,ask.uid,ask.crid,u.realname,u.sex,u.username,u.face FROM ebh_askquestions ask LEFT JOIN ebh_users u ON ask.uid=u.uid ';
        $sql .= ' WHERE ask.crid='.$crid.' GROUP BY ask.uid   '.' ORDER BY number DESC,ask.dateline DESC LIMIT 100';

        return Ebh()->db->query($sql)->list_array();
    }

    /***
     * 获取问答比例
     * @param  int $uid  用户id
     * @param string $username 用户昵称
     * @return array
     */
    public function getRate($uid, $realname,$crid) {
        $beginTime = strtotime('-30 days', strtotime(date('Y-m-d')));
        $endTime   = strtotime('+1 days');
        $sql       = 'SELECT a.isbest,a.thankcount as praise,q.reward,a.uid as auid, FROM_UNIXTIME(a.dateline,\'%Y-%m-%d\') as atime FROM ebh_askanswers a INNER JOIN ebh_askquestions q ON a.qid=q.qid WHERE q.crid='.$crid.' AND a.uid='.$uid.' AND a.dateline>='.$beginTime.' AND a.dateline<'.$endTime;
        $aArr      = Ebh()->db->query($sql)->list_array();
        $sql       = 'SElECT (0) as isbest,(0) as praise,q.reward,q.uid as quid,FROM_UNIXTIME(q.dateline,\'%Y-%m-%d\') as qtime FROM ebh_askquestions q WHERE q.crid='.$crid.' AND q.uid='.$uid.' AND q.dateline>='.$beginTime.' AND q.dateline<'.$endTime;
        $qArr      = Ebh()->db->query($sql)->list_array();
        $list      = array_merge($aArr,$qArr);
        $data      = array(
            'cNumber' => 0
        , 'notNumber' => 0
        , 'solveNumber' => 0
        , 'rewardNumber' => 0
        , 'praise' => 0
        , 'reward' => 0
        , 'username' => $realname);
        //获取打赏金额
        $sql = 'SELECT SUM(`totalfee`) AS praise FROM `ebh_rewards` WHERE `status`=1 AND `refunded`=0 AND `crid`='.$crid.' AND `touid`='.$uid;
        $praise = Ebh()->db->query($sql)->row_array();
        $data['reward'] = $praise['praise']?$praise['praise']:0;
       // $list = Ebh()->db->query($sql)->list_array();
        $key = 'ebh_askquestions'.date('Ymd').$uid.$crid;
        $count = Ebh()->cache->get($key);
        if(empty($count)){
            //无缓存查询数据库
        $sql = 'SELECT sum(if(reward>0,1,0)) c ,sum(if( `status`=0,1,0))as c1,sum(if(status=1,1,0)) as c2 from ebh_askquestions where uid='.$uid.' AND crid='.$crid;
        $r = Ebh()->db->query($sql)->row_array();
            //计时到明天得秒数
        $time = strtotime(' +1 days',strtotime(date('Y-m-d')))-SYSTIME;

        Ebh()->cache->set($key,$r,$time);
        $count = $r;
        }

        //获取未回答的问题总数
        $data['rewardNumber'] = $count['c']?$count['c']:0;
        $data['notNumber']    = $count['c1']?$count['c1']:0;
        $data['solveNumber']  = $count['c2']?$count['c2']:0;

        $date = array();

        for ($d = 0; $d <= 30; $d++) {
            $nowday = date('Y-m-d', strtotime('-' . (30 - $d) . ' days'));
            $key    = '\''.$nowday.'\'';
            foreach ($list as $value) {

                if ((isset($value['atime']) && $nowday == $value['atime'] && $uid==$value['auid']) || (isset($value['qtime']) && $nowday == $value['qtime'] && $uid==$value['quid'])) {


                     $data['praise'] += $value['praise'];

                    if (array_key_exists($key, $date)) {
                        $date[$key][1]++;
                    } else {
                        $date[$key][1] = 1;
                        $date[$key][0] = $nowday;
                    }
                }

            }
            if (!array_key_exists($key, $date)) {
                $date[$key][1] = 0;
                $date[$key][0] = $nowday;

            }
        }
        return   array('rate' => $data, 'list' => $date);
    }


    /**
     * 获取我回答的问题条数
     * @param $crid 网校ID
     * @param $filters 搜索条件参数
     * @return int
     */
    public function getAnswerCount($crid, $filters) {

        $sql = 'select count(1) as c from ebh_askanswers aa left join ebh_askquestions aq on aa.qid = aq.qid';

        $whereArr = array(
            'aq.crid='.intval($crid)
        );

        if (isset($filters['folderid'])) {
            $whereArr[] = 'aq.folderid='.intval($filters['folderid']);
        }
        if (isset($filters['cwid'])) {
            $whereArr[] = 'aq.cwid='.intval($filters['cwid']);
        }
        if (isset($filters['shield'])) {
            $shield = intval($filters['shield']);
            $whereArr[] = 'aq.shield='.($shield > 0 ? 1 : 0);
        }
        if (isset($filters['early'])) {
            $whereArr[] = 'aq.dateline>='.intval($filters['early']);
        }
        if (isset($filters['latest'])) {
            $whereArr[] = 'aq.dateline<='.intval($filters['latest']);
        }
        if (!empty($filters['k'])) {
            $whereArr[] = 'aq.title LIKE '.Ebh()->db->escape('%'.$filters['k'].'%');
        }
        if (isset($filters['hasbest'])) {
            $whereArr[] = 'aq.hasbest='.intval($filters['hasbest']);
        }

        if (isset($filters['uid'])) {
            //自己的
            $whereArr[] = 'aa.uid='.intval($filters['uid']);
        }
        $sql .= ' where '.implode(' and ',$whereArr);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['c'];
        }
        return 0;
    }

    /**
     * 获取我回答的问题
     * @param $crid
     * @param $filters
     */
    public function getListByMyAnswer($crid, $filters){

        $sql = 'select DISTINCT aq.qid,aq.crid,aq.folderid,aq.uid,aq.title,aq.message,aq.audioname,aq.audiosrc,aq.audiotime,aq.imagename,aq.imagesrc,aq.answercount,aq.thankcount,aq.hasbest,aq.dateline,aq.viewnum,aq.cwid,aq.cwname,aq.reward,u.username,u.realname,u.nickname,u.face,u.groupid,u.sex from ebh_askanswers aa left join ebh_askquestions aq on aa.qid = aq.qid left join ebh_users u on u.uid=aq.uid';

        $whereArr = array(
            'aq.crid='.intval($crid)
        );

        if (isset($filters['folderid'])) {
            $whereArr[] = 'aq.folderid='.intval($filters['folderid']);
        }
        if (isset($filters['cwid'])) {
            $whereArr[] = 'aq.cwid='.intval($filters['cwid']);
        }
        if (isset($filters['shield'])) {
            $shield = intval($filters['shield']);
            $whereArr[] = 'aq.shield='.($shield > 0 ? 1 : 0);
        }
        if (isset($filters['early'])) {
            $whereArr[] = 'aq.dateline>='.intval($filters['early']);
        }
        if (isset($filters['latest'])) {
            $whereArr[] = 'aq.dateline<='.intval($filters['latest']);
        }
        if (!empty($filters['k'])) {
            $whereArr[] = 'aq.title LIKE '.Ebh()->db->escape('%'.$filters['k'].'%');
        }
        if (isset($filters['hasbest'])) {
            $whereArr[] = 'aq.hasbest='.intval($filters['hasbest']);
        }

        if (isset($filters['uid'])) {
            //自己的
            $whereArr[] = 'aa.uid='.intval($filters['uid']);
        }
        $sql .= ' where '.implode(' and ',$whereArr);
        if(!empty($filters['order'])){
            $sql.= ' order by '.$filters['order'];
        }else{
            $sql.= ' order by aa.dateline desc';
        }

        if(isset($filters['limit'])){
            $sql .= ' limit '.$filters['limit'];
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 获取我关注问题数
     * @param $crid
     * @param $filters
     * @return int
     */
    public function favoriteCount($crid,$filters){
        $sql = 'select count(1) as c from ebh_askfavorites af left join ebh_askquestions aq on af.qid = aq.qid';

        $whereArr = array(
            'aq.crid='.intval($crid)
        );

        if (isset($filters['folderid'])) {
            $whereArr[] = 'aq.folderid='.intval($filters['folderid']);
        }
        if (isset($filters['cwid'])) {
            $whereArr[] = 'aq.cwid='.intval($filters['cwid']);
        }
        if (isset($filters['shield'])) {
            $shield = intval($filters['shield']);
            $whereArr[] = 'aq.shield='.($shield > 0 ? 1 : 0);
        }
        if (isset($filters['early'])) {
            $whereArr[] = 'aq.dateline>='.intval($filters['early']);
        }
        if (isset($filters['latest'])) {
            $whereArr[] = 'aq.dateline<='.intval($filters['latest']);
        }
        if (!empty($filters['k'])) {
            $whereArr[] = 'aq.title LIKE '.Ebh()->db->escape('%'.$filters['k'].'%');
        }
        if (isset($filters['hasbest'])) {
            $whereArr[] = 'aq.hasbest='.intval($filters['hasbest']);
        }

        if (isset($filters['uid'])) {
            //自己的
            $whereArr[] = 'af.uid='.intval($filters['uid']);
        }
        $sql .= ' where '.implode(' and ',$whereArr);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['c'];
        }
        return 0;
    }

    /**
     * 我关注的问题列表
     * @param $crid
     * @param $filters
     * @return mixed
     */
    public function getFavoriteList($crid, $filters){

        $sql = 'select DISTINCT aq.qid,aq.crid,aq.folderid,aq.uid,aq.title,aq.message,aq.audioname,aq.audiosrc,aq.audiotime,aq.imagename,aq.imagesrc,aq.answercount,aq.thankcount,aq.hasbest,aq.dateline,aq.viewnum,aq.cwid,aq.cwname,aq.reward,u.username,u.realname,u.nickname,u.face,u.groupid,u.sex from ebh_askfavorites af left join ebh_askquestions aq on af.qid = aq.qid left join ebh_users u on u.uid=aq.uid';

        $whereArr = array(
            'aq.crid='.intval($crid)
        );

        if (isset($filters['folderid'])) {
            $whereArr[] = 'aq.folderid='.intval($filters['folderid']);
        }
        if (isset($filters['cwid'])) {
            $whereArr[] = 'aq.cwid='.intval($filters['cwid']);
        }
        if (isset($filters['shield'])) {
            $shield = intval($filters['shield']);
            $whereArr[] = 'aq.shield='.($shield > 0 ? 1 : 0);
        }
        if (isset($filters['early'])) {
            $whereArr[] = 'aq.dateline>='.intval($filters['early']);
        }
        if (isset($filters['latest'])) {
            $whereArr[] = 'aq.dateline<='.intval($filters['latest']);
        }
        if (!empty($filters['k'])) {
            $whereArr[] = 'aq.title LIKE '.Ebh()->db->escape('%'.$filters['k'].'%');
        }
        if (isset($filters['hasbest'])) {
            $whereArr[] = 'aq.hasbest='.intval($filters['hasbest']);
        }

        if (isset($filters['uid'])) {
            //自己的
            $whereArr[] = 'af.uid='.intval($filters['uid']);
        }
        $sql .= ' where '.implode(' and ',$whereArr);
        if(!empty($filters['order'])){
            $sql.= ' order by '.$filters['order'];
        }else{
            $sql.= ' order by af.dateline desc';
        }

        if(isset($filters['limit'])){
            $sql .= ' limit '.$filters['limit'];
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 添加对回答的感谢
     */
    function addthankanswer($param) {
        $setarr = array('thankcount' => 'thankcount+1');
        $wherearr = array('aid' => $param['aid'], 'qid' => $param['qid']);
        $afrows = Ebh()->db->update('ebh_askanswers', array(), $wherearr, $setarr);
        return $afrows;
    }

    /**
     * 添加我的关注
     * @param array $param
     * @return int 影响行数
     */
    public function addfavorit($param) {
        $setarr = array('qid' => $param['qid'], 'uid' => $param['uid'], 'dateline' => SYSTIME);
        $afrows = Ebh()->db->insert('ebh_askfavorites', $setarr);
        return $afrows;
    }

    /**
     * 删除我的关注
     * @param array $param
     * @return int 影响行数
     */
    public function delfavorit($param) {
        $wherearr = array();
        if (!empty($param['uid']) && !empty($param['aid'])) {
            $wherearr['uid'] = $param['uid'];
            $wherearr['aid'] = $param['aid'];
        } else if (!empty($param['uid']) && !empty($param['qid'])) {
            $wherearr['uid'] = $param['uid'];
            $wherearr['qid'] = $param['qid'];
        }
        $afrows = Ebh()->db->delete('ebh_askfavorites', $wherearr);
        return $afrows;
    }

    /**
     * 查看是否收藏
     * @param $qid
     * @param $uid
     * @return int
     */
    public function isFavorite($qid,$uid){
        $sql = 'select count(aid) as count from ebh_askfavorites where qid ='.$qid.' and uid ='.$uid;
        $res = Ebh()->db->query($sql)->row_array();
        if($res){
            return $res['count'];
        }
        return 0;
    }

    /**
     * 针对老师屏蔽最佳答案的回答，对问题恢复成为解答状态
     */
    public function updateQueStatusByQid($qid){
        if(empty($qid)){
            return false;
        }
        $setarr = array(
            'hasbest'=> 0,
            'status'=> 0
        );
        $wherearr = array('qid'=>intval($qid));
        return Ebh()->db->update('ebh_askquestions', $setarr, $wherearr);
    }
}