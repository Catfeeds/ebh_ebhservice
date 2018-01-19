<?php
/**
 * 课程包
 * Author: ycq
 */
class BundleModel{
    /**
     * 课程包列表
     * @param int $crid 网校ID
     * @param array $filterParams 过滤条件
     * @param int $limits 限量条件
     * @return array
     */
    public function bundleList($crid, $filterParams = array(), $limits = 0) {
        $wheres = array('`a`.`crid`='.$crid);
        if (!empty($filterParams['pid'])) {
            $wheres[] = '`a`.`pid`='.intval($filterParams['pid']);
        }
        if (!empty($filterParams['pid']) && isset($filterParams['sid'])) {
            $wheres[] = '`a`.`sid`='.intval($filterParams['sid']);
        }
        if (!empty($filterParams['k'])) {
            $wheres[] = '`a`.`name` LIKE '.Ebh()->db->escape('%'.$filterParams['k'].'%');
        }
        if (!empty($filterParams['free'])) {
            $wheres[] = '`a`.`bprice`=0';
        }
        $offset = 0;
        $top = 0;
        if (!empty($limits)) {
            if (is_array($limits)) {
                $page = isset($limits['page']) ? intval($limits['page']) : 1;
                $top = isset($limits['pagesize']) ? intval($limits['pagesize']) : 1;
                $page = max(1, $page);
                $top = max(1, $top);
                $offset = ($page - 1) * $top;
            } else {
                $top = intval($limits);
                $top = max(1, $top);
            }
        }
        $sql = 'SELECT `a`.`bid`,`a`.`name`,`a`.`cover`,`a`.`speaker`,`a`.`remark`,`a`.`bprice`,`a`.`pid`,`a`.`sid`,`a`.`display`,`a`.`displayorder`,`a`.`cannotpay`,`a`.`limitnum`,`a`.`islimit`,`b`.`pname`,`b`.`displayorder` AS `pdisplayorder`,IFNULL(`c`.`sname`,\'其他课程\') AS `sname` 
                FROM `ebh_bundles` `a` JOIN `ebh_pay_packages` `b` ON `b`.`pid`=`a`.`pid`
                LEFT JOIN `ebh_pay_sorts` `c` ON `c`.`sid`=`a`.`sid`';
        if (!empty($filterParams['display'])) {
            $wheres[] = '`a`.`display`=1';
            $sql .= ' WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`displayorder` ASC,`a`.`bid` DESC';
        } else {
            $sql .= ' WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`bid` DESC';
        }

        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        return Ebh()->db->query($sql)->list_array('bid');
    }

