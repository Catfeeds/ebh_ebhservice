<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:34
 * Sns同步类
 */
class Sns {
    private $roomlist = NULL;

    /**
     * 获取网校列表
     * @return null|所有网校列表
     */
    public function getRoomList() {
        if(isset($this->roomlist)) {
            return $this->roomlist;
        }
        $classroomModel = new ClassRoomModel();

        $this->roomlist = $classroomModel->getClassRoomListAll();

        return $this->roomlist;
    }

    /**
     *初始化所有网校基本信息缓存 crid domain crname
     *网校基本信息redis hash key格式为 room_info_crid
     */
    public function initRoominfoCache() {
        $redis =  Ebh()->cache->getRedis();
        $roomlist = $this->getRoomList();
        foreach($roomlist as $room) {
            //设置网校基本信息redis
            $roominfokey = 'room_info_'.$room['crid'];
            $roominfovalue = array('domain'=>$room['domain'],'crname'=>$room['crname'],'cface'=>$room['cface']);
            $redis->hMset($roominfokey,$roominfovalue);
        }
    }
    /**
     *初始化所有网校用户(包括学生和教师)对应缓存key
     *网校学生redis hash key格式为 room_ulist_crid
     */
    public function initRoomUserCache(){
        $roomlist = $this->getRoomList();
        $roomuserModel  = new RoomUserModel();
        $roomteacherModel = new RoomTeacherModel();
        $redis =  Ebh()->cache->getRedis();
        foreach($roomlist as $room) {
            //设置网校学生关联信息redis
            $crid = $room['crid'];
            $pagesize = 1000;
            $page = 1;
            $roomuserkey = 'room_ulist_'.$crid;
            $count = $roomuserModel->getRoomUserCount(array('crid'=>$crid));
            $curcount = $count;
            while($curcount > 0) {
                $ulist = array();
                $urows = $roomuserModel->getUserIdList($crid,$page,$pagesize);
                foreach($urows as $urow) {
                    $ulist[$urow['uid']] = $urow['uid'];
                }
                $redis->hMset($roomuserkey,$ulist);
                $curcount -= $pagesize;
                $page ++;
            }

            //设置网校教师关联信息redis
            $page = 1;
            $count = $roomteacherModel->getRoomTeacherCount(array('crid'=>$crid));
            $curcount = $count;
            while($curcount > 0) {
                $ulist = array();
                $urows = $roomteacherModel->getTeacheIdList($crid,$page,$pagesize);
                foreach($urows as $urow) {
                    $ulist[$urow['tid']] = $urow['tid'];
                }
                $redis->hMset($roomuserkey,$ulist);
                $curcount -= $pagesize;
                $page ++;
            }
        }
    }

    /**
     *初始化所有班级学生对应缓存key
     *网校学生redis hash key格式为 class_ulist_classid
     */
    public function initClassuserCache() {
        $roomlist = $this->getRoomList();
        $classesModel = new ClassesModel();
        $redis =  Ebh()->cache->getRedis();
        foreach($roomlist as $room) {
            $crid = $room['crid'];
            $classlist = $classesModel->getRoomClassList($crid);
            foreach($classlist as $myclass) {
                $classid = $myclass['classid'];
                $classuserkey = 'class_ulist_'.$classid;
                $ulist = array();
                $urows = $classesModel->getClassStudentUid($classid);
                foreach($urows as $urow) {
                    $ulist[$urow['uid']] = $urow['uid'];
                }
                //获取班级教师ID列表
                $urows = $classesModel->getClassTeacherUid($classid);
                foreach($urows as $urow) {
                    $ulist[$urow['uid']] = $urow['uid'];
                }
                $redis->hMset($classuserkey,$ulist);
            }
        }
    }

    //更新学校信息
    public function updateClassRooomCache($param){
        $redis =  Ebh()->cache->getRedis();
        $roominfokey = 'room_info_'.$param['crid'];
        $roominfovalue = array('domain'=>$param['domain'],'crname'=>$param['crname'],'cface'=>$param['cface']);
        $redis->hMset($roominfokey,$roominfovalue);
    }

