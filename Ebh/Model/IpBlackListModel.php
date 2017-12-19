<?php

/**
 * IP黑名单
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/22
 * Time: 11:05
 */
class IpBlackListModel {
    /**
     * 添加IP黑名单
     * @param $ip IP地址,int或字符串
     * @param $addr IP所在地
     * @param $remark 备注
     * @param $crid 网校ID
     * @param $operid 操作用户ID
     * @return bool|int
     */
    public function add($ip, $addr, $remark, $crid, $operid,$state = 1, $type = 3, $deny = 'LOGIN') {
        $fields = array('`ip`', '`addr`', '`remark`', '`crid`', '`operid`', '`dateline`', '`state`', '`type`', '`deny`');
        if (!is_numeric($ip)) {
            $ip = ip2long($ip);
            if ($ip === false) {
                return false;
            }
        }
        $values = array($ip, Ebh()->db->escape($addr), Ebh()->db->escape($remark), intval($crid), intval($operid), SYSTIME, $state, $type, Ebh()->db->escape($deny));
        $sql = 'INSERT INTO `ebh_blacklists`('.implode(',', $fields).') VALUES('.implode(',', $values).')';
        Ebh()->db->query($sql);
        if (Ebh()->db->is_fail() === true) {
            return false;
        }
        return $ip;
    }

    /**
     * IP黑名单列表
     * @param $crid 网校ID
     * @param null $k 搜索关键字
     * @param int $orderType 排序方式，默认0，0-日期降序，1-日期升序，2-ip降序，3-ip升序
     * @param int $limit 分页参数
     * @return mixed
     */
    public function getList($crid, $k = NULL, $orderType = 0, $limit = 20) {
        $k = strval($k);
        $whereArr[] = '`crid`='.intval($crid);
        $whereArr[] = "`ip`!=0";
        $wherearr[] = 'state=1';
        $wherearr[] = "(deny='LOGIN' or deny='All')";
        if (!empty($k)) {
            $whereArr[] = 'INET_NTOA(`ip`) LIKE '.Ebh()->db->escape('%'.$k.'%');
        }
        $offset = 0;
        $pagesize = 20;
        if (empty($limit)) {
            $pagesize = 20;
        } else if (is_array($limit)) {
            if (isset($limit['pagesize'])) {
                $pagesize = max(1, intval($limit['pagesize']));
            }
            $page = 1;
            if (isset($limit['page'])) {
                $page = max(1, intval($limit['page']));
            }
            $offset = ($page - 1) * $pagesize;
        } else {
            $pagesize = max(1, intval($limit));
        }
        $orderArr = array(
            0 => '`dateline` DESC',
            1 => '`dateline` ASC',
            2 => '`ip` DESC',
            3 => '`ip` ASC'
        );
        $orderStr = isset($orderArr[$orderType]) ? $orderArr[$orderType] : '`dateline` DESC';
        $sql = 'SELECT `ip`,`operid`,`addr`,`dateline`,`remark` FROM `ebh_blacklists` WHERE '.
            implode(' AND ', $whereArr).' ORDER BY '.$orderStr.' LIMIT '.$offset.','.$pagesize;
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * IP黑名单统计
     * @param $crid 网校ID
     * @param null $k 搜索关键字
     * @return int
     */
    public function getCount($crid, $k = NULL) {
        $k = strval($k);
        $whereArr[] = '`crid`='.intval($crid);
        $whereArr[] = "`ip`!=0";
        $wherearr[] = 'state=1';
        $wherearr[] = "(deny='LOGIN' or deny='All')";
        if (!empty($k)) {
            $whereArr[] = 'INET_NTOA(`ip`) LIKE '.Ebh()->db->escape($k.'%');
        }
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_blacklists` WHERE '.implode(' AND ', $whereArr);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['c'];
        }
        return 0;
    }

    /**
     * 删除IP黑名单
     * @param $crid 网校ID
     * @param $ips ip参数
     * @return int
     */
    public function remove($crid, $ips) {
        if (empty($ips)) {
            return 0;
        }
        $where = array('`crid`='.intval($crid));
        if (is_array($ips)) {
            $ips = array_map(function($ip) {
                return intval($ip);
            }, $ips);
            $where[] = '`ip` IN('.implode(',',$ips).')';
        } else {
            $where[] = '`ip`='.intval($ips);
        }
        $sql = 'DELETE FROM `ebh_blacklists` WHERE '.implode(' AND ', $where);
        Ebh()->db->query($sql, FALSE);
        if (Ebh()->db->is_fail() === true) {
            return false;
        }
        return Ebh()->db->affected_rows();
    }

    /**
     * 校验IP是否已经存在于黑名单
     */
    public function ipIsExists($ip,$crid){
        $sql = "select count(1) as count from ebh_blacklists where ip=$ip and crid=$crid";
        return Ebh()->db->query($sql)->row_array();
    }
}