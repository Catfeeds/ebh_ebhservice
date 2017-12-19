<?php

/**
 * 用户黑名称
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/22
 * Time: 11:05
 */
class UserBlackListModel {
    /**
     * 添加用户黑名单
     * @param $uid 用户ID
     * @param $username 用户账号
     * @param $realname 用户姓名
     * @param $remark 备注
     * @param $crid 网校ID
     * @param $operid 操作用户ID
     * @return bool|int
     */
    public function add($uid, $username, $remark, $crid, $operid, $state = 1, $type = 2, $deny = 'LOGIN') {
        $fields = array('`uid`', '`username`', '`remark`', '`crid`', '`operid`', '`dateline`', '`state`', '`type`', '`deny`');
        $values = array($uid, Ebh()->db->escape($username), Ebh()->db->escape($remark), intval($crid), intval($operid), SYSTIME, $state, $type, Ebh()->db->escape($deny));
        $sql = 'INSERT INTO `ebh_blacklists`('.implode(',', $fields).') VALUES('.implode(',', $values).')';
        Ebh()->db->query($sql);
        if (Ebh()->db->is_fail() === true) {
            return false;
        }
        return $uid;
    }

    /**
     * 用户黑名单列表
     * @param $crid 网校ID
     * @param null $k 搜索关键字
     * @param int $orderType 排序方式，默认0，0-日期降序，1-日期升序，2-用户ID降序，3-用户ID升序
     * @param int $limit 分页参数
     * @return mixed
     */
    public function getList($crid, $k = NULL, $orderType = 0, $limit = 20, $setKey = false) {
        $k = strval($k);
        $whereArr[] = '`crid`='.intval($crid);
        $whereArr[] = "`username`!=''";
        $wherearr[] = 'state=1';
        $wherearr[] = "(deny='LOGIN' or deny='All')";
        if (!empty($k)) {
            $whereArr[] = '`username` LIKE '.Ebh()->db->escape('%'.$k.'%');
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
            2 => '`uid` DESC',
            3 => '`uid` ASC'
        );
        $orderStr = isset($orderArr[$orderType]) ? $orderArr[$orderType] : '`dateline` DESC';
        $sql = 'SELECT `uid`,`username`,`dateline`,`remark`,`operid` FROM `ebh_blacklists` WHERE '.
            implode(' AND ', $whereArr).' ORDER BY '.$orderStr.' LIMIT '.$offset.','.$pagesize;
        if ($setKey) {
            return Ebh()->db->query($sql)->list_array('uid');
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 用户黑名单统计
     * @param $crid 网校ID
     * @param null $k 搜索关键字
     * @return int
     */
    public function getCount($crid, $k = NULL) {
        $k = strval($k);
        $whereArr[] = '`crid`='.intval($crid);
        $whereArr[] = "`username`!=''";
        $wherearr[] = 'state=1';
        $wherearr[] = "(deny='LOGIN' or deny='All')";
        if (!empty($k)) {
            $whereArr[] = '`username` LIKE '.Ebh()->db->escape($k.'%');
        }
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_blacklists` WHERE '.implode(' AND ', $whereArr);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['c'];
        }
        return 0;
    }

    /**
     * 删除用户黑名单
     * @param $crid 网校ID
     * @param $uids id参数
     * @return int
     */
    public function remove($crid, $uids) {
        if (empty($uids)) {
            return 0;
        }
        $where = array('`crid`='.intval($crid));
        if (is_array($uids)) {
            $uids = array_map(function($uid) {
                return intval($uid);
            }, $uids);
            $where[] = '`uid` IN('.implode(',',$uids).')';
        } else {
            $where[] = '`uid`='.intval($uids);
        }
        $sql = 'DELETE FROM `ebh_blacklists` WHERE '.implode(' AND ', $where);
        Ebh()->db->query($sql, FALSE);
        if (Ebh()->db->is_fail() === true) {
            return false;
        }
        return Ebh()->db->affected_rows();
    }

    /**
     * 校验用户名是否已经存在于黑名单
     */
    public function userIsExists($username,$crid){
        $sql = "select count(1) as count from ebh_blacklists where username='$username' and crid=$crid";
        return Ebh()->db->query($sql)->row_array();
    }
}