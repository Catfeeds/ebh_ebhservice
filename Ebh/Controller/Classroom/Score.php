<?php
/**
 * 获取用户学分
 * Created by PhpStorm.
 * User: zyp
 * Date: 2017/07/08
 * Time: 16:21
 */
class ScoreController extends Controller{
    public $scoreModel;
    public $courseModel;
    public $newsModel;
    public function init(){
        parent::init();
        $this->scoreModel = new StudycreditlogsModel();
        $this->courseModel = new CoursewareModel();
        $this->newsModel = new NewsModel();
    }
    public function parameterRules(){
        return array(
            'getUserScoreListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'order'  =>  array('name'=>'order','default'=>'desc','type'=>'string'),
                'pagesize' =>	array('name'=>'pagesize','default'=>30,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>0,'type'=>'int')
            ),
            'getUserSumAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','type'=>'int'),
				'exceptlogid_p' => array('name'=>'exceptlogid_p','type'=>'int','default'=>0),
				'exceptlogid_c' => array('name'=>'exceptlogid_c','type'=>'int','default'=>0),
            ),
            'addOneScoreAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'dateline'  =>  array('name'=>'dateline','default'=>0,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','default'=>0,'type'=>'int'),
                'reviewid'  =>  array('name'=>'reviewid','default'=>0,'type'=>'int'),
                'eid'  =>  array('name'=>'eid','default'=>0,'type'=>'int'),
                'cwid'  =>  array('name'=>'cwid','default'=>0,'type'=>'int'),
                'fromip'  =>  array('name'=>'fromip','default'=>0,'type'=>'string'),
                'type'  =>  array('name'=>'type','default'=>0,'type'=>'int'),
                'wordslength'  =>  array('name'=>'wordslength','default'=>0,'type'=>'int'),
                'articleid'  =>  array('name'=>'articleid','default'=>0,'type'=>'int'),
                'readtime'  =>  array('name'=>'readtime','default'=>0,'type'=>'int')
            ),
			'folderScoreAction' => array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
				'folderids' => array('name'=>'folderids','require'=>true,'type'=>'string'),
			),
            'doReviewScoreSyncAction' => array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int')
			),
            'doOneScoreSyncAction' => array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','require'=>true,'type'=>'int')
			),
            'doStudyScoreSyncAction' => array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int')
            ),
            'isVideoAction'  =>  array(
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1),
            ),
            'deleteScoreAction'   =>  array(
            'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
            'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            'type'  =>  array('name'=>'type','require'=>true,'type'=>'int'),
            'logid'  =>  array('name'=>'logid','type'=>'int'),
            'itemid'  =>  array('name'=>'itemid','type'=>'int')
            )
        );
    }
    /**
    获取指定用户的学分列表
     */
    public function getUserScoreListAction(){
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
        $param['pagesize'] = $this->pagesize;
        $param['page'] = $this->page;
        $scorelist = $this->scoreModel->getUserScoreList($param);
        $scorecount = $this->scoreModel->getUserSum($param);
        if(empty($scorecount['count']) && count($scorelist) ==0){
            return array('scorelist'=>array(),'scorecount'=>0);
        }else{
            return array('scorelist'=>$scorelist,'scorecount'=>$scorecount['count']);
        }
    }
    /**
    获取指定用户总学分和学分列表总数
     */
    public function getUserSumAction(){
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
        if(isset($this->type)){
            $param['type'] = $this->type;   //获取指定用户文章、课件或评论获得的总学分
        }
		if(isset($this->exceptlogid_c)){//不查询的学分记录id
			$param['exceptlogid'] = $this->exceptlogid_c;
		}
        $scoresum = $this->scoreModel->getUserSum($param);//学分
		$scoresum['scoresum'] = empty($scoresum['scores'])?0:round($scoresum['scores'],1);
		
		$plmodel = new PlayLogModel();
		$paramp = array('uid'=>$this->uid,'crid'=>$this->crid);
		if(isset($this->exceptlogid_p)){//不查询的听课记录id
			$paramp['exceptlogid'] = $this->exceptlogid_p;
		}
		$ltime = $plmodel->getTimeByCrid($paramp);//学时
		$ltime = round(intval($ltime)/3600,1);
		$scoresum['ltime'] = $ltime;
        return $scoresum;
    }

    /**
    增加一条学生的学分记录
     */
    public function addOneScoreAction(){
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
        $param['dateline'] = $this->dateline;
        $param['folderid'] = $this->folderid;
        $param['eid'] = $this->eid;
        $param['reviewid'] = $this->reviewid;
        $param['cwid'] = $this->cwid;
        $param['type'] = $this->type;
        $param['fromip'] = $this->fromip;
        $param['articleid'] = $this->articleid;
        $param['wordslength'] = $this->wordslength;

        //获取网校配置
        $model = new SystemSettingModel();
        $systemSetting = $model->getOtherSetting($this->crid);
        if (empty($systemSetting['creditrule'])) {
            return returnData(0,'系统设置获取失败');
        }
        $scoreSetting = json_decode($systemSetting['creditrule'],TRUE);
        if (!$scoreSetting) {
            return returnData(0,'学分规则获取失败');
        }
        switch ($param['type']) {
            case 2:
                $scorekey = 'article';//发表文章
                break;
            case 3:
                $scorekey = 'comment';//评论
                break;
            case 4:
                $scorekey = 'notvideo';//其他学分获取
                break;
            case 5:
                $scorekey = 'news';//阅读原创文章
                break;
            default:
                break;
        }
        //禁用该功能
        if (!$scoreSetting[$scorekey]['on']) {
            return returnData(0,'该学分规则未开启');
        }
        if(!empty($param['cwid'])){
            $info = $this->isVideoAction($param['cwid']);
        }
        if ($param['type'] == 3) {
            if($this->wordslength <= $scoreSetting['comment']['needwords']){
                return returnData(0,'评论字数不足学分添加失败');
            }
            if(empty($param['cwid'])){
                return returnData(0,'评论课件信息未获取到无法获得学分');
            }
            $param['needwords'] = $scoreSetting['comment']['needwords'];
            $isreview = $this->scoreModel->getReviewScoreById($param);
            if(!empty($isreview)){
                return returnData(0,'已评论过该课件无法再获得学分');
            }
        }
        if($param['type'] == 4){
            if(isset($info['data']['isvideo']) && $info['data']['isvideo']==TRUE){
                return returnData(0,'视频课件学分添加失败');
            }
            $score = $this->scoreModel->getScoreById($param);
            if(!empty($score)){
                return returnData(0,'已获取过阅读该课件的学分');
            }
        }
        if($param['type'] == 5){
            if(empty($param['articleid'])){
                return returnData(0,'查看原创文章详情失败');
            }
            $ret = $this->newsModel->getModel($param['articleid']);
            if(!empty($ret['uid']) && $ret['uid']==$param['uid']){
                return returnData(0,'查看自己发表的原创文章,学分添加失败');
            }
            $score = $this->scoreModel->getScoreById($param);
            if(!empty($score)){
                return returnData(0,'已获取过阅读该文章的学分');
            }
        }
        $param['score'] = $scoreSetting[$scorekey]['single'];

        $ret = $this->scoreModel->addOneScore($param);
        if($ret === false){
            return returnData(0,'该功能学分添加失败');
        }else{
            return returnData(1,'学分添加成功',$ret);
        }
    }

    /**
     *删除评论后评论所得学分记录也同时删除
     *@param int $logid
     *@return bool
     */
    public function deleteScoreAction(){
        $param['crid'] = $this->crid;//清除缓存用到
        $param['uid'] = $this->uid;
        $param['type'] = $this->type;
        if(!empty($this->logid)){
            $param['reviewid'] = $this->logid;   //获取指定用户删除的评论id
        }
        if(!empty($this->itemid)){
            $param['articleid'] = $this->itemid;   //获取指定用户删除的原创文章id
        }
        $ret = $this->scoreModel->deleteScore($param);
        if($ret){
            return returnData(1,'该功能所得学分删除成功',$ret);
        }else{
            return returnData(0,'该功能所得学分删除失败');
        }
    }

	/*
	学生课程学分
	*/
	public function folderScoreAction(){
		$param['crid'] = $this->crid;
		$param['folderids'] = $this->folderids;
		$param['uid'] = $this->uid;
		return $this->scoreModel->getFolderScore($param);
	}
	/**
	*修改系统其他学分设置的评论设置时，同步评论学分
	*/
	public function doReviewScoreSyncAction(){
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
		return $this->scoreModel->doReviewScoreSync($param);
	}
    /**
     *用户删除评论或原创文章时，同步该用户对应学分
     */
    public function doOneScoreSyncAction(){
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
        $param['type'] = $this->type;
        return $this->scoreModel->doOneScoreSync($param);
    }
    /**
     *修改系统其他学分设置的非视频课件、原创文章学习或原创文章发表学分设置时，同步对应学分
     */
    public function doStudyScoreSyncAction(){
        $param['crid'] = $this->crid;
        $param['uid'] = $this->uid;
        $param['type'] = $this->type;
        return $this->scoreModel->doStudyScoreSync($param);
    }
    /**
     *判断是否为视频课件* @param
     * @return Boolean 视频课件返回TRUE，类型video，非视频课件返回FALSE和类型notvideo，文件名为空返回FALSE和类型nofile
     */
    public function isVideoAction($cwid=0){
        $cwid = !empty($cwid) ? $cwid : $this->cwid;
        $course = $this->courseModel->getCourseByCwid($cwid);
        if(!empty($course['cwname'])){
            $videos = array('flv','avi','rmvb','rm','asf','divx','mpg','mpeg','mpe','wmv','mp4','mkv','vob','swf');
            $info = pathinfo($course['cwname']);
            if(!empty($info['extension']) && in_array($info['extension'],$videos)){
                return returnData(0,'视频课件',array('isvideo'=>TRUE,'cwtype'=>'video'));
            }else{
                return returnData(1,'非视频课件',array('isvideo'=>FALSE,'cwtype'=>'notvideo'));
            }
        }
        return returnData(1,'图文课件',array('isvideo'=>FALSE,'cwtype'=>'nofile'));
    }
}