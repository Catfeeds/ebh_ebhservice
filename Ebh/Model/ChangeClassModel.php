<?php
class ChangeClassModel{
    /**
     * 返回学生当前网校下所在班级信息
     * @param $uid
     * @param $crid
     * @return bool
     */
    public function getCurrentClassForStudent($uid, $crid) {
        $uid = (int) $uid;
        $crid = (int) $crid;
        if ($uid < 1 || $crid < 1) {
            return false;
        }
        $sql = "SELECT `a`.`classid`,`a`.`classname`,`a`.`stunum` FROM `ebh_classes` `a` JOIN `ebh_classstudents` `b` " .
            "ON `a`.`classid`=`b`.`classid` AND `a`.`crid`=$crid AND `a`.`status`=0 AND `b`.`uid`=$uid";
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 我的同学列表
     * @param $classid
     * @param int $uid
     * @param int $pageindex
     * @param int $pagesize
     * @return bool
     */
    public function myClassmates($classid, $uid, $pageindex = 1, $pagesize = 20) {
        $classid = (int) $classid;
        $uid = (int) $uid;
        if ($classid < 1 || $uid < 1) {
            return false;
        }
        $pageindex = max(1, intval($pageindex));
        $pagesize = max(1, intval($pagesize));
        $offset = ($pageindex - 1) * $pagesize;
        $sql = "SELECT `uid` FROM `ebh_classstudents` WHERE `uid`<> $uid AND `classid`=$classid LIMIT $offset,$pagesize";
        $uid_arr = Ebh()->db->query($sql)->list_field('uid');
        if (empty($uid_arr)) {
            return false;
        }
        $uid_arr_str = implode(',', $uid_arr);
        unset($uid_arr);
        //$sql = "SELECT `uid`,`username`,`groupid`,`realname`,`email`,`sex`,`face`,`mobile` FROM `ebh_users` WHERE `uid` IN($uid_arr_str)";
        $sql = "SELECT `a`.`uid`,`a`.`username`,`a`.`groupid`,`a`.`realname`,`a`.`email`,`a`.`sex`,`a`.`face`,`a`.`mobile`,`b`.`phone` 
                FROM `ebh_users` `a` JOIN `ebh_members` `b` ON `a`.`uid`=`b`.`memberid` WHERE `a`.`uid` IN($uid_arr_str)";
        $classmates = Ebh()->db->query($sql)->list_array();
        if (empty($classmates)) {
            return false;
        }
        //获取关注信息
        $snsdb = getOtherDb('snsdb');
        $sql = "SELECT `disable`,`fuid` FROM `ebh_sns_follows` WHERE `uid`=$uid AND `fuid` IN($uid_arr_str)";
        if ($follows = $snsdb->query($sql)->list_array('fuid')) {
            foreach ($classmates as &$classmate) {
                if (key_exists($classmate['uid'], $follows)) {
                    $classmate['followed'] = true;
                }
            }
        }
        return $classmates;
    }

    /**
     * 班级教师人数
     * @param $classid
     * @return bool
     */
    public function classTeacherCount($classid) {
        $classid = (int) $classid;
        if ($classid < 1) {
            return false;
        }
        $sql = "SELECT COUNT(1) AS `c` FROM `ebh_classteachers` WHERE `classid`=$classid";
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return false;
        }
        return $ret['c'];
    }

    /**
     * 班级教师列表
     * @param $classid
     * @param int $uid
     * @param int $pageindex
     * @param int $pagesize
     * @return bool
     */
    public function classTeacher($classid, $uid, $pageindex = 1, $pagesize = 20) {
        $classid = (int) $classid;
        $uid = (int) $uid;
        if ($classid < 1 || $uid < 1) {
            return false;
        }
        $pageindex = max(1, intval($pageindex));
        $pagesize = max(1, intval($pagesize));
        $offset = ($pageindex - 1) * $pagesize;
        $sql = "SELECT `uid` FROM `ebh_classteachers` WHERE `classid`=$classid LIMIT $offset,$pagesize";
        $uid_arr = Ebh()->db->query($sql)->list_field('uid');
        if (empty($uid_arr)) {
            return false;
        }
        $uid_arr_str = implode(',', $uid_arr);
        unset($uid_arr);
        $sql = "SELECT `uid`,`username`,`groupid`,`realname`,`email`,`sex`,`face`,`mobile` FROM `ebh_users` WHERE `uid` IN($uid_arr_str)";
        $teachers =  Ebh()->db->query($sql)->list_array();

        //获取关注信息
        $snsdb = getOtherDb('snsdb');
        $sql = "SELECT `disable`,`fuid` FROM `ebh_sns_follows` WHERE `uid`=$uid AND `fuid` IN($uid_arr_str)";
        if ($follows = $snsdb->query($sql)->list_array('fuid')) {
            foreach ($teachers as &$teacher) {
                if (key_exists($teacher['uid'], $follows)) {
                    $teacher['followed'] = true;
                }
            }
        }
        return $teachers;
    }

