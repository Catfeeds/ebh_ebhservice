<?php
/**
 *学分获取模型
 */
class StudycreditlogsModel{
    private $db;
    function __construct(){
        $this->db = Ebh()->db;
    }
    //获取指定用户学分列表（包括文章、课件和评论）
    public function getUserScoreList($param = array()){
        if (empty($param['uid']) || empty($param['crid'])){
            return false;
        }
        $sql = 'SELECT scl.logid,scl.score,scl.dateline,scl.type,cw.title,ne.subject,re.subject AS reviews FROM ebh_studycreditlogs scl LEFT JOIN ebh_coursewares cw ON scl.cwid = cw.cwid LEFT JOIN ebh_news ne ON scl.articleid = ne.itemid LEFT JOIN ebh_reviews re ON scl.reviewid = re.logid ';
        $whereArr = array();
        $whereArr[] = 'scl.uid = '.intval($param['uid']);
        $whereArr[] = 'scl.crid = '.intval($param['crid']);
        if(!empty($param['del'])){
            $whereArr[] = 'scl.del = '.intval($param['del']);
        }else{
            $whereArr[] = 'scl.del = 0';
        }
        if(!empty($whereArr)){
            $sql.=' WHERE '.implode(' AND ', $whereArr);
        }
        if(!empty($param['order'])){
            $sql.=' ORDER BY '.$this->db->escape_str($param['order']);
        }else{
            $sql.=' ORDER BY scl.dateline DESC';
        }
        if(!empty($param['limit'])) {
            $sql .= ' LIMIT '.$this->db->escape_str($param['limit']);
        } else {
            if (empty($param['page']) || $param['page'] < 1)
                $page = 1;
            else
                $page = intval($param['page']);
            $pagesize = empty($param['pagesize']) ? 10 : intval($param['pagesize']);
            $start = ($page - 1) * $pagesize;
            $sql .= ' LIMIT ' . $start . ',' . $pagesize;
        }
        return $this->db->query($sql)->list_array();
    }
	
    //获取指定用户总学分和学分列表总数
    public function getUserSum($param = array()){
        if ((empty($param['uid']) && empty($param['uids'])) || empty($param['crid'])){
            return false;
        }
        $sql = 'SELECT count(1) count,sum(scl.score) scores,scl.uid FROM ebh_studycreditlogs scl LEFT JOIN ebh_coursewares cw ON scl.cwid = cw.cwid LEFT JOIN ebh_news ne ON scl.articleid = ne.itemid LEFT JOIN ebh_reviews re ON scl.reviewid = re.logid ';
        $whereArr = array();
        if(!empty($param['uid'])){
            $whereArr[] = 'scl.uid = '.intval($param['uid']);
        }
		if(!empty($param['uids'])){
			$whereArr[] = 'scl.uid in ('.$this->db->escape_str($param['uids']).')';
		}
        $whereArr[] = 'scl.crid = '.intval($param['crid']);
        if(isset($param['type'])){
            $whereArr[] = 'scl.type = '.intval($param['type']);
        }
		if(!empty($param['folderid'])){
            $whereArr[] = 'scl.folderid = '.intval($param['folderid']);
        }
        if(!empty($param['del'])){
            $whereArr[] = 'scl.del = '.intval($param['del']);
        }else{
            $whereArr[] = 'scl.del = 0';
        }
		if(!empty($param['exceptlogid'])){//不获取特定logid
			$whereArr[] = 'scl.logid<>'.$param['exceptlogid'];
		}
        if(!empty($whereArr)){
            $sql.=' WHERE '.implode(' AND ', $whereArr);
        }
		if(!empty($param['uids'])){
			$sql.= ' group by uid';
			return $this->db->query($sql)->list_array('uid');
		} else {
			return $this->db->query($sql)->row_array();
		}
    }

