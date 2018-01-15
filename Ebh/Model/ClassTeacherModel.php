<?php
/**
 * ebhservice.
 * Author: ycq
 */
class ClassTeacherModel{
    /**
     * 获取教师任课班级列表
     * @param int $uid 教师ID
     * @param int $crid 网校ID
     * @return mixed
     */
    public function getClassesForTeacher($uid, $crid) {
        $sql = 'SELECT `b`.`classid`,`b`.`classname` 
                FROM `ebh_classteachers` `a` JOIN `ebh_classes` `b` ON `b`.`classid`=`a`.`classid` 
                WHERE `a`.`uid`='.$uid.' AND `b`.`crid`='.$crid.' ORDER BY `b`.`displayorder` DESC,`b`.`classid` DESC';
        return Ebh()->db->query($sql)->list_array('classid');
    }

    /**
     * 获取教师管理的部门路径范围
     * @param int $uid 教师用户ID
     * @param int $crid 网校ID
     * @return mixed
     */
    public function getDeptsForTeacher($uid, $crid) {
        $wheres = array(
            '`a`.`uid`='.$uid,
            '`b`.`crid`='.$crid
        );
        $sql = 'SELECT `b`.`lft`,`b`.`rgt` FROM `ebh_classteachers` `a` JOIN `ebh_classes` `b` ON `b`.`classid`=`a`.`classid` WHERE '.implode(' AND ', $wheres).' ORDER BY `b`.`lft` ASC';
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 获取教师的所有部门(包括下级部门)
     * @param int $crid 网校ID
     * @param array $paths 部门路径参数数组
     * @return mixed
     */
    public function getDeptsForTeacherWithPath($crid, $paths = array()) {
        $sql = array();
        foreach ($paths as $path) {
            $sql[] = 'SELECT `classid`,`classname`,`code`,`superior`,`category`,`path`,`stunum`,`lft`,`rgt`,`displayorder` FROM `ebh_classes` WHERE `crid`='.$crid.' AND `status`=0 AND `lft`>='.$path['lft'].' AND `rgt`<='.$path['rgt'];
        }
        $sql = implode(' UNION ', $sql);
        return Ebh()->db->query($sql)->list_array('classid');
    }

    /**
     * 添加班级任课教师
     * @param int $uid 教师ID
     * @param int $classid 班级ID
     * @param int $folderid 任课课程ID
     * @return mixed
     */
    public function addTeacher($uid, $classid, $folderid = 0) {
        return Ebh()->db->insert('ebh_classteachers', array(
            'uid' => $uid,
            'classid' => $classid,
            'folderid' => $folderid
        ));
    }
}