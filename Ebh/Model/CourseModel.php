<?php
/**
 * 课程
 */
class CourseModel{
    /**
     * 课程详情
     * @param int $itemid 服务项ID
     * @return mixed
     */
	public function detail($itemid){
	    $wheres = array(
	        '`a`.`status`=0',
            '`b`.`del`=0',
            '`b`.`folderlevel`=2',
            '`b`.`power`=0'
        );
        $sql = 'SELECT `a`.`itemid`,`a`.`folderid`,`b`.`foldername`,`b`.`img` FROM `ebh_pay_items` `a` JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid` WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->row_array();
	}

    /**
     * 企业选课课程列表
     * @param int $crid 网校ID
     * @param int $sourcecrid 来源网校ID
     * @param int $pid 筛选pid
     * @param null $sid 筛选sid
     * @param string $search 筛选课程名称关键字
     * @param null $limit 限量条件
     * @return array
     */
    public function getSchCourse($crid, $sourcecrid, $pid = 0, $sid = null, $search = '', $limit = null) {
	    $wheres = array(
	        '`a`.`crid`='.$crid,
            '`a`.`sourcecrid`='.$sourcecrid,
            '`a`.`del`=0',
            '`b`.`status`=0',
            '`d`.`del`=0',
            '`d`.`power`=0',
            '`d`.`folderlevel`=2',
            '`e`.`status`=1',
            'IFNULL(`f`.`ishide`,0)=0'
        );
	    $rank = '`grank`';
	    if ($pid > 0) {
	        $wheres[] = '`b`.`pid`='.$pid;
            $rank = '`prank`';
            if ($sid !== null) {
                $wheres[] = '`b`.`sid`='.$sid;
                $rank = '`srank`';
            }
        }

        if ($search != '') {
	        $wheres[] = '`d`.`foldername` LIKE '.Ebh()->db->escape('%'.$search.'%');
        }
        $offset = $top = 0;
        if ($limit !== null) {
	        if (is_array($limit)) {
                $page = isset($limit['page']) ? intval($limit['page']) : 1;
                $page = max($page, 1);
                $pagesize = isset($limit['pagesize']) ? intval($limit['pagesize']) : 1;
                $top = $pagesize = max($pagesize, 1);
                $offset = ($page - 1) * $pagesize;
            } else {
	            $top = max(intval($limit), 1);
            }
        }
        $sql = 'SELECT `b`.`itemid`,`b`.`iname`,`b`.`pid`,`b`.`sid`,`a`.`price`,`d`.`folderid`,`d`.`foldername`,`d`.`img`,IFNULL(`g`.`grank`, 0) AS `grank`,IFNULL(`g`.`prank`, 0) AS `prank`,IFNULL(`g`.`srank`,0) AS `srank` 
                FROM `ebh_schsourceitems` `a` JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`itemid` AND `b`.`crid`=`a`.`sourcecrid` 
                JOIN `ebh_classrooms` `c` ON `c`.`crid`=`a`.`sourcecrid` 
                JOIN `ebh_folders` `d` ON `d`.`folderid`=`b`.`folderid` 
                JOIN `ebh_pay_packages` `e` ON `e`.`pid`=`b`.`pid` 
                LEFT JOIN `ebh_pay_sorts` `f` ON `f`.`sid`=`b`.`sid` 
                LEFT JOIN `ebh_courseranks` `g` ON `g`.`folderid`=`b`.`itemid` AND `g`.`crid`=`b`.`crid`
                WHERE '.implode(' AND ', $wheres).' ORDER BY '.$rank.' ASC,`b`.`itemid` DESC';
        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
	    $ret = Ebh()->db->query($sql)->list_array();
	    if (empty($ret)) {
	        return array();
        }
        return $ret;
    }