    /**
     * 关注
     * @param $uid
     * @param $fuid
     * @return bool
     */
    public function follow($uid, $fuid) {
        $uid = (int) $uid;
        $fuid = (int) $fuid;
        if ($uid < 1 || $fuid < 1) {
            return false;
        }
        $snsdb = getOtherDb('snsdb');
        $snsdb->begin_trans();
        $newid =  $snsdb->insert('ebh_sns_follows', array(
            'uid' => $uid,
            'fuid' => $fuid
        ));
        if (empty($newid)) {
            return 0;
        }
        $row = $snsdb->query("SELECT `bid` FROM `ebh_sns_baseinfos` WHERE `uid`=$uid")->row_array();
        if (empty($row)) {
            $ret = $snsdb->insert('ebh_sns_baseinfos', array(
                'followsnum'=>1,
                'uid'=>$uid
            ));
        } else {
            $ret = $snsdb->update('ebh_sns_baseinfos', array(),"`uid`=$uid",array('followsnum'=>'followsnum+1'));
        }
        if (empty($ret)) {
            $snsdb->rollback_trans();
            return 0;
        }

        $row = $snsdb->query("SELECT `bid` FROM `ebh_sns_baseinfos` WHERE `uid`=$fuid")->row_array();
        if (empty($row)) {
            $ret = $snsdb->insert('ebh_sns_baseinfos', array(
                'fansnum'=>1,
                'uid'=>$fuid
            ));
        } else {
            $ret = $snsdb->update('ebh_sns_baseinfos', array(),"`uid`=$fuid",array('fansnum'=>'fansnum+1'));
        }
        if (empty($ret)) {
            $snsdb->rollback_trans();
            return 0;
        }
        $snsdb->commit_trans();
        return $newid;
    }

    /**
     * 取消关注
     * @param $uid
     * @param $fuid
     * @return bool
     */
    public function unfollow($uid, $fuid) {
        $uid = (int) $uid;
        $fuid = (int) $fuid;
        if ($uid < 1 || $fuid < 1) {
            return false;
        }
        $snsdb = getOtherDb('snsdb');
        return $snsdb->delete('ebh_sns_follows', array(
            'uid' => $uid,
            'fuid' => $fuid
        ));
    }

