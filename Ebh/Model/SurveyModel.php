<?php
/**
 * 调查问卷model类 SurveyModel
 */
class SurveyModel{
    private $db;
    function __construct(){
        $this->db = Ebh()->db;
    }
    /**
     * 调查问卷列表
     */
	public function getSurveyList($param){
		$sql = 'select s.folderid, s.sid,s.cwid,s.title,s.type,s.dateline,s.ispublish,s.allowview,s.answernum,s.uid,s.startdate,s.enddate,c.title cwname from ebh_surveys s left join ebh_coursewares c on s.cwid=c.cwid';
		if(!empty($param['answered']) && !empty($param['uid']))
			$sql = 'select s.folderid, s.sid,s.cwid,s.title,s.type,s.dateline,s.ispublish,s.allowview,s.answernum,s.uid,s.startdate,s.enddate,c.title cwname,sa.aid from ebh_surveys s left join ebh_coursewares c on s.cwid=c.cwid left join ebh_surveyanswers sa on s.sid=sa.sid and sa.uid=' . intval($param['uid']);
		if(!empty($param['crid']))
			$wherearr[] = 'crid='.$param['crid'];
		if(isset($param['type']))
			$wherearr[] = 's.type='.$param['type'];
        if(!empty($param['untype'])){
            $wherearr[] = 's.type<>'.$param['untype'];
        }
		if(!empty($param['folderid']))
			$wherearr[] = 's.folderid='.$param['folderid'];
		if(isset($param['ispublish']))
			$wherearr[] = 'ispublish='.$param['ispublish'];
		if(!empty($param['teacherid']))
			$wherearr[] = 's.uid='.$param['teacherid'];
		if(!empty($param['filteruid']))
			$wherearr[] = 's.uid<>'.$param['filteruid'];
		if(!empty($param['isopening']))
			$wherearr[] = '(s.startdate<' . SYSTIME . ' AND (s.enddate>' . SYSTIME . ' OR s.enddate=0))';
        if(!empty($param['showlist'])) {
            if ($param['showlist'] == 1) {
                $wherearr[] = 's.startdate>' . SYSTIME;
            } elseif ($param['showlist'] == 2) {
                $wherearr[] = 's.startdate<=' . SYSTIME . ' AND (s.enddate>=' . SYSTIME .' OR s.enddate=0)';
            } elseif ($param['showlist'] == 3) {
                $wherearr[] = 's.enddate<' . SYSTIME .' AND s.enddate <>0';
            }
        }
		if(isset($param['q']))
			$wherearr[] = 's.title like \'%'.$param['q'].'%\'';
		$wherearr[] = 'isdelete=0';
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);
		if(!empty($param['order']))
			$sql.= ' order by '.$param['order'];
		else
			$sql.= ' order by sid desc';
		if(!empty($param['limit'])) {
			$sql .= ' limit '.$param['limit'];
		} else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
			$sql .= ' limit ' . $start . ',' . $pagesize;
        }
		return $this->db->query($sql)->list_array();
	}
    /**
     * 调查问卷列表按照进行中，未开始，已结束排序
     */
    public function getSurveyOrderList($param){
        $sql = 'select s.folderid, s.sid,s.cwid,s.title,s.type,s.dateline,s.ispublish,s.allowview,s.answernum,s.uid,s.startdate,s.enddate,c.title cwname, ';
        $sql .= 'CASE WHEN s.startdate <= '.SYSTIME.' AND (s.enddate >= '.SYSTIME .' OR s.enddate=0) THEN 1 WHEN s.startdate > '.SYSTIME.' THEN	2 WHEN s.enddate < '.SYSTIME .' AND s.enddate <>0 THEN 3 ELSE	\'ok\' END showlist ';
        $sql .='from ebh_surveys s left join ebh_coursewares c on s.cwid=c.cwid';
        if(!empty($param['answered']) && !empty($param['uid']))
            $sql = 'select s.folderid, s.sid,s.cwid,s.title,s.type,s.dateline,s.ispublish,s.allowview,s.answernum,s.uid,s.startdate,s.enddate,c.title cwname,sa.aid, ';
            $sql .= 'CASE WHEN s.startdate <= '.SYSTIME.' AND (s.enddate >= '.SYSTIME .' OR s.enddate=0) THEN 1 WHEN s.startdate > '.SYSTIME.' THEN	2 WHEN s.enddate < '.SYSTIME .' AND s.enddate <>0 THEN 3 ELSE	\'ok\' END showlist ';
            $sql .='from ebh_surveys s left join ebh_coursewares c on s.cwid=c.cwid left join ebh_surveyanswers sa on s.sid=sa.sid and sa.uid=' . intval($param['uid']);
        if(!empty($param['crid']))
            $wherearr[] = 'crid='.$param['crid'];
        if(isset($param['type']))
            $wherearr[] = 's.type='.$param['type'];
        if(!empty($param['untype'])){
            $wherearr[] = 's.type<>'.$param['untype'];
        }
        if(!empty($param['folderid']))
            $wherearr[] = 's.folderid='.$param['folderid'];
        if(isset($param['ispublish']))
            $wherearr[] = 'ispublish='.$param['ispublish'];
        if(!empty($param['teacherid']))
            $wherearr[] = 's.uid='.$param['teacherid'];
        if(!empty($param['filteruid']))
            $wherearr[] = 's.uid<>'.$param['filteruid'];
        if(!empty($param['isopening']))
            $wherearr[] = '(s.startdate<' . SYSTIME . ' AND (s.enddate>' . SYSTIME . ' OR s.enddate=0))';
        if(isset($param['q']))
            $wherearr[] = 's.title like \'%'.$param['q'].'%\'';
        $wherearr[] = 'isdelete=0';
        if(!empty($wherearr))
            $sql.= ' where '.implode(' AND ',$wherearr);
        if(!empty($param['order']))
            $sql.= ' order by showlist ASC,'.$param['order'];
        else
            $sql.= ' order by showlist ASC,sid DESC';
        if(!empty($param['limit'])) {
            $sql .= ' limit '.$param['limit'];
        } else {
            if (empty($param['page']) || $param['page'] < 1)
                $page = 1;
            else
                $page = $param['page'];
            $pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
            $start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        return $this->db->query($sql)->list_array();
    }
    /**
     * 调查问卷列表数量
     */
	public function getSurveyCount($param){
		$count = 0;
		$sql = 'select count(*) count from ebh_surveys s';
		if(!empty($param['crid']))
			$wherearr[] = 'crid='.$param['crid'];
		if(isset($param['type']))
			$wherearr[] = 's.type='.$param['type'];
		if(!empty($param['folderid']))
			$wherearr[] = 's.folderid='.$param['folderid'];
		if(isset($param['ispublish']))
			$wherearr[] = 'ispublish='.$param['ispublish'];
		if(!empty($param['teacherid']))
			$wherearr[] = 's.uid='.$param['teacherid'];
        if(!empty($param['untype'])){
            $wherearr[] = 's.type<>'.$param['untype'];
        }
		if(!empty($param['filteruid']))
			$wherearr[] = 's.uid<>'.$param['filteruid'];
		if(!empty($param['isopening']))
			$wherearr[] = '(s.startdate<' . SYSTIME . ' AND (s.enddate>' . SYSTIME . ' OR s.enddate=0))';
        if(!empty($param['showlist'])) {
            if ($param['showlist'] == 1) {
                $wherearr[] = 's.startdate>' . SYSTIME;
            } elseif ($param['showlist'] == 2) {
                $wherearr[] = 's.startdate<=' . SYSTIME . ' AND (s.enddate>=' . SYSTIME.' OR s.enddate=0)';
            } elseif ($param['showlist'] == 3) {
                $wherearr[] = 's.enddate<' . SYSTIME.' AND s.enddate <>0';
            }
        }
		if(isset($param['q']))
			$wherearr[] = 's.title like \'%'.$param['q'].'%\'';
		$wherearr[] = 'isdelete=0';
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);
		$row = $this->db->query($sql)->row_array();
		if(!empty($row))
			$count = $row['count'];
        return $count;
	}
    /**
    *问卷是否回答过
    */
    public function ifAnswered($param){
        $sql = 'select sid from ebh_surveyanswers where sid='.$param['sid'].' and uid='.$param['uid'];
        return $this->db->query($sql)->row_array();
    }
    /**
     * 获取一份调查问卷内容
     * @param  integer $sid  调查问卷编号
     * @param  integer $crid 学校编号
     * @return array       问卷内容
     */
    public function getOne($sid, $crid) {
        $survey = array();
        $sql = 'SELECT s.sid,s.crid,s.type,s.folderid,s.cwid,s.title,s.content,s.dateline,s.istemplate,s.ispublish,s.isdelete,s.allowview,s.answernum,s.allowanonymous,s.uid,s.cid,s.startdate,s.enddate,us.realname';
        $sql .=' FROM ebh_surveys s LEFT JOIN ebh_users us ON s.uid=us.uid WHERE s.sid=' . intval($sid) . ' AND s.crid=' . intval($crid);
        $survey = $this->db->query($sql)->row_array();
        if (!empty($survey))
        {
            $survey_question = $this->getQuestionList($survey['sid']);
        }
        if (!empty($survey_question))
        {
            foreach ($survey_question as $key => $value) {
                if ($value['type'] != 3) {
                    $sql_option = 'SELECT opid,qid,sid,content,displayorder,count FROM ebh_surveyoptions WHERE qid=' . $value['qid'] . ' ORDER BY displayorder,opid';
                    $survey_question[$key]['optionlist'] = $this->db->query($sql_option)->list_array();
                }
            }
            $survey['questionlist'] = $survey_question;
        }
        return $survey;
    }
    /**
     * 获取问题列表
     * @return [type] [description]
     */
    public function getQuestionList($sid = 0) {
        $sql = 'SELECT qid,sid,type,title,displayorder,layout_type,group_id FROM ebh_surveyquestions WHERE sid=' . intval($sid) . ' ORDER BY displayorder,qid';
        return $this->db->query($sql)->list_array();
    }
    /**
     * 获得一份答卷记录
     * @param  interger $sid 问卷编号
     * @param  interger $uid 用户编号
     * @return array        答题记录
     */
    public function getOneAnswer($sid, $uid){
        $sql = 'select answers from ebh_surveyanswers a ';
        $wherearr[] = 'a.sid='.$sid;
        $wherearr[] = 'a.uid='.$uid;
        $sql.= ' where '.implode(' AND ',$wherearr);
        $row = $this->db->query($sql)->row_array();
        if (!empty($row['answers']))
            return unserialize($row['answers']);
        else
            return FALSE;
    }
    /**
     * 获取课程详情
     * @param  interger $cid 选课课程编号
     * @param  interger $uid 用户编号
     * @return array        答题记录
     */
    public function getCourse($param){
        if(empty($param['cid'])){
            return false;
        }
        $sql = 'select cid,xkid from ebh_xk_courses xc';
        $where = ' where xc.cid ='.$param['cid'];
        if(!empty($param['status'])){
            $where .= ' and xc.status='.$param['status'];
        }else{
            $where .= ' and xc.status in(1,2)';
        }
        $sql .= $where;

        $course = $this->db->query($sql)->row_array();
        if(empty($course) === true) {
            return false;
        }
        $uid = $course['uid'];
        $user = $this->db->query("SELECT uid,realname,username FROM ebh_users WHERE uid=$uid")->row_array();
        if(empty($user)) {
            return false;
        }
        $course['realname'] = $user['realname'];
        $course['username'] = $user['username'];
        return $course;
    }
    /**
     *  验证用户是否做过该网校最新的特定问卷
     * @param unknown $crid
     * @param unknown $uid
     * @param number $type
     * @return 失败返回FALSE,成功返回$sid(特定问卷的最后一个sid)
     */
    public function checkSurvey($param){
        $return = false;
        $uid = !empty($param['uid']) ? intval($param['uid']) : 0;
        $crid = !empty($param['crid']) ? intval($param['crid']) : 0;
        $type = isset($param['type']) ? intval($param['type']) : 5;
        $isroomclass = (!empty($param['isroomclass']) && ($param['isroomclass'] == 1)) ? 1 : 0;//0全校,1年级/班级
        $classids = !empty($param['classids']) ? $param['classids'] : array();  //指定被调查班级的id集
        $classids = array_filter($classids, function($classid) {    //过滤数组
            return is_numeric($classid) && ($classid>0);
        });
        if(empty($uid) || empty($crid) || (($isroomclass == 1) && empty($classids))){
            return $return;
        }
        //如果有指定被调查的年级/班级,查询当前登录用户是否为其中的学生
        if(!empty($isroomclass) && !empty($classids)){
            $isexistlog = 'SELECT count(1) count from ebh_classstudents WHERE uid='.$uid.' AND classid IN ('.implode(',',$classids).')';
            $isexist = $this->db->query($isexistlog)->row_array();
            if(empty($isexist['count']) || !($isexist['count']>0)){
                return $return;
            }
        }
        //查询该用户回答过的调查问卷
        $sql = 'select s.sid from ebh_surveyanswers a inner join ebh_surveys s on a.sid = s.sid where s.crid = '.$crid.' and a.uid = '.$uid.' and s.type = '.$type;
        $ret = $this->db->query($sql)->list_array();
        //查询最新的一条有效的调查问卷
        $ssql = 'select s.sid from ebh_surveys s WHERE s.crid = '.$crid.' and s.type = '.$type.' and s.isdelete=0 and s.ispublish=1';
        $ssql .=' and s.startdate<=' . SYSTIME . ' and (s.enddate>=' . SYSTIME .' OR s.enddate=0) order by s.sid desc limit 1';
        $row = $this->db->query($ssql)->row_array();
        //查询最新发布的一条调查问卷
        if($type == 5){
            $lastsql = 'select s.sid from ebh_surveys s WHERE s.crid = '.$crid.' and s.type = '.$type.' order by s.sid desc limit 1';
            $lastsurvey = $this->db->query($lastsql)->row_array();
        }
        $sids = array();
        if(!empty($ret)){
            $sids = array_column($ret,'sid');
        }
        if(!empty($row['sid'])){
            if(!empty($lastsurvey['sid']) && ($lastsurvey['sid'] != $row['sid'])){//若最新的调查问卷无效,则返回false
                return $return;
            }
            if(empty($sids) || (!empty($sids) && !in_array($row['sid'],$sids))){
                $return = $row['sid'];
            }
        }
        return $return;
    }
    /**
     * 获取指定类型最新一条调查问卷
     * @return 失败返回FALSE,成功返回调查问卷
     */
    public function getLastSurvey($crid,$type) {
        $result = false;
        if(!empty($crid)){
            $sql = 'select s.sid,s.crid,s.type,s.folderid,s.cwid,s.title,s.content,s.dateline,s.istemplate,s.ispublish,s.isdelete,s.allowview,s.answernum,s.allowanonymous,s.uid,s.cid,s.startdate,s.enddate';
            $sql .= ' from ebh_surveys s WHERE s.crid = '.$crid.' and s.type = '.$type.' and s.isdelete=0 and s.ispublish=1';
            $sql .=' and s.startdate<=' . SYSTIME . ' and (s.enddate>=' . SYSTIME .' OR s.enddate=0) order by s.sid desc limit 1';
            $res= $this->db->query($sql)->row_array();
            if(!empty($res)){
                $result = $res;
            }
        }
        return $result;
    }
}