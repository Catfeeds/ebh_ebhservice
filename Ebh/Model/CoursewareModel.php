<?php

/**
 * 课件
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/15
 * Time: 16:27
 */
class CoursewareModel
{
    private $db;
    function __construct()
    {
        $this->db = Ebh()->db;
    }

    /**
     * 获取课件详情
     * @param int $cwid
     * @param int $crid
     * @return array
     */
    public function getcoursedetail($cwid,$crid=0) {
        $sql = 'select c.cwid,c.uid,c.catid,c.title,c.tag,c.logo,c.images,c.isrtmp,c.ism3u8,c.m3u8url,c.thumb,c.summary,c.message,c.cwname,c.cwsource,c.cwurl,cwsize,c.dateline,c.ispreview,c.apppreview,c.status,c.submitat,c.endat,c.cwlength,c.islive,c.liveid,c.sourceid,c.checksum,c.zannum,c.assistantid,c.open_chatroom,c.live_type,u.username,u.realname,rc.crid,rc.folderid,rc.sid,rc.isfree,rc.cdisplayorder,rc.delaytime,rc.classids,f.foldername,f.fprice,f.isremind,f.remindtime,f.remindmsg,f.summary as fsummary,f.img as flogo,f.isschoolfree,c.viewnum,c.submitat,c.endat,c.cwlength,c.truedateline,u.sex,u.face,rc.cwpay,rc.cmonth,rc.cday,rc.cprice,rc.roomfee,rc.looktime,rc.comfee,rc.classids ' .
            'from ebh_coursewares c ' .
            'join ebh_roomcourses rc on (c.cwid = rc.cwid) ' .
            'left join ebh_users u on (u.uid = c.uid) ' .
            'join ebh_folders f on (f.folderid = rc.folderid) ' .
            'where c.cwid=' . $cwid . ' and f.del=0';
        if($crid>0){
            $sql .= ' and rc.crid= '.$crid;
        }
        return $this->db->query($sql)->row_array();
    }
    /**
     * 获取平台最新发布的课件
     */
    public function getNewCourseList($queryarr) {
        $sql = 'SELECT c.cwid,c.title,c.summary,c.viewnum,c.reviewnum,c.dateline,c.logo,u.username,u.realname,c.cwurl,f.foldername,f.folderid,f.coursewarelogo,u.face,u.sex,c.submitat,c.endat,c.ism3u8,c.cwlength,c.truedateline,c.islive,c.uid,c.assistantid FROM ebh_coursewares c ' .
            'JOIN ebh_roomcourses rc on (c.cwid = rc.cwid) '.
            'JOIN ebh_users u on (u.uid = c.uid) '.
            'JOIN ebh_folders f on f.folderid=rc.folderid';
        $wherearr = array();
        $wherearr[] = ' c.status != -3 ';
        if (!empty($queryarr['crid'])) {
            $wherearr[] = 'rc.crid=' . $queryarr['crid'];
        }
        if (!empty($queryarr['status'])) {
            $wherearr[] = 'c.status = 1';
        }
        if (!empty($queryarr['uid'])) {
            $wherearr[] = 'c.uid=' . $queryarr['uid'];
        }
        if(!empty($queryarr['abegindate'])) {
            $wherearr[] = 'c.dateline>='.$queryarr['abegindate'];
        }
        if(!empty($queryarr['aenddate'])) {
            $wherearr[] = 'c.dateline<'.$queryarr['aenddate'];
        }
        if (!empty($queryarr['classids'])) {
            if (empty($queryarr['classids'])) {
                $wherearr[] = '(rc.classids=\'\' OR rc.classids=\'0\')';
            } else {
                $wherearr[] = '(rc.classids=\'\' OR rc.classids=\'0\' OR find_in_set('.$queryarr['classids'].',rc.classids))';
            }
        }
        if(!empty($queryarr['truedatelinefrom']))
            $wherearr[] = 'c.truedateline>='.$queryarr['truedatelinefrom'];
        if(!empty($queryarr['truedatelineto']))
            $wherearr[] = 'c.truedateline<'.$queryarr['truedatelineto'];
        if(isset($queryarr['power']))
            $wherearr[] = 'f.power in ('.$queryarr['power'].')';
        if(!empty($queryarr['folderids']))
            $wherearr[] = 'rc.folderid in ('.$queryarr['folderids'].')';
        if (!empty($wherearr))
            $sql .= ' WHERE ' . implode(' AND ', $wherearr);
        if (!empty($queryarr['order']))
            $sql .= ' ORDER BY ' . $queryarr['order'];
        else
            $sql .= ' ORDER BY c.cwid DESC ';
        if (!empty($queryarr['limit']))
            $sql .= ' limit ' . $queryarr['limit'];
        else {
            $sql .= ' limit 0,10 ';
        }
        //  log_message($sql);
        return $this->db->query($sql)->list_array();
    }

    /**
     * 添加课件的评论数
     * @param int $cwid
     * @param int $num
     */
    public function addreviewnum($cwid, $num = 1) {
        $where = 'cwid=' . $cwid;
        $setarr = array('reviewnum' => 'reviewnum+' . $num);
        Ebh()->db->update('ebh_coursewares', array(), $where, $setarr);
    }