    /**
     * 课程包统计
     * @param int $crid 网校ID
     * @param array $filterParams 过滤参数
     * @return int
     */
    public function bundleCount($crid, $filterParams = array()) {
        $wheres = array('`crid`='.$crid);
        if (!empty($filterParams['pid'])) {
            $wheres[] = '`pid`='.intval($filterParams['pid']);
        }
        if (!empty($filterParams['pid']) && isset($filterParams['sid'])) {
            $wheres[] = '`sid`='.intval($filterParams['sid']);
        }
        if (!empty($filterParams['k'])) {
            $wheres[] = '`name` LIKE '.Ebh()->db->escape('%'.$filterParams['k'].'%');
        }
        if (!empty($filterParams['free'])) {
            $wheres[] = '`bprice`=0';
        }
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_bundles` WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return 0;
        }
        return intval($ret['c']);
    }
    /**
     * 添加课程包
     * @param int $crid 课程ID
     * @param array $params 参数
     * @return type
     */
    public function add($crid, $params) {
        $params['crid'] = $crid;
        //同一分类下包名不能重复
        $repeat_params = array(
            '`name`='.Ebh()->db->escape($params['name']),
            '`pid`='.$params['pid'],
            '`sid`='.$params['sid'],
            '`crid`='.$crid
        );
        $bundle = Ebh()->db->query(
            'SELECT `bid` FROM `ebh_bundles` WHERE '.implode(' AND ', $repeat_params))
            ->row_array();
        if (!empty($bundle)) {
            return false;
        }
        $itemids = $params['itemids'];
        unset($params['itemids']);
        Ebh()->db->begin_trans();
        $bid = Ebh()->db->insert('ebh_bundles', $params);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        $now = SYSTIME;
        foreach ($itemids as $itemid) {
            Ebh()->db->insert('ebh_bundle_assos', array(
                'bid' => $bid,
                'asid' => $itemid,
                'astype' => 0,
                'status' => 0,
                'dateline' => $now++
            ));
            if (Ebh()->db->trans_status() === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
        }
        Ebh()->db->commit_trans();
        return $bid;
    }

    /**
     * 编辑课程包
     * @param int $bid 课程包ID
     * @param int $crid 网校ID
     * @param array $params 修改参数
     * @return bool
     */
    public function edit($bid, $crid, $params) {
        $edit_params = array();
        if (!empty($params['name'])) {
            $edit_params['name'] = $params['name'];
        }
        if (!empty($params['remark'])) {
            $edit_params['remark'] = $params['remark'];
        }
        if (isset($params['cover'])) {
            $edit_params['cover'] = $params['cover'];
        }
        if (!empty($params['pid'])) {
            $edit_params['pid'] = $params['pid'];
        }
        if (isset($params['sid'])) {
            $edit_params['sid'] = $params['sid'];
        }
        if (!empty($params['speaker'])) {
            $edit_params['speaker'] = $params['speaker'];
        }
        if (isset($params['bprice'])) {
            $edit_params['bprice'] = $params['bprice'];
        }
        if (isset($params['detail'])) {
            $edit_params['detail'] = $params['detail'];
        }
        if (isset($params['uid'])) {
            $edit_params['uid'] = $params['uid'];
        }
        if (isset($params['display'])) {
            $edit_params['display'] = $params['display'];
        }
        if (isset($params['displayorder'])) {
            $edit_params['displayorder'] = $params['displayorder'];
        }
		if (isset($params['limitnum'])) {
            $edit_params['limitnum'] = $params['limitnum'];
        }
		if (isset($params['islimit'])) {
            $edit_params['islimit'] = $params['islimit'];
        }
        if (!empty($params['name']) || !empty($params['pid']) || isset($params['sid'])) {
            //检查重复包名
            $target = Ebh()->db->query('SELECT `name`,`pid`,`sid` FROM `ebh_bundles` WHERE `bid`='.$bid.' LIMIT 1')->row_array();
            if (empty($target)) {
                return false;
            }
            $repeat_params[] = !empty($params['name']) ? '`name`='.Ebh()->db->escape($params['name']) : $target['name'];
            $repeat_params[] = !empty($params['pid']) ? '`pid`='.$params['pid'] : $target['pid'];
            $repeat_params[] = isset($params['sid']) ? '`sid`='.$params['sid'] : $target['sid'];
            $sql = 'SELECT `bid` FROM `ebh_bundles` WHERE '.implode(' AND ', $repeat_params);
            $repeat = Ebh()->db->query($sql)->row_array();
            if (!empty($repeat) && $repeat['bid'] != $bid) {
                return false;
            }
        }
        if (isset($params['cannotpay'])) {
            $edit_params['cannotpay'] = $params['cannotpay'];
        }
        Ebh()->db->begin_trans();
        $ret = Ebh()->db->update('ebh_bundles', $edit_params, '`bid`='.$bid.' AND `crid`='.$crid);
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        if (isset($params['itemids'])) {
            Ebh()->db->update('ebh_bundle_assos', array('status' => 1), array('bid' => $bid, 'astype' => 0));
            if (Ebh()->db->trans_status() === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
            $now = SYSTIME;
            foreach ($params['itemids'] as $itemid) {
                $now++;
                $values = array($bid, $itemid, 0, 0, $now);
                Ebh()->db->query('INSERT `ebh_bundle_assos`(`bid`,`asid`,`astype`,`status`,`dateline`) VALUES('.implode(',', $values).') ON DUPLICATE KEY UPDATE `status`=0,`dateline`='.$now);
                if (Ebh()->db->trans_status() === false) {
                    Ebh()->db->rollback_trans();
                    return false;
                }
            }
        }
        Ebh()->db->commit_trans();
        return $ret;
    }

    /**
     * 删除课程包
     * @param int $bid 课程包ID
     * @return mixed
     */
    public function remove($bid) {
        $ret = Ebh()->db->delete('ebh_bundles', array('bid' => $bid));
        if (!empty($ret)) {
            Ebh()->db->update('ebh_bundle_assos', array('status' => 1), array('bid' => $bid));
        }
        return $ret;
    }

    /**
     * 编辑课程包教师
     * @param int $bid 课程包ID
     * @param int $tids 教师ID集
     * @return bool
     */
    public function editTeachers($bid, $tids) {
        if (!is_array($tids)) {
            $tids = array(intval($tids));
        }
        Ebh()->db->begin_trans();
        Ebh()->db->update('ebh_bundle_assos', array('status' => 1), array('bid' => $bid, 'astype' => 1));
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        foreach ($tids as $tid) {
            $values = array($bid, $tid, 1, 0);
            Ebh()->db->query('INSERT `ebh_bundle_assos`(`bid`,`asid`,`astype`,`status`) VALUES('.implode(',', $values).') ON DUPLICATE KEY UPDATE `status`=0');
            if (Ebh()->db->trans_status() === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
        }
        Ebh()->db->commit_trans();
        return true;
    }

    /**
     * 课程包教师ID集
     * @param int $bid 课程包ID
     * @return mixed
     */
    public function teacheridList($bid) {
        $sql = 'SELECT `asid` FROM `ebh_bundle_assos` WHERE `bid`='.$bid.' AND `astype`=1 AND `status`=0';
        return Ebh()->db->query($sql)->list_field();
    }

    /**
     * 课程包课程列表
     * @param int $bid 课程包ID
     * @param bool $simple 是否显示简单信息
     * @param bool $setKey 是否以课程服务项ID为键
     * @return mixed
     */
    public function getCourseList($bid, $simple = true, $setKey = false) {
        $fields = array(
            '`b`.`itemid`',
            '`b`.`iname`',
            '`b`.`pid`',
            '`c`.`folderid`',
            '`c`.`foldername`',
            '`c`.`img`',
            '`c`.`showmode`'
        );
        if (!$simple) {
            $fields[] = '`b`.`iprice`';
            $fields[] = '`b`.`imonth`';
            $fields[] = '`b`.`iday`';
            $fields[] = '`c`.`coursewarenum`';
            $fields[] = '`c`.`viewnum`';
            $fields[] = '`c`.`summary`';
        }
        $wheres = array(
            '`a`.`bid`='.$bid,
            '`a`.`astype`=0',
            '`a`.`status`=0',
            '`b`.`status`=0',
            '`c`.`del`=0'
        );
        $sql = 'SELECT '.implode(',', $fields).' FROM `ebh_bundle_assos` `a` 
                JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`asid`  
                JOIN `ebh_folders` `c` ON `c`.`folderid`=`b`.`folderid` 
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`dateline` ASC';
        return Ebh()->db->query($sql)->list_array($setKey ? 'itemid' : '');
    }

    /**
     * 课程包课程统计信息
     * @param array $bids
     * @return array
     */
    public function courseList($bids) {
        $wheres = array(
            '`a`.`bid` IN('.implode(',', $bids).')',
            '`a`.`astype`=0',
            '`a`.`status`=0'
        );
        $sql = 'SELECT `a`.`bid`,`b`.`itemid`,`b`.`folderid`,`b`.`imonth`,`b`.`iday`,`c`.`foldername`,`c`.`coursewarenum`,`c`.`viewnum`,`c`.`showmode`,`c`.`img`,`c`.`summary`,`e`.`grank`,`e`.`prank`,`e`.`srank` 
                FROM `ebh_bundle_assos` `a`
                LEFT JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`asid` AND `b`.`status`=0 
                LEFT JOIN `ebh_folders` `c` ON `c`.`folderid`=`b`.`folderid` AND `c`.`del`=0 AND `c`.`power`=0 AND `c`.`folderlevel`>1
                LEFT JOIN `ebh_pay_packages` `d` ON `d`.`pid`=`a`.`asid` AND `d`.`status`=1
                LEFT JOIN `ebh_courseranks` `e` ON `e`.`folderid`=`c`.`folderid` AND `e`.`crid`=`c`.`crid`
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 课程包详情
     * @param int $bid 课程包ID
     * @return mixed
     */
    public function bundleDetail($bid) {
        $wheres = array(
            '`a`.`bid`='.$bid,
            '`b`.`status`=1',
            'IFNULL(`c`.`ishide`,0)=0'
        );
        $sql = 'SELECT `a`.`crid`,`a`.`bid`,`a`.`name`,`a`.`remark`,`a`.`cover`,`a`.`pid`,`a`.`sid`,`a`.`speaker`,`a`.`bprice`,`a`.`detail`,`a`.`cannotpay`,`b`.`pname`,IFNULL(`c`.`sname`,\'其他课程\') AS `sname` ,`a`.`limitnum`,`a`.`islimit`
                FROM `ebh_bundles` `a` JOIN `ebh_pay_packages` `b` ON `b`.`pid`=`a`.`pid`
                LEFT JOIN `ebh_pay_sorts` `c` ON `c`.`sid`=`a`.`sid` WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->row_array();
    }
	
	
	/**
     * 课程包简单查询
     * @param int $bid 课程包ID
     * @return mixed
     */
	public function getSimpleByBid($bid){
		if(empty($bid)){
			return FALSE;
		}
		$sql = 'select bid,name,limitnum,islimit from ebh_bundles where bid='.$bid;
		return  Ebh()->db->query($sql)->row_array();
	}
	
    /**
     * 课程包教师统计信息
     * @param array $tids 教师ID集
     * @param $crid 网校ID
     * @return mixed
     */
    public function teacherInfoList($tids, $crid) {
        $uids = implode(',', $tids);
        //课件、课时统计
        $sql = 'SELECT COUNT(1) AS `c`,SUM(`b`.`cwlength`) AS `cwlength`,`b`.`uid` 
                FROM `ebh_roomcourses` `a` JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` 
                WHERE `a`.`crid`='.$crid.' AND `b`.`uid` IN('.$uids.') AND `b`.`status`=1 GROUP BY `b`.`uid`';
        $ret['courseware'] = Ebh()->db->query($sql)->list_array('uid');
        $ret['answer'] = Ebh()->db->query('SELECT COUNT(1) AS `c`,`tid` FROM `ebh_askquestions` WHERE `crid`='.$crid.' AND `tid` IN('.$uids.') AND `answered`=1 GROUP BY `tid`')
            ->list_array('tid');
        $sql = 'SELECT COUNT(1) AS `c`,`a`.`uid` FROM `ebh_reviews` `a` JOIN `ebh_coursewares` `b` ON (`a`.`toid`=`b`.`cwid`) 
                JOIN `ebh_roomcourses` `c` ON (`b`.`cwid`=`c`.cwid) WHERE `c`.`crid`='.$crid.' AND `a`.`uid` IN('.$uids.') GROUP BY `a`.`uid`';
        $ret['review'] = Ebh()->db->query($sql)->list_array('uid');
        $sql = 'SELECT COUNT(1) AS `c`,`uid` FROM `ebh_exams` WHERE `crid`='.$crid.' AND `uid` IN('.$uids.') AND `status`=1 GROUP BY `uid`';
        $ret['exams'] = Ebh()->db->query($sql)->list_array('uid');
        return $ret;
    }

