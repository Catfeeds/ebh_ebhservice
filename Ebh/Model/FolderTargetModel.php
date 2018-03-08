<?php

/**
 * 课程服务对象(范围)
 * Created by ycq.
 * User: ycq
 * Date: 2018/3/5
 * Time: 13:45
 */
class FolderTargetModel {
    /**
     * 获取课程服务对象(范围)
     * @param int $folderid 课程ID
     * @param int $crid 网校ID
     * @return string
     */
    public function getTargetsForFolder($folderid, $crid) {
        $wheres = array(
            '`folderid`='.$folderid,
            '`crid`='.$crid,
            '`status`=0'
        );
        $sql = 'SELECT `targets` FROM `ebh_foldertargets` WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret['targets'])) {
            return '';
        }
        return trim($ret['targets'], " \t\n\r \v,");
    }

    /**
     * 获取课程服务对象(范围)列表
     * @param array $folderids 课程ID列表
     * @param int $crid 网校ID
     * @return array
     */
    public function getTargets($folderids, $crid) {
        $wheres = array(
            '`folderid` IN('.implode(',', $folderids).')',
            '`crid`='.$crid,
            '`status`=0'
        );
        $sql = 'SELECT `folderid`,`targets` FROM `ebh_foldertargets` WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array('folderid');
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 编辑课程服务对象
     * @param array $ids 年级或班级ID集，年级用负数表示
     * @param int $folderid 课程ID
     * @param int $crid 网校ID
     * @return int
     */
    public function edit($ids, $folderid, $crid) {
        if (empty($ids)) {
            return Ebh()->db->update('ebh_foldertargets', array('status' => 1), array('folderid' => $folderid, 'crid' => $crid));
        }
        $targets = implode(',', $ids);
        $targets = Ebh()->db->escape($targets);
        $sql = 'INSERT INTO `ebh_foldertargets`(`folderid`,`crid`,`targets`,`status`) VALUES('.$folderid.','.$crid.','.$targets.',0) ON DUPLICATE KEY UPDATE `targets`='.$targets.',`status`=0';
        Ebh()->db->query($sql, false);
        return Ebh()->db->affected_rows();
    }
}