<?php
/**
 * 问卷调查
 * Created by PhpStorm.
 * User: zyp
 * Date: 2017/07/03
 * Time: 19:12
 */
class SurveyController extends Controller{
    public $surveyModel;
    public $folderModel;
    public $coursewareModel;
    public function init(){
        parent::init();
        $this->surveyModel = new SurveyModel();
        $this->folderModel = new FolderModel();
        $this->coursewareModel = new CoursewareModel();
    }
    public function parameterRules(){
        return array(
            'surveyListAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','default'=>0,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','default'=>0,'type'=>'int'),
                'teacherid'  =>  array('name'=>'teacherid','default'=>0,'type'=>'int'),
                'filteruid'  =>  array('name'=>'filteruid','default'=>0,'type'=>'int'),
                'isopening'  =>  array('name'=>'isopening','default'=>0,'type'=>'int'),
                'showlist'  =>  array('name'=>'showlist','default'=>0,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>'','type'=>'string'),
                'order'  =>  array('name'=>'order','default'=>'desc','type'=>'string'),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>0,'type'=>'int'),
            ),
            'getFolderByIdAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int')
            ),
            'ifAnsweredAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
            ),
            'getOneAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
            ),
            'getOneAnswerAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'sid'  =>  array('name'=>'sid','require'=>true,'type'=>'int'),
            ),
            'getCourseAction'   =>  array(
                'cid'  =>  array('name'=>'cid','require'=>true,'type'=>'int')
            ),
            'getSimpleInfoByIdAction'   =>  array(
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int')
            ),
            'checkSurveyAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','default'=>5,'type'=>'int'),
                'isroomclass'  =>  array('name'=>'isroomclass','type'=>'int'),
                'classids'  =>  array('name'=>'classids','type'=>'array'),
            ),
            'getLastSurveyAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','default'=>5,'type'=>'int'),
            ),
        );
    }
	/**
	 * 问卷列表
	 */
	public function surveyListAction(){
		$param = array();
        $param['pagesize'] = $this->pagesize;
        $param['page'] = $this->page;
		$param['q'] = $this->q;
		$param['crid'] = $this->crid;
		$param['uid'] = $this->uid;
        $param['showlist'] =$this->showlist;

		$param['ispublish'] = 1;
		$param['answered'] = true;//是否已回答
        $param['untype'] = 3;//不看选课问卷
        $param['folderid'] = $this->folderid;
        $param['teacherid'] = $this->teacherid;
        $param['filteruid'] = $this->filteruid;
        if(!empty($param['showlist'])){
            $surveylist = $this->surveyModel->getSurveyList($param);
        }else{
            $surveylist = $this->surveyModel->getSurveyOrderList($param);
        }
        $surveycount = $this->surveyModel->getSurveyCount($param);
        if($surveycount == 0 && count($surveylist) ==0){
            return array('surveylist'=>array(),'surveycount'=>0);
        }else{
            return array('surveylist'=>$surveylist,'surveycount'=>$surveycount);
        }
	}
    /**
        获取附加课件详情
    */
    public function getFolderByIdAction(){
        $crid = $this->crid;
        $folderid = $this->folderid;
        $folder = $this->folderModel->getFolderById($folderid,$crid);
        return $folder;
    }
    /**
    是否做过该问卷
     */
    public function ifAnsweredAction(){
        $param['uid'] = $this->uid;
        $param['sid'] = $this->sid;
        $surveyanswer = $this->surveyModel->ifAnswered($param);
        return $surveyanswer;
    }
    /**
    获取一个问卷
     */
    public function getOneAction(){
        $crid = $this->crid;
        $sid = $this->sid;
        $survey = $this->surveyModel->getOne($sid, $crid);
        return $survey;
    }
    /**
    获取一个答卷
     */
    public function getOneAnswerAction(){
        $uid = $this->uid;
        $sid = $this->sid;
        $answer = $this->surveyModel->getOneAnswer($sid, $uid);
        return $answer;
    }
    /**
    获取选课课程
     */
    public function getCourseAction(){
        $cid = $this->cid;
        $course  = $this->surveyModel->getCourse($cid);
        return $course ;
    }
    /**
    获取关联课件信息
     */
    public function getSimpleInfoByIdAction(){
        $cwid = $this->cwid;
        $cw  = $this->coursewareModel->getSimpleInfoById($cwid);
        return $cw ;
    }
    /**
    *验证用户是否做过该网校的特定问卷
     * return 成功返回$sid(特定问卷的最后一个sid),失败返回FALSE
     */
    public function checkSurveyAction(){
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
        if(!empty($this->type)){
            $param['type'] = $this->type;
        }
        if(!empty($this->isroomclass)){
            $param['isroomclass'] = $this->isroomclass; //0全校,1指定年级/班级
        }
        if(!empty($this->classids)){
            $param['classids'] = $this->classids;       //班级id集
        }
        return $this->surveyModel->checkSurvey($param);
    }
    /**
    获取最新一条对应类型调查问卷
     */
    public function getLastSurveyAction(){
        $crid = $this->crid;
        if(!empty($this->type)){
            $type = $this->type;
        }else{
            $type = 5;
        }
        return $this->surveyModel->getLastSurvey($crid,$type);
    }
}