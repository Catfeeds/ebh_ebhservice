<?php

/**
 * 用户终端类
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/21
 * Time: 17:39
 */
class UserClientModel {

    /*
	设备绑定信息
	@param array $param
	@return int
	*/
    public function add($param){
        if(empty($param['uid']))
            return FALSE;
        $setarr = array();
        $setarr['uid'] = $param['uid'];
        if(!empty($param['crid']))
            $setarr['crid'] = $param['crid'];
        if(!empty($param['ismobile']))
            $setarr['ismobile'] = $param['ismobile'];
        if(!empty($param['system']))
            $setarr['system'] = $param['system'];
        if(!empty($param['browser']))
            $setarr['browser'] = $param['browser'];
        if(!empty($param['broversion']))
            $setarr['broversion'] = $param['broversion'];
        if(!empty($param['screen']))
            $setarr['screen'] = $param['screen'];
        if(!empty($param['ip']))
            $setarr['ip'] = $param['ip'];
        if(!empty($param['vendor']))
            $setarr['vendor'] = $param['vendor'];
        if(!empty($param['dateline']))
            $setarr['dateline'] = $param['dateline'];
        if(!empty($param['lasttime']))
            $setarr['lasttime'] = $param['lasttime'];
        if(!empty($param['isext']))
            $setarr['isext'] = $param['isext'];
        if(empty($setarr))
            return FALSE;
        $clientid = Ebh()->db->insert('ebh_userclients',$setarr);
        return $clientid;
    }
    /**
     * 根据用户编号获取用户设备绑定信息
     * @param int $uid 用户uid
     */
    public function getClientsByUid($uid,$crid) {
        $sql = "select clientid,crid,ismobile,system,browser,broversion,screen,ip,dateline,lasttime,isext,vendor from ebh_userclients where uid=$uid and crid=$crid";
        return Ebh()->db->query($sql)->list_array();
    }
    /**
     * 登录限制列表
     * @param $crid 网校ID
     * @param null $k 查询关键字
     * @param int $limit 分页参数
     * @return mixed
     */
    public function getClientList($crid, $k = null, $limit = 20) {
        $whereArr = array();
        $whereArr[] = '`crid`='.$crid;
        $whereArr[] = '`b`.`uid` IS NOT NULL';
        if (!empty($k)) {
            if (is_numeric($k)) {
                $whereArr[] = '`b`.`uid`='.intval($k);
            } else {
                $whereArr[] = '(`b`.`realname` LIKE '.Ebh()->db->escape('%'.$k.'%').' OR `b`.`username` LIKE '.Ebh()->db->escape('%'.$k.'%').')';
            }
        }
        $offset = 0;
        $pagesize = 20;
        if (is_array($limit)) {
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
        $sql = 'SELECT `a`.`uid`,GROUP_CONCAT(DISTINCT `a`.`system`,\' \', `a`.`browser`,\'浏览器\') AS `ip`,`b`.`username`,`b`.`realname`,`b`.`sex`,`b`.`groupid`'.
            ' FROM `ebh_userclients` `a` LEFT JOIN `ebh_users` `b` ON `a`.`uid`=`b`.`uid` WHERE '.implode(' AND ', $whereArr).' GROUP BY `a`.`uid`'.
            ' LIMIT '.$offset.','.$pagesize;
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 登录限制数量
     * @param $crid 网校ID
     * @param null $k 查询关键字
     * @return int
     */
    public function getClientCount($crid, $k = null) {
        $whereArr = array();
        $whereArr[] = '`crid`='.$crid;
        $whereArr[] = '`b`.`uid` IS NOT NULL';
        if (!empty($k)) {
            if (is_numeric($k)) {
                $whereArr[] = '`b`.`uid`='.intval($k);
            } else {
                $whereArr[] = '(`b`.`realname` LIKE '.Ebh()->db->escape('%'.$k.'%').' OR `b`.`username` LIKE '.Ebh()->db->escape('%'.$k.'%').')';
            }
        }
        $sql = 'SELECT COUNT(DISTINCT `a`.`uid`) AS `c` FROM `ebh_userclients` `a` LEFT JOIN `ebh_users` `b` ON `a`.`uid`=`b`.`uid` WHERE '.implode(' AND ', $whereArr);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return intval($ret['c']);
        }
        return 0;
    }

    /**
     * 取消登录限制
     * @param $crid 网校ID
     * @param $uids 用户ID
     * @return bool
     */
    public function remove($crid, $uids) {
        if (empty($uids)) {
            return false;
        }
        $where = array('`crid`='.intval($crid));
        if (is_array($uids)) {
            $uids = array_map(function($uid) {
                return (int) $uid;
            }, $uids);
            $where[] = '`uid` IN('.implode(',', $uids).')';
        } else {
            $where[] = '`uid`='.intval($uids);
        }
        $sql = 'DELETE FROM `ebh_userclients` WHERE '.implode(' AND ', $where);
        Ebh()->db->query($sql, FALSE);
        if (Ebh()->db->is_fail() === true) {
            return false;
        }
        return Ebh()->db->affected_rows();
    }
}