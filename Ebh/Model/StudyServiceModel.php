<?php
/**
 * 学习服务相关类
 * 课程包、打包课程、零售课程、企业选课程
 * Author: ycq
 */
class StudyServiceModel {
    /**
     * 课程包
     */
    const SERVICE_TYPE_BUNDLE = 1;
    /**
     * 课程
     */
    const SERVICE_TYPE_COURSE = 0;
    function __construct() {
        $this->db = Ebh()->db;
    }

    /**
     * 课程包详情
     * @param int $crid 网校ID
     * @param int $bid 课程包ID
     * @return mixed
     */
    public function getBundleDetail($crid, $bid) {
        $wheres = array(
            '`a`.`bid`='.$bid,
            '`a`.`crid`='.$crid,
            '`b`.`status`=1',
            'IFNULL(`c`.`ishide`,0)=0'
        );
        $sql = 'SELECT `a`.`bid`,`a`.`name`,`a`.`remark`,`a`.`cover`,`a`.`speaker`,`a`.`bprice`,`a`.`detail`,`a`.`cannotpay`,`a`.`limitnum`,`a`.`islimit`,`b`.`pid`,`b`.`pname`,`c`.`sid`,`c`.`sname` 
                FROM `ebh_bundles` `a` 
                JOIN `ebh_pay_packages` `b` ON `b`.`pid`=`a`.`pid` 
                LEFT JOIN `ebh_pay_sorts` `c` ON `c`.`sid`=`a`.`sid` 
                WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 课程包列表
     * @param int $crid 网校ID
     * @param array $params 过滤条件
     * @param mixed $limit 限量参数
     * @param bool $setKey 是否以课程包ID为返回数组键
     * @return array
     */
    public function getBundleList($crid, $params = array(), $limit = null, $setKey = false) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`dateline`>0',
            '`b`.`status`=1',
            'IFNULL(`c`.`ishide`,0)=0'
        );
        if (!empty($params['pid'])) {
            $wheres[] = '`a`.`pid`='.intval($params['pid']);
        }
        if (isset($params['sid'])) {
            $wheres[] = '`a`.`sid`='.intval($params['sid']);
        }
        if (isset($params['s']) && $params['s'] != '') {
            $wheres[] = '`a`.`name` LIKE \'%'.Ebh()->db->escape_str($params['s']).'%\'';
        }
        if (isset($params['islimit'])) {
            $wheres[] = '`a`.`islimit`='.$params['islimit'];
        }
        $top = 0;
        $offset = 0;
        if (!empty($limit)) {
            if (is_array($limit)) {
                $page = isset($limit['page']) ? intval($limit['page']) : 1;
                $page = max(1, $page);
                $top = isset($limit['pagesize']) ? intval($limit['pagesize']) : 1;
                $top = max(1, $top);
                $offset = ($page - 1) * $top;
            } else if (is_numeric($limit)){
                $top = intval($limit);
            }
        }
        $sql = 'SELECT `a`.`bid`,`a`.`name`,`a`.`remark` AS `summary`,`a`.`cover`,`a`.`pid`,`a`.`sid`,`a`.`speaker`,`a`.`bprice` AS `price`,`a`.`displayorder` AS `bdisplayorder`,`a`.`display`,`a`.`cannotpay`,`a`.`limitnum`,`a`.`islimit`,`b`.`pname`,`b`.`displayorder` AS `pdisplayorder`,`b`.`located`,`c`.`sname`,`c`.`sdisplayorder` 
                FROM `ebh_bundles` `a` 
                JOIN `ebh_pay_packages` `b` ON `b`.`pid`=`a`.`pid` 
                LEFT JOIN `ebh_pay_sorts` `c` ON `c`.`sid`=`a`.`sid` 
                WHERE '.implode(' AND ', $wheres);
        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        $ret = Ebh()->db->query($sql)->list_array($setKey ? 'bid' : '');
        return !empty($ret) ? $ret : array();
    }

    /**
     * 获取课程包服务项
     * @param mixed $bids 课程包ID
     * @return array
     */
    public function getBundleItemList($bids) {
        $wheres = array(
            '`a`.`status`=0',
            '`b`.`status`=0',
            '`c`.`del`=0',
            '`c`.`power`=0',
            '`c`.`folderlevel`>1',
            '`d`.`status`=1',
            'IFNULL(`e`.`ishide`,0)=0'
        );
        if (is_array($bids)) {
            array_unshift($wheres, '`a`.`bid` IN('.implode(',', $bids).')');
        } else {
            array_unshift($wheres, '`a`.`bid`='.$bids);
        }
        $sql = 'SELECT `a`.`bid`,`b`.`itemid`,`b`.`iname`,`b`.`iprice` AS `price`,`b`.`isummary` AS `summary`,`b`.`cannotpay`,`b`.`imonth`,`b`.`iday`,`c`.`folderid`,IF(`c`.`isschoolfree`=1 AND `c`.`crid`=`b`.`crid`,1,0) AS `isschoolfree`,`c`.`coursewarenum`,`c`.`viewnum`,`c`.`foldername`,`c`.`showmode`,`c`.`img` AS `cover`,`d`.`pid`,`d`.`pname`,`f`.`grank`,`f`.`prank`,`f`.`srank` 
                FROM `ebh_bundle_assos` `a` JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`asid` AND `a`.`astype`=0 
                JOIN `ebh_folders` `c` ON `c`.`folderid`=`b`.`folderid` 
                JOIN `ebh_pay_packages` `d` ON `d`.`pid`=`b`.`pid` 
                LEFT JOIN `ebh_pay_sorts` `e` ON `e`.`sid`=`b`.`sid` 
                LEFT JOIN `ebh_courseranks` `f` ON `f`.`folderid`=`c`.`folderid` AND `f`.`crid`=`c`.`crid`
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array();
        return !empty($ret) ? $ret : array();
    }

    /**
     * 本校课程列表
     * @param int $crid 网校ID
     * @param array $params 过滤条件
     * @param bool $setKey 是否以课程包ID为返回数组键
     * @return array 是否以服务项ID做为返回数组的键
     */
    public function getSchoolCourseList($crid, $params = array(), $setKey = false) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`status`=0',
            '`b`.`del`=0',
            '`b`.`power`=0',
            '`b`.`folderlevel`>1',
            '`c`.`status`=1',
            'IFNULL(`d`.`ishide`,0)=0'
        );
        if (!empty($params['pid'])) {
            $wheres[] = '`a`.`pid`='.$params['pid'];
            if (isset($params['sid'])) {
                $wheres[] = '`a`.`sid`='.$params['sid'];
            }
        }
        $sql = 'SELECT `a`.`pid`,`a`.`sid`,`a`.`itemid`,`a`.`iprice` AS `price`,`a`.`cannotpay`,`a`.`folderid`,`a`.`iname`,`a`.`isummary` AS `summary`,`a`.`view_mode`,`a`.`islimit`,`a`.`limitnum`,`a`.`imonth`,`a`.`iday`,`b`.`foldername`,`b`.`img` AS `cover`,`b`.`viewnum`,`b`.`coursewarenum`,IF(`b`.`isschoolfree`=1 AND `b`.`crid`=`a`.`crid`,1,0) AS `isschoolfree`,`b`.`crid` AS `fcrid`,`b`.`showmode`,`b`.`speaker`,`c`.`pname`,`c`.`displayorder` AS `pdisplayorder`,`c`.`crid` AS `pcrid`,`c`.`located`,`d`.`sname`,`d`.`showbysort`,`d`.`sdisplayorder`,`e`.`grank`,`e`.`prank`,`e`.`srank` 
                FROM `ebh_pay_items` `a` JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid`
                JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid`
                LEFT JOIN `ebh_pay_sorts` `d` ON `d`.`sid`=`a`.`sid`
                LEFT JOIN `ebh_courseranks` `e` ON `e`.`folderid`=`b`.`folderid` and `e`.`crid`=`b`.`crid`
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array($setKey ? 'itemid' : '');
        return empty($ret) ? array() : $ret;
    }

    /**
     * 企业选课课程列表
     * @param int $crid 网校ID
     * @param array $params 过滤条件
     * @param bool $setKey 是否以服务项ID做为返回数组的键
     * @return array
     */
    public function getOtherCourseList($crid, $params = array(), $setKey = false) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`del`=0',
            '`b`.`status`=0',
            '`d`.`del`=0',
            '`d`.`power`=0',
            '`d`.`folderlevel`>1',
            '`e`.`status`=1',
            'IFNULL(`f`.`ishide`,0)=0'
        );
        if (!empty($params['pid'])) {
            $wheres[] = '`b`.`pid`='.$params['pid'];
            if (isset($params['sid'])) {
                $wheres[] = '`b`.`sid`='.$params['sid'];
            }
        }
        $sql = 'SELECT `a`.`price`,`b`.`pid`,`b`.`sid`,`b`.`itemid`,`b`.`iprice`,`b`.`folderid`,`b`.`iname`,`b`.`isummary` AS `summary`,`c`.`crid`,`c`.`crname`,`c`.`displayorder`,`d`.`foldername`,`d`.`img` AS `cover`,`d`.`coursewarenum`,`d`.`viewnum`,`d`.`showmode`,`d`.`speaker`,`e`.`pname`,`e`.`displayorder` AS `pdisplayorder`,`f`.`sname`,`f`.`sdisplayorder`,`g`.`grank`,`g`.`prank`,`g`.`srank` 
                FROM `ebh_schsourceitems` `a` 
                JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`itemid` 
                JOIN `ebh_classrooms` `c` ON `c`.`crid`=`a`.`sourcecrid` 
                JOIN `ebh_folders` `d` ON `d`.`folderid`=`b`.`folderid` 
                JOIN `ebh_pay_packages` `e` ON `e`.`pid`=`b`.`pid`
                LEFT JOIN `ebh_pay_sorts` `f` ON `f`.`sid`=`b`.`sid` 
                LEFT JOIN `ebh_courseranks` `g` ON `g`.`folderid`=`d`.`folderid` AND `g`.`crid`=`c`.`crid`
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array($setKey ? 'itemid' : '');
        return !empty($ret) ? $ret : array();
    }

    /**
     * 获取分类信息
     * @param array $sids 服务包分类ID集
     * @return mixed
     */
    public function getSortDetails($sids) {
        $sql = 'SELECT `sid`,`pid`,`imgurl` AS `cover`,`content` AS `summary`,`sdisplayorder` FROM `ebh_pay_sorts` WHERE `sid` IN('.implode(',', $sids).')';
        return Ebh()->db->query($sql)->list_array('sid');
    }

    /**
     * 打包分类详情
     * @param int $sid ID
     * @return mixed
     */
    public function getSortDetail($sid) {
        $wheres = array(
            '`a`.`sid`='.$sid,
            '`a`.`showbysort`=1',
            '`a`.`ishide`=0',
            '`b`.`status`=1'
        );
        $sql = 'SELECT `a`.`sid`,`a`.`sname`,`a`.`content`,`a`.`imgurl`,`b`.`pid`,`b`.`pname` FROM `ebh_pay_sorts` `a` JOIN `ebh_pay_packages` `b` ON `b`.`pid`=`a`.`pid` WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 自选课程列表
     * @param int $crid 网校ID
     * @param bool $setKey 是否以课程包ID为返回数组键
     * @return array
     */
    public function getManualCourseList($crid, $setKey = false) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`b`.`status`=0',
            '`c`.`del`=0',
            '`c`.`power`=0',
            '`c`.`folderlevel`>1',
            '`d`.`status`=1',
            'IFNULL(`e`.`ishide`,0)=0'
        );
        $sql = 'SELECT `b`.`itemid`,`b`.`iname`,`b`.`isummary` AS `summary`,`b`.`iprice` AS `price`,IF(`b`.`cannotpay`=1 AND `b`.`crid`=`a`.`crid`,1,0) AS `cannotpay`,`b`.`limitnum`,IF(`b`.`islimit`=1 AND `b`.`crid`=`a`.`crid`,1,0) AS `islimit`,`c`.`folderid`,`c`.`foldername`,IF(`c`.`isschoolfree`=1 AND `c`.`crid`=`a`.`crid`,1,0) AS `isschoolfree`,`c`.`img` AS `cover`,`c`.`coursewarenum`,`c`.`viewnum`,`c`.`speaker`,`d`.`pid`,`d`.`pname` 
                FROM `ebh_manual_courses` `a` 
                JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`itemid` 
                JOIN `ebh_folders` `c` ON `c`.`folderid`=`b`.`folderid` 
                JOIN `ebh_pay_packages` `d` ON `d`.`pid`=`b`.`pid` 
                LEFT JOIN `ebh_pay_sorts` `e` ON `e`.`sid`=`b`.`sid`
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`order` DESC,`a`.`dateline` ASC';
        $ret = Ebh()->db->query($sql)->list_array($setKey ? 'itemid' : '');
        return !empty($ret) ? $ret : array();
    }

    /**
     * 单课列表
     * @param int $crid 网校ID
     * @param int $folderid 课程ID
     * @param null $limit 限量条件
     * @return array
     */
    public function getFineList($crid, $folderid = 0, $limit = null) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`c`.`crid`='.$crid,
            '`cwpay`=1',
            '`b`.`status`=1',
            '`c`.`del`=0',
            '`c`.`power`=0',
            '`c`.`folderlevel`>1'
        );
        if ($folderid > 0) {
            $wheres[] = '`c`.`folderid`='.$folderid;
        }
        $offset = 0;
        $top = 0;
        if ($limit !== null) {
            if (is_array($limit)) {
                $page = isset($limit['page']) ? intval($limit['page']) : 1;
                $page = max(1, $page);
                $top = isset($limit['pagesize']) ? intval($limit['pagesize']) : 1;
                $top = max(1, $top);
                $offset = ($page - 1) * $top;
            } else if (is_numeric($limit)){
                $top = intval($limit);
            }
        }
        $sql = 'SELECT `a`.`cprice`,`a`.`isfree`,`a`.`cdisplayorder`,`b`.`cwid`,`b`.`title`,`b`.`logo`,`b`.`summary`,`b`.`viewnum`,`b`.`dateline`,`b`.`islive`,`b`.`reviewnum`,`b`.`truedateline`,`b`.`submitat`,`b`.`endat`,`c`.`folderid`,`c`.`foldername`,`d`.`uid`,`d`.`username`,`d`.`realname`,`d`.`groupid`,`d`.`face`,`d`.`sex`,`e`.`grank` 
                FROM `ebh_roomcourses` `a` 
                JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` 
                JOIN `ebh_folders` `c` ON `c`.`folderid`=`a`.`folderid` 
                JOIN `ebh_users` `d` ON `d`.`uid`=`b`.`uid` 
                LEFT JOIN `ebh_courseranks` `e` ON `e`.`folderid`=`c`.`folderid` AND `e`.`crid`=`c`.`crid` 
                WHERE '.implode(' AND ', $wheres);
        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        $ret = Ebh()->db->query($sql)->list_array();
        return !empty($ret) ? $ret : array();
    }

    /**
     * 报名统计
     * @param int $crid 网校ID
     * @param mixed $ids 课程或课程包ID
     * @param int $uid 当前用户ID
     * @param int $type 统计类型
     * @return mixed
     */
    public function reportCount($crid, $ids, $uid = 0, $type = self::SERVICE_TYPE_COURSE) {
        if (empty($ids)) {
            return array();
        }
        $wheres = array(
            '`crid`='.$crid,
            '`dstatus`=1'
        );
        if ($uid > 0) {
            $wheres[] = '`uid`<>'.$uid;
        }
        if ($type == self::SERVICE_TYPE_BUNDLE) {
            if (is_array($ids)) {
                $wheres[] = '`bid` IN('.implode(',', $ids).')';
            } else {
                $wheres[] = '`bid`='.$ids;
            }
            $sql = 'SELECT COUNT(DISTINCT `uid`) AS `c`,`bid` FROM `ebh_pay_orderdetails` WHERE '.implode(' AND ', $wheres);
            return Ebh()->db->query($sql)->list_array('bid');
        }
        $wheres[] = '`bid`=0';
        if (is_array($ids)) {
            $wheres[] = '`itemid` IN('.implode(',', $ids).')';
        } else {
            $wheres[] = '`itemid`='.$ids;
        }
        $sql = 'SELECT COUNT(DISTINCT `uid`) AS `c`,`itemid` FROM `ebh_pay_orderdetails` WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->list_array('itemid');
    }

    private function format($s) {
        $s = strtr($s, array('`'=>''));
        $s = preg_replace_callback('/(\bgroup\b|\bselect\b|\bon\b|\border\b|\band\b|\bor\b|\bfrom\b|\bjoin\b|\bif\b|\bleft\b|\bas\b|\bwhere\b|\basc\b|\bdesc\b|\bby\b|\bifnull\b|\bunion\b|\bin\b)|(\b[a-z\_A-Z]+\b)/i', function($m) {
            if (count($m) == 2) {
                return strtoupper($m[0]);
            }
            return '`'.$m[0].'`';
        }, $s);
        return $s;
    }
}