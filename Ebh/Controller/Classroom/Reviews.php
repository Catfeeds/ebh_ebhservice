<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:31
 */
class ReviewsController extends Controller{


    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'cwid'  =>  array('name'=>'cwid','default'=>0),
                'uid'  =>  array('name'=>'uid','default'=>0),
            ),
            'addAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'content'  =>  array('name'=>'content','require'=>true),
                'score'  =>  array('name'=>'score','require'=>false,'type'=>'int','default'=>0),
                'fromip'  =>  array('name'=>'fromip','require'=>true),
            ),
            'replyAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'upid'  =>  array('name'=>'upid','require'=>true,'type'=>'int'),
                'toid'  =>  array('name'=>'toid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','require'=>true),
                'msg'  =>  array('name'=>'msg','require'=>true),
                'ip'  =>  array('name'=>'ip','require'=>true),
            ),
            'delAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'logid'  =>  array('name'=>'logid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'getDetailByLogidAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'logid'  =>  array('name'=>'logid','require'=>true,'type'=>'int'),
            )
        );
    }

    /**
     * 删除评论
     * @return array
     */
    public function delAction(){
        $reviewModel = new ReviewModel();
        $review = $reviewModel->getReviewByLogid($this->logid);
        if($review['uid'] == $this->uid){
            if($reviewModel->deletereview(array('logid'=>$this->logid))){
                return returnData(1,'删除成功');
            }else{
                return returnData(0,'删除失败');
            }
        }else{
            return returnData(0,'删除失败');
        }
    }

    /**
     * @param $content
     * @return array|bool
     */
    private function checkSensitive($content){
        if (!file_exists(APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'sensitive.cache')) {
            SimpleDict::make(APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'sensitive.dat', APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'sensitive.cache');
        }
        $dict = new SimpleDict(APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'sensitive.cache');
        $content = preg_replace("/\s/", "", $content);
        $result = $dict->search($content);
        $resultarr = array();
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $resultarr[] = $value['value'];
            }

            return returnData(0,'内容含有敏感词',array('Sensitive' => $resultarr));
        }
        return false;
    }

    /**
     * 添加评论消息
     */
    public function addAction(){
        $parameters['uid'] = $this->uid;
        $parameters['crid'] = $this->crid;
        $parameters['toid'] = $this->cwid;
        $parameters['opid'] = 8192;
        $parameters['type'] = 'Courseware';
        $parameters['subject'] = $this->content;
        $parameters['score'] = $this->score;
        $parameters['credit'] = 0;
        $parameters['upid'] = 0;
        $parameters['value'] = 0;
        $parameters['dateline'] = time();
        $parameters['fromip'] = $this->fromip;

        $sensitive = $this->checkSensitive($this->content);
        if($sensitive){
            return $sensitive;
        }
        $reviewsModel = new ReviewModel();
        $result = $reviewsModel->insert($parameters);

        //如果添加成功
        if($result > 0){
            $courseModel = new CoursewareModel();//增加课件评论数
            $courseModel->addreviewnum($this->cwid);

            //新评论通过私信告诉主讲教师
            $course = $courseModel->getcoursedetail($this->cwid);
            $msglib = new MessageUtil();
            $msgtype = 4; //新评论
            $lastmsg = $msglib->getLastUnReadMessage($course['uid'], $this->cwid, $msgtype);
            $userModel = new UserModel();
            $user = $userModel->getUserByUid($this->uid);
            $uname = empty($user['realname']) ? $user['username'] : $user['realname'];
            if(empty($lastmsg)) {	//如果当前的答疑私信没有未读的，则直接添加消息
                $msglib->sendMessage($user['uid'], $uname, $course['uid'], $this->cwid, $msgtype, $course['title'],$this->crid);
            } else {	//否则更新消息即可
                $ulist = $lastmsg['ulist'];
                parse_str($ulist,$uarr);
                if(!isset($uarr[$user['uid']])) {
                    if(empty($ulist)) {
                        $ulist = $user['uid'].'='.$uname;
                    } else {
                        $ulist .= '&'.$user['uid'].'='.$uname;
                    }
                    $lastmsg['ulist'] = $ulist;
                    $lastmsg['dateline'] = SYSTIME;
                    $msglib->updateMessage($lastmsg);
                }
            }
            return returnData(1,'评论成功');


        }else{
            return returnData(0,'评论失败');
        }
    }

    /**
     * 回复评论
     * @return array
     */
    public function replyAction(){
        $type = $this->type;
        $uid = $this->uid;
        $upid = $this->upid;
        $toid = $this->toid;
        $msg = $this->msg;
        $fromip = $this->ip;
        $sensitive = $this->checkSensitive($msg);
        if($sensitive){
            return $sensitive;
        }
        $reviewModel = new ReviewModel();
        if($type == 'courseware_reply' || $type == 'courseware_reply_son'){
            $upReview = $reviewModel->getReviewByLogid($upid);
            if(!$upReview){
                return returnData(0,'回复的内容不存在');
            }

            if($type == 'courseware_reply'){
                if($upReview['toid'] != $uid && $upReview['upid'] != 0){
                    returnData(0,'你没有权限回复该内容');
                }
            }else{
                if($upReview['toid'] != $uid &&  $upReview['uid'] != $uid){
                    returnData(0,'你没有权限回复该内容');
                }
            }

            $param = array('uid'=>$uid,'toid'=>$toid,'opid'=>8192,'type'=>$type,'subject'=>$msg,'credit'=>0,'upid'=>$upid,'value'=>0,'fromip'=>$fromip,'dateline'=>time());
            $param['audit'] = 1;//暂时设置回复默认通过
            $result = $reviewModel->insert($param);
            return returnData(1,'回复成功',array('logid'=>$result));

        }else{
            return returnData(0,'回复失败');
        }
    }

    /**
     * 读取评论列表
     * cwid :获取指定课件ID
     * uid:获取指定用户发布的评论
     * 如果两个参数都传了,则获取交集
     * @return array
     */
    public function listAction(){
        if($this->cwid == 0 && $this->uid == 0){
            return returnData(0,'用户ID或者课件ID必须传一个');
        }
        if($this->cwid > 0){
            $parameters['cwid'] = $this->cwid;
        }
        if($this->uid > 0){
            $parameters['uid'] = $this->uid;
        }
        $reviewsModel = new ReviewModel();
        $total = $reviewsModel->getReviewCount($parameters);

        $pageLib = new Page($total);
        $parameters['limit'] = 'limit '. $pageLib->firstRow . ',' . $pageLib->listRows;
        $list = $reviewsModel->getReviewListByCwidOnRecUrsion($parameters);
        $result['total'] = $total;
        $result['list'] = $list;
        return returnData(1,'',$result);

    }

    /**
     * 根据logid获取评论和课程,课件详情
     * @return array
     */
    public function getDetailByLogidAction(){
        $reviewModel = new ReviewModel();
        return $reviewModel->getDetailByLogid($this->logid,$this->crid);
    }
}