    /**
     * 教师课程包列表
     * @param int $tid
     * @param int $crid
     * @return mixed
     */
    public function teacherBundleList($tid, $crid) {
        $sql = 'SELECT `b`.`bid`,`b`.`name`,`b`.`remark`,`b`.`cover`,`b`.`speaker`,`b`.`bprice` 
                FROM `ebh_bundle_assos` `a` JOIN `ebh_bundles` `b` ON `b`.`bid`=`a`.`bid` 
                WHERE `a`.`asid`='.$tid.' AND `a`.`astype`=1 AND `a`.`status`=0 AND `b`.`crid`='.$crid;
        return Ebh()->db->query($sql)->list_array('bid');
    }

    /**
     * 修改课程包属性
     * @param array $params 属性
     * @param int $bid 课程包ID
     * @param int $crid 网校ID
     * @return mixed
     */
    public function setAttribute($params, $bid, $crid) {
        $edit_params = array();
        if (!empty($params['name'])) {
            $edit_params['name'] = $params['name'];
        }
        if (!empty($params['remark'])) {
            $edit_params['remark'] = $params['remark'];
        }
        if (isset($params['cover'])) {
            $edit_params['cover'] = $params['cover'];
        }
        if (!empty($params['pid'])) {
            $edit_params['pid'] = $params['pid'];
        }
        if (isset($params['sid'])) {
            $edit_params['sid'] = $params['sid'];
        }
        if (!empty($params['speaker'])) {
            $edit_params['speaker'] = $params['speaker'];
        }
        if (isset($params['bprice'])) {
            $edit_params['bprice'] = $params['bprice'];
        }
        if (isset($params['detail'])) {
            $edit_params['detail'] = $params['detail'];
        }
        if (isset($params['uid'])) {
            $edit_params['uid'] = $params['uid'];
        }
        if (isset($params['display'])) {
            $edit_params['display'] = $params['display'];
        }
        if (isset($params['displayorder'])) {
            $edit_params['displayorder'] = $params['displayorder'];
        }
        if (isset($params['cannotpay'])) {
            $edit_params['cannotpay'] = $params['cannotpay'];
        }
        return Ebh()->db->update('ebh_bundles', $edit_params, array('bid' => $bid, $crid=> $crid));
    }

