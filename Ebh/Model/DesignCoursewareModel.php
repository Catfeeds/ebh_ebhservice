<?php
/**
 * 网校装扮课件模块
 * Author: ycq
 */
class DesignCoursewareModel{
    /**
     * 免费试听
     */
    const FREE = 1;
    /**
     * 部件视频
     */
    const VEDIO = 2;
    /**
     * 设置免费试听课件
     * @param array $cwids 课件ID集
     * @param int $crid 网校ID
     * @param int $did 装扮ID
     * @param int $groupid 课件分组ID
     * @return int 设置成功数
     */
    public function setDesignCoursewares($cwids, $crid, $did, $groupid = self::FREE) {
        $valid = $this->checkDesign($did, $crid);
        if (!$valid) {
            return false;
        }
        $affectedRows = 0;
        Ebh()->db->begin_trans();
        Ebh()->db->update('ebh_designcoursewares', array('del' => 1), array('did' => $did, 'groupid' => $groupid));
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        foreach ($cwids as $cwid) {
            $affectedRows += Ebh()->db->query('INSERT INTO `ebh_designcoursewares`(`cwid`,`did`,`groupid`,`dateline`,`del`) VALUES('.$cwid.','.$did.','.$groupid.','.SYSTIME.',0) ON DUPLICATE KEY UPDATE `del`=0,`dateline`='.SYSTIME, false);
            if (Ebh()->db->trans_status() === false) {
                Ebh()->db->rollback_trans();
                return false;
            }
        }
        Ebh()->db->commit_trans();
        return $affectedRows;
    }

    /**
     * 获取免费试听列表
     * @param int $did 装扮ID
     * @param int $crid 网校ID
     * @param array $cwids 课件ID集
     * @return mixed
     */
    public function getFreeCoursewareList($did, $crid, $cwids = array()) {
        $valid = $this->checkDesign($did, $crid);
        if (!$valid) {
            return array();
        }
        $wheres = array(
            '`a`.`did`='.$did,
            '`a`.`groupid`='.self::FREE,
            '`a`.`del`=0',
            '`b`.`status`=1'
        );
        if (!empty($cwids)) {
            $wheres[] = '`a`.`cwid` IN('.implode(',', $cwids).')';
        }
        $sql = 'SELECT `c`.`cwid`,`c`.`crid`,`b`.`ism3u8`,`b`.`title`,`b`.`logo`,`b`.`viewnum` 
                FROM `ebh_designcoursewares` `a` 
                JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` 
                JOIN `ebh_roomcourses` `c` ON `c`.`cwid`=`a`.`cwid` 
                WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->list_array('cwid');
    }

    /**
     * 获取视频课件
     * @param int $cwid 课件ID
     * @param int $did 装扮ID
     * @param int $crid 网校ID
     * @return mixed
     */
    public function getVedio($cwid, $did, $crid) {
        $valid = $this->checkDesign($did, $crid);
        if (!$valid) {
            return false;
        }
        $wheres = array(
            '`a`.`cwid`='.$cwid,
            '`a`.`did`='.$did,
            '`a`.`groupid`='.self::VEDIO,
            '`a`.`del`=0',
            '`b`.`status`=1',
            '`b`.`ism3u8`=1'
        );
        $sql = 'SELECT `b`.`cwid`,`b`.`logo`,`b`.`thumb`,`b`.`cwurl`,`b`.`cwsize`,`b`.`summary`,`c`.`crid`,`c`.`folderid`,`c`.`isfree` 
                FROM `ebh_designcoursewares` `a` 
                JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` 
                JOIN `ebh_roomcourses` `c` ON `c`.`cwid`=`a`.`cwid` 
                WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return false;
        }
        if ($ret['crid'] == $crid) {
            //本校课件，直接返回
            return $ret;
        }
        //验证企业选课课件是否有效
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`sourcecrid`='.$ret['crid'],
            '`a`.`del`=0',
            '`b`.`status`=0',
            '`c`.`del`=0',
            '`c`.`power`=0',
            '`c`.`folderlevel`>1'
        );
        $sql = 'SELECT `a`.`itemid` FROM `ebh_schsourceitems` `a` JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`itemid` 
                JOIN `ebh_folders` `c` ON `c`.`folderid`=`b`.`folderid` 
                WHERE '.implode(' AND ', $wheres);
        $item = Ebh()->db->query($sql)->row_array();
        if (empty($item)) {
            return false;
        }
        return $ret;
    }

    /**
     * 验证装扮ID是否有效
     * @param int $did 装扮ID
     * @param int $crid 网校ID
     * @return bool
     */
    private function checkDesign($did, $crid) {
        $wheres = array(
            '`did`='.$did,
            '`crid`='.$crid,
            '`checked`=1'
        );
        $sql = 'SELECT `checked` FROM `ebh_roomdesigns` WHERE '.implode(' AND ', $wheres).' LIMIT 1';
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret['checked'])) {
            return true;
        }
        return false;
    }
}