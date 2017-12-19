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
     * @return  array
     */
    public function getAskanswer($crid){
        $sql
            = 'SELECT COUNT(ask.uid) as number ,ask.uid,q.crid,u.realname,u.sex,u.username,u.face FROM ebh_askanswers ask INNER JOIN ebh_users u ON ask.uid=u.uid';
        $sql .= ' INNER JOIN ebh_askquestions q ON q.qid=ask.qid';

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

}