    /**
     * 插入一条学分记录
     * @param $param
     * @return bool
     */
    public function addOneScore($param) {
        if (empty($param['crid']) || empty($param['uid']))
            return false;
        $setarr = array();
        $setarr['crid'] = intval($param['crid']);
        $setarr['uid'] = intval($param['uid']);
        if (!empty($param ['dateline'])) { //记录添加时间
            $setarr ['dateline'] = intval($param ['dateline']);
        } else {
            $setarr ['dateline'] = SYSTIME;
        }
        if (!empty($param ['folderid'])) {   //课程编号
            $setarr ['folderid'] = intval($param ['folderid']);
        }
        if (!empty($param ['type'])) {
            $setarr ['type'] = intval($param ['type']);
        }
        if (isset($param ['eid'])) { //作业编号
            $setarr ['eid'] = intval($param['eid']);
        }
        if (isset($param ['reviewid'])) {
            $setarr ['reviewid'] = intval($param['reviewid']);
        }
        if (isset($param ['cwid'])) {
            $setarr ['cwid'] = intval($param ['cwid']);
        }
        if (isset($param ['score'])) {
            $setarr ['score'] = floatval($param ['score']);
        }
        if (!empty($param ['fromip'])) {
            $setarr ['fromip'] = intval($param ['fromip']);
        }
        if (isset($param ['articleid'])) {
            $setarr ['articleid'] = intval($param ['articleid']);
        }
        if (isset($param ['del'])) {
            $setarr ['del'] = intval($param ['del']);
        }

        $afrows = Ebh()->db->insert('ebh_studycreditlogs',$setarr);
		//清除学生学分缓存
		$uslib = new UserStudyInfo();
		$uslib->clearCache($setarr['crid'],$setarr['uid']);
        return $afrows;
    }

    /**
    *删除单个评论或原创文章后所得学分记录也同时删除
    *@param int $logid
    *@return bool
    */
    public function deleteScore($param = array()){
        $result = false;
        if((empty($param['reviewid']) && empty($param['articleid'])) && empty($param['type']) && empty($param['uid'])){
            return false;
        }
        $uid = intval($param['uid']);
        if(($param['type'] == 3) && !empty($param['reviewid'])){
            $reviewid = intval($param['reviewid']);
            $result = $this->db->update('ebh_studycreditlogs',array('del'=>1),array('uid'=>$uid,'type'=>3,'del'=>0,'reviewid'=>$reviewid));
        }elseif(($param['type'] == 2) && !empty($param['articleid'])){
            $articleid = intval($param['articleid']);
            $result = $this->db->update('ebh_studycreditlogs',array('del'=>1),array('uid'=>$uid,'type'=>2,'del'=>0,'articleid'=>$articleid));
        }
		//清除网校学生学分缓存
		$uslib = new UserStudyInfo();
		$uslib->clearCache($param['crid']);
        return $result;
    }