    /**
     * 设置课程包在首页的可见性
     * @param array $addids 可见的课程包ID集
     * @param array $delids 隐藏的课程包ID集
     * @param int $crid 网校ID
     * @return bool
     */
    public function setVisibility($addids, $delids, $crid) {
        if (!empty($delids)) {
            Ebh()->db->update('ebh_bundles', array('display' => 0, 'displayorder' => 0),'`bid` IN('.implode(',', $delids).') AND `crid`='.$crid);
        }
        if (empty($addids)) {
            return true;
        }
        $now = SYSTIME;
        foreach ($addids as $addid) {
            Ebh()->db->update('ebh_bundles', array('display' => 1, 'displayorder' => $now++), '`bid`='.$addid.' AND `crid`='.$crid);
        }
        return true;
    }

    /**
     * 课程包列表
     * @param int $crid 所属网校ID
     * @param array $filterParams 筛选条件
     * @param $limits 查询限量条件
     * @return array
     */
    public function simpleList($crid, $filterParams = array(), $limits = null) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`b`.`status`=1',
            'IFNULL(`c`.`ishide`,0)=0'
        );
        if (!empty($filterParams['pid'])) {
            $wheres[] = '`a`.`pid`='.intval($filterParams['pid']);
            if (isset($filterParams['sid'])) {
                $wheres[] = '`a`.`sid`='.intval($filterParams['sid']);
            }
        }
        if (!empty($filterParams['free'])) {
            $wheres[] = '`a`.`bprice`=0';
        }
        $offset = 0;
        $top = 0;
        if (!empty($limits)) {
            if (is_array($limits)) {
                $page = isset($limits['page']) ? intval($limits['page']) : 1;
                $top = isset($limits['pagesize']) ? intval($limits['pagesize']) : 1;
                $page = max(1, $page);
                $top = max(1, $top);
                $offset = ($page - 1) * $top;
            } else {
                $top = intval($limits);
                $top = max(1, $top);
            }
        }
        $sql = 'SELECT `a`.`bid`,`a`.`name`,`a`.`cover`,`a`.`speaker`,`a`.`remark`,`a`.`bprice`,`a`.`pid`,`a`.`sid`,`a`.`displayorder`,`a`.`display`,`a`.`cannotpay`  
                FROM `ebh_bundles` `a` JOIN `ebh_pay_packages` `b` ON `b`.`pid`=`a`.`pid`
                LEFT JOIN `ebh_pay_sorts` `c` ON `c`.`sid`=`a`.`sid`';
        if (!empty($filterParams['choosed'])) {
            $wheres[] = '`a`.`display`=1';
            $sql .= ' WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`displayorder` ASC,`a`.`bid` DESC';
        } else {
            $sql .= ' WHERE '.implode(' AND ', $wheres);
        }

        if ($top > 0) {
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        $ret = Ebh()->db->query($sql)->list_array('bid');
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 获取网校课程包分类ID集
     * @param int $crid 网校ID
     * @return array
     */
    public function getCategoryIds($crid) {
        $sql = 'SELECT `a`.`pid`,`a`.`sid` FROM `ebh_bundles` `a` JOIN `ebh_bundle_assos` `b` ON `b`.`bid`=`a`.`bid` AND `b`.`astype`=0 JOIN `ebh_pay_items` `c` ON `c`.`itemid`=`b`.`asid` JOIN `ebh_folders` `d` ON `d`.`folderid`=`c`.`folderid` WHERE `a`.`crid`='.$crid.' AND `b`.`status`=0 AND `c`.`status`=0 AND `d`.`del`=0 AND `d`.`folderlevel`>1 AND `d`.`power`=0';
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }
}