    /**
     * 企业选课课程数
     * @param int $crid 网校ID
     * @param int $sourcecrid 来源网校ID
     * @param int $pid 筛选pid
     * @param null $sid 筛选sid
     * @param string $search 筛选课程名称关键字
     * @return array
     */
    public function getSchCourseCount($crid, $sourcecrid, $pid = 0, $sid = null, $search = '') {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`sourcecrid`='.$sourcecrid,
            '`a`.`del`=0',
            '`b`.`status`=0',
            '`d`.`del`=0',
            '`d`.`power`=0',
            '`d`.`folderlevel`=2',
            '`e`.`status`=1',
            'IFNULL(`f`.`ishide`,0)=0'
        );
        if ($pid > 0) {
            $wheres[] = '`b`.`pid`='.$pid;
            if ($sid !== null) {
                $wheres[] = '`b`.`sid`='.$sid;
            }
        }

        if ($search != '') {
            $wheres[] = '`d`.`foldername` LIKE '.Ebh()->db->escape('%'.$search.'%');
        }
        $sql = 'SELECT COUNT(1) AS `c` 
                FROM `ebh_schsourceitems` `a` JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`itemid` AND `b`.`crid`=`a`.`sourcecrid` 
                JOIN `ebh_classrooms` `c` ON `c`.`crid`=`a`.`sourcecrid` 
                JOIN `ebh_folders` `d` ON `d`.`folderid`=`b`.`folderid` 
                JOIN `ebh_pay_packages` `e` ON `e`.`pid`=`b`.`pid` 
                LEFT JOIN `ebh_pay_sorts` `f` ON `f`.`sid`=`b`.`sid` 
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return 0;
        }
        return intval($ret['c']);
    }

    /***
     * 获取学习进度和课程比例
     * @param int $uid 用户uid
     * @return array
     */
    public function getProgressAndRate($uid,$crid){
        //获取所有的课程id
        $sql  = 'SELECT folderid From ebh_userpermisions WHERE uid='.$uid.' AND crid='.$crid;
        $folderidArr = Ebh()->db->query($sql)->list_array();
        $rate = array('video'=>0,'broadcast'=>0,'ppt'=>0,'word'=>0,'other'=>0);
        if(empty($folderidArr)){
            return array('progress'=>0,'rate'=>$rate);
        }

        $folderid = '';
        foreach ($folderidArr as $v){
            $folderid .= $v['folderid'].',';
        }
        $folderid = substr($folderid,0,-1);
        $sql  = 'SELECT COUNT(*) AS c ,SUM(if(finished=1,1,0)) AS su FROM ebh_playlogs WHERE uid='.$uid.' AND crid='.$crid;
        $count = Ebh()->db->query($sql)->row_array();//获取统计
        if(!empty($count)){
            if($count['c'] == 0 || $count['su'] == 0){
                $progress = 0;
            }else{
                $progress = round($count['su']/$count['c'] *100);

            }
        }else{
            $progress = 0;
        }
        //获取用户的所有课程r
        $sql = 'SELECT c.cwurl,c.islive FROM ebh_roomcourses r';
        $sql .= ' LEFT JOIN ebh_coursewares c ON r.cwid=c.cwid WHERE  r.folderid in('.$folderid.') AND c.status<>-3';
        $list = Ebh()->db->query($sql)->list_array();

        $rate = array('video'=>0,'broadcast'=>0,'ppt'=>0,'word'=>0,'other'=>0);
        if(!empty($list)){
            foreach ($list as $value){
                if($value['islive'] == 1){
                    $rate['broadcast'] ++;
                }else{
                    if(empty($value['cwurl'])){
                        $rate['other']++;
                    }else{
                        $postfix = explode('.',$value['cwurl']);
                        $postfix = is_array($postfix)?end($postfix):$postfix;
                        $postfix = strtolower($postfix);
                        $videoArr = array('wma','mp4','ebhp','flv','mp3','avi','mpg','mpge','rmb','swf');
                        $wordArr  = array('doc','docx','rtf');
                        $pptArr   = array('pps','ppt','ppts','pdf');
                        if(in_array($postfix,$videoArr)){
                            $rate['video']++;
                        }elseif (in_array($postfix,$wordArr)){
                            $rate['word']++;
                        }elseif (in_array($postfix,$pptArr)){
                            $rate['ppt']++;
                        }else{
                            $rate['other']++;
                        }
                    }
                }
            }
        }

        return array('progress'=>$progress,'rate'=>$rate);
    }


}