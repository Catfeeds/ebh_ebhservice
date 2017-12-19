<?php
/**
 * 身份审核
 * Class JsauthModel
 */
class JsauthModel{
    /**
     * 获取身份审核列表
     * @param array $filters 筛选条件
     * @param null $limit 限量条件
     * @return mixed
     */
    public function getList($filters = array(), $limit = null) {
        $wheres = array('1=1');
        if (isset($filters['aids'])) {
            $wheres[] = '`a`.`aid` IN('.implode(',', $filters['aids']).')';
        }
        if (isset($filters['status'])) {
            $wheres[] = '`a`.`status`='.$filters['status'];
        }
        if (!empty($filters['crid'])) {
            $wheres[] = '`a`.`crid`='.$filters['crid'];
        }
        if (!empty($filters['end'])) {
            $wheres[] = '`a`.`dateline`<='.$filters['end'];
        }
        if (isset($filters['start'])) {
            $wheres[] = '`a`.`dateline`>='.$filters['start'];
        }
        if (!empty($filters['access']) && empty($filters['crid'])) {
            $wheres[] = '`a`.`crid` IN('.$filters['access'].')';
        }
        $sql = 'SELECT `a`.`aid`,`a`.`crid`,`a`.`uid`,`a`.`mobile`,`a`.`idcard_z`,`a`.`idcard_b`,`a`.`status`,`a`.`ip`,`a`.`dateline`,`b`.`realname`,`b`.`username`,`b`.`mobile`,`c`.`crname`,`d`.`admin_uid`,`d`.`admin_remark`,`d`.`admin_dateline`,`d`.`admin_ip` 
                FROM `ebh_jsauths` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` 
                JOIN `ebh_classrooms` `c` ON `c`.`crid`=`a`.`crid` 
                LEFT JOIN `ebh_billchecks` `d` ON `d`.`toid`=`a`.`aid` AND `d`.`type`=16 
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`aid` DESC';
        $offset = 0;
        $top = 0;
        if (!empty($limit)) {
            if (is_array($limit)) {
                $page = isset($limit['page']) ? intval($limit['page']) : 1;
                $page = max(1, $page);
                $top = isset($limit['pagesize']) ? intval($limit['pagesize']) : 1;
                $top = max(1, $top);
                $offset = ($page - 1) * $top;
            } else {
                $top = intval($limit);
            }
        }
        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return $ret;
        }
        $uids = array_column($ret, 'admin_uid');
        $uids = array_unique($uids);
        $uids = array_filter($uids, function($uid) {
           return !empty($uid);
        });
        if (empty($uids)) {
            return $ret;
        }
        $kfdb = new Db(Ebh()->config->get('kfdb'));
        $users = $kfdb->query('SELECT `uid`,`username`,`realname` FROM `kf_user` WHERE `uid` IN('.implode(',', $uids).')')->list_array('uid');

        foreach ($ret as &$item) {
            if (isset($users[$item['admin_uid']])) {
                $item['aname'] = $users[$item['admin_uid']]['realname'];
                $item['ausername'] = $users[$item['admin_uid']]['username'];
            }
        }
        return $ret;
    }

    /**
     * 获取身份审核统计数
     * @param array $filters 筛选条件
     * @return int
     */
    public function getCount($filters = array()) {
        $wheres = array('1=1');
        if (isset($filters['aids'])) {
            $wheres[] = '`a`.`aid` IN('.implode(',', $filters['aids']).')';
        }
        if (isset($filters['status'])) {
            $wheres[] = '`a`.`status`='.$filters['status'];
        }
        if (!empty($filters['crid'])) {
            $wheres[] = '`a`.`crid`='.$filters['crid'];
        }
        if (!empty($filters['end'])) {
            $wheres[] = '`a`.`dateline`<='.$filters['end'];
        }
        if (isset($filters['start'])) {
            $wheres[] = '`a`.`dateline`>='.$filters['start'];
        }
        if (!empty($filters['access']) && empty($filters['crid'])) {
            $wheres[] = '`a`.`crid` IN('.$filters['access'].')';
        }
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_jsauths` `a` JOIN `ebh_users` `b` ON `b`.`uid`=`a`.`uid` JOIN `ebh_classrooms` `c` ON `c`.`crid`=`a`.`crid` WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return 0;
        }
        return $ret['c'];
    }

    /**
     * 审核用户身份
     * @param int $aid 用户身份验证申请ID
     * @param int $status 状态值，1-审核通过，2-审核通不过
     * @param string $remark 审核备注
     * @param int $uid 审核用户ID
     * @param string $ip 操作IP
     * @return mixed
     */
    public function audit($aid, $status, $remark, $uid, $ip) {
        Ebh()->db->begin_trans();
        //申请验证
        $sql = 'SELECT `aid` FROM `ebh_jsauths` WHERE `aid`='.$aid.' AND `status`=0';
        $auth = Ebh()->db->query($sql)->row_array();
        if (empty($auth)) {
            Ebh()->db->rollback_trans();
            return false;
        }
        //审核验证
        $sql = 'SELECT `ckid`,`admin_status` FROM `ebh_billchecks` WHERE `toid`='.$aid.' AND `type`=16 AND `del`=0 ORDER BY `ckid` DESC LIMIT 1';
        $check = Ebh()->db->query($sql)->row_array();
        if (empty($check)) {
            //生成审核记录
            $params = array(
                'toid' => $aid,
                'type' => 16,
                'admin_uid' => $uid,
                'teach_uid' => 0,
                'admin_status' => $status,
                'teach_status' => 0,
                'admin_remark' => Ebh()->db->escape_str($remark),
                'teach_remark' => '',
                'admin_dateline' => SYSTIME,
                'teach_dateline' => 0,
                'teach_ip' => '',
                'admin_ip' => $ip,
                'delline' => 0,
                'old_status' => 0,
                'del' => 0
            );
            Ebh()->db->insert('ebh_billchecks', $params);
        } else {
            //修改审核记录
            $params = array(
                'admin_uid' => $uid,
                'teach_uid' => 0,
                'admin_status' => $status,
                'teach_status' => 0,
                'admin_remark' => Ebh()->db->escape($remark),
                'teach_remark' => '',
                'admin_dateline' => SYSTIME,
                'teach_dateline' => 0,
                'teach_ip' => '',
                'admin_ip' => $ip,
                'delline' => 0,
                'old_status' => $check['admin_status'],
                'del' => 0
            );
            Ebh()->db->update('ebh_billchecks', $params, '`ckid`='.$check['ckid']);
        }
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->update('ebh_jsauths', array('status' => $status), '`aid`='.$aid);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->commit_trans();
        return true;
    }

    /**
     * 身份申请
     * @param int $aid 申请ID
     * @return mixed
     */
    public function getInfo($aid) {
        $sql = 'SELECT `c`.`notes` AS `kfnotes`,`b`.`crname`,`d`.`admin_dateline`,`d`.`admin_remark` FROM `ebh_jsauths` `a` 
                JOIN `ebh_classrooms` `b` ON `b`.`crid`=`a`.`crid`
                JOIN `ebh_jsapplys` `c` ON `c`.`aid`=`a`.`aid` 
                LEFT JOIN `ebh_billchecks` `d` ON `d`.`toid`=`a`.`aid` AND `d`.`type`=16
                WHERE `a`.`aid`='.$aid;
        return Ebh()->db->query($sql)->row_array();
    }
}