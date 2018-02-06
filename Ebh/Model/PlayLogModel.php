<?php

/**
 * 学习记录
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/21
 * Time: 15:01
 */
class PlayLogModel {
    private $db;
    public function __construct() {
        $this->db = Ebh()->db;
    }

    /**
     * 网校学生学习记录统计
     * @param $crid
     * @return int
     */
    public function getStudyCountForRoom($crid) {
        $crid = (int) $crid;
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_playlogs` WHERE `crid`='.$crid.' AND `finished`=1';
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret['c'])) {
            return $ret['c'];
        }
        return 0;
    }
    /**
     * 将日志数据插入到playlog表中
     * @param $param Array 日志数据参数
     */
    public function addLog($param) {
        if (empty($param['cwid']) || empty($param['uid']) || empty($param['ltime']))
            return FALSE;
        $logid = Ebh()->db->insert('ebh_playlogs',$param);
        return $logid;
    }
    /**
     * 根据单次playlog记录更新总的playlog记录(totalflag = 1)
	 * @param $param array 每次更新的学习记录参数
	 * @param $ismeger bool 是否合并学习记录时长，如果TRUE，则总记录的ltime会根据每次的ltime累加，并重新计算finished值，一般国土资源会有这个需求
     */
    public function addTotalLog($param,$ismeger = FALSE) {
        if (empty($param['cwid']) || empty($param['uid']) || empty($param['ltime']))
            return FALSE;

        $cwid = intval($param['cwid']);
        $uid = intval($param['uid']);
		if ($param['ltime'] < 0) 
			$param['ltime'] = 0;
        //先取出总记录，如存在则编辑否则就插入
        $logrow = $this->getTotalLog($param);
        if (!empty($logrow)) {
			if ($logrow['ltime'] < 0) 
				$logrow['ltime'] = 0; 
			if ($ismeger) {	//合并ltime
				$logrow['ltime'] = $param['ltime'] + $logrow['ltime'];
			} else {
				$logrow['ltime'] = $param['ltime'] > $logrow['ltime'] ? $param['ltime'] : $logrow['ltime']; //总记录的ltime取播放中最长的ltime记录
			}
            $logrow['lastdate'] = $param['lastdate'];
            $logrow['curtime'] = $param['curtime'];
            if(!empty($param['ip'])) {  //此ip为最后一次播放的IP地址
                $logrow['ip'] = $param['ip'];
            }
            if ($param['finished'] == 1 || $logrow['ltime'] > $logrow['ctime'] * 0.9)	//如果ltime>ctime*0.9 则表示finished=1
                $logrow['finished'] = 1;
			if ($logrow['ltime'] <= 0 || $logrow['ctime'] <= 0)	//ltime和ctime不能为空
				return FALSE;
            $wherearr = array('logid' => $logrow['logid']);
            return Ebh()->db->update('ebh_playlogs',$logrow,$wherearr);
        } else {
            $logrow = array('uid'=>$param['uid'],'cwid'=>$param['cwid'],'ctime'=>$param['ctime'],'ltime'=>$param['ltime'],
                'startdate'=>$param['startdate'],'lastdate'=>$param['lastdate'],'totalflag'=>1,'finished'=>$param['finished'],
                'crid'=>$param['crid'],'folderid'=>$param['folderid']);
            if(!empty($param['ip'])) {  //此ip为最后一次播放的IP地址
                $logrow['ip'] = $param['ip'];
            }
			if ($logrow['ltime'] <= 0 || $logrow['ctime'] <= 0)	//ltime和ctime不能为空
				return FALSE;
            return Ebh()->db->insert('ebh_playlogs',$logrow);
        }
    }
    /**
     * 根据cwid和uid获取对应课件的total log
     * @param $param array 带有uid和cwid的数组参数
     * @param $withCount bool 是否统计该用户单课播放次数
     */
    public function getTotalLog($param,$withCount = FALSE) {
        if (empty($param['cwid']) || empty($param['uid']))
            return FALSE;
        $cwid = $param['cwid'];
        $uid = $param['uid'];
        $sql = "SELECT logid,uid,cwid,ctime,ltime,finished,curtime,crid,folderid,ip,startdate,lastdate FROM ebh_playlogs WHERE cwid=$cwid AND uid=$uid AND totalflag=1 limit 1";
        $totalLog = Ebh()->db->query($sql)->row_array();
        if (!empty($totalLog) && $withCount) {
            $countsql = "SELECT count(*) playcount,sum(ltime) totalltime FROM ebh_playlogs WHERE cwid=$cwid AND uid=$uid AND totalflag=0";
            $countrow  = Ebh()->db->query($countsql)->row_array();
            $playcount = !empty($countrow) ? $countrow['playcount'] : 0;    //学习次数
            $totalLog['playcount'] = $playcount;
            $totalltime = !empty($countrow) ? $countrow['totalltime'] : 0;  //学习总时长
            $totalLog['totalltime'] = $totalltime;
        }
        return $totalLog;
    }
    /**
     *获取学生的听课记录 (课程或者课件或者学生从数据库删除了则忽略该记录)
     */
    public function getListForClassroom2($queryarr = array()){
        //获取班级学生(包括user表里面有的和没的)
        if(!empty($queryarr['classid'])){
            $sql_for_uid = "select cs.uid as uid,cs.classid,classname from ebh_classstudents cs join ebh_classes c on cs.classid = c.classid  where c.classid = ".$queryarr['classid'];
            $wherearr[]= 'cl.classid='.$queryarr['classid'];
        }else{
            $sql_for_uid = 'select cs.uid as uid,cs.classid,classname from ebh_classstudents cs join ebh_classes c on cs.classid = c.classid  where cs.classid in (select classid from ebh_classes where crid = '.$queryarr['crid'].')';
        }

        $uidArrList = $this->db->query($sql_for_uid)->list_array();
        if(empty($uidArrList)){
            //班级或者学校没有一个学生
            return array();
        }
        $uid_classname_map = array();
        $uid_in = array();
        foreach ($uidArrList as $uidArr) {
            $uid_classname_map['udm_'.$uidArr['uid']] = $uidArr['classname'];
            $uid_in[] = $uidArr['uid'];
        }
        //获取班级学生(剔除掉user表里面没有的学生)
        $othercolumns = '';
        $sql_for_uid_filter = 'select u.uid,u.username,u.realname'.$othercolumns.' from ebh_users u where uid in ('.implode(',', $uid_in).')';
        if(!empty($queryarr['limit'])) {
            $sql_for_uid_filter .= ' limit '. $queryarr['limit'];
        } else {
            if (empty($queryarr['page']) || $queryarr['page'] < 1)
                $page = 1;
            else
                $page = $queryarr['page'];
            $pagesize = empty($queryarr['pagesize']) ? 10 : $queryarr['pagesize'];
            $start = ($page - 1) * $pagesize;
            $sql_for_uid_filter .= ' limit ' . $start . ',' . $pagesize;
        }

        $userList = $this->db->query($sql_for_uid_filter)->list_array();
        if(empty($userList)){
            //虽然班级里面有学生但是用户表里面一个也没有对应的学生,也就是说符合条件的学生在users表里不存在
            return array();
        }
        $uid_userinfo_map = array();
        $uid_in = array();
        foreach ($userList as $user) {
            $uid_userinfo_map['uum'.$user['uid']] = $user;
            $uid_in[] = $user['uid'];
        }
        $wherearr = array();

        //$sql_for_playlogs = "select pl.uid,pl.ctime,pl.ltime,pl.cwid,f.folderid,f.foldername from ebh_playlogs pl join ebh_roomcourses rc on pl.cwid = rc.cwid join ebh_folders f on rc.folderid = f.folderid join ebh_coursewares cw on pl.cwid = cw.cwid";

        $sql_for_playlogs = "select pl.uid,pl.ctime,pl.ltime,pl.cwid,rc.folderid from ebh_playlogs pl join ebh_roomcourses rc on pl.cwid = rc.cwid";

        if(!empty($uid_in)){
            $wherearr[] = 'pl.uid in ('.implode(',', $uid_in).')';
        }
        if(!empty($queryarr['starttime'])){
            $wherearr[] = 'pl.lastdate >='.$queryarr['starttime'];
        }
        if(!empty($queryarr['endtime'])){
            $wherearr[] = 'pl.lastdate <='.$queryarr['endtime'];
        }
        $wherearr[] = 'pl.totalflag = 0';
        $wherearr[] = 'rc.crid = '.$queryarr['crid'];
        if(!empty($wherearr)){
            $sql_for_playlogs .= ' WHERE '.implode(' AND ', $wherearr);
        }
        $sql_for_playlogs .= ' order by pl.uid,pl.cwid';
        $loglist = $this->db->query($sql_for_playlogs)->list_array();
        if(empty($loglist)){
            if(!empty($queryarr['get_nologs'])){
                $reuturnArr = array();
                foreach ($uid_in as $uid) {
                    $userinfo = $uid_userinfo_map['uum'.$uid];
                    $item = array(
                        'uid'=>$uid,
                        'scount'=>0,
                        'stime'=>0,
                        'ctime'=>0,
                        'classname'=>$uid_classname_map['udm_'.$uid],
                        'foldername'=>'无',
                        'folderid'=>0,
                        'username'=>$userinfo['username'],
                        'realname'=>$userinfo['realname'],
                        'tag'=>1
                    );
                    $reuturnArr[] = $item;
                }
                return $reuturnArr;
            }else{
                return array();
            }
        }

        $folderid_in = $this->_getFieldArr($loglist,'folderid');
        $sql_for_folderinfo = 'select f.folderid,f.foldername from ebh_folders f where f.folderid in ('.implode(',',$folderid_in).')';
        $folderList = $this->db->query($sql_for_folderinfo)->list_array();
        if(empty($folderList)){
            //课程全删了 什么都没有了
            return array();
        }
        $folderid_foldername_map = array();
        foreach ($folderList as $folder) {
            $folderid_foldername_map['ffm_'.$folder['folderid']] = $folder['foldername'];
        }

        $cwid_in = $this->_getFieldArr($loglist,'cwid');
        $sql_for_cwid = 'select cw.cwid from ebh_coursewares cw where cw.cwid in ('.implode(',', $cwid_in).')';
        $cwidList = $this->db->query($sql_for_cwid)->list_array();
        if(empty($cwidList)){
            //课件都没了，什么都没了
            return array();
        }
        $cwid_cwid_map = array();
        foreach ($cwidList as $cwidinfo) {
            $cwid_cwid_map['ccm_'.$cwidinfo['cwid']] = $cwidinfo;
        }

        $reuturnArr = array();

        foreach ($loglist as $log) {
            if(empty($cwid_cwid_map['ccm_'.$log['cwid']])){
                //这一步主要用来剔除掉课件不存在[课件被彻底删除了]的记录
                continue;
            }
            $key = $log['uid'].$log['folderid'];
            if(!array_key_exists($key, $reuturnArr)){
                if(empty($folderid_foldername_map['ffm_'.$log['folderid']])){
                    //这一步主要用来剔除掉课程不存在[课程被删除了]的记录
                    continue;
                }else{
                    $foldername = $folderid_foldername_map['ffm_'.$log['folderid']];
                }
                $userinfo = $uid_userinfo_map['uum'.$log['uid']];
                $item = array(
                    'uid'=>$log['uid'],
                    'scount'=>1,
                    'stime'=>$log['ltime'],
                    'ctime'=>$log['ctime'],
                    'classname'=>$uid_classname_map['udm_'.$log['uid']],
                    'foldername'=>$foldername,
                    'folderid'=>$log['folderid'],
                    'username'=>$userinfo['username'],
                    'realname'=>$userinfo['realname']
                );
                $reuturnArr[$key] = $item;
            }else{
                $reuturnArr[$key]['scount']++;
                $reuturnArr[$key]['stime'] += $log['ltime'];
            }

        }

        //是否获取没有记录的学生列表
        if(!empty($queryarr['get_nologs'])){
            $hasLogUids = $this->_getFieldArr($reuturnArr,'uid');
            foreach ($uid_in as $uid) {
                if(!in_array($uid, $hasLogUids)){
                    $userinfo = $uid_userinfo_map['uum'.$uid];
                    $item = array(
                        'uid'=>$uid,
                        'scount'=>0,
                        'stime'=>0,
                        'ctime'=>0,
                        'classname'=>$uid_classname_map['udm_'.$uid],
                        'foldername'=>'无',
                        'folderid'=>0,
                        'username'=>$userinfo['username'],
                        'realname'=>$userinfo['realname'],
                        'tag'=>1
                    );
                    $reuturnArr[] = $item;
                }
            }
        }
        return array_values($reuturnArr);
    }
    /**
     *获取二维数组指定的字段集合
     */
    private function _getFieldArr($param = array(),$filedName=''){

        $reuturnArr = array();

        if(empty($filedName)||empty($param)){
            return $reuturnArr;
        }

        foreach ($param as $value) {
            array_push($reuturnArr, $value[$filedName]);
        }

        return array_unique($reuturnArr);
    }
	
	
	/*
	 * 学生整个学校的学习时间
	 * @param $param [array]  uid,crid
	*/
	public function getTimeByCrid($param){
		if(empty($param['uid']) || empty($param['crid'])){
			return FALSE;
		}
		$sql = 'select sum(ltime) ltime from ebh_playlogs ';
		$wherearr[] = 'uid='.$param['uid'];
		$wherearr[] = 'crid='.$param['crid'];
		$wherearr[] = 'totalflag=0';
		if(!empty($param['exceptlogid'])){//不获取特定logid
			$wherearr[] = 'logid<>'.$param['exceptlogid'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$row = $this->db->query($sql)->row_array();
		return $row['ltime'];
	}
		
	/*
	 * 学生累计时间 和 完成数量(同一课件只记一次) 按课程分组
	 * @param $param [array]  uid,folderids
	*/
	public function getTimeForForFolderCredit($param){
		if(empty($param['uid']) || empty($param['folderids'])){
			return FALSE;
		}
		$sql = 'select sum(ltime) ltime,folderid,count(DISTINCT CASE WHEN finished=1 THEN cwid END) finishedcount from ebh_playlogs';
		$wherearr[] = 'uid='.$param['uid'];
		$wherearr[] = 'folderid in ('.$param['folderids'].')';
		$wherearr[] = 'totalflag=0';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by folderid';
		return $this->db->query($sql)->list_array('folderid');
	}
	
	/*
	 * 学习情况,按 课件-多个用户组 分
	*/
	public function getStudyInfoByClass($param){
		$sql = 'select ';
		foreach($param['conditionarr'] as $condition){
			$conditionarr[] = 'count(distinct CASE WHEN uid in ('.$condition['uids'].') and cwid = '.$condition['cwid'].' THEN uid END) studyusercount_c'.$condition['classid'].'_cw'.$condition['cwid'];
			$conditionarr[] = 'count(CASE WHEN uid in ('.$condition['uids'].') and cwid = '.$condition['cwid'].' and totalflag=0 THEN uid END) studycount_c'.$condition['classid'].'_cw'.$condition['cwid'];
			$conditionarr[] = 'sum(CASE WHEN uid in ('.$condition['uids'].') and cwid = '.$condition['cwid'].' and totalflag=0 THEN ltime END) ltime_c'.$condition['classid'].'_cw'.$condition['cwid'];
		}
		$sql.= implode(',',$conditionarr);
		$sql.=' from ebh_playlogs';
		$wherearr[] = 'crid='.$param['crid'];
		if(!empty($param['starttime'])){
			$wherearr[] = 'lastdate>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'lastdate<='.$param['endtime'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		return $this->db->query($sql)->row_array();
	}
	
	/*
	 * 单课件,学生学习情况
	*/
	public function getCwStudyListByUser($param){
		$sql = 'select max(lastdate) lastdate,min(lastdate) startdate,sum(ltime) ltime,count(1) studycount,uid from ebh_playlogs';
		$wherearr[] = 'cwid ='.$param['cwid'];
		$wherearr[] = 'uid in('.$param['uids'].')';
		$wherearr[] = 'totalflag =0';
		if(!empty($param['starttime'])){
			$wherearr[] = 'lastdate>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'lastdate<='.$param['endtime'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by uid';
		return $this->db->query($sql)->list_array('uid');
	}
	
	/*
	 * 多个课件,学生学习情况
	*/
	public function getMultiCwStudyListByUser($param){
		$sql = 'select max(lastdate) lastdate,min(lastdate) startdate,sum(ltime) ltime,count(1) studycount,CONCAT(cwid,\'_\',uid) as cwuidkey from ebh_playlogs';
		foreach($param['conditionarr'] as $condition){
			$conditionarr[] = '(uid in ('.$condition['uids'].') AND cwid='.$condition['cwid'].')';
		}
		$wherearr[] = '('.implode(' OR ',$conditionarr).')';
		$wherearr[] = 'totalflag=0';
		if(!empty($param['starttime'])){
			$wherearr[] = 'lastdate>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'lastdate<='.$param['endtime'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by cwid,uid';
		return $this->db->query($sql)->list_array('cwuidkey');
	}
	
	/*
	编辑课程学分时 处理学生学分记录
	先清空关于该课程的学分记录,然后根据N=课程总分/单课件分数,
	每个学生从playlogs插入N条记录到studycreditlogs
	*/
	public function doStudyCredit($folderid,$crid){
		if(empty($folderid) || empty($crid)){
			return FALSE;
		}
		$uslib = new UserStudyInfo();
		$this->db->delete('ebh_studycreditlogs',array('folderid'=>$folderid,'type'=>0));
		$sql = 'select cwcredit,cwpercredit from ebh_folders where folderid='.$folderid;
		$folder = $this->db->query($sql)->row_array();
		if(empty($folder['cwpercredit']) || floatval($folder['cwpercredit']) == 0){
			return FALSE;
		}
		$cwnum = floor($folder['cwcredit']/$folder['cwpercredit']);
		
		$sql = 'select distinct(uid) from ebh_playlogs where totalflag=1 and finished=1 and folderid='.$folderid;
		$uidlist = $this->db->query($sql)->list_array();
		if(empty($uidlist)){
			//清除网校学生学分缓存
			$uslib->clearCache($crid);
			return FALSE;
		}
		$uidcount = count($uidlist);
		$i = 0;
		$pindex = 0;
		$uids = '';
		foreach($uidlist as $uid){
			$uids .= $uid['uid'].',';
			$i++;
			$pindex++;
			if($i == 100 || $pindex == $uidcount){
				$uids = rtrim($uids,',');
				$insertsql = 'insert into ebh_studycreditlogs (`uid`,`crid`,`folderid`,`cwid`,`dateline`,`type`,`score`,`fromip`)
							select a.uid,a.crid,a.folderid,a.cwid,a.startdate,0,'.$folder['cwpercredit'].',a.ip
							from ebh_playlogs a 
							join ebh_playlogs b on a.uid=b.uid and a.crid=b.crid and a.logid>= b.logid
							where a.folderid='.$folderid.' and b.folderid='.$folderid.' and a.totalflag=1 and b.totalflag=1 and a.finished=1 and b.finished=1
							and a.uid in ('.$uids.')
							group by a.uid,a.logid,a.crid having count(b.logid)<='.$cwnum.' order by a.logid';
				$this->db->query($insertsql);
				$i = 0;
				$uids = '';
			}
		}
		//清除网校学生学分缓存
		$uslib->clearCache($crid);
	}

    /**
     * 获取课件学习人数
     * @param $cwid
     * @return mixed
     */
	public function getStudyCountByCwid($cwid){
	    $sql = 'select count(distinct uid) as count from ebh_playlogs where cwid='.$cwid;
	    $res = $this->db->query($sql)->row_array();
	    return $res['count'];
    }

    /**
     * 获取学生学习的课件数量
     * @param $param
     * @return int
     */
    public function getCourseCountByUid($param){
        if(empty($param['uid'])){
            return 0;
        }
	    $sql  = 'select count(distinct pl.cwid) as count from ebh_playlogs pl left join ebh_coursewares cw on cw.cwid=pl.cwid';

        $wherearr[] = 'pl.uid ='.$param['uid'];
        if(!empty($param['normal'])){
            $wherearr[] = ' cw.status = 1';
        }
        if(!empty($param['crid'])){
            $wherearr[] = 'pl.crid ='.$param['crid'];
        }
        $sql.= ' where '.implode(' AND ',$wherearr);
        $res = $this->db->query($sql)->row_array();
        return $res['count'];
    }

    /**
     * 读取用户已学课件列表
     * @param $param
     * @return array
     */
    public function getCourseListByUid($param){
        if(empty($param['uid'])){
            return array();
        }
        $sql  = 'select distinct pl.cwid,pl.startdate,cw.uid,cw.title,cw.logo,cw.summary,cw.cwname,cw.cwsource,cw.cwurl,cw.cwlength,cw.dateline,cw.submitat,cw.truedateline,cw.islive,u.username,u.realname from ebh_playlogs pl left join ebh_coursewares cw on cw.cwid=pl.cwid left join ebh_users u on (u.uid = cw.uid)   ';

        $wherearr[] = 'pl.uid ='.$param['uid'];

        if(!empty($param['normal'])){
            $wherearr[] = ' cw.status = 1';
        }
        if(!empty($param['crid'])){
            $wherearr[] = ' pl.crid ='.$param['crid'];
        }

        $sql.= ' where '.implode(' AND ',$wherearr);
        $sql .= ' group by pl.cwid';
        $sql.= ' order by logid desc';
        if(!empty($param['limit'])) {
            $sql .= ' limit '. $param['limit'];
        }

        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 学生学分统计
     * @param array $uids 学生ID集
     * @param int $crid 网校ID
     * @return mixed
     */
    public function getStudentScoreList($uids, $crid) {
        $sql = 'SELECT SUM(`score`) AS `score`,`uid` FROM `ebh_studycreditlogs` WHERE `uid` IN('.implode(',', $uids).') AND `crid`='.$crid.' AND `del`=0 GROUP BY `uid`';
        return Ebh()->db->query($sql)->list_array('uid');
    }

    /**
     * @describe:获取用户的课程的总学习时长
     * @User:tzq
     * @Date:2017/12/19
     * @param int $crid 网校id
     * @param int $uid  当前用户id
     * @param string $folderids 课程id多个用逗号隔开
     * @return array   array('folderid1'=>array('ltime'=>4544),
    'folderid2'=>array('ltime'=>7845)
    )
     */
    public function getLengthByFolder($crid,$uid,$folderids){

        if (empty($crid) || empty($uid) || empty($folderids)) {
            return FALSE;
        }
        $where = array();
        $where[]  = '`co`.`cwlength`>0';//统计学习时长大于0的减少查询记录
        $where[]  = '`ro`.`folderid` IN(' . $folderids . ')';//
        $where[] = '`co`.`status` >= 0';//判断状态
        $where[]  = '`ro`.`crid`=' . $crid;
        $sql      = 'SELECT `ro`.`folderid`,`co`.`cwid`,`co`.`cwlength` FROM `ebh_roomcourses` `ro` ';
        $sql      .= 'JOIN `ebh_coursewares` `co` ON `ro`.`cwid`=`co`.`cwid` ';
        $sql      .= ' WHERE ' . implode(' AND ', $where);

        $list = $this->db->query($sql)->list_array();//获取课件列表
        //取出课件列表
        $cwidsArr = array_column($list,'cwid');
        if(!empty($cwidsArr)){
            $cwids = implode(',',$cwidsArr);
            $pwhere = array(); //学习日志表条件
            $pwhere[] = '`pl`.`ltime` >0';//统计听课时间大于0秒的
            $pwhere[] = '`pl`.`totalflag` = 1';//统计 总记录
            $pwhere[] = '`pl`.`crid`=' . $crid;
            $pwhere[] = '`pl`.`uid`=' . $uid;
            $pwhere[] = '`pl`.`cwid` IN (' . $cwids . ')';
            $sql = 'SELECT `cwid`,`folderid`, `ltime`, `ctime` FROM ebh_playlogs `pl` WHERE '.implode(' AND ',$pwhere);
            $sql .= ' GROUP BY `pl`.`cwid` ORDER BY NULL';
            $coursewareList = $this->db->query($sql)->list_array('cwid');
        }
        $folderList = array();
        foreach ($list as $item) {
            $folderid = $item['folderid'];
            $cwid     = $item['cwid'];
            if(!isset($folderList[$item['folderid']])){
                $ltime = isset($coursewareList[$cwid]['ltime']) ? $coursewareList[$cwid]['ltime'] : 0 ;
                $ctime = isset($coursewareList[$cwid]['ctime']) ? $coursewareList[$cwid]['ctime'] : 0 ;
                if($ltime > $ctime)
                    $ltime = $ctime;
                $folderList[$folderid]['ltime']  = $ltime ;
            }else{
                $ltime = isset($coursewareList[$cwid]['ltime']) ? $coursewareList[$cwid]['ltime'] : 0 ;
                $ctime = isset($coursewareList[$cwid]['ctime']) ? $coursewareList[$cwid]['ctime'] : 0 ;
                if($ltime > $ctime)
                    $ltime = $ctime;
                $folderList[$folderid]['ltime']  += $ltime ;
            }
        }
        return isset($folderList) ? $folderList : array();
    }

    /**
     * @describe:获取普通课件的学习时长,获取失败为没有学习
     * @User:gl
     * @Date:2017/12/22
     * @param int   $crid  网校id
     * @param int   $uid   用户id
     * @param string $cwids 课件id,多个逗号隔开
     * @return array array('123'=>array('cwid'=>123,'folderid'=>4656,'ltime'=>4512),
     *                     '124'=>array('cwid'=>124,'folderid'=>4656,'ltime'=>478)
     *                   )
     */
    public function getCourseltime($crid,$uid,$cwids){
        $crid = intval($crid);
        $uid  = intval($uid);
        if ($crid == 0 || $uid == 0) {
            return 0;
        }
        $where   = array();
        $where[] = '`crid`=' . $crid;
        $where[] = '`uid`=' . $uid;
        $where[] = '`cwid` IN(' . $cwids . ')';
        $where[] = '`totalflag`=1';
        $sql     = 'SELECT  `cwid`,`folderid`,`ltime`';
        $sql     .= ' FROM ebh_playlogs  WHERE ' . implode(' AND ', $where);
        return $this->db->query($sql)->list_array('cwid');

    }

    /**
     * @describe:取本课件用户学习持续时间最长的时间
     * @User:tzq
     * @Date:2017/12/21
     * @param int  $cwid 课件id
     * @param int   $crid 网校id
     * @param int  $uid   用户id
     * @return  int
     */
    public function getStudyLength($cwid,$crid,$uid){
        $cwid = intval($cwid);
        $crid = intval($crid);
        $uid  = intval($uid);
        if ($cwid == 0 || $crid == 0 || $uid == 0) {
            return 0;
        }
        $where   = array();
        $where[] = '`crid`=' . $crid;
        $where[] = '`uid`=' . $uid;
        $where[] = '`cwid`=' . $cwid;
        $where[] = '`totalflag`=0';

        $sql       = 'SELECT MAX(`ltime`) `ltime` ';
        $sql       .= 'FROM ebh_playlogs  WHERE ' . implode(' AND ', $where);
        $maxLength = $this->db->query($sql)->row_array();
        return $maxLength['ltime'] > 0 ? $maxLength['ltime'] : 0;
    }

    /**
     * @describe:获取国土的课件的累计时长,获取失败为没有学习
     * @User:tzq
     * @Date:2017/12/22
     * @param int   $crid  网校id
     * @param int   $uid   用户id
     * @param string $cwids 课件id,多个逗号隔开
     * @param bool   $isCount 是否统计
     * @return mixed 获取失败int
     */
    public function getCourseTotalltime($crid,$uid,$cwids,$isCount=true){
        $crid = intval($crid);
        $uid  = intval($uid);
        if ($crid == 0 || $uid == 0) {
            return 0;
        }
        $where   = array();
        $where[] = '`crid`=' . $crid;
        $where[] = '`uid`=' . $uid;
        $where[] = '`cwid` IN(' . $cwids . ')';
        $where[] = '`totalflag`=0';
        $field = ['`cwid`,`folderid`','`finished`'];
        if($isCount){
            array_push($field,'SUM(`ltime`)  `totalltime`');
        }
        $sql     = 'SELECT  '.implode(',',$field);
        $sql     .= ' FROM ebh_playlogs  WHERE ' . implode(' AND ', $where);
        $sql     .= ($isCount?' GROUP BY  `cwid` ORDER BY NULL':'');
        return $this->db->query($sql)->list_array('cwid');

    }
}