    //更新班级学生
    public function updateClassUserCache($param){
        $redis =  Ebh()->cache->getRedis();
        $classuserkey = 'class_ulist_'.$param['classid'];
        $redis->hMset($classuserkey,array($param['uid']=>$param['uid']));
    }

    //更新学校学生
    public function updateRoomUserCache($param){
        $redis =  Ebh()->cache->getRedis();
        $roomuserkey = 'room_ulist_'.$param['crid'];
        $redis->hMset($roomuserkey,array($param['uid']=>$param['uid']));
    }
    //删除班级学生
    public function delClassUserCache($param){
        $redis =  Ebh()->cache->getRedis();
        $redis->hdel('class_ulist_'.$param['classid'],$param['uid']);
    }
    //删除班级全部用户
    public function delClassUserCacheAll($param){
        $redis =  Ebh()->cache->getRedis();
        if (!empty($param['classid']))
        {
            $redis->del('class_ulist_'.$param['classid']);
        }
    }
    //删除学校学生
    public function delRoomUserCache($param){
        $redis =  Ebh()->cache->getRedis();
        $redis->hdel('room_ulist_'.$param['crid'],$param['uid']);
    }

    //批量班级学生
    public function updateClassUserCacheM($users){
        $redis =  Ebh()->cache->getRedis();
        $culist = array();
        foreach($users as $user){
            $classid = $user['classid'];
            $uid = $user['uid'];
            $culist[$classid][$uid] = $uid;

        }
        foreach($culist as $classid=>$cu){
            $classuserkey = 'class_ulist_'.$classid;

            $redis->hMset($classuserkey,$cu);
        }
    }
    //批量学校学生
    public function updateRoomUserCacheM($users){
        $redis =  Ebh()->cache->getRedis();
        $culist = array();
        foreach($users as $user){
            $crid = $user['crid'];
            $uid = $user['uid'];
            $culist[$crid][$uid] = $uid;

        }
        foreach($culist as $crid=>$cu){
            $roomuserkey = 'room_ulist_'.$crid;

            $redis->hMset($roomuserkey,$cu);
        }
    }

    //批量删除网校学生
    public function delRoomUserCacheM($roomstulist){
        $redis =  Ebh()->cache->getRedis();
        foreach($roomstulist as $rs){
            $redis->hdel('room_ulist_'.$rs['crid'],$rs['uid']);
        }
    }
    //批量删除班级学生
    public function delClassUserCacheM($clsstulist){
        $redis =  Ebh()->cache->getRedis();
        foreach($clsstulist as $cs){
            $redis->hdel('class_ulist_'.$cs['classid'],$cs['uid']);
        }
    }

    /**
     * 同步操作
     * 使用方法Ebh::app()->lib('Sns')->do_sync($uid, 1);
     * @param  int $uid  用户编号
     * @param  int $type 类型 1问题数+1,2学习数+1,3作业数+1,4网校操作（包括移动用户到新的网校，为用户添加新的网校，删除网校）,5添加用户（注册）,6删除用户,-1问题数-1,-3作业数-1。
     * @return boolean   true成功false失败
     */
    function do_sync($uid, $type)
    {
        //从配置文件获得adminkey
        $syncconfig = Ebh()->config->get('snssync');
        $adminkey = $syncconfig['adminkey'];
        if (empty($uid) || empty($type) || empty($adminkey))
        {
            return false;
        }
        //使用curl调用接口
        $url = 'http://sns.ebh.net/auth/syncoperate.html?uid=' . $uid . '&type=' . $type . '&adminkey=' . $adminkey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $ret = json_decode($output);

        if (empty($ret) || empty($ret->code) || $ret->code !== true)
        {
            log_message('SNS同步错误 url:' . $url . ' output:' . $output);
            return false;
        }
        return true;
    }

}