	/*
	用户课程学分
	*/
	public function getFolderScore($param){
		if(empty($param['uid']) || empty($param['crid']) || empty($param['folderids'])){
			return false;
		}
        $wherearr = array();
		$sql = 'select sum(score) sumscore,folderid from ebh_studycreditlogs';
		$wherearr[] = 'uid='.intval($param['uid']);
		$wherearr[] = 'crid='.intval($param['crid']);
        $whereArr[] = 'del = 0';
		$wherearr[] = 'folderid in('.$this->db->escape_str($param['folderids']).')';
		$sql.= ' where '.implode(' AND ',$wherearr);
		$sql.= ' group by folderid';
		return $this->db->query($sql)->list_array('folderid');
	}
    /**
    *获取用户学习单个非视频课件或原创文章的学分
    */
        public function getScoreById($param){
            $return = false;
            $wherearr = array();
            if(empty($param['uid']) || empty($param['crid']) || empty($param['type'])){
                return $return;
            }
            $sql = 'select count(1) count from ebh_studycreditlogs';
            $wherearr[] = 'uid='.intval($param['uid']);
            $wherearr[] = 'crid='.intval($param['crid']);
            $whereArr[] = 'del = 0';
            $wherearr[] = 'type='.intval($param['type']);
            if($param['type'] == 4){
                if(empty($param['cwid'])){
                    return $return;
                }else{
                    $wherearr[] = 'cwid='.intval($param['cwid']);
                }
            }elseif($param['type'] == 5){
                if(empty($param['articleid'])){
                    return $return;
                }else{
                    $wherearr[] = 'articleid='.intval($param['articleid']);
                }
            }else{
                return $return;
            }

            $sql.= ' where '.implode(' AND ',$wherearr);
            $ret = $this->db->query($sql)->row_array();
            if(!empty($ret['count'])){
                $return = true;
            }
            return $return;
        }
	/**
     *修改评论学分的字数限制时,同步用户评论学分记录
     *先清空,再同步。(先清空关于该评论的学分记录,然后根据系统评论学分设置(评论超a字,每次b分)同步学分,
     *每个用户从reviews插入N条记录到studycreditlogs)
     */
    public function doReviewScoreSync($param){
        set_time_limit(0);
        //初始化变量
        $result = false;
        $ret = array();         //学分设置参数
        $logpage = 1;           //课件页数
        $logsize = 100;          //每次处理课件数
        $folderids = '12867,12868,12869';//线上国土课程包12867,12868,12869//测试12871,12872,12873,开发新国土课程包30338,30335,30326
        $cwidlist = array();   //课件id列表集合
        $cwids = '';            //课件id集合（字符串）
        $reviews = array();    //符合条件的评论logid集合
        $reviewnumsql = '';     //评论数量和logid查询语句
        $logidlist = array();
        $reviewsql = '';    //评论字段内容查询语句
        $logids = '';       //转成字符串的logid集合
        $str = '';          //转成字符串的待导入学分表数据
        $insertsql = '';    //导入学分表的sql语句
        $getscorenum = 0;   //得分的评论数量总计
		$uslib = new UserStudyInfo();
        $crid = !empty($param['crid']) ? intval($param['crid']) : 0;
        $uid = !empty($param['uid']) ? intval($param['uid']) : 0;
        if(empty($crid) || empty($uid)){
            return false;
        }

        //1、获取网校其他学分设置信息
        $ret = $this->getsystemsetting(array('crid'=>$crid,'type'=>3));
        if(empty($ret['single']) || empty($ret['needwords'])){
            return false;
        }
        $single = floatval($ret['single']);       //单个评论获取学分值
        $needwords = intval($ret['needwords']);   //评论字数限制

        //2、先清空当前网校关于评论的学分记录
        $updateres = $this->db->update('ebh_studycreditlogs',array('del'=>3),array('crid'=>$crid,'type'=>3,'del'=>0));//评论同步时将del字段置为3，若同步失败改回0
        if ($updateres === false) {
            return false;
        }

        //3、查询当前网校课件数
        $cwidsql = 'SELECT cwid from ebh_roomcourses where folderid in('.$folderids.')';
        $cwidlist = $this->db->query($cwidsql)->list_array();
        $cwidlist = array_column($cwidlist, 'cwid');
        $cwidcount = count($cwidlist);

        //4、分页查询当前网校所有符合条件的评论logid集合（每个课件id只取一条评论）
        if(!empty($cwidcount) || ($cwidcount>0)){
            $logtotalpage = ceil($cwidcount/$logsize);//分页总数
            while ($logpage <= $logtotalpage ) {
                //按照分段数去取cwid集合
                $begin = ($logpage - 1) * $logsize;
                $cwids = implode(',', array_slice($cwidlist, $begin, $logsize)); //转字符串

                $reviewnumsql = 'SELECT ra.logid FROM `ebh_reviews` ra WHERE ra.crid=' . $crid . ' AND ra.toid IN ('.$cwids.') AND ra.type=\'courseware\' AND CHAR_LENGTH(ra.`subject`)>' . $needwords;
                $reviewnumsql .= ' GROUP BY ra.uid,ra.toid ORDER BY ra.logid ASC';
                $reviews = $this->db->query($reviewnumsql)->list_array();
                $cwids = '';//释放空间
                $reviewnumsql = ''; //评论数量和logid查询语句

        //5、查询当前网校有做过评论的用户的所有符合条件的评论列表（每个课件id只取一条评论）
                $page = 1;          //当前页（评论）
                $pagesize = 10000; //计入学分偏移量,每次添加学分数量
                $total = count($reviews);   //可获取学分的评论总数
                if (!empty($reviews)) {
                    $totalpage = ceil($total / $pagesize);//分页总数
                    while ($page <= $totalpage) {
                        $start = ($page - 1) * $pagesize;
                        $logids = implode(',', array_column(array_slice($reviews, $start, $pagesize), 'logid')); //转字符串
                        if (!empty($logids)) {
                            $reviewsql = 'SELECT ra.uid,ra.crid,ra.toid,ra.logid,ra.dateline,' . $single . ' as score,3 as type,ra.fromip FROM `ebh_reviews` ra WHERE ra.logid IN (' . $logids . ')';
                            $logidlist = $this->db->query($reviewsql)->list_array();
                            $logids = '';//释放空间
                            $reviewsql = '';//释放评论字段内容查询语句
                            $getscorenum = $getscorenum+count($logidlist);
        //6、每个评论获取对应的学分,记录到学分记录表
                            if (!empty($logidlist) && is_array($logidlist)) {
                                $str = $this->arr_to_str($logidlist);//组装待插入学分表数据
                                $logidlist = array();//释放分页取到的评论列表集
                                if (!empty($str)) {
                                    $insertsql = 'INSERT INTO ebh_studycreditlogs (`uid`,`crid`,`cwid`,`reviewid`,`dateline`,`score`,`type`,`fromip`) VALUES ' . $str;
                                    $result = $this->db->query($insertsql);
                                    if ($result === false) {
										//清除网校学生学分缓存
										$uslib->clearCache($crid);
                                        return false;
                                    }
                                }
                            }
                            $str = '';//释放转成字符串的待导入学分表数据
                            $insertsql = '';
                        }
                        $page++;
                    }
                    $reviews = array();//释放空间
                }
                $logpage++;
            }
        }
        $cwids = '';
        $cwidlist = array();
        if($result === false){
            log_message('评论学分同步失败!同步'.$getscorenum.'条评论学分');
        }else{
            log_message($getscorenum.'条评论学分同步成功!');
        }
		//清除网校学生学分缓存
		$uslib->clearCache($crid);
        return $result;
    }

