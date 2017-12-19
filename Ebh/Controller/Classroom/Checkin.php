<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:26
 */
class CheckinController extends Controller{

    public function parameterRules(){
        return array(
            'msgAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1),
            ),
        );
    }

    /**
     * 短信通知未签到用户
     */
    public function msgAction(){
        //fastcgi_finish_request();
        $cwid = $this->cwid;
        $classroomModel = new ClassRoomModel();
        $redis = Ebh()->cache->getRedis();
        $sendCount = intval($redis->get('chatroom_checkin_sms_'.$this->cwid));
        if($sendCount >= 2){
            return returnData(0,'通知次数超过限制');
        }
        echo json_encode(array(
            'ret'   =>  200,
            'data'  =>  returnData(1,'开始通知'),
            'msg'   =>  ''
        ));
        fastcgi_finish_request();
        $courseModel = new CoursewareModel();
        $course = $courseModel->getCourseByCwid($this->cwid);
        //获取课件网校信息
        $classroom = $classroomModel->getModel($course['crid']);
        $userpermisionMoldel = new UserpermisionsModel();
        $userList = $userpermisionMoldel->getUserList(array('crid'=>$this->crid,'folderid'=>$course['folderid']));
        //获取已签到的学生数组
        $checkinUserList = $redis->zRange('chatroom_checkin_'.$cwid,0,-1);
        //获取需要通知的用户列表
        $notifyUserList = array();
        foreach ($userList as $user){
            if($this->is_mobile($user['mobile']) && !in_array($user['uid'],$checkinUserList)){
                $notifyUserList[] = $user;
            }
        }
        //将数组分割为200个用户为一组
        $notifyList = array_chunk($notifyUserList,200);
        $smsUtil = new SmsUtil();
        foreach ($notifyList as $notify){
            $mobiles = array_column($notify,'mobile');
            $mobiles = array_unique($mobiles);
            $smsUtil->sendCheckinNotify($mobiles,$course['foldername'].'['.$course['title'].']',$course['submitat'],$classroom['domain']);
        }

        //短信发送完成后标记发送次数
        $redis->incr('chatroom_checkin_sms_'.$this->cwid);
        exit;
    }

    /**
     * 验证是否为手机号
     * @param $mobile 手机号码
     * @return bool
     */
    private function is_mobile($mobile){
        if(!preg_match("/^1[345789]\d{9}$/", $mobile)){
            return false;
        }else{
            return true;
        }
    }
}