    /**
     * 管理员获取班级信息
     * @param $params
     * @return bool
     */
    public function getClass($params) {
        if (!$this->is_required(array('classid','crid'), $params)) {
            return false;
        }

        $classid = intval($params['classid']);
        $crid = intval($params['crid']);
        $sql = "SELECT `classid`,`classname` FROM `ebh_classes` WHERE `classid`=$classid AND `crid`=$crid";
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 获取网校班级列表
     * @param $crid
     * @return bool
     */
    public function getClasses($crid) {
        $crid = (int) $crid;
        if ($crid < 1) {
            return false;
        }
        $sql = "SELECT `classid`,`classname` FROM `ebh_classes` WHERE `crid`=$crid AND `status`=0";
        return Ebh()->db->query($sql)->list_array('classid');
    }

    /**
     * 管理员升班操作
     * @param $params
     * @param $errcode
     * @return bool
     */
    public function changeClass($params, &$errcode) {
        if (empty($params) || !is_array($params)) { return false; }
        $valid = $this->is_required(array('crid', 'sourceid', 'uid'), $params);
        if (!$valid) { return false; }
        $uid = intval($params['uid']);
        $crid = intval($params['crid']);
        $sourceid = intval($params['sourceid']);
        $classid = isset($params['classid']) ? intval($params['classid']) : 0;
        $pid = isset($params['pid']) ? intval($params['pid']) : 0;
        if ($uid < 1 || $sourceid < 1) {
            $errcode = 1;
            return false;
        }
        //验证操作权限
        $sql = "SELECT `a`.`uid`,`b`.`username` FROM `ebh_classrooms` `a` JOIN `ebh_users` `b` " .
            "ON `a`.`uid`=$uid AND `a`.`crid`=$crid AND `b`.`uid`=`a`.`uid`";

        $user = Ebh()->db->query($sql)->row_array();
        if (empty($user)) {
            $errcode = 2;
            return false;
        }
        //获取当前自主升班设置
        if ($pid > 0) {
            $sql = "SELECT `pid`,`classids`,`starttime`,`endtime` FROM `ebh_changeclass` WHERE `pid`=$pid AND " .
                "`crid`=$crid AND `sourceid`=$sourceid AND `classid`=0 AND `endtime`>" . SYSTIME;
        } else {
            $sql = "SELECT `pid`,`classids`,`starttime`,`endtime` FROM `ebh_changeclass` WHERE `crid`=$crid AND " .
                "`sourceid`=$sourceid AND `classid`=0 AND `endtime`>" . SYSTIME  . " ORDER BY `pid` DESC LIMIT 1";
        }
        $change = Ebh()->db->query($sql)->row_array();
        if ($pid > 0 && empty($change) || $pid > 0 && $change['pid'] != $pid || $pid <= 0 && !empty($change)) {
            $errcode = 3;
            return false;
        }


        //return false;
        $is_modify = !empty($change);

        if (!$is_modify) {
            //新增升班设置
            if ($sourceid == $classid) { return false; }
            if ($classid > 0) {
                //直接升班事务开始
                return $this->_changeByAdmin($crid, $sourceid, $classid, $user, $errcode);
            }
        }

        if (empty($params['classids'])) {
            $errcode = 4;
            return false;
        }
        $starttime = intval($params['starttime']);
        $endtime = intval($params['endtime']);
        if ($starttime < 1 || $endtime < 1 || $starttime > $endtime) {
            $errcode = 5;
            return false;
        }

        $id_arr = is_array($params['classids']) ? $params['classids'] : explode(',', strval($params['classids']));
        $id_arr = array_diff($id_arr, array($sourceid));
        $id_arr = array_filter($id_arr, function($item) {
            return preg_match('/^\d+$/', $item) > 0;
        });
        if (empty($id_arr)) {
            $errcode = 6;
            return false;
        }
        $id_arr = array_unique($id_arr);

        if (!$is_modify) {
            //自主升班事件开始
            return $this->_changeByStudent($crid, $sourceid, $id_arr, $starttime, $endtime, $user, $errcode);
        }


        $current_plan_id_arr = explode(',', $change['classids']);
        $current_plan_id_arr = array_diff($current_plan_id_arr, array($sourceid));
        $current_plan_id_arr = array_filter($current_plan_id_arr, function($item) {
            return preg_match('/^\d+$/', $item) > 0;
        });
        $diff1 = array_diff($current_plan_id_arr, $id_arr);
        $diff2 = array_diff($id_arr, $current_plan_id_arr);
        if (empty($diff1) && empty($diff2) && $change['starttime'] == $starttime && $change['endtime'] == $endtime) {
            //未做实际修改
            $errcode = 7;
            return false;
        }
        unset($diff1, $diff2);
        //修改自主升班设置
        //是否还原学生升班操作
        $is_reduce = $change['starttime'] < SYSTIME;
        return $this->_modifyChangePlan($change['pid'], $crid, $sourceid, $id_arr, $starttime, $endtime, $user, $is_reduce, $errcode);
    }

    /**
     * 获取当前班级的自主升班设置
     * @param $uid
     * @param $crid
     * @param $classid
     * @return bool
     */
    public function getChangePlan($uid, $crid, $classid) {
        $uid = (int) $uid;
        $crid = (int) $crid;
        $classid = (int) $classid;
        if ($uid < 1 || $crid < 1 || $classid < 1) {
            return false;
        }
        $sql = "SELECT `a`.`pid`,`a`.`classids`,`a`.`starttime`,`a`.`endtime`,`a`.`sourceid` FROM `ebh_changeclass` `a` JOIN `ebh_classrooms` `b` " .
            "ON `a`.`crid`=$crid AND `a`.`sourceid`=$classid AND `a`.`classid`=0 AND `a`.`endtime`>" . SYSTIME .
            " AND `b`.`uid`=$uid AND `a`.`crid`=`b`.`crid`";
        $change = Ebh()->db->query($sql)->row_array();
        if (empty($change)) {
            return false;
        }
        $class_ids = explode(',', $change['classids']);
        $class_ids = array_diff($class_ids, array($change['sourceid']));
        $class_ids = array_filter($class_ids, function($item) {
            return preg_match('/^\d+$/', $item) > 0;
        });
        if (empty($class_ids)) {
            return false;
        }
        $class_ids = array_unique($class_ids);
        $id_arr_str = implode(',', $class_ids);
        $classes = Ebh()->db->query(
            "SELECT `a`.`classid`,`a`.`classname` FROM `ebh_classes` `a` JOIN `ebh_classrooms` `b` ON " .
            "`a`.`classid` IN($id_arr_str) AND `a`.`crid`=$crid AND `a`.`status`=0 AND `b`.`uid`=$uid " .
            "AND `a`.`crid`=`b`.`crid` ORDER BY `a`.`classid` DESC")
            ->list_array('classid');
        if (empty($classes)) { //|| count($class_ids) != count($classes)去除班级是否被删除验证
            return false;
        }
        $change['classes'] = $classes;
        return $change;
    }

    /**
     * 学生自主升班操作
     * @param $params
     * @param $errcode
     * @return bool
     */
    public function changeClassBySelf($params, &$errcode) {
        $errcode = 100;
        if (empty($params) || !is_array($params)) {
            return false;
        }
        $valid = $this->is_required(array(
            'uid', 'crid', 'sourceid', 'classid'
        ), $params);

        if (!$valid) {
            return false;
        }
        $uid = intval($params['uid']);
        $crid = intval($params['crid']);
        $sourceid = intval($params['sourceid']);
        $classid = intval($params['classid']);
        $changelogid = intval($params['changelogid']);
        if ($sourceid == $classid) { return false; }
        //操作权限验证
        if (empty($changelogid)) {
            $sql = "SELECT `a`.`uid` FROM `ebh_classstudents` `a` JOIN `ebh_roomusers` `b` ON " .
                "`a`.`uid`=$uid AND `a`.`classid`=$sourceid AND " .
                "`a`.`uid`=`b`.`uid` AND `b`.`crid`=$crid LIMIT 1";
        } else {
            $sql = "SELECT `a`.`uid` FROM `ebh_classstudents` `a` JOIN `ebh_roomusers` `b` ON " .
                "`a`.`uid`=$uid AND `a`.`classid`=$changelogid AND " .
                "`a`.`uid`=`b`.`uid` AND `b`.`crid`=$crid LIMIT 1";
        }

        $user = Ebh()->db->query($sql)->row_array();
        if (empty($user)) {
            return false;
        }
        //目标班级范围验证
        $sql = "SELECT `pid`,`classids` FROM `ebh_changeclass` WHERE `crid`=$crid AND `sourceid`=$sourceid AND `classid`=0 AND `starttime`<" .
            SYSTIME . " AND `endtime`>" . SYSTIME . " ORDER BY `pid` DESC LIMIT 1";
        $change = Ebh()->db->query($sql)->row_array();
        if (empty($change)) {
            $errcode = 1;
            return false;
        }
        $classid_arr = explode(',', $change['classids']);

        if (!in_array($classid, $classid_arr)) {
            return false;
        }
        unset($classid_arr);
        //目标班级有效性验证(班级可能被删除)
        $sql = "SELECT `classid` FROM `ebh_classes` WHERE `classid`=$classid AND `crid`=$crid AND `status`=0";
        if (!Ebh()->db->query($sql)->row_array()) {
            return false;
        }
        //学生自主升班事务开始
        Ebh()->db->begin_trans();
        //记录升班日志
        if (empty($changelogid)) {
            $newid = Ebh()->db->insert('ebh_classlog', array(
                'pid' => $change['pid'],
                'crid' => $crid,
                'uid' => $uid,
                'sourceid' => $sourceid,
                'classid' => $classid,
                'udate' => SYSTIME
            ));
        } else {
            $newid = 0;
            if ($log = Ebh()->db->query(
                "SELECT `lid` FROM `ebh_classlog` WHERE `crid`=$crid AND `uid`=$uid AND `sourceid`=$sourceid AND " .
                "`classid`=$changelogid ORDER BY `lid` DESC LIMIT 1")->row_array()) {
                $newid = $log['lid'];
            }
        }

        if ($newid == 0) {
            return false;
        }

        if (empty($changelogid)) {
            //升班
            $sql = "UPDATE `ebh_classstudents` SET `classid`=$classid WHERE `uid`=$uid AND `classid`=$sourceid";
            if (!Ebh()->db->query($sql)) {
                Ebh()->db->rollback_trans();
                return false;
            }
            //更新原班级学生数
            $sql = "UPDATE `ebh_classes` SET `stunum`=`stunum`-1 WHERE `classid`=$sourceid";
            if (!Ebh()->db->query($sql)) {
                Ebh()->db->rollback_trans();
                return false;
            }
            //更新新班级学生数
            $sql = "UPDATE `ebh_classes` SET `stunum`=`stunum`+1 WHERE `classid`=$classid";
            if (!Ebh()->db->query($sql)) {
                Ebh()->db->rollback_trans();
                return false;
            }
            //迁移健康数据
            $ret = Ebh()->db->update('ebh_constitution', array(
                'cid' => $classid
            ), array(
                'uid' => $uid,
                'cid' => $sourceid
            ));
            if ($ret === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
            //迁移健康评论数据
            $ret = Ebh()->db->update('ebh_health_comment', array(
                'classid' => $classid
            ), array(
                'studentid' => $uid,
                'classid' => $sourceid
            ));
            if ($ret === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
        } else {
            $ret = Ebh()->db->update('ebh_classlog', array(
                'classid' => $classid
            ), "`lid`=$newid");
            if ($ret === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
            //升班
            $sql = "UPDATE `ebh_classstudents` SET `classid`=$classid WHERE `uid`=$uid AND `classid`=$changelogid";
            if (!Ebh()->db->query($sql)) {
                Ebh()->db->rollback_trans();
                return false;
            }
            //更新原班级学生数
            $sql = "UPDATE `ebh_classes` SET `stunum`=`stunum`-1 WHERE `classid`=$changelogid";
            if (!Ebh()->db->query($sql)) {
                Ebh()->db->rollback_trans();
                return false;
            }
            //更新新班级学生数
            $sql = "UPDATE `ebh_classes` SET `stunum`=`stunum`+1 WHERE `classid`=$classid";
            if (!Ebh()->db->query($sql)) {
                Ebh()->db->rollback_trans();
                return false;
            }
            //迁移健康数据
            $ret = Ebh()->db->update('ebh_constitution', array(
                'cid' => $classid
            ), array(
                'uid' => $uid,
                'cid' => $changelogid
            ));
            if ($ret === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
            //迁移健康评论数据
            $ret = Ebh()->db->update('ebh_health_comment', array(
                'classid' => $classid
            ), array(
                'studentid' => $uid,
                'classid' => $changelogid
            ));
            if ($ret === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
        }

        Ebh()->db->commit_trans();
        $errcode = 0;
        return $newid;
    }

    /**
     * 学生获取自主升班信息
     * @param $uid
     * @param $crid
     * @param $classid
     * @return bool
     */
    public function getChangeInfo($uid, $crid, $classid) {
        $sourceid = (int) $classid;
        $crid = (int) $crid;
        $uid = (int) $uid;
        if ($sourceid < 1 || $crid < 1 || $uid < 1) {
            return false;
        }

        $now = SYSTIME;

        $sql = "SELECT `a`.`classid`,`b`.`classids`,`b`.`sourceid` FROM `ebh_classlog` `a` JOIN `ebh_changeclass` `b` " .
            "ON `a`.`pid`=`b`.`pid` AND `a`.`uid`=$uid AND `a`.`crid`=$crid AND `a`.`classid`=$sourceid AND " .
            "`a`.`status`=0 AND `b`.`starttime`<$now AND `b`.`endtime`>$now ORDER BY `a`.`lid` DESC LIMIT 1";
        $classids = Ebh()->db->query($sql)->row_array();
        if (empty($classids)) {
            $sql = "SELECT `a`.`classids`,`a`.`sourceid` FROM `ebh_changeclass` `a` JOIN `ebh_classstudents` `b` ON " .
                "`a`.`crid`=$crid AND `a`.`sourceid`=$sourceid AND `a`.`classid`=0 " .
                "AND `a`.`starttime`<$now AND `a`.`endtime`>$now" .
                " AND `a`.`sourceid`=`b`.`classid` AND `b`.`uid`=$uid ORDER BY `a`.`pid` DESC LIMIT 1";
            $classids = Ebh()->db->query($sql)->row_array();
        }
        if (empty($classids)) {
            return false;
        }
        $classid_arr = explode(',', $classids['classids']);
        $classid_arr = array_diff($classid_arr, array($classids['sourceid']));
        $classid_arr = array_filter($classid_arr, function($e) {
           return !empty($e) && preg_match('/^\d+$/', $e) > 0;
        });
        $classid_arr = array_unique($classid_arr);
        if (empty($classid_arr)) {
            return false;
        }
        $classid_params = implode(',', $classid_arr);
        unset($classid_arr);
        $sql = "SELECT `classid`,`classname` FROM `ebh_classes` WHERE `classid` IN($classid_params) AND " .
            "`crid`=$crid AND `status`=0 ORDER BY `classid` DESC";
        $classes = Ebh()->db->query($sql)->list_array('classid');
        if (empty($classes)) {
            return false;
        }
        if (isset($classids['classid'])) {
            return array(
                'classid' => $classids['classid'],
                'sourceid' => $classids['sourceid'],
                'classes' => $classes
            );
        }
        return array(
            'classid' => 0,
            'sourceid' => $classids['sourceid'],
            'classes' => $classes
        );
    }

    /**
     * 参数数组非空验证
     * @param $fields 键名组
     * @param $params 参数数组
     * @return bool
     */
    private function is_required($fields, $params) {
        foreach ($fields as $field) {
            if (empty($params[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * 单个学生升班
     * @param $crid 网校ID
     * @param $sourceid 原班级ID
     * @param $classid 新班级ID
     * @param $user 用户数组
     * @param $student_id
     * @return bool
     */
    public function changeSingleStudent($crid, $sourceid, $classid, $user, $student_id) {
        $sql = "SELECT `classid`,`classname` FROM `ebh_classes` WHERE `classid`= $sourceid AND `crid`=$crid AND `status`=0 UNION " .
            "SELECT `classid`,`classname` FROM `ebh_classes` WHERE `classid`= $classid AND `crid`=$crid AND `status`=0";
        $classes = Ebh()->db->query($sql)->list_array('classid');
        if (empty($classes) || count($classes) != 2) {
            return false;
        }

        $message = sprintf('根据学校教务管理安排，你所处的班级已由原先的“%s”更换至“%s”，你可以点击“更多模块”查看“我的班级”相关信息。',
            Ebh()->db->escape_str($classes[$sourceid]['classname']),
            Ebh()->db->escape_str($classes[$classid]['classname']));

        $ulist = sprintf('%d=%s', $user['uid'], $user['username']);
        $insert_params = array(
            'crid' => $crid,
            'sourceid' => $sourceid,
            'classid' => $classid,
            'endtime' => SYSTIME
        );
        Ebh()->db->begin_trans();
        //记录升班事件日志
        $newid = Ebh()->db->insert('ebh_changeclass', $insert_params);
        if ($newid == 0) {
            return false;
        }

        //记录学生升班日志
        $new_log_id = Ebh()->db->insert('ebh_classlog', array(
            'pid' => $newid,
            'crid' => $crid,
            'uid' => $student_id,
            'sourceid' => $sourceid,
            'classid' => $classid,
            'udate' => SYSTIME
        ));
        if (empty($new_log_id)) {
            Ebh()->db->rollback_trans();
            return false;
        }
        //发送学生升班消息
        $new_message_id = Ebh()->db->insert('ebh_messages', array(
            'fromid' => $user['uid'],
            'toid' => $student_id,
            'sourceid' => $newid,
            'crid' => $crid,
            'type' => 3,
            'ulist' => $ulist,
            'message' => $message,
            'dateline' => SYSTIME
        ));
        if (empty($new_message_id)) {
            Ebh()->db->rollback_trans();
            return false;
        }
        //迁移健康数据
        $ret = Ebh()->db->update('ebh_constitution', array(
            'cid' => $classid
        ), array(
            'uid' => $student_id,
            'cid' => $sourceid
        ));
        if ($ret === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        //迁移健康评论数据
        $ret = Ebh()->db->update('ebh_health_comment', array(
            'classid' => $classid
        ), array(
            'studentid' => $student_id,
            'classid' => $sourceid
        ));
        if ($ret === false) {
            Ebh()->db->rollback_trans();
            return false;
        }

        Ebh()->db->commit_trans();

        //更新消息提示

        Ebh()->cache->hIncrBy('msg_'.$student_id.'_'.$crid, 3);
        return $newid;
    }

    /**
     * 直接升班
     * @param $crid 网校ID
     * @param $sourceid 原班级ID
     * @param $classid 新班级ID
     * @param $user 用户数组
     * @param $errcode
     * @return bool
     */
    private function _changeByAdmin($crid, $sourceid, $classid, $user, &$errcode) {
        $sql = "SELECT `classid`,`classname` FROM `ebh_classes` WHERE `classid`= $sourceid AND `crid`=$crid AND `status`=0 UNION " .
            "SELECT `classid`,`classname` FROM `ebh_classes` WHERE `classid`= $classid AND `crid`=$crid AND `status`=0";
        $classes = Ebh()->db->query($sql)->list_array('classid');

        if (empty($classes) || count($classes) != 2) {
            $errcode = 101;
            return false;
        }

        $message = sprintf('根据学校教务管理安排，你所处的班级已由原先的“%s”更换至“%s”，你可以点击“更多模块”查看“我的班级”相关信息。',
            Ebh()->db->escape_str($classes[$sourceid]['classname']),
            Ebh()->db->escape_str($classes[$classid]['classname']));

        $ulist = sprintf('%d=%s', $user['uid'], $user['username']);
        $insert_params = array(
            'crid' => $crid,
            'sourceid' => $sourceid,
            'classid' => $classid,
            'endtime' => SYSTIME
        );

        Ebh()->db->begin_trans();
        //记录升班事件日志
        $newid = Ebh()->db->insert('ebh_changeclass', $insert_params);
        if ($newid == 0) {
            $errcode = 102;
            return false;
        }

        $sql = "SELECT `uid` FROM `ebh_classstudents` WHERE `classid`=$sourceid";
        if ($student_id_arr = Ebh()->db->query($sql)->list_field('uid')) {
            foreach ($student_id_arr as $student_id) {
                //记录学生升班日志
                $new_log_id = Ebh()->db->insert('ebh_classlog', array(
                    'pid' => $newid,
                    'crid' => $crid,
                    'uid' => $student_id,
                    'sourceid' => $sourceid,
                    'classid' => $classid,
                    'udate' => SYSTIME
                ));
                if (empty($new_log_id)) {
                    Ebh()->db->rollback_trans();
                    $errcode = 103;
                    return false;
                }
                //发送学生升班消息
                $new_message_id = Ebh()->db->insert('ebh_messages', array(
                    'fromid' => $user['uid'],
                    'toid' => $student_id,
                    'sourceid' => $newid,
                    'crid' => $crid,
                    'type' => 3,
                    'ulist' => $ulist,
                    'message' => $message,
                    'dateline' => SYSTIME
                ));
                if (empty($new_message_id)) {
                    Ebh()->db->rollback_trans();
                    $errcode = 104;
                    return false;
                }
                //迁移健康数据
                $ret = Ebh()->db->update('ebh_constitution', array(
                    'cid' => $classid
                ), array(
                    'uid' => $student_id,
                    'cid' => $sourceid
                ));
                if ($ret === false) {
                    Ebh()->db->rollback_trans();
                    $errcode = 105;
                    return false;
                }
                //迁移健康评论数据
                $ret = Ebh()->db->update('ebh_health_comment', array(
                    'classid' => $classid
                ), array(
                    'studentid' => $student_id,
                    'classid' => $sourceid
                ));
                if ($ret === false) {
                    Ebh()->db->rollback_trans();
                    return false;
                }
            }
        }
        $student_count = empty($student_id_arr) ? 0 : count($student_id_arr);
        if ($student_count > 0) {
            //学生升班
            $sql = "UPDATE `ebh_classstudents` SET `classid`=$classid WHERE `classid`=$sourceid";
            if (!Ebh()->db->query($sql)) {
                Ebh()->db->rollback_trans();
                $errcode = 106;
                return false;
            }
            //更新原班级学生数
            $sql = "UPDATE `ebh_classes` SET `stunum`=`stunum`-$student_count WHERE `classid`=$sourceid";
            if (!Ebh()->db->query($sql)) {
                Ebh()->db->rollback_trans();
                $errcode = 107;
                return false;
            }
            //更新新班级学生数
            $sql = "UPDATE `ebh_classes` SET `stunum`=`stunum`+$student_count WHERE `classid`=$classid";
            if (!Ebh()->db->query($sql)) {
                Ebh()->db->rollback_trans();
                $errcode = 108;
                return false;
            }
        }

        Ebh()->db->commit_trans();

        if (!empty($student_id_arr)) {
            //更新消息提示

            foreach ($student_id_arr as $student_id) {
                Ebh()->cache->hIncrBy('msg_'.$student_id.'_'.$crid, 3);
            }
        }
        return $newid;
    }

    /**
     * 自主升班
     * @param $crid 网校ID
     * @param $sourceid 原班级ID
     * @param $classids 新班级ID组
     * @param $starttime 自主升班开始时间戳
     * @param $endtime 自主升班截止时间戳
     * @param $user 用户数组
     * @param $errcode
     * @return bool
     */
    private function _changeByStudent($crid, $sourceid, $classids, $starttime, $endtime, $user, &$errcode) {
        $insert_params = array(
            'crid' => $crid,
            'sourceid' => $sourceid,
            'classids' => implode(',', $classids),
            'starttime' => $starttime,
            'endtime' => $endtime
        );
        $classids[] = $sourceid;
        $id_arr_str = implode(',', $classids);
        $classes = Ebh()->db->query(
            "SELECT `classid`,`classname` FROM `ebh_classes` WHERE `classid` IN($id_arr_str) AND `crid`=$crid AND `status`=0")
            ->list_array('classid');
        if (empty($classes) || count($classids) != count($classes)) {
            $errcode = 1001;
            return false;
        }

        $source_class_name = Ebh()->db->escape_str($classes[$sourceid]['classname']);
        unset($classes[$sourceid]);
        $class_options = array();
        foreach ($classes as $classitem) {
            $class_options[] = '“' . Ebh()->db->escape_str($classitem['classname']) . '”';
        }
        $class_options_arr = implode('、', $class_options);
        unset($class_options, $classids, $classes);
        $message = sprintf('根据学校教务管理安排，你所处的班级将由原先的“%s”更换至%s，请各位同学点击“更多模块”，进入“我的班级”页面，点击“自主升班”按钮，根据自身实际情况，选择正确的班级。<br />班级调整持续时间为：%s  至  %s',
            $source_class_name, $class_options_arr,
            date('Y-m-d H:i', $starttime),
            date('Y-m-d H:i', $endtime));
        $uid = $user['uid'];
        $ulist = sprintf('%d=%s', $uid, $user['username']);

        Ebh()->db->begin_trans();
        //记录升班事件日志
        $newid = Ebh()->db->insert('ebh_changeclass', $insert_params);
        if ($newid == 0) {
            $errcode = 1002;
            return false;
        }

        $student_id_arr = Ebh()->db->query("SELECT `uid` FROM `ebh_classstudents` WHERE `classid`=$sourceid")->list_field('uid');
        if (!empty($student_id_arr)) {
            //发送学生自主升班消息
            foreach ($student_id_arr as $studentid) {
                if (!Ebh()->db->insert('ebh_messages', array(
                    'fromid' => $uid,
                    'toid' => $studentid,
                    'sourceid' => $newid,
                    'crid' => $crid,
                    'type' => 3,
                    'ulist' => $ulist,
                    'message' => $message,
                    'dateline' => SYSTIME
                ))) {
                    Ebh()->db->rollback_trans();
                    $errcode = 1003;
                    return false;
                }
            }
        }
        Ebh()->db->commit_trans();
        if (!empty($student_id_arr)) {
            //更新消息提示

            foreach ($student_id_arr as $stuent_id) {
                Ebh()->cache->hIncrBy('msg_'.$stuent_id.'_'.$crid, 3);
            }
        }
        return $newid;
    }

    /**
     * 修改自主升班设置
     * @param $pid
     * @param $crid
     * @param $sourceid
     * @param $classids
     * @param $starttime
     * @param $endtime
     * @param $user
     * @param $is_reduce
     * @param $errcode
     * @return bool
     */
    private function _modifyChangePlan($pid, $crid, $sourceid, $classids, $starttime, $endtime, $user, $is_reduce, &$errcode) {
        $up_params = array(
            'classids' => implode(',', $classids),
            'starttime' => $starttime,
            'endtime' => $endtime
        );
        $classids[] = $sourceid;
        $id_arr_str = implode(',', $classids);
        $classes = Ebh()->db->query(
            "SELECT `classid`,`classname` FROM `ebh_classes` WHERE `classid` IN($id_arr_str) AND `crid`=$crid AND `status`=0")
            ->list_array('classid');
        if (empty($classes) || count($classids) != count($classes)) {
            $errcode = 10001;
            return false;
        }

        $source_class_name = Ebh()->db->escape_str($classes[$sourceid]['classname']);
        unset($classes[$sourceid]);
        $class_options = array();
        foreach ($classes as $classitem) {
            $class_options[] = '“' . Ebh()->db->escape_str($classitem['classname']) . '”';
        }
        $class_options_arr = implode('、', $class_options);
        unset($class_options, $classids, $classes);

        $message = sprintf('根据学校教务管理安排，你所处的班级将由原先的“%s”更换至%s，请各位同学点击“更多模块”，进入“我的班级”页面，点击“自主升班”按钮，根据自身实际情况，选择正确的班级。<br />班级调整持续时间为：%s  至  %s',
            $source_class_name, $class_options_arr,
            date('Y-m-d H:i', $starttime),
            date('Y-m-d H:i', $endtime));
        $uid = $user['uid'];
        $ulist = sprintf('%d=%s', $uid, $user['username']);

        Ebh()->db->begin_trans();
        //修改升班事件日志
        $ret = Ebh()->db->update('ebh_changeclass', $up_params, "`pid`=$pid");
        if ($ret === false) {
            $errcode = 10002;
            return false;
        }

        if ($is_reduce) {
            //还原已操作升班的学生到旧班级中
            $restore = array(0);
            $sql = "SELECT `lid`,`uid`,`classid` FROM `ebh_classlog` WHERE `pid`=$pid AND `status`=0 AND `sourceid`=$sourceid AND `crid`=$crid";
            $logs = Ebh()->db->query($sql)->list_array();
            if (!empty($logs)) {
                foreach ($logs as $log) {
                    //迁移健康数据
                    $ret = Ebh()->db->update('ebh_constitution', array(
                        'classid' => $log['classid']
                    ), "`uid`=" . $log['uid']);
                    if ($ret === false) {
                        Ebh()->db->rollback_trans();
                        $errcode = 10003;
                        return false;
                    }

                    $ret = Ebh()->db->update('ebh_classstudents', array(
                        'classid' => $sourceid
                    ), array(
                        'uid' => $log['uid'],
                        'classid' => $log['classid']
                    ));
                    if ($ret === false) {
                        Ebh()->db->rollback_trans();
                        $errcode = 10004;
                        return false;
                    }
                    if ($ret > 0) {
                        if (!key_exists($log['classid'], $restore)) {
                            $restore[$log['classid']] = 0;
                        }
                        $restore[$log['classid']]++;
                        $restore[0]++;
                    }
                }
            }
            //更新班级学生数
            foreach ($restore as $rk => $rv) {
                if ($rk > 0 && $rv > 0) {
                    if (!Ebh()->db->query("UPDATE `ebh_classes` SET `stunum`=`stunum`-$rv WHERE `classid`=$rk")) {
                        Ebh()->db->rollback_trans();
                        $errcode = 10005;
                        return false;
                    }
                } elseif ($rk == 0) {
                    if (!Ebh()->db->query("UPDATE `ebh_classes` SET `stunum`=`stunum`+$rv WHERE `classid`=$sourceid")) {
                        Ebh()->db->rollback_trans();
                        $errcode = 10006;
                        return false;
                    }
                }

            }
            //标志无效的升班日志
            $log_id_arr = array_column($logs, 'lid');
            if (!empty($log_id_arr)) {
                $log_id_arr = array_unique($log_id_arr);
                $log_id_arr_str = implode(',', $log_id_arr);
                unset($log_id_arr);
                $ret = Ebh()->db->query("UPDATE `ebh_classlog` SET `status`=1 WHERE `lid` IN($log_id_arr_str)");
                if ($ret === false) {
                    Ebh()->db->rollback_trans();
                    $errcode = 10007;
                    return false;
                }
            }
        }
        $student_id_arr = Ebh()->db->query("SELECT `uid` FROM `ebh_classstudents` WHERE `classid`=$sourceid")->list_field('uid');
        if (!empty($student_id_arr)) {
            //发送学生自主升班消息
            foreach ($student_id_arr as $studentid) {
                if (!Ebh()->db->insert('ebh_messages', array(
                    'fromid' => $uid,
                    'toid' => $studentid,
                    'sourceid' => $pid,
                    'crid' => $crid,
                    'type' => 3,
                    'ulist' => $ulist,
                    'message' => $message,
                    'dateline' => SYSTIME
                ))) {
                    Ebh()->db->rollback_trans();
                    $errcode = 10008;
                    return false;
                }
            }
        }
        Ebh()->db->commit_trans();
        if (!empty($student_id_arr)) {
            //更新消息提示

            foreach ($student_id_arr as $stuent_id) {
                Ebh()->cache->hIncrBy('msg_'.$stuent_id.'_'.$crid, 3);
            }
        }
        Ebh()->db->commit_trans();
        return $pid;
    }

    /**
     * 统计学生数
     * @param $crid
     * @param $uid
     * @param $classid
     * @param string $q
     * @return bool
     */
    public function getStudentCount($crid, $uid, $classid, $q = '') {
        $crid = (int) $crid;
        $classid = (int) $classid;
        $uid = (int) $uid;
        if ($crid < 1 || $classid < 0 || $uid < 1) {
            return false;
        }
        $sql = "SELECT COUNT(1) AS `c` FROM `ebh_roomusers` `a` JOIN `ebh_users` `b` ON `a`.`uid`=`b`.`uid`";
        if ($classid > 0) {
            $sql .= " JOIN `ebh_classstudents` `c` ON `a`.`uid`=`c`.`uid`";
        }
        $sql .= " WHERE `a`.`crid`=$crid AND `a`.`uid`<>$uid";
        if ($classid > 0) {
            $sql .= " AND `c`.`classid`=$classid";
        }
        if (!empty($q)) {
            $q = Ebh()->db->escape('%'.$q.'%');
            $sql .= " AND (`b`.`username` LIKE $q OR `b`.`realname` LIKE $q)";
        }
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['c'];
        }
        return false;
    }

    public function getStudents($crid, $uid, $classid, $q = '', $pageindex = 1, $pagesize = 10) {
        $crid = (int) $crid;
        $classid = (int) $classid;
        $uid = (int) $uid;
        if ($crid < 1 || $classid < 0 || $uid < 1) {
            return false;
        }
        $pageindex = max(intval($pageindex), 1);
        $pagesize = max(intval($pagesize), 1);
        $offset = ($pageindex - 1) * $pagesize;
        $sql = "SELECT `b`.`uid`,`b`.`username`,`b`.`realname`,`b`.`face`,`b`.`groupid`,`b`.`sex`,`e`.`mobile`,`e`.`email`,`e`.`phone` 
                FROM `ebh_roomusers` `a` JOIN `ebh_users` `b` ON `a`.`uid`=`b`.`uid`";
        if ($classid > 0) {
            $sql .= " JOIN `ebh_classstudents` `c` ON `a`.`uid`=`c`.`uid`";
        }
        $sql .= " JOIN `ebh_members` `e` ON `a`.`uid`=`e`.`memberid`";
        $sql .= " WHERE `a`.`crid`=$crid AND `a`.`uid`<>$uid";
        if ($classid > 0) {
            $sql .= " AND `c`.`classid`=$classid";
        }
        if (!empty($q)) {
            $q = Ebh()->db->escape('%'.$q.'%');
            $sql .= " AND (`b`.`username` LIKE $q OR `b`.`realname` LIKE $q)";
        }
        $sql .= " LIMIT $offset,$pagesize";
        return Ebh()->db->query($sql)->list_array('uid');
    }
}