    /**非视频课件、原创文章学习、原创文章发表或评论课件学分同步
     *根据系统学分设置(学习时间a,每次b分)同步学分
     */
    public function doStudyScoreSync($param = array()){
        $crid = !empty($param['crid']) ? intval($param['crid']) : 0;
        $uid = !empty($param['uid']) ? intval($param['uid']) : 0;
        $type = !empty($param['type']) ? intval($param['type']) : 0;
        if(empty($uid) || empty($crid) || empty($type)){ return false; }
        $result = false;
        //1获取网校其他学分设置信息
        $ret = $this->getsystemsetting($param);
        $this->db->set_con(0);
        $this->db->begin_trans();
        if(!empty($ret) && !empty($ret['single'])){
            $result = $this->db->update('ebh_studycreditlogs',array('score'=>$ret['single']),array('crid'=>$crid,'type'=>$type,'del'=>0));
            //清除学生学分缓存
			$uslib = new UserStudyInfo();
			$uslib->clearCache($crid,$uid);
			if ($this->db->trans_status() === false) {
                $this->db->rollback_trans();
                $this->db->reset_con();
                return false;
            }
        }
        $this->db->commit_trans();
        $this->db->reset_con();
        return $result;
    }
    /**
     *查询用户是否评论过指定课件
     */
    public function getReviewScoreById($param){
        $return = false;
        $wherearr = array();
        if(empty($param['uid']) || empty($param['crid']) || empty($param['type']) || empty($param['cwid']) || empty($param['needwords'])){
            return $return;
        }
        $sql = 'select count(1) count from ebh_reviews';
        $wherearr[] = 'uid='.intval($param['uid']);
        $wherearr[] = 'crid='.intval($param['crid']);
        $wherearr[] = 'type=\'courseware\' AND CHAR_LENGTH(subject)>'.intval($param['needwords']);
        $wherearr[] = 'toid='.intval($param['cwid']);
        $sql.= ' where '.implode(' AND ',$wherearr);
        $ret = $this->db->query($sql)->row_array();
        if(!empty($ret['count']) && $ret['count']>1){
            $return = true;
        }
        return $return;
    }

