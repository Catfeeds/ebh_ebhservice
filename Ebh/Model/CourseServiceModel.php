<?php
/**
 * 学习服务
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/11
 * Time: 14:40
 */
class CourseServiceModel
{
    function __construct()
    {
        $this->db = Ebh()->db;
    }

    /**
     * 本校课程
     * @param int $crid 网校ID
     * @param array $filterParams 筛选参数
     * @param bool $setKey 是否以itemid做为结果集的键
     * @return mixed
     */
    public function getRoomCourseList($crid, $filterParams = array(), $setKey = false) {
        $fields = array(
            '`a`.`itemid` AS `id`', '`a`.`iname`', '`a`.`iprice`', '`a`.`cannotpay`', '`a`.`pid`', '`a`.`sid`',
            '`b`.`folderid`', '`b`.`foldername`', '`b`.`img`', '`b`.`coursewarenum`', '`b`.`viewnum`', '`b`.`speaker`', '`b`.`isschoolfree`', '`b`.`showmode`', '`b`.`summary`',
            '`d`.`showbysort`',
            '`e`.`grank`', '`e`.`prank`', '`e`.`srank`'
        );
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`status`=0',
            '`b`.`del`=0',
            '`b`.`folderlevel`>1',
            '`b`.`power`=0',
            '`c`.`status`=1',
            'IFNULL(`d`.`ishide`,0)=0'
        );
        if (!empty($filterParams['pid'])) {
            $wheres[] = '`a`.`pid`='.intval($filterParams['pid']);
            if (isset($filterParams['sid'])) {
                $wheres[] = '`a`.`sid`='.intval($filterParams['sid']);
            }
        }
        if (!empty($filterParams['k'])) {
            $wheres[] = '(`b`.`foldername` LIKE '.Ebh()->db->escape('%'.$filterParams['k'].'%').' OR `a`.`iname` LIKE '.Ebh()->db->escape('%'.$filterParams['k'].'%').')';
        }
        $sql = 'SELECT '.implode(',', $fields).' FROM `ebh_pay_items` `a`
                JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid`
                JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid` 
                LEFT JOIN `ebh_pay_sorts` `d` ON `d`.`sid`=`a`.`sid` 
                LEFT JOIN `ebh_courseranks` `e` ON `e`.`folderid`=`a`.`folderid` AND `e`.`crid`=`a`.`crid` 
                WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->list_array($setKey ? 'id' : '');
    }

    /**
     * 捆绑销售分类
     * @param $sids 分类ID集
     * @return mixed
     */
    public function getTaggedList($sids) {
        if (!is_array($sids)) {
            $wheres[] = '`sid`='.$sids;
        } else {
            $wheres[] = '`sid` IN('.implode(',', $sids).')';
        }
        $sql = 'SELECT `sid` AS `id`,`pid`,`sname` AS `iname`,`sdisplayorder`,`imgurl` AS `img`,`showaslongblock`,`content` AS `summary` 
                FROM `ebh_pay_sorts` WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->list_array('id');
    }

    /**
     * 企业选课课程
     * @param int $crid 网校ID
     * @param array $filterParams 筛选条件
     * @return mixed
     */
    public function getOtherCourseList($crid, $filterParams = array()) {
        $fields = array('`a`.`sourcecrid`',
            '`c`.`itemid` AS `id`', '`c`.`iname`', '`c`.`iprice`', '`c`.`cannotpay`', '`c`.`pid`', '`c`.`sid`',
            '`d`.`folderid`', '`d`.`foldername`', '`d`.`img`', '`d`.`coursewarenum`', '`d`.`viewnum`', '`d`.`speaker`', '`d`.`showmode`', '`d`.`summary`',
            '`f`.`grank`', '`f`.`prank`', '`f`.`srank`'
        );
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`del`=0',
            '`c`.`status`=0',
            '`d`.`del`=0',
            '`d`.`power`=0',
            '`d`.`folderlevel`>1',
            '`e`.`status`=1'
        );
        if (!empty($filterParams['crid'])) {
            $wheres[] = '`a`.`sourcecrid`='.intval($filterParams['crid']);
        }
        if (!empty($filterParams['pid'])) {
            $wheres[] = '`c`.`pid`='.intval($filterParams['pid']);
        }
        if (!empty($filterParams['k'])) {
            $wheres[] = '`d`.`foldername` LIKE '.Ebh()->db->escape('%'.$filterParams['k'].'%');
        }
        $sql = 'SELECT '.implode(',', $fields).' FROM `ebh_schsourceitems` `a` 
                JOIN `ebh_schsources` `b` ON `b`.`crid`=`a`.`crid` AND `b`.`sourcecrid`=`a`.`sourcecrid`
                JOIN `ebh_pay_items` `c` ON `c`.`itemid`=`a`.`itemid` 
                JOIN `ebh_folders` `d` ON `d`.`folderid`=`c`.`folderid` 
                JOIN `ebh_pay_packages` `e` ON `e`.`pid`=`c`.`pid`
                LEFT JOIN `ebh_courseranks` `f` ON `f`.`folderid`=`d`.`folderid` AND `f`.`crid`=`d`.`crid` 
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 课程包列表
     * @param int $crid 网校ID
     * @param array $filterParams 筛选参数
     */
    public function getRoomBundleList($crid, $filterParams = array()) {
        $wheres = array(
            '`crid`='.$crid
        );
        if (!empty($filterParams['pid'])) {
            $wheres[] = '`pid`='.intval($filterParams['pid']);
            if (isset($filterParams['sid'])) {
                $wheres[] = '`sid`='.intval($filterParams['sid']);
            }
        }
        if (!empty($filterParams['k'])) {
            $wheres[] = '`name` LIKE '.Ebh()->db->escape('%'.$filterParams['k'].'%');
        }
        $sql = 'SELECT `bid` AS `id`,`name` AS `iname`,`remark` AS `summary`,`cover` AS `img`,`pid`,`sid`,`speaker`,`bprice` AS `iprice`,`displayorder` 
                FROM `ebh_bundles` WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->list_array('id');
    }

    /**
     * 课程包列表
     * @param $bids 课程包ID
     * @return mixed
     */
    public function getBundleFolderList($bids) {
        $wheres = array(
            is_array($bids) ? '`a`.`bid` IN('.implode(',', $bids).')' : '`a`.`bid`='.$bids,
            '`a`.`status`=0',
            '`a`.`astype`=0',
            '`b`.`status`=0',
            '`b`.`cannotpay`=0',
            '`c`.`power`=0',
            '`c`.`del`=0',
            '`c`.`folderlevel`>1'
        );
        $sql = 'SELECT `a`.`bid`,`b`.`cannotpay`,`c`.`folderid`,`c`.`foldername`,`c`.`viewnum`,`c`.`coursewarenum`,`c`.`showmode` FROM `ebh_bundle_assos` `a` 
                JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`asid` 
                JOIN `ebh_folders` `c` ON `c`.`folderid`=`b`.`folderid` 
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 课程包分类下课程集
     * @param $sid 课程包分类ID
     * @param $crid 网校ID
     * @param bool $setKey 是否以itemid为结果集数组键
     * @return mixed
     */
    public function getPayItemsUnderSort($sid, $crid, $setKey = true) {
        $wheres = array(
            '`a`.`sid`='.$sid,
            '`a`.`crid`='.$crid,
            '`a`.`status`=0',
            '`b`.`del`=0',
            '`b`.`power`=0',
            '`b`.`folderlevel`>1'
        );
        $sql = 'SELECT `a`.`itemid`,`a`.`iname`,`a`.`folderid`,`a`.`iprice`,`a`.`longblockimg`,`a`.`iday`,`a`.`imonth`,`a`.`cannotpay`,`b`.`foldername`,`b`.`img`,`b`.`coursewarenum`,`b`.`viewnum`,`b`.`speaker`,`b`.`isschoolfree`,IFNULL(`c`.`srank`,0) AS `srank` 
                FROM `ebh_pay_items` `a` 
                JOIN `ebh_folders` `b` ON `a`.`folderid`=`b`.`folderid`
                LEFT JOIN `ebh_courseranks` `c` ON `c`.`folderid`=`a`.`folderid` AND `c`.`crid`=`a`.`crid`
                WHERE '.implode(' AND ', $wheres).' ORDER BY `c`.`srank` ASC,`a`.`itemid` DESC';
        return Ebh()->db->query($sql)->list_array($setKey ? 'itemid' : '');
    }

    /**
     * 本校有效课程
     * @param int $crid 网校ID
     * @param array $filterParams 筛选条件
     * @param bool $setKey 是否以itemid做为结果数组键
     * @return array
     */
    public function courseList($crid, $filterParams = array(), $setKey = false) {
        $fields = array(
            '`a`.`itemid`', '`a`.`iname`', '`a`.`iprice`', '`a`.`cannotpay`', '`a`.`pid`', '`a`.`sid`', '`a`.`view_mode`',
            '`b`.`folderid`', '`b`.`foldername`', '`b`.`img`', '`b`.`coursewarenum`', '`b`.`viewnum`', '`b`.`speaker`', '`b`.`isschoolfree`', '`b`.`showmode`', '`b`.`summary`',
            '`d`.`showbysort`',
            '`e`.`grank`', '`e`.`prank`', '`e`.`srank`'
        );
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`status`=0',
            '`b`.`del`=0',
            '`b`.`folderlevel`>1',
            '`b`.`power`=0',
            '`c`.`status`=1',
            'IFNULL(`d`.`ishide`,0)=0'
        );
        if (!empty($filterParams['pid'])) {
            $wheres[] = '`a`.`pid`='.intval($filterParams['pid']);
            if (isset($filterParams['sid'])) {
                $wheres[] = '`a`.`sid`='.intval($filterParams['sid']);
            }
        }
        if (!empty($filterParams['k'])) {
            $wheres[] = '(`b`.`foldername` LIKE '.Ebh()->db->escape('%'.$filterParams['k'].'%').' OR `a`.`iname` LIKE '.Ebh()->db->escape('%'.$filterParams['k'].'%').')';
        }
        $sql = 'SELECT '.implode(',', $fields).' FROM `ebh_pay_items` `a`
                JOIN `ebh_folders` `b` ON `b`.`folderid`=`a`.`folderid`
                JOIN `ebh_pay_packages` `c` ON `c`.`pid`=`a`.`pid` 
                LEFT JOIN `ebh_pay_sorts` `d` ON `d`.`sid`=`a`.`sid` 
                LEFT JOIN `ebh_courseranks` `e` ON `e`.`folderid`=`a`.`folderid` AND `e`.`crid`=`a`.`crid` 
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array($setKey ? 'itemid' : '');
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 企业选课课程
     * @param int $crid 网校ID
     * @param array $filterParams 筛选条件
     * @param bool $setKey 是否以itemid做为结果数组键
     * @return array
     */
    public function schCourseList($crid, $filterParams = array(), $setKey = false) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`del`=0',
            '`b`.`status`=0',
            '`c`.`del`=0',
            '`c`.`power`=0',
            '`c`.`folderlevel`>1',
            '`d`.`status`=1'
        );
        if (!empty($filterParams['pid'])) {
            $wheres[] = '`b`.`pid`='.intval($filterParams['pid']);
            if (isset($filterParams['sid'])) {
                $wheres[] = '`b`.`sid`='.intval($filterParams['sid']);
            }
        }
        $sql = 'SELECT `a`.`sourcecrid`,`a`.`price`,`b`.`itemid`,`b`.`pid`,`b`.`sid`,`b`.`iname`,`c`.`img`,`c`.`summary`,`c`.`foldername`,`c`.`folderid`,`c`.`speaker`,`c`.`viewnum`,`c`.`coursewarenum`,`c`.`showmode`,`e`.`displayorder`,`f`.`grank`,`f`.`prank`,`f`.`srank` 
                FROM `ebh_schsourceitems` `a` 
                JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`itemid` 
                JOIN `ebh_folders` `c` ON `c`.`folderid`=`b`.`folderid` 
                JOIN `ebh_pay_packages` `d` ON `d`.`pid`=`b`.`pid` 
                JOIN `ebh_classrooms` `e` ON `e`.`crid`=`a`.`crid`
                LEFT JOIN `ebh_courseranks` `f` ON `f`.`folderid`=`c`.`folderid` AND `f`.`crid`=`c`.`crid` 
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array($setKey ? 'itemid' : '');

        if (empty($ret)) {
            return array();
        }
        return $ret;
    }
}