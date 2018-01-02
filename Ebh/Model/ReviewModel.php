<?php

/**
 * 评论
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/24
 * Time: 11:12
 */
class ReviewModel {
    /**
     * 插入评论数据
     * @param type $param
     * @return type
     */
    public function insert($param = array()) {
        $setarr = array();
        if (!empty($param['upid'])) {
            $setarr['upid'] = $param['upid'];
        }
        if (!empty($param['crid'])) {
            $setarr['crid'] = $param['crid'];
        }
        if (isset($param['service'])) {
            $setarr['service'] = $param['service'];
        }
        if (isset($param['environment'])) {
            $setarr['environment'] = $param['environment'];
        }
        if (isset($param['score'])) {
            $setarr['score'] = $param['score'];
        }
        if (isset($param['useful'])) {
            $setarr['useful'] = $param['useful'];
        }
        if (isset($param['useless'])) {
            $setarr['useless'] = $param['useless'];
        }
        if (isset($param['viewnum'])) {
            $setarr['viewnum'] = $param['viewnum'];
        }
        if (isset($param['replynum'])) {
            $setarr['replynum'] = $param['replynum'];
        }
        if (!empty($param['subject'])) {
            $setarr['subject'] = $param['subject'];
        }
        if (isset($param['good'])) {
            $setarr['good'] = $param['good'];
        }
        if (isset($param['bad']) ) {
            $setarr['bad'] = $param['bad'];
        }
        if (!empty($param['type'])) {
            $setarr['type'] = $param['type'];
        }
        if (!empty($param['uid'])) {
            $setarr['uid'] = $param['uid'];
        }
        if (!empty($param['levels'])) {
            $setarr['levels'] = $param['levels'];
        }
        if (!empty($param['toid'])) {
            $setarr['toid'] = $param['toid'];
        }
        if (!empty($param['opid'])) {
            $setarr['opid'] = $param['opid'];
        }
        if (!empty($param['fromip'])) {
            $setarr['fromip'] = $param['fromip'];
        }
        if (!empty($param['dateline'])) {
            $setarr['dateline'] = $param['dateline'];
        }
        $logid = Ebh()->db->insert('ebh_reviews', $setarr);
        return $logid;
    }
    /**
     * 统计被评论的课件数
     * @param $crid
     * @filters 过滤条件
     * @return int
     */
    public function getCourseReviewCount($crid = null, $filters = null,$bylist = FALSE) {
        /*$whereArr = array(
            //'`a`.`type`=\'courseware\'',
            '`b`.`crid`='.intval($crid),
            '`c`.`status`=1'
        );*/
        if($crid){
            $whereArr = array('`b`.`crid`='.intval($crid),'`c`.`status`=1');
        }else{
            $whereArr = array('`c`.`status`=1');
        }
        if (isset($filters['folderid'])) {
			if(!$bylist){
				$whereArr[] = '`b`.`folderid`='.intval($filters['folderid']);
			} else {
				$whereArr[] = '`b`.`folderid` in ('.$filters['folderid'].')';
			}
        }
        if (isset($filters['early'])) {
            $whereArr[] = '`a`.`dateline`>='.intval($filters['early']);
        }
        if (isset($filters['latest'])) {
            $whereArr[] = '`a`.`dateline`<='.intval($filters['latest']);
        }
        if (isset($filters['shield'])) {
            $whereArr[] = '`a`.`shield`='.intval($filters['shield']);
        }
        if (isset($filters['audit'])) {
            $whereArr[] = '`a`.`audit`='.intval($filters['audit']);
        }
        $sql = 'SELECT COUNT(`a`.`toid`) AS `c` ,folderid,`a`.`audit` FROM `ebh_reviews` `a` LEFT JOIN `ebh_roomcourses` `b` ON `a`.`toid`=`b`.`cwid` left join ebh_coursewares c on `c`.`cwid`=`b`.`cwid`'.
            ' WHERE '.implode(' AND ', $whereArr);
        
		if(!$bylist){
			$ret = Ebh()->db->query($sql)->row_array();
			if (!empty($ret)) {
				return $ret['c'];
			}
			return 0;
		} else {
			$sql .= ' group by folderid';
			$ret = Ebh()->db->query($sql)->list_array('folderid');
			if(!empty($ret)){
				return $ret;
			}
			return array();
		}
    }