    //二维数组转化为字符串，中间用()隔开
    private function arr_to_str($arr){
        $str = '';
        $temp = array();
        foreach ($arr as $value){
            $value['fromip'] = '\''.$value['fromip'].'\'';
            $temp[] = implode(",",$value);
        }
        $str .= implode('),(',$temp);
        $str='('.trim($str,',').')';
        return $str;
    }
    //获取其他学分设置
    private function getsystemsetting($param){
        $result = array();
        if(empty($param['crid']) || empty($param['type'])){
            return $result;
        }
        $crid = intval($param['crid']);
        $type = intval($param['type']);
        $redis_key = 'room_systemsetting_' . $crid;
        $creditrule_cache = Ebh()->cache->getRedis()->hMget($redis_key, array('creditrule'));
        if (!empty($creditrule_cache)) {//从缓存获取
            $ret = $creditrule_cache;
        } else {//从数据库获取
            $sql = 'SELECT `creditrule` FROM `ebh_systemsettings`  WHERE `crid`=' . $crid . ' LIMIT 1';
            $ret = $this->db->query($sql)->row_array();
        }
        //返回学分设置详细设置信息
        if (!empty($ret['creditrule'])) {
            $creditrule = json_decode($ret['creditrule'],true);
            $key = '';
            if($type == 2){
                $key = 'article';   //发表原创文章
            }elseif ($type == 3){
                $key = 'comment';   //发表评论
                if(empty($creditrule['comment']['needwords'])){ return $result; }
                $result['needwords'] = intval($creditrule['comment']['needwords']);
            }elseif($type == 4){
                $key = 'notvideo';  //非视频课件学习
                if(empty($creditrule['notvideo']['needtime'])){ return $result; }
                $result['needtime'] = intval($creditrule['notvideo']['needtime']);
            }elseif ($type == 5){
                $key = 'news';      //原创文章学习
                if(empty($creditrule['news']['readtime'])){ return $result; }
                $result['readtime'] = intval($creditrule['news']['readtime']);
            }else{
                return $result;
            }
            if(!empty($creditrule[$key]['on']) && ($creditrule[$key]['on']==1) && !empty($creditrule[$key]['single'])) {
                $result['single'] = floatval($creditrule[$key]['single']);      //单次获得学分
            }else{
                $result = array();
            }
        }
        return $result;
    }

    /**
     * @describe:更新folderid
     * @Author:tzq
     * @Date:2018/01/09
     * @param string $sql 要更新的sql语句
     * @return int
     */
    public function updateFolderid($folderList){
        $sql = 'UPDATE `ebh_studycreditlogs` SET `folderid`= CASE  ';
        foreach ($folderList as $folderid => $cwidArr) {
            $cwids = implode(',', $cwidArr);
            if (!empty($cwids)){
                $sql .= 'WHEN `cwid` IN(' . $cwids . ') THEN ' . ($folderid) . ' ';
                $isUpdate = true;
            }
        }
        $sql .= ' END WHERE `folderid`=0 AND `cwid`>0';
        if(isset($isUpdate)){
            //有需要更新
            if ($this->db->query($sql))
                return $this->db->affected_rows();
            else
                return false;
        }else{
            //没有需要更新的内容
            return 0;
        }


    }

    /**
     * @describe:获取单个或者多个用户的学分
     * @Author:tzq
     * @Date:2018/01/13
     * @param int $crid 网校id
     * @param int $beginTime 查询开始时间
     * @param int $endTime   查询结束时间
     * @param string $uids   用户uid单个或者多个
     * @return array
     */
    public function getCreditList($param){
        $where   = [];
        $where[] = '`crid`=' . $param['crid'];
        $where[] = '`del`=0';
        if (isset($param['beginTime']) && $param['beginTime'] > 0)
            $where[] = '(`dateline` >=' . $param['beginTime'] . ' AND `dateline`<=' . $param['endTime'] . ')'; //优先执行时间条件
        //学生人数大于1000个取出所有的进行筛选
        $uidArr = explode(',', $param['uids']);
        $count  = count($uidArr);
        if ($count <= 1000) {
            $where[] = '`uid` IN(' . $param['uids'] . ')';
        }
        $filed = [
            '`uid`',
            ' SUM(`score`) `score`',
        ];
        $sql   = 'SELECT ' . implode(',', $filed) . ' FROM `ebh_studycreditlogs`';
        $sql   .= ' WHERE ' . implode(' AND ', $where);
        $sql   .= ' GROUP BY `uid` ORDER BY NULL';
        $lists = $this->db->query($sql)->list_array('uid');
       // log_message('获取学分' . $sql);
        //结果数据中有多余的数据进行筛选
        if ($count < count($lists)) {
            //计算多余数据
            $unsetArr = array_diff(array_keys($lists), $uidArr);
            if (!empty($unsetArr)) {
                foreach ($unsetArr as $uid) {
                    unset($lists[$uid]);
                }
            }
        }
        return $lists;
    }
}