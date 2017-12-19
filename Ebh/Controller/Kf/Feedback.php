<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:06
 */
class FeedbackController extends Controller{


    const ONE_DAY_FEEDBACK_COUNT = 10;    //一个用户每日最多反馈次数

    public function parameterRules(){
        return array(
            'addAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'feedback'  =>  array('name'=>'feedback','require'=>true),
                'email'  =>  array('name'=>'email','require'=>true),
                'ip'  =>  array('name'=>'ip','require'=>true),
            ),
        );
    }

    /**
     * 添加反馈
     * @return array
     */
    public function addAction(){
        $userModel = new UserModel();
        $user = $userModel->getUserByUid($this->uid);
        if(mb_strlen($this->feedback, 'utf8') > 500){
            return returnData(0,'字数超过限制');
        }
        $param['uid'] = $user['uid'];
        $t = time();
        $param['dateline'] = $t;
        $redis =  Ebh()->cache->getRedis();
        $userlimitkey = 'VC_USER_LIMIT_'.$user['uid'];
        // 邮箱限制
        $sendCnt = $redis->zScore($userlimitkey, $user['uid']);
        if($sendCnt && $sendCnt >= self::ONE_DAY_FEEDBACK_COUNT) {
            return returnData(0,'反馈次数超过限制');
        }
        $email = $this->email;
        if(!empty($email)){
            if(is_numeric($email)){
                $email = intval($email);
                $match = preg_match("/^[1-9][0-9]{5,11}$/", $email);
//                var_dump($match);
                if(!$match){
                    $email = FALSE;
                }
            }else{
                $email = filter_var($email,FILTER_VALIDATE_EMAIL);
            }
            if($email !== FALSE){
                $param['email'] = $email;
            }
        }

        $param['loginip'] = $this->ip;
        $param['feedback'] = strip_tags($this->feedback);
        $classroomModel = new ClassRoomModel();
        $roominfo = $classroomModel->getModel($this->crid);
        $param['schoolname'] = $roominfo['crname'];
        if(is_numeric($user['groupid'])){
            $param['urole'] = intval($user['groupid']);
        }
        if($param['urole'] == 5 && $roominfo['uid'] == $user['uid']){
            $param['urole'] = 1;
        }
        $projectfbmodel = new ProjectfeedbackModel();
        $result = $projectfbmodel->add($param);
        if($result !== FALSE){
            //发送成功 设置缓存
            $this->setLimitTimes($user['uid']);
            return returnData(1,'反馈成功');
        }else{
            return returnData(0,'反馈失败');
        }
    }
    /**
     * 发送成功后设置缓存
     * @param $userid
     * @return bool
     */
    private function setLimitTimes($userid){
        $redis =  Ebh()->cache->getRedis();
        $userlimitkey = 'VC_USER_LIMIT_'.$userid;

        //设置邮箱每天限制
        $redis->zIncrBy($userlimitkey, 1, $userid);
        $redis->expireAt($userlimitkey, strtotime(date('Y-m-d',strtotime('+1 day'))));

        return true;
    }
}