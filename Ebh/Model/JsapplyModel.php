<?php
/**
 * 结算申请记录类
 */
class JsapplyModel {
    /**
     * 获取第一阶段公司审核结算申请记录列表
     * @param array $filters 筛选条件
     * @param null $limit 限量条件
     * @return mixed
     */
    public function getFirstList($filters = array(), $limit = null) {
        $wheres = array(
            'IFNULL(`c`.`status`,1)=1'
        );
        if (isset($filters['status'])) {
            $wheres[] = '`a`.`status`='.$filters['status'];
        }
        if (isset($filters['paystatus'])) {
            $wheres[] = '`a`.`paystatus`='.$filters['paystatus'];
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
        if (empty($wheres)) {
            $wheres[] = '1=1';
        }
        $sql = 'SELECT `a`.`jid`,`a`.`crid`,`a`.`realname`,`a`.`type`,`a`.`bankname`,`a`.`uname`,`a`.`isinvoice`,`a`.`money`,`a`.`moneyaftertax`,`a`.`accountnum`,`a`.`dateline`,`a`.`status`,`a`.`mstatus`,`a`.`notes`,`a`.`jsnotes`,`b`.`crname`,`d`.`admin_uid`,`d`.`admin_remark`,`d`.`admin_dateline`,`e`.`username`,`e`.`realname` AS `showname`
                FROM `ebh_jsapplys` `a` JOIN `ebh_classrooms` `b` ON `b`.`crid`=`a`.`crid`
                LEFT JOIN `ebh_jsauths` `c` ON `c`.`aid`=`a`.`aid`
                LEFT JOIN `ebh_billchecks` `d` ON `d`.`toid`=`a`.`jid` AND `d`.`type`=17 
                LEFT JOIN `ebh_users` `e` ON `e`.`uid`=`d`.`admin_uid` 
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`jid` DESC';
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
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 获取第一阶段公司审核结算申请记录统计数
     * @param array $filters 筛选条件
     * @return int
     */
    public function getFirstCount($filters = array()) {
        $wheres = array(
            'IFNULL(`c`.`status`,1)=1'
        );
        if (isset($filters['status'])) {
            $wheres[] = '`a`.`status`='.$filters['status'];
        }
        if (isset($filters['paystatus'])) {
            $wheres[] = '`a`.`paystatus`='.$filters['paystatus'];
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
        if (empty($wheres)) {
            $wheres[] = '1=1';
        }
        $sql = 'SELECT COUNT(1) AS `c`
                FROM `ebh_jsapplys` `a` JOIN `ebh_classrooms` `b` ON `b`.`crid`=`a`.`crid`
                LEFT JOIN `ebh_jsauths` `c` ON `c`.`aid`=`a`.`aid`
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`jid` DESC';
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return 0;
        }
        return $ret['c'];
    }

    /**
     * 获取第二阶段财务结算申请记录列表
     * @param array $filters 筛选条件
     * @param null $limit 限量条件
     * @return mixed
     */
    public function getSecondList($filters = array(), $limit = null) {
        $wheres = array('`a`.`status`=1', 'IFNULL(`f`.`status`,1)=1');
        if (isset($filters['status'])) {
            $wheres[] = '`a`.`mstatus`='.$filters['status'];
        }
        if (isset($filters['paystatus'])) {
            $wheres[] = '`a`.`paystatus`='.$filters['paystatus'];
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
        if (isset($filters['type'])) {
            $wheres[] = '`a`.`type`='.$filters['type'];
        }
        $sql = 'SELECT `a`.`jid`,`a`.`crid`,`a`.`realname`,`a`.`ip`,`a`.`type`,`a`.`bankname`,`a`.`uname`,`a`.`isinvoice`,`a`.`money`,`a`.`moneyaftertax`,`a`.`accountnum`,`a`.`dateline`,`a`.`mstatus` AS `status`,`a`.`paystatus`,`a`.`notes`,`a`.`jsnotes`,`b`.`crname`,`d`.`admin_dateline`,`e`.`username`,`e`.`realname` AS `showname`,`a`.`updateline`,`a`.`isprocessed`
                FROM `ebh_jsapplys` `a` JOIN `ebh_classrooms` `b` ON `b`.`crid`=`a`.`crid`
                LEFT JOIN `ebh_jsauths` `f` ON `f`.`aid`=`a`.`aid`
                LEFT JOIN `ebh_billchecks` `c` ON `c`.`toid`=`a`.`jid` AND `c`.`type`=17
                LEFT JOIN `ebh_billchecks` `d` ON `d`.`toid`=`a`.`jid` AND `d`.`type`=18
                LEFT JOIN `ebh_users` `e` ON `e`.`uid`=`d`.`admin_uid`
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`jid` DESC';
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
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 获取第二阶段财务审核结算申请记录统计数
     * @param array $filters 筛选条件
     * @return int
     */
    public function getSecondCount($filters = array()) {
        $wheres = array(
            '`a`.`status`=1',
            'IFNULL(`c`.`status`,1)=1'
        );
        if (isset($filters['status'])) {
            $wheres[] = '`a`.`mstatus`='.$filters['status'];
        }
        if (isset($filters['paystatus'])) {
            $wheres[] = '`a`.`paystatus`='.$filters['paystatus'];
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
        if (isset($filters['type'])) {
            $wheres[] = '`a`.`type`='.$filters['type'];
        }
        if (!empty($filters['access']) && empty($filters['crid'])) {
            $wheres[] = '`a`.`crid` IN('.$filters['access'].')';
        }
        $sql = 'SELECT COUNT(1) AS `c`
                FROM `ebh_jsapplys` `a` JOIN `ebh_classrooms` `b` ON `b`.`crid`=`a`.`crid` LEFT JOIN `ebh_jsauths` `c` ON `c`.`aid`=`a`.`aid`
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`jid` DESC';
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return 0;
        }
        return $ret['c'];
    }

    /**
     * 审核结算申请
     * @param int $jid 结算申请ID
     * @param int $uid 操作用户ID
     * @param int $step 第几轮审核：１-第一轮公司审核，2-第二轮财务审核
     * @param int $status 状态值，1-审核通过，2-审核通不过
     * @param string $remark 审核备注
     * @param string 操作IP
     * @return bool
     */
    public function audit($jid, $uid, $step, $status, $remark, $ip) {
        Ebh()->db->begin_trans();
        //数据有效性验证
        if ($step == 1) {
            $sql = 'SELECT `jid` FROM `ebh_jsapplys` WHERE `jid`='.$jid.' AND `status`=0';
        } else {
            $sql = 'SELECT `jid` FROM `ebh_jsapplys` WHERE `jid`='.$jid.' AND `status`=1 AND `mstatus`=0';
        }
        $apply = Ebh()->db->query($sql)->row_array();
        if (empty($apply)) {
            Ebh()->db->rollback_trans();
            return false;
        }
        //审核验证
        if ($step == 1) {
            $sql = 'SELECT `ckid`,`admin_status` FROM `ebh_billchecks` WHERE `toid`='.$jid.' AND `type`=17 AND `del`=0 ORDER BY `ckid` DESC LIMIT 1';
        } else {
            $sql = 'SELECT `ckid`,`admin_status` FROM `ebh_billchecks` WHERE `toid`='.$jid.' AND `type`=18 AND `del`=0 ORDER BY `ckid` DESC LIMIT 1';
        }
        $check = Ebh()->db->query($sql)->row_array();
        if (empty($check)) {
            //生成审核记录
            $params = array(
                'toid' => $jid,
                'type' => $step == 1 ? 17 : 18,
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
                'admin_remark' => $remark,
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
        //修改申请记录状态
        if ($step == 1) {
            $params = array(
                'status' => $status,
                'mstatus' => 0
            );
        } else {
            $params = array(
                'mstatus' => $status
            );
        }
        Ebh()->db->update('ebh_jsapplys', $params, '`jid`='.$jid);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->commit_trans();
        return true;
    }

    /**
     * 公司审核短信通知信息
     * @param array $jids 审核ID集
     * @return array
     */
    public function auditMsgForSms($jids) {
        $sql = 'SELECT `a`.`taxrat`,`a`.`dateline`,`b`.`crname` FROM `ebh_jsapplys` `a` JOIN `ebh_classrooms` `b` ON `b`.`crid`=`a`.`crid` WHERE `a`.`jid` IN('.implode(',', $jids).') AND `a`.`status`=1';
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 结算支付
     * @param int $jid 结算申请ID
     * @param int $paystatus 支付状态
     * @param string $remark 结算备注
     * @param int $uid 经办人ID
     * @param string $ip 操作IP
     * @return mixed
     */
    public function pay($jid, $paystatus, $remark, $uid, $ip) {
        return Ebh()->db->update('ebh_jsapplys', array('jsnotes' => $remark, 'jsuid' => $uid, 'paystatus' => $paystatus, 'jsip' => $ip, 'updateline' => SYSTIME), '`jid`='.$jid.' AND `type`=3');
    }

    /**
     * 编辑保存付款备注
     * @param int $jid 结算申请ID
     * @param string $payremark 付款备注
     * @return mixed
     */
    public function editPayRemark($jid, $payremark) {
        return Ebh()->db->update('ebh_jsapplys', array('jsnotes' => $payremark), '`jid`='.$jid);
    }

    /**
     * 结算申请详情
     * @param int $jid 结算申请ID
     * @param int $step 第几轮审核：１-第一轮公司审核，2-第二轮财务审核
     * @return mixed
     */
    public function getInfo($jid, $step = 1) {
        $wheres = array('`a`.`jid`='.$jid);
        if ($step == 1) {
            $sql = 'SELECT `a`.`jid`,`a`.`crid`,`a`.`realname`,`a`.`type`,`a`.`bankname`,`a`.`uname`,`a`.`isinvoice`,`a`.`money`,`a`.`moneyaftertax`,`a`.`accountnum`,`a`.`dateline`,`a`.`status`,`a`.`mstatus`,`a`.`notes`,`a`.`jsnotes`,`b`.`crname`,`c`.`admin_uid`,`c`.`admin_remark`,`c`.`admin_dateline`,`d`.`username`,`d`.`realname` AS `showname`
                FROM `ebh_jsapplys` `a` JOIN `ebh_classrooms` `b` ON `b`.`crid`=`a`.`crid`
                LEFT JOIN `ebh_billchecks` `c` ON `c`.`toid`=`a`.`jid` AND `c`.`type`=17 
                LEFT JOIN `ebh_users` `d` ON `d`.`uid`=`c`.`admin_uid` 
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`jid` DESC';
        } else {
            $sql = 'SELECT `a`.`jid`,`a`.`crid`,`a`.`realname`,`a`.`ip`,`a`.`type`,`a`.`bankname`,`a`.`uname`,`a`.`isinvoice`,`a`.`money`,`a`.`moneyaftertax`,`a`.`accountnum`,`a`.`dateline`,`a`.`mstatus` AS `status`,`a`.`paystatus`,`a`.`notes`,`a`.`jsnotes`,`b`.`crname`,`c`.`admin_remark`,`d`.`admin_remark` AS `final_remark`,`d`.`admin_dateline`,`e`.`username`,`e`.`realname` AS `showname`
                FROM `ebh_jsapplys` `a` JOIN `ebh_classrooms` `b` ON `b`.`crid`=`a`.`crid`
                LEFT JOIN `ebh_billchecks` `c` ON `c`.`toid`=`a`.`jid` AND `c`.`type`=17
                LEFT JOIN `ebh_billchecks` `d` ON `d`.`toid`=`a`.`jid` AND `d`.`type`=18
                LEFT JOIN `ebh_users` `e` ON `e`.`uid`=`d`.`admin_uid`
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`jid` DESC';
        }
        return Ebh()->db->query($sql)->row_array();
    }
	
	/**
     * 查询是否有结算申请
     * @param int $crid
     * @return bool
     */
    public function checkExists($crid) {
        $wheres = array(
            '`crid`='.$crid,
            '`status`<>2',
            '`mstatus`<>2',
            '`paystatus`=0'
        );
        $sql = 'SELECT `jid` FROM `ebh_jsapplys` WHERE '.implode(' AND ', $wheres).' LIMIT 1';
        $row = Ebh()->db->query($sql)->row_array();
        if (!empty($row['jid'])) {
            return true;
        }
        return false;
    }

	//更新付款状态
	public function updatePayStatus($param){
		if(empty($param['jids']) || empty($param['paystatus'])){
    		return false;
    	}
		$setarr['paystatus'] = $param['paystatus'];
		$setarr['updateline'] = SYSTIME;
		$setarr['jsip'] = getclientip();
		if(!empty($param['jsuid'])){
			$setarr['jsuid'] = $param['jsuid'];
		}
		$where = 'jid in ('.$param['jids'].')';
		
    	return Ebh()->db->update('ebh_jsapplys',$setarr,$where);
	}
	
	/*
	已处理状态
	*/
	public function process($param){
		$setarr['isprocessed'] = 1;
		$where = 'jid in ('.$param['jids'].')';
		return Ebh()->db->update('ebh_jsapplys',$setarr,$where);
	}
}