    /**
     * 统计网校下评论总数
     * @param $crid
     * @return bool
     */
    function getReviewCountForRoom($crid)
    {
        $crid = (int) $crid;
        $sql = "SELECT SUM(`a`.`reviewnum`) AS `c` FROM `ebh_coursewares` `a`
                LEFT JOIN `ebh_roomcourses` `b` ON `a`.`cwid`=`b`.`cwid`
                LEFT JOIN `ebh_folders` `c` ON `b`.`folderid`=`c`.`folderid`
                WHERE `c`.`crid`=$crid";
        $ret = $this->db->query($sql)->row_array();
        if (isset($ret['c'])) {
            return $ret['c'];
        }
        return false;
    }

	/*
	课程的课件列表
	 @param array  folderid,crid
	 @return array
	*/
	public function getCwListByFolderid($param){
		if(empty($param['folderid']) || empty($param['crid']))
			return FALSE;
		$sql = 'SELECT cw.cwid,cw.uid,cw.notice,cw.islive,cw.title,cw.dateline,cw.attachmentnum,cw.examnum,cw.ism3u8,cw.logo,cw.thumb,cw.zannum,cw.cwurl,cw.islive,cw.summary,cw.viewnum,cw.reviewnum,cw.submitat,cw.endat,cw.cwsize,cw.cwlength,r.cwpay,r.cprice,r.folderid,r.sid,r.cdisplayorder,r.crid
		from ebh_roomcourses r join ebh_coursewares cw on r.cwid=cw.cwid';
		if(!empty($param['issimple'])){
			$sql = 'SELECT cw.cwid,cw.title as name,r.folderid
				from ebh_roomcourses r join ebh_coursewares cw on r.cwid=cw.cwid';
		}
		$wherearr = array();
		$wherearr[] = 'folderid in('.$param['folderid'].')';
		$wherearr[] = 'crid ='.$param['crid'];
		$wherearr[] = 'cw.status=1';
        if (!empty($param['s'])) {
            $wherearr[] = 'cw.title like \'%'.Ebh()->db->escape_str($param['s']).'%\'';
        }
		if(!empty($param['starttime'])){
			$wherearr[] = 'cw.truedateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'cw.truedateline<='.$param['endtime'];
		}
		if(!empty($param['videoonly'])){
			$wherearr[] = 'cw.ism3u8=1';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' order by r.cdisplayorder,r.sid desc,cw.cwid desc';
		if(!empty($param['limit'])) {
            $sql .= ' limit '. $param['limit'];
        } else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
		return Ebh()->db->query($sql)->list_array();
	}
	/*
	课程的课件数量
	 @$param array  folderid,crid, needgroup 按folderid分组
	 @return int
	*/
	public function getCwCountByFolderid($param){
		if(empty($param['folderid']) || empty($param['crid']))
			return FALSE;
		$sql = 'select count(*) count,folderid from ebh_roomcourses r join ebh_coursewares cw on r.cwid=cw.cwid ';
		$wherearr = array();
		$wherearr[] = 'folderid in('.$param['folderid'].')';
		$wherearr[] = 'crid ='.$param['crid'];
		$wherearr[] = 'cw.status=1';
        if (isset($param['sid'])) {
            $wherearr[] = 'r.sid='.intval($param['sid']);
        }
		if (!empty($param['s'])) {
		    $wherearr[] = 'cw.title like \'%'.Ebh()->db->escape_str($param['s']).'%\'';
        }
		if(!empty($param['starttime'])){
			$wherearr[] = 'cw.truedateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'cw.truedateline<='.$param['endtime'];
		}
		if(!empty($param['videoonly'])){
			$wherearr[] = 'cw.ism3u8=1';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		if(empty($param['needgroup'])){
			$count = Ebh()->db->query($sql)->row_array();
			return $count['count'];
		} else {
			$sql .= ' group by folderid';
			return Ebh()->db->query($sql)->list_array('folderid');
		}
	}

    /**
     * 简介课件信息列表
     * @param $cwids 课件ID集
     * @param $setKey 是否设置键为课件ID
     * @return bool
     */
	public function getSimpleInfoByIds($cwids, $setKey = false) {
	    if (empty($cwids)) {
	        return false;
        }
        if (is_array($cwids)) {
	        $cwids = array_map(function($cwid) {
	            return intval($cwid);
            }, $cwids);
	        $cwids = array_unique($cwids);
        } else {
	        $cwids = array(intval($cwids));
        }
        $sql = 'SELECT `cwid`,`uid`,`title`,`summary`,`logo`,`dateline`,`viewnum`,`reviewnum`,`cwurl`,`islive`,`cwlength` FROM `ebh_coursewares` WHERE `cwid` IN('.implode(',', $cwids).')';
	    if ($setKey) {
            return Ebh()->db->query($sql)->list_array('cwid');
        }
	    return Ebh()->db->query($sql)->list_array();
    }
    /**
     * 根据课件编号cwid获取课件基础信息，（一般只取较小和常用的字段，不建议这里获取课件详情等超长信息）
     * @param $cwid int 课件编号cwid
     * @return Array 返回课件相关信息数组
     */
    public function getSimpleInfoById($cwid) {
    	$cwid = intval($cwid);
    	if ($cwid <= 0)
    		return 0;
    	$sql = "SELECT c.cwid,c.title,c.islive,c.ism3u8,c.cwlength,c.cwsize,c.status FROM ebh_coursewares c where c.cwid = $cwid";
    	return Ebh()->db->query($sql)->row_array();
    }
    /**
     * 根据课件编号cwid获取课件附加信息，如课件所在课程folderid，所在网校crid等
     * @param $cwid int 课件编号cwid
     * @return Array 返回课件附加信息数组
     */
    public function getExtraInfoById($cwid) {
    	$cwid = intval($cwid);
    	if ($cwid <= 0)
    		return 0;
    	$sql = "SELECT rc.crid,rc.folderid FROM ebh_roomcourses rc where rc.cwid = $cwid";
    	return Ebh()->db->query($sql)->row_array();
    }

	/**
	*获取课件详情
	*/
	public function getCourseByCwid($cwid){
		$sql = 'select rc.delaytime,c.cwid,c.uid,c.catid,c.title,c.tag,c.logo,c.images,c.summary,c.message,c.cwname,c.cwsource,c.cwurl,cwsize,c.dateline,rc.crid,rc.folderid,rc.sid,rc.isfree,rc.cdisplayorder,c.status,f.foldername,c.islive,c.ism3u8,c.m3u8url,c.thumb,c.cwlength,c.cwsize,c.submitat,c.endat,c.ispreview,c.live_type '.
                'from ebh_coursewares c ' .
                'join ebh_roomcourses rc on (c.cwid = rc.cwid) ' .
				'join ebh_folders f on rc.folderid=f.folderid '.
                'where c.cwid=' . $cwid;
        return $this->db->query($sql)->row_array();
	}
    /**
     *获取课件详情
     */
    public function getCourseByCwids($cwids=''){
        if (empty($cwids)) {
            return false;
        }
        $sql = 'select rc.delaytime,c.cwid,c.uid,c.catid,c.title,c.tag,c.logo,c.images,c.summary,c.message,c.cwname,c.cwsource,c.cwurl,cwsize,c.dateline,rc.crid,rc.folderid,rc.sid,rc.isfree,rc.cdisplayorder,c.status,c.islive,c.ism3u8,c.m3u8url,c.thumb,c.cwlength,c.cwsize,c.submitat,c.endat,c.ispreview,c.live_type '.
            'from ebh_coursewares c ' .
            'join ebh_roomcourses rc on (c.cwid = rc.cwid) '.
            'where c.cwid in(' . $cwids . ')';
        return $this->db->query($sql)->list_array();
    }

	/**
	*删除课件
	*/
	public function del($cwid){
		return Ebh()->db->update('ebh_coursewares',array('status'=>-3),array('cwid'=>$cwid));
	}

    /**
     * 获取热门课件列表
     * @param $crid 网校ID
     * @param $num 数量
     * @return array
     */
	public function getHotList($crid, $num) {
	    $fields = array(
	        '`a`.`cwid`', '`a`.`title`,`a`.`logo`','`a`.`summary`','`a`.`viewnum`','`a`.`islive`','`a`.`cwurl`'
        );
	    $params = array(
	        '`b`.`crid`='.intval($crid),
            '`a`.`status`=1',
            '`c`.`del`=0'
        );
	    $sql = 'SELECT '.implode(',', $fields).' FROM `ebh_coursewares` `a`'.
            ' LEFT JOIN `ebh_roomcourses` `b` ON `a`.`cwid`=`b`.`cwid`'.
            ' LEFT JOIN `ebh_folders` `c` ON `b`.`folderid`=`c`.`folderid`'.
            ' WHERE '.implode(' AND ', $params).
            ' ORDER BY `a`.`viewnum` DESC LIMIT '.intval($num);
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

	/*
	 *按课程分组查询课件数量，学分计算用
	*/
	public function getCountForFolderCredit($crid){
		if(empty($crid)){
			return FALSE;
		}
		$sql = 'select f.folderid ,count(*) cwcount,credit,creditmode,creditrule,credittime from ebh_folders f
				join ebh_roomcourses rc on f.folderid=rc.folderid
				join ebh_coursewares cw on rc.cwid=cw.cwid';
		$wherearr[] = 'f.crid='.$crid;
		$wherearr[] = 'f.credit<>0';
		$wherearr[] = 'cw.status=1';
		$wherearr[] = 'cw.ism3u8=1';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by folderid';
		return $this->db->query($sql)->list_array();
	}

    /**
     * 设置课件在章节中的排序号，当章节下的课件的排序值有重复或强制重新排序时排序号从１开始连接排序
     * @param $cwid 课件ID
     * @param $crid 网校ID
     * @param int $step 排序变化值，负数表示优先级提高位数，正数表示优先级降低位数
     * @param bool $reset 全部从1开始连续排序
     * @return bool
     */
	public function rankCoursewareInSection($cwid, $crid, $step = 1, $reset = false) {
	    $step = intval($step);
        if ($step == 0) {
            return false;
        }
        $cwid = intval($cwid);
        $crid = intval($crid);
        $sql = 'SELECT `a`.`sid`,`a`.`folderid` FROM `ebh_roomcourses` `a` LEFT JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE `a`.`cwid`='.$cwid.' AND `a`.`crid`='.$crid. ' AND `b`.`status`=1';
        $self = $this->db->query($sql)->row_array();
        if (empty($self)) {
            return false;
        }
        $sql = 'SELECT `a`.`cwid`,`a`.`cdisplayorder` FROM `ebh_roomcourses` `a` LEFT JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE `a`.`folderid`='.$self['folderid'].' AND `a`.`sid`='.$self['sid'].' AND `a`.`crid`='.$crid.' AND `b`.`status`=1 ORDER BY `a`.`cdisplayorder` ASC,`cwid` DESC';
        $brothers = $this->db->query($sql)->list_array('cwid', 'cw');
        if (empty($brothers)) {
            return false;
        }
        $len = count($brothers);
        //获取课件原先的位置号
        $pos = 0;
        foreach ($brothers as $k => $brother) {
            if ($brother['cwid'] == $cwid) {
                break;
            }
            $pos++;
        }
        //目标位置
        $dstPos = $pos + $step;
        $dstPos = max(0, $dstPos);
        $dstPos = min($dstPos, $len - 1);
        if ($dstPos == $pos) {
            //位置无实际变动
            return false;
        }
        //移动课件位置
        $self = $brothers['cw'.$cwid];
        unset($brothers['cw'.$cwid]);
        $front = array_slice($brothers, 0, $dstPos, true);
        $end = array_diff_key($brothers, $front);
        $front['cw'.$cwid] = $self;
        $brothers = array_merge($front, $end);
        unset($front, $end);
        $cdisplayorders = array_column($brothers, 'cdisplayorder');
        $cdisplayorders = array_flip($cdisplayorders);
        if ($reset || count($cdisplayorders) != $len) {
            //强制重新连续编辑编号或排序编号有重复，重新连续编辑编号
            $start = 1;
        } else {
            $brothers = array_slice($brothers, min($dstPos, $pos), abs($dstPos - $pos) + 1);
            $cdisplayorders = array_column($brothers, 'cdisplayorder');
            $start = min($cdisplayorders);
        }
        //批量更新排序号
        $whenGroup = array();
        $whereGroup = array();
        foreach ($brothers as $brother) {
            $whenGroup[] = ' WHEN '.$brother['cwid'].' THEN '.($start++);
            $whereGroup[] = $brother['cwid'];
        }
        $sql = 'UPDATE `ebh_roomcourses` SET `cdisplayorder`=CASE `cwid`'.implode(' ', $whenGroup).' END WHERE `cwid` IN('.implode(',', $whereGroup).') AND `crid`='.$crid;
        unset($brothers, $whereGroup, $whenGroup);
        return Ebh()->db->query($sql, false);
    }

    /**
     * 分课程章节批量修改课件排序
     * @param int $folderid 课程ID
     * @param array $ranks 排序参数集，二维数组，array(
            cwid => array(
     *          cwid => 课件ID
     *          rank => 排序号
     *      )
     * )
     * @param $crid 网校ID
     * @return bool
     */
    public function batchRankCoursewares($folderid = 0, $ranks = array(), $crid) {
        $cwids = array_keys($ranks);
        $cwids = array_map('intval', $cwids);
        $cwids = implode(',', $cwids);
        $folderid = intval($folderid);
        $sql = 'SELECT `a`.`sid`,`a`.`folderid`,`a`.`cwid` FROM `ebh_roomcourses` `a` JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE `a`.`cwid` IN('.$cwids.') AND `a`.`crid`='.$crid. ' AND `b`.`status`=1';
        if ($folderid > 0) {
            $sql .= ' AND `a`.`folderid`='.$folderid;
        }
        $coursewares = Ebh()->db->query($sql)->list_array('cwid');
        if (empty($coursewares)) {
            return false;
        }
        //将课件根据章节分组
        $group = array();
        foreach ($coursewares as $k => $courseware) {
            if (isset($ranks[$k])) {
                $courseware['rank'] = $ranks[$k]['rank'];
                $courseware['reset'] = true;
            }
            $group[$courseware['folderid'].'_'.$courseware['sid']][$k] = $courseware;
        }
        unset($ranks, $coursewares);
        array_walk($group, function(&$gitem, $gk, $crid) {
            //分课程章节排序课件
            list($folderid, $sid) = explode('_', $gk);
            $wheres = array(
                '`a`.`folderid`='.$folderid,
                '`a`.`sid`='.$sid,
                '`a`.`crid`='.$crid,
                '`b`.`status`=1'
            );
            $sql = 'SELECT `a`.`cwid`,`a`.`cdisplayorder` AS `rank` FROM `ebh_roomcourses` `a` JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE '.implode(' AND ', $wheres);
            $coursewaresInSections = Ebh()->db->query($sql)->list_array('cwid');
            if (empty($coursewaresInSections)) {
                return;
            }
            foreach ($gitem as $sk => $section) {
                if (isset($coursewaresInSections[$sk])) {
                    $coursewaresInSections[$sk]['rank'] = $section['rank'];
                }
            }
            //排序自定义课件
            $displayorders = $keys = $resets = array();
            foreach ($coursewaresInSections as $key => $item) {
                $keys[] = $key;
                $displayorders[] = $item['rank'];
                $resets[] = isset($item['reset']) ? 1 : 0;
            }
            array_multisort($displayorders, SORT_ASC, SORT_NUMERIC,
                $resets, SORT_DESC, SORT_NUMERIC,
                $keys, SORT_DESC, SORT_NUMERIC, $coursewaresInSections);
            //批量更新排序号
            $whenGroup = array();
            $whereGroup = array();
            $start = 1;
            foreach ($coursewaresInSections as $section) {
                $whenGroup[] = ' WHEN '.$section['cwid'].' THEN '.($start++);
                $whereGroup[] = $section['cwid'];
            }
            $sql = 'UPDATE `ebh_roomcourses` SET `cdisplayorder`=CASE `cwid`'.implode(' ', $whenGroup).' END WHERE `cwid` IN('.implode(',', $whereGroup).') AND `crid`='.$crid;
            Ebh()->db->query($sql, false);
        }, $crid);
        return true;
    }

    /**
     * 重置章节内课程排序号
     * @param $folderid 课程ID
     * @param $sid 章节ID
     * @param $crid 网校ID
     * @return bool
     */
    public function resetRankSectionCoursewares($folderid, $sid, $crid) {
        $wheres = array(
            '`a`.`folderid`='.$folderid,
            '`a`.`sid`='.$sid,
            '`a`.`crid`='.$crid,
            '`b`.`status`=1'
        );
        $sql = 'SELECT `a`.`cwid`,`a`.`cdisplayorder` FROM `ebh_roomcourses` `a` LEFT JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE '.implode(' AND ', $wheres);
        $coursewaresInSections = Ebh()->db->query($sql)->list_array('cwid');
        if (empty($coursewaresInSections)) {
            return false;
        }
        //排序自定义课件
        $displayorders = array_column($coursewaresInSections, 'cdisplayorder');
        $keys = array_keys($coursewaresInSections);
        array_multisort($displayorders, SORT_ASC, SORT_NUMERIC, $keys, SORT_DESC, SORT_NUMERIC, $coursewaresInSections);
        //批量更新排序号
        $whenGroup = array();
        $whereGroup = array();
        $start = 1;
        foreach ($coursewaresInSections as $section) {
            $whenGroup[] = ' WHEN '.$section['cwid'].' THEN '.($start++);
            $whereGroup[] = $section['cwid'];
        }
        $sql = 'UPDATE `ebh_roomcourses` SET `cdisplayorder`=CASE `cwid`'.implode(' ', $whenGroup).' END WHERE `cwid` IN('.implode(',', $whereGroup).') AND `crid`='.$crid;
        return Ebh()->db->query($sql, false);
    }

    /**
     * 通过章节名称、课件名称获取课件ID
     * @param array $ranks 二维数组，第二维数组包含课件名称、章节名称
     * @param $crid 网校ID
     * @return array
     */
    public function getCwids($ranks = array(), $crid) {
        $crid = intval($crid);
        if (empty($ranks) || $crid < 1 || !is_array($ranks)) {
            return array();
        }
        $wheres = array();
        foreach ($ranks as $rank) {
            $wheres[] = '`b`.`title`=\''.Ebh()->db->escape_str($rank['title']).'\' AND IFNULL(`c`.`sname`,\'\')=\''.Ebh()->db->escape_str($rank['sname']).'\'';
        }
        $sql = 'SELECT `b`.`cwid`,`b`.`title`,IFNULL(`c`.`sname`,\'\') AS `sname` FROM `ebh_roomcourses` `a`
                LEFT JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid`
                LEFT JOIN `ebh_sections` `c` ON `c`.`sid`=`a`.`sid`
                WHERE `a`.`crid`='.$crid.' AND `b`.`status`=1 AND ('.implode(' OR ', $wheres).')';
        return Ebh()->db->query($sql)->list_array();
    }
	/*
	 *课件学习统计，课件列表
	*/
	public function statsCw($param){
		$sql = 'select distinct(cs.cwid),cw.title,cs.path,i.iname,cs.itemid,cs.classid from ebh_classcwstats cs
				join ebh_coursewares cw on cs.cwid=cw.cwid
				join ebh_roomcourses rc on cs.cwid=rc.cwid
				join ebh_pay_items i on cs.itemid=i.itemid';
		$wherearr[] = 'i.crid='.$param['crid'];
		$wherearr[] = 'rc.crid='.$param['crid'];
		$wherearr[] = 'cs.crid='.$param['crid'];
		if(!empty($param['cwid'])){
			$wherearr[] = 'cs.cwid='.$param['cwid'];
		}
		if(!empty($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$wherearr[] = 'cw.title like \'%'.$q.'%\'';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		if(!empty($param['limit'])) {
            $sql .= ' limit '. $param['limit'];
        } else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
		return $this->db->query($sql)->list_array('cwid');
	}
	/*
	 *课件学习统计，课件数量
	*/
	public function statsCwCount($param){
		$sql = 'select count(distinct cs.cwid) count from ebh_classcwstats cs
				join ebh_coursewares cw on cs.cwid=cw.cwid
				join ebh_roomcourses rc on cs.cwid=rc.cwid
				join ebh_pay_items i on cs.itemid=i.itemid';
		$wherearr[] = 'i.crid='.$param['crid'];
		$wherearr[] = 'rc.crid='.$param['crid'];
		$wherearr[] = 'cs.crid='.$param['crid'];
		if(!empty($param['cwid'])){
			$wherearr[] = 'cs.cwid='.$param['cwid'];
		}
		if(!empty($param['q'])){
			$q = $this->db->escape_str($param['q']);
			$wherearr[] = 'cw.title like \'%'.$q.'%\'';
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		$count = $this->db->query($sql)->row_array();
		return $count['count'];
	}

/*
	 * 课件学习统计，课件，班级列表
	*/
	public function statsClassCw($param){
		if(empty($param['cwids'])){
			return FALSE;
		}
		$sql = 'select cs.cwid,c.classname,c.classid from ebh_classcwstats cs
				join ebh_classes c on cs.classid=c.classid';
		$wherearr[] = 'c.crid='.$param['crid'];
		$wherearr[] = 'cs.cwid in('.$param['cwids'].')';
		$sql.= ' where '.implode(' AND ',$wherearr);
		return Ebh()->db->query($sql)->list_array();
	}


	/*
	 *清空课件班级，课件学习统计用
	*/
	public function clearAllStats($param){
		if(empty($param['cwid'])){
			return false;
		}
		$sql = 'delete from ebh_classcwstats where cwid = '.$param['cwid'];
		$res = Ebh()->db->query($sql,false);
	}
	/*
	 *保存班级课件，课件学习统计用
	*/
	public function saveClassCwStats($param){
		Ebh()->db->begin_trans();
		$this->clearAllStats($param);
		if(empty($param['isclear'])){
			$this->addStats($param);
		}
		if(Ebh()->db->trans_status()===FALSE) {
            Ebh()->db->rollback_trans();
            return FALSE;
        } else {
            Ebh()->db->commit_trans();
        }
		return TRUE;
	}

	/*
	 *添加班级课程关联记录，课件学习统计用
	 */
	private function addStats($param){
		if(empty($param['classids']) || empty($param['cwid']) || empty($param['itemid']) || empty($param['crid'])){
			return false;
		}
		$path = implode('/',$param['path']);
		$sql = 'insert into `ebh_classcwstats` (classid,cwid,itemid,crid,path) values ';
		foreach ($param['classids'] as $classid){
			$sql .= '('.$classid.','.$param['cwid'].','.$param['itemid'].','.$param['crid'].',\''.$path.'\'),';
		}
		$sql = rtrim($sql,',');
		Ebh()->db->query($sql);
	}


	/*
	 *课件学习统计,课件列表
	*/
	public function getCwListStats($param)
    {
        $sql = 'select cs.itemid,c.classid,cw.cwid,c.classname,i.iname,cw.title,cw.uid
			from ebh_classcwstats cs
			join ebh_classes c on cs.classid=c.classid
			join ebh_pay_items i on cs.itemid = i.itemid
			join ebh_coursewares cw on cs.cwid=cw.cwid';
        $wherearr[] = 'i.crid=' . $param['crid'];
        $wherearr[] = 'c.crid=' . $param['crid'];
        $wherearr[] = 'cw.status=1';
        $wherearr[] = 'cw.ism3u8=1';
        if (!empty($param['classid'])) {
            $wherearr[] = 'cs.classid =' . $param['classid'];
        }
        if (!empty($param['itemid'])) {
            $wherearr[] = 'cs.itemid=' . $param['itemid'];
        }
        if (!empty($param['q'])) {
            $q = Ebh()->db->escape_str($param['q']);
            $wherearr[] = '(i.iname like \'%' . $q . '%\' or c.classname like \'%' . $q . '%\' or cw.title like \'%' . $q . '%\')';
        }

        $sql .= ' where ' . implode(' AND ', $wherearr);
        $sql .= ' order by classid asc,itemid asc,cwid asc';
        if (!empty($param['limit'])) {
            $sql .= ' limit ' . $param['limit'];
        } else {
            if (empty($param['page']) || $param['page'] < 1)
                $page = 1;
            else
                $page = $param['page'];
            $pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
            $start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /*
     *课件学习统计,课件数量
    */
    public function getCwCountStats($param){
        $sql = 'select count(*) count
			from ebh_classcwstats cs
			join ebh_classes c on cs.classid=c.classid
			join ebh_pay_items i on cs.itemid = i.itemid
			join ebh_coursewares cw on cs.cwid=cw.cwid';
        $wherearr[]= 'i.crid='.$param['crid'];
        $wherearr[]= 'c.crid='.$param['crid'];
        $wherearr[]= 'cw.status=1';
        $wherearr[]= 'cw.ism3u8=1';
        if(!empty($param['classid'])){
            $wherearr[] = 'cs.classid ='.$param['classid'];
        }
        if(!empty($param['itemid'])){
            $wherearr[] = 'cs.itemid='.$param['itemid'];
        }
        if(!empty($param['q'])){
            $q = Ebh()->db->escape_str($param['q']);
            $wherearr[] = '(i.iname like \'%'.$q.'%\' or c.classname like \'%'.$q.'%\' or cw.title like \'%'.$q.'%\')';
        }

        $sql.= ' where '.implode(' AND ',$wherearr);
        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

    /**
     * 课件排序列表
     * @param $folderid 课程ID
     * @param $crid 网校ID
     * @param null $sid 章节ID
     * @param string $s 查询关键字
     * @return array
     */
    public function coursewareRankTpl($folderid, $crid, $sid = null, $s = '') {
        $folderid = intval($folderid);
        $crid = intval($crid);
        $sql = 'SELECT `c`.`sname`,`b`.`title`,`a`.`cdisplayorder` FROM `ebh_roomcourses` `a`
                LEFT JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid`
                LEFT JOIN `ebh_sections` `c` ON `c`.`sid`=`a`.`sid`
                WHERE `a`.`crid`='.$crid. ' AND `b`.`status`=1';
        if ($folderid > 0) {
            $sql .= ' AND `a`.`folderid`='.$folderid;
        }
        if ($sid !== NULL) {
            $sql .= ' AND `a`.`sid`='.$sid;
        }
        if (!empty($s)) {
            $sql .= ' AND `b`.`title` LIKE \'%'.Ebh()->db->escape_str($s).'%\'';
        }
        $sql .= ' ORDER BY `a`.`sid`,`a`.`cdisplayorder`';
        return Ebh()->db->query($sql)->list_array();
    }


    /**
     * 读取列表条数
     * @return int
     */
    public function getListCountByFolderId($param){
        if(empty($param['folderid']) || empty($param['crid'])){
            return 0;
        }

        $sql = 'select count(cw.cwid) as count from ebh_roomcourses rc
                join ebh_coursewares cw on cw.cwid=rc.cwid
                join ebh_users u on (u.uid = cw.uid)
                left join ebh_sections s on s.sid=rc.sid ';

        $wherearr = array();
        $wherearr[] = 'rc.folderid='.$param['folderid'];
        //$wherearr[] = 'rc.crid ='.$param['crid'];
        $wherearr[] = 'cw.status=1';

        $sql.= ' where '.implode(' and ',$wherearr);
        $rs = Ebh()->db->query($sql)->row_array();

        if($rs){
            return $rs['count'];
        }else{
            return 0;
        }

    }

    public function getListByFolderId($param){
        if(empty($param['folderid']) || empty($param['crid'])){
            return false;
        }

	    $sql = 'select cw.cwid,cw.uid,cw.title,cw.logo,cw.summary,cw.cwname,cw.cwsource,cw.cwurl,cw.cwlength,cw.dateline,cw.submitat,cw.truedateline,cw.islive,s.sid,s.sname,ifnull(s.displayorder,10000) sdisplayorder,u.username,u.realname,rc.cdisplayorder,cw.displayorder from ebh_roomcourses rc
                join ebh_coursewares cw on cw.cwid=rc.cwid
                join ebh_users u on (u.uid = cw.uid)
                left join ebh_sections s on s.sid=rc.sid ';
        $wherearr = array();
        $wherearr[] = 'rc.folderid='.$param['folderid'];
        //$wherearr[] = 'rc.crid ='.$param['crid'];
        $wherearr[] = 'cw.status=1';

        $sql.= ' where '.implode(' and ',$wherearr);
        $sql.= ' order by sdisplayorder asc,s.sid desc,rc.cdisplayorder asc,cw.displayorder asc,cw.cwid desc';
        if(!empty($param['limit'])) {
            $sql .= ' limit '. $param['limit'];
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 根据课件编号获取课件信息(主要针对直播课)
     * @param $cwid int 课件编号
     */
    public function getLiveCourse($cwid) {
        $sql = "select c.cwid,c.uid,c.title,c.islive,c.liveid,c.status,c.truedateline,c.endat from ebh_coursewares c where c.cwid=$cwid";
        return $this->db->query($sql)->row_array();
    }


    /**
     * 批量设置免费试听
     */
    public function updatefreecourse($param){
        $cwidarr = $param['cwid'];
        $crid = $param['crid'];
        $this->db->begin_trans();
        //重置所有的免费视频
        $upsql = 'update ebh_roomcourses set isfree = 0 where isfree = 1 and crid = '.$crid;
        $this->db->query($upsql);
        //添加免费视频
        $apsql = 'update ebh_roomcourses set isfree = 1 where cwid in( '.implode(" , ", $cwidarr).' ) and crid='.$crid;
        $this->db->query($apsql);
        $status = $this->db->trans_status();
        if($status !=false){
           //提交
            $this->db->commit_trans();
        }else{
            //回滚
            $this->db->rollback_trans();
        }

        return $status;
    }

    /**
     * 网校课程下课件统计
     * @param int $crid 网校ID
     * @param array $folderids 课程ID集
     * @return array
     */
    public function getCoursewareCounts($crid, $folderids) {
        $sql = 'SELECT COUNT(1) AS `c`,`a`.`folderid` FROM `ebh_roomcourses` `a`
                JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` AND `a`.`folderid` IN('.implode(',', $folderids).')
                WHERE `a`.`crid`='.$crid.' AND `b`.`status`=1 GROUP BY `a`.`folderid`';
        $ret = Ebh()->db->query($sql)->list_array('folderid');
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

	/**
     * 网校视频课件列表
     * @param int $crid 网校ID
     * @param array $params 过滤条件
	 * @param $limit 限量条件
	 * @param bool $setKey 是否以课件Id
     * @return array
     */
    public function getVedioList($crid, $params = array(), $limit = null, $setKey = false) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`b`.`status`=1',
            '`b`.`islive`=0',
            '`b`.`ism3u8`=1'
        );
        if (!empty($params['folderid'])) {
            $wheres[] = '`a`.`folderid`='.$params['folderid'];
        }
        $offset = 0;
        $top = 0;
        if ($limit !== null) {
            if (is_array($limit)) {
                $page = max(1, isset($limit['page']) ? intval($limit['page']) : 1);
                $top = max(1, isset($limit['pagesize']) ? intval($limit['pagesize']) : 1);
                $offset = ($page - 1) * $top;
            } else {
                $top = intval($limit);
            }
        }
        $sql = 'SELECT `b`.`cwid`,`b`.`title`,`b`.`cwsize`,`b`.`logo`,`b`.`thumb`,`b`.`ism3u8`,`b`.`m3u8url`,`b`.`summary`
                FROM `ebh_roomcourses` `a` JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid`
                WHERE '.implode(' AND ', $wheres);
        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        return Ebh()->db->query($sql)->list_array($setKey ? 'cwid' : '');
    }

	/**
     * 网校视频课件数量
     * @param int $crid 网校ID
     * @param array $params 过滤条件
     * @return int
     */
    public function getVedioCount($crid, $params = array()) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`b`.`status`=1',
            '`b`.`islive`=0',
            '`b`.`ism3u8`=1'
        );
        if (!empty($params['folderid'])) {
            $wheres[] = '`a`.`folderid`='.$params['folderid'];
        }
        $sql = 'SELECT COUNT(1) AS `c`
                FROM `ebh_roomcourses` `a` JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid`
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret['c'])) {
            return 0;
        }
        return $ret['c'];
    }

    /**
     * 根据cwid获取公告
     * @param $cwid
     * @return string
     */
    public function getNotice($cwid){
        if(empty($cwid)){
            return '';
        }

        $redis = Ebh()->cache->getRedis();
        $notice = $redis->hget('coursenotice_'.$cwid,'notice');
        if($notice){
            return $notice;
        }
        $sql = 'select notice from `ebh_coursewares` where cwid='.$cwid;
        $row = Ebh()->db->query($sql)->row_array();
        if($row){
            return $row['notice'];
        }else{
            return '';
        }
    }

    /*
	获取单课收费信息,ibuy用
	*/
    public function getcwpay($cwid){
        if(empty($cwid))
            return false;
        $sql = 'select c.cwid,c.title,rc.cprice,rc.cmonth,rc.cday,cr.crname,c.summary,cr.crid,f.folderid,c.logo,rc.roomfee,rc.comfee,c.cwurl,c.islive,cr.domain
				from ebh_coursewares c
				join ebh_roomcourses rc on c.cwid=rc.cwid
				join ebh_folders f on f.folderid = rc.folderid
				join ebh_classrooms cr on rc.crid=cr.crid';
        // $wherearr[] = 'rc.crid='.$crid;
        $wherearr[] = 'rc.cwid='.$cwid;
        $wherearr[] = 'f.power=0';
        $wherearr[] = 'f.del=0';
        $wherearr[] = 'rc.cwpay=1';
        $sql.= ' where '.implode(' AND ',$wherearr);
        return $this->db->query($sql)->row_array();

    }
    /**
     * @describe:课件->课件信息
     * @User:tzq
     * @Date:2017/12/12
     * @param $param
     * @param int $uid 用户id
     * @param string $cwids 课件id多个逗号隔开
     * @return
     */
    public function courseswareList($param)  {
        if (empty($param['cwids']) || empty($param['uid']))
            return FALSE;
        $field = array(
            '`logid`',
            //  '`uid`',
            '`cwid`',
            '`ctime`',
            '`curtime`',
            '`finished`',
            '`folderid`',
            '`ltime`',
            '`ip`',
            '`startdate`',
            '`lastdate`',
            ' COUNT(`logid`) `playcount`',
            ' SUM(`ltime`) `totalltime`',

        );
        //查询条件
        $where   = array();
        $where[] = '`crid` = ' . $param['crid'];
        $where[] = '`uid` = ' . $param['uid'];
        $where[] = '`cwid` IN ( ' . $param['cwids'] . ')';
        $where[] = '`totalflag` = 0';
        // $where[] = '`ltime` > 0';
        $sql = 'SELECT ' . implode(',', $field) . ' FROM ebh_playlogs ';
        $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' GROUP BY `cwid`';
        //log_message($sql);
        return $this->db->query($sql)->list_array();

    }

    /**
     * @describe:获取课程总时长
     * @User:tzq
     * @Date:2017/12/19
     * @param string $folderids 课程id多个用逗号隔开
     * @return array array('folderid1'=>array('folderid'=>1234,'cwlength'=>4567),
     *                     'folderid2'=>array('folderid'=>1234,'cwlength'=>4567)
     *                         )
     */
    public function getLengthByFolderid($folderids){
        // if(empty($folderids)){
        //     return FALSE;
        // }
        $where = array();
        $where[] = '`ro`.`folderid` IN('.$folderids.')';
        $where[] = '`co`.`status`=1';
        $sql = 'SELECT `ro`.`folderid`,SUM(`co`.`cwlength`) `cwlength` ';
        $sql .= 'FROM `ebh_roomcourses` `ro` ';
        $sql .= ' INNER JOIN `ebh_coursewares` `co` ON `ro`.`cwid`=`co`.`cwid` ';
        $sql .= ' WHERE '.implode(' AND ',$where);
        $sql .= ' GROUP BY `ro`.`folderid` ORDER BY NULL';
        return $this->db->query($sql)->list_array('folderid');
    }


    /**
     * @describe:获取课件列表
     * @User:tzq
     * @Date:2017/12/22
     * @param int $crid     网校id
     * @param int $uid      用户id
     * @param int $folderids  课程id
     * @return
     */
    public function getCourseList($crid,$uid,$folderids){
        if (empty($crid) || empty($uid) || empty($folderids)) {
            return FALSE;
        }
        $where   = array();
        $where[] = '`co`.`cwlength`>0';//统计学习时长大于0的减少查询记录
        $where[] = '`ro`.`folderid` IN(' . $folderids . ')';//
        $where[] = '`ro`.`crid`=' . $crid;
        $where[] = '`co`.`status` >= 0';//判断状态
        $sql = 'SELECT `ro`.`folderid`,`co`.`cwid` FROM `ebh_roomcourses` `ro` ';
        $sql .= 'JOIN `ebh_coursewares` `co` ON `ro`.`cwid`=`co`.`cwid` ';
        $sql .= ' WHERE ' . implode(' AND ', $where);
        return $this->db->query($sql)->list_array('cwid');//获取课件列表
    }

}