<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:36
 * 获取直播课件信息
 */
class LiveController extends Controller{
    public function parameterRules(){
        return array(
            'infoAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1),
                'type'  =>  array('name'=>'type','default'=>'normal')
            )
        );
    }

    /**
     * 读取直播信息
     * @return array
     */
    public function infoAction(){
        $userModel = new UserModel();

        $userInfo = $userModel->getUserByUid($this->uid);

        if(!$userInfo){
            return returnData('0','用户不存在');
        }


        if($userInfo['groupid'] == 5){
            return $this->doTeacherLiveInfo($userInfo,$this->cwid,$this->type);
        }

        if($userInfo['groupid'] == 6){
            return $this->doStudentLiveInfo($userInfo,$this->cwid,$this->type);
        }
    }


    /**
     * 读取学生直播信息
     * @param $user
     * @param $cwid
     * @param $type
     * @return array
     */
    private function doStudentLiveInfo($user,$cwid,$type){
        $coursemodel = new CoursewareModel();
        $course = $coursemodel->getLiveCourse($cwid);
        if(empty($course) || $course['status'] != 1) {
            return returnData('0','课程不存在或已删除');
        }
        $liveInfoModel = new LiveinfoModel();
        $liveconfig = Ebh()->config->get('live');
        $live = $liveInfoModel->getLiveInfoByCwid($course['cwid']);
        if(!$live){//如果直播信息不存在 则直接读取Sata信息
            $live['httppullurl'] = '';
            $live['hlspullurl'] = $liveconfig['Sata']['hlsPurllUrl'];
            $live['rtmppullurl'] = $liveconfig['Sata']['rtmpPullUrl'];
            $live['pushurl'] = $liveconfig['Sata']['pushUrl'];
        }

        $liveinfo = array();

        $liveid = $course['liveid'];

        $docplay = str_replace('[liveid]',$course['liveid'].'s',$live['rtmppullurl']);
        $camplay = str_replace('[liveid]',$course['liveid'].'c',$live['rtmppullurl']);

        $hlsdocplay = str_replace('[liveid]',$course['liveid'].'s',$live['hlspullurl']);
        $hlscamplay = str_replace('[liveid]',$course['liveid'].'c',$live['hlspullurl']);

        $imurl = $liveconfig['imurl_app'];
        $key = $this->getKey($user);
        $imurl = str_replace('[key]',$key,$imurl);

        $liveinfo['docplay'] = $docplay;	//教师文档播放地址
        $liveinfo['camplay'] = $camplay;	//教师摄像头播放地址
        $liveinfo['hlsdocplay'] = $hlsdocplay;	//教师文档播放地址
        $liveinfo['hlscamplay'] = $hlscamplay;	//教师摄像头播放地址
        $liveinfo['title'] = $course['title'];	//课程名称
        $truedateline = $course['truedateline'];
        $liveinfo['starttime'] = date('Y/m/d H:i:s',$truedateline);	//开课时间
        $endat = $course['endat'];
        $liveinfo['endtime'] = date('Y/m/d H:i:s',$endat);			//结束时间
        $liveinfo['systime'] = date('Y/m/d H:i:s',SYSTIME);	//系统时间戳
        $liveinfo['im'] = $imurl;	//聊天室地址
        $liveinfo['key'] = $key;
        $liveinfo['review'] = $live['review'];
        return returnData(1,'',$liveinfo);
    }
    /**
     * 读取教师直播信息
     * @param $user
     * @param $cwid
     * @param $type
     * @return array
     */
    private function doTeacherLiveInfo($user,$cwid,$type){
        $coursemodel = new CoursewareModel();
        $course = $coursemodel->getLiveCourse($cwid);
        if(empty($course) || $course['status'] != 1) {
            return returnData('0','课程不存在或已删除');
        }
        $liveInfoModel = new LiveinfoModel();
        $liveconfig = Ebh()->config->get('live');
        $live = $liveInfoModel->getLiveInfoByCwid($course['cwid']);
        if(!$live){//如果直播信息不存在 则直接读取Sata信息
            $live['httppullurl'] = '';
            $live['hlspullurl'] = $liveconfig['Sata']['hlsPurllUrl'];
            $live['rtmppullurl'] = $liveconfig['Sata']['rtmpPullUrl'];
            $live['pushurl'] = $liveconfig['Sata']['pushUrl'];
        }

        $liveinfo = array();

        $liveid = $course['liveid'];

        $docpub = str_replace('[liveid]',$course['liveid'].'s',$live['pushurl']);
        $campub = str_replace('[liveid]',$course['liveid'].'c',$live['pushurl']);

        $docplay = str_replace('[liveid]',$course['liveid'].'s',$live['rtmppullurl']);
        $camplay = str_replace('[liveid]',$course['liveid'].'c',$live['rtmppullurl']);

        if($type == 'app'){
            $imurl = $liveconfig['imurl_app'];
        }else{
            $imurl = $liveconfig['imurl'];
        }
        $key = $this->getKey($user);
        $imurl = str_replace('[key]',$key,$imurl);
        $liveinfo['docpub'] = $docpub;	//教师文档推流地址
        $liveinfo['campub'] = $campub;	//教师摄像头推流地址
        $liveinfo['docplay'] = $docplay;	//教师文档播放地址
        $liveinfo['camplay'] = $camplay;	//教师摄像头播放地址
        $liveinfo['user'] = $user['username'];	//主讲老师账号
        $liveinfo['name'] = empty($user['realname']) ? $user['username'] : $user['realname'];		//主讲老师姓名
        $liveinfo['title'] = $course['title'];	//课程名称
        $liveinfo['num'] = 0;	//课件预估时间，预留
        $truedateline = $course['truedateline'];
        $liveinfo['starttime'] = date('Y/m/d H:i:s',$truedateline);	//开课时间
        $endat = $course['endat'];
        $liveinfo['endtime'] = date('Y/m/d H:i:s',$endat);			//结束时间
        $liveinfo['systime'] = date('Y/m/d H:i:s',SYSTIME);	//系统时间戳
        $liveinfo['im'] = $imurl;	//聊天室地址
        $liveinfo['key'] = $key;
        $liveinfo['review'] = $live['review'];
        return returnData(1,'',$liveinfo);
    }


    private function getKey($user){
        $uid = $user['uid'];
        $pwd = $user['password'];
        $ip = getip();
        $time = SYSTIME;
        $skey = "$pwd\t$uid\t$ip\t$time";
        $auth = authcode($skey, 'ENCODE');
        return $auth;
    }
}