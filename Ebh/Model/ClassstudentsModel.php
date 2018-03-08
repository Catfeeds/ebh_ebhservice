<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 11:19
 */
class ClassstudentsModel{

    /**
     * 通过学生用户ID获取学生的班级ID
     * @param $uid
     * @return bool
     */
    public function getClassIdByUid($uid,$crid){
        $sql = 'select cs.uid,cs.classid from ebh_classstudents cs left join ebh_classes c on c.classid=cs.classid where cs.uid='.$uid.' and c.crid='.$crid;

        $result =  Ebh()->db->query($sql)->row_array();

        if(!$result){
            return false;
        }

        return $result['classid'];
    }

    /**
     * 学生积分排行榜
     * @param int $classid 班级ID
     * @param mixed $limit 限量条件
     * @param int $orderType 0-降序，1-升序
     * @return mixed
     */
    public function getCreditRankList($classid, $limit = null, $orderType = 0) {
        $offset = 0;
        $top = 0;
        $order = $orderType == 0 ? 'DESC' : 'ASC';
        if ($limit !== null) {
            if (is_array($limit)) {
                $page = isset($limit['page']) ? intval($limit['page']) : 1;
                $page = max(1, $page);
                $top = isset($limit['pagesize']) ? intval($limit['pagesize']) : 1;
                $top = max(1, $top);
                $offset = ($page - 1) * $top;
            } else {
                $top = max(1, intval($limit));
            }
        }
        $sql = 'SELECT `b`.`username`,`b`.`realname`,`b`.`credit`,`b`.`face`,`b`.`sex`,`b`.`groupid` 
                FROM `ebh_classstudents` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid`
                JOIN `ebh_classes` `c` ON `c`.`classid`=`a`.`classid` 
                JOIN `ebh_roomusers` `d` ON `d`.`uid`=`a`.`uid` AND `d`.`crid`=`c`.`crid` 
                WHERE `a`.`classid`='.$classid.' ORDER BY `b`.`credit` '.$order;
        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 学生积分排行统计
     * @param int $classid 班级ID
     * @return int
     */
    public function getRankCount($classid) {
        $sql = 'SELECT COUNT(1) AS `c` 
                FROM `ebh_classstudents` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` 
                JOIN `ebh_classes` `c` ON `c`.`classid`=`a`.`classid` 
                JOIN `ebh_roomusers` `d` ON `d`.`uid`=`a`.`uid` AND `d`.`crid`=`c`.`crid`
                WHERE `a`.`classid`='.$classid;
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret['c'])) {
            return 0;
        }
        return $ret['c'];
    }

    /**
     * 班级学生列表
     * @param int $classid 班级ID
     * @return mixed
     */
    public function getClassStudentList($classid) {
        $sql = 'SELECT `b`.`uid`,`b`.`username`,`b`.`realname`,`b`.`credit`,`b`.`face`,`b`.`sex`,`b`.`groupid` 
                FROM `ebh_classstudents` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid`
                JOIN `ebh_classes` `c` ON `c`.`classid`=`a`.`classid` 
                JOIN `ebh_roomusers` `d` ON `d`.`uid`=`a`.`uid` AND `d`.`crid`=`c`.`crid` 
                WHERE `a`.`classid`='.$classid;
        return Ebh()->db->query($sql)->list_array('uid');
    }

    /**
     * 通过学生ID获取学生的年级ID
     * @param int $uid 学生ID
     * @param int $crid 网校ID
     * @return mixed
     */
    public function getClassInfo($uid, $crid) {
        $sql = 'SELECT `b`.`classid`,`b`.`classname`,`b`.`grade` FROM `ebh_classstudents` `a` LEFT JOIN `ebh_classes` `b` ON `b`.`classid`=`a`.`classid` WHERE `a`.`uid`='.$uid.' AND `b`.`crid`='.$crid.' LIMIT 1';
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return false;
        }
        return $ret;
    }
}