    /**
     * 课件评论列表
     * @param $crid 网校ID
     * @param $filters 过滤条件
     * @param int $limits 分页条件
     * @param bool $setKey 是否设置键
     * @return mixed
     */
    public function getCourseReviewList($crid = null, $filters = null, $limits = 20, $setKey = false) {
        if($crid){
            $whereArr = array(
                //'`a`.`type`=\'courseware\'',
                '`b`.`crid`='.intval($crid),
                '`c`.`status`=1'
                //'`a`.`logid`=(SELECT MAX(`logid`) FROM `ebh_reviews` WHERE `toid`=`a`.`toid` AND `type`=\'courseware\')'
            );
        }else{
            $whereArr = array(
                '`c`.`status`=1'
            );
        }
        
        if (isset($filters['folderid'])) {
            $whereArr[] = '`b`.`folderid`='.intval($filters['folderid']);
        }
        if (isset($filters['early'])) {
            $whereArr[] = '`a`.`dateline`>='.intval($filters['early']);
        }
        if (isset($filters['latest'])) {
            $whereArr[] = '`a`.`dateline`<='.intval($filters['latest']);
        }
        if (isset($filters['shield'])) {
            $whereArr[] = '`a`.`shield`='.intval($filters['shield']);
        }
        if (isset($filters['audit'])) {
            $whereArr[] = '`a`.`audit`='.intval($filters['audit']);
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
        $sql = 'SELECT `a`.`logid`,`a`.`audit`,`a`.`toid`,`a`.`subject`,`a`.`uid`,`a`.`dateline`,`a`.`shield`,`b`.`folderid`'.
            ' FROM `ebh_reviews` `a` LEFT JOIN `ebh_roomcourses` `b` ON `a`.`toid`=`b`.`cwid` left join ebh_coursewares c on c.cwid=b.cwid'.
            ' WHERE '.implode(' AND ', $whereArr).
            ' ORDER BY `a`.`logid` DESC LIMIT '.$offset.','.$pagesize;

        if ($setKey) {
            return Ebh()->db->query($sql)->list_array('logid');
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /*
    多个课程评论数
    @param $param crid,folderid,starttime,endtime
    @return array
    */
    public function getReviewCountByFolderid($param){
        if(empty($param['crid']) || empty($param['folderid'])){
            return FALSE;
        }
        $sql = 'select count(*) count ,rc.folderid 
				from ebh_reviews r join ebh_roomcourses rc on rc.cwid=r.toid';
        $wherearr = array('r.type=\'courseware\'');
        $wherearr[]= 'rc.crid='.$param['crid'];
        $wherearr[]= 'rc.folderid in ('.$param['folderid'].')';
        $wherearr[]= 'r.shield=0';
        if(!empty($param['starttime'])){
            $wherearr[]= 'r.dateline>='.$param['starttime'];
        }
        if(!empty($param['endtime'])){
            $wherearr[]= 'r.dateline<='.$param['endtime'];
        }
        $sql.= ' where '.implode(' AND ',$wherearr);
        $sql.= ' group by rc.folderid';
        return Ebh()->db->query($sql)->list_array('folderid');
    }

    /**
     * 设置问题屏蔽状态
     * @param $logid 评论ID
     * @param $shield 屏蔽状态
     * @return mixed
     */
    public function setShield($logid, $shield) {
        $whereStr = '`logid`='.intval($logid);//.' AND `type`=\'courseware\''
        return Ebh()->db->update('ebh_reviews', array('shield' => intval($shield)), $whereStr);
    }

    public function setAudit($logid, $audit) {
        $whereStr = '`logid`='.intval($logid);
        return Ebh()->db->update('ebh_reviews', array('audit' => intval($audit)), $whereStr);
    }

    /**
     * [getSchsource 获取来源网校列表]
     * @param  [int] $crid   [本网校ID]
     * @return [array]       
     */
    public function getSchsource($crid){
        $sql = 'select name,sourcecrid from ebh_schsources where crid='.$crid;
        return Ebh()->db->query($sql)->list_array();
    }


    public function getName($crid,$sourcecrid){
        $sql = "select name from ebh_schsources where crid=$crid and sourcecrid=$sourcecrid";
        return Ebh()->db->query($sql)->row_array();
    }





    /**
     * 根据课件编号递归获取评论列表以及评论回复内容
     * @param array $queryarr
     * @param int $parent_id
     */
    public function getReviewListByCwidOnRecUrsion($queryarr = array(),$parent_id = 0,&$result = array()){

        $wherearr = array();
        // audit值：0 为待审核状态，1 为审核通过，2 为审核不通过
        $where = '';

        if (!empty($queryarr['audit'])) {
            $where = ' and audit = 1 ';
        }

        if($parent_id == 0){
            if (!empty($queryarr['cwid'])){
                $wherearr[]  = ' r.toid=' . $queryarr['cwid'];
            }
            if (!empty($queryarr['uid'])){
                $wherearr[]  = ' r.uid=' . $queryarr['uid'];
            }
            $where = ' and '.implode(' and ',$wherearr).$where;

            $sql = 'select r.logid,r.dateline,r.subject,r.score,r.uid,r.toid,r.type,u.uid,u.username,u.realname,u.sex,u.face,u.groupid as groupid,r.replyuid,r.replysubject,r.replydateline,r.fromip from ebh_reviews r ' .
                'join ebh_users u on (u.uid = r.uid) ' .
                'where r.type=\'courseware\' and r.shield=0 and r.upid=' . $parent_id .$where.' order by r.logid desc ';
            if(isset($queryarr['limit'])){
                $sql .=  $queryarr['limit'];
            }
        }else{
            $sql = 'select r.logid,r.dateline,r.subject,r.score,r.uid,r.toid,r.type,u.uid,u.username,u.realname,u.sex,u.face,u.groupid,r.replyuid,r.replysubject,r.replydateline,u2.realname as torealname,u2.username as tousername,r.fromip from ebh_reviews r ' .
                'join ebh_users as u on (u.uid = r.uid) ' .
                'join ebh_users u2 on (u2.uid = r.toid) ' .
                'where r.shield=0 and r.upid=' . $parent_id .$where.' order by r.logid asc';
        }

        $arr = Ebh()->db->query($sql)->list_array();

        if(empty($arr)){
            return array();
        }
        foreach ($arr as $review) {
            $thisArr=&$result[];
            $review['children'] = $this->getReviewListByCwidOnRecUrsion($queryarr,$review['logid'],$thisArr);
            $thisArr = $review;
        }
        return $result;
    }

    /**
     * 获取评论条数
     * @param array $queryarr
     * @return int
     */
    public function getReviewCount($queryarr = array()){
        $count = 0;
        if (!empty($queryarr['cwid'])){
            $wherearr[]  = ' r.toid=' . $queryarr['cwid'];
        }
        if (!empty($queryarr['uid'])){
            $wherearr[]  = ' r.uid=' . $queryarr['uid'];
        }
        if (!empty($queryarr['audit'])) {
            $wherearr[] = ' audit = 1 ';
        }
        $where =  ' and ' .implode(' and ',$wherearr);
        $sql = 'SELECT count(*) count from ebh_reviews r join ebh_users u on (u.uid = r.uid) ' .
            'where shield = 0 and r.type=\'courseware\''.$where;

        $countrow = Ebh()->db->query($sql)->row_array();
        if (!empty($countrow))
            $count = $countrow['count'];
        return $count;
    }

    /**
     * 根据课件编号获取评论列表记录数(评论数不包括账号已删除的评论)
     * @param type $queryarr
     * @return type
     */
    public function getReviewCountByCwid($queryarr = array()) {
        $count = 0;

        // audit值：0 为待审核状态，1 为审核通过，2 为审核不通过
        $where = '';
        if (!empty($queryarr['audit'])) {
            $where = ' and audit = 1 ';
        }

        $sql = 'SELECT count(*) count from ebh_reviews r join ebh_users u on (u.uid = r.uid) ' .
            'where shield = 0 and r.type=\'courseware\''.$where;
        $countrow = Ebh()->db->query($sql)->row_array();
        if (!empty($countrow))
            $count = $countrow['count'];
        return $count;
    }
    /**
     * 获取指定用户指定网校发表评论的总数量(包含视频和非视频课件)
     * @param $crid 网校ID
     * @param $uid 用户ID
     * @return array
     */
    public function getAllReviewCount($param = array()) {
        $sql = 'SELECT COUNT(*) count FROM `ebh_reviews` re';
        $params = array();
        if(!empty($param['crid'])){
            $params[] = 're.crid = '.$param['crid'];
        }
        if(!empty($param['uid'])){
            $params[] = 're.uid = '.$param['uid'];
        }
        $params[] = 're.type=\'courseware\'';
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
        }
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 根据ID获取评论
     * @param $logid
     * @return mixed
     */
    public function getReviewByLogid($logid){
        $sql = 'select * from ebh_reviews where logid = '.$logid;

        return Ebh()->db->query($sql)->row_array();
    }
    /*
	删除评论
	@param int $logid
	@return bool
	*/
    public function deletereview($param = array()){
        return Ebh()->db->delete('ebh_reviews',$param);
        // $sql = 'delete r.* from ebh_reviews r where r.logid='.$logid;
        // return $this->db->simple_query($sql);
    }

    /**
     * 根据logid获取评论和课程,课件详情
     * @param $logid
     * @return mixed
     */
    public function getDetailByLogid($logid,$crid){
        if(empty($logid) || !is_numeric($logid) || empty($crid) || !is_numeric($crid)){
            return false;
        }
        $sql = 'SELECT re.logid,re.dateline,re.subject,re.uid,re.toid,re.type,re.fromip,u.uid,u.username,u.realname,u.sex,u.groupid,c.title,f.folderid,f.foldername FROM ebh_reviews re'.
                ' LEFT JOIN ebh_users u ON (u.uid = re.uid)'.
                ' LEFT JOIN ebh_roomcourses rc ON (rc.cwid=re.toid)'.
                ' LEFT JOIN ebh_coursewares c ON (c.cwid=re.toid)'.
                ' LEFT JOIN ebh_folders f ON (f.folderid=rc.folderid)'.
                ' WHERE re.type = \'courseware\' AND re.upid = 0 AND re.logid ='.intval($logid).' AND rc.crid='.intval($crid);
        return Ebh()->db->query($sql)->row_array();
    }
}