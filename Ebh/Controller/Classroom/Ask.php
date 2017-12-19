<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:37
 */
class AskController extends Controller{
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'cwid'  =>  array('name'=>'cwid','default'=>0),
                'uid'  =>  array('name'=>'uid','default'=>0),
                'folderid'  =>  array('name'=>'folderid','default'=>0),
                'type'  =>  array('name'=>'type','default'=>0), //列表类型 0:所有问题 1:热门问题 2:推荐问题 3:等待答复 (此处UID必传) 4:已解决的问题 5:我回答的问题（此处UID必传） 6:我关注的问题 (此处UID必传)
            ),
            'addAction'    =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'folderid'  =>  array('name'=>'folderid','default'=>0),
                'cwid'  =>  array('name'=>'cwid','default'=>0),
                'title'  =>  array('name'=>'title','require'=>true),
                'message'  =>  array('name'=>'message','require'=>true),
                'imagename'  =>  array('name'=>'imagename','type'=>'array','dafaule'=>array()),
                'imagesrc'  =>  array('name'=>'imagesrc','type'=>'array','dafaule'=>array()),
                'reward'  =>  array('name'=>'reward','default'=>0),
                'tid'  =>  array('name'=>'tid','default'=>0),
                'fromip'  =>  array('name'=>'fromip','require'=>true),
            ),
            'editAction'    =>  array(
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'folderid'  =>  array('name'=>'folderid'),
                'cwid'  =>  array('name'=>'cwid'),
                'title'  =>  array('name'=>'title'),
                'message'  =>  array('name'=>'message'),
                'imagename'  =>  array('name'=>'imagename','type'=>'array'),
                'imagesrc'  =>  array('name'=>'imagesrc','type'=>'array'),
                'tid'  =>  array('name'=>'tid'),
                'reward'  =>  array('name'=>'reward'),
            ),
            'detailAction'  =>  array(
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','type'=>'int','default'=>0),
            ),
            'deleteAction'  =>  array(
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','type'=>'int','default'=>0),
            ),
            'bestAction'  =>  array(
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int','min'=>1),
                'aid'  =>  array('name'=>'aid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
            ),
            'addAnswerAction'    =>  array(
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'message'  =>  array('name'=>'message','require'=>true),
                'ip'  =>  array('name'=>'ip','default'=>''),
            ),
            'addAnswerThankAction'    =>  array(
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'aid'  =>  array('name'=>'aid','require'=>true,'type'=>'int','min'=>1),
                'ip'  =>  array('name'=>'ip','require'=>true),
            ),
            'delAnswerAction'    =>  array(
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'aid'  =>  array('name'=>'aid','require'=>true,'type'=>'int','min'=>1),
            ),
            'favoriteAction'    =>  array(
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'flag'  =>  array('name'=>'flag','require'=>true,'type'=>'int','min'=>0),
            ),
            'rewardAction'    =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'rewards'  =>  array('name'=>'rewards','require'=>true,'type'=>'array'),
                'aids'  =>  array('name'=>'aids','require'=>true,'type'=>'array'),
            ),

        );
    }


    /**
     * 分配悬赏积分
     * @return array
     */
    public function rewardAction(){
        $qid = $this->qid;
        $uid = $this->uid;
        $rewards = $this->rewards;
        $aids = $this->aids;
        $parameters['qid'] = $this->qid;
        $askModel = new AskQuestionModel();
        $question = $askModel->detail($parameters);

        if($question['uid']  != $uid){
            return returnData(0,'没有操作权限');
        }
        if ($question['isrewarded'] == 1) {
            return returnData(0,'该问题已经发放过悬赏积分');
        }

        if ($question['reward'] != array_sum($rewards)) {
            return returnData(0,'分配积分与悬赏积分不一致');
        }
        $creditModel = new CreditModel();
        $askModel->update(array('qid' => $qid, 'uid' => $uid, 'isrewarded' => 1));
        $messageUtil = new MessageUtil();
        $answerMolde= new AskAnswersModel();
        foreach ($rewards as $k => $reward) {
            if ($reward > 0) {
                $creditModel->addCreditlog(array('ruleid' => 27, 'aid' => $aids[$k], 'credit' => $reward, 'qid' => $qid,'loginuid'=>$uid,'crid'=>$this->crid));
                //发送私信给回答者
                $answerDetail = $answerMolde->detail($aids[$k]);
                $toid = $answerDetail['uid'];
                $type = 1;    //系统消息
                $msg = serialize(array(
                        'title' => $question['title'],
                        'reward' => $reward
                    )
                );//由问题和积分组合
                $messageUtil->sendMessage(0, '系统管理员', $toid, $qid, $type, $msg,$this->crid);
            }
        }
        return returnData(1,'分配积分完成');

    }
    /**
     * 添加回答
     * @return array
     */
    public function addAnswerAction(){
        $parameters['qid'] = $this->qid;

        $askQuestionModel = new AskQuestionModel();
        $ask = $askQuestionModel->detail($parameters);

        if(!$ask){
            return returnData(0,'该问题不存在');
        }

        if($ask['uid'] == $this->uid){
            return returnData(0,'不能回答自己的问题');
        }

        if($ask['status'] == 1){
            return returnData(0,'该问题已解决');
        }

        $parameters['uid'] = $this->uid;
        $parameters['message'] = $this->message;


        $parameters['fromip'] = $this->ip;
        $askAnswerModel = new AskAnswersModel();
        $id = $askAnswerModel->addanswer($parameters);

        if($id > 0){
            $askQuestionModel = new AskQuestionModel();

            $question = $askQuestionModel->detail($parameters);
            $upparam = array(
                'qid' => $this->qid,
                'uid' => $ask['uid'],
                'lastansweruid' => $this->uid
            );
            $askQuestionModel->update($upparam);

            //回答加分
            $creditModel = new CreditModel();
            if ($question['uid'] != $this->uid){
                $creditModel->addCreditlog(array('ruleid' => 21, 'qid' => $this->qid,'loginuid'=>$this->uid,'crid'=>$question['crid']));
            }
            //发送通知
            $messageUtil = new MessageUtil();
            $type = 2; //答疑新回答
            $lastmsg = $messageUtil->getLastUnReadMessage($ask['uid'], $this->qid, $type);
            $userModel = new UserModel();
            $user = $userModel->getUserByUid($this->uid);
            $uname = empty($user['realname']) ? $user['username'] : $user['realname'];
            if (empty($lastmsg)) {    //如果当前的答疑私信没有未读的，则直接添加消息
                $title = $ask['title'];
                $msg = $title;
                $messageUtil->sendMessage($user['uid'], $uname, $ask['uid'], $this->qid, $type, $msg,$question['crid']);
            }else {    //否则更新消息即可
                $ulist = $lastmsg['ulist'];
                parse_str($ulist, $uarr);
                if (!isset($uarr[$user['uid']])) {
                    if (empty($ulist)) {
                        $ulist = $user['uid'] . '=' . $uname;
                    } else {
                        $ulist .= '&' . $user['uid'] . '=' . $uname;
                    }
                    $lastmsg['ulist'] = $ulist;
                    $lastmsg['dateline'] = SYSTIME;
                    $messageUtil->updateMessage($lastmsg);
                }
            }
            return returnData(1,'',array('aid'=>$id));
        }else{
            return returnData(0,'回答失败');
        }
    }

    /**
     * 删除答案
     * @return array
     */
    public function delAnswerAction(){
        $qid = $this->qid;
        $aid = $this->aid;
        $uid = $this->uid;
        $askModel = new AskQuestionModel();
        $answerModel = new AskAnswersModel();

        $ans = $answerModel->getAnswerInfoByAid($aid);
        $param = array('qid' => $qid, 'aid' => $aid, 'uid' => $uid);
        $result = $answerModel->delanswer($param);

        if($result > 0){
            if($ans['isbest']){
                $askModel->updateQueStatusByQid($qid);
            }
            return returnData(1,'删除成功');
        }
        return returnData(0,'删除失败');
    }
    /**
     * 删除答疑
     */
    public function deleteAction(){
        $askModel = new AskQuestionModel();
        $res = $askModel->deleteaskquestion($this->qid);
        if($res !== false){
            if($this->crid > 0){
                $classroomModel = new ClassRoomModel();
                $classroomModel->addasknum($this->crid, -1);
            }
            return returnData(1,'');
        }else{
            return returnData(0,'删除成功');
        }
    }
    /**
     * 读取答疑列表
     */
    public function listAction(){
        if($this->cwid > 0){
            $parameters['cwid'] = $this->cwid;
        }
        if($this->folderid > 0){
            $parameters['folderid'] = $this->folderid;
        }
        if($this->uid > 0){
            $parameters['uid'] = $this->uid;
        }
        $parameters['shield'] = 0;
        switch ($this->type){
            case 1:
                //热门 回答数倒叙 并且没有设置最佳答案
                $parameters['hasbest'] = 0;
                $parameters['order'] = 'a.answercount desc';
                return $this->getQuestionList($parameters);
                break;
            case 2:
                //推荐 回答数倒叙
                $parameters['order'] = 'a.answercount desc';
                return $this->getQuestionList($parameters);
                break;
            case 3:
                //等待回复 。自己未回答的
                $parameters['waitanswer'] = true;//读取等待答复
                return $this->getQuestionList($parameters);
                break;
            case 4:
                //已解决 已设置过最佳答案
                $parameters['hasbest'] = 1;
                return $this->getQuestionList($parameters);
                break;
            case 5:
                //我的回答
                return $this->getMyAnswerQuestionList($parameters);
                break;
            case 6:
                //我关注的问题
                return $this->getMyFavoriteQuestionList($parameters);
                break;
            default:
                return $this->getQuestionList($parameters);
                break;
        }


    }

    /**
     * 获取问题列表
     * @param $parameters
     * @return array
     */
    private function getQuestionList($parameters){
        $askModel = new AskQuestionModel();
        $total =  $askModel->getCount($this->crid,$parameters);
        $pageLib = new Page($total);
        $parameters['limit'] = $pageLib->firstRow.','.$pageLib->listRows;
        $list = $askModel->getAskList($this->crid,$parameters);
        $result['total'] = $total;
        $result['list'] = $list;
        return returnData(1,'',$result);
    }

    /**
     * 获取我回答的问题列表
     * @param $parameters
     * @return array
     */
    private function getMyAnswerQuestionList($parameters){
        $askModel = new AskQuestionModel();
        $total = $askModel->getAnswerCount($this->crid,$parameters);
        $pageLib = new Page($total);
        $parameters['limit'] = $pageLib->firstRow.','.$pageLib->listRows;
        $list = $askModel->getListByMyAnswer($this->crid,$parameters);
        $result['total'] = $total;
        $result['list'] = $list;
        return returnData(1,'',$result);
    }

    /**
     * 获取我关注的问题列表
     * @param $parameters
     * @return array
     */
    private function getMyFavoriteQuestionList($parameters){
        $askModel = new AskQuestionModel();
        $total = $askModel->favoriteCount($this->crid,$parameters);
        $pageLib = new Page($total);
        $parameters['limit'] = $pageLib->firstRow.','.$pageLib->listRows;
        $list = $askModel->getFavoriteList($this->crid,$parameters);
        $result['total'] = $total;
        $result['list'] = $list;
        return returnData(1,'',$result);
    }


    private function checkSensitive($title, $message){
        if (!file_exists(APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'sensitive.cache')) {
            SimpleDict::make(APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'sensitive.dat', APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'sensitive.cache');
        }
        $dict = new SimpleDict(APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'sensitive.cache');
        $title = preg_replace("/\s/", "", $title);
        $result = $dict->search($title);
        $resultarr = array();
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $resultarr[] = $value['value'];
            }

            return returnData(0,'标题含有敏感词',array('Sensitive' => $resultarr));
        }
        $message = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", strip_tags($message));
        $result1 = $dict->search($message);
        $resultarr1 = array();
        if (!empty($result1)) {
            foreach ($result1 as $key => $value) {
                $resultarr1[] = $value['value'];
            }
            return returnData(0,'内容含有敏感词',array('Sensitive' => $resultarr));
        }
        return false;
    }
    /**
     * 添加疑问
     * @return array
     */
    public function addAction(){
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;
        if($this->folderid > 0){
            $parameters['folderid'] = $this->folderid;

            if($this->cwid > 0){
                $parameters['cwid'] = $this->cwid;

                $courseModel = new CoursewareModel();

                $course = $courseModel->getSimpleInfoById($this->cwid);

                if(!$course){
                    return returnData(0,'课件不存在');
                }

                $parameters['cwname'] = $course['title'];
            }
        }


        $userModel = new UserModel();
        $user = $userModel->getUserByUid($this->uid);
        if ($this->reward > 0 && $this->reward > $user['credit']){
            return returnData(0,'你的积分不足以支付本次悬赏积分');
        }

        $parameters['title'] = $this->title;
        $parameters['message'] = $this->message;
        $parameters['fromip'] = $this->fromip;
        $parameters['reward'] = $this->reward;
        $parameters['tid'] = $this->tid;

        $sensitive = $this->checkSensitive($this->title,$this->message);
        if($sensitive){
            return $sensitive;
        }
        if(!empty($this->imagename) && !empty($this->imagesrc) && count($this->imagename) == count($this->imagesrc)){
            $parameters['imagename'] = implode(',',$this->imagename);
            $parameters['imagesrc'] = implode(',',$this->imagesrc);
        }

        $askQuestionModel = new AskQuestionModel();
        $qid = $askQuestionModel->insert($parameters);

        if($qid > 0){
            //提问加分
            $creditModel = new CreditModel();
            $creditModel->addCreditlog(array('ruleid' => 15, 'qid' => $qid,'loginuid'=>$this->uid,'crid'=>$this->crid));
            if ($this->reward > 0) {
                //提问悬赏
                $creditModel->addCreditlog(array('ruleid' => 26, 'qid' => $qid, 'credit' => $this->reward,'loginuid'=>$this->uid,'crid'=>$this->crid));
            }
            //添加教师
            if($this->tid > 0){
                $uname = empty($user['realname']) ? $user['username'] : $user['realname'];
                $messageUtil = new MessageUtil();
                $type = 5;    //答疑新提问(针对老师)
                $msg = $this->title;
                $messageUtil->sendMessage($this->uid, $uname, $this->tid, $qid, $type, $msg,$this->crid);
            }
            //更新答疑数
            $classroomModel = new ClassRoomModel();
            $classroomModel->addasknum($this->crid, 1);
            return returnData(1,'',array('qid'=>$qid));
        }else{
            return returnData(0,'添加问题失败');
        }
    }

    /**
     * 编辑问题
     * @return array
     */
    public function editAction(){
        $askModel = new AskQuestionModel();
        $ask = $askModel->detail(array('qid'=>$this->qid));
        $parameters['qid'] = $this->qid;
        $parameters['uid'] = $this->uid;
        if($this->folderid !== null ){
            $parameters['folderid'] = $this->folderid;
        }

        if($this->cwid !== null){
            $parameters['cwid'] = $this->cwid;

            if($this->cwid > 0){
                $courseModel = new CoursewareModel();

                $course = $courseModel->getSimpleInfoById($this->cwid);

                if(!$course){
                    return returnData(0,'课件不存在');
                }

                $parameters['cwname'] = $course['title'];
            }
        }

        if($this->title !== null && !empty($this->title)){
            $parameters['title'] = $this->title;
        }
        if($this->message !== null && !empty($this->message)){
            $parameters['message'] = $this->message;
        }
        if($this->imagename !== null && $this->imagesrc !== null){
            if(empty($this->imagename) || empty($this->imagesrc)){
                $parameters['imagename'] = '';
                $parameters['imagesrc'] = '';
            }else{
                $parameters['imagename'] = implode(',',$this->imagename);
                $parameters['imagesrc'] = implode(',',$this->imagesrc);
            }

        }
        $sensitive = $this->checkSensitive($this->title,$this->message);
        if($sensitive){
            return $sensitive;
        }
        $userModel = new UserModel();
        $user = $userModel->getUserByUid($this->uid);
        if($this->reward !== null && $ask['isrewarded'] == 0 ){
            $parameters['reward'] = $this->reward;
            //如果原来积分没设置 编辑后积分大于 0
            if(empty($ask['reward']) && $this->reward > 0){
                $ruleid = 26;
                $newreward = $this->reward;
            }elseif ($this->reward > $ask['reward']) {
                //如果编辑后积分大于原来积分 。扣除多的部分
                $ruleid = 28;
                $newreward = $this->reward - $ask['reward'];
                if($newreward > $user['credit']){
                    return returnData(0,'你的积分不足,修改失败');
                }
            }elseif ($this->reward < $ask['reward']) {
                //如果修改积分小于原来的积分 返回多余部分
                $ruleid = 29;
                $newreward = $ask['reward'] - $this->reward;
            }
        }
        if($this->tid !== null){
            $parameters['tid'] = $this->tid;
        }
        $result = $askModel->update($parameters);
        if ($result !== false) {
            //修改成功
            //提问悬赏
            if (!empty($newreward) && !empty($ruleid)) {
                $creditModel = new CreditModel();
                $creditModel->addCreditlog(array('ruleid' => $ruleid, 'qid' => $this->qid, 'credit' => $newreward,'loginuid'=>$this->uid,'crid'=>$this->crid));
            }
            return returnData(1,'修改成功');
        }else{
            return returnData(0,'修改失败');
        }




    }

    /**
     * 读取答疑详情
     * @return array
     */
    public function detailAction(){
        $parameters['qid'] = $this->qid;

        $askQuestionModel = new AskQuestionModel();
        $result = $askQuestionModel->detail($parameters);

        if(!$result){
            return returnData(0,'该问题不存在');
        }
        $result['favorite'] = 0;
        //如果用户ID已传 查看该用户是否已经关注该问题
        if($this->uid > 0){
            $askModel = new AskQuestionModel();
            $favorite = $askModel->isFavorite($result['qid'],$this->uid);

            $result['favorite'] = $favorite > 0 ? 1 : 0;
        }

        $askQuestionModel->updateViewnum($parameters);
        $askAnswersModel = new AskAnswersModel();
        $answers = $askAnswersModel->getList($parameters);

        //如果回答不为空的 处理下回答内容
        if(!empty($answers)){
            $aids = array_column($answers,'aid');
            $logparam = array('uid' => $this->uid, 'toid' => implode(',',$aids), 'opid' => 1, 'type' => 'addthankanswer');
            $logModel = new LogModel();
            $logList = $logModel->getLaseLogTimeList($logparam);

            foreach ($answers as &$answer){
                if(isset($logList[$answer['aid']])){
                    $answer['thanks'] = 1;
                }else{
                    $answer['thanks'] = 0;
                }
            }
        }

        $result['answers'] = $answers;



        return returnData(1,'',$result);
    }

    /**
     * 设置最佳
     * @return array
     */
    public function bestAction(){
        $parameters = array('uid' => $this->uid, 'qid' => $this->qid, 'aid'=>$this->aid);
        $askModel = new AskQuestionModel();
        $question = $askModel->detail($parameters);
        $res = $askModel->setbest($parameters);

        if (!empty($res)) {
            $creditModel = new CreditModel();
            $creditModel->addCreditlog(array('ruleid'=>14,'aid'=>$this->aid,'loginuid'=>$this->uid,'crid'=>$question['crid']));
            return returnData(1,'设置成功');
        } else {
            return returnData(0,'设置失败');
        }
    }

    /**
     * 对回答进行感谢
     * @return array
     */
    public function addAnswerThankAction(){
        $qid = $this->qid;
        $uid = $this->uid;
        $aid = $this->aid;
        $logModel = new LogModel();
        $logparam = array('uid' => $uid, 'toid' => $aid, 'opid' => 1, 'type' => 'addthankanswer');//value 0为投票，不需要加入review表 1为评论 需要加入review表
        $lasttime = $logModel->getLastLogTime($logparam);
        $today = date('Y-m-d');
        $todaybegintime = strtotime($today);
        if (!empty($lasttime) && ($lasttime >= $todaybegintime)) {    //一天只能一次投票
            return returnData(0,'您今天已经感谢过了');
        }
        $param = array('qid' => $qid, 'aid' => $aid);
        $askModel = new AskQuestionModel();
        $result = $askModel->addthankanswer($param);
        if ($result > 0) {
            $logparam['message'] = '回答感谢';
            $logparam['fromip'] = $this->ip;
            $logModel->add($logparam);
            return returnData(1,'感谢成功');
        }
        return returnData(0,'感谢失败');
    }

    /**
     * 关注与取消关注
     * @return array
     */
    public function favoriteAction(){
        $qid = $this->qid;
        $uid = $this->uid;
        $flag = $this->flag;
        $param = array('uid' => $uid, 'qid' => $qid);
        $askModel = new AskQuestionModel();
        if ($flag == 1) {
            $result = $askModel->addfavorit($param);
        } else {
            $result = $askModel->delfavorit($param);
        }

        if ($result > 0) {
            return returnData(1,'操作成功');
        }else{
            return returnData(0,'操作失败');
        }
    }


}